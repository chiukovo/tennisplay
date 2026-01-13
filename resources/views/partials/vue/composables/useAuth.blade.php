// --- useAuth Composable ---
// 登入/登出、使用者狀態管理

const useAuth = (showToast, navigateTo, initSettings, isLoggedIn, currentUser, settingsForm) => {
    const isLoginMode = ref(true);
    const showUserMenu = ref(false);
    const isSavingSettings = ref(false);
    // LINE 登入時的 Loading 狀態 - 初始化時就檢測 URL 參數
    const hasLineToken = new URLSearchParams(window.location.search).has('line_token');
    const isAuthLoading = ref(hasLineToken); // 如果有 line_token 參數，立即顯示 Loading

    const checkAuth = (loadMessages, loadMyCards) => {
        const urlParams = new URLSearchParams(window.location.search);
        const lineToken = urlParams.get('line_token');
        const lineUser = urlParams.get('line_user');
        
        if (lineToken && lineUser) {
            isAuthLoading.value = true; // 開始 Loading
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
                isAuthLoading.value = false; // 結束 Loading
                return;
            } catch (e) {
                isAuthLoading.value = false;
            }
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
            
            // 背景同步獲取最新用戶資料 (解決跨設備照片 URL 過期問題)
            refreshUserData();
        }
    };

    // 從後端獲取最新用戶資料並更新 localStorage
    const refreshUserData = async () => {
        try {
            const response = await api.get('/user');
            // 注意：API 返回格式是 { success: true, user: {...} }
            if (response.data?.user) {
                currentUser.value = response.data.user;
                localStorage.setItem('auth_user', JSON.stringify(response.data.user));
            }
        } catch (error) {
            // Token 無效時自動登出
            if (error.response?.status === 401) {
                localStorage.removeItem('auth_token');
                localStorage.removeItem('auth_user');
                isLoggedIn.value = false;
                currentUser.value = null;
            }
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

    return { isLoginMode, showUserMenu, isSavingSettings, isAuthLoading, checkAuth, logout, saveSettings };
};
