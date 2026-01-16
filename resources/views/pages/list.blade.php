{{-- List View --}}
<div v-if="view === 'list'" class="space-y-6">
    {{-- SEO Content for Crawlers (SSR) --}}
    <div class="sr-only" aria-hidden="true">
        @foreach($initialPlayers ?? [] as $p)
            <h3>{{ $p->name }} - {{ $p->level }} ({{ $p->region }})</h3>
            <p>{{ $p->intro }}</p>
        @endforeach
    </div>
    {{-- Header --}}
    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl sm:text-5xl font-black italic uppercase tracking-tighter leading-tight">球友列表</h2>
                <p class="text-slate-400 font-bold text-xs sm:text-base uppercase tracking-[0.2em] mt-1">Find your matching</p>
            </div>
            <div class="text-right">
                <div class="text-2xl font-black text-blue-600">@{{ playersPagination.total }}</div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">位球友</div>
            </div>
        </div>
        
        {{-- Search Bar with Region Select --}}
        <div class="relative flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <app-icon name="search" class-name="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 w-5 h-5"></app-icon>
                <input type="text" v-model="searchDraft" @keyup.enter="handleSearch" placeholder="搜尋姓名、程度或地區..." class="w-full pl-12 pr-4 py-3 sm:py-4 bg-white border border-slate-200 rounded-2xl outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 font-bold text-base transition-all shadow-sm">
            </div>
            <div class="flex gap-2">
                <select v-model="regionDraft" @change="handleSearch" class="flex-1 sm:flex-none px-4 py-3 sm:py-4 bg-white border border-slate-200 rounded-2xl outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 font-black text-sm uppercase tracking-widest cursor-pointer appearance-none min-w-[100px] sm:min-w-[120px] shadow-sm">
                    <option value="全部">全部地區</option>
                    <option v-for="r in activeRegions" :key="r" :value="r">@{{ r }}</option>
                </select>
                <button @click="showAdvancedFilters = !showAdvancedFilters" :class="['px-4 rounded-2xl border transition-all flex items-center justify-center gap-2 shadow-sm', showAdvancedFilters ? 'bg-blue-50 border-blue-200 text-blue-600' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50']">
                    <app-icon name="filter" class-name="w-5 h-5"></app-icon>
                    <span class="hidden sm:inline font-black text-sm uppercase tracking-widest">篩選</span>
                </button>
                <button @click="handleSearch" class="px-6 sm:px-8 bg-slate-900 text-white rounded-2xl font-black uppercase tracking-widest text-sm sm:text-base hover:bg-blue-600 transition-all shadow-lg active:scale-95">
                    搜尋
                </button>
            </div>
        </div>

        {{-- Quick NTRP Filters --}}
        <div class="bg-white border border-slate-200 rounded-[22px] p-3 sm:p-4 shadow-sm">
            <div class="flex flex-nowrap items-center gap-2 overflow-x-auto no-scrollbar">
                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-900 text-white text-[10px] sm:text-[11px] font-black uppercase tracking-[0.2em] shrink-0">
                    <app-icon name="filter" class-name="w-4 h-4"></app-icon>
                    快速篩選
                </div>
                <button type="button" @click="applyQuickLevel('5.0', '7.0', 'pro')" :class="['px-3 py-1.5 rounded-full text-[11px] sm:text-sm font-black uppercase tracking-widest border transition-all shrink-0', activeQuickLevel === 'pro' ? 'bg-slate-900 text-white border-slate-900 shadow-sm' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50']">
                    🏆 找大神
                </button>
                <button type="button" @click="applyQuickLevel('4.0', '5.0', 'battle')" :class="['px-3 py-1.5 rounded-full text-[11px] sm:text-sm font-black uppercase tracking-widest border transition-all shrink-0', activeQuickLevel === 'battle' ? 'bg-slate-900 text-white border-slate-900 shadow-sm' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50']">
                    ⚔️ 競技對戰
                </button>
                <button type="button" @click="applyQuickLevel('3.5', '4.5', 'mid')" :class="['px-3 py-1.5 rounded-full text-[11px] sm:text-sm font-black uppercase tracking-widest border transition-all shrink-0', activeQuickLevel === 'mid' ? 'bg-slate-900 text-white border-slate-900 shadow-sm' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50']">
                    🚀 中高級
                </button>
                <button type="button" @click="applyQuickLevel('3.0', '3.5', 'steady')" :class="['px-3 py-1.5 rounded-full text-[11px] sm:text-sm font-black uppercase tracking-widest border transition-all shrink-0', activeQuickLevel === 'steady' ? 'bg-slate-900 text-white border-slate-900 shadow-sm' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50']">
                    🎯 穩定練習
                </button>
                <button type="button" @click="applyQuickLevel('1.0', '2.5', 'starter')" :class="['px-3 py-1.5 rounded-full text-[11px] sm:text-sm font-black uppercase tracking-widest border transition-all shrink-0', activeQuickLevel === 'starter' ? 'bg-slate-900 text-white border-slate-900 shadow-sm' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50']">
                    🌱 初學友善
                </button>
                <button type="button" @click="applyQuickLevel('', '', 'all')" :class="['px-3 py-1.5 rounded-full text-[11px] sm:text-sm font-black uppercase tracking-widest border transition-all shrink-0', activeQuickLevel === 'all' ? 'bg-slate-900 text-white border-slate-900 shadow-sm' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50']">
                    🎾 不限程度
                </button>
            </div>
        </div>

        {{-- Advanced Filters Panel --}}
        <transition name="fade-slide">
            <div v-if="showAdvancedFilters" class="bg-white border border-slate-100 rounded-[32px] p-6 sm:p-8 shadow-xl shadow-slate-200/50 space-y-8">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    {{-- Gender Filter --}}
                    <div class="space-y-3">
                        <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">生理性別</label>
                        <div class="flex gap-2">
                            <button v-for="g in ['全部', '男', '女']" :key="g" @click="genderDraft = g" 
                                :class="['flex-1 py-3 rounded-xl font-black text-xs transition-all border-2', genderDraft === g ? 'bg-slate-900 text-white border-slate-900 shadow-md' : 'bg-slate-50 text-slate-400 border-transparent hover:border-slate-200']">
                                @{{ g }}
                            </button>
                        </div>
                    </div>

                    {{-- Handedness Filter --}}
                    <div class="space-y-3">
                        <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">持拍手</label>
                        <div class="flex gap-2">
                            <button v-for="h in ['全部', '右手', '左手']" :key="h" @click="handedDraft = h" 
                                :class="['flex-1 py-3 rounded-xl font-black text-xs transition-all border-2', handedDraft === h ? 'bg-slate-900 text-white border-slate-900 shadow-md' : 'bg-slate-50 text-slate-400 border-transparent hover:border-slate-200']">
                                @{{ h }}
                            </button>
                        </div>
                    </div>

                    {{-- Backhand Filter --}}
                    <div class="space-y-3">
                        <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">反手類型</label>
                        <div class="flex gap-2">
                            <button v-for="b in ['全部', '單反', '雙反']" :key="b" @click="backhandDraft = b" 
                                :class="['flex-1 py-3 rounded-xl font-black text-xs transition-all border-2', backhandDraft === b ? 'bg-slate-900 text-white border-slate-900 shadow-md' : 'bg-slate-50 text-slate-400 border-transparent hover:border-slate-200']">
                                @{{ b }}
                            </button>
                        </div>
                    </div>

                    {{-- NTRP Range Filter --}}
                    <div class="space-y-3">
                        <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">NTRP 程度範圍</label>
                        <div class="flex items-center gap-2">
                            <select v-model="levelMinDraft" class="flex-1 px-3 py-3 bg-slate-50 border-2 border-transparent rounded-xl outline-none focus:border-blue-500 font-black text-xs transition-all appearance-none">
                                <option value="">Min</option>
                                <option v-for="l in levels" :key="'min-'+l" :value="l">@{{ l }}</option>
                            </select>
                            <span class="text-slate-300 font-black text-xs">~</span>
                            <select v-model="levelMaxDraft" class="flex-1 px-3 py-3 bg-slate-50 border-2 border-transparent rounded-xl outline-none focus:border-blue-500 font-black text-xs transition-all appearance-none">
                                <option value="">Max</option>
                                <option v-for="l in levels" :key="'max-'+l" :value="l">@{{ l }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex justify-end pt-4 border-t border-slate-50">
                    <button @click="handleSearch" class="w-full sm:w-auto px-12 py-4 bg-blue-600 text-white rounded-2xl font-black uppercase tracking-widest text-sm hover:bg-blue-700 transition-all shadow-lg shadow-blue-200 active:scale-95">
                        確認篩選
                    </button>
                </div>
            </div>
        </transition>
    </div>

    {{-- Results Info --}}
    <div v-if="searchQuery || selectedRegion !== '全部' || selectedGender !== '全部' || selectedLevelMin || selectedLevelMax || selectedHanded !== '全部' || selectedBackhand !== '全部'" class="flex items-center gap-3 text-sm">
        <span class="text-slate-400">篩選條件:</span>
        <div class="flex flex-wrap gap-2">
            <span v-if="searchQuery" class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-[10px] font-black uppercase tracking-wider">關鍵字: @{{ searchQuery }}</span>
            <span v-if="selectedRegion !== '全部'" class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-[10px] font-black uppercase tracking-wider">地區: @{{ selectedRegion }}</span>
            <span v-if="selectedGender !== '全部'" class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-[10px] font-black uppercase tracking-wider">性別: @{{ selectedGender }}</span>
            <span v-if="selectedHanded !== '全部'" class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-[10px] font-black uppercase tracking-wider">持拍: @{{ selectedHanded }}</span>
            <span v-if="selectedBackhand !== '全部'" class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-[10px] font-black uppercase tracking-wider">反手: @{{ selectedBackhand }}</span>
            <span v-if="selectedLevelMin || selectedLevelMax" class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-[10px] font-black uppercase tracking-wider">
                程度: @{{ selectedLevelMin || '1.0' }} - @{{ selectedLevelMax || '7.0' }}
            </span>
        </div>
        <button type="button" @click="searchDraft = ''; searchQuery = ''; regionDraft = '全部'; selectedRegion = '全部'; genderDraft = '全部'; selectedGender = '全部'; levelMinDraft = ''; selectedLevelMin = ''; levelMaxDraft = ''; selectedLevelMax = ''; handedDraft = '全部'; selectedHanded = '全部'; backhandDraft = '全部'; selectedBackhand = '全部'; activeQuickLevel = 'all'; handleSearch();" class="text-red-500 text-xs font-black uppercase tracking-widest hover:underline ml-auto">清除全部</button>
    </div>

    {{-- Skeleton Loading --}}
    <div v-if="isPlayersLoading" class="grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-6">
        <div v-for="i in 12" :key="i" class="relative max-w-60 mx-auto w-full">
            <div class="aspect-[2.5/3.8] rounded-2xl skeleton-shimmer"></div>
        </div>
    </div>

    {{-- Player Cards Grid (Using PlayerCard Component) --}}
    <div v-else-if="paginatedPlayers.length > 0" class="grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-6">
        <template v-for="(player, index) in paginatedPlayers" :key="player?.id ? `player-${player.id}` : `placeholder-${index}`">
            <div v-if="player && player.id" class="relative group max-w-60 mx-auto w-full transition-transform duration-300 sm:hover:-translate-y-2">
                {{-- Player Card Component with proper positioning data --}}
                <div @click="showDetail(player)" class="cursor-pointer">
                    <player-card 
                        :player="player" 
                        size="sm">
                    </player-card>
                </div>
            {{-- Action Buttons Overlay (Hidden on Mobile) --}}
            <div class="absolute bottom-4 left-4 right-4 hidden sm:flex gap-2 opacity-0 group-hover:opacity-100 pointer-events-none group-hover:pointer-events-auto transition-opacity duration-200 z-30">
                <button type="button" @click.stop="showDetail(player)" class="flex-1 py-3 bg-white/95 text-slate-700 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-white transition-colors flex items-center justify-center gap-2 shadow-lg">
                    <app-icon name="user" class-name="w-4 h-4"></app-icon> 詳細
                </button>
                <button type="button" @click.stop="openMatchModal(player)" class="flex-1 py-3 bg-blue-600/95 text-white rounded-xl font-black text-xs uppercase tracking-widest hover:bg-blue-600 transition-colors flex items-center justify-center gap-2 shadow-lg">
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
    <div v-if="totalPages > 1" class="flex flex-wrap items-center justify-center gap-2 pt-8">
        {{-- Previous Button --}}
        <button type="button" @click="currentPage = Math.max(1, playersPagination.current_page - 1)" :disabled="playersPagination.current_page === 1"
            :class="['w-10 h-10 rounded-xl font-black text-sm transition-all', playersPagination.current_page === 1 ? 'bg-slate-100 text-slate-300 cursor-not-allowed' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50']">
            ←
        </button>
        
        {{-- Page Numbers --}}
        <div v-for="(page, idx) in displayPages" :key="'p-' + idx" class="flex">
            <span v-if="page === '...'" class="w-10 h-10 flex items-center justify-center text-slate-400">...</span>
            <button v-else type="button" @click="currentPage = page"
                :class="['w-10 h-10 rounded-xl font-black text-sm transition-all', playersPagination.current_page === page ? 'bg-blue-600 text-white shadow-lg' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50']">
                @{{ page }}
            </button>
        </div>
        
        {{-- Next Button --}}
        <button type="button" @click="currentPage = Math.min(totalPages, playersPagination.current_page + 1)" :disabled="playersPagination.current_page === totalPages"
            :class="['w-10 h-10 rounded-xl font-black text-sm transition-all', playersPagination.current_page === totalPages ? 'bg-slate-100 text-slate-300 cursor-not-allowed' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50']">
            →
        </button>
    </div>
</div>
