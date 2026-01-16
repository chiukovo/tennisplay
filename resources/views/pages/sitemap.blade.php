{{-- Sitemap Page --}}
<div v-if="view === 'sitemap'" class="animate__animated animate__fadeIn">
    <div class="max-w-4xl mx-auto py-12 px-4">
        <div class="text-center mb-16">
            <h1 class="text-5xl font-black italic uppercase tracking-tighter text-slate-900 mb-4">網站地圖 <span class="text-blue-600">SITEMAP</span></h1>
            <p class="text-slate-500 font-bold uppercase tracking-widest text-sm">快速導覽 LoveTennis 各項功能</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            {{-- Main Sections --}}
            <div class="bg-white p-8 rounded-[40px] shadow-xl border border-slate-100">
                <h3 class="text-xl font-black italic uppercase tracking-tight mb-6 flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-2xl flex items-center justify-center shadow-lg shadow-blue-600/20">
                        <app-icon name="home" class-name="w-5 h-5 text-white"></app-icon>
                    </div>
                    主要頁面
                </h3>
                <ul class="space-y-4">
                    <li>
                        <a href="/" @click.prevent="navigateTo('home')" class="flex items-center gap-3 text-slate-600 hover:text-blue-600 font-bold transition-colors group">
                            <div class="w-1.5 h-1.5 bg-slate-200 rounded-full group-hover:bg-blue-600 transition-colors"></div>
                            首頁 / 最新動態
                        </a>
                    </li>
                    <li>
                        <a href="/list" @click.prevent="navigateTo('list')" class="flex items-center gap-3 text-slate-600 hover:text-blue-600 font-bold transition-colors group">
                            <div class="w-1.5 h-1.5 bg-slate-200 rounded-full group-hover:bg-blue-600 transition-colors"></div>
                            發現球友 / 球友列表
                        </a>
                    </li>
                    <li>
                        <a href="/events" @click.prevent="navigateTo('events')" class="flex items-center gap-3 text-slate-600 hover:text-blue-600 font-bold transition-colors group">
                            <div class="w-1.5 h-1.5 bg-slate-200 rounded-full group-hover:bg-blue-600 transition-colors"></div>
                            開團揪球 / 活動列表
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Member Services --}}
            <div class="bg-white p-8 rounded-[40px] shadow-xl border border-slate-100">
                <h3 class="text-xl font-black italic uppercase tracking-tight mb-6 flex items-center gap-3">
                    <div class="w-10 h-10 bg-slate-900 rounded-2xl flex items-center justify-center shadow-lg shadow-slate-900/20">
                        <app-icon name="user" class-name="w-5 h-5 text-white"></app-icon>
                    </div>
                    會員服務
                </h3>
                <ul class="space-y-4">
                    <li>
                        <a href="/create" @click.prevent="navigateTo('create')" class="flex items-center gap-3 text-slate-600 hover:text-blue-600 font-bold transition-colors group">
                            <div class="w-1.5 h-1.5 bg-slate-200 rounded-full group-hover:bg-blue-600 transition-colors"></div>
                            製作球友卡
                        </a>
                    </li>
                    <li>
                        <a href="/messages" @click.prevent="navigateTo('messages')" class="flex items-center gap-3 text-slate-600 hover:text-blue-600 font-bold transition-colors group">
                            <div class="w-1.5 h-1.5 bg-slate-200 rounded-full group-hover:bg-blue-600 transition-colors"></div>
                            約打訊息 / 站內信
                        </a>
                    </li>
                    <li>
                        <a href="/settings" @click.prevent="navigateTo('settings')" class="flex items-center gap-3 text-slate-600 hover:text-blue-600 font-bold transition-colors group">
                            <div class="w-1.5 h-1.5 bg-slate-200 rounded-full group-hover:bg-blue-600 transition-colors"></div>
                            個人設置
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Legal & Info --}}
            <div class="bg-white p-8 rounded-[40px] shadow-xl border border-slate-100">
                <h3 class="text-xl font-black italic uppercase tracking-tight mb-6 flex items-center gap-3">
                    <div class="w-10 h-10 bg-slate-100 rounded-2xl flex items-center justify-center">
                        <app-icon name="shield-check" class-name="w-5 h-5 text-slate-600"></app-icon>
                    </div>
                    相關資訊
                </h3>
                <ul class="space-y-4">
                    <li>
                        <a href="/privacy" @click.prevent="navigateTo('privacy')" class="flex items-center gap-3 text-slate-600 hover:text-blue-600 font-bold transition-colors group">
                            <div class="w-1.5 h-1.5 bg-slate-200 rounded-full group-hover:bg-blue-600 transition-colors"></div>
                            隱私權政策
                        </a>
                    </li>
                    <li>
                        <a href="/sitemap.xml" target="_blank" class="flex items-center gap-3 text-slate-600 hover:text-blue-600 font-bold transition-colors group">
                            <div class="w-1.5 h-1.5 bg-slate-200 rounded-full group-hover:bg-blue-600 transition-colors"></div>
                            XML 網站地圖 (SEO)
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Support & Contact --}}
            <div class="bg-white p-8 rounded-[40px] shadow-xl border border-slate-100">
                <h3 class="text-xl font-black italic uppercase tracking-tight mb-6 flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-500 rounded-2xl flex items-center justify-center shadow-lg shadow-green-500/20">
                        <app-icon name="message-circle" class-name="w-5 h-5 text-white"></app-icon>
                    </div>
                    支援與聯絡
                </h3>
                <ul class="space-y-4">
                    <li>
                        <a href="https://line.me/R/ti/p/@344epiuj" target="_blank" class="flex items-center gap-3 text-slate-600 hover:text-blue-600 font-bold transition-colors group">
                            <div class="w-1.5 h-1.5 bg-slate-200 rounded-full group-hover:bg-blue-600 transition-colors"></div>
                            官方 LINE 客服
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center gap-3 text-slate-400 font-bold cursor-not-allowed">
                            <div class="w-1.5 h-1.5 bg-slate-100 rounded-full"></div>
                            常見問題 (即將推出)
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        {{-- How it works --}}
        <div class="mt-16 bg-slate-900 rounded-[48px] p-10 text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-blue-600 blur-[100px] opacity-20 -mr-32 -mt-32"></div>
            <div class="relative z-10">
                <h3 class="text-2xl font-black italic uppercase tracking-tight mb-8">如何開始使用？ / How it works</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-8">
                    <div class="space-y-3">
                        <div class="text-3xl font-black text-blue-500">01</div>
                        <div class="font-black text-lg">建立球友卡</div>
                        <p class="text-slate-400 text-sm font-medium leading-relaxed">填寫您的 NTRP 等級與地區，製作專屬數位名片。</p>
                    </div>
                    <div class="space-y-3">
                        <div class="text-3xl font-black text-blue-500">02</div>
                        <div class="font-black text-lg">發現新夥伴</div>
                        <p class="text-slate-400 text-sm font-medium leading-relaxed">在找球友瀏覽球友，或在活動列表加入感興趣的球局。</p>
                    </div>
                    <div class="space-y-3">
                        <div class="text-3xl font-black text-blue-500">03</div>
                        <div class="font-black text-lg">即時約打</div>
                        <p class="text-slate-400 text-sm font-medium leading-relaxed">透過站內信或 LINE 快速聯繫，開啟您的網球社交。</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-12 text-center">
            <button @click="navigateTo('home')" class="px-8 py-4 bg-slate-900 text-white rounded-2xl font-black uppercase tracking-widest hover:bg-blue-600 transition-all shadow-xl">
                返回首頁
            </button>
        </div>
    </div>
</div>
