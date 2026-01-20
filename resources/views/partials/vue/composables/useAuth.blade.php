// --- useAuth Composable ---
// 登入/登出、使用者狀態管理

const useAuth = (showToast, navigateTo, initSettings, isLoggedIn, currentUser, settingsForm, view) => {
    const isLoginMode = ref(true);
    const showUserMenu = ref(false);
    const isSavingSettings = ref(false);

    // 監聽路由變化，自動關閉選單
    if (view) {
        watch(view, () => {
            showUserMenu.value = false;
        });
    }

    // 點擊外部自動關閉選單
    const handleOutsideClick = (e) => {
        if (showUserMenu.value && !e.target.closest('.user-menu-container')) {
            showUserMenu.value = false;
        }
    };

    onMounted(() => {
        window.addEventListener('click', handleOutsideClick);
    });

    onUnmounted(() => {
        window.removeEventListener('click', handleOutsideClick);
    });
    // LINE 登入時的 Loading 狀態 - 初始化時就檢測 URL 參數
    const hasLineToken = new URLSearchParams(window.location.search).has('line_token');
    const isAuthLoading = ref(hasLineToken); // 如果有 line_token 參數，立即顯示 Loading

    // 安全機制：如果在 5 秒內沒有完成驗證（可能 JS 錯誤或參數遺失），強制關閉 Loading
    if (hasLineToken) {
        setTimeout(() => {
            if (isAuthLoading.value) {
                isAuthLoading.value = false;
                console.warn('Auth loading timed out, forcing close.');
            }
        }, 5000);
    }

    const checkAuth = async (loadMessages, loadMyCards) => {
        const urlParams = new URLSearchParams(window.location.search);
        const lineToken = urlParams.get('line_token');
        const lineUser = urlParams.get('line_user');
        
        if (lineToken) {
            isAuthLoading.value = true;
            localStorage.setItem('auth_token', lineToken);
            
            let success = false;

            // 1. 嘗試快速登入 (如果有 line_user 參數)
            if (lineUser) {
                try {
                    const userData = JSON.parse(lineUser);
                    localStorage.setItem('auth_user', lineUser);
                    currentUser.value = userData;
                    isLoggedIn.value = true;
                    success = true;
                } catch (e) {
                    console.error('JSON parse error:', e);
                }
            }

            // 2. 如果沒有 line_user 或解析失敗，嘗試從後端獲取 (Mobile Fallback)
            if (!success) {
                await refreshUserData(); // 這會更新 currentUser
                if (currentUser.value) {
                    isLoggedIn.value = true;
                    success = true;
                }
            }

            // 3. 結果處理
            if (success) {
                if (initSettings) initSettings();
                // 登入成功，強制重整跳回首頁
                window.location.href = '/';
            } else {
                // 登入失敗 (Token 無效或網路錯誤)
                console.error('Login failed after retry');
                isAuthLoading.value = false;
                localStorage.removeItem('auth_token'); // 清除無效 Token
                if (document.getElementById('auth-preloader')) document.getElementById('auth-preloader').style.display = 'none';
            }
        } else {
            // 一般頁面載入檢查 (無 URL 參數)
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
                
                refreshUserData();
            }
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
        navigateTo('home');
    };

    const saveSettings = async () => {
        if (isSavingSettings.value) return;
        isSavingSettings.value = true;
        try {
            const response = await api.put('/user/settings', {
                settings: { 
                    default_region: settingsForm.default_region,
                    notify_line: settingsForm.notify_line,
                    notify_event: settingsForm.notify_event
                }
            });
            if (response.data.success) {
                currentUser.value = response.data.data;
                localStorage.setItem('auth_user', JSON.stringify(currentUser.value));
            }
        } catch (error) {
            showToast('儲存失敗', 'error');
        } finally {
            isSavingSettings.value = false;
        }
    };

    const loginWithLine = async () => {
        // Detect if running in Capacitor (Mobile)
        const isMobile = typeof window.MobileLineLogin !== 'undefined';

        if (isMobile) {
            isAuthLoading.value = true;
            try {
                // Call Native Plugin
                const result = await window.MobileLineLogin.login();
                
                if (result && result.idToken) {
                    // Send Token to Backend
                    const response = await api.post('/auth/line/native', {
                        id_token: result.idToken,
                        access_token: result.accessToken
                    });

                    if (response.data.success) {
                        const { token, user } = response.data;
                        localStorage.setItem('auth_token', token);
                        localStorage.setItem('auth_user', JSON.stringify(user));
                        currentUser.value = user;
                        isLoggedIn.value = true;
                        
                        showToast('登入成功', 'success');
                        navigateTo('home');
                    } else {
                        showToast(response.data.message || '登入失敗', 'error');
                    }
                }
            } catch (error) {
                console.error('Native LINE Login Error:', error);
                showToast('LINE 登入失敗', 'error');
            } finally {
                isAuthLoading.value = false;
            }
        } else {
            // Standard Web Flow
            window.location.href = '/auth/line';
        }
    };

    return { isLoginMode, showUserMenu, isSavingSettings, isAuthLoading, checkAuth, logout, saveSettings, loginWithLine };
};
