{{-- My Cards View --}}
<div v-if="view === 'mycards'" class="max-w-4xl mx-auto space-y-8 pb-20 animate__animated animate__fadeIn">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-3xl sm:text-5xl font-black italic uppercase tracking-tighter leading-tight">我的球友卡</h2>
            <p class="text-slate-400 font-bold text-xs sm:text-base uppercase tracking-[0.2em] mt-1">My Cards</p>
        </div>
        <a href="/create" @click.prevent="resetForm(); navigateTo('create')" class="flex items-center gap-2 bg-blue-600 text-white px-5 py-2.5 rounded-2xl font-black uppercase tracking-widest text-[10px] hover:bg-blue-700 transition-all shadow-xl">
            <app-icon name="plus" class-name="w-4 h-4"></app-icon>
            新增
        </a>
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
    <div v-else-if="myCards.length > 0" class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div v-for="card in myCards" :key="card.id" class="relative group">
            <div @click="showDetail(card)" class="cursor-pointer">
                <player-card :player="card" size="sm"></player-card>
            </div>
            {{-- Action Overlay --}}
            <div class="absolute bottom-4 left-4 right-4 flex flex-col gap-2 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 pointer-events-auto sm:pointer-events-none sm:group-hover:pointer-events-auto transition-all duration-300 z-30">
                <button @click.stop="editCard(card)" class="w-full py-2.5 bg-white/90 backdrop-blur-md text-slate-700 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-white transition-all flex items-center justify-center gap-2 shadow-lg">
                    <app-icon name="edit-3" class-name="w-3.5 h-3.5"></app-icon> 編輯
                </button>
                <button @click.stop="deleteCard(card.id)" class="w-full py-2.5 bg-red-500/90 backdrop-blur-md text-white rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-red-600 transition-all flex items-center justify-center gap-2 shadow-lg">
                    <app-icon name="trash" class-name="w-3.5 h-3.5"></app-icon> 刪除
                </button>
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
