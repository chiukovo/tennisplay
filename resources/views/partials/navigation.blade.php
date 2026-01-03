{{-- Navigation --}}
<nav class="bg-white/90 backdrop-blur-xl border-b sticky top-0 z-50">
    <div class="max-w-6xl mx-auto px-4 h-20 flex items-center justify-between gap-2">
        <div class="flex items-center gap-2 sm:gap-3 cursor-pointer shrink-0" @click="view = 'home'">
            <div class="bg-blue-600 p-1.5 sm:p-2 rounded-xl shadow-lg">
                <app-icon name="trophy" class-name="text-white w-5 h-5 sm:w-6 h-6"></app-icon>
            </div>
            <div class="flex flex-col leading-none">
                <span class="font-black text-lg sm:text-2xl tracking-tighter italic uppercase text-slate-900">Ace<span class="text-blue-600">Mate</span></span>
                <span class="text-[8px] sm:text-[10px] font-bold text-slate-400 tracking-[0.2em] uppercase">愛思拍檔</span>
            </div>
        </div>
        
        <div class="hidden md:flex gap-10 text-sm font-black uppercase tracking-[0.2em] text-slate-400">
            <button @click="view = 'list'" :class="view === 'list' ? 'text-blue-600 border-b-2 border-blue-600 pb-1' : 'hover:text-slate-900 transition-colors'">發現球友</button>
            <button @click="view = 'messages'" :class="['relative', view === 'messages' ? 'text-blue-600 border-b-2 border-blue-600 pb-1' : 'hover:text-slate-900 transition-colors']">
                約打訊息
                <div v-if="hasUnread" class="absolute -top-1 -right-3 w-2.5 h-2.5 bg-red-500 rounded-full animate-pulse border-2 border-white"></div>
            </button>
        </div>

        <div class="flex items-center gap-2 sm:gap-4 shrink-0">
            <button v-if="!isLoggedIn" @click="view = 'auth'" class="hidden sm:block text-slate-400 hover:text-slate-900 text-xs font-black uppercase tracking-widest transition-all">登入 / 註冊</button>
            <button @click="view = 'create'" class="bg-slate-950 text-white px-4 sm:px-6 py-2.5 sm:py-3 rounded-2xl text-[10px] sm:text-xs font-black uppercase tracking-widest hover:bg-blue-600 transition-all flex items-center gap-2 shadow-xl">
                <app-icon name="plus" class-name="w-4 h-4 sm:w-5 h-5"></app-icon>
                <span class="hidden sm:inline">製作球友卡</span>
                <span class="sm:hidden">製作</span>
            </button>
        </div>
    </div>
</nav>
