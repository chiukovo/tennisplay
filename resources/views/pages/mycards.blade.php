{{-- My Cards View --}}
<div v-if="view === 'mycards'" class="max-w-4xl mx-auto space-y-8 pb-20 animate__animated animate__fadeIn">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-4xl sm:text-5xl font-black italic uppercase tracking-tighter">我的球友卡</h2>
            <p class="text-slate-400 font-bold text-sm uppercase tracking-[0.2em] mt-2">My Cards</p>
        </div>
        <a href="/create" @click.prevent="resetForm(); navigateTo('create')" class="flex items-center gap-2 bg-blue-600 text-white px-6 py-3 rounded-2xl font-black uppercase tracking-widest text-xs hover:bg-blue-700 transition-all shadow-xl">
            <app-icon name="plus" class-name="w-5 h-5"></app-icon>
            新增
        </a>
    </div>

    {{-- Login Required --}}
    <div v-if="!isLoggedIn" class="bg-white rounded-[48px] shadow-2xl border border-slate-100 p-16 text-center">
        <div class="bg-slate-100 w-24 h-24 rounded-3xl flex items-center justify-center mx-auto mb-8">
            <app-icon name="user" class-name="w-12 h-12 text-slate-300"></app-icon>
        </div>
        <h3 class="text-2xl font-black italic uppercase tracking-tight mb-4">登入以查看您的球友卡</h3>
        <p class="text-slate-400 font-medium mb-8">登入後即可管理您建立的所有球友卡</p>
        <a href="/auth" @click.prevent="navigateTo('auth')" class="inline-block bg-blue-600 text-white px-10 py-4 rounded-2xl font-black uppercase tracking-widest hover:bg-blue-700 transition-all shadow-xl">
            立即登入
        </a>
    </div>

    {{-- Cards List --}}
    <div v-else-if="myCards.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-8">
        <div v-for="card in myCards" :key="card.id" class="relative group">
            <player-card :player="card" @click="showDetail(card)"></player-card>
            {{-- Action Overlay --}}
            <div class="absolute inset-0 bg-slate-950/60 opacity-0 group-hover:opacity-100 transition-opacity rounded-[32px] flex items-center justify-center gap-4">
                <button @click.stop="editCard(card)" class="bg-white text-slate-900 px-6 py-3 rounded-2xl font-black uppercase tracking-widest text-xs hover:bg-blue-100 transition-colors shadow-xl">
                    <app-icon name="edit" class-name="w-5 h-5 inline-block mr-2"></app-icon>
                    編輯
                </button>
                <button @click.stop="deleteCard(card.id)" class="bg-red-500 text-white px-6 py-3 rounded-2xl font-black uppercase tracking-widest text-xs hover:bg-red-600 transition-colors shadow-xl">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    刪除
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
