{{-- Home View --}}
<div v-if="view === 'home'" class="space-y-12 sm:space-y-20">
    {{-- Unified Premium Hero --}}
    <div class="bg-slate-950 rounded-[32px] sm:rounded-[64px] p-6 sm:p-16 lg:p-24 text-white relative overflow-hidden shadow-2xl">
        <div class="absolute inset-0 opacity-20 pointer-events-none bg-[radial-gradient(circle_at_50%_0%,rgba(59,130,246,0.3),transparent)]"></div>
        {{-- Floating Tennis Balls Decoration --}}
        <div class="absolute top-10 left-10 w-20 h-20 bg-yellow-400 rounded-full blur-3xl opacity-10 animate-pulse"></div>
        <div class="absolute bottom-20 right-16 w-16 h-16 bg-blue-500 rounded-full blur-2xl opacity-20 animate-pulse"></div>
        
        <div class="relative z-10 grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-20 items-center">
            {{-- Left: Text Content --}}
            <div class="space-y-5 sm:space-y-6 text-center lg:text-left">
                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 sm:px-4 sm:py-2 bg-white/5 rounded-full border border-white/10 text-white text-[10px] sm:text-xs font-black uppercase tracking-[0.15em]">
                        <app-icon name="shield-check" class-name="w-3.5 h-3.5 sm:w-4 sm:h-4 text-blue-400"></app-icon> 全台網球媒合新標竿
                    </div>
                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 sm:px-4 sm:py-2 bg-[#06C755]/20 rounded-full border border-[#06C755]/30 text-[#06C755] text-[10px] sm:text-xs font-black uppercase tracking-wider">
                        <app-icon name="line" fill="currentColor" stroke="none" class-name="w-3.5 h-3.5 sm:w-4 sm:h-4"></app-icon> LINE 秒註冊
                    </div>
                </div>
                <h1 class="text-3xl sm:text-6xl lg:text-7xl font-black italic uppercase tracking-tighter leading-[0.9]">
                    找球友，<br><span class="text-blue-500">來 LoveTennis</span>
                </h1>
                <p class="text-slate-400 max-w-xl mx-auto lg:mx-0 text-sm sm:text-xl font-medium leading-relaxed">
                    全台最專業的網球約打平台。LINE 快速登入，3 秒建立球友卡，即刻開始約打！
                </p>
                <div class="flex flex-col sm:flex-row gap-3 sm:gap-5 justify-center lg:justify-start pt-2">
                    <a href="/create" @click.prevent="navigateTo('create')" class="bg-blue-600 text-white px-8 py-4 sm:px-10 sm:py-5 rounded-2xl sm:rounded-3xl font-black text-base sm:text-xl hover:scale-105 transition-all shadow-2xl shadow-blue-500/40 text-center">製作球友卡</a>
                    <a href="/list" @click.prevent.prevent="navigateTo('list')" class="bg-white/5 text-white border border-white/10 px-8 py-4 sm:px-10 sm:py-5 rounded-2xl sm:rounded-3xl font-black text-base sm:text-xl hover:bg-white/10 transition-all backdrop-blur-md text-center">球友列表</a>
                </div>
            </div>

            {{-- Right: Card Showcase Mockup --}}
            <div class="relative flex justify-center lg:justify-end py-4 sm:py-6 lg:py-0 overflow-hidden">
                <div class="relative w-[200px] sm:w-full sm:max-w-[340px] aspect-[2.5/3.5] group">
                    {{-- Secondary Image (Action) --}}
                    <div class="absolute -bottom-3 -left-3 sm:-bottom-10 sm:-left-10 w-full z-10 transform -rotate-6 group-hover:-rotate-3 transition-transform duration-1000 shadow-[0_40px_80px_-15px_rgba(0,0,0,0.7)] rounded-[16px] sm:rounded-[32px] overflow-hidden border-2 sm:border-4 border-white/10">
                        <img src="/img/card2.jpg" alt="Player Action" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/60 to-transparent"></div>
                    </div>
                    {{-- Primary Image (Card) --}}
                    <div class="relative w-full z-20 transform rotate-3 group-hover:rotate-0 transition-transform duration-1000 shadow-[0_50px_100px_-20px_rgba(0,0,0,0.8)] rounded-[16px] sm:rounded-[32px] overflow-hidden border-2 sm:border-4 border-white/10">
                        <img src="/img/card1.jpg" alt="Player Card Mockup" class="w-full h-full object-cover">
                    </div>
                    
                    {{-- Floating Badges --}}
                    <div class="absolute top-1/2 -right-12 z-0 hidden lg:block opacity-20 transform rotate-90 scale-150">
                        <span class="text-6xl font-black tracking-tighter italic uppercase text-white">LoveTennis</span>
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
        
        {{-- Mobile: Horizontal Scroll / Desktop: Grid --}}
        <div class="flex overflow-x-auto no-scrollbar snap-x snap-mandatory gap-4 pb-4 pt-6 -mx-4 px-4 sm:mx-0 sm:px-0 md:grid md:grid-cols-3 md:gap-8">
            <div class="relative bg-white rounded-[28px] sm:rounded-[40px] p-6 sm:p-10 border border-slate-100 shadow-lg text-center min-w-[240px] sm:min-w-0 snap-center shrink-0 group hover:-translate-y-2 transition-all">
                <div class="absolute -top-4 sm:-top-6 left-1/2 -translate-x-1/2 w-8 h-8 sm:w-12 sm:h-12 bg-blue-600 rounded-xl sm:rounded-2xl flex items-center justify-center text-white font-black text-sm sm:text-xl shadow-xl shadow-blue-600/30">1</div>
                <div class="pt-2 sm:pt-6">
                    <div class="w-12 h-12 sm:w-20 sm:h-20 bg-blue-50 rounded-2xl sm:rounded-3xl flex items-center justify-center mx-auto mb-3 sm:mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 sm:w-10 sm:h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-sm sm:text-xl font-black italic uppercase tracking-tight mb-1 sm:mb-3">建立球友卡</h3>
                    <p class="text-slate-400 font-medium text-[11px] sm:text-base leading-relaxed">上傳照片、設定等級，30秒完成</p>
                </div>
            </div>
            
            <div class="relative bg-white rounded-[28px] sm:rounded-[40px] p-6 sm:p-10 border border-slate-100 shadow-lg text-center min-w-[240px] sm:min-w-0 snap-center shrink-0 group hover:-translate-y-2 transition-all">
                <div class="absolute -top-4 sm:-top-6 left-1/2 -translate-x-1/2 w-8 h-8 sm:w-12 sm:h-12 bg-slate-900 rounded-xl sm:rounded-2xl flex items-center justify-center text-white font-black text-sm sm:text-xl shadow-xl">2</div>
                <div class="pt-2 sm:pt-6">
                    <div class="w-12 h-12 sm:w-20 sm:h-20 bg-slate-100 rounded-2xl sm:rounded-3xl flex items-center justify-center mx-auto mb-3 sm:mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 sm:w-10 sm:h-10 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-sm sm:text-xl font-black italic uppercase tracking-tight mb-1 sm:mb-3">瀏覽球友列表</h3>
                    <p class="text-slate-400 font-medium text-[11px] sm:text-base leading-relaxed">依地區、等級篩選球伴</p>
                </div>
            </div>
            
            <div class="relative bg-white rounded-[28px] sm:rounded-[40px] p-6 sm:p-10 border border-slate-100 shadow-lg text-center min-w-[240px] sm:min-w-0 snap-center shrink-0 group hover:-translate-y-2 transition-all">
                <div class="absolute -top-4 sm:-top-6 left-1/2 -translate-x-1/2 w-8 h-8 sm:w-12 sm:h-12 bg-green-500 rounded-xl sm:rounded-2xl flex items-center justify-center text-white font-black text-sm sm:text-xl shadow-xl shadow-green-500/30">3</div>
                <div class="pt-2 sm:pt-6">
                    <div class="w-12 h-12 sm:w-20 sm:h-20 bg-green-50 rounded-2xl sm:rounded-3xl flex items-center justify-center mx-auto mb-3 sm:mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 sm:w-10 sm:h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </div>
                    <h3 class="text-sm sm:text-xl font-black italic uppercase tracking-tight mb-1 sm:mb-3">發送約打邀請</h3>
                    <p class="text-slate-400 font-medium text-[11px] sm:text-base leading-relaxed">站內訊息，安全有保障</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Featured Players --}}
    <section>
        <div class="flex items-center justify-between mb-12">
            <h2 class="text-3xl font-black italic uppercase tracking-tighter flex items-center gap-4">
                <div class="w-1.5 h-10 bg-blue-600 rounded-full"></div> 推薦戰友
            </h2>
            <a v-if="players.length > 0" href="/list" @click.prevent="navigateTo('list')" class="text-blue-600 text-sm font-black uppercase tracking-widest border-b-2 border-blue-600/10 pb-1">顯示更多</a>
        </div>
        
        {{-- Has Players --}}
        <div v-if="players.length > 0" class="flex overflow-x-auto no-scrollbar snap-x snap-mandatory gap-8 pb-8 -mx-4 px-4 sm:mx-0 sm:px-0 sm:grid sm:grid-cols-2 lg:grid-cols-3 sm:gap-12">
            <player-card v-for="p in players.slice(0, 3)" :key="p.id" :player="p" @click="showDetail(p)" class="min-w-[280px] sm:min-w-0 snap-center"></player-card>
        </div>
        
        {{-- Empty State --}}
        <div v-else class="bg-gradient-to-br from-slate-50 to-white rounded-[48px] border-2 border-dashed border-slate-200 p-12 text-center">
            <div class="bg-blue-50 w-20 h-20 rounded-3xl flex items-center justify-center mx-auto mb-6">
                <app-icon name="user" class-name="w-10 h-10 text-blue-300"></app-icon>
            </div>
            <h3 class="text-xl font-black italic uppercase tracking-tight mb-3">成為第一位球友！</h3>
            <p class="text-slate-400 font-medium mb-6 max-w-md mx-auto">目前還沒有球友加入，立即建立您的專業球友卡，開始您的網球社交之旅！</p>
            <a href="/create" @click.prevent="navigateTo('create')" class="inline-flex items-center gap-3 bg-blue-600 text-white px-8 py-4 rounded-2xl font-black uppercase tracking-widest hover:bg-blue-700 transition-all shadow-xl">
                <app-icon name="plus" class-name="w-5 h-5"></app-icon>
                建立球友卡
            </a>
        </div>
    </section>

    {{-- Features --}}
    <div class="flex overflow-x-auto no-scrollbar snap-x snap-mandatory gap-4 pb-4 -mx-4 px-4 sm:mx-0 sm:px-0 md:grid md:grid-cols-3 md:gap-10">
        <div v-for="f in features" :key="f.title" class="bg-white p-6 sm:p-10 rounded-[28px] sm:rounded-[40px] shadow-sm border border-slate-100 hover:shadow-xl hover:-translate-y-2 transition-all min-w-[260px] sm:min-w-0 snap-center">
            <div class="bg-blue-50 w-12 h-12 sm:w-16 sm:h-16 rounded-2xl sm:rounded-3xl flex items-center justify-center text-blue-600 mb-4 sm:mb-8 shadow-inner">
                <app-icon :name="f.icon" class-name="w-6 h-6 sm:w-8 sm:h-8"></app-icon>
            </div>
            <h3 class="text-base sm:text-xl font-black italic uppercase tracking-tighter mb-2 sm:mb-4">@{{ f.title }}</h3>
            <p class="text-slate-500 text-xs sm:text-base font-medium leading-relaxed">@{{ f.desc }}</p>
        </div>
    </div>

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
    <section class="bg-slate-950 rounded-[32px] sm:rounded-[48px] p-8 sm:p-20 text-center text-white relative overflow-hidden mb-20 sm:mb-0">
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
