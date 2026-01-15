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
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
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

    /* Low-Cost Shine Effect */
    .card-shine {
        position: absolute;
        inset: 0;
        z-index: 15;
        pointer-events: none;
        background: linear-gradient(
            110deg,
            transparent 20%,
            rgba(255, 255, 255, 0.1) 40%,
            rgba(255, 255, 255, 0.4) 50%,
            rgba(255, 255, 255, 0.1) 60%,
            transparent 80%
        );
        background-size: 200% 100%;
        animation: shine-flow 6s infinite linear;
        mix-blend-mode: overlay;
        opacity: 0.5;
    }

    @keyframes shine-flow {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    /* Base Card Style */
    .card-holo {
        position: relative;
        z-index: 10;
        border-radius: 32px;
        overflow: hidden;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        box-shadow: 0 20px 40px -10px rgba(0,0,0,0.5), inset 0 0 20px rgba(255,255,255,0.05);
        isolation: isolate;
    }
    .card-holo::before {
        content: "";
        position: absolute;
        inset: 0;
        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
        opacity: 0.03;
        pointer-events: none;
        z-index: 20;
    }

    /* Theme Specific Card Styles */
    .theme-gold.card-holo { 
        background: linear-gradient(135deg, #1a1c2c 0%, #0a0b16 100%); 
        border: 2px solid rgba(251, 191, 36, 0.6);
        box-shadow: 0 20px 40px -10px rgba(251, 191, 36, 0.3), inset 0 0 20px rgba(251, 191, 36, 0.1);
    }
    .theme-platinum.card-holo { 
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); 
        border: 2px solid rgba(148, 163, 184, 0.6);
        box-shadow: 0 20px 40px -10px rgba(148, 163, 184, 0.3), inset 0 0 20px rgba(148, 163, 184, 0.1);
    }
    .theme-holographic.card-holo { 
        background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%); 
        border: 2px solid rgba(192, 132, 252, 0.6);
        box-shadow: 0 20px 40px -10px rgba(192, 132, 252, 0.3), inset 0 0 20px rgba(192, 132, 252, 0.1);
    }
    .theme-onyx.card-holo { 
        background: linear-gradient(135deg, #0f172a 0%, #020617 100%); 
        border: 2px solid rgba(71, 85, 105, 0.6);
        box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.8), inset 0 0 20px rgba(255, 255, 255, 0.05);
    }
    .theme-sakura.card-holo { 
        background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%); 
        border: 2px solid rgba(244, 114, 182, 0.6);
        box-shadow: 0 20px 40px -10px rgba(244, 114, 182, 0.3), inset 0 0 20px rgba(244, 114, 182, 0.1);
    }
    .theme-standard.card-holo { 
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); 
        border: 2px solid rgba(59, 130, 246, 0.6);
        box-shadow: 0 20px 40px -10px rgba(59, 130, 246, 0.3), inset 0 0 20px rgba(59, 130, 246, 0.1);
    }

    /* Shine Effect Theme Overrides */
    .theme-gold .card-shine {
        background: linear-gradient(
            110deg,
            transparent 20%,
            rgba(255, 215, 0, 0.1) 40%,
            rgba(255, 240, 150, 0.5) 50%,
            rgba(255, 215, 0, 0.1) 60%,
            transparent 80%
        );
        background-size: 200% 100%;
    }

    .theme-holographic .card-shine {
        background: linear-gradient(
            110deg,
            transparent 10%,
            rgba(255, 0, 255, 0.1) 25%,
            rgba(0, 255, 255, 0.3) 50%,
            rgba(255, 255, 0, 0.1) 75%,
            transparent 90%
        );
        background-size: 200% 100%;
        animation: shine-flow 4s infinite linear;
        opacity: 0.6;
    }

    /* Holographic Text Gradient */
    .text-holo-gradient {
        background: linear-gradient(to right, #efb2fb, #acc6f8, #efb2fb, #acc6f8);
        background-size: 200% auto;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        animation: holo-text-flow 4s linear infinite;
        color: #acc6f8; /* Fallback */
    }

    @keyframes holo-text-flow {
        to { background-position: 200% center; }
    }

    @keyframes holo-shift {
        0% { filter: hue-rotate(0deg) brightness(1); }
        50% { filter: hue-rotate(180deg) brightness(1.1); }
        100% { filter: hue-rotate(360deg) brightness(1); }
    }
    .theme-holographic.card-holo::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(255,0,255,0.05), rgba(0,255,255,0.05));
        z-index: 12;
        pointer-events: none;
        animation: holo-shift 15s infinite linear;
    }

    .theme-holographic { --color1: #efb2fb; --color2: #acc6f8; }

    /* Capture Stability & Performance */
    .is-capturing {
        transform: none !important;
        animation: none !important;
        transition: none !important;
    }
    .is-capturing *, .card-sm, .card-sm * {
        animation: none !important;
        transition: none !important;
    }
    .is-capturing .card-shine, .card-sm .card-shine {
        display: none !important;
    }
    .card-sm .card-holo::after {
        animation: none !important;
        background: rgba(255, 255, 255, 0.05) !important;
    }
    .is-capturing .signature-layer img {
        transition: none !important;
    }
</style>
