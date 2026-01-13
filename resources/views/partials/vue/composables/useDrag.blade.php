// --- useDrag Composable ---
// 拖曳功能

const useDrag = (form) => {
    const dragInfo = reactive({ isDragging: false, target: null, startX: 0, startY: 0, initialX: 0, initialY: 0 });

    const handleDrag = (e) => {
        if (!dragInfo.isDragging) return;
        
        // Prevent scrolling while dragging on touch devices
        if (e.type === 'touchmove' && e.cancelable) {
            e.preventDefault();
        }

        const clientX = e.clientX || (e.touches && e.touches[0].clientX);
        const clientY = e.clientY || (e.touches && e.touches[0].clientY);
        
        if (clientX === undefined || clientY === undefined) return;

        const dx = clientX - dragInfo.startX;
        const dy = clientY - dragInfo.startY;
        
        if (dragInfo.target === 'photo') {
            form.photoX = dragInfo.initialX + (dx / 6); // Lower sensitivity for smoother dragging
            form.photoY = dragInfo.initialY + (dy / 6);
        } else {
            form.sigX = dragInfo.initialX + dx;
            form.sigY = dragInfo.initialY + dy;
        }
    };

    const stopDrag = () => {
        if (dragInfo.isDragging) {
            dragInfo.isDragging = false;
            dragInfo.target = null;
            window.removeEventListener('mousemove', handleDrag);
            window.removeEventListener('mouseup', stopDrag);
            window.removeEventListener('touchmove', handleDrag);
            window.removeEventListener('touchend', stopDrag);
        }
    };

    const startDrag = (e, target) => {
        // Don't prevent default here to allow clicking other elements
        dragInfo.isDragging = true;
        dragInfo.target = target;
        const clientX = e.clientX || (e.touches && e.touches[0].clientX);
        const clientY = e.clientY || (e.touches && e.touches[0].clientY);
        dragInfo.startX = clientX;
        dragInfo.startY = clientY;
        
        if (target === 'photo') {
            dragInfo.initialX = form.photoX || 0;
            dragInfo.initialY = form.photoY || 0;
        } else {
            dragInfo.initialX = form.sigX || 0;
            dragInfo.initialY = form.sigY || 0;
        }

        window.addEventListener('mousemove', handleDrag);
        window.addEventListener('mouseup', stopDrag);
        window.addEventListener('touchmove', handleDrag, { passive: false });
        window.addEventListener('touchend', stopDrag);
    };

    return { dragInfo, startDrag, handleDrag, stopDrag };
};
