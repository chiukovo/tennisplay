// --- useNavigation Composable ---
// 路由管理、History API 整合

const useNavigation = (routes, routePaths, viewTitles, showToast, applyDefaultFilters, isLoggedIn, currentUser) => {
    const view = ref('home');
    const lastNavigationTap = ref(0); // 記錄最後一次點擊導航的時間，用於強制刷新

    watch(view, (newView) => {
        if (viewTitles[newView]) document.title = viewTitles[newView];
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    const navigateTo = (viewName, shouldReset = true, uid = null, resetForm = null, resetEventForm = null, loadProfile = null) => {
        // If already on home and clicking home, refresh the page
        if (viewName === 'home' && view.value === 'home') {
            window.location.href = '/';
            return;
        }

        // Basic protection for internal calls (though usually handled by wrapped navigateTo)
        if (!isLoggedIn.value && (viewName === 'create' || (viewName === 'profile' && !uid))) {
            view.value = 'auth';
            window.history.pushState({ view: 'auth' }, '', routePaths['auth'] || '/auth');
            return;
        }

        if (viewName === 'create' && shouldReset && resetForm) resetForm();
        if (viewName === 'create-event' && shouldReset && resetEventForm) resetEventForm();
        
        // Load profile data when navigating to profile
        if (viewName === 'profile' && uid && loadProfile) {
            loadProfile(uid);
        }
        
        if (applyDefaultFilters) applyDefaultFilters(viewName);
        
        lastNavigationTap.value = Date.now();
        view.value = viewName;

        // Expose to window for Capacitor Deep Linking
        window.AppNavigate = (v, s = true, u = null) => navigateTo(v, s, u, resetForm, resetEventForm, loadProfile);
        
        let path = routePaths[viewName] || '/';
        if (viewName === 'profile' && uid) path = `/profile/${uid}`;
        if (viewName === 'messages' && uid) path = `/messages/${uid}`;
        
        window.history.pushState({ view: viewName, uid: uid }, '', path);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    // Initial exposure
    window.AppNavigate = (v, s = true, u = null) => navigateTo(v, s, u);

    // Consume pending navigation from mobile.js (Cold Start)
    if (window.PendingAppNavigate) {
        const data = window.PendingAppNavigate;
        setTimeout(() => {
            if (data.event_id) navigateTo('events', true, data.event_id);
            else if (data.uid) navigateTo('profile', true, data.uid);
            else if (data.chat_uid) navigateTo('messages', true, data.chat_uid);
            delete window.PendingAppNavigate;
        }, 500);
    }

    const parseRoute = (loadProfile, resetForm, resetEventForm, openChatByUid) => {
        const path = window.location.pathname;
        let viewName = routes[path];
        let derivedUid = null;

        if (!viewName) {
            const matchedKey = Object.keys(routes).find(r => r !== '/' && path.endsWith(r));
            if (matchedKey) viewName = routes[matchedKey];
        }

        if (path.includes('/profile/')) {
            const parts = path.split('/');
            derivedUid = parts[parts.length - 1];
            if (derivedUid) {
                viewName = 'profile';
                if (loadProfile) loadProfile(derivedUid);
            }
        }

        if (path.includes('/messages/')) {
            const parts = path.split('/');
            derivedUid = parts[parts.length - 1];
            if (derivedUid) {
                viewName = 'messages';
                if (openChatByUid) openChatByUid(derivedUid);
            }
        }

        if (!viewName) viewName = 'home';

        // Protection for direct URL access
        if (!isLoggedIn.value && (viewName === 'create' || viewName === 'messages' || (viewName === 'profile' && !derivedUid))) {
            if (showToast) showToast('請先登入以訪問此功能', 'warning');
            viewName = 'home';
            window.history.replaceState({ view: 'home' }, '', '/');
        }

        if (applyDefaultFilters) applyDefaultFilters(viewName);
        view.value = viewName;
        if (viewName === 'create' && resetForm) resetForm();
        if (viewName === 'create-event' && resetEventForm) resetEventForm();
        return viewName;
    };

    const goBack = (fallbackView = 'home') => {
        if (window.history.length > 1) {
            window.history.back();
        } else {
            navigateTo(fallbackView);
        }
    };

    return { view, lastNavigationTap, navigateTo, parseRoute, goBack };
};
