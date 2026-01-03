{{-- Mobile Navigation Dock --}}
<div class="fixed bottom-6 left-1/2 -translate-x-1/2 w-[94%] max-w-md bg-slate-950/95 backdrop-blur-3xl border border-white/10 rounded-[32px] p-2 flex justify-between items-center shadow-[0_20px_50px_rgba(0,0,0,0.6)] z-[150]">
    <a href="/" @click.prevent="navigateTo('home')" class="flex-1 flex flex-col items-center gap-1 py-3 rounded-2xl transition-all" :class="view === 'home' ? 'text-blue-400 bg-white/5' : 'text-slate-500'">
        <app-icon name="home" class-name="w-5.5 h-5.5"></app-icon>
        <span class="text-[10px] font-black uppercase tracking-widest">Home</span>
    </a>
    <a href="/list" @click.prevent="navigateTo('list')" class="flex-1 flex flex-col items-center gap-1 py-3 rounded-2xl transition-all" :class="view === 'list' ? 'text-blue-400 bg-white/5' : 'text-slate-500'">
        <app-icon name="search" class-name="w-5.5 h-5.5"></app-icon>
        <span class="text-[10px] font-black uppercase tracking-widest">Hall</span>
    </a>
    <a href="/create" @click.prevent="navigateTo('create')" class="relative -mt-10 group px-2">
        <div class="absolute inset-0 bg-blue-600 rounded-full blur-2xl opacity-40 group-hover:opacity-100 transition-opacity"></div>
        <div class="relative bg-blue-600 text-white w-14 h-14 rounded-2xl flex items-center justify-center border-4 border-slate-950 shadow-2xl transition-all hover:scale-110">
            <app-icon name="plus" class-name="w-8 h-8"></app-icon>
        </div>
    </a>
    <a href="/messages" @click.prevent="navigateTo('messages')" class="flex-1 flex flex-col items-center gap-1 py-3 rounded-2xl transition-all" :class="view === 'messages' ? 'text-blue-400 bg-white/5' : 'text-slate-500'">
        <div class="relative">
            <app-icon name="mail" class-name="w-5.5 h-5.5"></app-icon>
            <div v-if="hasUnread" class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-slate-950"></div>
        </div>
        <span class="text-[10px] font-black uppercase tracking-widest">Mail</span>
    </a>
    <a :href="isLoggedIn ? '/profile' : '/auth'" @click.prevent="navigateTo(isLoggedIn ? 'profile' : 'auth')" class="flex-1 flex flex-col items-center gap-1 py-3 text-slate-500">
        <app-icon name="user" class-name="w-5.5 h-5.5"></app-icon>
        <span class="text-[10px] font-black uppercase tracking-widest">Me</span>
    </a>
</div>
