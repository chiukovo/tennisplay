{{-- List View --}}
<div v-if="view === 'list'" class="space-y-16 pb-24">
    <div class="flex flex-col md:flex-row justify-between items-end gap-10">
        <div>
            <h2 class="text-5xl font-black italic uppercase tracking-tighter leading-tight">球友大廳</h2>
            <p class="text-slate-400 font-bold text-base uppercase tracking-[0.2em] mt-2">Find your matching AceMate</p>
        </div>
        <div class="relative w-full md:w-80">
            <app-icon name="search" class-name="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 w-6 h-6"></app-icon>
            <input type="text" placeholder="搜尋姓名或程度..." class="w-full pl-14 pr-8 py-5 bg-white border border-slate-200 rounded-[28px] outline-none focus:ring-4 focus:ring-blue-500/10 font-bold text-lg">
        </div>
    </div>

    <div v-for="region in regions" :key="region">
        <div v-if="getPlayersByRegion(region).length > 0" class="mb-16">
            <div class="flex items-center gap-8 mb-10">
                <span class="bg-slate-900 text-white text-xs font-black px-6 py-2.5 rounded-2xl uppercase tracking-[0.3em]">@{{region}}</span>
                <div class="h-px flex-1 bg-slate-200"></div>
            </div>
            <div class="flex overflow-x-auto no-scrollbar snap-x snap-mandatory gap-8 pb-8 -mx-4 px-4 sm:mx-0 sm:px-0 sm:grid sm:grid-cols-2 lg:grid-cols-4 sm:gap-12">
                <div v-for="player in getPlayersByRegion(region)" :key="player.id" class="flex flex-col min-w-[240px] sm:min-w-0 snap-center">
                    <player-card :player="player" size="sm" @click="showDetail(player)" />
                    <button @click="openMatchModal(player)" class="mt-8 py-5 bg-white border-2 border-slate-950 rounded-3xl font-black text-xs uppercase tracking-[0.2em] hover:bg-slate-950 hover:text-white transition-all shadow-lg flex items-center justify-center gap-3">
                        <app-icon name="message-circle" class-name="w-5 h-5"></app-icon> 發送約打信
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
