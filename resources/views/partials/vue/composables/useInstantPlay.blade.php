// --- useInstantPlay Composable ---
// 處理即時揪球、分區聊天室邏輯

const useInstantPlay = (isLoggedIn, currentUser, showToast, view) => {
    const instantRooms = ref([]);
    const currentRoom = ref(null);
    const instantMessages = ref([]);
    const isInstantLoading = ref(false);
    const globalInstantStats = reactive({ active_count: 0, display_count: 0, avatars: [] });
    const instantMessageDraft = ref('');
    
    // Global Features
    const globalData = reactive({ recent_messages: [], lfg_users: [] });
    const isLfg = ref(false); // Local state for toggle
    const selectedLfgRemark = ref('');
    
    const isSending = ref(false);
    const activityNotifications = ref([]);
    const roomSearch = ref('');
    const roomCategory = ref('全部');
    
    // Geographic Mapping
    const REGION_GROUPS = {
        '北部': ['基隆市', '台北市', '新北市', '桃園市', '新竹市', '新竹縣'],
        '中部': ['苗栗縣', '台中市', '彰化縣', '南投縣', '雲林縣'],
        '南部': ['嘉義市', '嘉義縣', '台南市', '高雄市', '屏東縣'],
        '東部/離島': ['宜蘭縣', '花蓮縣', '台東縣', '澎湖縣', '金門縣', '連江縣']
    };

    const sortedAndFilteredRooms = computed(() => {
        let rooms = [...instantRooms.value];

        // 1. Search Filter
        if (roomSearch.value) {
            const s = roomSearch.value.toLowerCase();
            rooms = rooms.filter(r => r.name.toLowerCase().includes(s));
        }

        // 2. Category Filter
        if (roomCategory.value !== '全部') {
            const targetRegions = REGION_GROUPS[roomCategory.value] || [];
            rooms = rooms.filter(r => targetRegions.includes(r.name));
        }

        // 3. Smart Sorting (Activity First)
        return rooms.sort((a, b) => {
            // Priority 1: Has unread-like activity (last message time)
            const timeA = a.last_message_at ? new Date(a.last_message_at).getTime() : 0;
            const timeB = b.last_message_at ? new Date(b.last_message_at).getTime() : 0;
            
            // Only prioritize messages within the last 24 hours
            const oneDayAgo = Date.now() - 24 * 60 * 60 * 1000;
            const hasRecentA = timeA > oneDayAgo;
            const hasRecentB = timeB > oneDayAgo;

            if (hasRecentA && !hasRecentB) return -1;
            if (!hasRecentA && hasRecentB) return 1;
            if (hasRecentA && hasRecentB) return timeB - timeA;

            // Priority 2: Active user count
            if ((b.active_count || 0) !== (a.active_count || 0)) {
                return (b.active_count || 0) - (a.active_count || 0);
            }

            // Priority 3: Default sort order
            return (a.sort_order || 0) - (b.sort_order || 0);
        });
    });

    let statsTimer = null;
    let globalTimer = null;
    let heartbeatTimer = null;
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

    const fetchGlobalData = async () => {
        try {
            const response = await api.get('/instant/global-data');
            globalData.recent_messages = response.data.recent_messages;
            globalData.lfg_users = response.data.lfg_users;
            
            // Sync current user's LFG status if found in list
            if (isLoggedIn.value && currentUser.value) {
                const me = globalData.lfg_users.find(u => String(u.uid) === String(currentUser.value.uid));
                isLfg.value = !!me;
                if (me && me.remark) {
                    selectedLfgRemark.value = me.remark;
                }
            }
        } catch (error) {
            console.error('Failed to fetch global data', error);
        }
    };

    const toggleLfg = async (remark = null) => {
        if (!isLoggedIn.value) {
            showToast('請先登入後再發佈狀態', 'warning');
            return;
        }
        
        // If turning ON and no remark provided, we might want to handle it in UI
        const newStatus = !isLfg.value;
        const finalRemark = remark || selectedLfgRemark.value;

        try {
            const response = await api.post('/instant/toggle-lfg', { 
                status: newStatus,
                remark: newStatus ? finalRemark : null
            });
            if (response.data.status === 'success') {
                isLfg.value = newStatus;
                if (newStatus) {
                    selectedLfgRemark.value = finalRemark;
                    startHeartbeat();
                } else {
                    stopHeartbeat();
                }
                showToast(newStatus ? '已開啟「想打球」狀態！' : '已關閉「想打球」狀態', 'success');
                fetchGlobalData();
            }
        } catch (error) {
            showToast('操作失敗', 'error');
        }
    };


    const startHeartbeat = () => {
        stopHeartbeat();
        console.log('LFG Heartbeat: Started');
        heartbeatTimer = setInterval(async () => {
            if (isLfg.value && isLoggedIn.value) {
                try {
                    await api.post('/instant/toggle-lfg', { 
                        status: true,
                        remark: selectedLfgRemark.value
                    });
                    console.log('LFG Heartbeat: Pulsed');
                } catch (e) {
                    console.warn('LFG Heartbeat: Failed', e);
                }
            } else {
                stopHeartbeat();
            }
        }, 60000); // 1 minute
    };

    const stopHeartbeat = () => {
        if (heartbeatTimer) {
            clearInterval(heartbeatTimer);
            heartbeatTimer = null;
            console.log('LFG Heartbeat: Stopped');
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
                    
                    // Entry Notification
                    const notification = {
                        id: Date.now(),
                        type: 'join',
                        user: user,
                        text: `球友 ${user.name} 剛進入了網球大廳`
                    };
                    activityNotifications.value.push(notification);
                    setTimeout(() => {
                        activityNotifications.value = activityNotifications.value.filter(n => n.id !== notification.id);
                    }, 5000);
                })
                .leaving((user) => {
                    console.log('Lobby Leave:', user.name);
                    api.post('/instant/exit-room');
                    api.post('/instant/sync-global');
                });
        }

        // 3. Start Global Poller (Every 30s as heartbeat for activity feed)
        fetchGlobalData();
        globalTimer = setInterval(fetchGlobalData, 30000);
        
        if (isLfg.value) {
            startHeartbeat();
        }
    };

    const deactivatePresence = () => {
        stopHeartbeat();
        if (globalTimer) clearInterval(globalTimer);
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
        globalData, isLfg, selectedLfgRemark, roomSearch, roomCategory, sortedAndFilteredRooms, activityNotifications,
        fetchRooms, selectRoom, sendInstantMessage, fetchMessages, joinBySlug, fetchGlobalData, toggleLfg
    };
};
