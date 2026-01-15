// --- useCardCreation Composable ---
// 步驟驗證、卡片建立流程

const useCardCreation = (form, showToast) => {
    const currentStep = ref(1);
    const stepAttempted = reactive({});
    const isAdjustingPhoto = ref(false);
    const isAdjustingSig = ref(false);
    const isCapturing = ref(false);
    const isPhotoAdjustLoading = ref(false);
    const isSigAdjustLoading = ref(false);

    const canProceedStep1 = computed(() => !!form.photo);
    const canProceedStep2 = computed(() => !!form.level && !!form.handed && !!form.backhand);
    const canProceedStep3 = computed(() => true); // Intro is optional

    const canGoToStep = (targetStep) => {
        if (targetStep === 1) return true;
        if (targetStep === 2) return canProceedStep1.value;
        if (targetStep === 3) return canProceedStep1.value && canProceedStep2.value;
        if (targetStep === 4) return canProceedStep1.value && canProceedStep2.value && canProceedStep3.value;
        return false;
    };

    const tryNextStep = () => {
        stepAttempted[currentStep.value] = true;
        if (currentStep.value === 1 && !canProceedStep1.value) { showToast('請上傳照片', 'error'); return; }
        if (currentStep.value === 2 && !canProceedStep2.value) { showToast('請選擇 NTRP 等級和技術設定', 'error'); return; }
        currentStep.value++;
    };

    const tryGoToStep = (targetStep) => {
        if (canGoToStep(targetStep)) currentStep.value = targetStep;
        else {
            if (targetStep >= 2 && !canProceedStep1.value) showToast('請先完成第一步：上傳照片', 'warning');
            else if (targetStep >= 3 && !canProceedStep2.value) showToast('請先完成第二步：設定等級與技術', 'warning');
        }
    };

    return { currentStep, stepAttempted, isAdjustingPhoto, isAdjustingSig, isCapturing, isPhotoAdjustLoading, isSigAdjustLoading, canProceedStep1, canProceedStep2, canProceedStep3, canGoToStep, tryNextStep, tryGoToStep };
};
