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

    /* Skeleton Loading Animation */
    @keyframes skeleton-pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.4; }
    }
    .skeleton {
        animation: skeleton-pulse 1.5s ease-in-out infinite;
        background: linear-gradient(90deg, #e2e8f0 0%, #f1f5f9 50%, #e2e8f0 100%);
        background-size: 200% 100%;
    }
    @keyframes skeleton-shimmer {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }
    .skeleton-shimmer {
        background: linear-gradient(90deg, #e2e8f0 25%, #f8fafc 50%, #e2e8f0 75%);
        background-size: 200% 100%;
        animation: skeleton-shimmer 1.5s infinite;
    }

    /* Page Transition Effects */
    .page-enter-active, .page-leave-active {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .page-enter-from {
        opacity: 0;
        transform: translateY(20px);
    }
    .page-leave-to {
        opacity: 0;
        transform: translateY(-10px);
    }

    /* Card Hover 3D Effect */
    .card-3d {
        transition: transform 0.4s cubic-bezier(0.23, 1, 0.32, 1), box-shadow 0.4s ease;
        transform-style: preserve-3d;
        perspective: 1000px;
    }
    .card-3d:hover {
        transform: translateY(-8px) rotateX(2deg) rotateY(-2deg);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(59, 130, 246, 0.1);
    }

    /* Toast Animations */
    .toast-enter-active {
        animation: toast-in 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .toast-leave-active {
        animation: toast-out 0.3s ease-in forwards;
    }
    @keyframes toast-in {
        0% { opacity: 0; transform: translateX(100%); }
        100% { opacity: 1; transform: translateX(0); }
    }
    @keyframes toast-out {
        0% { opacity: 1; transform: translateX(0); }
        100% { opacity: 0; transform: translateX(100%); }
    }

    /* Smooth Focus States */
    input:focus, textarea:focus, select:focus, button:focus-visible {
        outline: none;
        ring: 4px;
        ring-color: rgba(59, 130, 246, 0.2);
    }

    /* Form Validation States */
    .field-error {
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }
    .field-success {
        border-color: #22c55e !important;
    }
    .error-message {
        color: #ef4444;
        font-size: 12px;
        font-weight: 700;
        margin-top: 6px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* Confirm Dialog Overlay */
    .confirm-backdrop {
        background: rgba(15, 23, 42, 0.8);
        backdrop-filter: blur(4px);
    }
</style>
