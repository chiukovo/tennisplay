<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $seo['title'] ?? 'LoveTennis | 專業網球約打媒合與球友卡社群' }}</title>
    <!-- SEO Meta Tags -->
    <meta name="description" content="{{ $seo['description'] ?? 'LoveTennis 是全台最專業的網球約打平台，提供職業級球友卡製作、透明約打費用與安全站內信媒合系統。' }}">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $seo['title'] ?? 'LoveTennis' }}">
    <meta property="og:description" content="{{ $seo['description'] ?? '全台最專業的網球約打平台' }}">
    <meta property="og:image" content="{{ url($seo['og_image'] ?? '/img/og-default.jpg') }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="{{ $seo['title'] ?? 'LoveTennis' }}">
    <meta property="twitter:description" content="{{ $seo['description'] ?? '全台最專業的網球約打平台' }}">
    <meta property="twitter:image" content="{{ url($seo['og_image'] ?? '/img/og-default.jpg') }}">
    
    <!-- Canonical Link -->
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Structured Data (JSON-LD) -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "WebSite",
      "name": "LoveTennis",
      "url": "{{ url('/') }}",
      "potentialAction": {
        "@type": "SearchAction",
        "target": "{{ url('/list') }}?q={search_term_string}",
        "query-input": "required name=search_term_string"
      },
      "description": "全台最專業的網球約打平台"
    }
    </script>
    
    {{-- Favicon --}}
    <link rel="icon" type="image/x-icon" href="/img/favicon/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/img/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/img/favicon/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/img/favicon/apple-touch-icon.png">
    <link rel="manifest" href="/img/favicon/site.webmanifest">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>[v-cloak] { display: none !important; }</style>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    {{-- External Scripts (Loaded in head for reliability) --}}
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://unpkg.com/moveable/dist/moveable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    
    @include('partials.styles')
</head>
<body class="bg-slate-50 text-slate-900 leading-normal">

{{-- Vue Component Templates --}}
@include('components.vue-templates')
@include('components.modals')

<div id="app" v-cloak>
    {{-- Navigation --}}
    @include('partials.navigation')

    {{-- Main Content --}}
    <main class="max-w-6xl mx-auto px-4 pt-6 sm:pt-10 min-h-screen pb-32 sm:pb-32">
        @include('pages.auth')
        @include('pages.home')
        @include('pages.create')
        @include('pages.list')
        @include('pages.messages')
        @include('pages.profile')
        @include('pages.events')
        @include('pages.create-event')
        @include('pages.settings')
    </main>

    {{-- Modal Components --}}
    <player-detail-modal v-model:player="detailPlayer" :players="filteredPlayers" :stats="getDetailStats(detailPlayer)" @close="detailPlayer = null" @open-match="p => { detailPlayer = null; openMatchModal(p); }"></player-detail-modal>
    <match-modal v-model:open="matchModal.open" :player="matchModal.player" @submit="text => { matchModal.text = text; sendMatchRequest(); }"></match-modal>
    <ntrp-guide-modal v-model:open="showNtrpGuide" :descs="levelDescs"></ntrp-guide-modal>
    <message-detail-modal v-model:open="showMessageDetail" :target-user="selectedChatUser" :current-user="currentUser" @message-sent="onMessageSent"></message-detail-modal>
    <event-detail-modal 
        v-model:open="showEventDetail" 
        :event="activeEvent" 
        :likes="eventLikes" 
        :comments="eventComments" 
        v-model:comment-draft="eventCommentDraft"
        :current-user="currentUser"
        @like="toggleEventLike" 
        @join="joinEvent" 
        @comment="submitEventComment"
        @delete-comment="deleteEventComment"
    ></event-detail-modal>

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
    <div class="fixed bottom-28 sm:bottom-8 right-4 sm:right-8 z-[180] space-y-3">
        <transition-group name="toast">
            <div v-for="toast in toasts" :key="toast.id" 
                :class="['px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-3 min-w-[280px] max-w-md',
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
