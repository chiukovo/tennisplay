<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&family=Noto+Sans+TC:wght@400;700;900&display=swap');
    *, ::before, ::after {
        box-sizing: border-box;
    }
    html, body { 
        font-family: 'Noto Sans TC', 'Inter', sans-serif; 
        font-size: 16px;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        text-rendering: optimizeLegibility;
        overflow-x: hidden;
        width: 100%;
        position: relative;
    }
    
    .card-shadow {
        box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.3);
    }

    .premium-blur {
        backdrop-filter: blur(8px);
        background: rgba(15, 23, 42, 0.7);
        will-change: opacity;
    }

    /* 自定義捲軸 */
    ::-webkit-scrollbar { width: 8px; }
    ::-webkit-scrollbar-track { background: #f1f5f9; }
    ::-webkit-scrollbar-thumb { background: #94a3b8; border-radius: 10px; }

    /* 輕量級漸變效果 */
    .fade-enter-active, .fade-leave-active {
        transition: opacity 0.2s ease-out;
    }
    .fade-enter-from, .fade-leave-to {
        opacity: 0;
    }

    /* 動畫優化 */
    .modal-enter-active, .modal-leave-active {
        transition: opacity 0.25s ease, transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .modal-enter-from, .modal-leave-to {
        opacity: 0;
        transform: scale(0.98) translateY(5px);
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
    .moveable-control { border: 2px solid #3b82f6 !important; background: #fff !important; width: 16px !important; height: 16px !important; margin-top: -8px !important; margin-left: -8px !important; border-radius: 50% !important; cursor: pointer !important; }

    /* 3D Flip Card Styles */
    .flip-card {
        perspective: 1500px;
        background-color: transparent;
    }
    .flip-card-inner {
        position: relative;
        width: 100%;
        height: 100%;
        text-align: center;
        transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        transform-style: preserve-3d;
    }
    .flip-card.is-flipped .flip-card-inner {
        transform: rotateY(180deg);
    }
    .flip-card-front, .flip-card-back {
        position: absolute;
        width: 100%;
        height: 100%;
        -webkit-backface-visibility: hidden;
        backface-visibility: hidden;
        border-radius: 24px;
    }
    .flip-card-back {
        transform: rotateY(180deg);
    }

    /* Prevent mobile scroll during adjustment */
    .touch-none {
        touch-action: none !important;
        overscroll-behavior: none !important;
        user-select: none !important;
        -webkit-user-select: none !important;
    }

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

    .bg-pattern {
        background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23fff' fill-opacity='0.4'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }

    /* Directional Slide Transitions */
    .slide-next-enter-active, .slide-next-leave-active,
    .slide-prev-enter-active, .slide-prev-leave-active {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .slide-next-enter-from { opacity: 0; transform: translateX(50px); }
    .slide-next-leave-to { opacity: 0; transform: translateX(-50px); }
    
    .slide-prev-enter-from { opacity: 0; transform: translateX(-50px); }
    .slide-prev-leave-to { opacity: 0; transform: translateX(50px); }

    /* Navigation Pulse Animation */
    @keyframes nav-pulse {
        0% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.6); }
        70% { box-shadow: 0 0 0 15px rgba(37, 99, 235, 0); }
        100% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0); }
    }
    .nav-pulse {
        animation: nav-pulse 2s infinite;
    }

    /* Card Container - GPU accelerated for crisp text */
    .holo-container {
        transform: translateZ(0);
        -webkit-transform: translateZ(0);
    }

    /* Premium Holo Effects (Refined CodePen Style - Subtle & Persistent) */
    .card-holo {
        --color1: rgb(0, 231, 255);
        --color2: rgb(255, 0, 231);
        position: relative;
        z-index: 10;
        isolation: isolate;
        transition: transform 0.6s cubic-bezier(0.2, 0.8, 0.2, 1), box-shadow 0.4s ease;
        background-color: #040712;
        /* Premium Layered Shadow - Softer and more floating */
        box-shadow: 
            0 5px 15px -5px rgba(0,0,0,0.4),
            0 20px 40px -10px rgba(0,0,0,0.6);
        border-radius: 32px; /* Softer base */
        /* Text rendering optimization for scaled elements */
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        text-rendering: optimizeLegibility;
        image-rendering: -webkit-optimize-contrast;
        backface-visibility: hidden;
        -webkit-backface-visibility: hidden;
        transform-style: preserve-3d;
    }

    .card-holo:before,
    .card-holo:after {
        content: "";
        position: absolute;
        inset: 0;
        background-repeat: no-repeat;
        opacity: var(--opc, 0);
        mix-blend-mode: color-dodge;
        transition: all 0.5s ease; /* Smoother transitions */
        z-index: 60;
        pointer-events: none;
        border-radius: inherit;
    }

    .card-holo:before {
        background-position: var(--lp, 50%) var(--tp, 50%);
        background-size: 250% 250%;
        background-image: linear-gradient(
            115deg,
            transparent 0%,
            var(--color1) 25%,
            transparent 47%,
            transparent 53%,
            var(--color2) 75%,
            transparent 100%
        );
        filter: brightness(0.5) contrast(1.1);
    }

    .card-holo:after {
        background-image: url("https://assets.codepen.io/13471/sparkles.gif"),
            url(https://assets.codepen.io/13471/holo.png),
            linear-gradient(
                125deg,
                #ff008450 15%,
                #fca40040 30%,
                #ffff0030 40%,
                #00ff8a20 60%,
                #00cfff40 70%,
                #cc4cfa50 85%
            );
        background-position: var(--spx, 50%) var(--spy, 50%);
        background-size: 160%;
        background-blend-mode: overlay;
        filter: brightness(0.8) contrast(1.1);
    }

    /* Animated State (Initial Shine - Softened) */
    .card-holo.animated:before {
        animation: holoGradient var(--duration, 12s) ease var(--delay, 0s) infinite;
        opacity: calc(0.1 * var(--int, 1));
    }
    .card-holo.animated:after {
        animation: holoSparkle var(--duration, 12s) ease var(--delay, 0s) infinite;
        opacity: calc(1 * var(--int, 1));
    }

    @keyframes holoSparkle {
        0%, 100% { opacity: calc(0.5 * var(--int, 1)); background-position: 50% 50%; }
        5%, 8% { opacity: calc(0.8 * var(--int, 1)); background-position: 40% 40%; }
        13%, 16% { opacity: calc(0.35 * var(--int, 1)); background-position: 50% 50%; }
        35%, 38% { opacity: calc(0.8 * var(--int, 1)); background-position: 60% 60%; }
        55% { opacity: calc(0.2 * var(--int, 1)); background-position: 45% 45%; }
    }

    @keyframes holoGradient {
        0%, 100% { opacity: calc(0.1 * var(--int, 1)); background-position: 50% 50%; }
        5%, 9% { background-position: 100% 100%; opacity: calc(0.25 * var(--int, 1)); }
        13%, 17% { background-position: 0% 0%; opacity: calc(0.2 * var(--int, 1)); }
        35%, 39% { background-position: 100% 100%; opacity: calc(0.25 * var(--int, 1)); }
        55% { background-position: 0% 0%; opacity: calc(0.2 * var(--int, 1)); }
    }

    /* Removed holoCard rotation from always-on animation to keep it stable */
    .card-holo.animated {
        transform: rotateX(0deg) rotateY(0deg) translateZ(0);
    }

    /* Active State (Interaction - Subtle) */
    .card-holo:not(.animated) {
        transition: transform 0.2s ease-out; /* Slower, gentler return */
    }
    .card-holo:not(.animated):before,
    .card-holo:not(.animated):after {
        opacity: var(--opc, 0.2); 
        transition: opacity 0.4s cubic-bezier(0.2, 0.8, 0.2, 1);
    }

    .theme-holographic { --color1: #efb2fb; --color2: #acc6f8; }

    /* Capture Stability */
    .is-capturing {
        transform: none !important;
        animation: none !important;
        transition: none !important;
    }
    .is-capturing * {
        animation: none !important;
        transition: none !important;
        /* Force text rendering to be stable */
        -webkit-background-clip: border-box !important;
        background-clip: border-box !important;
        -webkit-text-fill-color: currentColor !important;
        text-fill-color: currentColor !important;
    }
    /* Ensure signature maintains its transform but without transitions */
    .is-capturing .signature-layer img {
        transition: none !important;
    }
</style>
