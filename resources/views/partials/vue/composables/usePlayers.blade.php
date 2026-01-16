// --- usePlayers Composable ---
// 球友列表、球友卡 CRUD

const usePlayers = (isLoggedIn, currentUser, showToast, navigateTo, showConfirm, loadProfile, form) => {
    const players = ref([]);
    const myPlayers = ref([]);
    const isPlayersLoading = ref(false);
    const playersPagination = ref({ total: 0, current_page: 1, last_page: 1, per_page: 12 });
    
    // 分頁快取機制
    const playersCache = reactive(new Map());
    let lastCacheKey = '';

    const getCacheKey = (params) => {
        return JSON.stringify({
            search: params.search || '',
            region: params.region || '',
            gender: params.gender || '',
            level_min: params.level_min || '',
            level_max: params.level_max || '',
            handed: params.handed || '',
            backhand: params.backhand || '',
            page: params.page || 1
        });
    };

    const loadPlayers = async (params = {}) => {
        const cacheKey = getCacheKey(params);
        
        // 檢查快取 (30秒內有效)
        const cached = playersCache.get(cacheKey);
        if (cached && Date.now() - cached.timestamp < 30000) {
            players.value = cached.data;
            playersPagination.value = cached.pagination;
            return;
        }

        isPlayersLoading.value = true;
        try {
            // Default per_page to 12 for better grid layout (4x3)
            const response = await api.get('/players', { 
                params: { per_page: 12, ...params } 
            });
            if (response.data.success) {
                const data = response.data.data;
                if (data.data) {
                    // Paginated response
                    players.value = data.data.filter(p => p && p.id);
                    playersPagination.value = {
                        total: data.total,
                        current_page: data.current_page,
                        last_page: data.last_page,
                        per_page: data.per_page
                    };
                } else {
                    // Non-paginated fallback
                    players.value = (Array.isArray(data) ? data : []).filter(p => p && p.id);
                    playersPagination.value = { total: players.value.length, current_page: 1, last_page: 1, per_page: 1000 };
                }
                
                // 存入快取
                playersCache.set(cacheKey, {
                    data: [...players.value],
                    pagination: { ...playersPagination.value },
                    timestamp: Date.now()
                });
                lastCacheKey = cacheKey;
            }
        } catch (error) {} finally { isPlayersLoading.value = false; }
    };

    // 清除快取（當資料變更時呼叫）
    const clearPlayersCache = () => {
        playersCache.clear();
    };

    const loadMyCards = async () => {
        if (!isLoggedIn.value) return;
        try {
            const response = await api.get('/my-cards');
            if (response.data.success) myPlayers.value = response.data.data;
        } catch (error) {}
    };

    const saveCard = async (resetForm) => {
        if (!isLoggedIn.value) { showToast('請先登入', 'error'); navigateTo('auth'); return; }
        
        // Internal validation check
        const canProceedStep1 = !!form.photo;
        const canProceedStep2 = !!form.level && !!form.handed && !!form.backhand;
        const canProceedStep3 = true; // Intro is optional

        if (!canProceedStep1 || !canProceedStep2 || !canProceedStep3) { 
            showToast('請確認必填欄位', 'error'); 
            return; 
        }
        
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
                // Sync user info back to currentUser (連動更新個人資料)
                if (currentUser.value) {
                    currentUser.value.name = form.name;
                    currentUser.value.gender = form.gender;
                    currentUser.value.region = form.region;
                    localStorage.setItem('auth_user', JSON.stringify(currentUser.value));
                }
                // 清除快取確保資料最新
                clearPlayersCache();
                await loadPlayers(); await loadMyCards();
                
                if (resetForm) resetForm();
                
                // If we were editing from profile, go back to profile
                if (form.id) {
                    navigateTo('profile', false, currentUser.value.uid);
                } else {
                    navigateTo('list');
                }
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        } catch (error) {
            console.error('Save card error:', error);
            if (error.response?.status === 413) {
                showToast('照片檔案過大，請嘗試更換較小的照片或重新上傳', 'error');
            } else {
                const msg = error.response?.data?.error || error.response?.data?.message || '儲存失敗';
                showToast(msg, 'error');
            }
        }
    };

    const deleteCard = (cardId, view) => {
        showConfirm({
            title: '刪除球友卡', message: '確定要刪除嗎？', confirmText: '確認刪除', type: 'danger',
            onConfirm: async () => {
                try {
                    await api.delete(`/players/${cardId}`);
                    // 清除快取確保資料最新
                    clearPlayersCache();
                    await loadPlayers(); await loadMyCards();
                    if (view.value === 'profile' && loadProfile) loadProfile(currentUser.value.uid);
                } catch (error) {
                    const msg = error.response?.data?.error || error.response?.data?.message || '刪除失敗';
                    showToast(msg, 'error');
                }
            }
        });
    };

    return { players, myPlayers, isPlayersLoading, playersPagination, loadPlayers, loadMyCards, saveCard, deleteCard, clearPlayersCache };
};
