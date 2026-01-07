{{-- Premium Redesigned Mobile Navigation Dock --}}
<div class="fixed bottom-6 left-1/2 -translate-x-1/2 w-[90%] max-w-md bg-slate-950/90 backdrop-blur-2xl border border-white/10 rounded-[32px] p-2 flex justify-between items-center shadow-[0_25px_50px_-12px_rgba(0,0,0,0.7)] z-[150] md:hidden">
    {{-- Home --}}
    <a href="/" @click.prevent="navigateTo('home')" class="flex-1 flex flex-col items-center gap-1 py-2 rounded-2xl transition-all relative" :class="view === 'home' ? 'text-blue-400' : 'text-slate-500'">
        <app-icon name="home" class-name="w-6 h-6"></app-icon>
        <span class="text-[9px] font-black uppercase tracking-widest">首頁</span>
        <div v-if="view === 'home'" class="absolute -bottom-1 w-1 h-1 bg-blue-400 rounded-full"></div>
    </a>

    {{-- List --}}
    <a href="/list" @click.prevent="navigateTo('list')" class="flex-1 flex flex-col items-center gap-1 py-2 rounded-2xl transition-all relative" :class="view === 'list' ? 'text-blue-400' : 'text-slate-500'">
        <app-icon name="search" class-name="w-6 h-6"></app-icon>
        <span class="text-[9px] font-black uppercase tracking-widest">大廳</span>
        <div v-if="view === 'list'" class="absolute -bottom-1 w-1 h-1 bg-blue-400 rounded-full"></div>
    </a>

    {{-- Central Create Button --}}
    <div class="px-2">
        <a href="/create" @click.prevent="resetForm(); navigateTo('create')" class="flex items-center justify-center w-14 h-14 bg-blue-600 text-white rounded-2xl shadow-[0_10px_20px_rgba(37,99,235,0.4)] hover:scale-110 active:scale-95 transition-all border-4 border-slate-900">
            <app-icon name="plus" class-name="w-8 h-8"></app-icon>
        </a>
    </div>

    {{-- Messages --}}
    <a href="/messages" @click.prevent="navigateTo('messages')" class="flex-1 flex flex-col items-center gap-1 py-2 rounded-2xl transition-all relative" :class="view === 'messages' ? 'text-blue-400' : 'text-slate-500'">
        <div class="relative">
            <app-icon name="mail" class-name="w-6 h-6"></app-icon>
            <div v-if="hasUnread" class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-slate-950"></div>
        </div>
        <span class="text-[9px] font-black uppercase tracking-widest">訊息</span>
        <div v-if="view === 'messages'" class="absolute -bottom-1 w-1 h-1 bg-blue-400 rounded-full"></div>
    </a>

    {{-- Profile / Auth --}}
    <div v-if="isLoggedIn" class="flex-1 flex flex-col items-center gap-1 py-2 relative" @click="showUserMenu = !showUserMenu">
        <div class="w-7 h-7 rounded-full overflow-hidden border-2" :class="showUserMenu ? 'border-blue-400' : 'border-white/20'">
            <img v-if="currentUser?.line_picture_url" :src="currentUser.line_picture_url" class="w-full h-full object-cover">
            <app-icon v-else name="user" class-name="w-full h-full text-slate-400"></app-icon>
        </div>
        <span class="text-[9px] font-black uppercase tracking-widest text-slate-500">帳號</span>
        <div v-if="showUserMenu" class="absolute -bottom-1 w-1 h-1 bg-blue-400 rounded-full"></div>
    </div>
    <a v-else href="/auth" @click.prevent="navigateTo('auth')" class="flex-1 flex flex-col items-center gap-1 py-2 rounded-2xl transition-all" :class="view === 'auth' ? 'text-[#06C755]' : 'text-slate-500'">
        <div class="w-6 h-6 bg-[#06C755] rounded-lg flex items-center justify-center">
            <app-icon name="line" fill="white" stroke="none" class-name="w-4 h-4"></app-icon>
        </div>
        <span class="text-[9px] font-black uppercase tracking-widest">登入</span>
    </a>
</div>
