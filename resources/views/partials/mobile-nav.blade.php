{{-- Mobile Navigation Dock --}}
<div class="fixed bottom-4 left-1/2 -translate-x-1/2 w-[92%] max-w-md bg-slate-950/95 backdrop-blur-3xl border border-white/10 rounded-[24px] p-1.5 flex justify-between items-center shadow-[0_20px_50px_rgba(0,0,0,0.6)] z-[150] md:hidden">
    <a href="/" @click.prevent="navigateTo('home')" class="flex-1 flex flex-col items-center gap-1 py-1.5 rounded-xl transition-all" :class="view === 'home' ? 'text-blue-400 bg-white/5' : 'text-slate-500'">
        <app-icon name="home" class-name="w-5 h-5"></app-icon>
        <span class="text-[9px] font-black uppercase tracking-widest">Home</span>
    </a>
    <a href="/list" @click.prevent="navigateTo('list')" class="flex-1 flex flex-col items-center gap-1 py-1.5 rounded-xl transition-all" :class="view === 'list' ? 'text-blue-400 bg-white/5' : 'text-slate-500'">
        <app-icon name="search" class-name="w-5 h-5"></app-icon>
        <span class="text-[9px] font-black uppercase tracking-widest">Hall</span>
    </a>
    <a href="/create" @click.prevent="navigateTo('create')" class="relative -mt-6 group px-2">
        <div class="absolute inset-0 bg-blue-600 rounded-full blur-2xl opacity-40 group-hover:opacity-100 transition-opacity"></div>
        <div class="relative bg-blue-600 text-white w-12 h-12 rounded-xl flex items-center justify-center border-4 border-slate-950 shadow-2xl transition-all hover:scale-110">
            <app-icon name="plus" class-name="w-7 h-7"></app-icon>
        </div>
    </a>
    <a href="/messages" @click.prevent="navigateTo('messages')" class="flex-1 flex flex-col items-center gap-1 py-1.5 rounded-xl transition-all" :class="view === 'messages' ? 'text-blue-400 bg-white/5' : 'text-slate-500'">
        <div class="relative">
            <app-icon name="mail" class-name="w-5 h-5"></app-icon>
            <div v-if="hasUnread" class="absolute -top-1 -right-1 w-2 h-2 bg-red-500 rounded-full border-2 border-slate-950"></div>
        </div>
        <span class="text-[9px] font-black uppercase tracking-widest">Mail</span>
    </a>
    {{-- Profile / Login / Logout --}}
    <template v-if="isLoggedIn">
        <button @click="logout()" class="flex-1 flex flex-col items-center gap-1 py-1.5 text-slate-500 hover:text-red-400 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
            </svg>
            <span class="text-[9px] font-black uppercase tracking-widest">登出</span>
        </button>
    </template>
    <template v-else>
        <a href="/auth" @click.prevent="navigateTo('auth')" class="flex-1 flex flex-col items-center gap-1 py-1.5 text-slate-500" :class="view === 'auth' ? 'text-blue-400 bg-white/5' : ''">
            <app-icon name="user" class-name="w-5 h-5"></app-icon>
            <span class="text-[9px] font-black uppercase tracking-widest">登入</span>
        </a>
    </template>
</div>
