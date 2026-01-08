{{-- My Cards View --}}
<div v-if="view === 'mycards'" class="max-w-6xl mx-auto pb-24 px-4 animate__animated animate__fadeIn">
    {{-- Hero Section --}}
    <div class="relative bg-slate-900 rounded-[48px] overflow-hidden mb-12 shadow-2xl p-8 sm:p-16">
        <div class="absolute inset-0 opacity-20 pointer-events-none">
            <div class="absolute top-0 right-10 w-64 h-64 bg-blue-600 rounded-full blur-[100px]"></div>
            <div class="absolute bottom-0 left-10 w-64 h-64 bg-indigo-600 rounded-full blur-[100px]"></div>
            <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, rgba(255,255,255,0.05) 1px, transparent 0); background-size: 24px 24px;"></div>
        </div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-end justify-between gap-8">
            <div class="max-w-xl">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600/20 text-blue-400 rounded-full border border-blue-600/20 mb-6 backdrop-blur-md">
                    <app-icon name="user" class-name="w-4 h-4"></app-icon>
                    <span class="text-[10px] font-black uppercase tracking-widest leading-none">Personal Profile</span>
                </div>
                <h2 class="text-4xl sm:text-6xl font-black italic text-white leading-tight uppercase tracking-tighter mb-4">
                    我的球友卡 <span class="bg-gradient-to-r from-blue-400 to-indigo-400 bg-clip-text text-transparent">COLLECTION</span>
                </h2>
                <p class="text-slate-400 font-bold text-sm sm:text-base leading-relaxed">
                    在這裡管理您的所有球友卡，隨時調整您的等級、持拍手與反手類型，展現您最專業的選手形象。
                </p>
            </div>
            <div class="flex items-center gap-4">
                <a href="/create" @click.prevent="resetForm(); navigateTo('create')" class="group flex items-center gap-3 bg-white text-slate-900 px-8 py-5 rounded-[24px] font-black uppercase tracking-widest text-sm hover:bg-blue-600 hover:text-white transition-all shadow-2xl">
                    <app-icon name="plus" class-name="w-5 h-5 group-hover:rotate-90 transition-transform duration-500"></app-icon>
                    新增球友卡
                </a>
            </div>
        </div>
    </div>

    {{-- Login Required --}}
    <div v-if="!isLoggedIn" class="bg-white rounded-[40px] shadow-2xl border border-slate-100 p-12 text-center">
        <div class="bg-slate-100 w-20 h-20 rounded-3xl flex items-center justify-center mx-auto mb-6">
            <app-icon name="user" class-name="w-10 h-10 text-slate-300"></app-icon>
        </div>
        <h3 class="text-xl font-black italic uppercase tracking-tight mb-2">登入以查看您的球友卡</h3>
        <p class="text-slate-400 font-bold text-sm mb-6">登入後即可管理您建立的所有球友卡</p>
        <a href="/auth" @click.prevent="navigateTo('auth')" class="inline-block bg-blue-600 text-white px-8 py-3.5 rounded-2xl font-black uppercase tracking-widest text-xs hover:bg-blue-700 transition-all shadow-xl">
            立即登入
        </a>
    </div>

    {{-- Cards List --}}
    <div v-else-if="myCards.length > 0">
        <div class="flex items-center justify-between mb-8 px-4">
            <div class="flex items-center gap-3">
                <div class="w-1.5 h-6 bg-blue-600 rounded-full"></div>
                <span class="font-black italic uppercase tracking-widest text-lg">您的選手清單 (@{{myCards.length}})</span>
            </div>
        </div>
        
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-8">
            <div v-for="card in myCards" :key="card.id" class="relative group max-w-[280px] mx-auto w-full">
                <div @click="showDetail(card)" class="cursor-pointer hover:-translate-y-2 transition-transform duration-500">
                    <player-card :player="card" size="sm"></player-card>
                </div>
                {{-- Action Overlay --}}
                <div class="absolute -bottom-2 sm:bottom-4 left-4 right-4 flex flex-col gap-2 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 pointer-events-auto sm:pointer-events-none sm:group-hover:pointer-events-auto transition-all duration-300 z-30">
                    <button @click.stop="editCard(card)" class="w-full py-3 bg-white/95 backdrop-blur-xl text-slate-900 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-blue-600 hover:text-white transition-all flex items-center justify-center gap-2 shadow-2xl border border-slate-200/50">
                        <app-icon name="edit-3" class-name="w-4 h-4"></app-icon> 編輯卡片
                    </button>
                    <button @click.stop="deleteCard(card.id)" class="w-full py-3 bg-red-500 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-red-600 transition-all flex items-center justify-center gap-2 shadow-2xl">
                        <app-icon name="trash" class-name="w-4 h-4"></app-icon> 刪除卡片
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Empty State --}}
    <div v-else class="bg-white rounded-[48px] shadow-2xl border border-slate-100 p-16 text-center">
        <div class="bg-blue-50 w-24 h-24 rounded-3xl flex items-center justify-center mx-auto mb-8">
            <app-icon name="plus" class-name="w-12 h-12 text-blue-300"></app-icon>
        </div>
        <h3 class="text-2xl font-black italic uppercase tracking-tight mb-4">還沒有球友卡</h3>
        <p class="text-slate-400 font-medium mb-8">建立您的第一張專業球友卡，開始在社群大廳曝光！</p>
        <a href="/create" @click.prevent="navigateTo('create')" class="inline-flex items-center gap-3 bg-blue-600 text-white px-10 py-4 rounded-2xl font-black uppercase tracking-widest hover:bg-blue-700 transition-all shadow-xl">
            <app-icon name="plus" class-name="w-5 h-5"></app-icon>
            建立球友卡
        </a>
    </div>
</div>
