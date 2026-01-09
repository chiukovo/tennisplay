{{-- Navigation --}}
<nav class="bg-white/90 backdrop-blur-xl border-b sticky top-0 z-50">
    <div class="max-w-6xl mx-auto px-4 h-20 flex items-center justify-between gap-2">
        <div class="flex items-center gap-2 sm:gap-3 cursor-pointer shrink-0" @click="navigateTo('home')">
            <img src="/img/logo.png" alt="LoveTennis Logo" class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl object-contain">
            <div class="flex flex-col leading-none">
                <span class="font-black text-base sm:text-2xl tracking-tighter italic uppercase text-slate-900">LOVE<span class="text-blue-600">TENNIS</span></span>
                <span class="hidden sm:block text-[8px] sm:text-[10px] font-bold text-slate-400 tracking-[0.2em] uppercase">愛網球</span>
            </div>
        </div>
        
        <div class="hidden md:flex gap-10 text-sm font-black uppercase tracking-[0.2em] text-slate-400">
            <a href="/list" @click.prevent="navigateTo('list')" :class="view === 'list' ? 'text-blue-600 border-b-2 border-blue-600 pb-1' : 'hover:text-slate-900 transition-colors'">發現球友</a>
            <a href="/events" @click.prevent="navigateTo('events')" :class="view === 'events' || view === 'create-event' ? 'text-blue-600 border-b-2 border-blue-600 pb-1' : 'hover:text-slate-900 transition-colors'">開團揪球</a>
            <a href="/messages" @click.prevent="navigateTo('messages')" :class="['relative', view === 'messages' ? 'text-blue-600 border-b-2 border-blue-600 pb-1' : 'hover:text-slate-900 transition-colors']">
                約打訊息
                <div v-if="hasUnread" class="absolute -top-1 -right-3 w-2.5 h-2.5 bg-red-500 rounded-full animate-pulse border-2 border-white"></div>
            </a>
        </div>

        <div class="flex items-center gap-2 sm:gap-4 shrink-0">
            {{-- Not Logged In: LINE Login Button --}}
            <a v-if="!isLoggedIn" href="/auth" @click.prevent="navigateTo('auth')" class="flex items-center gap-2 bg-[#06C755] hover:bg-[#05b34c] text-white px-3 py-2 sm:px-4 sm:py-2.5 rounded-xl text-[10px] sm:text-xs font-black uppercase tracking-widest transition-all shadow-lg shadow-green-500/20">
                <app-icon name="line" fill="currentColor" stroke="none" class-name="w-4 h-4"></app-icon>
                <span class="hidden sm:inline">LINE 登入</span>
                <span class="sm:hidden">登入</span>
            </a>
            
            {{-- Logged In: User Dropdown --}}
            <div v-if="isLoggedIn" class="relative group">
                <button @click="showUserMenu = !showUserMenu" class="flex items-center text-slate-600 hover:text-slate-900 transition-colors">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 bg-blue-100 rounded-full flex items-center justify-center overflow-hidden border-2 border-white shadow-md">
                        <img v-if="currentUser?.line_picture_url" :src="currentUser.line_picture_url" class="w-full h-full object-cover">
                        <app-icon v-else name="user" class-name="w-5 h-5 text-blue-600"></app-icon>
                    </div>
                </button>
                {{-- Dropdown Menu - Click to toggle, click outside to close --}}
                <div v-show="showUserMenu" @click.stop class="absolute right-0 top-full pt-2 w-48 z-50">
                    <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 py-2">
                    <a href="/messages" @click.prevent="navigateTo('messages'); showUserMenu = false" class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors">
                        <app-icon name="mail" class-name="w-5 h-5"></app-icon>
                        <span>我的訊息</span>
                        <div v-if="hasUnread" class="ml-auto w-2 h-2 bg-red-500 rounded-full"></div>
                    </a>
                    <a href="/create" @click.prevent="navigateTo('create'); showUserMenu = false" class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors">
                        <app-icon name="plus" class-name="w-5 h-5"></app-icon>
                        <span>建立球友卡</span>
                    </a>
                    <a href="/profile" @click.prevent="openProfile(currentUser.id); showUserMenu = false" class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors">
                        <app-icon name="user" class-name="w-5 h-5"></app-icon>
                        <span>個人主頁</span>
                    </a>
                    <a href="/settings" @click.prevent="navigateTo('settings'); showUserMenu = false" class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors">
                        <app-icon name="zap" class-name="w-5 h-5"></app-icon>
                        <span>個人設置</span>
                    </a>
                    <div class="border-t border-slate-100 my-2"></div>
                    <button @click="logout(); showUserMenu = false" class="w-full flex items-center gap-3 px-4 py-3 text-sm font-bold text-red-500 hover:bg-red-50 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        <span>登出</span>
                    </button>
                    </div>
                </div>
                {{-- Click outside to close --}}
                <div v-if="showUserMenu" @click="showUserMenu = false" class="fixed inset-0 z-40"></div>
            </div>
            
            <a href="/create" @click.prevent="navigateTo('create')" class="bg-slate-950 text-white px-4 sm:px-6 py-2.5 sm:py-3 rounded-2xl text-[10px] sm:text-xs font-black uppercase tracking-widest hover:bg-blue-600 transition-all flex items-center gap-2 shadow-xl">
                <app-icon name="plus" class-name="w-4 h-4 sm:w-5 h-5"></app-icon>
                <span class="hidden sm:inline">製作球友卡</span>
                <span class="sm:hidden">製作</span>
            </a>
        </div>
    </div>
</nav>
