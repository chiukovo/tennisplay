// --- useAuth Composable ---
// 登入/登出、使用者狀態管理

const useAuth = (showToast, navigateTo, initSettings, isLoggedIn, currentUser, settingsForm) => {
    const isLoginMode = ref(true);
    const showUserMenu = ref(false);
    const isSavingSettings = ref(false);
    // LINE 登入時的 Loading 狀態 - 初始化時就檢測 URL 參數
    const hasLineToken = new URLSearchParams(window.location.search).has('line_token');
    const isAuthLoading = ref(hasLineToken); // 如果有 line_token 參數，立即顯示 Loading

    // 安全機制：如果在 5 秒內沒有完成驗證（可能 JS 錯誤或參數遺失），強制關閉 Loading
    if (hasLineToken) {
        if (lineToken && lineUser) {
            isAuthLoading.value = true;
            try {
                const userData = JSON.parse(lineUser);
                localStorage.setItem('auth_token', lineToken);
                localStorage.setItem('auth_user', lineUser);
                // 收到 user 指示：不用倒數，直接一直 loading，因為完成會跳轉 (重整)
                // 使用 location.href 進行重整，讓 Loading 畫面持續直到頁面刷新
                window.location.href = '/';
            } catch (e) {
                console.error('Login error:', e);
                // 只有失敗時才關閉 Loading
                isAuthLoading.value = false;
                if (document.getElementById('auth-preloader')) document.getElementById('auth-preloader').style.display = 'none';
            }
        } else if (lineToken) {
            // 有 Token 但沒有 User 資料 (可能是 URL 截斷或其他錯誤)
            isAuthLoading.value = false;
            if (document.getElementById('auth-preloader')) document.getElementById('auth-preloader').style.display = 'none';
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
                settings: { 
                    default_region: settingsForm.default_region,
                    notify_line: settingsForm.notify_line
                }
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
