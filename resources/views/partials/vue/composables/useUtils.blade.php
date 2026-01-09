// --- useUtils Composable ---
// Toast 通知、確認對話框、日期格式化工具

const useUtils = () => {
    const toasts = ref([]);
    let lastToastMessage = '';
    let lastToastTime = 0;

    const showToast = (message, type = 'info', duration = 4000) => {
        const now = Date.now();
        if (message === lastToastMessage && now - lastToastTime < 500) return;
        lastToastMessage = message;
        lastToastTime = now;
        const id = now;
        toasts.value.push({ id, message, type });
        setTimeout(() => {
            const index = toasts.value.findIndex(t => t.id === id);
            if (index > -1) toasts.value.splice(index, 1);
        }, duration);
    };

    const removeToast = (id) => {
        const index = toasts.value.findIndex(t => t.id === id);
        if (index > -1) toasts.value.splice(index, 1);
    };

    const confirmDialog = reactive({
        open: false, title: '', message: '', confirmText: '確認', cancelText: '取消', onConfirm: null, type: 'danger'
    });

    const showConfirm = (options) => {
        Object.assign(confirmDialog, {
            open: true,
            title: options.title || '確認操作',
            message: options.message || '確定要執行此操作嗎？',
            confirmText: options.confirmText || '確認',
            cancelText: options.cancelText || '取消',
            type: options.type || 'danger',
            onConfirm: options.onConfirm
        });
    };

    const hideConfirm = () => { confirmDialog.open = false; confirmDialog.onConfirm = null; };
    const executeConfirm = () => { if (confirmDialog.onConfirm) confirmDialog.onConfirm(); hideConfirm(); };

    const formatDate = (dateStr) => {
        if (!dateStr) return '';
        const date = new Date(dateStr);
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);
        if (diffMins < 1) return '剛剛';
        if (diffMins < 60) return `${diffMins} 分鐘前`;
        if (diffHours < 24) return `${diffHours} 小時前`;
        if (diffDays < 7) return `${diffDays} 天前`;
        return date.toLocaleDateString('zh-TW', { month: 'short', day: 'numeric' });
    };

    const getUrl = (path) => {
        if (!path) return null;
        if (path.startsWith('http') || path.startsWith('data:')) return path;
        return `/storage/${path}`;
    };

    const formatLocalDateTime = (date) => {
        const pad = (n) => String(n).padStart(2, '0');
        return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
    };

    return { toasts, showToast, removeToast, confirmDialog, showConfirm, hideConfirm, executeConfirm, formatDate, getUrl, formatLocalDateTime };
};
