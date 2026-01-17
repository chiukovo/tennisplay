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

    const validateEventTimes = () => {
        if (!eventForm.event_date) {
            showToast('請選擇開始時間', 'error');
            return false;
        }
        const eventDate = new Date(eventForm.event_date);
        if (Number.isNaN(eventDate.getTime())) {
            showToast('請選擇正確的日期時間', 'error');
            return false;
        }
        if (eventDate <= new Date()) {
            showToast('開始時間必須是未來的時間', 'error');
            return false;
        }
        if (eventForm.end_date) {
            const endDate = new Date(eventForm.end_date);
            if (Number.isNaN(endDate.getTime())) {
                showToast('請選擇正確的結束時間', 'error');
                return false;
            }
            if (endDate <= eventDate) {
                showToast('結束時間必須晚於開始時間', 'error');
                return false;
            }
        }
        return true;
    };

    const createEvent = async () => {
        if (!isLoggedIn.value) { showToast('請先登入', 'error'); return; }
        if (!validateEventTimes()) return;
        
        eventSubmitting.value = true;
        try {
            const response = await api.post('/events', eventForm);
            if (resetEventForm) resetEventForm(); 
            await loadEvents(); 
            navigateTo('events');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } catch (error) {
            const apiErrors = error.response?.data?.errors;
            const firstError = apiErrors ? Object.values(apiErrors).flat()[0] : null;
            const msg = firstError || error.response?.data?.error || error.response?.data?.message || '建立失敗';
            showToast(msg, 'error');
        } finally { eventSubmitting.value = false; }
    };

    const updateEvent = async (id) => {
        if (!isLoggedIn.value) { showToast('請先登入', 'error'); return; }
        if (!validateEventTimes()) return;
        
        eventSubmitting.value = true;
        try {
            const response = await api.put(`/events/${id}`, eventForm);
            if (resetEventForm) resetEventForm(); 
            await loadEvents(); 
            navigateTo('events');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } catch (error) {
            const apiErrors = error.response?.data?.errors;
            const firstError = apiErrors ? Object.values(apiErrors).flat()[0] : null;
            const msg = firstError || error.response?.data?.error || error.response?.data?.message || '更新失敗';
            showToast(msg, 'error');
        } finally { eventSubmitting.value = false; }
    };

    const deleteEvent = async (id) => {
        if (!isLoggedIn.value || eventSubmitting.value) return;
        eventSubmitting.value = true;
        try {
            await api.delete(`/events/${id}`);
            await loadEvents();
        } catch (error) {
            showToast(error.response?.data?.error || '刪除失敗', 'error');
        } finally { eventSubmitting.value = false; }
    };

    const joinEvent = async (eventId) => {
        if (!isLoggedIn.value) { showToast('請先登入', 'error'); navigateTo('auth'); return null; }
        if (eventSubmitting.value) return null;
        
        eventSubmitting.value = true;
        try {
            const response = await api.post(`/events/${eventId}/join`);
            await loadEvents();
            return response.data.event;
        } catch (error) { 
            showToast(error.response?.data?.error || '報名失敗', 'error'); 
            return null;
        } finally { eventSubmitting.value = false; }
    };

    const leaveEvent = async (eventId) => {
        if (eventSubmitting.value) return null;
        
        eventSubmitting.value = true;
        try {
            const response = await api.post(`/events/${eventId}/leave`);
            await loadEvents();
            return response.data.event;
        } catch (error) { 
            showToast(error.response?.data?.error || '取消失敗', 'error'); 
            return null;
        } finally { eventSubmitting.value = false; }
    };

    return { events, eventsLoading, eventSubmitting, eventsPagination, loadEvents, createEvent, updateEvent, deleteEvent, joinEvent, leaveEvent };
};
