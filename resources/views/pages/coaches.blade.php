{{-- Coaches View --}}
<div v-if="view === 'coaches'" class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-6 sm:gap-8">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl sm:text-5xl font-black italic uppercase tracking-tighter leading-tight text-slate-900">找教練</h2>
                <p class="text-slate-400 font-bold text-xs sm:text-base uppercase tracking-[0.2em] mt-1">Find Your Coach</p>
            </div>
            <div class="text-right hidden sm:block">
                <div class="text-2xl font-black text-blue-600">@{{ playersPagination.total }}</div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">位教練</div>
            </div>
        </div>

        {{-- CTA + Filter Bar --}}
        <div class="flex flex-col xl:flex-row items-stretch xl:items-center gap-4 bg-white p-4 sm:p-5 rounded-[32px] border border-slate-200 shadow-sm relative z-20">
            <div class="flex items-center gap-4 w-full xl:w-auto">
                {{-- Region Select --}}
                <div class="flex-1 xl:flex-none shrink-0 flex items-center bg-slate-50 px-3 py-1 rounded-2xl border border-slate-100">
                    <div class="text-slate-400 pl-1"><app-icon name="map-pin" class-name="w-4 h-4"></app-icon></div>
                    <select v-model="coachRegionDraft" @change="handleCoachSearch" class="w-full bg-transparent pl-2 pr-8 py-3 sm:py-3.5 focus:outline-none font-black text-sm uppercase tracking-widest cursor-pointer appearance-none min-w-[100px] sm:min-w-[120px]">
                        <option value="全部">全部地區</option>
                        <option v-for="r in activeRegions" :key="r" :value="r">@{{ r }}</option>
                    </select>
                </div>

                {{-- Sort Select --}}
                <div class="flex-1 xl:flex-none shrink-0 flex items-center bg-slate-50 px-3 py-1 rounded-2xl border border-slate-100">
                    <div class="text-slate-400 pl-1"><app-icon name="bar-chart-3" class-name="w-4 h-4"></app-icon></div>
                    <select v-model="coachSortBy" class="w-full bg-transparent pl-2 pr-8 py-3 sm:py-3.5 focus:outline-none font-black text-sm uppercase tracking-widest cursor-pointer appearance-none min-w-[100px]">
                        <option value="popular">熱門</option>
                        <option value="rated">好評</option>
                        <option value="newest">最新</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-grow min-w-0">
                <div class="relative flex-1 group">
                    <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-500 transition-colors">
                        <app-icon name="search" class-name="w-5 h-5"></app-icon>
                    </div>
                    <input type="text" v-model="coachSearchDraft" @keyup.enter="handleCoachSearch" placeholder="搜尋教練 / 專長..."
                        class="w-full min-w-[160px] sm:min-w-0 pl-12 pr-4 py-3 sm:py-3.5 bg-slate-50 border-2 border-transparent focus:border-blue-500 rounded-2xl outline-none font-bold text-base transition-all text-slate-700 placeholder:text-slate-400">
                </div>

                <button @click="showCoachFilters = !showCoachFilters"
                    :class="['px-4 py-3.5 rounded-2xl border-2 transition-all flex items-center justify-center gap-2 shrink-0', showCoachFilters ? 'bg-blue-50 border-blue-200 text-blue-600' : 'bg-slate-50 border-transparent text-slate-400 hover:bg-slate-100']">
                    <app-icon name="filter" class-name="w-5 h-5"></app-icon>
                </button>

                <button @click="handleCoachSearch" class="px-6 sm:px-8 py-3.5 bg-slate-900 text-white rounded-2xl font-black uppercase tracking-widest text-sm sm:text-base hover:bg-blue-600 transition-all shadow-lg active:scale-95 shrink-0">
                    搜尋
                </button>

                <button @click="openCoachForm" class="px-5 sm:px-6 py-3.5 bg-blue-600 text-white rounded-2xl font-black uppercase tracking-widest text-sm sm:text-base hover:bg-blue-700 transition-all shadow-lg active:scale-95 shrink-0">
                    我是教練
                </button>
            </div>
        </div>

        {{-- Advanced Filters Panel --}}
        <transition name="fade-slide">
            <div v-if="showCoachFilters" class="bg-white border border-slate-100 rounded-[32px] p-6 sm:p-8 shadow-xl shadow-slate-200/50 space-y-8 relative z-10 -mt-4 pt-12">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="space-y-3">
                        <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">教學方式</label>
                        <div class="flex gap-2">
                            <button v-for="m in ['全部', ...coachMethods]" :key="m" @click="coachMethodDraft = m"
                                :class="['flex-1 py-3 rounded-xl font-black text-xs transition-all border-2', coachMethodDraft === m ? 'bg-slate-900 text-white border-slate-900 shadow-md' : 'bg-slate-50 text-slate-400 border-transparent hover:border-slate-200']">
                                @{{ m }}
                            </button>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">每小時費用</label>
                        <input v-model="coachPriceMinDraft" type="number" min="0" placeholder="時薪"
                            class="w-full px-3 py-3 bg-slate-50 border-2 border-transparent rounded-xl outline-none focus:border-blue-500 font-black text-xs transition-all">
                    </div>

                    <div class="space-y-3">
                        <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">上課地點</label>
                        <input v-model="coachLocationDraft" type="text" placeholder="例如：台北 / 大安"
                            class="w-full px-3 py-3 bg-slate-50 border-2 border-transparent rounded-xl outline-none focus:border-blue-500 font-black text-xs transition-all">
                    </div>

                    <div class="space-y-3">
                        <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">專長</label>
                        <input v-model="coachTagDraft" type="text" placeholder="例如：發球 / 雙打"
                            class="w-full px-3 py-3 bg-slate-50 border-2 border-transparent rounded-xl outline-none focus:border-blue-500 font-black text-xs transition-all">
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-slate-50">
                    <button @click="handleCoachSearch" class="w-full sm:w-auto px-12 py-4 bg-blue-600 text-white rounded-2xl font-black uppercase tracking-widest text-sm hover:bg-blue-700 transition-all shadow-lg shadow-blue-200 active:scale-95">
                        確認篩選
                    </button>
                </div>
            </div>
        </transition>
    </div>

    {{-- Results Info --}}
    <div v-if="coachSearchQuery || coachSelectedRegion !== '全部' || coachPriceMin || coachPriceMax || coachSelectedMethod !== '全部' || coachSelectedTag || coachSelectedLocation" class="flex items-center gap-3 text-sm px-1">
        <span class="text-slate-400 font-medium">篩選條件:</span>
        <div class="flex flex-wrap gap-2 bg-white/80 border border-slate-100 rounded-2xl px-3 py-2">
            <span v-if="coachSearchQuery" class="px-3 py-1 bg-white border border-blue-100 text-blue-600 rounded-full text-[10px] font-black uppercase tracking-wider shadow-sm">關鍵字: @{{ coachSearchQuery }}</span>
            <span v-if="coachSelectedRegion !== '全部'" class="px-3 py-1 bg-white border border-slate-200 text-slate-600 rounded-full text-[10px] font-black uppercase tracking-wider shadow-sm">@{{ coachSelectedRegion }}</span>
            <span v-if="coachSelectedMethod !== '全部'" class="px-3 py-1 bg-white border border-slate-200 text-slate-600 rounded-full text-[10px] font-black uppercase tracking-wider shadow-sm">@{{ coachSelectedMethod }}</span>
            <span v-if="coachPriceMin" class="px-3 py-1 bg-white border border-slate-200 text-slate-600 rounded-full text-[10px] font-black uppercase tracking-wider shadow-sm">時薪 @{{ coachPriceMin }}</span>
            <span v-if="coachSelectedTag" class="px-3 py-1 bg-white border border-slate-200 text-slate-600 rounded-full text-[10px] font-black uppercase tracking-wider shadow-sm">專長: @{{ coachSelectedTag }}</span>
            <span v-if="coachSelectedLocation" class="px-3 py-1 bg-white border border-slate-200 text-slate-600 rounded-full text-[10px] font-black uppercase tracking-wider shadow-sm">地點: @{{ coachSelectedLocation }}</span>
        </div>
        <span class="text-slate-300 text-xs font-black uppercase tracking-widest hidden sm:inline">第 @{{ playersPagination.current_page }} / @{{ coachTotalPages }} 頁</span>
        <button type="button" @click="coachSearchDraft = ''; coachSearchQuery = ''; coachRegionDraft = '全部'; coachSelectedRegion = '全部'; coachPriceMinDraft = ''; coachPriceMaxDraft = ''; coachPriceMin = ''; coachPriceMax = ''; coachMethodDraft = '全部'; coachSelectedMethod = '全部'; coachTagDraft = ''; coachSelectedTag = ''; coachLocationDraft = ''; coachSelectedLocation = ''; handleCoachSearch();" class="text-red-500 text-xs font-black uppercase tracking-widest hover:underline ml-auto">清除全部</button>
    </div>

    {{-- Skeleton Loading --}}
    <div v-if="isPlayersLoading" class="grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-6">
        <div v-for="i in 12" :key="i" class="relative max-w-60 mx-auto w-full">
            <div class="aspect-[2.5/3.8] rounded-2xl skeleton-shimmer"></div>
        </div>
    </div>

    {{-- Coach Cards Grid --}}
    <div v-else-if="coachPaginatedPlayers.length > 0" class="grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-6">
        <template v-for="(player, index) in coachPaginatedPlayers" :key="player?.id ? `coach-${player.id}` : `coach-placeholder-${index}`">
            <div v-if="player && player.id" class="relative group max-w-60 mx-auto w-full transition-transform duration-300 sm:hover:-translate-y-1">
                <div @click="showDetail(player)" class="cursor-pointer">
                    <player-card :player="player" size="sm"></player-card>
                </div>
                <div v-if="player.is_coach" class="mt-2.5 space-y-2">
                    <div class="flex items-center justify-center bg-white border border-amber-100 rounded-xl px-3.5 py-2 shadow-sm">
                        <div class="text-base font-black text-slate-900">
                            @{{ player.coach_price_min ? `$${Number(player.coach_price_min).toLocaleString('en-US')}` : '洽談為主' }}
                            <span v-if="player.coach_price_min" class="text-[11px] font-black text-amber-600">/小時</span>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 text-[10px] font-bold text-slate-500">
                        <div v-if="player.coach_locations" class="flex items-center gap-1 bg-slate-50 border border-slate-100 rounded-full px-2 py-1">
                            <span class="uppercase tracking-wider text-slate-400">地點</span>
                            <span class="text-slate-700">
                                @{{ String(player.coach_locations).split(',').map(x => x.trim()).filter(x => x).slice(0, 2).join('、') }}
                            </span>
                            <span v-if="String(player.coach_locations).split(',').map(x => x.trim()).filter(x => x).length > 2" class="text-slate-400">...</span>
                        </div>
                    </div>
                    <div v-if="player.coach_tags" class="flex flex-wrap gap-2 max-h-[64px] overflow-hidden">
                        <span v-for="(t, idx) in String(player.coach_tags).split(',').map(x => x.trim()).filter(x => x).slice(0, 5)" :key="`${t}-${idx}`" class="px-3 py-1.5 rounded-full bg-amber-50 text-amber-800 text-[10px] font-black uppercase tracking-[0.16em] border border-amber-200">
                            @{{ t }}
                        </span>
                        <span v-if="String(player.coach_tags).split(',').map(x => x.trim()).filter(x => x).length > 5" class="px-3 py-1.5 rounded-full bg-white text-slate-500 text-[10px] font-black uppercase tracking-[0.16em] border border-slate-200">
                            +@{{ String(player.coach_tags).split(',').map(x => x.trim()).filter(x => x).length - 5 }}
                        </span>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- Empty State --}}
    <div v-else class="text-center py-20">
        <div class="bg-slate-100 w-20 h-20 rounded-3xl flex items-center justify-center mx-auto mb-6">
            <app-icon name="search" class-name="w-10 h-10 text-slate-300"></app-icon>
        </div>
        <h3 class="text-xl font-black uppercase italic tracking-tight mb-2">找不到符合的教練</h3>
        <p class="text-slate-400 font-bold">請嘗試其他搜尋條件或地區</p>
        <div class="mt-6 bg-white border border-slate-100 rounded-2xl p-4 max-w-md mx-auto text-left">
            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3">推薦篩選</div>
            <div class="flex flex-wrap gap-2">
                <button type="button" @click="coachMethodDraft = '個人'; coachSelectedMethod = '個人'; handleCoachSearch();" class="px-3 py-1.5 rounded-full bg-amber-50 text-amber-700 text-[10px] font-black uppercase tracking-[0.18em] border border-amber-200">個人</button>
                <button type="button" @click="coachMethodDraft = '團體'; coachSelectedMethod = '團體'; handleCoachSearch();" class="px-3 py-1.5 rounded-full bg-amber-50 text-amber-700 text-[10px] font-black uppercase tracking-[0.18em] border border-amber-200">團體</button>
                <button type="button" @click="coachRegionDraft = '台北市'; coachSelectedRegion = '台北市'; handleCoachSearch();" class="px-3 py-1.5 rounded-full bg-white text-slate-600 text-[10px] font-black uppercase tracking-[0.18em] border border-slate-200">台北市</button>
                <button type="button" @click="coachRegionDraft = '台中市'; coachSelectedRegion = '台中市'; handleCoachSearch();" class="px-3 py-1.5 rounded-full bg-white text-slate-600 text-[10px] font-black uppercase tracking-[0.18em] border border-slate-200">台中市</button>
            </div>
            <button type="button" @click="coachSearchDraft = ''; coachSearchQuery = ''; coachRegionDraft = '全部'; coachSelectedRegion = '全部'; coachPriceMinDraft = ''; coachPriceMaxDraft = ''; coachPriceMin = ''; coachPriceMax = ''; coachMethodDraft = '全部'; coachSelectedMethod = '全部'; coachTagDraft = ''; coachSelectedTag = ''; coachLocationDraft = ''; coachSelectedLocation = ''; handleCoachSearch();" class="mt-3 text-blue-600 text-[10px] font-black uppercase tracking-widest hover:underline">清除所有篩選</button>
        </div>
    </div>

    {{-- Pagination --}}
    <div v-if="coachTotalPages > 1" class="flex items-center justify-center gap-1 sm:gap-2 pt-8 pb-12 overflow-x-auto no-scrollbar flex-nowrap min-w-0 w-full">
        <button type="button" @click="coachCurrentPage = Math.max(1, playersPagination.current_page - 1)" :disabled="playersPagination.current_page === 1"
            :class="['w-9 h-9 sm:w-10 sm:h-10 shrink-0 rounded-lg sm:rounded-xl font-black text-xs sm:text-sm transition-all', playersPagination.current_page === 1 ? 'bg-slate-100 text-slate-300 cursor-not-allowed' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50']">
            ←
        </button>

        <div v-for="(page, idx) in coachDisplayPages" :key="'coach-p-' + idx" class="flex shrink-0">
            <span v-if="page === '...'" class="w-6 h-8 sm:w-10 sm:h-10 flex items-center justify-center text-slate-400 text-xs">...</span>
            <button v-else type="button" @click="coachCurrentPage = page"
                :class="['w-9 h-9 sm:w-10 sm:h-10 rounded-lg sm:rounded-xl font-black text-xs sm:text-sm transition-all', playersPagination.current_page === page ? 'bg-blue-600 text-white shadow-lg' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50']">
                @{{ page }}
            </button>
        </div>

        <button type="button" @click="coachCurrentPage = Math.min(coachTotalPages, playersPagination.current_page + 1)" :disabled="playersPagination.current_page === coachTotalPages"
            :class="['w-9 h-9 sm:w-10 sm:h-10 shrink-0 rounded-lg sm:rounded-xl font-black text-xs sm:text-sm transition-all', playersPagination.current_page === coachTotalPages ? 'bg-slate-100 text-slate-300 cursor-not-allowed' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50']">
            →
        </button>
    </div>

    {{-- Coach Form Modal --}}
    <transition name="modal">
        <div v-if="showCoachForm" class="fixed inset-0 z-[250] flex items-center justify-center p-4 sm:p-6" @click.self="closeCoachForm">
            <div class="bg-white w-full max-w-2xl rounded-[32px] shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
                <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <div>
                        <h3 class="text-2xl font-black italic uppercase tracking-tight text-slate-900">教練資料</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Coach Profile</p>
                    </div>
                    <button @click="closeCoachForm" class="w-10 h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:text-slate-600 hover:border-slate-300 transition-all shadow-sm">
                        <app-icon name="x" class-name="w-5 h-5"></app-icon>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-8 sm:p-10 space-y-6 custom-scrollbar">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">每小時費用</label>
                            <input v-model="coachForm.coach_price_min" type="number" min="0" placeholder="時薪"
                                class="w-full px-4 py-3 bg-slate-50 border-2 border-transparent rounded-2xl outline-none focus:border-blue-500 font-black text-sm">
                            <input v-model="coachForm.coach_price_note" type="text" placeholder="例如：含場地費"
                                class="w-full px-4 py-3 bg-slate-50 border-2 border-transparent rounded-2xl outline-none focus:border-blue-500 font-black text-sm">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">教學方式</label>
                            <div class="flex flex-wrap gap-2">
                                <button v-for="m in coachMethods" :key="m" type="button"
                                    @click="coachForm.coach_methods.includes(m) ? coachForm.coach_methods = coachForm.coach_methods.filter(x => x !== m) : coachForm.coach_methods.push(m)"
                                    :class="['px-4 py-2 rounded-xl font-black text-xs transition-all border-2', coachForm.coach_methods.includes(m) ? 'bg-blue-600 text-white border-blue-600 shadow-md' : 'bg-slate-50 text-slate-400 border-transparent hover:border-slate-200']">
                                    @{{ m }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">上課地點（逗號分隔）</label>
                        <input v-model="coachForm.coach_locations" type="text" placeholder="例：台北市, 新北市"
                            class="w-full px-4 py-3 bg-slate-50 border-2 border-transparent rounded-2xl outline-none focus:border-blue-500 font-black text-sm">
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">專長標籤（逗號分隔）</label>
                        <input v-model="coachForm.coach_tags" type="text" placeholder="例：發球, 正拍, 雙打"
                            class="w-full px-4 py-3 bg-slate-50 border-2 border-transparent rounded-2xl outline-none focus:border-blue-500 font-black text-sm">
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">資歷 / 教學風格</label>
                        <textarea v-model="coachForm.coach_certs" rows="4" placeholder="描述資歷與教學風格"
                            class="w-full px-4 py-3 bg-slate-50 border-2 border-transparent rounded-2xl outline-none focus:border-blue-500 font-black text-sm"></textarea>
                    </div>
                </div>

                <div class="px-8 py-6 border-t border-slate-100 bg-white flex items-center justify-end gap-3">
                    <button @click="closeCoachForm" class="px-6 py-3 rounded-2xl font-black text-xs uppercase tracking-widest text-slate-400 hover:text-slate-600">取消</button>
                    <button @click="saveCoachProfile" :disabled="isSavingCoach" class="px-8 py-3 rounded-2xl font-black text-xs uppercase tracking-widest text-white bg-blue-600 hover:bg-blue-500 transition-all disabled:opacity-60">
                        <span v-if="!isSavingCoach">儲存教練資料</span>
                        <span v-else>儲存中...</span>
                    </button>
                </div>
            </div>
        </div>
    </transition>
</div>
