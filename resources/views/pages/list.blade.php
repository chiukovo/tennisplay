{{-- List View --}}
<div v-if="view === 'list'" class="space-y-6 pb-24 animate__animated animate__fadeIn">
    {{-- Header --}}
    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl sm:text-5xl font-black italic uppercase tracking-tighter leading-tight">球友大廳</h2>
                <p class="text-slate-400 font-bold text-xs sm:text-base uppercase tracking-[0.2em] mt-1">Find your matching AceMate</p>
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
        <button @click="selectedRegion = '全部'" 
            :class="['px-4 py-2.5 rounded-full font-black text-xs uppercase tracking-widest whitespace-nowrap transition-all border-2', 
            selectedRegion === '全部' ? 'bg-slate-900 text-white border-slate-900 shadow-lg' : 'bg-white text-slate-400 border-slate-200 hover:border-slate-300']">
            全部 <span class="ml-1 opacity-60">(@{{ players.length }})</span>
        </button>
        <button v-for="r in activeRegions" :key="r" @click="selectedRegion = r" 
            :class="['px-4 py-2.5 rounded-full font-black text-xs uppercase tracking-widest whitespace-nowrap transition-all border-2', 
            selectedRegion === r ? 'bg-blue-600 text-white border-blue-600 shadow-lg' : 'bg-white text-slate-400 border-slate-200 hover:border-slate-300']">
            @{{ r }} <span class="ml-1 opacity-60">(@{{ getPlayersByRegion(r).length }})</span>
        </button>
    </div>

    {{-- Results Info --}}
    <div v-if="searchQuery || selectedRegion !== '全部'" class="flex items-center gap-3 text-sm">
        <span class="text-slate-400">搜尋結果:</span>
        <span class="font-black text-slate-900">@{{ filteredPlayers.length }} 位球友</span>
        <button v-if="searchQuery || selectedRegion !== '全部'" @click="searchQuery = ''; selectedRegion = '全部'" class="text-blue-600 text-xs font-bold underline">清除篩選</button>
    </div>

    {{-- Skeleton Loading --}}
    <div v-if="isPlayersLoading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div v-for="i in 8" :key="i" class="bg-white rounded-3xl p-4 shadow-lg border border-slate-100">
            <div class="flex gap-4">
                <div class="w-20 h-24 rounded-2xl skeleton-shimmer shrink-0"></div>
                <div class="flex-1 space-y-3">
                    <div class="h-5 w-24 skeleton-shimmer rounded-lg"></div>
                    <div class="flex gap-2">
                        <div class="h-5 w-16 skeleton-shimmer rounded-lg"></div>
                        <div class="h-5 w-12 skeleton-shimmer rounded-lg"></div>
                    </div>
                    <div class="h-4 w-full skeleton-shimmer rounded-lg"></div>
                </div>
            </div>
            <div class="flex gap-2 mt-4">
                <div class="flex-1 h-10 skeleton-shimmer rounded-xl"></div>
                <div class="flex-1 h-10 skeleton-shimmer rounded-xl"></div>
            </div>
        </div>
    </div>

    {{-- Player Cards Grid (Mobile Optimized) --}}
    <div v-else-if="paginatedPlayers.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div v-for="player in paginatedPlayers" :key="player.id" class="bg-white rounded-3xl p-4 shadow-lg border border-slate-100 card-3d">
            {{-- Mobile Compact Card --}}
            <div class="flex gap-4">
                {{-- Photo --}}
                <div class="w-20 h-24 rounded-2xl overflow-hidden shrink-0 bg-slate-100 cursor-pointer" @click="showDetail(player)">
                    <img :src="player.photo || 'https://images.unsplash.com/photo-1614743758466-e569f4791116?q=80&w=200&auto=format&fit=crop'" class="w-full h-full object-cover">
                </div>
                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <h3 class="font-black text-lg uppercase italic tracking-tight truncate cursor-pointer hover:text-blue-600 transition-colors" @click="showDetail(player)">@{{ player.name }}</h3>
                    <div class="flex flex-wrap items-center gap-2 mt-1">
                        <span class="bg-blue-600 text-white text-[10px] font-black px-2 py-0.5 rounded-lg">NTRP @{{ player.level }}</span>
                        <span class="text-slate-400 text-xs font-bold">@{{ player.region }}</span>
                    </div>
                    <div class="flex items-center gap-3 mt-2 text-[11px] text-slate-500 font-bold">
                        <span>@{{ player.gender }}</span>
                        <span>•</span>
                        <span>@{{ player.handed }}</span>
                        <span v-if="player.backhand">•</span>
                        <span v-if="player.backhand">@{{ player.backhand }}</span>
                    </div>
                    <p v-if="player.intro" class="text-xs text-slate-400 mt-2 line-clamp-2">@{{ player.intro }}</p>
                </div>
            </div>
            {{-- Actions --}}
            <div class="flex gap-2 mt-4">
                <button @click="showDetail(player)" class="flex-1 py-3 bg-slate-100 text-slate-700 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-slate-200 transition-all flex items-center justify-center gap-2">
                    <app-icon name="user" class-name="w-4 h-4"></app-icon> 詳細
                </button>
                <button @click="openMatchModal(player)" class="flex-1 py-3 bg-blue-600 text-white rounded-xl font-black text-xs uppercase tracking-widest hover:bg-blue-700 transition-all shadow-lg flex items-center justify-center gap-2">
                    <app-icon name="message-circle" class-name="w-4 h-4"></app-icon> 約打
                </button>
            </div>
        </div>
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
        <button @click="currentPage = Math.max(1, currentPage - 1)" :disabled="currentPage === 1"
            :class="['w-10 h-10 rounded-xl font-black text-sm transition-all', currentPage === 1 ? 'bg-slate-100 text-slate-300 cursor-not-allowed' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50']">
            ←
        </button>
        <template v-for="page in displayPages" :key="page">
            <span v-if="page === '...'" class="w-10 h-10 flex items-center justify-center text-slate-400">...</span>
            <button v-else @click="currentPage = page"
                :class="['w-10 h-10 rounded-xl font-black text-sm transition-all', currentPage === page ? 'bg-blue-600 text-white shadow-lg' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50']">
                @{{ page }}
            </button>
        </template>
        <button @click="currentPage = Math.min(totalPages, currentPage + 1)" :disabled="currentPage === totalPages"
            :class="['w-10 h-10 rounded-xl font-black text-sm transition-all', currentPage === totalPages ? 'bg-slate-100 text-slate-300 cursor-not-allowed' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50']">
            →
        </button>
    </div>
</div>
