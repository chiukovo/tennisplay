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
            is_coach: params.is_coach || false,
            coach_price_min: params.coach_price_min || '',
            coach_price_max: params.coach_price_max || '',
            coach_method: params.coach_method || '',
            coach_tag: params.coach_tag || '',
            coach_location: params.coach_location || '',
            page: params.page || 1,
            sort: params.sort || 'popular'
        });
    };

    const isSubmitting = ref(false);

    const loadPlayers = async (params = {}, force = false) => {
        const page = params.page || 1;
        const sort = params.sort || 'popular';
        const shouldMixPopular = sort === 'popular' && page === 1;
        const cacheKey = getCacheKey({ ...params, sort: shouldMixPopular ? 'popular-mixed' : sort });
        
        // 如果不是強制刷新，則檢查快取
        if (!force) {
            const cached = playersCache.get(cacheKey);
            if (cached && Date.now() - cached.timestamp < 10000) {
                players.value = cached.data;
                playersPagination.value = cached.pagination;
                return;
            }
        }

        // 強制刷新或快取過期，執行請求並更新快取
        isPlayersLoading.value = true;
        try {
            const baseParams = { per_page: 12, ...params };

            const normalize = (resp) => {
                if (!resp?.data?.success) return { items: [], pagination: null };
                const data = resp.data.data;
                if (data?.data) {
                    return {
                        items: (data.data || []).filter(p => p && p.id),
                        pagination: {
                            total: data.total,
                            current_page: data.current_page,
                            last_page: data.last_page,
                            per_page: data.per_page
                        }
                    };
                }
                const items = (Array.isArray(data) ? data : []).filter(p => p && p.id);
                return { items, pagination: { total: items.length, current_page: 1, last_page: 1, per_page: 1000 } };
            };

            if (shouldMixPopular) {
                const [popularRes, newestRes] = await Promise.all([
                    api.get('/players', { params: baseParams }),
                    api.get('/players', { params: { ...baseParams, sort: 'newest' } })
                ]);

                const popular = normalize(popularRes);
                const newest = normalize(newestRes);

                const perPage = popular.pagination?.per_page || 12;
                const popularQuota = Math.round(perPage * 0.7);
                const newestQuota = perPage - popularQuota;

                const seen = new Set();
                const mixed = [];
                const pushUnique = (arr, limit) => {
                    for (const p of arr) {
                        if (mixed.length >= limit) break;
                        if (p?.id && !seen.has(p.id)) {
                            seen.add(p.id);
                            mixed.push(p);
                        }
                    }
                };

                pushUnique(popular.items, popularQuota);
                pushUnique(newest.items, popularQuota + newestQuota);
                if (mixed.length < perPage) pushUnique(popular.items, perPage);

                players.value = mixed;
                playersPagination.value = popular.pagination || { total: mixed.length, current_page: 1, last_page: 1, per_page: perPage };
            } else {
                const response = await api.get('/players', { params: baseParams });
                const normalized = normalize(response);
                players.value = normalized.items;
                playersPagination.value = normalized.pagination || { total: players.value.length, current_page: 1, last_page: 1, per_page: 1000 };
            }

            playersCache.set(cacheKey, {
                data: [...players.value],
                pagination: { ...playersPagination.value },
                timestamp: Date.now()
            });
            lastCacheKey = cacheKey;
        } catch (error) {} finally { isPlayersLoading.value = false; }
    };

    // 清除快取（當資料變更時呼叫）
    const clearPlayersCache = () => {
        playersCache.clear();
    };

    // 載入首頁隨機球友
    const randomPlayers = ref([]);
    const loadRandomPlayers = async () => {
        try {
            const response = await api.get('/players/random');
            if (response.data.success) {
                randomPlayers.value = response.data.data.filter(p => p && p.id);
            }
        } catch (error) {
            console.error('Load random players error:', error);
        }
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
        if (isSubmitting.value) return;

        // Internal validation check
        const canProceedStep1 = !!form.photo;
        const canProceedStep2 = !!form.level && !!form.handed && !!form.backhand;
        const canProceedStep3 = true; // Intro is optional

        if (!canProceedStep1 || !canProceedStep2 || !canProceedStep3) { 
            showToast('請確認必填欄位', 'error'); 
            return; 
        }
        
        isSubmitting.value = true;
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
        } finally { isSubmitting.value = false; }
    };

    const deleteCard = (cardId, view) => {
        showConfirm({
            title: '刪除球友卡', message: '確定要刪除嗎？', confirmText: '確認刪除', type: 'danger',
            onConfirm: async () => {
                if (isSubmitting.value) return;
                isSubmitting.value = true;
                try {
                    await api.delete(`/players/${cardId}`);
                    // 清除快取確保資料最新
                    clearPlayersCache();
                    await loadPlayers(); await loadMyCards();
                    if (view.value === 'profile' && loadProfile) loadProfile(currentUser.value.uid);
                } catch (error) {
                    const msg = error.response?.data?.error || error.response?.data?.message || '刪除失敗';
                    showToast(msg, 'error');
                } finally { isSubmitting.value = false; }
            }
        });
    };

    return { players, myPlayers, randomPlayers, isPlayersLoading, isSubmitting, playersPagination, loadPlayers, loadRandomPlayers, loadMyCards, saveCard, deleteCard, clearPlayersCache };
};
