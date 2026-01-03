{{-- Auth View --}}
<div v-if="view === 'auth'" class="flex items-center justify-center py-10 animate__animated animate__fadeIn">
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
        <form class="space-y-6" @submit.prevent="login">
            <div v-if="!isLoginMode">
                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">真實姓名</label>
                <input type="text" required class="w-full px-6 py-4 bg-slate-50 border-2 border-transparent rounded-2xl focus:border-blue-500 outline-none font-bold text-lg" placeholder="例如: Roger Chen">
            </div>
            <div>
                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">電子郵件</label>
                <input type="email" required class="w-full px-6 py-4 bg-slate-50 border-2 border-transparent rounded-2xl focus:border-blue-500 outline-none font-bold text-lg" placeholder="your@email.com">
            </div>
            <div>
                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">密碼</label>
                <input type="password" required class="w-full px-6 py-4 bg-slate-50 border-2 border-transparent rounded-2xl focus:border-blue-500 outline-none font-bold text-lg" placeholder="••••••••">
            </div>
            <button type="submit" class="w-full bg-slate-950 text-white py-5 rounded-2xl font-black uppercase tracking-widest hover:bg-blue-600 transition-all shadow-xl text-lg">
                @{{ isLoginMode ? '進入系統' : '完成註冊' }}
            </button>
        </form>
        <div class="mt-8 text-center">
            <button @click="isLoginMode = !isLoginMode" class="text-base font-bold text-slate-400 hover:text-blue-600">
                @{{ isLoginMode ? '還沒有帳號？立即註冊' : '已有帳號？直接登入' }}
            </button>
        </div>
    </div>
</div>
