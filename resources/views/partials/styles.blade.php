<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&family=Noto+Sans+TC:wght@400;700;900&display=swap');
    body { 
        font-family: 'Noto Sans TC', 'Inter', sans-serif; 
        font-size: 16px;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        text-rendering: optimizeLegibility;
    }
    
    .card-shadow {
        box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.3);
    }

    .premium-blur {
        backdrop-filter: blur(12px);
        background: rgba(15, 23, 42, 0.75);
        will-change: opacity;
    }

    /* 自定義捲軸 */
    ::-webkit-scrollbar { width: 8px; }
    ::-webkit-scrollbar-track { background: #f1f5f9; }
    ::-webkit-scrollbar-thumb { background: #94a3b8; border-radius: 10px; }

    /* 動畫優化 */
    .modal-enter-active, .modal-leave-active {
        transition: opacity 0.3s ease, transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .modal-enter-from, .modal-leave-to {
        opacity: 0;
        transform: scale(0.95) translateY(10px);
    }
    
    .modal-content {
        will-change: transform, opacity;
    }
    /* 隱藏捲軸但保留滾動功能 */
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

    /* Step Transitions */
    .step-enter-active, .step-leave-active {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .step-enter-from {
        opacity: 0;
        transform: translateX(20px);
    }
    .step-leave-to {
        opacity: 0;
        transform: translateX(-20px);
    }

    /* Moveable Customization */
    .moveable-control.moveable-origin { display: none !important; }
    .moveable-line { background: #3b82f6 !important; opacity: 0.5; }
    .moveable-control { border: 2px solid #3b82f6 !important; background: #fff !important; width: 12px !important; height: 12px !important; margin-top: -6px !important; margin-left: -6px !important; border-radius: 50% !important; }
</style>
