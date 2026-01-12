// --- useProfile Composable ---
// 個人資料、編輯功能

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
    const profileComments = ref([]);
    const followingUsers = ref([]);
    const followerUsers = ref([]);
    const likedPlayers = ref([]);
    const playerCommentDraft = ref('');

    const loadProfile = async (userId, loadProfileEventsCallback, autoEdit = false) => {
        try {
            const response = await api.get(`/profile/${userId}`);
            Object.assign(profileData, response.data);
            if (response.data.status.is_me) {
                Object.assign(profileForm, {
                    name: response.data.user.name, gender: response.data.user.gender,
                    region: response.data.user.region, bio: response.data.user.bio
                });
                if (autoEdit) isEditingProfile.value = true;
            }
            profileEventsPage.value = 1;
            if (loadProfileEventsCallback) loadProfileEventsCallback(false);
        } catch (error) { 
            showToast('會員資料不存在或載入失敗', 'error');
            navigateTo('home');
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
            showToast('留言成功', 'success');
        } catch (error) { showToast('發送失敗', 'error'); }
    };

    const saveProfile = async () => {
        try {
            const response = await api.post('/profile/update', profileForm);
            if (response.data.user) {
                currentUser.value = response.data.user;
                localStorage.setItem('auth_user', JSON.stringify(response.data.user));
                await loadProfile(currentUser.value.uid, loadProfileEvents);
                isEditingProfile.value = false;
                showToast('個人資料已更新', 'success');
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
            showToast(response.data.message, 'success');
        } catch (error) { showToast('操作失敗', 'error'); }
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
            showToast(response.data.message, 'success');
        } catch (error) { showToast('操作失敗', 'error'); }
    };

    const openProfile = (uid) => {
        loadProfile(uid, loadProfileEvents);
        navigateTo('profile', true, uid);
    };

    return { 
        profileData, profileTab, profileEvents, profileEventsHasMore, isEditingProfile, profileForm, 
        profileComments, followingUsers, followerUsers, likedPlayers, playerCommentDraft,
        loadProfile, loadProfileEvents, saveProfile, openProfile, toggleFollow, toggleLike,
        loadProfileComments, loadFollowing, loadFollowers, loadLikedPlayers, submitPlayerComment
    };
};
