// --- useNavigation Composable ---
// 路由管理、History API 整合

const useNavigation = (routes, routePaths, viewTitles, showToast, applyDefaultFilters, isLoggedIn, currentUser) => {
    const view = ref('home');

    watch(view, (newView) => {
        if (viewTitles[newView]) document.title = viewTitles[newView];
    });

    const navigateTo = (viewName, shouldReset = true, uid = null, resetForm = null, resetEventForm = null, loadProfile = null) => {
        if (viewName === 'create' && isLoggedIn.value) {
            if (!currentUser.value?.gender || !currentUser.value?.region) {
                showToast('請先完成基本資料（性別、地區）再建立球友卡', 'warning');
                viewName = 'profile'; uid = currentUser.value?.uid || currentUser.value?.id; shouldReset = false;
            }
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
        if (!viewName) {
            const matchedKey = Object.keys(routes).find(r => r !== '/' && path.endsWith(r));
            if (matchedKey) viewName = routes[matchedKey];
        }
        if (!viewName && path.includes('/profile/')) {
            const parts = path.split('/');
            const uid = parts[parts.length - 1];
            if (uid) {
                viewName = 'profile';
                if (loadProfile) loadProfile(uid);
            }
        }
        if (!viewName) viewName = 'home';
        view.value = viewName;
        if (viewName === 'create' && resetForm) resetForm();
        if (viewName === 'create-event' && resetEventForm) resetEventForm();
        return viewName;
    };

    return { view, navigateTo, parseRoute };
};
