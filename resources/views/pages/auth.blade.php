{{-- Auth View --}}
<div v-if="view === 'auth'" class="flex items-center justify-center py-10 sm:py-20">
    <div class="w-full max-w-md bg-white rounded-[48px] shadow-[0_20px_80px_rgba(0,0,0,0.1)] p-10 sm:p-14 border border-slate-100 relative overflow-hidden">
        {{-- Decorative Background --}}
        <div class="absolute top-0 right-0 w-32 h-32 bg-green-500/5 blur-[60px] rounded-full"></div>
        <div class="absolute bottom-0 left-0 w-32 h-32 bg-blue-500/5 blur-[60px] rounded-full"></div>

        {{-- Loading Overlay --}}
        <div v-if="isAuthLoading" class="absolute inset-0 z-50 bg-white/95 backdrop-blur-sm flex flex-col items-center justify-center animate__animated animate__fadeIn">
            <div class="relative w-16 h-16 mb-6">
                <div class="absolute inset-0 border-4 border-slate-100 rounded-full"></div>
                <div class="absolute inset-0 border-4 border-blue-600 rounded-full border-t-transparent animate-spin"></div>
            </div>
            <h3 class="text-xl font-black italic uppercase tracking-tighter text-slate-900 mb-2">正在登入中</h3>
            <p class="text-slate-400 text-xs font-bold uppercase tracking-widest animate-pulse">Verifying credentials...</p>
        </div>

        <div class="relative z-10">
            <div class="text-center mb-12">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-[28px] mb-6 shadow-xl border border-slate-100 overflow-hidden">
                    <img src="/img/logo.png" alt="LoveTennis Logo" class="w-12 h-12 object-contain">
                </div>
                <h2 class="text-4xl font-black italic uppercase tracking-tighter leading-tight text-slate-900">
                    開始約打
                </h2>
                <p class="text-slate-400 text-sm font-bold mt-3 uppercase tracking-widest">Join the LoveTennis community</p>
            </div>

            {{-- Error Message --}}
            <div v-if="authError" class="mb-8 bg-red-50 border border-red-100 text-red-600 px-6 py-4 rounded-2xl text-xs font-bold flex items-center gap-3">
                <app-icon name="x" class-name="w-4 h-4"></app-icon>
                @{{ authError }}
            </div>

            <div class="space-y-6">
                <p class="text-center text-slate-500 text-sm font-medium leading-relaxed px-4">
                    為了提供更即時的約打通知與安全的社群環境，我們採用 LINE 快速登入。
                </p>

                <button type="button" @click="loginWithLine" class="w-full bg-[#06C755] text-white py-5 rounded-2xl font-black uppercase tracking-widest hover:bg-[#05b34c] transition-all shadow-xl shadow-green-500/20 flex items-center justify-center gap-4 text-lg group">
                    <app-icon name="line" fill="currentColor" stroke="none" class-name="w-7 h-7"></app-icon>
                    使用 LINE 快速登入
                </button>

                <div class="flex items-center gap-4 py-4">
                    <div class="h-px flex-1 bg-slate-100"></div>
                    <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest">Secure & Fast</span>
                    <div class="h-px flex-1 bg-slate-100"></div>
                </div>

                <div class="bg-blue-50/50 p-6 rounded-3xl border border-blue-100/50">
                    <div class="flex gap-4">
                        <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shrink-0 shadow-sm">
                            <app-icon name="shield-check" class-name="w-5 h-5 text-blue-600"></app-icon>
                        </div>
                        <div class="space-y-1">
                            <h4 class="text-xs font-black text-slate-900 uppercase tracking-wider">隱私保護</h4>
                            <p class="text-[10px] font-bold text-slate-400 leading-relaxed">
                                我們僅會取得您的公開資訊與唯一識別碼，不會在未經許可下發布任何動態。
                            </p>
                        </div>
                    </div>
                </div>

                <p class="text-center text-[10px] text-slate-400 font-medium mt-4">
                    登入即代表您同意我們的 <a href="/privacy" @click.prevent="navigateTo('privacy')" class="text-blue-500 hover:underline">隱私權政策</a>
                </p>
            </div>

            <div class="mt-12 text-center">
                <p class="text-[10px] font-bold text-slate-300 uppercase tracking-[0.2em]">
                    Powered by LoveTennis Engine
                </p>
            </div>
        </div>
    </div>
</div>
