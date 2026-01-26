<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-J5H444JR6E"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-J5H444JR6E');
    </script>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $seo['title'] ?? 'LoveTennis | 專業網球約打媒合與球友卡社群' }}</title>
    <!-- SEO Meta Tags -->
    <meta name="description" content="{{ $seo['description'] ?? 'LoveTennis 是全台最專業的網球約打平台，提供職業級球友卡製作、透明約打費用與安全站內信媒合系統。' }}">
    <meta name="keywords" content="網球約打, 陪打, 揪打, 找教練, 網球找教練, 推薦教練, 網球教練推薦, 網球約球, 一起打網球, 網球揪球, 網球球友, 網球場次, 網球活動">
    @php
        $canonical = $seo['canonical'] ?? url()->current();
    @endphp
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:title" content="{{ $seo['title'] ?? 'LoveTennis' }}">
    <meta property="og:description" content="{{ $seo['description'] ?? '全台最專業的網球約打平台' }}">
    <meta property="og:image" content="{{ url($seo['og_image'] ?? '/img/og-default.jpg') }}">
    <link rel="canonical" href="{{ $canonical }}">

    @if(isset($seo['type']) && $seo['type'] === 'event')
        <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => $seo['title'] ?? 'LoveTennis 活動',
            'description' => $seo['description'] ?? '查看此網球活動詳情與報名資訊',
            'startDate' => $seo['start_date'] ?? null,
            'endDate' => $seo['end_date'] ?? null,
            'eventStatus' => 'https://schema.org/EventScheduled',
            'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
            'location' => [
                '@type' => 'Place',
                'name' => $seo['location'] ?? '網球場',
                'address' => $seo['address'] ?? '',
            ],
            'url' => $canonical,
            'image' => url($seo['og_image'] ?? '/img/og-default.jpg'),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
        </script>
    @endif

    {{-- Favicon --}}
    <link rel="shortcut icon" href="/favicon.ico">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="192x192" href="/img/favicon/android-chrome-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/img/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/img/favicon/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/img/favicon/apple-touch-icon.png">
    <link rel="manifest" href="/img/favicon/site.webmanifest">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        window.tennisConfig = @json(config('tennis'));
    </script>
    <style>[v-cloak] { display: none !important; }</style>
    <script src="/vendor/vue/vue.global.js"></script>
    <script src="/vendor/tailwind/tailwind.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.17/dist/tailwind.min.css" id="tailwind-fallback" media="print" disabled>
    <script>
        (function () {
            const fallback = document.getElementById('tailwind-fallback');
            const hasTailwindStyles = () => {
                const styles = Array.from(document.querySelectorAll('style'));
                return styles.some(style => style.textContent && style.textContent.includes('tailwindcss v'));
            };
            const enableFallback = () => {
                if (!fallback) return;
                fallback.disabled = false;
                fallback.media = 'all';
            };
            const check = () => {
                if (!hasTailwindStyles()) {
                    enableFallback();
                }
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => setTimeout(check, 1200));
            } else {
                setTimeout(check, 1200);
            }

            window.addEventListener('load', () => setTimeout(check, 1500));
        })();
    </script>
    <link rel="stylesheet" href="/vendor/animate/animate.min.css"/>
    
    {{-- External Scripts --}}
    <script src="/vendor/axios/axios.min.js"></script>
    <script>
        if (typeof window.axios === 'undefined') {
            const axiosScript = document.createElement('script');
            axiosScript.src = 'https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js';
            axiosScript.defer = true;
            document.head.appendChild(axiosScript);
        }
    </script>
    <script src="/vendor/moveable/moveable.min.js" defer></script>
    <script src="/vendor/html2canvas/html2canvas.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/html-to-image@1.11.11/dist/html-to-image.min.js"></script>
    
    {{-- Swiper for Cards Effect --}}
    <link rel="stylesheet" href="/vendor/swiper/swiper-bundle.min.css" />
    <script src="/vendor/swiper/swiper-bundle.min.js"></script>
    
    @include('partials.styles')
</head>
<body class="bg-slate-50 text-slate-900 leading-normal min-h-screen">
    {{-- Vanilla JS Pre-loader for LINE Login --}}


    {{-- 全域初始化 Loading（預渲染暖身時顯示）--}}
    <div id="init-loader" style="position: fixed; inset: 0; z-index: 99998; background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); display: flex; align-items: center; justify-content: center; flex-direction: column; transition: opacity 0.3s ease;">
        <img src="/img/logo.png" alt="LoveTennis" style="width: 4rem; height: 4rem; margin-bottom: 1rem; object-fit: contain;">
        <div style="width: 2rem; height: 2rem; border-radius: 50%; border: 3px solid #e2e8f0; border-top-color: #3b82f6; animation: spin 0.8s linear infinite;"></div>
    </div>
    <script>
        // Set body to hidden until Vue settles (handled in vue-scripts onMounted)
        document.body.classList.add('warmup-hidden');
    </script>
    <style>
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>

{{-- Vue Component Templates --}}
@include('components.vue-templates')
@include('components.modals')

<div id="app" v-cloak>
    {{-- LINE Login Loading Overlay --}}


    {{-- Navigation --}}
    @include('partials.navigation')

    {{-- Main Content --}}
    <main class="max-w-6xl w-full mx-auto px-4 pt-6 sm:pt-10 pb-4 sm:pb-8">
        @include('pages.auth')
        @include('pages.home')
        @include('pages.create')
        @include('pages.list')
        @include('pages.coaches')
        @include('pages.messages')
        @include('pages.profile')
        @include('pages.events')
        @include('pages.create-event')
        @include('pages.settings')
        @include('pages.privacy')
        @include('pages.sitemap')
        @include('pages.instant-play')
    </main>

    {{-- Footer --}}
    <footer class="max-w-6xl w-full mx-auto px-4 py-8 pb-16 sm:pb-12 border-t border-slate-200">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            {{-- Left: Logo & Copyright --}}
            <div class="flex items-center gap-3">
                <img src="/img/logo.png" alt="LoveTennis" class="w-8 h-8 opacity-50 grayscale">
                <div class="text-slate-500 text-[11px] sm:text-sm font-black uppercase tracking-widest">
                    Copyright © 2026 chiuko. All rights reserved.
                </div>
            </div>
            
            {{-- Right: Contact & Links --}}
            <div class="flex flex-wrap justify-center md:justify-end items-center gap-x-6 gap-y-2 text-slate-500 text-[11px] sm:text-sm font-black tracking-widest">
                <div class="flex items-center gap-1">
                    <span class="opacity-70">建議來信：</span>
                    <a href="mailto:q8156697@gmail.com" class="text-blue-600 hover:text-blue-700 transition-colors lowercase">q8156697@gmail.com</a>
                </div>
                <div class="flex items-center gap-4">
                    <a href="https://www.threads.com/@chiuko_o" target="_blank" rel="noopener noreferrer" class="hover:text-slate-600 transition-colors" title="Threads">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12.186 24h-.007c-3.581-.024-6.334-1.205-8.184-3.51C2.35 18.44 1.5 15.586 1.472 12.01v-.017C1.5 8.418 2.35 5.564 3.995 3.51 5.845 1.205 8.598.024 12.179 0h.014c2.746.02 5.043.725 6.826 2.098 1.677 1.29 2.858 3.13 3.509 5.467l-2.04.569c-1.104-3.96-3.898-5.984-8.304-6.015-2.91.022-5.11.936-6.54 2.717C4.307 6.504 3.616 8.914 3.589 12c.027 3.086.718 5.496 2.055 7.164 1.43 1.781 3.631 2.695 6.54 2.717 2.623-.02 4.358-.631 5.8-2.045 1.647-1.613 1.618-3.593 1.09-4.798-.31-.71-.873-1.3-1.634-1.75-.192 1.352-.622 2.446-1.284 3.272-.886 1.102-2.14 1.704-3.73 1.79-1.202.065-2.361-.218-3.259-.801-1.063-.689-1.685-1.74-1.752-2.96-.065-1.182.408-2.256 1.332-3.022.85-.704 2.04-1.106 3.543-1.192 1.28-.073 2.446.06 3.476.398-.011-.742-.106-1.41-.283-1.993-.273-.89-.718-1.535-1.37-1.98-.694-.473-1.645-.73-2.749-.747l-.127.002c-1.372.028-2.517.507-3.408 1.425l-1.44-1.418c1.215-1.253 2.834-1.911 4.81-1.958l.157-.003c1.514.028 2.81.405 3.858 1.12.98.668 1.71 1.62 2.178 2.835.377.979.574 2.111.59 3.386.238.14.47.29.693.452 1.162.842 1.996 1.907 2.48 3.17.752 1.967.652 4.765-1.678 7.048-1.919 1.88-4.259 2.707-7.389 2.73Zm1.39-8.856c-.455-.105-.969-.158-1.53-.127-1.047.06-1.863.33-2.426.805-.526.445-.783 1.009-.745 1.633.042.752.464 1.33 1.187 1.631.638.265 1.438.348 2.199.305 1.04-.056 1.86-.458 2.439-1.193.498-.636.795-1.513.932-2.606-.65-.223-1.349-.367-2.056-.448Z"/></svg>
                    </a>
                    <a href="https://www.instagram.com/chiuko_o" target="_blank" rel="noopener noreferrer" class="hover:text-pink-500 transition-colors" title="Instagram">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                    </a>
                </div>
                <div class="flex items-center gap-6">
                    <a href="{{ config('tennis.sponsor_url') }}" target="_blank" rel="noopener noreferrer" 
                       class="flex items-center gap-1 text-pink-500 hover:text-pink-600 transition-colors group">
                        <svg class="w-3.5 h-3.5 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                        </svg>
                        <span class="uppercase">贊助我</span>
                    </a>
                    <a href="/privacy" @click.prevent="navigateTo('privacy')" class="hover:text-blue-600 uppercase transition-colors">隱私權政策</a>
                    <a href="/sitemap" @click.prevent="navigateTo('sitemap')" class="hover:text-blue-600 uppercase transition-colors">網站地圖</a>
                </div>
            </div>
        </div>
    </footer>

    {{-- Modal Components --}}
    <player-detail-modal :player="detailPlayer" :current-user="currentUser" @update:player="handlePlayerUpdate" :players="filteredPlayers" :stats="getDetailStats(detailPlayer)" :is-logged-in="isLoggedIn" :show-toast="showToast" :navigate-to="navigateTo" @close="detailPlayer = null" @open-match="p => { detailPlayer = null; openMatchModal(p); }" @open-profile="uid => { detailPlayer = null; openProfile(uid); }" @open-ntrp-guide="showNtrpGuide = true" @share="p => openShare(p)"></player-detail-modal>
    <share-modal v-model="shareModal.open" :player="shareModal.player"></share-modal>
    <match-modal v-model:open="matchModal.open" :player="matchModal.player" :is-sending="isSendingMatch" @submit="text => { matchModal.text = text; sendMatchRequest(); }"></match-modal>
    <ntrp-guide-modal v-model:open="showNtrpGuide" :descs="levelDescs"></ntrp-guide-modal>
    <message-detail-modal v-model:open="showMessageDetail" :target-user="selectedChatUser" :current-user="currentUser" @message-sent="onMessageSent" @navigate-to-profile="uid => openProfile(uid)"></message-detail-modal>
    <event-detail-modal 
        v-model:open="showEventDetail" 
        :event="activeEvent" 
        :comments="eventComments" 
        :comment-draft="eventCommentDraft"
        :current-user="currentUser"
        :is-submitting="eventSubmitting"
        @update:comment-draft="v => eventCommentDraft = v"
        @like="toggleEventLike"
        @join="joinEvent"
        @leave="leaveEvent"
        @comment="postEventComment"
        @delete-comment="deleteEventComment"
        @open-profile="openProfile"
        @close="showEventDetail = false">
    </event-detail-modal>
    <privacy-modal v-model="showPrivacy" :navigate-to="navigateTo"></privacy-modal>

    {{-- LINE Promo Modal --}}
    <transition name="modal">
        <div v-if="showLinePromo" class="fixed inset-0 z-[500] flex items-center justify-center p-4 sm:p-6 bg-slate-900/80 backdrop-blur-md modal-content">
            <div class="bg-gradient-to-br from-blue-600 to-blue-700 w-full max-w-md rounded-[40px] overflow-hidden shadow-2xl relative animate__animated animate__zoomIn animate__faster">
                {{-- Decorative Background --}}
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl -mr-32 -mt-32"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/5 rounded-full blur-2xl -ml-24 -mb-24"></div>
                
                {{-- Close Button --}}
                <button type="button" @click="showLinePromo = false" class="absolute top-5 right-5 z-20 p-2 bg-white/10 hover:bg-white/20 backdrop-blur rounded-xl transition-all border border-white/10">
                    <app-icon name="x" class-name="w-5 h-5 text-white/70"></app-icon>
                </button>

                {{-- Content --}}
                <div class="relative z-10 p-8 sm:p-10 text-white text-center space-y-6">
                    {{-- QR Code --}}
                    <div class="bg-white p-4 rounded-3xl inline-block shadow-2xl shadow-blue-900/30 mx-auto">
                        <img src="/img/lineqrcode.png" alt="LINE QR Code" class="w-40 h-40 sm:w-48 sm:h-48">
                    </div>

                    {{-- Title & Description --}}
                    <div class="space-y-2">
                        <h3 class="text-2xl sm:text-3xl font-black uppercase tracking-tight">即時收到約打通知！</h3>
                        <p class="text-blue-100 text-sm sm:text-base font-bold leading-relaxed">
                            加入 LoveTennis 官方 LINE 好友，<br class="hidden sm:block">不再錯過任何挑戰與邀約。
                        </p>
                    </div>

                    {{-- LINE ID Badge --}}
                    <div class="bg-white/20 backdrop-blur-md px-5 py-3 rounded-2xl inline-block">
                        <span class="text-[10px] font-bold text-blue-200 uppercase tracking-widest block mb-1">LINE ID</span>
                        <span class="text-lg font-black tracking-wider">@344epiuj</span>
                    </div>

                    {{-- Action Button --}}
                    <a href="https://line.me/R/ti/p/@344epiuj" target="_blank" 
                       class="block w-full bg-white text-blue-600 py-4 rounded-2xl font-black uppercase tracking-[0.2em] text-sm hover:bg-blue-50 transition-all shadow-xl shadow-blue-900/20">
                        立即加入好友
                    </a>

                    {{-- Subtle Hint --}}
                    <p class="text-[11px] text-blue-200/60 font-medium">
                        掃描 QR Code 或點擊按鈕即可加入
                    </p>
                </div>
            </div>
        </div>
    </transition>

    {{-- Global Loading Overlay --}}
    <div v-if="isLoading" class="fixed inset-0 bg-slate-950/50 backdrop-blur-sm z-[200] flex items-center justify-center">
        <div class="bg-white rounded-3xl p-8 shadow-2xl flex items-center gap-4">
            <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="font-black text-lg text-slate-600">處理中...</span>
        </div>
    </div>

    {{-- Toast Notifications --}}
    <div class="fixed bottom-28 left-4 right-4 sm:left-auto sm:right-8 sm:bottom-8 z-[1000] space-y-3">
        <transition-group name="toast">
            <div v-for="toast in toasts" :key="toast.id" 
                :class="['px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-3 w-full sm:w-auto sm:min-w-[280px] sm:max-w-md',
                    toast.type === 'success' ? 'bg-green-600 text-white' : 
                    toast.type === 'error' ? 'bg-red-600 text-white' : 
                    'bg-slate-900 text-white']">
                <svg v-if="toast.type === 'success'" class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <svg v-else-if="toast.type === 'error'" class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <app-icon v-else name="check-circle" class-name="w-6 h-6 shrink-0"></app-icon>
                <span class="font-bold text-sm">@{{ toast.message }}</span>
                <button @click="removeToast(toast.id)" class="ml-auto opacity-60 hover:opacity-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </transition-group>
    </div>

    {{-- Custom Confirm Dialog --}}
    <transition name="modal">
        <div v-if="confirmDialog.open" class="fixed inset-0 z-[250] flex items-center justify-center p-4 confirm-backdrop">
            <div class="bg-white w-full max-w-md rounded-[32px] shadow-2xl overflow-hidden animate__animated animate__zoomIn animate__faster">
                <div class="p-8 text-center">
                    <div :class="['w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-6',
                        confirmDialog.type === 'danger' ? 'bg-red-100' : 
                        confirmDialog.type === 'warning' ? 'bg-amber-100' : 'bg-blue-100']">
                        <svg v-if="confirmDialog.type === 'danger'" class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        <svg v-else-if="confirmDialog.type === 'warning'" class="w-8 h-8 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <svg v-else class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-black italic uppercase tracking-tight mb-3">@{{ confirmDialog.title }}</h3>
                    <p class="text-slate-500 font-medium">@{{ confirmDialog.message }}</p>
                </div>
                <div class="flex border-t border-slate-100">
                    <button @click="hideConfirm" class="flex-1 py-5 text-slate-500 font-black uppercase tracking-widest text-sm hover:bg-slate-50 transition-colors border-r border-slate-100">
                        @{{ confirmDialog.cancelText }}
                    </button>
                    <button @click="executeConfirm" :class="['flex-1 py-5 font-black uppercase tracking-widest text-sm transition-colors',
                        confirmDialog.type === 'danger' ? 'bg-red-500 text-white hover:bg-red-600' : 
                        confirmDialog.type === 'warning' ? 'bg-amber-500 text-white hover:bg-amber-600' : 'bg-blue-600 text-white hover:bg-blue-700']">
                        @{{ confirmDialog.confirmText }}
                    </button>
                </div>
            </div>
        </div>
    </transition>

    {{-- Mobile Navigation Dock --}}
    @include('partials.mobile-nav')
</div>

    {{-- Initial State for SEO / Fast Load --}}
    <script>
        window.__INITIAL_STATE__ = {
            players: @json($initialPlayers ?? []),
            events: @json($initialEvents ?? [])
        };
    </script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script src="{{ asset('js/mobile.js') }}"></script>
    <script src="{{ asset('js/app.js') }}"></script>

    {{-- Vue Application Logic --}}
    @include('partials.vue-scripts')

</body>
</html>
