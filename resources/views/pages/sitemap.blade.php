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
            <div class="bg-white p-8 rounded-[40px] shadow-xl border border-slate-100 md:col-span-2">
                <h3 class="text-xl font-black italic uppercase tracking-tight mb-6 flex items-center gap-3">
                    <div class="w-10 h-10 bg-slate-100 rounded-2xl flex items-center justify-center">
                        <app-icon name="shield-check" class-name="w-5 h-5 text-slate-600"></app-icon>
                    </div>
                    相關資訊
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <a href="/privacy" @click.prevent="navigateTo('privacy')" class="flex items-center gap-3 text-slate-600 hover:text-blue-600 font-bold transition-colors group">
                        <div class="w-1.5 h-1.5 bg-slate-200 rounded-full group-hover:bg-blue-600 transition-colors"></div>
                        隱私權政策
                    </a>
                    <a href="/sitemap.xml" target="_blank" class="flex items-center gap-3 text-slate-600 hover:text-blue-600 font-bold transition-colors group">
                        <div class="w-1.5 h-1.5 bg-slate-200 rounded-full group-hover:bg-blue-600 transition-colors"></div>
                        XML 網站地圖 (SEO 用)
                    </a>
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
