<script>
@include('partials.vue.constants')
@include('partials.vue.components')
@include('partials.vue.composables.index')

window.vm = createApp({
    setup() {
        // --- 1. Basic State (Refs & Reactives) ---
        const isLoggedIn = ref(false);
        const currentUser = ref(null);
        const searchQuery = ref('');
        const searchDraft = ref('');
        const selectedRegion = ref('全部');
        const regionDraft = ref('全部');
        const currentPage = ref(1);
        const perPage = ref(12);
        const selectedGender = ref('全部');
        const genderDraft = ref('全部');
        const selectedLevelMin = ref('');
        const levelMinDraft = ref('');
        const selectedLevelMax = ref('');
        const levelMaxDraft = ref('');
        const selectedHanded = ref('全部');
        const handedDraft = ref('全部');
        const selectedBackhand = ref('全部');
        const backhandDraft = ref('全部');
        const sortBy = ref('newest');
        const showAdvancedFilters = ref(false);
        const coachSearchQuery = ref('');
        const coachSearchDraft = ref('');
        const coachSelectedRegion = ref('全部');
        const coachRegionDraft = ref('全部');
        const coachCurrentPage = ref(1);
        const coachSortBy = ref('newest');
        const coachPriceMin = ref('');
        const coachPriceMax = ref('');
        const coachPriceMinDraft = ref('');
        const coachPriceMaxDraft = ref('');
        const coachSelectedMethod = ref('全部');
        const coachMethodDraft = ref('全部');
        const coachSelectedTag = ref('');
        const coachTagDraft = ref('');
        const coachSelectedLocation = ref('');
        const coachLocationDraft = ref('');
        const showCoachFilters = ref(false);
        const showCoachForm = ref(false);
        const isSavingCoach = ref(false);
        const matchModal = reactive({ open: false, player: null, text: '' });
        const isSendingMatch = ref(false);
        const detailPlayer = ref(null);
        const eventMap = ref(null);
        const createEventMap = ref(null);
        let activeLeafletMap = null;
        let createLeafletMap = null;
        let createMapMarker = null;
        const featuredPlayersContainer = ref(null);  // 推薦戰友滾動容器
        const shareModal = reactive({ open: false, player: null });
        const isSigning = ref(false);
        const showNtrpGuide = ref(false);
        const eventFilter = ref('all');
        const eventRegionFilter = ref('all');
        const eventSearchQuery = ref('');
        const eventSearchDraft = ref('');
        const getTodayStr = () => {
            const now = new Date();
            return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
        };
        const eventDateShortcut = ref('month');
        const getMonthRange = () => {
            const now = new Date();
            const start = new Date(now.getFullYear(), now.getMonth(), 1);
            const end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
            const format = (d) => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
            return { start: format(start), end: format(end) };
        };
        const monthRange = getMonthRange();
        const eventStartDate = ref(monthRange.start);
        const eventEndDate = ref(monthRange.end);
        const eventCurrentPage = ref(1);
        const eventPerPage = ref(12);
        const showEventDetail = ref(false);
        const activeEvent = ref(null);
        const eventComments = reactive({});
        const eventCommentDraft = ref('');
        const messageTab = ref('inbox');
        const messagesLimit = ref(20);
        const selectedChatUser = ref(null);
        const showMessageDetail = ref(false);
        const showPrivacy = ref(false);
        const showLinePromo = ref(false);
        const showPreview = ref(false);
        const showQuickEditModal = ref(false);
        const profileActionMenu = reactive({ open: false });
        const navRefreshing = ref(false);
        const navRefreshView = ref('');
        const settingsForm = reactive({ 
            default_region: '全部',
            notify_line: true,
            notify_event: true
        });
        const form = reactive({
            id: null, name: '', region: '台中市', level: '3.5', handed: '右手', backhand: '雙反', gender: '男',
            intro: '', fee: '免費 (交流為主)', photo: null, signature: null, theme: 'standard',
            merged_photo: null, photoX: 0, photoY: 0, photoScale: 1, 
            sigX: 50, sigY: 50, sigScale: 1, sigRotate: 0, sigWidth: 100, sigHeight: 100
        });
        const coachForm = reactive({
            player_id: null,
            is_coach: true,
            coach_price_min: '',
            coach_price_max: '',
            coach_price_note: '',
            coach_methods: [],
            coach_locations: '',
            coach_tags: '',
            coach_certs: '',
            coach_experience_years: '',
            coach_certifications: '',
            coach_languages: '',
            coach_availability: '',
            coach_teaching_url: ''
        });
        const eventForm = reactive({
            title: '', region: '', event_date: '', end_date: '', location: '', address: '',
            fee: 0, max_participants: 0, match_type: 'all', gender: 'all', level_min: '', level_max: '', notes: ''
        });

        // --- 2. Helper Functions (Must be defined before composables that use them) ---
        const { 
            toasts, showToast, removeToast, confirmDialog, showConfirm, hideConfirm, executeConfirm, 
            formatDate, getUrl, formatLocalDateTime, formatEventDate, compressImage
        } = useUtils();

        const leafletLoader = { promise: null };
        const loadLeaflet = () => {
            if (window.L) return Promise.resolve();
            if (leafletLoader.promise) return leafletLoader.promise;
            leafletLoader.promise = new Promise((resolve, reject) => {
                if (!document.getElementById('leaflet-css')) {
                    const link = document.createElement('link');
                    link.id = 'leaflet-css';
                    link.rel = 'stylesheet';
                    link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                    link.crossOrigin = '';
                    document.head.appendChild(link);
                }
                if (!document.getElementById('leaflet-js')) {
                    const script = document.createElement('script');
                    script.id = 'leaflet-js';
                    script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                    script.crossOrigin = '';
                    script.onload = () => resolve();
                    script.onerror = () => reject(new Error('Leaflet load failed'));
                    document.body.appendChild(script);
                } else {
                    resolve();
                }
            });
            return leafletLoader.promise;
        };

        const initSettings = () => {
            if (currentUser.value && currentUser.value.settings) {
                const s = currentUser.value.settings;
                settingsForm.default_region = s.default_region || '全部';
                settingsForm.notify_line = s.notify_line !== undefined ? s.notify_line : true;
                settingsForm.notify_event = s.notify_event !== undefined ? s.notify_event : true;
            }
        };

        const applyDefaultFilters = (viewName) => {
            const defRegion = settingsForm.default_region;
            if (!defRegion || defRegion === '全部') return;
            if ((viewName === 'list' || viewName === 'home') && selectedRegion.value === '全部') {
                selectedRegion.value = defRegion;
                regionDraft.value = defRegion;
            }
            if (viewName === 'coaches' && coachSelectedRegion.value === '全部') {
                coachSelectedRegion.value = defRegion;
                coachRegionDraft.value = defRegion;
            }
        };

        const resetForm = () => {
            const user = currentUser.value;
            const defRegion = (user?.region && user.region !== '全部') 
                ? user.region 
                : ((settingsForm.default_region && settingsForm.default_region !== '全部') ? settingsForm.default_region : '台北市');

            Object.assign(form, {
                id: null, name: user?.name || '', region: defRegion, level: '3.5', handed: '右手', backhand: '雙反', gender: user?.gender || '男',
                intro: '', fee: '免費 (交流為主)', photo: null, signature: null, theme: 'standard', merged_photo: null,
                photoX: 0, photoY: 0, photoScale: 1, sigX: 50, sigY: 50, sigScale: 1, sigRotate: 0, sigWidth: 100, sigHeight: 100
            });
        };

        const triggerNavRefresh = (viewName, action) => {
            navRefreshView.value = viewName;
            navRefreshing.value = true;
            return Promise.resolve()
                .then(() => action && action())
                .finally(() => {
                    setTimeout(() => {
                        if (navRefreshView.value === viewName) navRefreshing.value = false;
                    }, 350);
                });
        };

        const resetEventForm = () => {
            const now = new Date();
            const startTime = new Date(now.getTime() + 60 * 60 * 1000);
            const endTime = new Date(now.getTime() + 3 * 60 * 60 * 1000);
            const start = formatLocalDateTime(startTime);
            const end = formatLocalDateTime(endTime);
            const defRegion = (settingsForm.default_region && settingsForm.default_region !== '全部') ? settingsForm.default_region : '';
            
            Object.assign(eventForm, {
                title: '', region: defRegion, event_date: start, end_date: end, location: '', address: '',
                fee: 0, max_participants: 0, match_type: 'all', gender: 'all', level_min: '', level_max: '', notes: '',
                latitude: null, longitude: null
            });

            if (createLeafletMap) {
                createLeafletMap.remove();
                createLeafletMap = null;
                createMapMarker = null;
            }
        };

        // --- 3. Initialize Composables ---
        const { view, lastNavigationTap, navigateTo, parseRoute, goBack } = useNavigation(
            { '/': 'home', '/list': 'list', '/coaches': 'coaches', '/create': 'create', '/messages': 'messages', '/auth': 'auth', '/profile': 'profile', '/events': 'events', '/create-event': 'create-event', '/settings': 'settings', '/privacy': 'privacy', '/sitemap': 'sitemap', '/instant-play': 'instant-play' },
            { 'home': '/', 'list': '/list', 'coaches': '/coaches', 'create': '/create', 'messages': '/messages', 'auth': '/auth', 'profile': '/profile', 'events': '/events', 'create-event': '/create-event', 'settings': '/settings', 'privacy': '/privacy', 'sitemap': '/sitemap', 'instant-play': '/instant-play' },
            { 
                'home': 'LoveTennis | 全台最專業的網球約打媒合與球友卡社群', 'list': '找球友 | 發現您的最佳網球夥伴', 'coaches': '找教練 | 專業網球教練媒合', 'create': '建立球友卡 | 展現您的網球風格', 'messages': '我的訊息 | 網球約打邀請管理', 'events': '揪球開團 | 搜尋全台網球場次', 'create-event': '發佈揪球 | 建立新的網球場次', 'auth': '登入/註冊 | 加入 LoveTennis 社群', 'profile': '個人主頁 | LoveTennis', 'settings': '帳號設置 | 個性化您的網球體驗', 'privacy': '隱私權政策 | LoveTennis', 'sitemap': '網站地圖 | LoveTennis', 'instant-play': '現在想打 | 即時揪球聊天室'
            },
            showToast,
            (viewName) => applyDefaultFilters(viewName),
            isLoggedIn,
            currentUser,
            eventDateShortcut,
            eventStartDate,
            eventEndDate
        );

        const { 
            isLoginMode, showUserMenu, isSavingSettings, isAuthLoading, authError,
            checkAuth, logout, saveSettings, loginWithLine
        } = useAuth(showToast, (v, s, i) => navigateTo(v, s, i), () => initSettings(), isLoggedIn, currentUser, settingsForm, view);

        const { 
            profileData, isProfileLoading, profileTab, profileEvents, profileEventsHasMore, isEditingProfile, profileForm, 
            profileComments, followingUsers, followerUsers, likedPlayers, playerCommentDraft, playerCommentRating,
            selectedProfileRegions, toggleProfileRegion, reportModal, isReporting, isBlocking,
            loadProfile, loadProfileEvents, saveProfile, openProfile, toggleFollow, toggleLike,
            loadProfileComments, loadFollowing, loadFollowers, loadLikedPlayers, submitPlayerComment, deletePlayerComment, getRatingPercentage,
            openReportModal, submitReport, toggleBlock
        } = useProfile(isLoggedIn, currentUser, showToast, navigateTo);

        const { 
            players, myPlayers, randomPlayers, isPlayersLoading, isSubmitting, playersPagination, loadPlayers, loadRandomPlayers, loadMyCards, saveCard, deleteCard, clearPlayersCache
        } = usePlayers(isLoggedIn, currentUser, showToast, navigateTo, showConfirm, (id) => loadProfile(id), form);

        const { 
            events, eventsLoading, eventSubmitting, eventsPagination, loadEvents, createEvent, updateEvent, deleteEvent, joinEvent: baseJoinEvent, leaveEvent: baseLeaveEvent 
        } = useEvents(isLoggedIn, showToast, navigateTo, formatLocalDateTime, eventForm, resetEventForm);

        const { messages, loadMessages, markMessageRead } = useMessages(isLoggedIn, currentUser, showToast);

        const { 
            currentStep, stepAttempted, isAdjustingPhoto, isAdjustingSig, isCapturing, 
            isPhotoAdjustLoading, isSigAdjustLoading,
            canProceedStep1, canProceedStep2, canProceedStep3, canGoToStep, tryNextStep, tryGoToStep 
        } = useCardCreation(form, showToast);

        const { dragInfo, startDrag, handleDrag, stopDrag } = useDrag(form);

        const { isCapturing: isCapturingImage, captureCardImage } = useCapture(showToast);
        const showLfgPicker = ref(false);
        const customLfgRemark = ref('');

        const { 
            instantRooms, currentRoom, instantMessages, isInstantLoading, globalInstantStats, instantMessageDraft, isSending,
            globalData, isLfg, selectedLfgRemark, roomSearch, roomCategory, sortedAndFilteredRooms, activityNotifications, currentTickerIndex, displayOtherAvatars, hiddenOthersCount,
            fetchRooms, selectRoom, sendInstantMessage, fetchMessages, joinBySlug, fetchGlobalData, toggleLfg,
            enterSingleRoom, singleRoomMode, userRegion, timeLabel, quickTemplates, quickRegions, selectedQuickRegion
        } = useInstantPlay(isLoggedIn, currentUser, showToast, view);

        const initialRouteIsCoaches = window.location.pathname.startsWith('/coaches');
        const initialRouteResolved = ref(false);

        // Full reset including steps (now that currentStep is available)
        const resetFormFull = () => {
            resetForm();
            currentStep.value = 1;
            Object.keys(stepAttempted).forEach(k => delete stepAttempted[k]);
            isAdjustingPhoto.value = false;
            isAdjustingSig.value = false;
        };

        // Wrapped navigateTo that handles profile loading
        const navigateToWithProfile = (viewName, shouldReset = true, uid = null) => {
            // 同一頁重複點擊時，直接重新載入資料
            if (viewName === view.value) {
                if (viewName === 'home' || viewName === 'list') {
                    triggerNavRefresh(viewName, () => loadPlayers({ search: searchQuery.value, region: selectedRegion.value, page: currentPage.value, sort: sortBy.value }));
                } else if (viewName === 'coaches') {
                    triggerNavRefresh(viewName, () => loadPlayers({ 
                        is_coach: true,
                        search: coachSearchQuery.value,
                        region: coachSelectedRegion.value,
                        coach_price_min: coachPriceMin.value,
                        coach_price_max: coachPriceMax.value,
                        coach_method: coachSelectedMethod.value,
                        coach_tag: coachSelectedTag.value,
                        coach_location: coachSelectedLocation.value,
                        page: coachCurrentPage.value,
                        sort: coachSortBy.value
                    }));
                } else if (viewName === 'events' || viewName === 'create-event') {
                    triggerNavRefresh('events', () => loadEvents({
                        search: eventSearchQuery.value,
                        region: eventRegionFilter.value,
                        match_type: eventFilter.value,
                        start_date: eventStartDate.value,
                        end_date: eventEndDate.value,
                        page: eventCurrentPage.value
                    }));
                } else if (viewName === 'messages') {
                    triggerNavRefresh(viewName, () => loadMessages());
                } else if (viewName === 'profile') {
                    const targetUid = uid || profileData.value?.user?.uid || profileData.value?.user?.id;
                    if (targetUid) triggerNavRefresh(viewName, () => loadProfile(targetUid, loadProfileEvents, false));
                }
                return;
            }
            // Unauthenticated checks for restricted views
            if (!isLoggedIn.value) {
                if (viewName === 'create') {
                    showConfirm({
                        title: '請先登入',
                        message: '登入後即可製作專屬球友卡，開啟您的網球社群之旅！',
                        confirmText: '去登入',
                        onConfirm: () => navigateTo('auth')
                    });
                    return;
                }
                if (viewName === 'profile' && !uid) {
                    showConfirm({
                        title: '請先登入',
                        message: '登入後即可查看您的個人檔案與活動紀錄。',
                        confirmText: '去登入',
                        onConfirm: () => navigateTo('auth')
                    });
                    return;
                }
            }

            // Check if trying to create card without basic info
            if (viewName === 'create' && isLoggedIn.value) {
                if (!currentUser.value?.gender || !currentUser.value?.region) {
                    showToast('請先完成預設資料（性別、地區）再製作球友卡', 'warning');
                    const userUid = currentUser.value?.uid || currentUser.value?.id;
                    loadProfile(userUid, loadProfileEvents, true); // Auto open edit mode
                    navigateTo('profile', false, userUid, resetFormFull, resetEventForm, loadProfile);
                    return;
                }
                
                // 如果用戶已有球友卡，自動進入編輯模式
                if (myPlayers.value && myPlayers.value.length > 0) {
                    editCard(myPlayers.value[0]); // 編輯第一張球友卡
                    return;
                }
            }
            
            // For profile navigation, load the profile data
            if (viewName === 'profile' && uid) {
                window.scrollTo({ top: 0, behavior: 'smooth' });
                // If we are navigating to our own profile and it's incomplete, auto-edit
                const isMe = uid === currentUser.value?.uid || String(uid) === String(currentUser.value?.id);
                const isIncomplete = isMe && (!currentUser.value?.gender || !currentUser.value?.region);
                loadProfile(uid, loadProfileEvents, isIncomplete);
            }
            
            if (viewName === 'list' && shouldReset) {
                searchDraft.value = '';
                searchQuery.value = '';
                regionDraft.value = '全部';
                selectedRegion.value = '全部';
                genderDraft.value = '全部';
                selectedGender.value = '全部';
                levelMinDraft.value = '';
                selectedLevelMin.value = '';
                levelMaxDraft.value = '';
                selectedLevelMax.value = '';
                handedDraft.value = '全部';
                selectedHanded.value = '全部';
                backhandDraft.value = '全部';
                selectedBackhand.value = '全部';
                activeQuickLevel.value = 'all';
            }

            if (viewName === 'create-event' && !uid) {
                initCreateMap();
            }

            navigateTo(viewName, shouldReset, uid, resetFormFull, resetEventForm, loadProfile);
        };

        // --- 4. Methods & Logic ---
        const editCard = (card) => {
            resetForm();
            Object.assign(form, {
                id: card.id, name: card.name, region: card.region, level: card.level, gender: card.gender,
                handed: card.handed, backhand: card.backhand, intro: card.intro, fee: card.fee,
                photo: card.photo_url || card.photo, signature: card.signature_url || card.signature,
                merged_photo: null, theme: card.theme || 'standard', photoX: card.photo_x || 0, photoY: card.photo_y || 0,
                photoScale: card.photo_scale || 1, sigX: card.sig_x ?? 50, sigY: card.sig_y ?? 50,
                sigScale: card.sig_scale || 1, sigRotate: card.sig_rotate || 0, sigWidth: card.sig_width || 100, sigHeight: card.sig_height || 100,
            });
            currentStep.value = 1;
            navigateTo('create', false);
        };

        const showDetail = (player) => {
            detailPlayer.value = player;
            playerCommentDraft.value = '';
            playerCommentRating.value = 0;
        };

        const handlePlayerUpdate = (updatedPlayer) => {
            if (!updatedPlayer || !updatedPlayer.id) return;
            
            if (detailPlayer.value) {
                if (detailPlayer.value.id === updatedPlayer.id) {
                    detailPlayer.value = { ...detailPlayer.value, ...updatedPlayer };
                } else {
                    detailPlayer.value = updatedPlayer;
                }
            }
            
            const pIdx = players.value.findIndex(p => p.id === updatedPlayer.id);
            if (pIdx !== -1) {
                players.value[pIdx] = { ...players.value[pIdx], ...updatedPlayer };
            }
            
            const mIdx = myPlayers.value.findIndex(p => p.id === updatedPlayer.id);
            if (mIdx !== -1) {
                myPlayers.value[mIdx] = { ...myPlayers.value[mIdx], ...updatedPlayer };
            }
            
            if (profileData.player && profileData.player.id === updatedPlayer.id) {
                profileData.player = { ...profileData.player, ...updatedPlayer };
            }

            const lIdx = likedPlayers.value.findIndex(p => p.id === updatedPlayer.id);
            if (lIdx !== -1) {
                likedPlayers.value[lIdx] = { ...likedPlayers.value[lIdx], ...updatedPlayer };
            }
            const flIdx = followingUsers.value.findIndex(p => p.id === updatedPlayer.id);
            if (flIdx !== -1) {
                followingUsers.value[flIdx] = { ...followingUsers.value[flIdx], ...updatedPlayer };
            }
            const frIdx = followerUsers.value.findIndex(p => p.id === updatedPlayer.id);
            if (frIdx !== -1) {
                followerUsers.value[frIdx] = { ...followerUsers.value[frIdx], ...updatedPlayer };
            }

            events.value.forEach(event => {
                if (event.player && event.player.id === updatedPlayer.id) {
                    event.player = { ...event.player, ...updatedPlayer };
                }
                if (event.confirmedParticipants) {
                    event.confirmedParticipants.forEach(cp => {
                        if (cp.player && cp.player.id === updatedPlayer.id) {
                            cp.player = { ...cp.player, ...updatedPlayer };
                        }
                    });
                }
            });
            
            if (activeEvent.value) {
                if (activeEvent.value.player && activeEvent.value.player.id === updatedPlayer.id) {
                    activeEvent.value.player = { ...activeEvent.value.player, ...updatedPlayer };
                }
                if (activeEvent.value.confirmedParticipants) {
                    activeEvent.value.confirmedParticipants.forEach(cp => {
                        if (cp.player && cp.player.id === updatedPlayer.id) {
                            cp.player = { ...cp.player, ...updatedPlayer };
                        }
                    });
                }
            }
        };

        const togglePlayerLike = async (player) => {
            if (!player || !player.id) return;
            if (!isLoggedIn.value) { showToast('請先登入', 'error'); navigateTo('auth'); return; }
            if (!togglePlayerLike._busy) togglePlayerLike._busy = new Set();
            if (togglePlayerLike._busy.has(player.id)) return;
            togglePlayerLike._busy.add(player.id);
            try {
                const endpoint = player.is_liked ? `/unlike/${player.id}` : `/like/${player.id}`;
                const response = await api.post(endpoint);
                player.is_liked = !player.is_liked;
                if (response?.data?.likes_count !== undefined) {
                    player.likes_count = response.data.likes_count;
                }
                handlePlayerUpdate(player);
            } catch (error) {
                const msg = error.response?.data?.error || error.response?.data?.message || '操作失敗';
                showToast(msg, 'error');
            } finally {
                togglePlayerLike._busy.delete(player.id);
            }
        };

        // Get stats for player detail modal
        const getDetailStats = (player) => {
            if (!player) return { likes: 0, matches: 0 };
            return { likes: player.likes_count || 0, matches: player.matches_count || 0 };
        };

        // Message sent callback
        const onMessageSent = (data) => {
            loadMessages();
            // Only close modal if it's the initial match request (no data or type != chat-reply)
            if (!data || data.type !== 'chat-reply') {
                showMessageDetail.value = false;
            }
        };

        // Loading state
        const isLoading = ref(false);

        // Level descriptions alias for template
        const levelDescs = LEVEL_DESCS;
        const levels = LEVELS;
        const regions = REGIONS;
        const coachMethods = ['個人', '團體'];

        // Features for home page
        const features = [
            { icon: 'card', title: '個人球友卡', desc: '建立專屬的網球名片，展現您的球技與風格' },
            { icon: 'search', title: '智能配對', desc: '根據程度、地區、時間媒合最適合的球友' },
            { icon: 'message', title: '即時約打', desc: '一鍵發送約打邀請，輕鬆安排練球時間' },
            { icon: 'users', title: '揪球開團', desc: '發起球聚活動，認識更多志同道合的球友' }
        ];

        const cardThemes = {
            standard: { label: '經典藍' },
            gold: { label: '尊爵金' },
            platinum: { label: '白金版' },
            holographic: { label: '幻彩版' },
            onyx: { label: '黑曜石' },
            sakura: { label: '櫻花粉' }
        };

        // --- 5. Computed Properties ---
        const activeRegions = computed(() => REGIONS);
        
        const activeEventRegions = computed(() => REGIONS);

        const filteredPlayers = computed(() => players.value);
        const totalPages = computed(() => playersPagination.value.last_page);
        const paginatedPlayers = computed(() => players.value);

        const coachTotalPages = computed(() => playersPagination.value.last_page);
        const coachPaginatedPlayers = computed(() => players.value.filter(p => p && p.is_coach));

        const coachDisplayPages = computed(() => {
            const total = coachTotalPages.value;
            if (total <= 5) return Array.from({ length: total }, (_, i) => i + 1);
            const current = playersPagination.value.current_page;
            if (current <= 3) return [1, 2, 3, '...', total];
            if (current >= total - 2) return [1, '...', total - 2, total - 1, total];
            return [1, '...', current, '...', total];
        });

        const displayPages = computed(() => {
            const total = totalPages.value;
            if (total <= 5) return Array.from({ length: total }, (_, i) => i + 1);
            const current = playersPagination.value.current_page;
            
            // 更精簡的顯示邏輯 (最多 5 個區塊)
            if (current <= 3) return [1, 2, 3, '...', total];
            if (current >= total - 2) return [1, '...', total - 2, total - 1, total];
            return [1, '...', current, '...', total];
        });

        const filteredEvents = computed(() => events.value);
        const eventTotalPages = computed(() => eventsPagination.value.last_page);
        const paginatedEvents = computed(() => events.value);

        const eventDisplayPages = computed(() => {
            const total = eventTotalPages.value;
            if (total <= 5) return Array.from({ length: total }, (_, i) => i + 1);
            const current = eventsPagination.value.current_page;
            
            // 更精簡的顯示邏輯 (最多 5 個區塊)
            if (current <= 3) return [1, 2, 3, '...', total];
            if (current >= total - 2) return [1, '...', total - 2, total - 1, total];
            return [1, '...', current, '...', total];
        });

        const hasUnread = computed(() => Array.isArray(messages.value) && messages.value.some(m => m.unread_count > 0));
        const hasPlayerCard = computed(() => myPlayers.value && myPlayers.value.length > 0);
        const myCards = computed(() => myPlayers.value);

        const allConversations = computed(() => {
            if (!Array.isArray(messages.value) || !currentUser.value) return [];
            return messages.value;
        });

        const paginatedMessages = computed(() => {
            return allConversations.value.slice(0, messagesLimit.value);
        });

        const hasMoreMessages = computed(() => {
            return allConversations.value.length > messagesLimit.value;
        });

        // --- 6. Event Handlers & API wrappers ---
        const handleFileUpload = (e) => {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = async (event) => {
                const base64 = event.target.result;
                // 壓縮圖片
                form.photo = await compressImage(base64);
                form.photoX = 0; form.photoY = 0; form.photoScale = 1;
                isAdjustingPhoto.value = true;
            };
            reader.readAsDataURL(file);
        };

        const triggerUpload = async () => {
            if (typeof window.takeAppPhoto === 'function') {
                const dataUrl = await window.takeAppPhoto();
                if (dataUrl) {
                    form.photo = await compressImage(dataUrl);
                    form.photoX = 0; form.photoY = 0; form.photoScale = 1;
                    isAdjustingPhoto.value = true;
                }
            } else {
                const el = document.getElementById('photo-upload');
                if (el) el.click();
            }
        };

        const useLinePhoto = async () => {
            if (!currentUser.value?.line_picture_url) return;
            const url = currentUser.value.line_picture_url;
            try {
                const response = await fetch(url);
                const blob = await response.blob();
                const reader = new FileReader();
                reader.onload = async (e) => {
                    const base64 = e.target.result;
                    // 壓縮圖片
                    form.photo = await compressImage(base64);
                    form.photoX = 0; form.photoY = 0; form.photoScale = 1;
                    isAdjustingPhoto.value = true;
                };
                reader.readAsDataURL(blob);
            } catch (error) {
                form.photo = url;
                isAdjustingPhoto.value = true;
                showToast('無法直接匯入圖片，已使用連結代替', 'warning');
            }
        };

        const handleSignatureUpdate = (sigData) => {
            if (!sigData) { form.signature = null; return; }
            form.signature = sigData.dataUrl;
            form.sigWidth = sigData.widthPct;
            form.sigHeight = sigData.heightPct;
            form.sigX = sigData.xPct;
            form.sigY = sigData.yPct;
            isSigning.value = false;

            isAdjustingSig.value = true;
            nextTick(() => {
                const target = document.querySelector('#target-signature');
                if (target) initMoveable(target);
            });
        };

        const moveableInstance = ref(null);
        let isInitializingMoveable = false;

        const toggleAdjustSig = () => {
            isAdjustingSig.value = !isAdjustingSig.value;
            if (isAdjustingSig.value) {
                isSigning.value = false; // 強制關閉簽名板防止衝突
                // Wait for DOM update then initialize Moveable
                nextTick(() => {
                    setTimeout(() => {
                        const target = document.querySelector('#target-signature');
                        if (target) initMoveable(target);
                    }, 50);
                });
            } else {
                if (moveableInstance.value) {
                    moveableInstance.value.destroy();
                    moveableInstance.value = null;
                }
                // Clean up any orphaned Moveable elements
                document.querySelectorAll('.moveable-control-box').forEach(el => el.remove());
                isInitializingMoveable = false;
            }
        };

        // 完成照片調整（帶 loading 效果）
        const finishPhotoAdjust = () => {
            isPhotoAdjustLoading.value = true;
            setTimeout(() => {
                isAdjustingPhoto.value = false;
                isPhotoAdjustLoading.value = false;
            }, 500);
        };

        // 完成簽名調整（帶 loading 效果）
        const finishSigAdjust = () => {
            isSigAdjustLoading.value = true;
            setTimeout(() => {
                // Destroy Moveable instance first
                if (moveableInstance.value) {
                    moveableInstance.value.destroy();
                    moveableInstance.value = null;
                }
                // Also remove any orphaned Moveable elements from DOM
                document.querySelectorAll('.moveable-control-box').forEach(el => el.remove());
                
                isInitializingMoveable = false;
                isAdjustingSig.value = false;
                isSigAdjustLoading.value = false;
            }, 500);
        };
        
        const initMoveable = (target) => {
            if (!isAdjustingSig.value || !target) return;
            
            // Prevent concurrent initializations
            if (isInitializingMoveable) return;
            
            // If already initialized for this target, skip
            if (moveableInstance.value) return;
            
            isInitializingMoveable = true;
            
            // Also clean up any orphaned Moveable elements
            document.querySelectorAll('.moveable-control-box').forEach(el => el.remove());

            // Wait for Vue and browser to finish rendering before initializing Moveable
            nextTick(() => {
                setTimeout(() => {
                    if (!isAdjustingSig.value || !target) return;
                    
                    moveableInstance.value = new Moveable(document.body, {
                        target: target,
                        draggable: true,
                        resizable: false,
                        scalable: true,
                        rotatable: true,
                        warpable: false,
                        keepRatio: true,
                        snappable: true,
                        renderDirections: ["nw", "ne", "sw", "se"],
                        zoom: 1,
                        origin: false,
                    });

                    moveableInstance.value
                        .on("drag", ({ target, left, top }) => {
                            const parent = target.parentElement;
                            if (!parent) return;
                            form.sigX = (left / parent.offsetWidth) * 100;
                            form.sigY = (top / parent.offsetHeight) * 100;
                        })
                        .on("scale", ({ target, scale }) => {
                            form.sigScale = scale[0];
                        })
                        .on("rotate", ({ target, rotate }) => {
                            form.sigRotate = rotate;
                        });
                    
                    isInitializingMoveable = false;
                }, 100);
            });
        };

        const openShare = (player) => {
            if (typeof window.appShare === 'function') {
                const uid = player.user_uid || player.user?.uid || player.user_id;
                const shareUrl = `${window.location.origin}/profile/${uid}`;
                window.appShare({
                    title: `LoveTennis 球友 - ${player.name}`,
                    text: `🎾 這是 ${player.name} 的網球個人資料，快來跟我約打吧！`,
                    url: shareUrl
                });
            } else {
                shareModal.player = player;
                shareModal.open = true;
            }
        };

        const handleSaveCard = async () => {
            await saveCard(resetFormFull);
            showQuickEditModal.value = false;
        };

        const handleSearch = () => {
            searchQuery.value = searchDraft.value;
            selectedRegion.value = regionDraft.value;
            selectedGender.value = genderDraft.value;
            selectedLevelMin.value = levelMinDraft.value;
            selectedLevelMax.value = levelMaxDraft.value;
            selectedHanded.value = handedDraft.value;
            selectedBackhand.value = backhandDraft.value;
            
            currentPage.value = 1;
            loadPlayers({ 
                search: searchQuery.value, 
                region: selectedRegion.value, 
                gender: selectedGender.value,
                level_min: selectedLevelMin.value,
                level_max: selectedLevelMax.value,
                handed: selectedHanded.value,
                backhand: selectedBackhand.value,
                page: 1 
            });
            showAdvancedFilters.value = false;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        };

        const handleCoachSearch = () => {
            coachSearchQuery.value = coachSearchDraft.value;
            coachSelectedRegion.value = coachRegionDraft.value;
            coachPriceMin.value = coachPriceMinDraft.value;
            coachPriceMax.value = '';
            coachSelectedMethod.value = coachMethodDraft.value;
            coachSelectedTag.value = coachTagDraft.value;
            coachSelectedLocation.value = coachLocationDraft.value;

            coachCurrentPage.value = 1;
            loadPlayers({ 
                is_coach: true,
                search: coachSearchQuery.value,
                region: coachSelectedRegion.value,
                coach_price_min: coachPriceMin.value,
                coach_price_max: '',
                coach_method: coachSelectedMethod.value,
                coach_tag: coachSelectedTag.value,
                coach_location: coachSelectedLocation.value,
                page: 1,
                sort: coachSortBy.value
            });
            showCoachFilters.value = false;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        };

        const resetCoachForm = () => {
            Object.assign(coachForm, {
                player_id: null,
                is_coach: true,
                coach_price_min: '',
                coach_price_max: '',
                coach_price_note: '',
                coach_methods: [],
                coach_locations: '',
                coach_tags: '',
                coach_certs: '',
                coach_experience_years: '',
                coach_certifications: '',
                coach_languages: '',
                coach_availability: '',
                coach_teaching_url: '',
                coach_venue: ''
            });
        };

        const openCoachForm = async () => {
            if (!isLoggedIn.value) {
                showToast('請先登入', 'error');
                navigateTo('auth');
                return;
            }
            if (!myPlayers.value.length) {
                await loadMyCards();
            }
            const target = myPlayers.value?.[0];
            if (!target) {
                showToast('請先建立球友卡', 'error');
                navigateTo('create');
                return;
            }
            Object.assign(coachForm, {
                player_id: target.id,
                is_coach: true,
                coach_price_min: target.coach_price_min ?? '',
                coach_price_max: target.coach_price_max ?? '',
                coach_price_note: target.coach_price_note ?? '',
                coach_methods: (target.coach_methods || '').split(',').filter(x => x),
                coach_locations: target.coach_locations ?? '',
                coach_tags: target.coach_tags ?? '',
                coach_certs: target.coach_certs ?? '',
                coach_experience_years: target.coach_experience_years ?? '',
                coach_certifications: target.coach_certifications ?? '',
                coach_languages: target.coach_languages ?? '',
                coach_availability: target.coach_availability ?? '',
                coach_teaching_url: target.coach_teaching_url ?? '',
                coach_venue: target.coach_venue ?? ''
            });
            showCoachForm.value = true;
        };

        const closeCoachForm = () => {
            showCoachForm.value = false;
            resetCoachForm();
        };

        const saveCoachProfile = async () => {
            if (!coachForm.player_id || isSavingCoach.value) return;
            const minPrice = coachForm.coach_price_min ? Number(coachForm.coach_price_min) : null;
            const experienceYears = coachForm.coach_experience_years !== '' && coachForm.coach_experience_years !== null
                ? Number(coachForm.coach_experience_years)
                : null;
            isSavingCoach.value = true;
            try {
                const payload = {
                    is_coach: true,
                    coach_price_min: minPrice,
                    coach_price_max: null,
                    coach_price_note: coachForm.coach_price_note || null,
                    coach_methods: coachForm.coach_methods.join(','),
                    coach_locations: coachForm.coach_locations || null,
                    coach_tags: coachForm.coach_tags || null,
                    coach_certs: coachForm.coach_certs || null,
                    coach_experience_years: experienceYears,
                    coach_certifications: coachForm.coach_certifications || null,
                    coach_languages: coachForm.coach_languages || null,
                    coach_availability: coachForm.coach_availability || null,
                    coach_teaching_url: coachForm.coach_teaching_url || null,
                    coach_venue: coachForm.coach_venue || null,
                };
                const response = await api.put(`/players/${coachForm.player_id}`, payload);
                if (response.data.success) {
                    clearPlayersCache();
                    await loadPlayers({
                        is_coach: true,
                        search: coachSearchQuery.value,
                        region: coachSelectedRegion.value,
                        coach_price_min: coachPriceMin.value,
                        coach_price_max: coachPriceMax.value,
                        coach_method: coachSelectedMethod.value,
                        coach_tag: coachSelectedTag.value,
                        coach_location: coachSelectedLocation.value,
                        page: coachCurrentPage.value,
                        sort: coachSortBy.value
                    }, true);
                    await loadMyCards();
                    showToast('教練資料已更新', 'success');
                    showCoachForm.value = false;
                    resetCoachForm();
                }
            } catch (error) {
                const msg = error.response?.data?.message || '更新失敗';
                showToast(msg, 'error');
            } finally {
                isSavingCoach.value = false;
            }
        };

        const cancelCoachProfile = async () => {
            if (!coachForm.player_id || isSavingCoach.value) return;
            showConfirm({
                title: '取消教練身份',
                message: '確定要取消教練身份嗎？取消後將不會出現在教練列表中。',
                confirmText: '確認取消',
                onConfirm: async () => {
                    isSavingCoach.value = true;
                    try {
                        const payload = {
                            is_coach: false,
                            coach_price_min: null,
                            coach_price_max: null,
                            coach_price_note: null,
                            coach_methods: null,
                            coach_locations: null,
                            coach_tags: null,
                            coach_certs: null,
                            coach_experience_years: null,
                            coach_certifications: null,
                            coach_languages: null,
                            coach_availability: null,
                            coach_teaching_url: null,
                            coach_venue: null,
                        };
                        const response = await api.put(`/players/${coachForm.player_id}`, payload);
                        if (response.data.success) {
                            clearPlayersCache();
                            await loadPlayers({
                                is_coach: true,
                                search: coachSearchQuery.value,
                                region: coachSelectedRegion.value,
                                coach_price_min: coachPriceMin.value,
                                coach_price_max: coachPriceMax.value,
                                coach_method: coachSelectedMethod.value,
                                coach_tag: coachSelectedTag.value,
                                coach_location: coachSelectedLocation.value,
                                page: coachCurrentPage.value,
                                sort: coachSortBy.value
                            }, true);
                            await loadMyCards();
                            showToast('已取消教練身份', 'success');
                            showCoachForm.value = false;
                            resetCoachForm();
                        }
                    } catch (error) {
                        const msg = error.response?.data?.message || '取消失敗';
                        showToast(msg, 'error');
                    } finally {
                        isSavingCoach.value = false;
                    }
                }
            });
        };

        const handleEventSearch = () => {
            eventSearchQuery.value = eventSearchDraft.value;
            eventCurrentPage.value = 1;
            loadEvents({ 
                search: eventSearchQuery.value, 
                region: eventRegionFilter.value, 
                match_type: eventFilter.value,
                start_date: eventStartDate.value,
                end_date: eventEndDate.value,
                page: 1 
            });
            window.scrollTo({ top: 0, behavior: 'smooth' });
        };

        const activeQuickLevel = ref('all');

        const applyQuickLevel = (min, max, key = 'all') => {
            const isSame = activeQuickLevel.value === key;
            if (isSame) {
                levelMinDraft.value = '';
                levelMaxDraft.value = '';
                activeQuickLevel.value = 'all';
            } else {
                levelMinDraft.value = min;
                levelMaxDraft.value = max;
                activeQuickLevel.value = key;
            }
            handleSearch();
        };

        const setDateRange = (type) => {
            eventDateShortcut.value = type;
            const now = new Date();
            const format = (d) => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
            
            if (type === 'today') {
                eventStartDate.value = format(now);
                eventEndDate.value = format(now);
            } else if (type === 'tomorrow') {
                const tomorrow = new Date(now);
                tomorrow.setDate(now.getDate() + 1);
                eventStartDate.value = format(tomorrow);
                eventEndDate.value = format(tomorrow);
            } else if (type === 'week') {
                // start of current week (Monday)
                const day = now.getDay();
                const diff = now.getDate() - day + (day === 0 ? -6 : 1);
                const start = new Date(now.setDate(diff));
                const end = new Date(start);
                end.setDate(start.getDate() + 6);
                eventStartDate.value = format(start);
                eventEndDate.value = format(end);
            } else if (type === 'month') {
                const start = new Date(now.getFullYear(), now.getMonth(), 1);
                const end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                eventStartDate.value = format(start);
                eventEndDate.value = format(end);
            } else if (type === 'all') {
                eventStartDate.value = '';
                eventEndDate.value = '';
            }
        };

        watch(isSigning, (newVal) => {
            if (newVal) {
                isAdjustingSig.value = false;
                if (moveableInstance.value) {
                    moveableInstance.value.destroy();
                    moveableInstance.value = null;
                }
            }
        });

        const openMatchModal = (p) => { matchModal.player = p; matchModal.open = true; };
        
        // 推薦戰友左右滾動
        const scrollFeaturedPlayers = (direction) => {
            if (!featuredPlayersContainer.value) return;
            const container = featuredPlayersContainer.value;
            const cardWidth = 300 + 40; // 卡片寬度 + gap
            container.scrollBy({ left: direction * cardWidth * 2, behavior: 'smooth' });
        };
        
        const getPlayersByRegion = (region) => players.value.filter(p => p.region === region);
        
        const getEventsByMatchType = (type) => {
            if (type === 'all') return events.value.length;
            return events.value.filter(e => e.match_type === type).length;
        };

        const getEventsByRegion = (region) => {
            return events.value.filter(e => (e.region === region) || (e.location && e.location.includes(region))).length;
        };
        
        const joinEvent = async (id) => {
            const updated = await baseJoinEvent(id);
            if (updated && activeEvent.value && activeEvent.value.id === id) {
                activeEvent.value = { ...activeEvent.value, ...updated };
            }
        };

        const leaveEvent = async (id) => {
            const updated = await baseLeaveEvent(id);
            if (updated && activeEvent.value && activeEvent.value.id === id) {
                activeEvent.value = { ...activeEvent.value, ...updated };
            }
        };

        const initCreateMap = async (lat = null, lng = null) => {
            try {
                await loadLeaflet();
            } catch (e) {
                showToast('地圖載入失敗，請稍後再試', 'error');
                return;
            }
            nextTick(() => {
                setTimeout(() => {
                    if (!createEventMap.value) return;
                    
                    if (createLeafletMap) {
                        createLeafletMap.remove();
                        createLeafletMap = null;
                    }

                    // Default to Taiwan center if no coordinates
                    const defaultLat = lat || 25.0478;
                    const defaultLng = lng || 121.5170;

                    createLeafletMap = L.map(createEventMap.value, {
                        zoomControl: false
                    }).setView([defaultLat, defaultLng], lat ? 16 : 8);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(createLeafletMap);

                    if (lat && lng) {
                        createMapMarker = L.marker([lat, lng], { draggable: true }).addTo(createLeafletMap);
                        createMapMarker.on('dragend', (e) => {
                            const pos = e.target.getLatLng();
                            eventForm.latitude = pos.lat;
                            eventForm.longitude = pos.lng;
                        });
                    }

                    createLeafletMap.on('click', (e) => {
                        const { lat, lng } = e.latlng;
                        eventForm.latitude = lat;
                        eventForm.longitude = lng;

                        if (createMapMarker) {
                            createMapMarker.setLatLng(e.latlng);
                        } else {
                            createMapMarker = L.marker(e.latlng, { draggable: true }).addTo(createLeafletMap);
                            createMapMarker.on('dragend', (ev) => {
                                const pos = ev.target.getLatLng();
                                eventForm.latitude = pos.lat;
                                eventForm.longitude = pos.lng;
                            });
                        }
                    });

                    L.control.zoom({ position: 'bottomright' }).addTo(createLeafletMap);
                }, 300);
            });
        };

        const useCurrentLocation = async () => {
            if (typeof window.MobileGeolocation === 'undefined') {
                showToast('您的裝置不支援定位功能', 'warning');
                return;
            }

            try {
                // Request Permission
                await window.MobileGeolocation.requestPermissions();
                
                const position = await window.MobileGeolocation.getCurrentPosition();
                const { latitude, longitude } = position.coords;
                
                eventForm.latitude = latitude;
                eventForm.longitude = longitude;

                if (createLeafletMap) {
                    createLeafletMap.setView([latitude, longitude], 16);
                    if (createMapMarker) {
                        createMapMarker.setLatLng([latitude, longitude]);
                    } else {
                        createMapMarker = L.marker([latitude, longitude], { draggable: true }).addTo(createLeafletMap);
                    }
                } else {
                    initCreateMap(latitude, longitude);
                }
                
                showToast('已取得目前位置', 'success');
            } catch (error) {
                console.error('Geolocation Error:', error);
                showToast('無法取得位置', 'error');
            }
        };

        const sendMatchRequest = async () => {
            if (!isLoggedIn.value) { matchModal.open = false; navigateTo('auth'); return; }
            if (isSendingMatch.value) return; // 防止重複發送
            isSendingMatch.value = true;
            try {
                await api.post('/messages', {
                    to_player_id: matchModal.player.id,
                    content: matchModal.text || `Hi ${matchModal.player.name}，我想跟你約打！`,
                });
                loadMessages();
                matchModal.open = false; matchModal.text = '';
            } catch (error) { showToast('發送失敗', 'error'); }
            finally { isSendingMatch.value = false; }
        };


        const openEventDetail = async (event) => {
            activeEvent.value = { ...event, loading: true };
            showEventDetail.value = true;
            try {
                const [eventRes, commentsRes] = await Promise.all([
                    api.get(`/events/${event.id}`),
                    api.get(`/events/${event.id}/comments`)
                ]);
                const eventData = eventRes?.data?.data ?? eventRes?.data ?? {};
                const commentsData = commentsRes?.data?.data ?? commentsRes?.data ?? [];
                const targetId = eventData.id ?? event.id;
                activeEvent.value = { ...eventData, loading: false };
                eventComments[targetId] = Array.isArray(commentsData) ? commentsData : [];

                // Initialize Map if coordinates exist
                if (eventData.latitude && eventData.longitude) {
                    try {
                        await loadLeaflet();
                    } catch (e) {
                        showToast('地圖載入失敗，請稍後再試', 'error');
                        return;
                    }
                    nextTick(() => {
                        setTimeout(() => {
                            if (!eventMap.value) return;
                            
                            // Cleanup previous map instance if any
                            if (activeLeafletMap) {
                                activeLeafletMap.remove();
                            }

                            const lat = parseFloat(eventData.latitude);
                            const lng = parseFloat(eventData.longitude);
                            
                            activeLeafletMap = L.map(eventMap.value, {
                                zoomControl: false,
                                scrollWheelZoom: false,
                                dragging: !L.Browser.mobile,
                                tap: !L.Browser.mobile
                            }).setView([lat, lng], 15);

                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                attribution: '&copy; OpenStreetMap contributors'
                            }).addTo(activeLeafletMap);

                            L.marker([lat, lng]).addTo(activeLeafletMap);

                            // Add zoom control to a corner
                            L.control.zoom({ position: 'bottomright' }).addTo(activeLeafletMap);
                        }, 300);
                    });
                }
            } catch (error) {
                activeEvent.value = { ...event, loading: false };
                showToast('載入失敗', 'error');
            }
        };

        const submitEventComment = async () => {
            if (!isLoggedIn.value) { showToast('請先登入', 'error'); navigateTo('auth'); return; }
            const eventId = activeEvent.value?.id;
            const text = eventCommentDraft.value?.trim();
            if (!text) return;
            try {
                const response = await api.post(`/events/${eventId}/comments`, { content: text });
                if (!eventComments[eventId]) eventComments[eventId] = [];
                eventComments[eventId].unshift(response.data.comment);
                eventCommentDraft.value = '';
            } catch (error) { showToast('發送失敗', 'error'); }
        };

        const submitEvent = async () => {
            if (eventForm.id) {
                await updateEvent(eventForm.id);
            } else {
                await createEvent();
            }
        };

        const editEvent = (event) => {
            resetEventForm();
            Object.assign(eventForm, {
                id: event.id,
                title: event.title,
                region: event.region,
                event_date: formatLocalDateTime(event.event_date),
                end_date: event.end_date ? formatLocalDateTime(event.end_date) : '',
                location: event.location,
                address: event.address,
                fee: event.fee,
                max_participants: event.max_participants,
                match_type: event.match_type,
                gender: event.gender,
                level_min: event.level_min,
                level_max: event.level_max,
                notes: event.notes
            });
            navigateTo('create-event', false);
            // Initialize map with existing coordinates
            initCreateMap(event.latitude, event.longitude);
        };

        const handleDeleteEvent = (id) => {
            showConfirm({
                title: '刪除活動',
                message: '確定要刪除此活動嗎？這項操作無法復原。',
                type: 'danger',
                onConfirm: () => deleteEvent(id)
            });
        };

        const deleteEventComment = async (commentId, eventId) => {
            showConfirm({
                title: '刪除留言', message: '確定要刪除嗎？', type: 'danger',
                onConfirm: async () => {
                    try {
                        await api.delete(`/events/comments/${commentId}`);
                        if (eventComments[eventId]) eventComments[eventId] = eventComments[eventId].filter(c => c.id !== commentId);
                        showToast('留言已刪除', 'success');
                    } catch (error) { showToast('刪除失敗', 'error'); }
                }
            });
        };

        const openMessage = (message) => {
            // 點進去立即設為已讀（前端即時回饋）
            message.unread_count = 0;
            const otherUser = (message.sender?.uid === currentUser.value.uid) ? message.receiver : message.sender;
            if (message.player) otherUser.player = message.player;
            selectedChatUser.value = otherUser;
            showMessageDetail.value = true;

            // 更新網址，方便分享與回退
            if (otherUser?.uid) {
                const currentPath = window.location.pathname;
                const targetPath = `/messages/${otherUser.uid}`;
                if (currentPath !== targetPath) {
                    navigateTo('messages', false, otherUser.uid);
                }
            }
        };

        const openChatByUid = async (uid) => {
            if (!uid) return;
            try {
                let msg = messages.value.find(m => {
                    const other = (m.sender?.uid === currentUser.value.uid) ? m.receiver : m.sender;
                    return other?.uid === uid;
                });
                
                if (msg) {
                    openMessage(msg);
                } else {
                    const response = await api.get(`/profile/${uid}`);
                    if (response.data.success) {
                        const user = response.data.user;
                        if (response.data.player) user.player = response.data.player;
                        selectedChatUser.value = user;
                        showMessageDetail.value = true;
                    }
                }
            } catch (error) {
                console.error('Failed to open chat by UID', error);
            }
        };

        const loadMoreMessages = () => {
            messagesLimit.value += 20;
        };



        // --- 7. Lifecycle & Watchers ---
        onMounted(async () => {
            await checkAuth(() => loadMessages(), () => loadMyCards());
            parseRoute(
                (id) => loadProfile(id, (append) => loadProfileEvents(append)), 
                () => resetFormFull(), 
                () => resetEventForm(),
                (uid) => openChatByUid(uid)
            );
            initialRouteResolved.value = true;

            const eventMatch = window.location.pathname.match(/^\/events\/(\d+)$/);
            if (eventMatch) {
                view.value = 'events';
                openEventDetail({ id: Number(eventMatch[1]) });
            }
            window.addEventListener('popstate', (event) => {
                if (showMessageDetail.value && (!event.state || !event.state.uid)) {
                    showMessageDetail.value = false;
                }

                if (event.state && event.state.view) {
                    applyDefaultFilters(event.state.view);
                    view.value = event.state.view;
                    if (event.state.view === 'messages' && event.state.uid) {
                        openChatByUid(event.state.uid);
                    }
                } else {
                    parseRoute(
                        (id) => loadProfile(id, (append) => loadProfileEvents(append)), 
                        () => resetFormFull(), 
                        () => resetEventForm(),
                        (uid) => openChatByUid(uid)
                    );
                }
            });

            // 預渲染暖身：讓 Vue 提前編譯 PlayerDetailModal 模板
            // Loading 保持顯示直到所有效果與 API 載入完成
            document.body.classList.add('warmup-hidden');
            
            const hideLoader = () => {
                document.body.classList.remove('warmup-hidden');
                // 額外延遲確保所有渲染完成
                setTimeout(() => {
                    const loader = document.getElementById('init-loader');
                    if (loader) {
                        loader.style.opacity = '0';
                        setTimeout(() => loader.remove(), 300);
                    }
                }, 200);
            };

            // 保險機制：無論 API/初始化是否卡住，最晚 6 秒移除 Loading
            const forceHideTimer = setTimeout(() => {
                hideLoader();
            }, 6000);
            
            setTimeout(async () => {
                try {
                    // Step 1: 暖身 Modal
                    const warmupPlayer = { id: 0, name: '', level: '3.5', region: '' };
                    detailPlayer.value = warmupPlayer;

                    await new Promise(resolve => setTimeout(resolve, 150));
                    detailPlayer.value = null;

                    await new Promise(resolve => setTimeout(resolve, 100));

                    // Step 2: 載入 API 資料
                    await loadRandomPlayers();

                    // Step 3: 初始化 Swiper
                    await nextTick();
                    initHomeSwiper();
                } catch (error) {
                    console.error('Warmup sequence failed', error);
                } finally {
                    // Step 4: 最後才移除 Loading
                    clearTimeout(forceHideTimer);
                    hideLoader();
                }
            }, 100);
        });

        // Swiper initialization function
        function initHomeSwiper() {
            if (typeof Swiper === 'undefined') return;
            
            nextTick(() => {
                // Desktop config: full effects
                const desktopConfig = {
                    effect: 'cards',
                    cardsEffect: {
                        rotate: true,
                        perSlideRotate: 3,
                        perSlideOffset: 8,
                    },
                    grabCursor: true,
                    initialSlide: 0,
                    speed: 300,
                    rewind: true,
                };
                
                // Mobile config: keep cards stacking but lower cost
                const mobileConfig = {
                    effect: 'cards',
                    cardsEffect: {
                        rotate: true,
                        perSlideRotate: 1.5,
                        perSlideOffset: 18,
                        slideShadows: false,
                    },
                    grabCursor: false,
                    initialSlide: 0,
                    speed: 160,
                    threshold: 4,
                    longSwipesRatio: 0.1,
                    longSwipesMs: 100,
                    resistanceRatio: 0.5,
                    rewind: true,
                };
                
                // Destroy existing swipers first
                const existingMobile = document.querySelector('.home-cards-swiper');
                if (existingMobile && existingMobile.swiper) {
                    existingMobile.swiper.destroy(true, true);
                }
                const existingDesktop = document.querySelector('.home-cards-swiper-desktop');
                if (existingDesktop && existingDesktop.swiper) {
                    existingDesktop.swiper.destroy(true, true);
                }
                
                // Mobile Swiper (use simplified config)
                const mobileEl = document.querySelector('.home-cards-swiper');
                if (mobileEl) {
                    new Swiper('.home-cards-swiper', mobileConfig);
                }
                
                // Desktop Swiper (use full effects config)
                const desktopEl = document.querySelector('.home-cards-swiper-desktop');
                if (desktopEl) {
                    new Swiper('.home-cards-swiper-desktop', desktopConfig);
                }
            });
        }

        // Re-initialize Swiper when navigating back to home
        watch(() => view.value, async (newView) => {
            if (newView === 'home') {
                // 只在資料為空時才重新載入
                if (randomPlayers.value.length === 0) {
                    await loadRandomPlayers();
                }
                nextTick(() => initHomeSwiper());
            }
        });

        // 當預設地區載入時，如果目前在列表頁或首頁且尚未篩選，則自動套用
        watch(() => settingsForm.default_region, (newRegion) => {
            if (newRegion && newRegion !== '全部' && (view.value === 'list' || view.value === 'home') && selectedRegion.value === '全部') {
                applyDefaultFilters(view.value);
                // 觸發載入
                loadPlayers({ search: searchQuery.value, region: selectedRegion.value, page: 1, sort: sortBy.value });
            }
            if (newRegion && newRegion !== '全部' && view.value === 'coaches' && coachSelectedRegion.value === '全部') {
                applyDefaultFilters(view.value);
                loadPlayers({ 
                    is_coach: true,
                    search: coachSearchQuery.value,
                    region: coachSelectedRegion.value,
                    coach_price_min: coachPriceMin.value,
                    coach_price_max: coachPriceMax.value,
                    coach_method: coachSelectedMethod.value,
                    coach_tag: coachSelectedTag.value,
                    coach_location: coachSelectedLocation.value,
                    page: 1,
                    sort: coachSortBy.value
                });
            }
        });

        let messagePollInterval;
        let prevView = null;  // 追蹤上一個 view
        // 監聽導航點擊 (包含點擊當前視圖按鈕)
        watch(lastNavigationTap, () => {
            const v = view.value;
            if (v === 'home' || v === 'list') {
                loadPlayers({ search: searchQuery.value, region: selectedRegion.value, page: 1, sort: sortBy.value }, true);
            } else if (v === 'coaches') {
                loadPlayers({ 
                    is_coach: true,
                    search: coachSearchQuery.value,
                    region: coachSelectedRegion.value,
                    coach_price_min: coachPriceMin.value,
                    coach_price_max: coachPriceMax.value,
                    coach_method: coachSelectedMethod.value,
                    coach_tag: coachSelectedTag.value,
                    coach_location: coachSelectedLocation.value,
                    page: 1,
                    sort: coachSortBy.value
                }, true);
            } else if (v === 'events') {
                loadEvents({ 
                    search: eventSearchQuery.value, 
                    region: eventRegionFilter.value, 
                    match_type: eventFilter.value,
                    start_date: eventStartDate.value,
                    end_date: eventEndDate.value,
                    page: 1 
                });
            }
        });

        watch(view, (newView) => {
            // Global Scroll Reset when switching views
            document.body.style.overflow = '';
            document.body.style.touchAction = '';

            // 如果是瀏覽器前進後退 (popstate) 觸發的視圖切換，需要載入資料
            // 但如果是透過導航點擊觸發的，則交給 lastNavigationTap 處理（避免雙重載入）
            const isIntentionalNav = (Date.now() - lastNavigationTap.value < 100);

              if (newView === 'home' || newView === 'list') {
                  if (!initialRouteResolved.value && initialRouteIsCoaches) return;
                 if (!isIntentionalNav) {
                     loadPlayers({ search: searchQuery.value, region: selectedRegion.value, page: 1, sort: sortBy.value });
                 }
            } else if (newView === 'coaches') {
                players.value = [];
                playersPagination.value = { total: 0, current_page: 1, last_page: 1, per_page: 12 };
                if (!isIntentionalNav) {
                    loadPlayers({ 
                        is_coach: true,
                        search: coachSearchQuery.value,
                        region: coachSelectedRegion.value,
                        coach_price_min: coachPriceMin.value,
                        coach_price_max: coachPriceMax.value,
                        coach_method: coachSelectedMethod.value,
                        coach_tag: coachSelectedTag.value,
                        coach_location: coachSelectedLocation.value,
                        page: 1,
                        sort: coachSortBy.value
                    });
                }
            } else if (newView === 'events' || newView === 'create-event') {
                if (!isIntentionalNav) {
                    loadEvents({ 
                        search: eventSearchQuery.value, 
                        region: eventRegionFilter.value, 
                        match_type: eventFilter.value,
                        start_date: eventStartDate.value,
                        end_date: eventEndDate.value,
                        page: 1 
                    });
                }
            }
            
            if (newView === 'messages') {
                loadMessages();
                if (!messagePollInterval) messagePollInterval = setInterval(loadMessages, 5000);
            } else if (messagePollInterval) {
                clearInterval(messagePollInterval);
                messagePollInterval = null;
            }
            
            prevView = newView;
        }, { immediate: true });

        watch(currentStep, () => {
            if (isAdjustingSig.value) {
                isAdjustingSig.value = false;
                if (moveableInstance.value) {
                    moveableInstance.value.destroy();
                    moveableInstance.value = null;
                }
            }
            if (isAdjustingPhoto.value) isAdjustingPhoto.value = false;
            
            // Scroll to top when step changes for better UX
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        watch(currentUser, (newVal) => {
            if (newVal && view.value === 'create' && !form.id && !form.photo) resetForm();
        }, { deep: true });

        watch(profileTab, () => loadProfileEvents(false));

        watch(showMessageDetail, (isOpen) => {
            if (!isOpen && view.value === 'messages') {
                const path = window.location.pathname;
                if (path.split('/').length > 2 && path.includes('/messages/')) {
                    navigateTo('messages', true); // 使用 replaceState 避免增加歷史紀錄
                }
            }
        });

        // Disable body scroll ONLY when on 'create' view and adjustment modes are active
        watch([isAdjustingPhoto, isAdjustingSig, isSigning, view], ([photo, sig, signing, currentView]) => {
            // Only lock scroll on create page with active adjustment; otherwise always ensure scroll is enabled
            if (currentView === 'create' && (photo || sig || signing)) {
                document.body.style.overflow = 'hidden';
                document.body.style.touchAction = 'none';
            } else {
                document.body.style.overflow = '';
                document.body.style.touchAction = '';
            }
        }, { immediate: true }); // immediate ensures cleanup runs on initial load
        
        // Players Watchers
        watch(currentPage, (newPage) => {
            if (view.value !== 'list') return;
            loadPlayers({ 
                search: searchQuery.value, 
                region: selectedRegion.value, 
                gender: selectedGender.value,
                level_min: selectedLevelMin.value,
                level_max: selectedLevelMax.value,
                handed: selectedHanded.value,
                backhand: selectedBackhand.value,
                page: newPage,
                sort: sortBy.value
            });
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        watch(sortBy, (newSort) => {
            if (view.value !== 'list') return;
            currentPage.value = 1;
            loadPlayers({ 
                search: searchQuery.value, 
                region: selectedRegion.value, 
                gender: selectedGender.value,
                level_min: selectedLevelMin.value,
                level_max: selectedLevelMax.value,
                handed: selectedHanded.value,
                backhand: selectedBackhand.value,
                page: 1,
                sort: newSort
            });
        });

        watch(coachCurrentPage, (newPage) => {
            if (view.value !== 'coaches') return;
            loadPlayers({
                is_coach: true,
                search: coachSearchQuery.value,
                region: coachSelectedRegion.value,
                coach_price_min: coachPriceMin.value,
                coach_price_max: coachPriceMax.value,
                coach_method: coachSelectedMethod.value,
                coach_tag: coachSelectedTag.value,
                coach_location: coachSelectedLocation.value,
                page: newPage,
                sort: coachSortBy.value
            });
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        watch(coachSortBy, (newSort) => {
            if (view.value !== 'coaches') return;
            coachCurrentPage.value = 1;
            loadPlayers({
                is_coach: true,
                search: coachSearchQuery.value,
                region: coachSelectedRegion.value,
                coach_price_min: coachPriceMin.value,
                coach_price_max: coachPriceMax.value,
                coach_method: coachSelectedMethod.value,
                coach_tag: coachSelectedTag.value,
                coach_location: coachSelectedLocation.value,
                page: 1,
                sort: newSort
            });
        });

        // Events Watchers
        watch([eventRegionFilter, eventFilter, eventStartDate, eventEndDate], () => {
            eventCurrentPage.value = 1;
            loadEvents({ 
                search: eventSearchQuery.value, 
                region: eventRegionFilter.value, 
                match_type: eventFilter.value,
                start_date: eventStartDate.value,
                end_date: eventEndDate.value,
                page: 1 
            });
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
        watch(eventCurrentPage, (newPage) => {
            if (typeof loadEvents === 'function') {
                loadEvents({ 
                    search: eventSearchQuery.value, 
                    region: eventRegionFilter.value, 
                    match_type: eventFilter.value,
                    start_date: eventStartDate.value,
                    end_date: eventEndDate.value,
                    page: newPage 
                });
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });

        return {
            // State
            view, isLoggedIn, currentUser, isLoginMode, showUserMenu, isSigning, messageTab,
            players, myPlayers, isPlayersLoading, isSubmitting, playersPagination, messages, events, eventsLoading, eventSubmitting, eventsPagination,
            profileData, isProfileLoading, profileTab, profileEvents, profileEventsHasMore, isEditingProfile, profileForm,
            form, eventForm, currentStep, stepAttempted, isAdjustingPhoto, isAdjustingSig, isCapturing, isPhotoAdjustLoading, isSigAdjustLoading,
            searchQuery, searchDraft, selectedRegion, regionDraft, selectedGender, genderDraft, selectedLevelMin, levelMinDraft, selectedLevelMax, levelMaxDraft, selectedHanded, handedDraft, selectedBackhand, backhandDraft, showAdvancedFilters, currentPage, perPage, matchModal, detailPlayer, featuredPlayersContainer,
            coachSearchQuery, coachSearchDraft, coachSelectedRegion, coachRegionDraft, coachCurrentPage, coachSortBy,
            coachPriceMin, coachPriceMax, coachPriceMinDraft, coachPriceMaxDraft, coachSelectedMethod, coachMethodDraft,
            coachSelectedTag, coachTagDraft, coachSelectedLocation, coachLocationDraft, showCoachFilters, showCoachForm, coachForm, isSavingCoach,
            eventFilter, eventRegionFilter, eventSearchQuery, eventSearchDraft, eventStartDate, eventEndDate, eventDateShortcut, eventCurrentPage, eventPerPage, showEventDetail, activeEvent, eventComments, eventCommentDraft,
            showNtrpGuide, showPrivacy, showLinePromo, showMessageDetail, selectedChatUser, isLoading, isAuthLoading, authError,
            showPreview, showQuickEditModal, navRefreshing, navRefreshView, features, cardThemes,
            shareModal, isSendingMatch, scrollFeaturedPlayers, openShare,
            settingsForm, isSavingSettings, toasts, confirmDialog, dragInfo,
            profileComments, followingUsers, followerUsers, likedPlayers, playerCommentDraft, playerCommentRating,
            selectedProfileRegions, toggleProfileRegion, reportModal, isReporting, isBlocking, profileActionMenu,
            instantRooms, currentRoom, instantMessages, isInstantLoading, globalInstantStats, instantMessageDraft, isSending,
            globalData, isLfg, selectedLfgRemark, showLfgPicker, customLfgRemark, roomSearch, roomCategory, sortedAndFilteredRooms, activityNotifications, currentTickerIndex, displayOtherAvatars, hiddenOthersCount,
            singleRoomMode, enterSingleRoom, userRegion, timeLabel, quickTemplates, quickRegions, selectedQuickRegion,
            // Computed
            hasUnread, hasPlayerCard, myCards, activeRegions, activeEventRegions, filteredPlayers, totalPages, paginatedPlayers, displayPages, randomPlayers,
            coachTotalPages, coachPaginatedPlayers, coachDisplayPages, coachMethods,
            filteredEvents, eventTotalPages, paginatedEvents, eventDisplayPages,
            minEventDate: computed(() => formatLocalDateTime(new Date())),
            paginatedMessages, hasMoreMessages,
            canProceedStep1, canProceedStep2, canProceedStep3, canGoToStep,
            // Methods
            navigateTo: navigateToWithProfile, goBack, logout, checkAuth, saveSettings, loginWithLine, loadPlayers, loadMyCards, saveCard: handleSaveCard, deleteCard, editCard, resetForm, resetFormFull,
            loadEvents, createEvent, updateEvent, deleteEvent: handleDeleteEvent, resetEventForm, openEventDetail, submitEventComment, deleteEventComment, setDateRange, editEvent, submitEvent, 
            joinEvent, 
            leaveEvent,
            // Event modal compatibility aliases
            toggleEventLike: (eventId) => {}, 
            postEventComment: () => submitEventComment(),
            // Profile methods with edit mode support
            openProfileWithEdit: () => { loadProfile(currentUser.value.uid, loadProfileEvents, true); navigateTo('profile', false, currentUser.value.uid); },
            loadProfile, loadProfileEvents, saveProfile, openProfile, toggleFollow, toggleLike,
            loadProfileComments, loadFollowing, loadFollowers, loadLikedPlayers, submitPlayerComment, deletePlayerComment, getRatingPercentage,
            openReportModal, submitReport, toggleBlock,
            selectRoom, sendInstantMessage, fetchMessages, joinBySlug,
            fetchGlobalData, toggleLfg,
            loadMessages, markMessageRead, openMessage, openChatByUid, onMessageSent, loadMoreMessages,
            handlePlayerUpdate,
            showToast, removeToast, showConfirm, hideConfirm, executeConfirm,
            formatDate, getUrl, formatLocalDateTime, formatEventDate,
            handleFileUpload, triggerUpload, useLinePhoto, handleSignatureUpdate, toggleAdjustSig, finishPhotoAdjust, finishSigAdjust, initMoveable, getPlayersByRegion,
            applyQuickLevel,
            activeQuickLevel,
            startDrag, handleDrag, stopDrag, captureCardImage,
            tryNextStep, tryGoToStep, openMatchModal, sendMatchRequest,
            handleSearch, handleCoachSearch, handleEventSearch,
            openCoachForm, closeCoachForm, saveCoachProfile, cancelCoachProfile, resetCoachForm,
            showDetail, getDetailStats, getEventsByMatchType, getEventsByRegion, togglePlayerLike,
            // Constants
            REGIONS, LEVELS, LEVEL_DESCS, LEVEL_TAGS, levelDescs, levels, regions,
            sortBy,
            // Map Refs & Methods
            eventMap, createEventMap, useCurrentLocation, initCreateMap
        };
    },
    components: {
        'app-icon': AppIcon,
        'player-card': PlayerCard,
        'player-detail-modal': PlayerDetailModal,
        'share-modal': ShareModal,
        'match-modal': MatchModal,
        'ntrp-guide-modal': NtrpGuideModal,
        'quick-edit-modal': QuickEditModal,
        'message-detail-modal': MessageDetailModal,
        'event-detail-modal': EventDetailModal,
        'privacy-modal': PrivacyModal,
        'emoji-picker': EmojiPicker
    }
}).mount('#app');
</script>
