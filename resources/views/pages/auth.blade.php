{{-- Auth View --}}
<div v-if="view === 'auth'" class="flex items-center justify-center py-10">
    <div class="w-full max-w-md bg-white rounded-[40px] shadow-2xl p-10 border border-slate-100">
        <div class="text-center mb-10">
            <div class="inline-block bg-blue-50 p-4 rounded-3xl mb-4">
                <app-icon name="user" class-name="text-blue-600 w-10 h-10"></app-icon>
            </div>
            <h2 class="text-3xl font-black italic uppercase tracking-tighter leading-tight">
                @{{ isLoginMode ? '歡迎回來' : '建立 AceMate 帳號' }}
            </h2>
            <p class="text-slate-500 text-base font-medium mt-2">啟動您的專業網球社交生活</p>
        </div>

        {{-- Error Message --}}
        <div v-if="authError" class="mb-6 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-2xl text-sm font-bold">
            @{{ authError }}
        </div>

        <form class="space-y-6" @submit.prevent="isLoginMode ? login() : register()">
            <div v-if="!isLoginMode">
                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">真實姓名</label>
                <input type="text" v-model="authForm.name" required class="w-full px-6 py-4 bg-slate-50 border-2 border-transparent rounded-2xl focus:border-blue-500 outline-none font-bold text-lg" placeholder="例如: Roger Chen">
            </div>
            <div>
                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">電子郵件</label>
                <input type="email" v-model="authForm.email" required class="w-full px-6 py-4 bg-slate-50 border-2 border-transparent rounded-2xl focus:border-blue-500 outline-none font-bold text-lg" placeholder="your@email.com">
            </div>
            <div>
                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">密碼</label>
                <input type="password" v-model="authForm.password" required minlength="6" class="w-full px-6 py-4 bg-slate-50 border-2 border-transparent rounded-2xl focus:border-blue-500 outline-none font-bold text-lg" placeholder="••••••••">
            </div>
            <div v-if="!isLoginMode">
                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">確認密碼</label>
                <input type="password" v-model="authForm.password_confirmation" required minlength="6" class="w-full px-6 py-4 bg-slate-50 border-2 border-transparent rounded-2xl focus:border-blue-500 outline-none font-bold text-lg" placeholder="••••••••">
            </div>
            <button type="submit" :disabled="isLoading" class="w-full bg-slate-950 text-white py-5 rounded-2xl font-black uppercase tracking-widest hover:bg-blue-600 transition-all shadow-xl text-lg disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-3">
                <svg v-if="isLoading" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                @{{ isLoading ? '處理中...' : (isLoginMode ? '進入系統' : '完成註冊') }}
            </button>
        </form>
        <div class="mt-8 text-center">
            <button @click="isLoginMode = !isLoginMode; authError = ''" class="text-base font-bold text-slate-400 hover:text-blue-600">
                @{{ isLoginMode ? '還沒有帳號？立即註冊' : '已有帳號？直接登入' }}
            </button>
        </div>
    </div>
</div>
