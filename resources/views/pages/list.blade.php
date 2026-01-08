{{-- List View --}}
<div v-if="view === 'list'" class="space-y-6 pb-24">
    {{-- Header --}}
    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl sm:text-5xl font-black italic uppercase tracking-tighter leading-tight">球友列表</h2>
                <p class="text-slate-400 font-bold text-xs sm:text-base uppercase tracking-[0.2em] mt-1">Find your matching LoveTennis</p>
            </div>
            <div class="text-right">
                <div class="text-2xl font-black text-blue-600">@{{ filteredPlayers.length }}</div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">位球友</div>
            </div>
        </div>
        
        {{-- Search Bar --}}
        <div class="relative">
            <app-icon name="search" class-name="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 w-5 h-5"></app-icon>
            <input type="text" v-model="searchQuery" placeholder="搜尋姓名、程度或地區..." class="w-full pl-12 pr-4 py-4 bg-white border border-slate-200 rounded-2xl outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 font-bold text-base transition-all">
        </div>
    </div>

    {{-- Region Filter Tabs (Scrollable on Mobile) --}}
    <div class="flex overflow-x-auto no-scrollbar gap-2 pb-2 -mx-4 px-4">
        <button type="button" @click="selectedRegion = '全部'" 
            :class="['px-4 py-2.5 rounded-full font-black text-xs uppercase tracking-widest whitespace-nowrap transition-all border-2', 
            selectedRegion === '全部' ? 'bg-slate-900 text-white border-slate-900 shadow-lg' : 'bg-white text-slate-400 border-slate-200 hover:border-slate-300']">
            全部 <span class="ml-1 opacity-60">(@{{ players.length }})</span>
        </button>
        <button type="button" v-for="r in activeRegions" :key="r" @click="selectedRegion = r" 
            :class="['px-4 py-2.5 rounded-full font-black text-xs uppercase tracking-widest whitespace-nowrap transition-all border-2', 
            selectedRegion === r ? 'bg-blue-600 text-white border-blue-600 shadow-lg' : 'bg-white text-slate-400 border-slate-200 hover:border-slate-300']">
            @{{ r }} <span class="ml-1 opacity-60">(@{{ getPlayersByRegion(r).length }})</span>
        </button>
    </div>

    {{-- Results Info --}}
    <div v-if="searchQuery || selectedRegion !== '全部'" class="flex items-center gap-3 text-sm">
        <span class="text-slate-400">搜尋結果:</span>
        <span class="font-black text-slate-900">@{{ filteredPlayers.length }} 位球友</span>
        <button type="button" v-if="searchQuery || selectedRegion !== '全部'" @click="searchQuery = ''; selectedRegion = '全部'" class="text-blue-600 text-xs font-bold underline">清除篩選</button>
    </div>

    {{-- Skeleton Loading --}}
    <div v-if="isPlayersLoading" class="grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-6">
        <div v-for="i in 12" :key="i" class="relative max-w-60 mx-auto w-full">
            <div class="aspect-[2.5/3.8] rounded-2xl skeleton-shimmer"></div>
        </div>
    </div>

    {{-- Player Cards Grid (Using PlayerCard Component) --}}
    <div v-else-if="paginatedPlayers.length > 0" class="grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-6">
        <template v-for="player in paginatedPlayers" :key="player?.id || Math.random()">
            <div v-if="player && player.id" class="relative group max-w-60 mx-auto w-full">
                {{-- Player Card Component with proper positioning data --}}
                <div @click="showDetail(player)" class="cursor-pointer">
                    <player-card 
                        :player="player" 
                        size="sm">
                    </player-card>
                </div>
            {{-- Action Buttons Overlay --}}
            <div class="absolute bottom-4 left-4 right-4 flex gap-2 opacity-0 group-hover:opacity-100 pointer-events-none group-hover:pointer-events-auto transition-all duration-300 z-30">
                <button type="button" @click.stop="showDetail(player)" class="flex-1 py-3 bg-white/90 backdrop-blur-md text-slate-700 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-white transition-all flex items-center justify-center gap-2 shadow-lg">
                    <app-icon name="user" class-name="w-4 h-4"></app-icon> 詳細
                </button>
                <button type="button" @click.stop="openMatchModal(player)" class="flex-1 py-3 bg-blue-600/90 backdrop-blur-md text-white rounded-xl font-black text-xs uppercase tracking-widest hover:bg-blue-600 transition-all flex items-center justify-center gap-2 shadow-lg">
                    <app-icon name="message-circle" class-name="w-4 h-4"></app-icon> 約打
                </button>
            </div>
        </div>
        </template>
    </div>

    {{-- Empty State --}}
    <div v-else class="text-center py-20">
        <div class="bg-slate-100 w-20 h-20 rounded-3xl flex items-center justify-center mx-auto mb-6">
            <app-icon name="search" class-name="w-10 h-10 text-slate-300"></app-icon>
        </div>
        <h3 class="text-xl font-black uppercase italic tracking-tight mb-2">找不到符合的球友</h3>
        <p class="text-slate-400 font-bold">請嘗試其他搜尋條件或地區</p>
    </div>

    {{-- Pagination --}}
    <div v-if="totalPages > 1" class="flex items-center justify-center gap-2 pt-8">
        {{-- Previous Button --}}
        <button type="button" @click="currentPage = Math.max(1, currentPage - 1)" :disabled="currentPage === 1"
            :class="['w-10 h-10 rounded-xl font-black text-sm transition-all', currentPage === 1 ? 'bg-slate-100 text-slate-300 cursor-not-allowed' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50']">
            ←
        </button>
        
        {{-- Page Numbers --}}
        <div v-for="(page, idx) in displayPages" :key="page.id || 'p-' + page" class="inline-flex">
            <span v-if="typeof page === 'object' && page.type === 'dot'" class="w-10 h-10 flex items-center justify-center text-slate-400">...</span>
            <button v-else type="button" @click="currentPage = page"
                :class="['w-10 h-10 rounded-xl font-black text-sm transition-all', currentPage === page ? 'bg-blue-600 text-white shadow-lg' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50']">
                @{{ page }}
            </button>
        </div>
        
        {{-- Next Button --}}
        <button type="button" @click="currentPage = Math.min(totalPages, currentPage + 1)" :disabled="currentPage === totalPages"
            :class="['w-10 h-10 rounded-xl font-black text-sm transition-all', currentPage === totalPages ? 'bg-slate-100 text-slate-300 cursor-not-allowed' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50']">
            →
        </button>
    </div>
</div>
