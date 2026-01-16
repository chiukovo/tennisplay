{{-- Premium Redesigned Mobile Navigation Dock --}}
<div class="fixed bottom-0 left-0 right-0 w-full bg-slate-950/95 backdrop-blur-xl border-t border-white/10 p-1.5 flex justify-between items-center z-[150] md:hidden">
    {{-- Home --}}
    <a href="/" @click.prevent="navigateTo('home')" class="flex-1 flex flex-col items-center gap-0.5 py-1.5 rounded-xl transition-all relative" :class="view === 'home' ? 'text-blue-400' : 'text-slate-500'">
        <app-icon name="home" class-name="w-4 h-4"></app-icon>
        <span class="text-[7px] font-black uppercase tracking-widest">首頁</span>
        <div v-if="view === 'home'" class="absolute -bottom-0.5 w-1 h-1 bg-blue-400 rounded-full"></div>
    </a>

    {{-- List --}}
    <a href="/list" @click.prevent="navigateTo('list')" class="flex-1 flex flex-col items-center gap-0.5 py-1.5 rounded-xl transition-all relative" :class="view === 'list' ? 'text-blue-400' : 'text-slate-500'">
        <app-icon name="search" class-name="w-4 h-4"></app-icon>
        <span class="text-[7px] font-black uppercase tracking-widest">找球友</span>
        <div v-if="view === 'list'" class="absolute -bottom-0.5 w-1 h-1 bg-blue-400 rounded-full"></div>
    </a>

    {{-- Central Create Button --}}
    <div class="px-0.5">
        <a href="/create" @click.prevent="resetForm(); navigateTo('create')" class="flex items-center justify-center w-10 h-10 bg-blue-600 text-white rounded-xl hover:scale-105 active:scale-95 transition-all border-[3px] border-slate-900">
            <app-icon name="plus" class-name="w-5 h-5"></app-icon>
        </a>
    </div>

    {{-- Messages --}}
    <a href="/messages" @click.prevent="navigateTo('messages')" class="flex-1 flex flex-col items-center gap-0.5 py-1.5 rounded-xl transition-all relative" :class="view === 'messages' ? 'text-blue-400' : 'text-slate-500'">
        <div class="relative">
            <app-icon name="message-circle" class-name="w-4 h-4"></app-icon>
            <div v-if="hasUnread" class="absolute -top-0.5 -right-0.5 w-1.5 h-1.5 bg-red-500 rounded-full border border-slate-950"></div>
        </div>
        <span class="text-[7px] font-black uppercase tracking-widest">訊息</span>
        <div v-if="view === 'messages'" class="absolute -bottom-0.5 w-1 h-1 bg-blue-400 rounded-full"></div>
    </a>

    {{-- Profile / Auth --}}
    <a :href="isLoggedIn ? '/profile' : '/auth'" @click.prevent="isLoggedIn ? openProfile(currentUser.uid) : navigateTo('auth')" class="flex-1 flex flex-col items-center gap-0.5 py-1.5 rounded-xl transition-all relative" :class="view === 'profile' || view === 'auth' ? 'text-blue-400' : 'text-slate-500'">
        <div class="relative">
            <app-icon :name="isLoggedIn ? 'user' : 'line'" class-name="w-4 h-4"></app-icon>
            <div v-if="isLoggedIn && hasUnread" class="absolute -top-0.5 -right-0.5 w-1.5 h-1.5 bg-red-500 rounded-full border border-slate-950"></div>
        </div>
        <span class="text-[7px] font-black uppercase tracking-widest">@{{ isLoggedIn ? '個人' : '登入' }}</span>
        <div v-if="view === 'profile' || view === 'auth'" class="absolute -bottom-0.5 w-1 h-1 bg-blue-400 rounded-full"></div>
    </a>
</div>

