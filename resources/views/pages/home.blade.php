{{-- Home View --}}
<div v-if="view === 'home'" class="space-y-20">
    {{-- Hero --}}
    <div class="bg-slate-950 rounded-[48px] sm:rounded-[64px] p-10 sm:p-24 text-center text-white relative overflow-hidden shadow-2xl">
        <div class="absolute inset-0 opacity-20 pointer-events-none bg-[radial-gradient(circle_at_50%_0%,rgba(59,130,246,0.3),transparent)]"></div>
        {{-- Floating Tennis Balls Decoration --}}
        <div class="absolute top-10 left-10 w-20 h-20 bg-yellow-400 rounded-full blur-2xl opacity-20 animate-pulse"></div>
        <div class="absolute bottom-20 right-16 w-16 h-16 bg-blue-500 rounded-full blur-xl opacity-30 animate-pulse"></div>
        <div class="relative z-10 space-y-8">
            <div class="inline-flex items-center gap-3 px-5 py-2.5 bg-white/5 rounded-full border border-white/10 text-white text-xs font-black uppercase tracking-[0.3em]">
                <app-icon name="shield-check" class-name="w-5 h-5 text-blue-400"></app-icon> 全台網球媒合新標竿
            </div>
            <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black italic uppercase tracking-tighter leading-none">
                找球友，<span class="text-blue-600">就上 AceMate</span>
            </h1>
            <p class="text-slate-400 max-w-2xl mx-auto text-lg sm:text-xl font-medium leading-relaxed">
                全台最專業的網球約打媒合平台。製作專屬球友卡，在社群大廳獲得曝光，完全免費刊登。
            </p>
            <div class="flex flex-col sm:flex-row gap-5 justify-center pt-8 px-4">
                <a href="/create" @click.prevent="navigateTo('create')" class="bg-blue-600 text-white px-12 py-5 rounded-3xl font-black text-xl hover:scale-105 transition-all shadow-2xl shadow-blue-500/40 text-center">製作球友卡</a>
                <a href="/list" @click.prevent="navigateTo('list')" class="bg-white/5 text-white border border-white/10 px-12 py-5 rounded-3xl font-black text-xl hover:bg-white/10 transition-all backdrop-blur-md text-center">瀏覽球友大廳</a>
            </div>
        </div>
    </div>

    {{-- How It Works - 3 Steps --}}
    <section class="py-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl sm:text-4xl font-black italic uppercase tracking-tighter mb-4">
                三步驟，<span class="text-blue-600">輕鬆開始</span>
            </h2>
            <p class="text-slate-400 font-medium text-lg max-w-xl mx-auto">從建立球友卡到找到球伴，只需要簡單三步驟</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
            {{-- Connecting Line --}}
            <div class="hidden md:block absolute top-20 left-1/6 right-1/6 h-0.5 bg-gradient-to-r from-transparent via-slate-200 to-transparent"></div>
            
            <div class="relative bg-white rounded-[40px] p-10 border border-slate-100 shadow-lg text-center group hover:-translate-y-2 transition-all">
                <div class="absolute -top-6 left-1/2 -translate-x-1/2 w-12 h-12 bg-blue-600 rounded-2xl flex items-center justify-center text-white font-black text-xl shadow-xl shadow-blue-600/30">1</div>
                <div class="pt-6">
                    <div class="w-20 h-20 bg-blue-50 rounded-3xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-black italic uppercase tracking-tight mb-3">建立球友卡</h3>
                    <p class="text-slate-400 font-medium">上傳照片、設定 NTRP 等級和地區，30 秒內完成您的專業球友檔案</p>
                </div>
            </div>
            
            <div class="relative bg-white rounded-[40px] p-10 border border-slate-100 shadow-lg text-center group hover:-translate-y-2 transition-all">
                <div class="absolute -top-6 left-1/2 -translate-x-1/2 w-12 h-12 bg-slate-900 rounded-2xl flex items-center justify-center text-white font-black text-xl shadow-xl">2</div>
                <div class="pt-6">
                    <div class="w-20 h-20 bg-slate-100 rounded-3xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-10 h-10 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-black italic uppercase tracking-tight mb-3">瀏覽球友大廳</h3>
                    <p class="text-slate-400 font-medium">依地區、等級篩選，找到符合您程度的理想球伴</p>
                </div>
            </div>
            
            <div class="relative bg-white rounded-[40px] p-10 border border-slate-100 shadow-lg text-center group hover:-translate-y-2 transition-all">
                <div class="absolute -top-6 left-1/2 -translate-x-1/2 w-12 h-12 bg-green-500 rounded-2xl flex items-center justify-center text-white font-black text-xl shadow-xl shadow-green-500/30">3</div>
                <div class="pt-6">
                    <div class="w-20 h-20 bg-green-50 rounded-3xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-black italic uppercase tracking-tight mb-3">發送約打邀請</h3>
                    <p class="text-slate-400 font-medium">透過站內訊息聯繫，安全隱私有保障，一起上場揮拍！</p>
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
            <player-card v-for="p in players.slice(0, 3)" :key="p.id" :player="p" @click="showDetail(p)" class="min-w-[280px] sm:min-w-0 snap-center" />
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
    <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
        <div v-for="f in features" :key="f.title" class="bg-white p-10 rounded-[40px] shadow-sm border border-slate-100 hover:shadow-xl hover:-translate-y-2 transition-all">
            <div class="bg-blue-50 w-16 h-16 rounded-3xl flex items-center justify-center text-blue-600 mb-8 shadow-inner">
                <app-icon :name="f.icon" class-name="w-8 h-8"></app-icon>
            </div>
            <h3 class="text-xl font-black italic uppercase tracking-tighter mb-4">@{{ f.title }}</h3>
            <p class="text-slate-500 text-base font-medium leading-relaxed">@{{ f.desc }}</p>
        </div>
    </div>

    {{-- Stats Counter --}}
    <section class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-[48px] p-12 sm:p-20 text-white relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 left-0 w-full h-full bg-pattern"></div>
        </div>
        <div class="relative z-10">
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-4xl font-black italic uppercase tracking-tighter">為什麼選擇 AceMate？</h2>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-8 text-center">
                <div class="space-y-2">
                    <div class="text-5xl sm:text-6xl font-black italic">100%</div>
                    <div class="text-sm font-black uppercase tracking-widest text-blue-100">完全免費</div>
                </div>
                <div class="space-y-2">
                    <div class="text-5xl sm:text-6xl font-black italic">22</div>
                    <div class="text-sm font-black uppercase tracking-widest text-blue-100">全台縣市</div>
                </div>
                <div class="space-y-2">
                    <div class="text-5xl sm:text-6xl font-black italic">7.0</div>
                    <div class="text-sm font-black uppercase tracking-widest text-blue-100">NTRP 分級</div>
                </div>
                <div class="space-y-2">
                    <div class="text-5xl sm:text-6xl font-black italic">30秒</div>
                    <div class="text-sm font-black uppercase tracking-widest text-blue-100">快速建卡</div>
                </div>
            </div>
        </div>
    </section>

    {{-- Professional Standards --}}
    <div class="bg-white rounded-[48px] p-10 sm:p-20 border border-slate-100 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-blue-50/50 blur-3xl rounded-full -mr-32 -mt-32"></div>
        <div class="relative z-10 grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            <div class="space-y-8">
                <h2 class="text-4xl font-black italic uppercase tracking-tighter leading-tight">
                    專業網球社交<br><span class="text-blue-600">從這裡開始</span>
                </h2>
                <p class="text-slate-500 text-lg font-medium leading-relaxed">
                    AceMate 不僅僅是一個約球網站，我們致力於建立一個高品質、誠信且專業的網球社群。透過數位球友卡，您可以更直觀地展示實力，並找到志同道合的夥伴。
                </p>
                <div class="space-y-6">
                    <div class="flex items-start gap-4">
                        <div class="bg-blue-600 p-2 rounded-lg mt-1">
                            <app-icon name="shield-check" class-name="w-5 h-5 text-white"></app-icon>
                        </div>
                        <div>
                            <h4 class="font-black uppercase italic tracking-tight text-lg">實名與實力認證</h4>
                            <p class="text-slate-400 font-medium">鼓勵使用者上傳真實照片與詳細 NTRP 說明，建立互信基礎。</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="bg-slate-900 p-2 rounded-lg mt-1">
                            <app-icon name="mail" class-name="w-5 h-5 text-white"></app-icon>
                        </div>
                        <div>
                            <h4 class="font-black uppercase italic tracking-tight text-lg">隱私保護通訊</h4>
                            <p class="text-slate-400 font-medium">在確認約打意向之前，您的個人聯絡資訊將受到嚴格保護。</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-6">
                <div class="bg-slate-50 p-8 rounded-[32px] text-center space-y-2 hover:bg-blue-50 transition-colors">
                    <div class="text-4xl font-black italic text-blue-600">100%</div>
                    <div class="text-xs font-black uppercase tracking-widest text-slate-400">免費刊登</div>
                </div>
                <div class="bg-slate-50 p-8 rounded-[32px] text-center space-y-2 hover:bg-slate-100 transition-colors">
                    <div class="text-4xl font-black italic text-slate-900">24/7</div>
                    <div class="text-xs font-black uppercase tracking-widest text-slate-400">即時媒合</div>
                </div>
                <div class="bg-slate-50 p-8 rounded-[32px] text-center space-y-2 hover:bg-slate-100 transition-colors">
                    <div class="text-4xl font-black italic text-slate-900">NTRP</div>
                    <div class="text-xs font-black uppercase tracking-widest text-slate-400">精準分級</div>
                </div>
                <div class="bg-slate-50 p-8 rounded-[32px] text-center space-y-2 hover:bg-green-50 transition-colors">
                    <div class="text-4xl font-black italic text-green-600">SAFE</div>
                    <div class="text-xs font-black uppercase tracking-widest text-slate-400">安全社群</div>
                </div>
            </div>
        </div>
    </div>

    {{-- CTA Banner --}}
    <section class="bg-slate-950 rounded-[48px] p-12 sm:p-20 text-center text-white relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-600/20 to-purple-600/20"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl"></div>
        <div class="relative z-10 space-y-8 max-w-2xl mx-auto">
            <h2 class="text-3xl sm:text-5xl font-black italic uppercase tracking-tighter">
                準備好了嗎？
            </h2>
            <p class="text-slate-400 text-lg font-medium">
                立即加入 AceMate，建立您的專業球友卡，開始您的網球社交之旅！
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/create" @click.prevent="navigateTo('create')" class="bg-blue-600 text-white px-10 py-5 rounded-3xl font-black text-lg hover:scale-105 transition-all shadow-2xl shadow-blue-500/40">
                    免費建立球友卡
                </a>
            </div>
            <p class="text-slate-500 text-sm font-medium">無需信用卡 · 完全免費 · 30 秒完成</p>
        </div>
    </section>
</div>
