{{-- Settings Page --}}
<div v-if="view === 'settings'" class="max-w-2xl mx-auto space-y-8 animate__animated animate__fadeIn">
    {{-- Header --}}
    <div class="flex items-center gap-4">
        <button @click="navigateTo('home')" class="w-10 h-10 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-slate-400 hover:text-blue-600 hover:border-blue-100 transition-all">
            <app-icon name="arrow-left" class-name="w-5 h-5"></app-icon>
        </button>
        <div>
            <h2 class="text-3xl font-black italic uppercase tracking-tighter leading-tight">個人設置</h2>
            <p class="text-slate-400 font-bold text-xs uppercase tracking-[0.2em] mt-1">Manage Your Preferences</p>
        </div>
    </div>

    {{-- Settings Card --}}
    <div class="bg-white rounded-[32px] border border-slate-200 shadow-xl overflow-hidden">
        <div class="p-8 space-y-8">
            {{-- Default Region Setting --}}
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center">
                        <app-icon name="map-pin" class-name="w-5 h-5"></app-icon>
                    </div>
                    <div>
                        <h4 class="font-black text-slate-900 leading-none">預設地區</h4>
                        <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mt-1">Default Browsing Region</p>
                    </div>
                </div>
                
                <p class="text-sm text-slate-500 font-medium">
                    設定後，當您進入「發現球友」或「開團揪球」時，系統會自動幫您套用此地區的篩選。
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-blue-500 transition-colors">
                            <app-icon name="map-pin" class-name="w-4 h-4"></app-icon>
                        </div>
                        <select v-model="settingsForm.default_region" 
                            class="w-full bg-slate-50 border-2 border-slate-100 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 rounded-2xl py-3.5 pl-10 pr-10 text-sm font-black text-slate-700 appearance-none transition-all">
                            <option value="全部">不限地區 (全部)</option>
                            <option v-for="r in regions" :key="r" :value="r">@{{ r }}</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-400">
                            <app-icon name="chevron-down" class-name="w-4 h-4"></app-icon>
                        </div>
                    </div>
                </div>
            </div>


            
            {{-- Notification Settings --}}
            <div class="border-t border-slate-100 pt-8 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-green-50 text-green-600 rounded-2xl flex items-center justify-center">
                            <app-icon name="bell" class-name="w-5 h-5"></app-icon>
                        </div>
                        <div>
                            <h4 class="font-black text-slate-900 leading-none">訊息即時通知</h4>
                            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mt-1">LINE Message Notifications</p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" v-model="settingsForm.notify_line" class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>
                <p class="text-sm text-slate-500 font-medium">
                    開啟後，當有人發送邀約信給您時，系統會立即透過 Line 官方帳號通知。
                </p>
                {{-- Add Friend Alert --}}
                <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 flex gap-4">
                    <img src="/img/lineqrcode.png" alt="QR" class="w-16 h-16 rounded-lg border border-white shadow-sm shrink-0">
                    <div class="space-y-1">
                        <p class="text-xs text-blue-800 font-black">
                            <app-icon name="alert-circle" class-name="w-3 h-3 inline mr-1"></app-icon>
                            必須加入官方 LINE 好友才能接收通知
                        </p>
                        <p class="text-[10px] text-blue-600 font-bold uppercase tracking-wider">
                            ID: @344epiuj
                        </p>
                        <a href="https://line.me/R/ti/p/@344epiuj" target="_blank" class="text-[10px] text-white bg-blue-600 px-3 py-1 rounded-lg font-black inline-block mt-1">立即加入</a>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-100 pt-8">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex-1">
                        <h4 class="font-black text-slate-900 leading-none">帳號資訊</h4>
                        <p class="text-xs text-slate-400 font-bold mt-1">@{{ currentUser?.name }} (@{{ currentUser?.id }})</p>
                    </div>
                    <button @click="logout" class="text-red-500 font-black text-xs uppercase tracking-widest hover:bg-red-50 px-4 py-2 rounded-xl transition-all">
                        登出帳號
                    </button>
                </div>
            </div>
        </div>

        {{-- Footer Actions --}}
        <div class="bg-slate-50 p-6 flex items-center justify-end border-t border-slate-200">
            <button @click="saveSettings" 
                :disabled="isSavingSettings"
                class="bg-blue-600 text-white px-8 py-4 rounded-2xl font-black uppercase tracking-widest text-xs shadow-xl shadow-blue-500/20 hover:bg-blue-700 hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-50 disabled:pointer-events-none transition-all flex items-center gap-2">
                <svg v-if="isSavingSettings" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                @{{ isSavingSettings ? '儲存中...' : '儲存設置' }}
            </button>
        </div>
    </div>

    {{-- Tips Card --}}
    <div class="bg-blue-600 rounded-[32px] p-8 text-white relative overflow-hidden shadow-2xl">
        <div class="absolute right-0 top-0 -translate-y-1/4 translate-x-1/4 opacity-10 pointer-events-none">
            <app-icon name="zap" class-name="w-64 h-64"></app-icon>
        </div>
        <div class="relative z-10 space-y-4">
            <div class="flex items-center gap-2">
                <app-icon name="help" class-name="w-5 h-5"></app-icon>
                <h4 class="font-black uppercase tracking-widest text-sm italic">你知道嗎？</h4>
            </div>
            <p class="font-bold text-blue-100 text-sm leading-relaxed max-w-md">
                設置預設地區可以讓您更快找到附近的場次與球友。未來我們還會加入更多自動媒合的設置功能，敬請期待！
            </p>
        </div>
    </div>
</div>
