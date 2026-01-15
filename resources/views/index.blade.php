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
    <link rel="icon" type="image/x-icon" href="/img/favicon/favicon.ico">
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
    <link rel="stylesheet" href="/vendor/animate/animate.min.css"/>
    
    {{-- External Scripts --}}
    <script src="/vendor/axios/axios.min.js"></script>
    <script src="/vendor/moveable/moveable.min.js" defer></script>
    <script src="/vendor/html2canvas/html2canvas.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/html-to-image@1.11.11/dist/html-to-image.min.js"></script>
    
    @include('partials.styles')
</head>
<body class="bg-slate-50 text-slate-900 leading-normal min-h-screen">
    {{-- Vanilla JS Pre-loader for LINE Login --}}
    <div id="auth-preloader" style="display: none; position: fixed; inset: 0; z-index: 99999; background: #fff; align-items: center; justify-content: center; flex-direction: column;">
        <div style="width: 4rem; height: 4rem; border-radius: 1rem; background: #06C755; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem; box-shadow: 0 10px 15px -3px rgba(6, 199, 85, 0.3);">
            <svg style="width: 2rem; height: 2rem; color: #fff;" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19.365 9.863c.349 0 .63.285.63.631 0 .345-.281.63-.63.63H17.61v1.125h1.755c.349 0 .63.283.63.63 0 .344-.281.629-.63.629h-2.386c-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.627-.63h2.386c.349 0 .63.285.63.63 0 .349-.281.63-.63.63H17.61v1.125h1.755zm-3.855 3.016c0 .27-.174.51-.432.596-.064.021-.133.031-.199.031-.211 0-.391-.09-.51-.25l-2.443-3.317v2.94c0 .344-.279.629-.631.629-.346 0-.626-.285-.626-.629V8.108c0-.27.173-.51.43-.595.06-.023.136-.033.194-.033.195 0 .375.104.495.254l2.462 3.33V8.108c0-.345.282-.63.63-.63.345 0 .63.285.63.63v4.771zm-5.741 0c0 .344-.282.629-.631.629-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.627-.63.349 0 .631.285.631.63v4.771zm-2.466.629H4.917c-.345 0-.63-.285-.63-.629V8.108c0-.345.285-.63.63-.63.348 0 .63.285.63.63v4.141h1.756c.348 0 .629.283.629.63 0 .344-.282.629-.629.629M24 10.314C24 4.943 18.615.572 12 .572S0 4.943 0 10.314c0 4.811 4.27 8.842 10.035 9.608.391.082.923.258 1.058.59.12.301.079.766.038 1.08l-.164 1.02c-.045.301-.24 1.186 1.049.645 1.291-.539 6.916-4.078 9.436-6.975C23.176 14.393 24 12.458 24 10.314"/>
            </svg>
        </div>
        <p style="font-size: 1.125rem; font-weight: 900; color: #0f172a; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.5rem;">登入中</p>
        <p style="font-size: 0.875rem; font-weight: 700; color: #94a3b8;">正在連接 LINE 帳號...</p>
    </div>
    <script>
        // Check immediately before anything else loads
        if (window.location.search.indexOf('line_token') > -1) {
            document.getElementById('auth-preloader').style.display = 'flex';
        }
    </script>

{{-- Vue Component Templates --}}
@include('components.vue-templates')
@include('components.modals')

<div id="app" v-cloak>
    {{-- LINE Login Loading Overlay --}}
    <transition name="fade">
        <div v-if="isAuthLoading" class="fixed inset-0 z-[9999] bg-white/95 backdrop-blur-xl flex items-center justify-center">
            <div class="text-center">
                <div class="w-16 h-16 mx-auto mb-6 rounded-2xl bg-[#06C755] flex items-center justify-center animate-pulse shadow-xl shadow-green-500/30">
                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19.365 9.863c.349 0 .63.285.63.631 0 .345-.281.63-.63.63H17.61v1.125h1.755c.349 0 .63.283.63.63 0 .344-.281.629-.63.629h-2.386c-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.627-.63h2.386c.349 0 .63.285.63.63 0 .349-.281.63-.63.63H17.61v1.125h1.755zm-3.855 3.016c0 .27-.174.51-.432.596-.064.021-.133.031-.199.031-.211 0-.391-.09-.51-.25l-2.443-3.317v2.94c0 .344-.279.629-.631.629-.346 0-.626-.285-.626-.629V8.108c0-.27.173-.51.43-.595.06-.023.136-.033.194-.033.195 0 .375.104.495.254l2.462 3.33V8.108c0-.345.282-.63.63-.63.345 0 .63.285.63.63v4.771zm-5.741 0c0 .344-.282.629-.631.629-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.627-.63.349 0 .631.285.631.63v4.771zm-2.466.629H4.917c-.345 0-.63-.285-.63-.629V8.108c0-.345.285-.63.63-.63.348 0 .63.285.63.63v4.141h1.756c.348 0 .629.283.629.63 0 .344-.282.629-.629.629M24 10.314C24 4.943 18.615.572 12 .572S0 4.943 0 10.314c0 4.811 4.27 8.842 10.035 9.608.391.082.923.258 1.058.59.12.301.079.766.038 1.08l-.164 1.02c-.045.301-.24 1.186 1.049.645 1.291-.539 6.916-4.078 9.436-6.975C23.176 14.393 24 12.458 24 10.314"/>
                    </svg>
                </div>
                <p class="text-lg font-black text-slate-900 uppercase tracking-widest mb-2">登入中</p>
                <p class="text-sm font-bold text-slate-400">正在連接 LINE 帳號...</p>
            </div>
        </div>
    </transition>

    {{-- Navigation --}}
    @include('partials.navigation')

    {{-- Main Content --}}
    <main class="max-w-6xl w-full mx-auto px-4 pt-6 sm:pt-10 pb-4 sm:pb-8">
        @include('pages.auth')
        @include('pages.home')
        @include('pages.create')
        @include('pages.list')
        @include('pages.messages')
        @include('pages.profile')
        @include('pages.events')
        @include('pages.create-event')
        @include('pages.settings')
        @include('pages.privacy')
        @include('pages.sitemap')
    </main>

    {{-- Footer --}}
    <footer class="max-w-6xl w-full mx-auto px-4 py-8 pb-16 sm:pb-12 border-t border-slate-200">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            {{-- Left: Logo & Copyright --}}
            <div class="flex items-center gap-3">
                <img src="/img/logo.png" alt="LoveTennis" class="w-8 h-8 opacity-50 grayscale">
                <div class="text-slate-400 text-[10px] sm:text-xs font-bold uppercase tracking-widest">
                    Copyright © 2026 chiuko. All rights reserved.
                </div>
            </div>
            
            {{-- Right: Contact & Links --}}
            <div class="flex flex-wrap justify-center md:justify-end items-center gap-x-6 gap-y-2 text-slate-400 text-[10px] sm:text-xs font-bold tracking-widest">
                <div class="flex items-center gap-1">
                    <span class="opacity-60">建議來信：</span>
                    <a href="mailto:q8156697@gmail.com" class="text-blue-600/80 hover:text-blue-600 transition-colors lowercase">q8156697@gmail.com</a>
                </div>
                <div class="flex items-center gap-6">
                    <a href="/privacy" @click.prevent="navigateTo('privacy')" class="hover:text-blue-600 uppercase transition-colors">隱私權政策</a>
                    <a href="/sitemap" @click.prevent="navigateTo('sitemap')" class="hover:text-blue-600 uppercase transition-colors">網站地圖</a>
                </div>
            </div>
        </div>
    </footer>

    {{-- Modal Components --}}
    <player-detail-modal :player="detailPlayer" @update:player="handlePlayerUpdate" :players="filteredPlayers" :stats="getDetailStats(detailPlayer)" :is-logged-in="isLoggedIn" :show-toast="showToast" :navigate-to="navigateTo" @close="detailPlayer = null" @open-match="p => { detailPlayer = null; openMatchModal(p); }" @open-profile="uid => { detailPlayer = null; openProfile(uid); }" @open-ntrp-guide="showNtrpGuide = true" @share="p => { shareModal.player = p; shareModal.open = true; }"></player-detail-modal>
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
        <div v-if="showLinePromo" class="fixed inset-0 z-[500] flex items-center justify-center p-4 sm:p-6 bg-slate-900/80 backdrop-blur-md modal-content" @click.self="showLinePromo = false">
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
        <div v-if="confirmDialog.open" class="fixed inset-0 z-[250] flex items-center justify-center p-4 confirm-backdrop" @click.self="hideConfirm">
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
            players: {!! isset($initialPlayers) ? $initialPlayers->toJson() : '[]' !!},
            events: {!! isset($initialEvents) ? $initialEvents->toJson() : '[]' !!}
        };
    </script>
    @include('partials.vue-scripts')

</body>
</html>
