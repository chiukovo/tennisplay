// --- useMessages Composable ---
// 訊息管理、輪詢機制

const useMessages = (isLoggedIn, currentUser, showToast) => {
    const messages = ref([]);
    
    const loadMessages = async () => {
        if (!isLoggedIn.value) return;
        try {
            const response = await api.get('/messages');
            if (response.data.success) {
                const data = response.data.data;
                messages.value = Array.isArray(data.data) ? data.data : (Array.isArray(data) ? data : []);
            }
        } catch (error) {}
    };

    const markMessageRead = async (messageId) => {
        try {
            await api.put(`/messages/${messageId}/read`);
            const msg = messages.value.find(m => m.id === messageId);
            if (msg) { msg.read_at = new Date().toISOString(); msg.unread = false; }
        } catch (error) {}
    };

    return { messages, loadMessages, markMessageRead };
};
