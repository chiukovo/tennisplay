<div v-if="view === 'events'" class="space-y-8">
    {{-- SEO Content for Crawlers (SSR) --}}
    <div class="sr-only" aria-hidden="true">
        @foreach($initialEvents ?? [] as $e)
            <h3>{{ $e->title }} - {{ $e->event_date }} ({{ $e->location }})</h3>
            <p>{{ $e->notes }}</p>
        @endforeach
    </div>
    {{-- Header --}}
    <div class="flex flex-nowrap items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-3xl sm:text-5xl font-black italic uppercase tracking-tighter leading-tight text-slate-900">開團揪球</h2>
            <p class="text-slate-400 font-bold text-xs sm:text-base uppercase tracking-[0.2em] mt-1">Join or Create Tennis Events</p>
        </div>
        <div class="flex items-center gap-6">
            <button v-if="isLoggedIn && hasPlayerCard" @click="navigateTo('create-event')" class="bg-blue-600 text-white px-6 py-4 rounded-2xl font-black uppercase tracking-widest text-xs shadow-2xl hover:-translate-y-1 hover:bg-blue-700 transition-all flex items-center gap-2 whitespace-nowrap shrink-0">
                <app-icon name="calendar-plus" class-name="w-5 h-5"></app-icon>
                開揪
            </button>
            <div class="text-right hidden sm:block">
                <div class="text-2xl font-black text-blue-600">@{{ eventsPagination.total }}</div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">場活動</div>
            </div>
        </div>
    </div>

    {{-- Filter Section --}}
    <div class="space-y-6 mb-10">
        {{-- Time & Date Search --}}
        {{-- Filter Header --}}
        <div class="flex flex-col lg:flex-row items-stretch lg:items-center gap-4 bg-white p-5 rounded-[32px] border border-slate-200 shadow-sm">
            {{-- Region Select --}}
            <div class="shrink-0 flex items-center bg-slate-50 px-3 py-1 rounded-2xl border border-slate-100">
                <div class="text-slate-400 pl-1"><app-icon name="map-pin" class-name="w-4 h-4"></app-icon></div>
                <select v-model="eventRegionFilter" class="bg-transparent pl-2 pr-4 py-3 sm:py-3.5 focus:outline-none font-black text-sm uppercase tracking-widest cursor-pointer appearance-none min-w-[100px] sm:min-w-[120px]">
                    <option value="all">全部地區</option>
                    <option v-for="r in activeEventRegions" :key="r" :value="r">@{{ r }}</option>
                </select>
            </div>

            {{-- Date Shortcuts --}}
            <div class="flex items-center gap-1 p-1 bg-slate-50 rounded-2xl overflow-x-auto no-scrollbar scroll-smooth">
                <button v-for="s in [
                    {val: 'today', label: '今日'},
                    {val: 'tomorrow', label: '明日'},
                    {val: 'week', label: '本週'},
                    {val: 'month', label: '本月'},
                    {val: 'all', label: '全部'}
                ]" :key="s.val" @click="setDateRange(s.val)"
                    :class="['px-4 sm:px-6 py-2.5 rounded-xl font-black text-xs sm:text-sm uppercase tracking-widest transition-all whitespace-nowrap shrink-0', 
                        eventDateShortcut === s.val ? 'bg-white text-blue-600 shadow-sm ring-1 ring-blue-50' : 'text-slate-400 hover:text-slate-600']">
                    @{{ s.label }}
                </button>
            </div>

            {{-- Date Range Picker --}}
            <div class="flex items-center gap-2 sm:gap-3 flex-grow lg:flex-grow-0 bg-slate-50 px-3 sm:px-4 py-1 rounded-2xl border border-slate-100 min-h-[52px]">
                <div class="text-slate-400 shrink-0"><app-icon name="calendar" class-name="w-4 h-4"></app-icon></div>
                <div class="flex items-center gap-1 sm:gap-2 flex-1 min-w-0">
                    <input type="date" v-model="eventStartDate" @change="eventDateShortcut = 'custom'"
                        class="bg-transparent py-2.5 sm:py-3 font-black text-[11px] sm:text-sm text-slate-700 outline-none w-0 flex-1 cursor-pointer min-w-0">
                    <span class="text-slate-300 font-bold shrink-0">~</span>
                    <input type="date" v-model="eventEndDate" @change="eventDateShortcut = 'custom'"
                        class="bg-transparent py-2.5 sm:py-3 font-black text-[11px] sm:text-sm text-slate-700 outline-none w-0 flex-1 cursor-pointer min-w-0">
                </div>
            </div>

            {{-- Search Input --}}
            <div class="relative flex flex-1 items-center gap-2">
                <div class="relative flex-1">
                    <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                        <app-icon name="search" class-name="w-5 h-5"></app-icon>
                    </div>
                    <input type="text" v-model="eventSearchDraft" @keyup.enter="handleEventSearch" placeholder="搜尋標題或地點..."
                        class="w-full pl-12 pr-4 py-3 sm:py-3.5 bg-slate-50 border-2 border-transparent focus:border-blue-500 rounded-2xl outline-none font-bold text-base transition-all">
                </div>
                <button @click="handleEventSearch" class="px-5 sm:px-8 py-3.5 bg-slate-900 text-white rounded-2xl font-black uppercase tracking-widest text-sm sm:text-base hover:bg-blue-600 transition-all shadow-lg active:scale-95 shrink-0">
                    搜尋
                </button>
            </div>
        </div>
    </div>

    {{-- Loading State --}}
    <div v-if="eventsLoading" class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div v-for="n in 6" :key="n" class="bg-white rounded-[32px] p-7 space-y-6 skeleton-shimmer border-[1.5px] border-slate-100">
            <div class="flex justify-between">
                <div class="h-8 bg-slate-100 rounded-full w-24"></div>
                <div class="h-8 bg-slate-100 rounded-full w-12"></div>
            </div>
            <div class="h-10 bg-slate-100 rounded-xl w-full"></div>
            <div class="space-y-3">
                <div class="h-4 bg-slate-100 rounded-lg w-2/3"></div>
                <div class="h-4 bg-slate-100 rounded-lg w-1/2"></div>
            </div>
            <div class="pt-6 border-t border-slate-50 flex justify-between">
                <div class="h-8 bg-slate-100 rounded-xl w-32"></div>
                <div class="h-8 bg-slate-100 rounded-xl w-16"></div>
            </div>
        </div>
    </div>

    {{-- Events Grid --}}
    <div v-else-if="paginatedEvents.length > 0">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div v-for="event in paginatedEvents" :key="event.id" 
                @click="openEventDetail(event)"
                class="bg-white rounded-[32px] p-5 sm:p-7 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border-[1.5px] border-slate-200 hover:shadow-[0_20px_50px_-12px_rgba(37,99,235,0.15)] hover:border-blue-500 transition-all cursor-pointer group relative overflow-hidden flex flex-col">
                
                {{-- Status & Capacity --}}
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-2">
                        <div class="flex items-center gap-1.5 bg-slate-50/80 px-3 py-1.5 rounded-full border border-slate-100">
                            <div :class="['w-2 h-2 rounded-full animate-pulse', event.status === 'open' ? 'bg-green-500' : event.status === 'full' ? 'bg-amber-500' : 'bg-slate-400']"></div>
                            <span :class="['text-[11px] font-black uppercase tracking-widest', event.status === 'open' ? 'text-green-600' : event.status === 'full' ? 'text-amber-600' : 'text-slate-400']">
                                @{{ event.status === 'open' ? '招募中' : event.status === 'full' ? '已滿' : '已結束' }}
                            </span>
                            <span class="text-slate-300 mx-1">|</span>
                            <span class="text-[11px] font-black text-slate-500 uppercase tracking-widest">
                                @{{ event.match_type === 'all' ? '不限' : event.match_type === 'singles' ? '單打' : event.match_type === 'doubles' ? '雙打' : '混雙' }}
                            </span>
                        </div>
                        <div v-if="event.is_organizer" class="bg-blue-600 text-white px-2.5 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest flex items-center gap-1 shadow-lg shadow-blue-100">
                            <app-icon name="star" class-name="w-3 h-3"></app-icon>
                            主辦
                        </div>
                        <div v-else-if="event.has_joined" class="bg-green-600 text-white px-2.5 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest flex items-center gap-1 shadow-lg shadow-green-100">
                            <app-icon name="check" class-name="w-3 h-3"></app-icon>
                            已報名
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 bg-blue-50 px-3 py-1.5 rounded-full border border-blue-100">
                        <span class="text-[10px] font-black text-blue-400 uppercase tracking-tighter">缺</span>
                        <span class="text-lg font-black italic text-blue-600 leading-none">@{{ event.max_participants === 0 ? '∞' : event.spots_left }}</span>
                    </div>
                </div>

                {{-- Title --}}
                <h3 class="font-black text-xl sm:text-2xl text-slate-900 group-hover:text-blue-600 transition-colors mb-6 leading-tight">
                    @{{ event.title }}
                </h3>

                {{-- Simplified Key Info --}}
                <div class="space-y-3.5 mb-7">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center shrink-0">
                            <app-icon name="calendar" class-name="w-5 h-5"></app-icon>
                        </div>
                        <div class="min-w-0">
                            <div class="text-[13px] font-black text-slate-800">@{{ formatEventDate(event.event_date) }}</div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">活動時間</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center shrink-0">
                            <app-icon name="map-pin" class-name="w-5 h-5"></app-icon>
                        </div>
                        <div class="min-w-0">
                            <div class="text-[13px] font-black text-slate-800 truncate">
                                @{{ event.region ? event.region + ' · ' : '' }}@{{ event.location }}
                            </div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">區域地點</div>
                        </div>
                    </div>
                </div>

                {{-- Interaction & Fee --}}
                <div class="mt-auto pt-6 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <template v-if="event.is_organizer">
                            <div class="flex items-center gap-2">
                                <button @click.stop="editEvent(event)" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-[11px] font-black uppercase tracking-widest hover:bg-blue-700 transition-all shadow-lg shadow-blue-100 flex items-center gap-1.5">
                                    <app-icon name="edit" class-name="w-3.5 h-3.5"></app-icon>
                                    編輯
                                </button>
                                <button @click.stop="deleteEvent(event.id)" class="px-4 py-2 bg-white text-red-500 rounded-xl text-[11px] font-black uppercase tracking-widest border border-red-100 hover:bg-red-50 transition-all flex items-center gap-1.5">
                                    <app-icon name="trash" class-name="w-3.5 h-3.5"></app-icon>
                                    刪除
                                </button>
                            </div>
                        </template>
                        <template v-else-if="event.has_joined">
                            <button @click.stop="leaveEvent(event.id)" class="px-5 py-2.5 bg-red-50 text-red-500 rounded-xl text-[11px] font-black uppercase tracking-widest border border-red-100 hover:bg-red-500 hover:text-white transition-all shadow-lg shadow-red-100/50 flex items-center gap-2">
                                <app-icon name="x" class-name="w-3.5 h-3.5"></app-icon>
                                取消報名
                            </button>
                        </template>
                        <template v-else>
                            <button @click.stop="joinEvent(event.id)" class="px-5 py-2.5 bg-slate-900 text-white rounded-xl text-[11px] font-black uppercase tracking-widest hover:bg-blue-600 shadow-xl transition-all">
                                快速報名
                            </button>
                        </template>
                    </div>
                    <div class="text-right">
                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">費用</div>
                        <div class="text-base font-black italic tracking-tighter text-slate-900">@{{ event.fee === 0 ? '免費' : '$' + event.fee }}</div>
                    </div>
                </div>

                {{-- Footer: Organizer --}}
                <div class="mt-6 pt-5 border-t border-slate-50 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl overflow-hidden bg-slate-100 border border-slate-100">
                            <img v-if="event.player?.photo" :src="event.player.photo_url || event.player.photo" class="w-full h-full object-cover">
                            <app-icon v-else name="user" class-name="w-full h-full text-slate-300 p-1"></app-icon>
                        </div>
                        <span class="text-xs font-black text-slate-600 truncate max-w-[100px]">@{{ event.player?.name || '主辦人' }}</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-1.5 text-slate-400">
                            <app-icon name="message-circle" class-name="w-3.5 h-3.5"></app-icon>
                            <span class="text-[10px] font-black">@{{ eventComments[event.id]?.length || 0 }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-slate-400">
                            <app-icon name="users" class-name="w-3.5 h-3.5"></app-icon>
                            <span class="text-[10px] font-black">@{{ event.participants_count || 1 }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pagination --}}
        <div v-if="eventTotalPages > 1" class="flex items-center justify-center gap-2 pt-12">
            {{-- Previous Button --}}
            <button type="button" @click="eventCurrentPage = Math.max(1, eventsPagination.current_page - 1)" :disabled="eventsPagination.current_page === 1"
                :class="['w-10 h-10 rounded-xl font-black text-sm transition-all', eventsPagination.current_page === 1 ? 'bg-slate-100 text-slate-300 cursor-not-allowed' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50']">
                ←
            </button>
            
            {{-- Page Numbers --}}
            <div v-for="(page, idx) in eventDisplayPages" :key="'ep-' + idx" class="inline-flex">
                <span v-if="page === '...'" class="w-10 h-10 flex items-center justify-center text-slate-400">...</span>
                <button v-else type="button" @click="eventCurrentPage = page"
                    :class="['w-10 h-10 rounded-xl font-black text-sm transition-all', eventsPagination.current_page === page ? 'bg-blue-600 text-white shadow-lg' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50']">
                    @{{ page }}
                </button>
            </div>
            
            {{-- Next Button --}}
            <button type="button" @click="eventCurrentPage = Math.min(eventTotalPages, eventsPagination.current_page + 1)" :disabled="eventsPagination.current_page === eventTotalPages"
                :class="['w-10 h-10 rounded-xl font-black text-sm transition-all', eventsPagination.current_page === eventTotalPages ? 'bg-slate-100 text-slate-300 cursor-not-allowed' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50']">
                →
            </button>
        </div>
    </div>

    {{-- Empty State --}}
    <div v-else class="text-center py-20">
        <div class="w-24 h-24 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-3xl flex items-center justify-center mx-auto mb-6 border border-dashed border-blue-200">
            <app-icon name="calendar" class-name="w-12 h-12 text-blue-300"></app-icon>
        </div>
        <h3 class="text-2xl font-black uppercase tracking-tight mb-2">目前沒有活動</h3>
        <p class="text-slate-500 font-medium mb-6">成為第一個開團的人吧！</p>
        <button v-if="isLoggedIn" @click="navigateTo('create-event')" class="bg-gradient-to-r from-blue-600 to-indigo-500 text-white px-7 py-3 rounded-2xl font-black text-sm uppercase tracking-widest shadow-xl hover:-translate-y-0.5 hover:shadow-2xl transition-all whitespace-nowrap">
            <app-icon name="calendar-plus" class-name="w-5 h-5 inline mr-2"></app-icon>
            開揪
        </button>
    </div>
</div>
