// --- useDrag Composable ---
// 拖曳功能

const useDrag = (form) => {
    const dragInfo = reactive({ isDragging: false, target: null, startX: 0, startY: 0, initialX: 0, initialY: 0 });

    const startDrag = (e, target) => {
        dragInfo.isDragging = true;
        dragInfo.target = target;
        const clientX = e.clientX || (e.touches && e.touches[0].clientX);
        const clientY = e.clientY || (e.touches && e.touches[0].clientY);
        dragInfo.startX = clientX;
        dragInfo.startY = clientY;
        if (target === 'photo') {
            dragInfo.initialX = form.photoX;
            dragInfo.initialY = form.photoY;
        } else {
            dragInfo.initialX = form.sigX;
            dragInfo.initialY = form.sigY;
        }
    };

    const handleDrag = (e) => {
        if (!dragInfo.isDragging) return;
        const clientX = e.clientX || (e.touches && e.touches[0].clientX);
        const clientY = e.clientY || (e.touches && e.touches[0].clientY);
        const dx = clientX - dragInfo.startX;
        const dy = clientY - dragInfo.startY;
        if (dragInfo.target === 'photo') {
            form.photoX = dragInfo.initialX + dx;
            form.photoY = dragInfo.initialY + dy;
        } else {
            form.sigX = dragInfo.initialX + dx;
            form.sigY = dragInfo.initialY + dy;
        }
    };

    const stopDrag = () => { dragInfo.isDragging = false; dragInfo.target = null; };

    return { dragInfo, startDrag, handleDrag, stopDrag };
};
