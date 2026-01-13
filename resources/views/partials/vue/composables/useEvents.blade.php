// --- useEvents Composable ---
// 活動管理、報名/取消

const useEvents = (isLoggedIn, showToast, navigateTo, formatLocalDateTime, eventForm, resetEventForm) => {
    const events = ref([]);
    const eventsLoading = ref(false);
    const eventSubmitting = ref(false);
    const eventsPagination = ref({ total: 0, current_page: 1, last_page: 1, per_page: 12 });

    const loadEvents = async (params = {}) => {
        eventsLoading.value = true;
        try {
            const response = await api.get('/events', {
                params: { per_page: 12, ...params }
            });
            if (response.data.success) {
                const data = response.data.data;
                if (data.data) {
                    // Paginated response
                    events.value = Array.isArray(data.data) ? data.data : [];
                    eventsPagination.value = {
                        total: data.total,
                        current_page: data.current_page,
                        last_page: data.last_page,
                        per_page: data.per_page
                    };
                } else {
                    // Non-paginated fallback
                    events.value = Array.isArray(data) ? data : [];
                    eventsPagination.value = { total: events.value.length, current_page: 1, last_page: 1, per_page: 100 };
                }
            }
        } catch (error) { events.value = []; } finally { eventsLoading.value = false; }
    };

    const createEvent = async () => {
        if (!isLoggedIn.value) { showToast('請先登入', 'error'); return; }
        eventSubmitting.value = true;
        try {
            const response = await api.post('/events', eventForm);
            showToast('活動建立成功！', 'success');
            if (resetEventForm) resetEventForm(); 
            await loadEvents(); 
            navigateTo('events');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } catch (error) { showToast('建立失敗', 'error'); } finally { eventSubmitting.value = false; }
    };

    const joinEvent = async (eventId) => {
        if (!isLoggedIn.value) { showToast('請先登入', 'error'); navigateTo('auth'); return null; }
        try {
            const response = await api.post(`/events/${eventId}/join`);
            showToast('報名成功！', 'success'); 
            await loadEvents();
            return response.data.event;
        } catch (error) { 
            showToast(error.response?.data?.error || '報名失敗', 'error'); 
            return null;
        }
    };

    const leaveEvent = async (eventId) => {
        try {
            const response = await api.post(`/events/${eventId}/leave`);
            showToast('已取消報名', 'info'); 
            await loadEvents();
            return response.data.event;
        } catch (error) { 
            showToast(error.response?.data?.error || '取消失敗', 'error'); 
            return null;
        }
    };

    return { events, eventsLoading, eventSubmitting, eventsPagination, loadEvents, createEvent, joinEvent, leaveEvent };
};
