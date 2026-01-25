{{-- Premium Redesigned Mobile Navigation Dock --}}
<div class="fixed bottom-0 left-0 right-0 w-full bg-slate-950/95 backdrop-blur-xl border-t border-white/10 px-1.5 pt-1.5 pb-[calc(0.375rem+env(safe-area-inset-bottom))] flex justify-between items-center z-[150] md:hidden">


    {{-- List --}}
    <a href="/list" @click.prevent="navigateTo('list')" class="flex-1 flex flex-col items-center gap-0.5 py-1.5 rounded-xl transition-all relative" :class="view === 'list' ? 'text-blue-400' : 'text-slate-500'">
        <app-icon name="search" class-name="w-4 h-4"></app-icon>
        <span class="text-[10px] font-black uppercase tracking-wider">找球友</span>
        <div v-if="view === 'list'" class="absolute -bottom-0.5 w-1 h-1 bg-blue-400 rounded-full"></div>
    </a>

    {{-- Coaches --}}
    <a href="/coaches" @click.prevent="navigateTo('coaches')" class="flex-1 flex flex-col items-center gap-0.5 py-1.5 rounded-xl transition-all relative" :class="view === 'coaches' ? 'text-blue-400' : 'text-slate-500'">
        <app-icon name="users" class-name="w-4 h-4"></app-icon>
        <span class="text-[10px] font-black uppercase tracking-wider">找教練</span>
        <div v-if="view === 'coaches'" class="absolute -bottom-0.5 w-1 h-1 bg-blue-400 rounded-full"></div>
    </a>

    {{-- Central Action Button --}}
    <div class="px-0.5 -mt-4">
        <button @click="navigateTo('instant-play')" 
            class="flex flex-col items-center justify-center relative animate-in zoom-in duration-500 delay-300">
            <div class="w-14 h-14 bg-blue-600 rounded-full flex flex-col items-center justify-center shadow-xl shadow-blue-500/40 border-4 border-slate-950 active:scale-95 transition-all group"
                :class="view === 'instant-play' ? 'bg-blue-500' : 'bg-blue-600'">
                <app-icon name="zap" class-name="w-5 h-5 text-white group-hover:animate-pulse"></app-icon>
                <span class="text-[10px] font-black text-white uppercase tracking-tighter -mt-0.5">打球拉</span>
            </div>
        </button>
    </div>

    {{-- Messages --}}
    <a href="/messages" @click.prevent="navigateTo('messages')" class="flex-1 flex flex-col items-center gap-0.5 py-1.5 rounded-xl transition-all relative" :class="view === 'messages' ? 'text-blue-400' : 'text-slate-500'">
        <div class="relative">
            <app-icon name="message-circle" class-name="w-4 h-4"></app-icon>
            <div v-if="hasUnread" class="absolute -top-0.5 -right-0.5 w-1.5 h-1.5 bg-red-500 rounded-full border border-slate-950"></div>
        </div>
        <span class="text-[10px] font-black uppercase tracking-wider">訊息</span>
        <div v-if="view === 'messages'" class="absolute -bottom-0.5 w-1 h-1 bg-blue-400 rounded-full"></div>
    </a>

    {{-- Profile / Auth --}}
    <a :href="isLoggedIn ? '/profile' : '/auth'" @click.prevent="isLoggedIn ? openProfile(currentUser.uid) : navigateTo('auth')" class="flex-1 flex flex-col items-center gap-0.5 py-1.5 rounded-xl transition-all relative" :class="view === 'profile' || view === 'auth' ? 'text-blue-400' : 'text-slate-500'">
        <div class="relative">
            <app-icon :name="isLoggedIn ? 'user' : 'line'" class-name="w-4 h-4"></app-icon>
            <div v-if="isLoggedIn && hasUnread" class="absolute -top-0.5 -right-0.5 w-1.5 h-1.5 bg-red-500 rounded-full border border-slate-950"></div>
        </div>
        <span class="text-[10px] font-black uppercase tracking-wider">@{{ isLoggedIn ? '個人' : '登入' }}</span>
        <div v-if="view === 'profile' || view === 'auth'" class="absolute -bottom-0.5 w-1 h-1 bg-blue-400 rounded-full"></div>
    </a>
</div>

