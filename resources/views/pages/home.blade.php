{{-- Home View --}}
<div v-if="view === 'home'" class="space-y-12 sm:space-y-20">
    {{-- Unified Premium Hero --}}
    <div class="group relative bg-[#0f172a] rounded-[32px] sm:rounded-[48px] p-6 sm:p-16 lg:p-24 text-white overflow-hidden shadow-2xl border border-white/5">
        {{-- Animated Grid Background --}}
        <div class="absolute inset-0 opacity-[0.15]" 
             style="background-image: linear-gradient(#3b82f6 1.5px, transparent 1.5px), linear-gradient(90deg, #3b82f6 1.5px, transparent 1.5px);
                    background-size: 40px 40px;
                    background-position: center center;
                    mask-image: radial-gradient(circle at center, black 0%, transparent 80%);
                    -webkit-mask-image: radial-gradient(circle at center, black 0%, transparent 80%);">
        </div>
        
        {{-- Ambient Lighting --}}
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-blue-600/30 rounded-full blur-[120px] pointer-events-none mix-blend-screen opacity-50 animate-subtle-pulse"></div>
        <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-indigo-600/20 rounded-full blur-[100px] pointer-events-none mix-blend-screen opacity-30"></div>
        
        <div class="relative z-10 grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-20 items-center">
            {{-- Left: Text Content --}}
            <div class="space-y-6 sm:space-y-8 text-center lg:text-left">
                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3">
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/5 border border-white/10 backdrop-blur-md shadow-sm">
                        <app-icon name="shield-check" class-name="w-3.5 h-3.5 sm:w-4 sm:h-4 text-emerald-400"></app-icon> 
                        <span class="text-[10px] sm:text-xs font-black uppercase tracking-[0.15em] text-slate-200">全台網球媒合新標竿</span>
                    </div>
                </div>
                
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black italic uppercase tracking-tighter leading-none drop-shadow-xl">
                    <span class="block text-transparent bg-clip-text bg-gradient-to-r from-white via-slate-200 to-slate-400">約球友</span>
                    <span class="block mt-2 text-transparent bg-clip-text bg-gradient-to-r from-blue-400 via-blue-500 to-indigo-500 pr-8 pb-2">
                        來 LoveTennis
                    </span>
                </h1>
                
                <p class="text-slate-300 max-w-xl mx-auto lg:mx-0 text-sm sm:text-lg font-medium leading-relaxed tracking-wide opacity-90">
                    全台最專業的網球約打平台。<br class="hidden sm:block">
                    LINE 快速登入，建立專屬球友卡，30秒即刻開打！
                </p>
                
                {{-- Mobile Swiper Cards (Above Buttons) --}}
                <div class="lg:hidden flex flex-col items-center mb-4">
                    <div class="swiper home-cards-swiper" style="width: 140px; height: 210px;">
                        <div class="swiper-wrapper">
                            <div v-if="players.length > 0" v-for="p in players.slice(0, 10)" :key="'swiper-' + p.id" class="swiper-slide">
                                <player-card :player="p" size="sm" class="w-full h-full"></player-card>
                            </div>
                            <template v-if="players.length === 0">
                                <div v-for="i in 3" :key="'placeholder-' + i" class="swiper-slide">
                                    <div class="w-full h-full rounded-xl overflow-hidden shadow-xl border border-white/10 bg-gradient-to-br from-slate-700 to-slate-800 flex items-center justify-center">
                                        <div class="text-center p-3">
                                            <div class="w-10 h-10 bg-slate-600 rounded-lg flex items-center justify-center mx-auto mb-2">
                                                <app-icon name="user" class-name="w-5 h-5 text-slate-400"></app-icon>
                                            </div>
                                            <p class="text-slate-400 text-[10px] font-bold">成為第一位球友</p>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                    <span class="text-white/40 text-[10px] font-medium mt-2">← 滑動探索 →</span>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-3 justify-center lg:justify-start">
                    <a href="/create" @click.prevent="navigateTo('create')" class="group/btn relative px-6 py-3 sm:px-10 sm:py-5 bg-blue-600 rounded-xl sm:rounded-3xl overflow-hidden shadow-[0_0_40px_-10px_rgba(37,99,235,0.5)] transition-transform active:scale-95">
                        <div class="absolute inset-0 bg-gradient-to-r from-blue-400/0 via-white/20 to-blue-400/0 translate-x-[-200%] group-hover/btn:translate-x-[200%] transition-transform duration-1000 ease-in-out"></div>
                        <span class="relative font-black text-sm sm:text-xl text-white tracking-wider flex items-center justify-center gap-2">
                            <span>製作球友卡</span>
                            <app-icon name="arrow-right" class-name="w-4 h-4 sm:w-5 sm:h-5 group-hover/btn:translate-x-1 transition-transform"></app-icon>
                        </span>
                    </a>
                    
                    <a href="/list" @click.prevent="navigateTo('list')" class="px-6 py-3 sm:px-10 sm:py-5 bg-white/5 border border-white/10 rounded-xl sm:rounded-3xl hover:bg-white/10 transition-all active:scale-95 backdrop-blur-sm flex items-center justify-center">
                        <span class="font-black text-sm sm:text-xl text-white tracking-wider">瀏覽列表</span>
                    </a>
                </div>
                
                {{-- Quick Links --}}
                <div class="pt-6 border-t border-white/5">
                    <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3 opacity-80">
                         <button v-for="item in [
                            {icon: 'search', label: '找球友', to: 'list'},
                            {icon: 'calendar', label: '揪團', to: 'events'},
                            {icon: 'plus', label: '開團', to: 'create-event'}
                         ]" :key="item.label" 
                           @click="navigateTo(item.to)" 
                           class="flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-800/50 hover:bg-slate-700/50 border border-white/5 transition-colors group/link">
                            <div class="p-1 rounded-lg bg-white/10 group-hover/link:bg-blue-500 group-hover/link:text-white transition-colors">
                                <app-icon :name="item.icon" class-name="w-3 h-3"></app-icon>
                            </div>
                            <span class="text-[11px] font-bold uppercase tracking-widest text-slate-300 group-hover/link:text-white">@{{ item.label }}</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Right: Swiper Cards (Desktop Only) --}}
            <div class="relative hidden lg:flex justify-center py-8 lg:py-0">
                <div class="swiper home-cards-swiper-desktop" style="width: 240px; height: 360px;">
                    <div class="swiper-wrapper">
                        <div v-if="players.length > 0" v-for="p in players.slice(0, 10)" :key="'swiper-desktop-' + p.id" class="swiper-slide">
                            <player-card :player="p" size="sm" class="w-full h-full"></player-card>
                        </div>
                        <template v-if="players.length === 0">
                            <div v-for="i in 3" :key="'placeholder-desktop-' + i" class="swiper-slide">
                                <div class="w-full h-full rounded-2xl overflow-hidden shadow-2xl border-2 border-white/10 bg-gradient-to-br from-slate-700 to-slate-800 flex items-center justify-center">
                                    <div class="text-center p-4">
                                        <div class="w-12 h-12 bg-slate-600 rounded-xl flex items-center justify-center mx-auto mb-3">
                                            <app-icon name="user" class-name="w-6 h-6 text-slate-400"></app-icon>
                                        </div>
                                        <p class="text-slate-400 text-xs font-bold">成為第一位球友</p>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- How It Works - 3 Steps --}}
    <section class="pt-8 sm:pt-12">
        <div class="text-center mb-8 sm:mb-16">
            <h2 class="text-2xl sm:text-4xl font-black italic uppercase tracking-tighter mb-2 sm:mb-4">
                三步驟，<span class="text-blue-600">輕鬆開始</span>
            </h2>
            <p class="text-slate-400 font-medium text-sm sm:text-lg max-w-xl mx-auto">從建立球友卡到找到球伴，只需要簡單三步驟</p>
        </div>
        
        {{-- Mobile: Vertical Stack / Desktop: Grid --}}
        <div class="grid grid-cols-1 gap-4 pt-6 sm:grid-cols-3 sm:gap-8">
            <div class="relative bg-white rounded-[24px] sm:rounded-[40px] p-4 sm:p-10 border border-slate-100 shadow-lg flex items-center gap-4 sm:block sm:text-center group hover:-translate-y-1 sm:hover:-translate-y-2 transition-all">
                <div class="sm:absolute sm:-top-6 sm:left-1/2 sm:-translate-x-1/2 w-10 h-10 sm:w-12 sm:h-12 bg-blue-600 rounded-xl sm:rounded-2xl flex items-center justify-center text-white font-black text-base sm:text-xl shadow-xl shadow-blue-600/30 shrink-0">1</div>
                <div class="flex-1 sm:pt-6">
                    <div class="hidden sm:flex w-20 h-20 bg-blue-50 rounded-3xl items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-sm sm:text-xl font-black italic uppercase tracking-tight mb-0.5 sm:mb-3">建立球友卡</h3>
                    <p class="text-slate-400 font-medium text-xs sm:text-base leading-relaxed">上傳照片、設定等級，30秒完成</p>
                </div>
            </div>
            
            <div class="relative bg-white rounded-[24px] sm:rounded-[40px] p-4 sm:p-10 border border-slate-100 shadow-lg flex items-center gap-4 sm:block sm:text-center group hover:-translate-y-1 sm:hover:-translate-y-2 transition-all">
                <div class="sm:absolute sm:-top-6 sm:left-1/2 sm:-translate-x-1/2 w-10 h-10 sm:w-12 sm:h-12 bg-slate-900 rounded-xl sm:rounded-2xl flex items-center justify-center text-white font-black text-base sm:text-xl shadow-xl shrink-0">2</div>
                <div class="flex-1 sm:pt-6">
                    <div class="hidden sm:flex w-20 h-20 bg-slate-100 rounded-3xl items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-10 h-10 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-sm sm:text-xl font-black italic uppercase tracking-tight mb-0.5 sm:mb-3">瀏覽球友列表</h3>
                    <p class="text-slate-400 font-medium text-xs sm:text-base leading-relaxed">依地區、等級篩選球伴</p>
                </div>
            </div>
            
            <div class="relative bg-white rounded-[24px] sm:rounded-[40px] p-4 sm:p-10 border border-slate-100 shadow-lg flex items-center gap-4 sm:block sm:text-center group hover:-translate-y-1 sm:hover:-translate-y-2 transition-all">
                <div class="sm:absolute sm:-top-6 sm:left-1/2 sm:-translate-x-1/2 w-10 h-10 sm:w-12 sm:h-12 bg-green-500 rounded-xl sm:rounded-2xl flex items-center justify-center text-white font-black text-base sm:text-xl shadow-xl shadow-green-500/30 shrink-0">3</div>
                <div class="flex-1 sm:pt-6">
                    <div class="hidden sm:flex w-20 h-20 bg-green-50 rounded-3xl items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </div>
                    <h3 class="text-sm sm:text-xl font-black italic uppercase tracking-tight mb-0.5 sm:mb-3">發送約打邀請</h3>
                    <p class="text-slate-400 font-medium text-xs sm:text-base leading-relaxed">站內訊息，安全有保障</p>
                </div>
            </div>
        </div>
    </section>

    {{-- LINE Notify CTA --}}
    <section class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-[32px] sm:rounded-[48px] p-6 sm:p-12 text-white relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl -mr-24 -mt-24"></div>
        <div class="absolute bottom-0 left-0 w-56 h-56 bg-white/5 rounded-full blur-2xl -ml-20 -mb-20"></div>
        <div class="relative z-10 grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
            <div class="space-y-4">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-white/15 rounded-full border border-white/20 text-[10px] sm:text-xs font-black uppercase tracking-widest">
                    <app-icon name="bell" class-name="w-4 h-4"></app-icon>
                    通知優先
                </div>
                <h2 class="text-2xl sm:text-4xl font-black italic uppercase tracking-tighter leading-tight">
                    加入好友<br><span class="text-white/90">立即收到通知</span>
                </h2>
                <p class="text-blue-100 font-bold text-sm sm:text-lg leading-relaxed max-w-xl">
                    新活動建立、修改或取消，第一時間推播提醒。加入官方 LINE 好友，才不會錯過好場次。
                </p>
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="https://line.me/R/ti/p/@344epiuj" target="_blank" class="inline-flex items-center justify-center gap-2 bg-white text-blue-700 px-6 py-3 rounded-2xl font-black uppercase tracking-widest text-xs sm:text-sm shadow-xl hover:scale-105 transition-all">
                        <app-icon name="line" fill="currentColor" stroke="none" class-name="w-4 h-4"></app-icon>
                        立即加入
                    </a>
                    <button type="button" @click="showLinePromo = true" class="inline-flex items-center justify-center gap-2 bg-white/10 text-white px-6 py-3 rounded-2xl font-black uppercase tracking-widest text-xs sm:text-sm border border-white/20 hover:bg-white/20 transition-all">
                        <app-icon name="qr-code" class-name="w-4 h-4"></app-icon>
                        顯示 QR
                    </button>
                </div>
            </div>
            <div class="flex items-center justify-center md:justify-end">
                <div class="bg-white p-4 rounded-3xl shadow-2xl shadow-blue-900/30">
                    <img src="/img/lineqrcode.png" alt="LINE QR" class="w-40 h-40 sm:w-48 sm:h-48">
                </div>
            </div>
        </div>
    </section>


    {{-- Stats Counter --}}
    <section class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-[32px] sm:rounded-[48px] p-8 sm:p-20 text-white relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 left-0 w-full h-full bg-pattern"></div>
        </div>
        <div class="relative z-10">
            <div class="text-center mb-6 sm:mb-12">
                <h2 class="text-xl sm:text-4xl font-black italic uppercase tracking-tighter">為什麼選擇 LoveTennis？</h2>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-8 text-center">
                <div class="space-y-1 sm:space-y-2">
                    <div class="text-3xl sm:text-6xl font-black italic">100%</div>
                    <div class="text-[10px] sm:text-sm font-black uppercase tracking-widest text-blue-100">完全免費</div>
                </div>
                <div class="space-y-1 sm:space-y-2">
                    <div class="text-3xl sm:text-6xl font-black italic">22</div>
                    <div class="text-[10px] sm:text-sm font-black uppercase tracking-widest text-blue-100">全台縣市</div>
                </div>
                <div class="space-y-1 sm:space-y-2">
                    <div class="text-3xl sm:text-6xl font-black italic">7.0</div>
                    <div class="text-[10px] sm:text-sm font-black uppercase tracking-widest text-blue-100">NTRP 分級</div>
                </div>
                <div class="space-y-1 sm:space-y-2">
                    <div class="text-3xl sm:text-6xl font-black italic">30秒</div>
                    <div class="text-[10px] sm:text-sm font-black uppercase tracking-widest text-blue-100">快速建卡</div>
                </div>
            </div>
        </div>
    </section>

    {{-- Professional Standards --}}
    <div class="bg-white rounded-[32px] sm:rounded-[48px] p-6 sm:p-20 border border-slate-100 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-blue-50/50 blur-3xl rounded-full -mr-32 -mt-32"></div>
        <div class="relative z-10 grid grid-cols-1 lg:grid-cols-2 gap-8 sm:gap-16 items-center">
            <div class="space-y-5 sm:space-y-8">
                <h2 class="text-2xl sm:text-4xl font-black italic uppercase tracking-tighter leading-tight">
                    專業網球社交<br><span class="text-blue-600">從這裡開始</span>
                </h2>
                <p class="text-slate-500 text-sm sm:text-lg font-medium leading-relaxed">
                    LoveTennis 致力於建立高品質的網球社群。透過數位球友卡展示實力，找到志同道合的夥伴。
                </p>
                <div class="space-y-4 sm:space-y-6">
                    <div class="flex items-start gap-3 sm:gap-4">
                        <div class="bg-blue-600 p-1.5 sm:p-2 rounded-lg mt-0.5">
                            <app-icon name="shield-check" class-name="w-4 h-4 sm:w-5 sm:h-5 text-white"></app-icon>
                        </div>
                        <div>
                            <h4 class="font-black uppercase italic tracking-tight text-sm sm:text-lg">實名與實力認證</h4>
                            <p class="text-slate-400 font-medium text-xs sm:text-base">鼓勵使用者上傳真實照片與詳細 NTRP 說明。</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 sm:gap-4">
                        <div class="bg-slate-900 p-1.5 sm:p-2 rounded-lg mt-0.5">
                            <app-icon name="mail" class-name="w-4 h-4 sm:w-5 sm:h-5 text-white"></app-icon>
                        </div>
                        <div>
                            <h4 class="font-black uppercase italic tracking-tight text-sm sm:text-lg">隱私保護通訊</h4>
                            <p class="text-slate-400 font-medium text-xs sm:text-base">您的個人聯絡資訊將受到嚴格保護。</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3 sm:gap-6">
                <div class="bg-slate-50 p-4 sm:p-8 rounded-[20px] sm:rounded-[32px] text-center space-y-1 sm:space-y-2 hover:bg-blue-50 transition-colors">
                    <div class="text-2xl sm:text-4xl font-black italic text-blue-600">100%</div>
                    <div class="text-[10px] sm:text-xs font-black uppercase tracking-widest text-slate-400">免費刊登</div>
                </div>
                <div class="bg-slate-50 p-4 sm:p-8 rounded-[20px] sm:rounded-[32px] text-center space-y-1 sm:space-y-2 hover:bg-slate-100 transition-colors">
                    <div class="text-2xl sm:text-4xl font-black italic text-slate-900">24/7</div>
                    <div class="text-[10px] sm:text-xs font-black uppercase tracking-widest text-slate-400">即時媒合</div>
                </div>
                <div class="bg-slate-50 p-4 sm:p-8 rounded-[20px] sm:rounded-[32px] text-center space-y-1 sm:space-y-2 hover:bg-slate-100 transition-colors">
                    <div class="text-2xl sm:text-4xl font-black italic text-slate-900">NTRP</div>
                    <div class="text-[10px] sm:text-xs font-black uppercase tracking-widest text-slate-400">精準分級</div>
                </div>
                <div class="bg-slate-50 p-4 sm:p-8 rounded-[20px] sm:rounded-[32px] text-center space-y-1 sm:space-y-2 hover:bg-green-50 transition-colors">
                    <div class="text-2xl sm:text-4xl font-black italic text-green-600">SAFE</div>
                    <div class="text-[10px] sm:text-xs font-black uppercase tracking-widest text-slate-400">安全社群</div>
                </div>
            </div>
        </div>
    </div>

    {{-- CTA Banner --}}
    <section class="bg-slate-950 rounded-[32px] sm:rounded-[48px] p-8 sm:p-20 text-center text-white relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-600/20 to-purple-600/20"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl"></div>
        <div class="relative z-10 space-y-5 sm:space-y-8 max-w-2xl mx-auto">
            <h2 class="text-2xl sm:text-5xl font-black italic uppercase tracking-tighter">
                準備好了嗎？
            </h2>
            <p class="text-slate-400 text-sm sm:text-lg font-medium">
                立即加入 LoveTennis，開始您的網球社交之旅！
            </p>
            <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 justify-center">
                <a href="/create" @click.prevent="navigateTo('create')" class="bg-blue-600 text-white px-8 py-4 sm:px-10 sm:py-5 rounded-2xl sm:rounded-3xl font-black text-base sm:text-lg hover:scale-105 transition-all shadow-2xl shadow-blue-500/40">
                    免費建立球友卡
                </a>
            </div>
            <p class="text-slate-500 text-xs sm:text-sm font-medium">無需信用卡 · 完全免費 · 30 秒完成</p>
        </div>
    </section>
</div>
