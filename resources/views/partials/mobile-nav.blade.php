{{-- Premium Redesigned Mobile Navigation Dock --}}
<div class="fixed bottom-6 left-1/2 -translate-x-1/2 w-[92%] max-w-md bg-slate-950/90 backdrop-blur-2xl border border-white/10 rounded-[32px] p-2 flex justify-between items-center shadow-[0_25px_50px_-12px_rgba(0,0,0,0.7)] z-[150] md:hidden">
    {{-- Home --}}
    <a href="/" @click.prevent="navigateTo('home')" class="flex-1 flex flex-col items-center gap-1 py-2 rounded-2xl transition-all relative" :class="view === 'home' ? 'text-blue-400' : 'text-slate-500'">
        <app-icon name="home" class-name="w-5 h-5"></app-icon>
        <span class="text-[8px] font-black uppercase tracking-widest">首頁</span>
        <div v-if="view === 'home'" class="absolute -bottom-1 w-1 h-1 bg-blue-400 rounded-full"></div>
    </a>

    {{-- List --}}
    <a href="/list" @click.prevent="navigateTo('list')" class="flex-1 flex flex-col items-center gap-1 py-2 rounded-2xl transition-all relative" :class="view === 'list' ? 'text-blue-400' : 'text-slate-500'">
        <app-icon name="search" class-name="w-5 h-5"></app-icon>
        <span class="text-[8px] font-black uppercase tracking-widest">大廳</span>
        <div v-if="view === 'list'" class="absolute -bottom-1 w-1 h-1 bg-blue-400 rounded-full"></div>
    </a>

    {{-- Central Create Button --}}
    <div class="px-1">
        <a href="/create" @click.prevent="resetForm(); navigateTo('create')" class="flex items-center justify-center w-12 h-12 bg-blue-600 text-white rounded-2xl shadow-[0_10px_20px_rgba(37,99,235,0.4)] hover:scale-110 active:scale-95 transition-all border-4 border-slate-900">
            <app-icon name="plus" class-name="w-7 h-7"></app-icon>
        </a>
    </div>

    {{-- Events --}}
    <a href="/events" @click.prevent="navigateTo('events')" class="flex-1 flex flex-col items-center gap-1 py-2 rounded-2xl transition-all relative" :class="view === 'events' || view === 'create-event' ? 'text-blue-400' : 'text-slate-500'">
        <app-icon name="calendar" class-name="w-5 h-5"></app-icon>
        <span class="text-[8px] font-black uppercase tracking-widest">開團</span>
        <div v-if="view === 'events' || view === 'create-event'" class="absolute -bottom-1 w-1 h-1 bg-blue-400 rounded-full"></div>
    </a>

    {{-- Profile / Auth --}}
    <a :href="isLoggedIn ? '/profile' : '/auth'" @click.prevent="isLoggedIn ? openProfile(currentUser.id) : navigateTo('auth')" class="flex-1 flex flex-col items-center gap-1 py-2 rounded-2xl transition-all relative" :class="view === 'profile' || view === 'auth' ? 'text-blue-400' : 'text-slate-500'">
        <div class="relative">
            <app-icon :name="isLoggedIn ? 'user' : 'line'" class-name="w-5 h-5"></app-icon>
            <div v-if="isLoggedIn && hasUnread" class="absolute -top-0.5 -right-0.5 w-2 h-2 bg-red-500 rounded-full border-2 border-slate-950"></div>
        </div>
        <span class="text-[8px] font-black uppercase tracking-widest">@{{ isLoggedIn ? '個人' : '登入' }}</span>
        <div v-if="view === 'profile' || view === 'auth'" class="absolute -bottom-1 w-1 h-1 bg-blue-400 rounded-full"></div>
    </a>
</div>

