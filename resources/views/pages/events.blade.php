{{-- Events List View --}}
<div v-if="view === 'events'" class="space-y-8 pb-24">
    {{-- SEO Content for Crawlers (SSR) --}}
    <div class="sr-only" aria-hidden="true">
        @foreach($initialEvents ?? [] as $e)
            <h3>{{ $e->title }} - {{ $e->event_date }} ({{ $e->location }})</h3>
            <p>{{ $e->notes }}</p>
        @endforeach
    </div>
    {{-- Hero --}}
    <div class="relative overflow-hidden rounded-[32px] bg-gradient-to-br from-blue-700 via-indigo-600 to-blue-600 text-white p-6 sm:p-10 shadow-2xl">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute -left-10 -top-10 w-64 h-64 bg-white/10 rounded-full blur-3xl opacity-50"></div>
            <div class="absolute right-0 bottom-0 w-80 h-80 bg-blue-300/10 rounded-full blur-2xl opacity-30"></div>
        </div>
        <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-8">
            <div class="flex-1 space-y-4">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-[10px] font-black uppercase tracking-[0.2em]">熱度上升中</span>
                    <span class="inline-flex items-center px-3 py-1 bg-blue-400/30 backdrop-blur-md rounded-full text-[10px] font-black uppercase tracking-[0.15em]">多人開團</span>
                </div>
                <div>
                    <h2 class="text-4xl sm:text-6xl font-black italic uppercase tracking-tighter leading-[0.9] mb-2">開團揪球</h2>
                    <p class="text-blue-100 font-bold text-xs sm:text-lg uppercase tracking-[0.2em] opacity-80">Join or Create Tennis Events</p>
                </div>
                <div class="flex flex-wrap items-center gap-3 pt-2">
                    <button v-if="isLoggedIn && hasPlayerCard" @click="navigateTo('create-event')" class="bg-white text-blue-700 px-6 py-4 rounded-2xl font-black uppercase tracking-widest text-xs shadow-2xl hover:-translate-y-1 hover:bg-blue-50 transition-all flex items-center gap-2">
                        <app-icon name="calendar-plus" class-name="w-5 h-5"></app-icon>
                        建立新活動
                    </button>
                    <div v-else-if="isLoggedIn && !hasPlayerCard" class="bg-white/10 backdrop-blur-md border border-white/30 rounded-2xl px-5 py-4 text-sm font-bold">
                        尚未建立球友卡，<button @click="navigateTo('create')" class="underline font-black hover:text-white transition-colors">立即製作</button>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-2 md:flex gap-3 text-center shrink-0">
                <div class="backdrop-blur-md bg-white/15 rounded-[24px] px-6 py-4 border border-white/20 shadow-xl flex-1 md:min-w-[120px]">
                    <div class="text-4xl sm:text-5xl font-black italic tracking-tighter">@{{ events.length }}</div>
                    <div class="text-[10px] font-black uppercase tracking-widest text-blue-100 mt-1 opacity-60">場活動</div>
                </div>
                <div class="backdrop-blur-md bg-blue-900/20 rounded-[24px] px-6 py-4 border border-white/10 shadow-xl flex-1 md:min-w-[120px]">
                    <div class="text-4xl sm:text-5xl font-black italic tracking-tighter">@{{ filteredEvents.length }}</div>
                    <div class="text-[10px] font-black uppercase tracking-widest text-blue-100 mt-1 opacity-60">篩選中</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Search & Filter Section --}}
    <div class="space-y-4 mb-8">
        {{-- Search Bar --}}
        <div class="relative group">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-blue-500 transition-colors">
                <app-icon name="search" class-name="w-5 h-5"></app-icon>
            </div>
            <input v-model="eventSearchQuery" type="text" 
                placeholder="搜尋標題、球場或地點..." 
                class="w-full bg-white border-2 border-slate-100 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 rounded-2xl py-3.5 pl-11 pr-4 text-slate-700 placeholder-slate-400 transition-all font-medium">
        </div>

        <div class="flex flex-col sm:flex-row gap-4">
            {{-- Region Selection --}}
            <div class="relative shrink-0 sm:w-48">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                    <app-icon name="map-pin" class-name="w-4 h-4"></app-icon>
                </div>
                <select v-model="eventRegionFilter" 
                    class="w-full bg-white border-2 border-slate-100 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 rounded-2xl py-2.5 pl-10 pr-10 text-sm font-black text-slate-700 appearance-none transition-all">
                    <option value="all">全部地區</option>
                    <option v-for="region in regions" :key="region" :value="region">@{{ region }}</option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-400">
                    <app-icon name="chevron-down" class-name="w-4 h-4"></app-icon>
                </div>
            </div>

            {{-- Filter Tabs --}}
            <div class="flex gap-2.5 overflow-x-auto no-scrollbar py-1 flex-1">
                <button v-for="(label, key) in {all: '🌐 全部', doubles: '🎾 雙打', singles: '🏸 單打', mixed: '👫 混雙'}" 
                    @click="eventFilter = key" 
                    :class="['pill-tab shrink-0 whitespace-nowrap px-6 py-2.5 rounded-2xl font-black text-sm transition-all border-2', eventFilter === key ? 'bg-slate-900 border-slate-900 text-white shadow-lg shadow-slate-200' : 'bg-white border-slate-100 text-slate-400 hover:border-slate-200']">
                    @{{ label }}
                </button>
            </div>
        </div>
    </div>

    {{-- Loading State --}}
    <div v-if="eventsLoading" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div v-for="n in 4" :key="n" class="bg-white rounded-3xl p-6 space-y-4 skeleton-shimmer">
            <div class="h-6 bg-slate-200 rounded-lg w-2/3"></div>
            <div class="h-4 bg-slate-200 rounded-lg w-1/2"></div>
            <div class="h-20 bg-slate-200 rounded-xl"></div>
        </div>
    </div>

    {{-- Events Grid --}}
    <div v-else-if="filteredEvents.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div v-for="event in filteredEvents" :key="event.id" 
            @click="openEventDetail(event)"
            class="bg-white rounded-[32px] p-5 sm:p-7 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border-[1.5px] border-slate-200 hover:shadow-[0_20px_50px_-12px_rgba(37,99,235,0.15)] hover:border-blue-500 transition-all cursor-pointer group relative overflow-hidden flex flex-col">
            
            {{-- Status & Capacity --}}
            <div class="flex items-center justify-between mb-5">
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
                    <button @click.stop="joinEvent(event.id)" class="px-5 py-2.5 bg-slate-900 text-white rounded-xl text-[11px] font-black uppercase tracking-widest hover:bg-blue-600 shadow-xl transition-all">
                        快速報名
                    </button>
                    <button @click.stop="toggleEventLike(event.id)" :class="['p-2.5 rounded-xl transition-all border', eventLikes[event.id] ? 'bg-pink-50 border-pink-100 text-pink-500 shadow-sm' : 'bg-white border-slate-100 text-slate-300 hover:border-pink-200 group-hover:text-slate-400']">
                        <app-icon name="heart" class-name="w-5 h-5"></app-icon>
                    </button>
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

    {{-- Empty State --}}
    <div v-else class="text-center py-20">
        <div class="w-24 h-24 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-3xl flex items-center justify-center mx-auto mb-6 border border-dashed border-blue-200">
            <app-icon name="calendar" class-name="w-12 h-12 text-blue-300"></app-icon>
        </div>
        <h3 class="text-2xl font-black uppercase tracking-tight mb-2">目前沒有活動</h3>
        <p class="text-slate-500 font-medium mb-6">成為第一個開團的人吧！</p>
        <button v-if="isLoggedIn" @click="navigateTo('create-event')" class="bg-gradient-to-r from-blue-600 to-indigo-500 text-white px-7 py-3 rounded-2xl font-black text-sm uppercase tracking-widest shadow-xl hover:-translate-y-0.5 hover:shadow-2xl transition-all">
            <app-icon name="calendar-plus" class-name="w-5 h-5 inline mr-2"></app-icon>
            建立活動
        </button>
    </div>
</div>
