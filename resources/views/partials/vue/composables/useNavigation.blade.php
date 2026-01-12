// --- useNavigation Composable ---
// 路由管理、History API 整合

const useNavigation = (routes, routePaths, viewTitles, showToast, applyDefaultFilters, isLoggedIn, currentUser) => {
    const view = ref('home');

    watch(view, (newView) => {
        if (viewTitles[newView]) document.title = viewTitles[newView];
    });

    const navigateTo = (viewName, shouldReset = true, uid = null, resetForm = null, resetEventForm = null, loadProfile = null) => {
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
        
        view.value = viewName;
        let path = routePaths[viewName] || '/';
        if (viewName === 'profile' && uid) path = `/profile/${uid}`;
        
        window.history.pushState({ view: viewName, uid: uid }, '', path);
        if (applyDefaultFilters) applyDefaultFilters(viewName);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    const parseRoute = (loadProfile, resetForm, resetEventForm) => {
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

        if (!viewName) viewName = 'home';

        // Protection for direct URL access
        if (!isLoggedIn.value && (viewName === 'create' || (viewName === 'profile' && !derivedUid))) {
            if (showToast) showToast('請先登入以訪問此功能', 'warning');
            viewName = 'home';
            window.history.replaceState({ view: 'home' }, '', '/');
        }

        view.value = viewName;
        if (viewName === 'create' && resetForm) resetForm();
        if (viewName === 'create-event' && resetEventForm) resetEventForm();
        return viewName;
    };

    return { view, navigateTo, parseRoute };
};
