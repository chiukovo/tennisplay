// --- useProfile Composable ---
// 個人資料、編輯功能

const useProfile = (isLoggedIn, currentUser, showToast, navigateTo) => {
    const profileData = reactive({
        user: { player: null },
        stats: { followers_count: 0, following_count: 0, likes_count: 0, events_count: 0 },
        status: { is_following: false, is_liked: false, is_me: false }
    });
    const isProfileLoading = ref(true); // Added: loading state to prevent flash
    const profileTab = ref('comments');
    const profileEvents = ref([]);
    const profileEventsPage = ref(1);
    const profileEventsHasMore = ref(false);
    const isEditingProfile = ref(false);
    const profileForm = reactive({ name: '', gender: '', region: '', bio: '', level: '', handed: '', backhand: '', intro: '', fee: '' });
    const profileComments = ref([]);
    const followingUsers = ref([]);
    const followerUsers = ref([]);
    const likedPlayers = ref([]);
    const playerCommentDraft = ref('');
    
    // 多地區選擇
    const selectedProfileRegions = ref([]);
    
    // 監聽 profileForm.region 初始化已選地區
    watch(() => profileForm.region, (newVal) => {
        if (newVal) {
            selectedProfileRegions.value = newVal.split(',').filter(r => r.trim());
        } else {
            selectedProfileRegions.value = [];
        }
    }, { immediate: true });
    
    // 切換地區選擇
    const toggleProfileRegion = (region) => {
        const idx = selectedProfileRegions.value.indexOf(region);
        if (idx > -1) {
            selectedProfileRegions.value.splice(idx, 1);
        } else {
            selectedProfileRegions.value.push(region);
        }
        // 同步到 profileForm.region（逗點分隔）
        profileForm.region = selectedProfileRegions.value.join(',');
    };


    const loadProfile = async (userId, loadProfileEventsCallback, autoEdit = false) => {
        isProfileLoading.value = true;
        try {
            const response = await api.get(`/profile/${userId}`);
            Object.assign(profileData, response.data);
            if (response.data.status.is_me) {
                const p = response.data.user.player || {};
                Object.assign(profileForm, {
                    name: response.data.user.name, gender: response.data.user.gender,
                    region: response.data.user.region, bio: response.data.user.bio,
                    level: p.level || '', handed: p.handed || '', backhand: p.backhand || '',
                    intro: p.intro || '', fee: p.fee || ''
                });
                if (autoEdit) isEditingProfile.value = true;
            }
            profileEventsPage.value = 1;
            if (loadProfileEventsCallback) loadProfileEventsCallback(false);
            if (profileTab.value === 'comments') await loadProfileComments();
        } catch (error) { 
            showToast('會員資料不存在或載入失敗', 'error');
            navigateTo('home');
        } finally {
            isProfileLoading.value = false;
        }
    };

    const loadProfileEvents = async (append = false) => {
        const userId = profileData.user?.uid || profileData.user?.id;
        if (!userId) return;
        try {
            const response = await api.get(`/profile/${userId}/events`, {
                params: { type: profileTab.value, page: profileEventsPage.value }
            });
            const data = response.data.data || [];
            profileEvents.value = append ? [...profileEvents.value, ...data] : data;
            profileEventsHasMore.value = response.data.next_page_url !== null;
            if (profileEventsHasMore.value) profileEventsPage.value++;
        } catch (error) {}
    };

    const loadProfileComments = async () => {
        const playerId = profileData.user?.player?.id;
        if (!playerId) return;
        try {
            const response = await api.get(`/players/${playerId}/comments`);
            profileComments.value = response.data;
        } catch (error) {}
    };

    const loadFollowing = async () => {
        const userId = profileData.user?.uid;
        if (!userId) return;
        try {
            const response = await api.get(`/following/${userId}`);
            followingUsers.value = response.data;
        } catch (error) {}
    };

    const loadFollowers = async () => {
        const userId = profileData.user?.uid;
        if (!userId) return;
        try {
            const response = await api.get(`/followers/${userId}`);
            followerUsers.value = response.data;
        } catch (error) {}
    };

    const loadLikedPlayers = async () => {
        const userId = profileData.user?.uid;
        if (!userId) return;
        try {
            const response = await api.get(`/likes/${userId}`);
            likedPlayers.value = response.data;
        } catch (error) {}
    };

    const submitPlayerComment = async (playerId) => {
        if (!isLoggedIn.value) { showToast('請先登入', 'error'); navigateTo('auth'); return; }
        const text = playerCommentDraft.value.trim();
        if (!text) return;
        try {
            const response = await api.post(`/players/${playerId}/comments`, { content: text });
            profileComments.value.unshift(response.data.comment);
            playerCommentDraft.value = '';
        } catch (error) { showToast('發送失敗', 'error'); }
    };

    const deletePlayerComment = async (commentId) => {
        if (!isLoggedIn.value) { showToast('請先登入', 'error'); navigateTo('auth'); return; }
        if (!confirm('確定要刪除這則留言嗎？')) return;
        try {
            await api.delete(`/players/comments/${commentId}`);
            profileComments.value = profileComments.value.filter(c => c.id !== commentId);
            showToast('留言已刪除', 'success');
        } catch (error) {
            showToast('刪除失敗', 'error');
        }
    };

    const saveProfile = async () => {
        try {
            const response = await api.post('/profile/update', profileForm);
            if (response.data.user) {
                currentUser.value = response.data.user;
                localStorage.setItem('auth_user', JSON.stringify(response.data.user));
                await loadProfile(currentUser.value.uid, loadProfileEvents);
                isEditingProfile.value = false;
            }
        } catch (error) { showToast('儲存失敗', 'error'); }
    };

    const toggleFollow = async () => {
        if (!isLoggedIn.value) { showToast('請先登入', 'error'); navigateTo('auth'); return; }
        const userId = profileData.user.uid || profileData.user.id;
        try {
            const endpoint = profileData.status.is_following ? `/unfollow/${userId}` : `/follow/${userId}`;
            const response = await api.post(endpoint);
            profileData.status.is_following = !profileData.status.is_following;
            profileData.stats.followers_count = response.data.followers_count;
        } catch (error) {
            const msg = error.response?.data?.error || error.response?.data?.message || '操作失敗';
            showToast(msg, 'error');
        }
    };

    const toggleLike = async () => {
        if (!isLoggedIn.value) { showToast('請先登入', 'error'); navigateTo('auth'); return; }
        const playerId = profileData.user.player?.id;
        if (!playerId) return;
        try {
            const endpoint = profileData.status.is_liked ? `/unlike/${playerId}` : `/like/${playerId}`;
            const response = await api.post(endpoint);
            profileData.status.is_liked = !profileData.status.is_liked;
            profileData.stats.likes_count = response.data.likes_count;
        } catch (error) {
            const msg = error.response?.data?.error || error.response?.data?.message || '操作失敗';
            showToast(msg, 'error');
        }
    };

    const openProfile = (uid) => {
        // Set loading state before clearing data
        isProfileLoading.value = true;

        profileTab.value = 'comments';
        
        // Clear existing data to force refresh display
        profileData.user = { player: null };
        profileEvents.value = [];
        profileEventsPage.value = 1;
        isEditingProfile.value = false;
        
        // Load fresh data
        loadProfile(uid, loadProfileEvents);
        navigateTo('profile', true, uid);
    };

    return { 
        profileData, isProfileLoading, profileTab, profileEvents, profileEventsHasMore, isEditingProfile, profileForm, 
        profileComments, followingUsers, followerUsers, likedPlayers, playerCommentDraft,
        selectedProfileRegions, toggleProfileRegion,
        loadProfile, loadProfileEvents, saveProfile, openProfile, toggleFollow, toggleLike,
        loadProfileComments, loadFollowing, loadFollowers, loadLikedPlayers, submitPlayerComment, deletePlayerComment
    };
};
