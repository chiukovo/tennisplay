// --- useEvents Composable ---
// 活動管理、報名/取消

const useEvents = (isLoggedIn, showToast, navigateTo, formatLocalDateTime, eventForm) => {
    const events = ref([]);
    const eventsLoading = ref(false);
    const eventSubmitting = ref(false);

    const loadEvents = async () => {
        eventsLoading.value = true;
        try {
            const response = await api.get('/events');
            if (response.data.success) events.value = response.data.data;
        } catch (error) { events.value = []; } finally { eventsLoading.value = false; }
    };

    const createEvent = async (resetEventForm) => {
        if (!isLoggedIn.value) { showToast('請先登入', 'error'); return; }
        eventSubmitting.value = true;
        try {
            const response = await api.post('/events', eventForm);
            showToast('活動建立成功！', 'success');
            resetEventForm(); await loadEvents(); navigateTo('events');
        } catch (error) { showToast('建立失敗', 'error'); } finally { eventSubmitting.value = false; }
    };

    const joinEvent = async (eventId) => {
        if (!isLoggedIn.value) { showToast('請先登入', 'error'); navigateTo('auth'); return; }
        try {
            await api.post(`/events/${eventId}/join`);
            showToast('報名成功！', 'success'); await loadEvents();
        } catch (error) { showToast('報名失敗', 'error'); }
    };

    const leaveEvent = async (eventId) => {
        try {
            await api.post(`/events/${eventId}/leave`);
            showToast('已取消報名', 'info'); await loadEvents();
        } catch (error) { showToast('取消失敗', 'error'); }
    };

    return { events, eventsLoading, eventSubmitting, loadEvents, createEvent, joinEvent, leaveEvent };
};
