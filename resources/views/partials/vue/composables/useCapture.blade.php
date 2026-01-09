// --- useCapture Composable ---
// 圖片擷取

const useCapture = (showToast) => {
    const isCapturing = ref(false);

    const captureCardImage = async (cardContainer) => {
        if (typeof html2canvas === 'undefined') {
            showToast('截圖組件載入失敗', 'error');
            return null;
        }
        const cardEl = cardContainer || document.querySelector('.capture-target');
        if (!cardEl) return null;
        
        isCapturing.value = true;
        const originalStyle = cardEl.getAttribute('style') || '';
        const mergedLayer = cardEl.querySelector('.merged-photo-layer');
        const originalMergedDisplay = mergedLayer ? mergedLayer.style.display : '';
        
        try {
            if (mergedLayer) mergedLayer.style.display = 'none';
            const targetWidth = 320;
            const targetHeight = (targetWidth / 2.5) * 3.8;
            
            cardEl.style.width = `${targetWidth}px`;
            cardEl.style.height = `${targetHeight}px`;
            cardEl.style.position = 'fixed';
            cardEl.style.top = '0';
            cardEl.style.left = '0';
            cardEl.style.zIndex = '9999';

            await new Promise(resolve => setTimeout(resolve, 100));

            const canvas = await html2canvas(cardEl, {
                useCORS: true, allowTaint: true, scale: 2, width: targetWidth, height: targetHeight, logging: false
            });

            return canvas.toDataURL('image/png');
        } catch (e) {
            return null;
        } finally {
            cardEl.setAttribute('style', originalStyle);
            if (mergedLayer) mergedLayer.style.display = originalMergedDisplay;
            isCapturing.value = false;
        }
    };

    return { isCapturing, captureCardImage };
};
