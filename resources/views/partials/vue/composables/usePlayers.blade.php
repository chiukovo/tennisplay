// --- usePlayers Composable ---
// 球友列表、球友卡 CRUD

const usePlayers = (isLoggedIn, currentUser, showToast, navigateTo, showConfirm, loadProfile, form) => {
    const players = ref([]);
    const myPlayers = ref([]);
    const isPlayersLoading = ref(false);
    const playersPagination = ref({ total: 0, current_page: 1, last_page: 1, per_page: 12 });

    const loadPlayers = async (params = {}) => {
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
                showToast(form.id ? '球友卡已更新' : '球友卡建立成功！', 'success');
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
            const msg = error.response?.data?.error || error.response?.data?.message || '儲存失敗';
            showToast(msg, 'error');
        }
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
                } catch (error) {
                    const msg = error.response?.data?.error || error.response?.data?.message || '刪除失敗';
                    showToast(msg, 'error');
                }
            }
        });
    };

    return { players, myPlayers, isPlayersLoading, playersPagination, loadPlayers, loadMyCards, saveCard, deleteCard };
};
