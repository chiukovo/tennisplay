<script>
@include('partials.vue.constants')
@include('partials.vue.components')
@include('partials.vue.composables.index')

createApp({
    setup() {
        // --- 1. Basic State (Refs & Reactives) ---
        const isLoggedIn = ref(false);
        const currentUser = ref(null);
        const searchQuery = ref('');
        const selectedRegion = ref('全部');
        const currentPage = ref(1);
        const perPage = ref(12);
        const matchModal = reactive({ open: false, player: null, text: '' });
        const detailPlayer = ref(null);
        const isSigning = ref(false);
        const showNtrpGuide = ref(false);
        const eventFilter = ref('all');
        const eventRegionFilter = ref('all');
        const eventSearchQuery = ref('');
        const showEventDetail = ref(false);
        const activeEvent = ref(null);
        const eventLikes = reactive({});
        const eventComments = reactive({});
        const eventCommentDraft = ref('');
        const messageTab = ref('inbox');
        const messagesLimit = ref(20);
        const selectedChatUser = ref(null);
        const showMessageDetail = ref(false);
        const showPreview = ref(false);
        const showQuickEditModal = ref(false);
        const settingsForm = reactive({ default_region: '全部' });
        const form = reactive({
            id: null, name: '', region: '台中市', level: '3.5', handed: '右手', backhand: '雙反', gender: '男',
            intro: '', fee: '免費 (交流為主)', photo: null, signature: null, theme: 'standard',
            merged_photo: null, photoX: 0, photoY: 0, photoScale: 1, 
            sigX: 50, sigY: 50, sigScale: 1, sigRotate: 0, sigWidth: 100, sigHeight: 100
        });
        const eventForm = reactive({
            title: '', region: '', event_date: '', end_date: '', location: '', address: '',
            fee: 0, max_participants: 0, match_type: 'all', gender: 'all', level_min: '', level_max: '', notes: ''
        });

        // --- 2. Helper Functions (Must be defined before composables that use them) ---
        const { 
            toasts, showToast, removeToast, confirmDialog, showConfirm, hideConfirm, executeConfirm, 
            formatDate, getUrl, formatLocalDateTime 
        } = useUtils();

        const initSettings = () => {
            if (currentUser.value && currentUser.value.settings) {
                const s = currentUser.value.settings;
                settingsForm.default_region = s.default_region || '全部';
            }
        };

        const applyDefaultFilters = (viewName) => {
            const defRegion = settingsForm.default_region;
            if (!defRegion || defRegion === '全部') return;
            if (viewName === 'list' && selectedRegion.value === '全部') selectedRegion.value = defRegion;
            else if (viewName === 'events' && eventRegionFilter.value === 'all') eventRegionFilter.value = defRegion;
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

        const resetEventForm = () => {
            const now = new Date();
            const start = formatLocalDateTime(now);
            const end = formatLocalDateTime(new Date(now.getTime() + 2 * 60 * 60 * 1000));
            const defRegion = (settingsForm.default_region && settingsForm.default_region !== '全部') ? settingsForm.default_region : '';
            
            Object.assign(eventForm, {
                title: '', region: defRegion, event_date: start, end_date: end, location: '', address: '',
                fee: 0, max_participants: 0, match_type: 'all', gender: 'all', level_min: '', level_max: '', notes: ''
            });
        };

        // --- 3. Initialize Composables ---
        const { view, navigateTo, parseRoute } = useNavigation(
            { '/': 'home', '/list': 'list', '/create': 'create', '/messages': 'messages', '/auth': 'auth', '/profile': 'profile', '/events': 'events', '/create-event': 'create-event' },
            { 'home': '/', 'list': '/list', 'create': '/create', 'messages': '/messages', 'auth': '/auth', 'profile': '/profile', 'events': '/events', 'create-event': '/create-event' },
            { 
                'home': 'LoveTennis | 全台最專業的網球約打媒合與球友卡社群', 'list': '球友大廳 | 發現您的最佳網球夥伴', 'create': '建立球友卡 | 展現您的網球風格', 'messages': '我的訊息 | 網球約打邀請管理', 'events': '揪球開團 | 搜尋全台網球場次', 'create-event': '發佈揪球 | 建立新的網球場次', 'auth': '登入/註冊 | 加入 LoveTennis 社群', 'profile': '個人主頁 | LoveTennis', 'settings': '帳號設置 | 個性化您的網球體驗' 
            },
            showToast,
            (viewName) => applyDefaultFilters(viewName),
            isLoggedIn,
            currentUser
        );

        const { 
            isLoginMode, showUserMenu, isSavingSettings, 
            checkAuth, logout, saveSettings 
        } = useAuth(showToast, (v, s, i) => navigateTo(v, s, i), () => initSettings(), isLoggedIn, currentUser, settingsForm);

        const { 
            profileData, profileTab, profileEvents, profileEventsHasMore, isEditingProfile, profileForm, 
            loadProfile, loadProfileEvents, saveProfile, openProfile, toggleFollow, toggleLike
        } = useProfile(isLoggedIn, currentUser, showToast, navigateTo);

        const { 
            players, myPlayers, isPlayersLoading, loadPlayers, loadMyCards, saveCard, deleteCard 
        } = usePlayers(isLoggedIn, currentUser, showToast, navigateTo, showConfirm, (id) => loadProfile(id), form);

        const { 
            events, eventsLoading, eventSubmitting, loadEvents, createEvent, joinEvent, leaveEvent 
        } = useEvents(isLoggedIn, showToast, navigateTo, formatLocalDateTime, eventForm);

        const { messages, loadMessages, markMessageRead } = useMessages(isLoggedIn, currentUser, showToast);

        const { 
            currentStep, stepAttempted, isAdjustingPhoto, isAdjustingSig, isCapturing, 
            canProceedStep1, canProceedStep2, canProceedStep3, canGoToStep, tryNextStep, tryGoToStep 
        } = useCardCreation(form, showToast);

        const { dragInfo, startDrag, handleDrag, stopDrag } = useDrag(form);

        const { isCapturing: isCapturingImage, captureCardImage } = useCapture(showToast);

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
            // Check if trying to create card without basic info
            if (viewName === 'create' && isLoggedIn.value) {
                if (!currentUser.value?.gender || !currentUser.value?.region) {
                    showToast('請先完成預設資料（性別、地區）再製作球友卡', 'warning');
                    const userUid = currentUser.value?.uid || currentUser.value?.id;
                    loadProfile(userUid, loadProfileEvents, true); // Auto open edit mode
                    navigateTo('profile', false, userUid, resetFormFull, resetEventForm, loadProfile);
                    return;
                }
            }
            
            // For profile navigation, load the profile data
            if (viewName === 'profile' && uid) {
                // If we are navigating to our own profile and it's incomplete, auto-edit
                const isMe = uid === currentUser.value?.uid || String(uid) === String(currentUser.value?.id);
                const isIncomplete = isMe && (!currentUser.value?.gender || !currentUser.value?.region);
                loadProfile(uid, loadProfileEvents, isIncomplete);
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
            currentStep.value = 4;
            navigateTo('create', false);
        };

        // Show player detail modal
        const showDetail = (player) => {
            detailPlayer.value = player;
        };

        // Get stats for player detail modal
        const getDetailStats = (player) => {
            if (!player) return { likes: 0, matches: 0 };
            return { likes: player.likes_count || 0, matches: player.matches_count || 0 };
        };

        // Toggle event like
        const toggleEventLike = async (eventId) => {
            if (!isLoggedIn.value) { showToast('請先登入', 'error'); navigateTo('auth'); return; }
            try {
                const response = await api.post(`/events/${eventId}/like`);
                eventLikes[eventId] = response.data.liked;
            } catch (error) { showToast('操作失敗', 'error'); }
        };

        // Message sent callback
        const onMessageSent = () => {
            loadMessages();
            showMessageDetail.value = false;
        };

        // Loading state
        const isLoading = ref(false);

        // Level descriptions alias for template
        const levelDescs = LEVEL_DESCS;
        const levels = LEVELS;
        const regions = REGIONS;

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
        const activeRegions = computed(() => {
            const counts = {};
            players.value.forEach(p => { if (p.region) counts[p.region] = (counts[p.region] || 0) + 1; });
            return Object.entries(counts).sort((a, b) => b[1] - a[1]).map(e => e[0]);
        });

        const filteredPlayers = computed(() => {
            let result = players.value;
            if (selectedRegion.value !== '全部') result = result.filter(p => p.region === selectedRegion.value);
            if (searchQuery.value) {
                const q = searchQuery.value.toLowerCase();
                result = result.filter(p => p.name.toLowerCase().includes(q) || (p.intro && p.intro.toLowerCase().includes(q)));
            }
            return result;
        });

        const totalPages = computed(() => Math.ceil(filteredPlayers.value.length / perPage.value));
        const paginatedPlayers = computed(() => {
            const start = (currentPage.value - 1) * perPage.value;
            return filteredPlayers.value.slice(start, start + perPage.value);
        });

        const displayPages = computed(() => {
            const total = totalPages.value;
            if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
            const current = currentPage.value;
            if (current <= 4) return [1, 2, 3, 4, 5, '...', total];
            if (current >= total - 3) return [1, '...', total - 4, total - 3, total - 2, total - 1, total];
            return [1, '...', current - 1, current, current + 1, '...', total];
        });

        const filteredEvents = computed(() => {
            let result = events.value;
            if (eventFilter.value !== 'all') result = result.filter(e => e.match_type === eventFilter.value);
            if (eventRegionFilter.value !== 'all') {
                result = result.filter(e => (e.region === eventRegionFilter.value) || (e.location && e.location.includes(eventRegionFilter.value)));
            }
            if (eventSearchQuery.value) {
                const q = eventSearchQuery.value.toLowerCase();
                result = result.filter(e => (e.title && e.title.toLowerCase().includes(q)) || (e.location && e.location.toLowerCase().includes(q)));
            }
            return result;
        });

        const hasUnread = computed(() => Array.isArray(messages.value) && messages.value.some(m => m.unread || !m.read_at));
        const hasPlayerCard = computed(() => myPlayers.value && myPlayers.value.length > 0);
        const myCards = computed(() => myPlayers.value);

        // --- 6. Methods ---
        const handleFileUpload = (e) => {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (event) => {
                form.photo = event.target.result;
                form.photoX = 0; form.photoY = 0; form.photoScale = 1;
                isAdjustingPhoto.value = true; // Auto enter adjustment mode
                showToast('照片上傳成功，您可以拖動調整位置', 'success');
            };
            reader.readAsDataURL(file);
        };

        const triggerUpload = () => document.getElementById('photo-upload').click();

        const useLinePhoto = async () => {
            if (!currentUser.value?.line_picture_url) return;
            const url = currentUser.value.line_picture_url;
            try {
                const response = await fetch(url);
                const blob = await response.blob();
                const reader = new FileReader();
                reader.onload = (e) => {
                    form.photo = e.target.result;
                    form.photoX = 0; form.photoY = 0; form.photoScale = 1;
                    isAdjustingPhoto.value = true; // Auto enter adjustment mode
                    showToast('已成功匯入 LINE 大頭貼', 'success');
                };
                reader.readAsDataURL(blob);
            } catch (error) {
                form.photo = url;
                isAdjustingPhoto.value = true; // Also enter adjustment mode for link fallback
                showToast('無法直接匯入圖片，已使用連結代替', 'warning');
            }
        };

        const handleSignatureUpdate = (sigData) => {
            if (!sigData) {
                form.signature = null;
                return;
            }
            form.signature = sigData.dataUrl;
            form.sigWidth = sigData.widthPct;
            form.sigHeight = sigData.heightPct;
            form.sigX = sigData.xPct;
            form.sigY = sigData.yPct;
            isSigning.value = false;
            showToast('簽名已更新', 'success');

            // Auto enter adjustment mode
            isAdjustingSig.value = true;
            nextTick(() => {
                const target = document.querySelector('#target-signature');
                if (target) initMoveable(target);
            });
        };

        const toggleAdjustSig = () => {
            isAdjustingSig.value = !isAdjustingSig.value;
            if (isAdjustingSig.value) {
                nextTick(() => {
                    const target = document.querySelector('#target-signature');
                    if (target) initMoveable(target);
                });
            } else if (moveableInstance.value) {
                moveableInstance.value.destroy();
                moveableInstance.value = null;
            }
        };

        const moveableInstance = ref(null);
        const initMoveable = (target) => {
            if (!isAdjustingSig.value || !target) return;
            if (moveableInstance.value) moveableInstance.value.destroy();

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
        };

        const handleSaveCard = () => {
            saveCard(resetFormFull);
        };

        const openMatchModal = (p) => { matchModal.player = p; matchModal.open = true; };
        const getPlayersByRegion = (region) => players.value.filter(p => p.region === region);
        const sendMatchRequest = async () => {
            if (!isLoggedIn.value) { matchModal.open = false; navigateTo('auth'); return; }
            try {
                await api.post('/messages', {
                    to_player_id: matchModal.player.id,
                    content: matchModal.text || `Hi ${matchModal.player.name}，我想跟你約打！`,
                });
                showToast(`已成功發送約打邀請給 ${matchModal.player.name}`, 'success');
                loadMessages();
            } catch (error) { showToast('發送失敗', 'error'); }
            matchModal.open = false; matchModal.text = '';
        };

        const openEventDetail = async (event) => {
            activeEvent.value = { ...event, loading: true };
            showEventDetail.value = true;
            try {
                const [eventRes, commentsRes] = await Promise.all([
                    api.get(`/events/${event.id}`),
                    api.get(`/events/${event.id}/comments`)
                ]);
                activeEvent.value = { ...eventRes.data, loading: false };
                eventComments[event.id] = commentsRes.data || [];
            } catch (error) { showToast('載入失敗', 'error'); showEventDetail.value = false; }
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
                showToast('留言成功', 'success');
            } catch (error) { showToast('發送失敗', 'error'); }
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
            const otherUser = (message.sender?.uid === currentUser.value.uid) ? message.receiver : message.sender;
            if (message.player) otherUser.player = message.player;
            selectedChatUser.value = otherUser;
            showMessageDetail.value = true;
        };

        // --- 7. Lifecycle & Watchers ---
        onMounted(() => {
            checkAuth(() => loadMessages(), () => loadMyCards());
            parseRoute((id) => loadProfile(id, (append) => loadProfileEvents(append)), () => resetFormFull(), () => resetEventForm());
            window.addEventListener('popstate', (event) => {
                if (event.state && event.state.view) view.value = event.state.view;
                else parseRoute((id) => loadProfile(id, (append) => loadProfileEvents(append)), () => resetFormFull(), () => resetEventForm());
            });
        });

        let messagePollInterval;
        watch(view, (newView) => {
            if (newView === 'home' || newView === 'list') loadPlayers();
            else if (newView === 'events' || newView === 'create-event') loadEvents();
            
            if (newView === 'messages') {
                loadMessages();
                if (!messagePollInterval) messagePollInterval = setInterval(loadMessages, 5000);
            } else if (messagePollInterval) {
                clearInterval(messagePollInterval);
                messagePollInterval = null;
            }
        }, { immediate: true });

        watch(currentStep, () => {
            if (isAdjustingSig.value) {
                isAdjustingSig.value = false;
                if (moveableInstance.value) {
                    moveableInstance.value.destroy();
                    moveableInstance.value = null;
                }
            }
            if (isAdjustingPhoto.value) {
                isAdjustingPhoto.value = false;
            }
        });

        watch(currentUser, (newVal) => {
            if (newVal && view.value === 'create' && !form.id && !form.photo) {
                resetForm();
            }
        }, { deep: true });

        watch(profileTab, () => loadProfileEvents(false));

        return {
            // State
            view, isLoggedIn, currentUser, isLoginMode, showUserMenu, isSigning, messageTab,
            players, myPlayers, isPlayersLoading, messages, events, eventsLoading,
            profileData, profileTab, profileEvents, profileEventsHasMore, isEditingProfile, profileForm,
            form, eventForm, currentStep, stepAttempted, isAdjustingPhoto, isAdjustingSig, isCapturing,
            searchQuery, selectedRegion, currentPage, perPage, matchModal, detailPlayer,
            eventFilter, eventRegionFilter, eventSearchQuery, showEventDetail, activeEvent, eventComments, eventCommentDraft,
            eventLikes, showNtrpGuide, showMessageDetail, selectedChatUser, isLoading,
            showPreview, showQuickEditModal, features, cardThemes,
            settingsForm, isSavingSettings, toasts, confirmDialog, dragInfo,
            // Computed
            hasUnread, hasPlayerCard, myCards, activeRegions, filteredPlayers, totalPages, paginatedPlayers, displayPages, filteredEvents,
            canProceedStep1, canProceedStep2, canProceedStep3, canGoToStep,
            // Methods - use navigateToWithProfile as the exported navigateTo
            navigateTo: navigateToWithProfile, logout, checkAuth, saveSettings, loadPlayers, loadMyCards, saveCard: handleSaveCard, deleteCard, editCard, resetForm, resetFormFull,
            loadEvents, createEvent, joinEvent, leaveEvent, resetEventForm, openEventDetail, submitEventComment, deleteEventComment,
            loadProfile, loadProfileEvents, saveProfile, openProfile, toggleFollow, toggleLike,
            loadMessages, markMessageRead, openMessage, onMessageSent,
            showToast, removeToast, showConfirm, hideConfirm, executeConfirm,
            formatDate, getUrl, formatLocalDateTime,
            handleFileUpload, triggerUpload, useLinePhoto, handleSignatureUpdate, toggleAdjustSig, initMoveable, getPlayersByRegion,
            startDrag, handleDrag, stopDrag, captureCardImage,
            tryNextStep, tryGoToStep, openMatchModal, sendMatchRequest,
            showDetail, getDetailStats, toggleEventLike,
            // Constants
            REGIONS, LEVELS, LEVEL_DESCS, LEVEL_TAGS, levelDescs, levels, regions
        };
    },
    components: {
        'app-icon': AppIcon,
        'player-card': PlayerCard,
        'player-detail-modal': PlayerDetailModal,
        'match-modal': MatchModal,
        'ntrp-guide-modal': NtrpGuideModal,
        'quick-edit-modal': QuickEditModal,
        'message-detail-modal': MessageDetailModal,
        'event-detail-modal': EventDetailModal
    }
}).mount('#app');
</script>
