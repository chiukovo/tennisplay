<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AceMate | 專業網球約打媒合與球友卡社群</title>
    <!-- SEO Meta Tags -->
    <meta name="description" content="AceMate 是全台最專業的網球約打平台，提供職業級球友卡製作、透明約打費用與安全站內信媒合系統。">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>[v-cloak] { display: none !important; }</style>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <script src="https://daybrush.com/moveable/release/latest/dist/moveable.min.js"></script>
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
    <main class="max-w-6xl mx-auto px-4 pt-6 sm:pt-10 min-h-screen pb-48 sm:pb-32">
        {{-- Auth View --}}
        @include('pages.auth')
        
        {{-- Home View --}}
        @include('pages.home')
        
        {{-- Create View --}}
        @include('pages.create')
        
        {{-- List View --}}
        @include('pages.list')
        
        {{-- Messages View --}}
        @include('pages.messages')
        
        {{-- My Cards View --}}
        @include('pages.mycards')
    </main>

    {{-- Modal Components --}}
    <player-detail-modal :player="detailPlayer" :stats="getDetailStats(detailPlayer)" @close="detailPlayer = null" @open-match="p => { detailPlayer = null; openMatchModal(p); }" />
    <match-modal v-model:open="matchModal.open" :player="matchModal.player" @submit="text => { matchModal.text = text; sendMatchRequest(); }" />

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
    <div class="fixed bottom-32 sm:bottom-8 right-4 sm:right-8 z-[180] space-y-3">
        <transition-group name="toast">
            <div v-for="toast in toasts" :key="toast.id" 
                :class="['px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-3 min-w-[280px] max-w-md animate__animated animate__fadeInRight',
                    toast.type === 'success' ? 'bg-green-600 text-white' : 
                    toast.type === 'error' ? 'bg-red-600 text-white' : 
                    'bg-slate-900 text-white']">
                <svg v-if="toast.type === 'success'" class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <svg v-else-if="toast.type === 'error'" class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
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

    {{-- Mobile Navigation Dock --}}
    @include('partials.mobile-nav')
</div>

{{-- Vue Scripts --}}
@include('partials.vue-scripts')

</body>
</html>
