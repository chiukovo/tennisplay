{{-- Privacy Policy Page --}}
<div v-if="view === 'privacy'" class="py-10 sm:py-20">
    <div class="max-w-3xl mx-auto bg-white rounded-[48px] shadow-[0_20px_80px_rgba(0,0,0,0.05)] overflow-hidden border border-slate-100">
        {{-- Header --}}
        <div class="px-10 py-12 bg-slate-50/50 border-b border-slate-100 text-center relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 blur-[60px] rounded-full"></div>
            <div class="relative z-10">
                <h1 class="text-4xl font-black italic uppercase tracking-tighter leading-tight text-slate-900 mb-4">
                    隱私權政策
                </h1>
                <p class="text-slate-400 text-sm font-bold uppercase tracking-widest">Privacy Policy & Terms of Service</p>
            </div>
        </div>

        {{-- Content --}}
        <div class="p-10 sm:p-16 space-y-12">
            <section class="space-y-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center shrink-0">
                        <app-icon name="shield" class-name="w-6 h-6 text-blue-600"></app-icon>
                    </div>
                    <h2 class="text-2xl font-black text-slate-900">資料收集與使用</h2>
                </div>
                <div class="pl-16 space-y-4">
                    <p class="text-slate-500 leading-relaxed font-medium">
                        為了提供專業的網球約打服務，我們會透過 LINE 登入收集您的公開個人檔案資訊（包含 LINE 顯示名稱、頭像圖片及唯一識別碼）。
                    </p>
                    <ul class="space-y-2 text-slate-500 text-sm font-bold">
                        <li class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 bg-blue-400 rounded-full"></div>
                            建立與維護您的數位球友卡
                        </li>
                        <li class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 bg-blue-400 rounded-full"></div>
                            處理球友間的約打邀請與訊息通知
                        </li>
                        <li class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 bg-blue-400 rounded-full"></div>
                            優化平台媒合演算法與使用者體驗
                        </li>
                    </ul>
                </div>
            </section>

            <section class="space-y-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-green-100 rounded-2xl flex items-center justify-center shrink-0">
                        <app-icon name="lock" class-name="w-6 h-6 text-green-600"></app-icon>
                    </div>
                    <h2 class="text-2xl font-black text-slate-900">個人資料保護</h2>
                </div>
                <div class="pl-16 space-y-4">
                    <p class="text-slate-500 leading-relaxed font-medium">
                        我們承諾不會在未經您許可的情況下，將您的個人資料提供給第三方，或用於非本平台服務之用途。
                    </p>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        您的球友卡資訊（如 NTRP 等級、地區、打球偏好）將公開顯示於平台大廳，以便其他球友與您聯繫。我們建議您在個人簡介中避免填寫過於私人的聯絡資訊。
                    </p>
                </div>
            </section>

            <section class="space-y-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-amber-100 rounded-2xl flex items-center justify-center shrink-0">
                        <app-icon name="user-check" class-name="w-6 h-6 text-amber-600"></app-icon>
                    </div>
                    <h2 class="text-2xl font-black text-slate-900">使用者權利</h2>
                </div>
                <div class="pl-16 space-y-4">
                    <p class="text-slate-500 leading-relaxed font-medium">
                        您可以隨時透過「個人設定」更新您的球友卡資訊。
                    </p>
                    <p class="text-slate-500 leading-relaxed font-medium">
                        若您希望刪除帳號及所有相關資料，請聯繫系統管理員（Email: <a href="mailto:q8156697@gmail.com" class="text-blue-600 hover:underline">q8156697@gmail.com</a>），我們將在核對身分後，於 7 個工作天內移除您的所有個人識別資訊。
                    </p>
                </div>
            </section>

            <section class="space-y-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-purple-100 rounded-2xl flex items-center justify-center shrink-0">
                        <app-icon name="alert-circle" class-name="w-6 h-6 text-purple-600"></app-icon>
                    </div>
                    <h2 class="text-2xl font-black text-slate-900">免責聲明</h2>
                </div>
                <div class="pl-16">
                    <div class="bg-slate-50 p-6 rounded-3xl border border-slate-100">
                        <p class="text-slate-500 text-sm leading-relaxed font-medium italic">
                            本平台僅提供約打媒合資訊，實際打球過程中的人身安全、場地糾紛或費用爭議，請由雙方自行協商解決。LoveTennis 團隊不對任何球友間的線下行為負法律責任。
                        </p>
                    </div>
                </div>
            </section>

            <div class="pt-10 border-t border-slate-100 flex justify-center">
                <button @click="navigateTo('home')" class="px-10 py-4 bg-slate-900 text-white rounded-2xl font-black uppercase tracking-widest text-sm hover:bg-blue-600 transition-all shadow-xl active:scale-95">
                    返回首頁
                </button>
            </div>
        </div>
    </div>
</div>
