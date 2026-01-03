{{-- Home View --}}
<div v-if="view === 'home'" class="space-y-20">
    {{-- Hero --}}
    <div class="bg-slate-950 rounded-[48px] sm:rounded-[64px] p-10 sm:p-24 text-center text-white relative overflow-hidden shadow-2xl">
        <div class="absolute inset-0 opacity-20 pointer-events-none bg-[radial-gradient(circle_at_50%_0%,rgba(59,130,246,0.3),transparent)]"></div>
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
                <button @click="view = 'create'" class="bg-blue-600 text-white px-12 py-5 rounded-3xl font-black text-xl hover:scale-105 transition-all shadow-2xl shadow-blue-500/40">製作球友卡</button>
                <button @click="view = 'list'" class="bg-white/5 text-white border border-white/10 px-12 py-5 rounded-3xl font-black text-xl hover:bg-white/10 transition-all backdrop-blur-md">瀏覽球友大廳</button>
            </div>
        </div>
    </div>

    {{-- Featured Players --}}
    <section>
        <div class="flex items-center justify-between mb-12">
            <h2 class="text-3xl font-black italic uppercase tracking-tighter flex items-center gap-4">
                <div class="w-1.5 h-10 bg-blue-600 rounded-full"></div> 推薦戰友
            </h2>
            <button @click="view = 'list'" class="text-blue-600 text-sm font-black uppercase tracking-widest border-b-2 border-blue-600/10 pb-1">顯示更多</button>
        </div>
        <div class="flex overflow-x-auto no-scrollbar snap-x snap-mandatory gap-8 pb-8 -mx-4 px-4 sm:mx-0 sm:px-0 sm:grid sm:grid-cols-2 lg:grid-cols-3 sm:gap-12">
            <player-card v-for="p in players.slice(0, 3)" :key="p.id" :player="p" @click="showDetail(p)" class="min-w-[280px] sm:min-w-0 snap-center" />
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
                <div class="bg-slate-50 p-8 rounded-[32px] text-center space-y-2">
                    <div class="text-4xl font-black italic text-blue-600">100%</div>
                    <div class="text-xs font-black uppercase tracking-widest text-slate-400">免費刊登</div>
                </div>
                <div class="bg-slate-50 p-8 rounded-[32px] text-center space-y-2">
                    <div class="text-4xl font-black italic text-slate-900">24/7</div>
                    <div class="text-xs font-black uppercase tracking-widest text-slate-400">即時媒合</div>
                </div>
                <div class="bg-slate-50 p-8 rounded-[32px] text-center space-y-2">
                    <div class="text-4xl font-black italic text-slate-900">NTRP</div>
                    <div class="text-xs font-black uppercase tracking-widest text-slate-400">精準分級</div>
                </div>
                <div class="bg-slate-50 p-8 rounded-[32px] text-center space-y-2">
                    <div class="text-4xl font-black italic text-blue-600">SAFE</div>
                    <div class="text-xs font-black uppercase tracking-widest text-slate-400">安全社群</div>
                </div>
            </div>
        </div>
    </div>
</div>
