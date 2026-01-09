// --- Composables ---

const useUtils = () => {
    const toasts = ref([]);
    let lastToastMessage = '';
    let lastToastTime = 0;

    const showToast = (message, type = 'info', duration = 4000) => {
        const now = Date.now();
        if (message === lastToastMessage && now - lastToastTime < 500) return;
        lastToastMessage = message;
        lastToastTime = now;
        const id = now;
        toasts.value.push({ id, message, type });
        setTimeout(() => {
            const index = toasts.value.findIndex(t => t.id === id);
            if (index > -1) toasts.value.splice(index, 1);
        }, duration);
    };

    const confirmDialog = reactive({
        open: false, title: '', message: '', confirmText: '確認', cancelText: '取消', onConfirm: null, type: 'danger'
    });

    const showConfirm = (options) => {
        Object.assign(confirmDialog, {
            open: true,
            title: options.title || '確認操作',
            message: options.message || '確定要執行此操作嗎？',
            confirmText: options.confirmText || '確認',
            cancelText: options.cancelText || '取消',
            type: options.type || 'danger',
            onConfirm: options.onConfirm
        });
    };

    const hideConfirm = () => { confirmDialog.open = false; confirmDialog.onConfirm = null; };
    const executeConfirm = () => { if (confirmDialog.onConfirm) confirmDialog.onConfirm(); hideConfirm(); };

    const formatDate = (dateStr) => {
        if (!dateStr) return '';
        const date = new Date(dateStr);
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);
        if (diffMins < 1) return '剛剛';
        if (diffMins < 60) return `${diffMins} 分鐘前`;
        if (diffHours < 24) return `${diffHours} 小時前`;
        if (diffDays < 7) return `${diffDays} 天前`;
        return date.toLocaleDateString('zh-TW', { month: 'short', day: 'numeric' });
    };

    const getUrl = (path) => {
        if (!path) return null;
        if (path.startsWith('http') || path.startsWith('data:')) return path;
        return `/storage/${path}`;
    };

    const formatLocalDateTime = (date) => {
        const pad = (n) => String(n).padStart(2, '0');
        return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
    };

    return { toasts, showToast, confirmDialog, showConfirm, hideConfirm, executeConfirm, formatDate, getUrl, formatLocalDateTime };
};

const useNavigation = (routes, routePaths, viewTitles, showToast, applyDefaultFilters, isLoggedIn, currentUser) => {
    const view = ref('home');

    watch(view, (newView) => {
        if (viewTitles[newView]) document.title = viewTitles[newView];
    });

    const navigateTo = (viewName, shouldReset = true, id = null, resetForm = null, resetEventForm = null) => {
        if (viewName === 'create' && isLoggedIn.value) {
            if (!currentUser.value?.gender || !currentUser.value?.region) {
                showToast('請先完成基本資料（性別、地區）再建立球友卡', 'warning');
                viewName = 'profile'; id = currentUser.value?.id; shouldReset = false;
            }
        }

        if (viewName === 'create' && shouldReset && resetForm) resetForm();
        if (viewName === 'create-event' && shouldReset && resetEventForm) resetEventForm();
        
        view.value = viewName;
        let path = routePaths[viewName] || '/';
        if (viewName === 'profile' && id) path = `/profile/${id}`;
        
        window.history.pushState({ view: viewName, id: id }, '', path);
        if (applyDefaultFilters) applyDefaultFilters(viewName);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    const parseRoute = (loadProfile, resetForm, resetEventForm) => {
        const path = window.location.pathname;
        let viewName = routes[path];
        if (!viewName) {
            const matchedKey = Object.keys(routes).find(r => r !== '/' && path.endsWith(r));
            if (matchedKey) viewName = routes[matchedKey];
        }
        if (!viewName && path.includes('/profile/')) {
            const parts = path.split('/');
            const id = parts[parts.length - 1];
            if (id && !isNaN(id)) {
                viewName = 'profile';
                if (loadProfile) loadProfile(id);
            }
        }
        if (!viewName) viewName = 'home';
        view.value = viewName;
        if (viewName === 'create' && resetForm) resetForm();
        if (viewName === 'create-event' && resetEventForm) resetEventForm();
        return viewName;
    };

    return { view, navigateTo, parseRoute };
};

const useAuth = (showToast, navigateTo, initSettings, isLoggedIn, currentUser, settingsForm) => {
    const isLoginMode = ref(true);
    const showUserMenu = ref(false);
    const isSavingSettings = ref(false);

    const checkAuth = (loadMessages, loadMyCards) => {
        const urlParams = new URLSearchParams(window.location.search);
        const lineToken = urlParams.get('line_token');
        const lineUser = urlParams.get('line_user');
        
        if (lineToken && lineUser) {
            try {
                const userData = JSON.parse(lineUser);
                localStorage.setItem('auth_token', lineToken);
                localStorage.setItem('auth_user', lineUser);
                isLoggedIn.value = true;
                currentUser.value = userData;
                if (initSettings) initSettings();
                showToast('LINE 登入成功！', 'success');
                if (loadMessages) loadMessages();
                if (loadMyCards) loadMyCards();
                window.history.replaceState({}, document.title, '/');
                return;
            } catch (e) {}
        }

        const token = localStorage.getItem('auth_token');
        const user = localStorage.getItem('auth_user');
        if (token && user) {
            isLoggedIn.value = true;
            try {
                currentUser.value = JSON.parse(user);
                if (initSettings) initSettings();
            } catch (e) {}
            if (loadMessages) loadMessages();
            if (loadMyCards) loadMyCards();
        }
    };

    const logout = async () => {
        try { await api.post('/logout'); } catch (error) {}
        localStorage.removeItem('auth_token');
        localStorage.removeItem('auth_user');
        isLoggedIn.value = false;
        currentUser.value = null;
        showToast('已成功登出', 'info');
        navigateTo('home');
    };

    const saveSettings = async () => {
        if (isSavingSettings.value) return;
        isSavingSettings.value = true;
        try {
            const response = await api.put('/user/settings', {
                settings: { default_region: settingsForm.default_region }
            });
            if (response.data.success) {
                currentUser.value = response.data.data;
                localStorage.setItem('auth_user', JSON.stringify(currentUser.value));
                showToast('設置已儲存', 'success');
            }
        } catch (error) {
            showToast('儲存失敗', 'error');
        } finally {
            isSavingSettings.value = false;
        }
    };

    return { isLoggedIn, currentUser, isLoginMode, showUserMenu, settingsForm, isSavingSettings, checkAuth, logout, saveSettings };
};

const usePlayers = (isLoggedIn, currentUser, showToast, navigateTo, showConfirm, loadProfile, form) => {
    const players = ref([]);
    const myPlayers = ref([]);
    const isPlayersLoading = ref(false);

    const loadPlayers = async () => {
        isPlayersLoading.value = true;
        try {
            const response = await api.get('/players?per_page=1000');
            if (response.data.success) {
                const data = response.data.data;
                const rawPlayers = Array.isArray(data.data) ? data.data : (Array.isArray(data) ? data : []);
                players.value = rawPlayers.filter(p => p && p.id);
            }
        } catch (error) {} finally { isPlayersLoading.value = false; }
    };

    const loadMyCards = async () => {
        if (!isLoggedIn.value) return;
        try {
            const response = await api.get('/my-cards');
            if (response.data.success) myPlayers.value = response.data.data;
        } catch (error) {}
    };

    const saveCard = async (canProceedStep1, canProceedStep2, canProceedStep3, resetForm) => {
        if (!isLoggedIn.value) { showToast('請先登入', 'error'); navigateTo('auth'); return; }
        if (!canProceedStep1 || !canProceedStep2 || !canProceedStep3) { showToast('請確認必填欄位', 'error'); return; }
        
        try {
            const payload = {
                name: form.name, region: form.region, level: form.level, gender: form.gender,
                handed: form.handed, backhand: form.backhand, intro: form.intro, fee: form.fee,
                theme: form.theme, photo: form.photo, signature: form.signature,
                photo_x: form.photoX, photo_y: form.photoY, photo_scale: form.photoScale,
                sig_x: form.sigX, sig_y: form.sigY, sig_scale: form.sigScale,
                sig_rotate: form.sigRotate, sig_width: form.sigWidth, sig_height: form.sigHeight,
            };
            let response = form.id ? await api.put(`/players/${form.id}`, payload) : await api.post('/players', payload);
            if (response.data.success) {
                showToast(form.id ? '球友卡已更新' : '球友卡建立成功！', 'success');
                await loadPlayers(); await loadMyCards();
                resetForm(); navigateTo('profile', true, currentUser.value.uid);
            }
        } catch (error) { showToast('儲存失敗', 'error'); }
    };

    const deleteCard = (cardId, view) => {
        showConfirm({
            title: '刪除球友卡', message: '確定要刪除嗎？', confirmText: '確認刪除', type: 'danger',
            onConfirm: async () => {
                try {
                    await api.delete(`/players/${cardId}`);
                    await loadPlayers(); await loadMyCards();
                    if (view.value === 'profile' && loadProfile) loadProfile(currentUser.value.uid);
                    showToast('球友卡已刪除', 'info');
                } catch (error) { showToast('刪除失敗', 'error'); }
            }
        });
    };

    return { players, myPlayers, isPlayersLoading, form, loadPlayers, loadMyCards, saveCard, deleteCard };
};

const useEvents = (isLoggedIn, showToast, navigateTo, formatLocalDateTime, eventForm) => {
    const events = ref([]);
    const eventsLoading = ref(false);
    const eventSubmitting = ref(false);

    const loadEvents = async () => {
        eventsLoading.value = true;
        try {
            const response = await api.get('/events');
            if (response.data.success) events.value = response.data.data;
        } catch (error) { events.value = []; } finally { eventsLoading.value = false; }
    };

    const createEvent = async (resetEventForm) => {
        if (!isLoggedIn.value) { showToast('請先登入', 'error'); return; }
        eventSubmitting.value = true;
        try {
            const response = await api.post('/events', eventForm);
            showToast('活動建立成功！', 'success');
            resetEventForm(); await loadEvents(); navigateTo('events');
        } catch (error) { showToast('建立失敗', 'error'); } finally { eventSubmitting.value = false; }
    };

    const joinEvent = async (eventId) => {
        if (!isLoggedIn.value) { showToast('請先登入', 'error'); navigateTo('auth'); return; }
        try {
            await api.post(`/events/${eventId}/join`);
            showToast('報名成功！', 'success'); await loadEvents();
        } catch (error) { showToast('報名失敗', 'error'); }
    };

    const leaveEvent = async (eventId) => {
        try {
            await api.post(`/events/${eventId}/leave`);
            showToast('已取消報名', 'info'); await loadEvents();
        } catch (error) { showToast('取消失敗', 'error'); }
    };

    return { events, eventsLoading, eventSubmitting, eventForm, loadEvents, createEvent, joinEvent, leaveEvent };
};

const useProfile = (isLoggedIn, currentUser, showToast, navigateTo) => {
    const profileData = reactive({
        user: { player: null },
        stats: { followers_count: 0, following_count: 0, likes_count: 0, events_count: 0 },
        status: { is_following: false, is_liked: false, is_me: false }
    });
    const profileTab = ref('active');
    const profileEvents = ref([]);
    const profileEventsPage = ref(1);
    const profileEventsHasMore = ref(false);
    const isEditingProfile = ref(false);
    const profileForm = reactive({ name: '', gender: '', region: '', bio: '' });

    const loadProfile = async (userId, loadProfileEvents) => {
        try {
            const response = await api.get(`/profile/${userId}`);
            Object.assign(profileData, response.data);
            if (response.data.status.is_me) {
                Object.assign(profileForm, {
                    name: response.data.user.name, gender: response.data.user.gender,
                    region: response.data.user.region, bio: response.data.user.bio
                });
            }
            profileEventsPage.value = 1;
            if (loadProfileEvents) loadProfileEvents(false);
        } catch (error) { showToast('載入失敗', 'error'); }
    };

    const loadProfileEvents = async (append = false) => {
        if (!profileData.user.id) return;
        try {
            const response = await api.get(`/profile/${profileData.user.id}/events`, {
                params: { type: profileTab.value, page: profileEventsPage.value }
            });
            const data = response.data.data || [];
            profileEvents.value = append ? [...profileEvents.value, ...data] : data;
            profileEventsHasMore.value = response.data.next_page_url !== null;
            if (profileEventsHasMore.value) profileEventsPage.value++;
        } catch (error) {}
    };

    const saveProfile = async (loadProfile) => {
        try {
            const response = await api.post('/profile/update', profileForm);
            if (response.data.user) {
                currentUser.value = response.data.user;
                localStorage.setItem('auth_user', JSON.stringify(response.data.user));
                await loadProfile(currentUser.value.uid);
                isEditingProfile.value = false;
                showToast('個人資料已更新', 'success');
            }
        } catch (error) { showToast('儲存失敗', 'error'); }
    };

    return { profileData, profileTab, profileEvents, profileEventsHasMore, isEditingProfile, profileForm, loadProfile, loadProfileEvents, saveProfile };
};

const useMessages = (isLoggedIn, currentUser, showToast) => {
    const messages = ref([]);
    const loadMessages = async () => {
        if (!isLoggedIn.value) return;
        try {
            const response = await api.get('/messages');
            if (response.data.success) {
                const data = response.data.data;
                messages.value = Array.isArray(data.data) ? data.data : (Array.isArray(data) ? data : []);
            }
        } catch (error) {}
    };

    const markMessageRead = async (messageId) => {
        try {
            await api.put(`/messages/${messageId}/read`);
            const msg = messages.value.find(m => m.id === messageId);
            if (msg) { msg.read_at = new Date().toISOString(); msg.unread = false; }
        } catch (error) {}
    };

    return { messages, loadMessages, markMessageRead };
};

const useCardCreation = (form, showToast) => {
    const currentStep = ref(1);
    const stepAttempted = reactive({});
    const isAdjustingPhoto = ref(false);
    const isAdjustingSig = ref(false);
    const isCapturing = ref(false);

    const canProceedStep1 = computed(() => !!form.photo);
    const canProceedStep2 = computed(() => !!form.level && !!form.handed && !!form.backhand);
    const canProceedStep3 = computed(() => true); // Intro is optional

    const canGoToStep = (targetStep) => {
        if (targetStep === 1) return true;
        if (targetStep === 2) return canProceedStep1.value;
        if (targetStep === 3) return canProceedStep1.value && canProceedStep2.value;
        if (targetStep === 4) return canProceedStep1.value && canProceedStep2.value && canProceedStep3.value;
        return false;
    };

    const tryNextStep = () => {
        stepAttempted[currentStep.value] = true;
        if (currentStep.value === 1 && !canProceedStep1.value) { showToast('請上傳照片', 'error'); return; }
        if (currentStep.value === 2 && !canProceedStep2.value) { showToast('請選擇 NTRP 等級和技術設定', 'error'); return; }
        currentStep.value++;
    };

    const tryGoToStep = (targetStep) => {
        if (canGoToStep(targetStep)) currentStep.value = targetStep;
        else {
            if (targetStep >= 2 && !canProceedStep1.value) showToast('請先完成第一步：上傳照片', 'warning');
            else if (targetStep >= 3 && !canProceedStep2.value) showToast('請先完成第二步：設定等級與技術', 'warning');
        }
    };

    return { currentStep, stepAttempted, isAdjustingPhoto, isAdjustingSig, isCapturing, canProceedStep1, canProceedStep2, canProceedStep3, canGoToStep, tryNextStep, tryGoToStep };
};

const useDrag = (form) => {
    const dragInfo = reactive({ isDragging: false, target: null, startX: 0, startY: 0, initialX: 0, initialY: 0 });

    const startDrag = (e, target) => {
        dragInfo.isDragging = true;
        dragInfo.target = target;
        const clientX = e.clientX || (e.touches && e.touches[0].clientX);
        const clientY = e.clientY || (e.touches && e.touches[0].clientY);
        dragInfo.startX = clientX;
        dragInfo.startY = clientY;
        if (target === 'photo') {
            dragInfo.initialX = form.photoX;
            dragInfo.initialY = form.photoY;
        } else {
            dragInfo.initialX = form.sigX;
            dragInfo.initialY = form.sigY;
        }
    };

    const handleDrag = (e) => {
        if (!dragInfo.isDragging) return;
        const clientX = e.clientX || (e.touches && e.touches[0].clientX);
        const clientY = e.clientY || (e.touches && e.touches[0].clientY);
        const dx = clientX - dragInfo.startX;
        const dy = clientY - dragInfo.startY;
        if (dragInfo.target === 'photo') {
            form.photoX = dragInfo.initialX + dx;
            form.photoY = dragInfo.initialY + dy;
        } else {
            form.sigX = dragInfo.initialX + dx;
            form.sigY = dragInfo.initialY + dy;
        }
    };

    const stopDrag = () => { dragInfo.isDragging = false; dragInfo.target = null; };

    return { dragInfo, startDrag, handleDrag, stopDrag };
};

const useCapture = (showToast) => {
    const isCapturing = ref(false);

    const captureCardImage = async (cardContainer) => {
        if (typeof html2canvas === 'undefined') {
            showToast('截圖組件載入失敗', 'error');
            return null;
        }
        const cardEl = cardContainer || document.querySelector('.capture-target');
        if (!cardEl) return null;
        
        isCapturing.value = true;
        const originalStyle = cardEl.getAttribute('style') || '';
        const mergedLayer = cardEl.querySelector('.merged-photo-layer');
        const originalMergedDisplay = mergedLayer ? mergedLayer.style.display : '';
        
        try {
            if (mergedLayer) mergedLayer.style.display = 'none';
            const targetWidth = 320;
            const targetHeight = (targetWidth / 2.5) * 3.8;
            
            cardEl.style.width = `${targetWidth}px`;
            cardEl.style.height = `${targetHeight}px`;
            cardEl.style.position = 'fixed';
            cardEl.style.top = '0';
            cardEl.style.left = '0';
            cardEl.style.zIndex = '9999';

            await new Promise(resolve => setTimeout(resolve, 100));

            const canvas = await html2canvas(cardEl, {
                useCORS: true, allowTaint: true, scale: 2, width: targetWidth, height: targetHeight, logging: false
            });

            return canvas.toDataURL('image/png');
        } catch (e) {
            return null;
        } finally {
            cardEl.setAttribute('style', originalStyle);
            if (mergedLayer) mergedLayer.style.display = originalMergedDisplay;
            isCapturing.value = false;
        }
    };

    return { isCapturing, captureCardImage };
};


