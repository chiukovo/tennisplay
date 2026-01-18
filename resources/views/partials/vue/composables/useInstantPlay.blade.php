// --- useInstantPlay Composable ---
// 處理即時揪球、分區聊天室邏輯

const useInstantPlay = (isLoggedIn, currentUser, showToast, view) => {
    const instantRooms = ref([]);
    const currentRoom = ref(null);
    const instantMessages = ref([]);
    const isInstantLoading = ref(false);
    const globalInstantStats = reactive({ active_count: 0, display_count: 0, avatars: [] });
    const presenceUsers = ref([]);
    const instantMessageDraft = ref('');
    
    // Global Features
    const globalData = reactive({ recent_messages: [], lfg_users: [] });
    const isLfg = ref(false); // Local state for toggle
    const selectedLfgRemark = ref('');
    
    const isSending = ref(false);
    const activityNotifications = ref([]);
    const roomSearch = ref('');
    const roomCategory = ref('全部');
    const currentTickerIndex = ref(0);
    
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

    // Avatars display logic for scalability (Cap at 20)
    const sortedOtherAvatars = computed(() => {
        const users = presenceUsers.value.length > 0 ? presenceUsers.value : globalInstantStats.avatars;
        return users.filter(a => 
            !globalData.lfg_users.some(l => String(l.uid) === String(a.uid))
        );
    });

    const displayOtherAvatars = computed(() => {
        return sortedOtherAvatars.value.slice(0, 20);
    });

    const hiddenOthersCount = computed(() => {
        return Math.max(0, sortedOtherAvatars.value.length - 20);
    });

    let statsTimer = null;
    let globalTimer = null;
    let heartbeatTimer = null;
    let tickerTimer = null;
    let failsafeTimer = null;
    let lastEventTime = Date.now();
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
            
            // Reset ticker if data changed substantially
            if (currentTickerIndex.value >= globalData.recent_messages.length) {
                currentTickerIndex.value = 0;
            }
            
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
                // Everyone can trigger sync once on enter to be sure
                api.post(`/instant/rooms/${room.slug}/sync`);
            })
            .joining((user) => {
                console.log('User joined room:', user.name);
                if (currentRoom.value) {
                    currentRoom.value.active_count = (currentRoom.value.active_count || 0) + 1;
                }
                // Only the joiner already called sync via selectRoom? 
                // No, selectRoom doesn't know when joining is done.
                // Let's rely on the direct state update above.
            })
            .leaving((user) => {
                console.log('User left room:', user.name);
                if (currentRoom.value) {
                    currentRoom.value.active_count = Math.max(0, (currentRoom.value.active_count || 1) - 1);
                }
                // To consider performance (Avoid Thundering Herd):
                // We sync room cards via direct push broadcast from the server.
                // Since this user is leaving, one REMAINING user triggers the broadcast.
                // Simple thundering herd prevention: only the user with the smallest UID who is still here.
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

        try {
            const response = await api.post(`/instant/rooms/${currentRoom.value.slug}/messages`, {
                content: finalContent
            });
            const newMessage = response.data;
            instantMessages.value.push(newMessage);
            
            // 同步更新大廳卡片預覽 (確保回列表時立即看到)
            const room = instantRooms.value.find(r => r.slug === currentRoom.value.slug);
            if (room) {
                room.last_message = newMessage.content;
                room.last_message_by = newMessage.user.name;
                room.last_message_at = newMessage.created_at;
            }

            // 同步注入頂部看板
            if (newMessage.content) {
                const msgId = newMessage.id || Date.now();
                if (!globalData.recent_messages.some(m => m.id === msgId)) {
                    globalData.recent_messages.unshift({
                        id: msgId,
                        content: newMessage.content,
                        last_message_by: newMessage.user.name,
                        created_at: newMessage.created_at,
                        user: { name: newMessage.user.name, avatar: newMessage.user.avatar },
                        room: { slug: currentRoom.value.slug, name: currentRoom.value.name }
                    });
                    if (globalData.recent_messages.length > 15) globalData.recent_messages.pop();
                }
            }

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

        // 2. Listen for Events
        statsChannel
            .listen('.room.stats.updated', (payload) => {
                console.log('--- WebSocket Event: [room.stats.updated] ---', payload.room_slug);
                lastEventTime = Date.now();
                const room = instantRooms.value.find(r => r.slug === payload.room_slug);
                if (room) {
                    room.active_count = payload.active_count;
                    room.active_avatars = payload.active_avatars;
                    // Update message preview
                    room.last_message = payload.last_message;
                    room.last_message_by = payload.last_message_by;
                    room.last_message_at = payload.last_message_at;

                    // SYNC TICKER: If this is new information, inject it into the top ticker
                    if (payload.last_message) {
                        const msgId = payload.message_id || payload.id || Date.now();
                        const existingMsg = globalData.recent_messages.find(m => m.id === msgId);
                        if (!existingMsg) {
                            globalData.recent_messages.unshift({
                                id: msgId,
                                content: payload.last_message,
                                last_message_by: payload.last_message_by,
                                created_at: payload.last_message_at,
                                user: { name: payload.last_message_by, avatar: null },
                                room: { slug: payload.room_slug, name: room.name }
                            });
                            // Keep max 15 ticker items
                            if (globalData.recent_messages.length > 15) globalData.recent_messages.pop();
                        }
                    }
                }
            })
            .listen('.global.stats.updated', (payload) => {
                console.log('--- WebSocket Event: [global.stats.updated] ---');
                lastEventTime = Date.now();
                // Only update if not already managed by local presence
                if (presenceUsers.value.length === 0) {
                    globalInstantStats.active_count = payload.active_count;
                    globalInstantStats.display_count = payload.active_count;
                    globalInstantStats.avatars = payload.avatars;
                }
            })
            .listen('.stats.changed', (payload) => {
                console.log('--- WebSocket Event: [stats.changed (Pulse)] ---', payload);
                lastEventTime = Date.now();
                if (payload.type === 'global') fetchStats();
                if (payload.type === 'room' && payload.room_slug) fetchRooms();
            });

        // 3. Join PRESENCE channel for identity tracking (Members only)
        if (isLoggedIn.value) {
            console.log('Connecting to Presence Lobby...');
            lobbyChannel = window.Echo.join('instant-lobby')
                .here((users) => {
                    console.log('Presence Lobby [here]:', users.length);
                    lastEventTime = Date.now();
                    // Deduplicate and set local state
                    const unique = [];
                    const uids = new Set();
                    users.forEach(u => {
                        if (!u.uid || uids.has(u.uid)) return;
                        uids.add(u.uid);
                        unique.push(u);
                    });
                    presenceUsers.value = unique;
                    globalInstantStats.active_count = unique.length;
                    globalInstantStats.display_count = unique.length;
                })
                .joining((user) => {
                    console.log('Presence Lobby [joining]:', user.name);
                    lastEventTime = Date.now();
                    if (!presenceUsers.value.some(u => u.uid === user.uid)) {
                        presenceUsers.value.push(user);
                        globalInstantStats.active_count = presenceUsers.value.length;
                        globalInstantStats.display_count = presenceUsers.value.length;
                    }
                })
                .leaving((user) => {
                    console.log('Presence Lobby [leaving]:', user.name);
                    lastEventTime = Date.now();
                    presenceUsers.value = presenceUsers.value.filter(u => u.uid !== user.uid);
                    globalInstantStats.active_count = presenceUsers.value.length;
                    globalInstantStats.display_count = presenceUsers.value.length;
                });
        }

        // 4. Start Pollers
        fetchGlobalData();
        globalTimer = setInterval(fetchGlobalData, 30000);
        
        // FAILSAFE Heartbeat: If no events for 15s while in lobby, trigger one manual fetch
        failsafeTimer = setInterval(() => {
            const idleTime = Date.now() - lastEventTime;
            if (idleTime > 15000 && view.value === 'instant-play') {
                console.log('[Self-Healing] Idle too long, refreshing room data');
                fetchRooms();
                fetchStats();
                lastEventTime = Date.now(); // Reset
            }
        }, 5000);
        
        if (isLfg.value) {
            startHeartbeat();
        }
    };

    const deactivatePresence = () => {
        stopHeartbeat();
        if (globalTimer) clearInterval(globalTimer);
        if (failsafeTimer) clearInterval(failsafeTimer);
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

        // Global Ticker Timer
        tickerTimer = setInterval(() => {
            if (globalData.recent_messages.length > 1) {
                currentTickerIndex.value = (currentTickerIndex.value + 1) % globalData.recent_messages.length;
            }
        }, 5000); // Rotate every 5 seconds
    });

    onUnmounted(() => {
        deactivatePresence();
        if (tickerTimer) clearInterval(tickerTimer);
    });

    return {
        instantRooms, currentRoom, instantMessages, isInstantLoading, globalInstantStats, instantMessageDraft, isSending,
        globalData, isLfg, selectedLfgRemark, roomSearch, roomCategory, sortedAndFilteredRooms, activityNotifications,
        currentTickerIndex, displayOtherAvatars, hiddenOthersCount,
        fetchRooms, selectRoom, sendInstantMessage, fetchMessages, joinBySlug, fetchGlobalData, toggleLfg
    };
};
