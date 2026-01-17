// --- useInstantPlay Composable ---
// 處理即時揪球、分區聊天室邏輯

const useInstantPlay = (isLoggedIn, currentUser, showToast, view) => {
    const instantRooms = ref([]);
    const currentRoom = ref(null);
    const instantMessages = ref([]);
    const isInstantLoading = ref(false);
    const globalInstantStats = reactive({ active_count: 0, display_count: 0, avatars: [] });
    const instantMessageDraft = ref('');
    
    const isSending = ref(false);
    let statsTimer = null;
    let currentChannel = null;

    // Mobile scroll lock: prevent body scroll when chat room is open
    watch(currentRoom, (room) => {
        if (window.innerWidth < 640) { // sm breakpoint
            if (room) {
                document.body.style.overflow = 'hidden';
                document.body.style.position = 'fixed';
                document.body.style.width = '100%';
                document.body.style.top = `-${window.scrollY}px`;
            } else {
                const scrollY = document.body.style.top;
                document.body.style.overflow = '';
                document.body.style.position = '';
                document.body.style.width = '';
                document.body.style.top = '';
                window.scrollTo(0, parseInt(scrollY || '0') * -1);
            }
        }
    });
    
    const fetchRooms = async () => {
        try {
            const response = await api.get('/instant/rooms');
            instantRooms.value = response.data;
        } catch (error) {
            console.error('Failed to fetch rooms', error);
        }
    };

    const fetchStats = async () => {
        try {
            const response = await api.get('/instant/stats');
            console.log('Stats: Refreshing from API');
            Object.assign(globalInstantStats, response.data);
        } catch (error) {
            console.error('Failed to fetch stats', error);
        }
    };

    const selectRoom = async (room) => {
        // PREVENTION: Don't rejoin same room
        if (currentRoom.value && currentRoom.value.slug === room.slug && currentChannel) {
            console.log('Already in this room channel, skipping join.');
            return;
        }

        // Leave old room room first
        if (window.Echo && currentChannel && currentRoom.value) {
            const oldSlug = currentRoom.value.slug;
            console.log('Switching: Leaving room:', oldSlug);
            window.Echo.leave('instant-room.' + oldSlug);
            currentChannel = null;
            api.post('/instant/exit-room');
        }

        currentRoom.value = room;
        instantMessages.value = [];
        await fetchMessages();

        if (!isLoggedIn.value) return;

        console.log('Joining room channel:', room.slug);
        if (typeof window.initEcho === 'function') window.initEcho();
        if (!window.Echo) return;

        currentChannel = window.Echo.join(`instant-room.${room.slug}`)
            .here((users) => {
                console.log(`Room [${room.slug}] Here:`, users.length);
                if (currentRoom.value) currentRoom.value.active_count = users.length;
                api.post(`/instant/rooms/${room.slug}/sync`);
            })
            .joining((user) => {
                console.log('User joined room:', user.name);
                if (currentRoom.value) currentRoom.value.active_count = (currentRoom.value.active_count || 0) + 1;
                api.post(`/instant/rooms/${room.slug}/sync`);
            })
            .leaving((user) => {
                console.log('User left room:', user.name);
                if (currentRoom.value) currentRoom.value.active_count = Math.max(0, (currentRoom.value.active_count || 1) - 1);
                api.post(`/instant/rooms/${room.slug}/sync`);
            })
            .listen('.message.sent', (e) => {
                instantMessages.value.push(e);
                scrollToBottom();
            });
    };

    const fetchMessages = async () => {
        if (!currentRoom.value) return;
        try {
            const response = await api.get(`/instant/rooms/${currentRoom.value.slug}/messages`);
            instantMessages.value = response.data;
            scrollToBottom();
        } catch (error) {
            console.error('Failed to fetch messages', error);
        }
    };

    const sendInstantMessage = async (content = null) => {
        if (!isLoggedIn.value) {
            showToast('請先登入後再發言', 'warning');
            return;
        }
        
        if (isSending.value) return;

        const finalContent = content || instantMessageDraft.value;
        if (!finalContent || !currentRoom.value) return;

        isSending.value = true;
        try {
            const response = await api.post(`/instant/rooms/${currentRoom.value.slug}/messages`, {
                content: finalContent
            });
            instantMessages.value.push(response.data);
            instantMessageDraft.value = '';
            scrollToBottom();
        } catch (error) {
            showToast('發送失敗，請稍後再試', 'error');
        } finally {
            isSending.value = false;
        }
    };

    const scrollToBottom = () => {
        nextTick(() => {
            const container = document.getElementById('instant-messages-container');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        });
    };

    const joinBySlug = async (slug) => {
        if (!instantRooms.value.length) await fetchRooms();
        const room = instantRooms.value.find(r => r.slug === slug);
        if (room) selectRoom(room);
    };

    // Presence Logic: WebSocket replaces ALL Polling
    let lobbyChannel = null;
    let statsChannel = null;

    const activatePresence = async () => {
        if (statsChannel) return;

        // Preload initial data
        fetchRooms();
        fetchStats();

        // Ensure Echo is initialized
        if (typeof window.initEcho === 'function') window.initEcho();
        if (!window.Echo) {
            console.warn('Echo not available yet');
            return;
        }

        // 1. Join PUBLIC channel for stats updates (Guests & Members)
        console.log('Attempting to connect to public stats channel...');
        statsChannel = window.Echo.channel('instant-public');
        
        // Debug connection state
        if (window.Echo && window.Echo.connector.socket) {
            window.Echo.connector.socket.on('connect', () => {
                console.log('WebSocket Connected successfully (Socket.io)');
                // If we are a guest, maybe trigger one manual fetch to be safe
                if (!isLoggedIn.value) fetchStats();
            });
            window.Echo.connector.socket.on('reconnect', () => console.log('WebSocket Reconnected'));
            window.Echo.connector.socket.on('connect_error', (err) => console.error('WebSocket Connection Error:', err));
        }

        statsChannel
            .listen('.stats.changed', (payload) => {
                console.log('Pulse Signal received [stats.changed]: Triggering API Refresh');
                fetchRooms();
                fetchStats();
            })
            // Fallbacks if needed
            .listen('stats.changed', (payload) => {
                console.log('Pulse Signal received [stats.changed fallback]: Triggering API Refresh');
                fetchRooms();
                fetchStats();
            });

        // 2. Join PRESENCE channel for identity tracking (Members only)
        if (isLoggedIn.value) {
            console.log('Connecting to Presence Lobby...');
            lobbyChannel = window.Echo.join('instant-lobby')
                .here((users) => {
                    console.log('Presence Lobby Here:', users.length);
                    api.post('/instant/sync-global');
                })
                .joining((user) => {
                    console.log('Lobby Join:', user.name);
                    api.post('/instant/sync-global');
                })
                .leaving((user) => {
                    console.log('Lobby Leave:', user.name);
                    api.post('/instant/sync-global');
                });
        }
    };

    const deactivatePresence = () => {
        if (currentChannel && currentRoom.value && window.Echo) {
            window.Echo.leave(`instant-room.${currentRoom.value.slug}`);
            currentChannel = null;
            api.post('/instant/exit-room');
        }

        if (lobbyChannel && window.Echo) {
            window.Echo.leave('instant-lobby');
            lobbyChannel = null;
            api.post('/instant/exit-room');
        }

        if (statsChannel && window.Echo) {
            window.Echo.leave('instant-public');
            statsChannel = null;
        }

        currentRoom.value = null;
    };

    // Watch for view changes
    watch(view, (newView, oldView) => {
        if (newView === 'instant-play') {
            activatePresence();
        } else if (oldView === 'instant-play') {
            deactivatePresence();
        }
    });

    onMounted(() => {
        // Handle initial load if deep linked
        if (view.value === 'instant-play') {
            activatePresence();
        }
    });

    onUnmounted(() => {
        deactivatePresence();
    });

    return {
        instantRooms, currentRoom, instantMessages, isInstantLoading, globalInstantStats, instantMessageDraft, isSending,
        fetchRooms, selectRoom, sendInstantMessage, fetchMessages, joinBySlug
    };
};
