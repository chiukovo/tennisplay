{{-- Modal Templates --}}

{{-- Player Detail Modal --}}
<script type="text/x-template" id="player-detail-modal-template">
    <transition name="modal">
        <div v-if="player" class="fixed inset-0 z-[200] flex items-center justify-center p-2 sm:p-10 premium-blur modal-content" @click.self="$emit('close')">
            <div class="bg-white w-full max-w-5xl h-full sm:h-auto max-h-[96vh] sm:max-h-[92vh] rounded-[32px] sm:rounded-[48px] overflow-hidden shadow-[0_0_100px_rgba(0,0,0,0.5)] flex flex-col md:flex-row relative">
                {{-- Close Button --}}
                <button type="button" @click="$emit('close')" class="absolute top-4 right-4 sm:top-8 sm:right-8 z-[120] p-2.5 bg-white/90 backdrop-blur-md hover:bg-red-50 hover:text-red-500 rounded-full shadow-xl transition-all border border-slate-100 group">
                    <app-icon name="x" class-name="w-5 h-5 group-hover:scale-110 transition-transform"></app-icon>
                </button>

                {{-- Page Counter --}}
                <div v-if="players && players.length > 1" class="absolute top-4 left-4 md:top-8 md:left-1/2 md:-translate-x-1/2 z-[120] px-3 py-1.5 md:px-4 md:py-2 bg-slate-900/10 backdrop-blur-md rounded-full border border-slate-200/50 flex items-center gap-2 md:gap-3">
                    <span class="text-[8px] md:text-[10px] font-black text-slate-400 uppercase tracking-widest">球友</span>
                    <span class="text-xs md:text-sm font-black italic text-slate-900">@{{ currentIndex + 1 }} / @{{ players.length }}</span>
                </div>

                <div class="hidden md:block">
                    <button v-if="hasPrev" @click="navigate(-1)" class="absolute left-6 top-1/2 -translate-y-1/2 z-[120] w-14 h-14 bg-blue-600 hover:bg-blue-500 text-white rounded-full shadow-xl transition-all border-4 border-white/30 group flex items-center justify-center nav-pulse">
                        <app-icon name="arrow-left" class-name="w-6 h-6 group-hover:scale-110 transition-transform" stroke-width="4"></app-icon>
                    </button>
                    <button v-if="hasNext" @click="navigate(1)" class="absolute right-6 top-1/2 -translate-y-1/2 z-[120] w-14 h-14 bg-blue-600 hover:bg-blue-500 text-white rounded-full shadow-xl transition-all border-4 border-white/30 group flex items-center justify-center nav-pulse">
                        <app-icon name="arrow-right" class-name="w-6 h-6 group-hover:scale-110 transition-transform" stroke-width="4"></app-icon>
                    </button>
                </div>

                {{-- Main Scrollable Area (Mobile) / Split Layout (Desktop) --}}
                <transition :name="transitionName" mode="out-in">
                    <div :key="player.id" 
                        @touchstart="handleTouchStart" 
                        @touchend="handleTouchEnd"
                        class="flex-1 flex flex-col md:flex-row overflow-y-auto md:overflow-hidden no-scrollbar pb-24 md:pb-0">
                        {{-- Left: Card Display --}}
                        <div class="w-full md:w-1/2 p-6 sm:p-10 flex items-center justify-center bg-slate-50 border-r border-slate-100 shrink-0 relative min-h-[400px] sm:min-h-0">
                            <div class="w-full max-w-[260px] sm:max-w-[340px]">
                                <player-card :player="player" />
                                
                                {{-- Floating Navigation (Mobile Only) - Hidden as requested --}}
                                <div class="hidden absolute inset-y-0 -left-4 -right-4 flex items-center justify-between pointer-events-none z-[90]">
                                    <button v-if="hasPrev" @click.stop="navigate(-1)" class="w-14 h-14 bg-blue-600 text-white rounded-full flex items-center justify-center shadow-[0_10px_30px_rgba(37,99,235,0.4)] border-2 border-white/50 pointer-events-auto active:scale-90 transition-all nav-pulse">
                                        <app-icon name="arrow-left" class-name="w-7 h-7" stroke-width="3.5"></app-icon>
                                    </button>
                                    <button v-if="hasNext" @click.stop="navigate(1)" class="w-14 h-14 bg-blue-600 text-white rounded-full flex items-center justify-center shadow-[0_10px_30px_rgba(37,99,235,0.4)] border-2 border-white/50 pointer-events-auto active:scale-90 transition-all nav-pulse">
                                        <app-icon name="arrow-right" class-name="w-7 h-7" stroke-width="3.5"></app-icon>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Right: Detailed Stats --}}
                        <div class="w-full md:w-1/2 p-8 sm:p-14 md:overflow-y-auto bg-white flex flex-col no-scrollbar">
                            <div class="mb-6">
                                <h3 class="text-4xl sm:text-5xl font-black italic uppercase tracking-tighter text-slate-900 leading-tight mb-4">@{{player.name}}</h3>
                                
                                <div class="flex flex-nowrap items-center gap-2 overflow-x-auto no-scrollbar">
                                    <span class="px-2 py-1 bg-blue-600 text-white text-[8px] font-black rounded-lg uppercase tracking-widest italic shrink-0">已認證</span>
                                    <div class="flex items-center gap-1 px-2 py-1 bg-slate-100 rounded-lg text-[10px] font-bold text-slate-600 shrink-0">
                                        <app-icon name="map-pin" class-name="w-3 h-3 text-slate-400"></app-icon>
                                        @{{player.region}}
                                    </div>
                                    <div class="flex items-center gap-1 px-2 py-1 bg-blue-50 rounded-lg text-[10px] font-black text-blue-600 italic shrink-0">
                                        <app-icon name="zap" class-name="w-3 h-3"></app-icon>
                                        NTRP @{{player.level}}
                                        <button @click.stop="$emit('open-ntrp-guide')" class="ml-1 text-blue-400 hover:text-blue-600 transition-colors">
                                            <app-icon name="help" class-name="w-3 h-3"></app-icon>
                                        </button>
                                    </div>
                                    <div class="flex items-center gap-1 px-2 py-1 bg-slate-100 rounded-lg text-[10px] font-bold text-slate-600 shrink-0">
                                        <app-icon name="gender" class-name="w-3 h-3 text-slate-400"></app-icon>
                                        @{{player.gender}}
                                    </div>
                                    <div class="flex items-center gap-1 px-2 py-1 bg-slate-100 rounded-lg text-[10px] font-bold text-slate-600 shrink-0">
                                        @{{player.handed || '右手'}}
                                    </div>
                                    <div class="flex items-center gap-1 px-2 py-1 bg-slate-100 rounded-lg text-[10px] font-bold text-slate-600 shrink-0">
                                        @{{player.backhand || '雙反'}}
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-6">
                                
                                {{-- Intro Section --}}
                                <div class="bg-slate-50 p-6 rounded-[24px] border border-slate-100 relative overflow-hidden mb-6">
                                    <div class="flex items-center gap-2 mb-3">
                                        <div class="w-1 h-3 bg-blue-600 rounded-full"></div>
                                        <span class="text-[10px] font-black uppercase tracking-[0.1em] text-slate-400 italic">個人特色 / 約打宣告</span>
                                    </div>
                                    <p class="text-base text-slate-700 font-bold leading-relaxed italic whitespace-pre-line relative z-10">
                                        「@{{player.intro || '這位球友很懶，什麼都沒留下... 希望能找到實力相當的球友進行約打與練習。'}}」
                                    </p>
                                </div>

                                {{-- Social Action Bar --}}
                                {{-- Action Dashboard (Desktop Only) --}}
                                <div class="hidden md:block mt-8 rounded-[28px] border-2 border-slate-100 overflow-hidden shadow-sm bg-white">
                                    <div class="flex divide-x divide-slate-100">
                                        <button type="button" @click="$emit('open-match', player)" 
                                                class="flex-1 py-5 flex flex-col items-center justify-center gap-2 bg-blue-600 text-white hover:bg-blue-500 transition-all group">
                                            <app-icon name="message-circle" class-name="w-5 h-5 group-hover:animate-bounce"></app-icon>
                                            <span class="text-[11px] font-black uppercase tracking-widest">約打</span>
                                        </button>
                                        <button type="button" @click="toggleFollowModal" 
                                                :class="socialStatus.is_following ? 'bg-emerald-500 text-white' : 'hover:bg-slate-50 text-slate-400'"
                                                class="flex-1 py-5 flex flex-col items-center justify-center gap-2 transition-all group">
                                            <app-icon :name="socialStatus.is_following ? 'check' : 'plus'" 
                                                     :class-name="['w-5 h-5 transition-all', socialStatus.is_following ? 'text-white scale-110' : 'text-slate-400 group-hover:text-slate-900 group-hover:scale-110']"></app-icon>
                                            <span class="text-[11px] font-black uppercase tracking-widest transition-colors"
                                                  :class="socialStatus.is_following ? 'text-white' : 'text-slate-400 group-hover:text-slate-900'">
                                                @{{ socialStatus.is_following ? '已追蹤' : '追蹤' }}
                                            </span>
                                        </button>
                                        <button type="button" @click="$emit('open-profile', player.user?.uid || player.user_id)" 
                                                class="flex-1 py-5 flex flex-col items-center justify-center gap-2 hover:bg-slate-50 transition-all group">
                                            <app-icon name="user" class-name="w-5 h-5 text-slate-400 group-hover:text-slate-900 group-hover:scale-110 transition-all"></app-icon>
                                            <span class="text-[11px] font-black uppercase tracking-widest text-slate-400 group-hover:text-slate-900 transition-colors">主頁</span>
                                        </button>
                                        <button type="button" @click="toggleLikeModal" 
                                                :class="socialStatus.is_liked ? 'bg-red-500 text-white' : 'hover:bg-slate-50 text-slate-400'"
                                                class="flex-1 py-5 flex flex-col items-center justify-center gap-2 transition-all group">
                                            <app-icon name="heart" 
                                                     :class-name="['w-5 h-5 transition-all', socialStatus.is_liked ? 'text-white fill-current scale-110' : 'text-slate-400 group-hover:text-red-500 group-hover:scale-110']"></app-icon>
                                            <span class="text-[11px] font-black uppercase tracking-widest transition-colors"
                                                  :class="socialStatus.is_liked ? 'text-white' : 'text-slate-400 group-hover:text-red-600'">
                                                @{{ socialStatus.likes_count || '讚' }}
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Comments Section --}}
                            <div class="mt-10 border-t border-slate-50 pt-8">
                                <div class="flex items-center justify-between mb-6">
                                    <div class="flex items-center gap-2">
                                        <div class="w-1 h-3 bg-blue-600 rounded-full"></div>
                                        <span class="text-[10px] font-black uppercase tracking-[0.1em] text-slate-400 italic">互動留言 / Comments</span>
                                    </div>
                                    <span class="text-[9px] font-black text-slate-300">@{{ comments.length }} COMMENTS</span>
                                </div>

                                <!-- Combined Comment Unit -->
                                <div class="bg-slate-50/50 rounded-[32px] p-6 border border-slate-100">
                                    <!-- Comment Input -->
                                    <div class="relative mb-6">
                                        <textarea v-model="commentDraft" 
                                               rows="1"
                                               @keyup.enter.prevent="postComment"
                                               placeholder="留個言打聲招呼吧..." 
                                               class="w-full bg-white border-2 border-transparent rounded-[20px] px-5 py-4 pr-16 text-sm font-bold focus:border-blue-500 outline-none transition-all shadow-sm shadow-blue-500/5 placeholder:text-slate-300 resize-none overflow-hidden"></textarea>
                                        <button @click="postComment" :disabled="!commentDraft.trim()" class="absolute right-2 top-2 bottom-2 w-12 bg-blue-600 text-white rounded-[14px] shadow-lg shadow-blue-500/20 active:scale-95 transition-all disabled:opacity-30 flex items-center justify-center">
                                            <app-icon name="send" class-name="w-5 h-5"></app-icon>
                                        </button>
                                    </div>

                                    <!-- Scrollable Comment List -->
                                    <div class="max-h-[400px] overflow-y-auto pr-2 space-y-4 scrollbar-thin">
                                        <div v-if="comments.length > 0" class="space-y-4">
                                            <div v-for="c in comments" :key="c.id" class="flex gap-3 group">
                                                <div class="w-10 h-10 rounded-2xl overflow-hidden bg-white border border-slate-100 shrink-0 shadow-sm cursor-pointer" @click="$emit('open-profile', c.user.uid)">
                                                    <img v-if="c.user.photo" :src="c.user.photo" class="w-full h-full object-cover">
                                                    <app-icon v-else name="user" class-name="w-full h-full text-slate-200 p-2"></app-icon>
                                                </div>
                                                <div class="flex-1 space-y-1">
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-[11px] font-black text-slate-900 cursor-pointer hover:text-blue-600" @click="$emit('open-profile', c.user.uid)">@{{ c.user.name }}</span>
                                                        <span class="text-[9px] font-black text-slate-300 tracking-tighter">@{{ formatDate(c.at) }}</span>
                                                    </div>
                                                    <div class="bg-white/80 p-4 rounded-[22px] rounded-tl-none text-slate-700 text-xs font-bold leading-relaxed shadow-sm">
                                                        @{{ c.text }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else class="py-10 text-center">
                                            <p class="text-slate-300 font-black italic text-xs uppercase tracking-widest">目前還沒有留言...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </transition>

                {{-- Mobile Sticky Action Bar --}}
                <div class="md:hidden absolute bottom-0 left-0 right-0 p-4 pb-8 bg-white/95 backdrop-blur-xl border-t border-slate-100 z-[110] shadow-[0_-15px_30px_rgba(0,0,0,0.08)]">
                    <div class="flex gap-2">
                        <button type="button" @click="$emit('open-match', player)" 
                                class="flex-1 py-3.5 bg-blue-600 text-white rounded-2xl font-black uppercase tracking-widest text-[10px] flex flex-col items-center justify-center gap-1 active:scale-95 transition-all">
                            <app-icon name="message-circle" class-name="w-4 h-4"></app-icon>
                            約打
                        </button>
                        <button type="button" @click="toggleFollowModal" 
                                :class="socialStatus.is_following ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-200' : 'bg-slate-50 text-slate-600'"
                                class="flex-1 py-3.5 rounded-2xl font-black uppercase tracking-widest text-[10px] transition-all flex flex-col items-center justify-center gap-1 active:scale-95">
                                <app-icon :name="socialStatus.is_following ? 'check' : 'plus'" :class-name="['w-4 h-4', socialStatus.is_following ? 'text-white' : '']"></app-icon>
                                @{{ socialStatus.is_following ? '已追蹤' : '追蹤' }}
                        </button>
                        <button type="button" @click="$emit('open-profile', player.user?.uid || player.user_id)" 
                                class="flex-1 bg-slate-50 text-slate-600 py-3.5 rounded-2xl font-black uppercase tracking-widest text-[10px] flex flex-col items-center justify-center gap-1 active:scale-95 transition-all">
                            <app-icon name="user" class-name="w-4 h-4"></app-icon>
                            主頁
                        </button>
                        <button type="button" @click="toggleLikeModal"
                                :class="socialStatus.is_liked ? 'bg-red-500 text-white shadow-lg shadow-red-200' : 'bg-slate-50 text-slate-600'"
                                class="flex-1 py-3.5 rounded-2xl font-black uppercase tracking-widest text-[10px] transition-all flex flex-col items-center justify-center gap-1 active:scale-95">
                            <app-icon name="heart" :class-name="['w-4 h-4', socialStatus.is_liked ? 'fill-current text-white' : '']"></app-icon>
                            @{{ socialStatus.likes_count || '讚' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</script>

@include('components.message-detail-modal')

{{-- Event Detail Modal --}}
<script type="text/x-template" id="event-detail-modal-template">
    <transition name="modal">
        <div v-if="open && event" class="fixed inset-0 z-[320] flex items-center justify-center p-0 sm:p-6 bg-slate-950/80 backdrop-blur-md modal-content" @click.self="$emit('update:open', false)">
            <div class="bg-slate-50 w-full h-full sm:h-auto max-w-2xl sm:rounded-[48px] overflow-hidden shadow-[0_32px_128px_-16px_rgba(0,0,0,0.5)] flex flex-col max-h-[100vh] sm:max-h-[95vh] animate__animated animate__zoomIn animate__faster">
                
                {{-- Dynamic Header Section --}}
                <div class="relative shrink-0">
                    <div class="h-48 sm:h-64 bg-slate-900 relative overflow-hidden">
                        {{-- Abstract Background --}}
                        <div class="absolute inset-0 opacity-40">
                            <div class="absolute -right-20 -top-20 w-80 h-80 bg-blue-600 rounded-full blur-[100px]"></div>
                            <div class="absolute -left-20 -bottom-20 w-80 h-80 bg-indigo-600 rounded-full blur-[100px]"></div>
                        </div>
                        {{-- Info Overlay --}}
                        <div class="absolute inset-0 flex flex-col justify-end p-8 sm:p-12 bg-gradient-to-t from-slate-900 via-slate-900/40 to-transparent">
                            <div class="flex items-center gap-2 mb-4">
                                <span :class="['px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest shadow-lg', 
                                    event.status === 'open' ? 'bg-green-500 text-white' : 
                                    event.status === 'full' ? 'bg-amber-500 text-white' : 'bg-slate-600 text-white']">
                                    @{{ event.status === 'open' ? '招募中' : event.status === 'full' ? '已滿' : '已結束' }}
                                </span>
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-white/20 backdrop-blur text-white border border-white/10">
                                    @{{ event.match_type === 'all' ? '不限賽制' : event.match_type === 'singles' ? '單打' : event.match_type === 'doubles' ? '雙打' : '混雙' }}
                                </span>
                            </div>
                            <h3 class="text-3xl sm:text-5xl font-black text-white leading-[0.9] uppercase tracking-tighter mb-2">@{{ event.title }}</h3>
                            <div class="flex items-center gap-4 text-slate-300">
                                <div class="flex items-center gap-1.5 font-bold text-xs uppercase tracking-wider">
                                    <app-icon name="map-pin" class-name="w-4 h-4 text-blue-400"></app-icon>
                                    @{{ event.region }} · @{{ event.location }}
                                </div>
                                <div class="flex items-center gap-1.5 font-bold text-xs uppercase tracking-wider">
                                    <app-icon name="users" class-name="w-4 h-4 text-indigo-400"></app-icon>
                                    已徵 @{{ event.participants_count || 1 }} 位球友
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="button" @click="$emit('update:open', false)" class="absolute top-6 right-6 z-[130] w-12 h-12 flex items-center justify-center bg-black/20 hover:bg-black/40 backdrop-blur-xl text-white rounded-2xl transition-all border border-white/10 group">
                        <app-icon name="x" class-name="w-6 h-6 transition-transform group-hover:rotate-90"></app-icon>
                    </button>
                </div>

                {{-- Scrollable Content Area --}}
                <div class="flex-1 overflow-y-auto no-scrollbar bg-slate-50">
                    <div class="p-6 sm:p-10 space-y-8 pb-32">
                        
                        {{-- Host Section --}}
                        <div class="flex items-center justify-between bg-white p-6 rounded-[32px] border border-slate-100 shadow-sm hover:border-blue-200 transition-all cursor-pointer" @click="$emit('open-profile', event.user?.uid || event.user_id)">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 rounded-2xl overflow-hidden bg-slate-100 border-2 border-slate-50 shadow-md">
                                    <img v-if="event.player?.photo" :src="event.player.photo_url || event.player.photo" class="w-full h-full object-cover">
                                    <app-icon v-else name="user" class-name="w-full h-full text-slate-300 p-2"></app-icon>
                                </div>
                                <div>
                                    <div class="text-slate-400 text-[10px] font-black uppercase tracking-widest leading-none mb-1">主辦人</div>
                                    <div class="text-slate-900 font-black text-lg">@{{ event.player?.name || '主辦人' }}</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-slate-400 text-[10px] font-black uppercase tracking-widest leading-none mb-1">費用 / 人</div>
                                <div class="text-2xl font-black italic tracking-tighter text-blue-600 leading-none">@{{ event.fee === 0 ? '全台免費' : '$' + event.fee }}</div>
                            </div>
                        </div>

                        {{-- Details Grid --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-sm">
                                <div class="flex items-center gap-3 mb-4 text-blue-600">
                                    <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center">
                                        <app-icon name="calendar" class-name="w-5 h-5"></app-icon>
                                    </div>
                                    <span class="font-black uppercase tracking-widest text-xs">活動時間資訊</span>
                                </div>
                                <div class="space-y-3">
                                    <div>
                                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">開始</div>
                                        <div class="font-black text-slate-800">@{{ formatEventDate(event.event_date) }}</div>
                                    </div>
                                    <div v-if="event.end_date">
                                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">結束</div>
                                        <div class="font-black text-slate-800 opacity-60">@{{ formatEventDate(event.end_date) }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-sm flex flex-col">
                                <div class="flex items-center gap-3 mb-4 text-indigo-600">
                                    <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center">
                                        <app-icon name="users" class-name="w-5 h-5"></app-icon>
                                    </div>
                                    <span class="font-black uppercase tracking-widest text-xs">參與狀況</span>
                                </div>
                                <div class="mt-auto">
                                    <div class="flex items-end justify-between mb-2">
                                        <div class="text-3xl font-black italic text-slate-900 leading-none">
                                            @{{ event.confirmed_participants?.length || 1 }} <span class="text-sm font-bold opacity-30">/ @{{ event.max_participants === 0 ? '∞' : event.max_participants }}</span>
                                        </div>
                                        <div class="text-[10px] font-black text-slate-400 tracking-widest uppercase">
                                            剩餘 @{{ event.max_participants === 0 ? '∞' : event.spots_left }}
                                        </div>
                                    </div>
                                    {{-- Participant Avatars --}}
                                    <div class="flex -space-x-3 overflow-hidden">
                                        <div v-for="p in event.confirmed_participants?.slice(0, 5)" :key="p.id" class="w-8 h-8 rounded-full border-2 border-white overflow-hidden bg-slate-100 shadow-sm shrink-0">
                                            <img v-if="p.player?.photo" :src="p.player.photo_url || p.player.photo" class="w-full h-full object-cover">
                                            <app-icon v-else name="user" class-name="w-full h-full text-slate-300 p-1.5"></app-icon>
                                        </div>
                                        <div v-if="event.participants_count > 5" class="w-8 h-8 rounded-full bg-slate-900 border-2 border-white flex items-center justify-center text-[10px] font-black text-white shrink-0">
                                            +@{{ event.participants_count - 5 }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Full-width Map/Address --}}
                        <div class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-sm">
                            <div class="flex items-center gap-3 mb-4 text-emerald-600">
                                <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center">
                                    <app-icon name="map" class-name="w-5 h-5"></app-icon>
                                </div>
                                <span class="font-black uppercase tracking-widest text-xs">集合地點</span>
                            </div>
                            <div class="text-slate-900 font-black text-lg mb-1 underline decoration-blue-500/30 decoration-4 underline-offset-4">@{{ event.address || event.location }}</div>
                            <div class="text-slate-400 font-bold text-sm">@{{ event.location }}</div>
                        </div>

                        {{-- Content / Notes --}}
                        <div class="bg-blue-600 text-white p-8 rounded-[40px] shadow-[0_20px_40px_-10px_rgba(37,99,235,0.4)] relative overflow-hidden" v-if="event.notes">
                            <div class="absolute right-[-10%] bottom-[-20%] w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
                            <div class="text-[10px] font-black uppercase tracking-[.3em] text-white/50 mb-3 block">
                                活動叮嚀 FROM HOST
                            </div>
                            <p class="text-xl sm:text-2xl font-black italic leading-tight whitespace-pre-line">
                                「@{{ event.notes }}」
                            </p>
                        </div>

                        {{-- Social interaction --}}
                        <div class="border-t border-slate-200 pt-10 space-y-8">
                            {{-- Comment Input --}}
                            <div class="relative">
                                <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3 ml-2">提問、留言、或打個招呼...</div>
                                <div class="flex gap-3">
                                    <div class="flex-1">
                                        <textarea :value="commentDraft" 
                                            @input="$emit('update:comment-draft', $event.target.value)"
                                            @keydown.enter.prevent="$emit('comment', event.id)"
                                            rows="1" placeholder="寫下你的訊息..." 
                                            class="w-full bg-white border-2 border-slate-100 rounded-[24px] px-6 py-4 text-sm font-bold focus:border-slate-900 focus:ring-0 outline-none transition-all placeholder:text-slate-300 min-h-[56px]"></textarea>
                                    </div>
                                    <button type="button" @click="$emit('comment', event.id)" class="bg-slate-900 hover:bg-blue-600 text-white px-8 rounded-[24px] font-black uppercase tracking-widest text-xs shadow-xl transition-all">
                                        發送
                                    </button>
                                </div>
                            </div>

                            {{-- Comments List --}}
                            <div class="space-y-4" v-if="comments[event.id]?.length">
                                <div v-for="c in comments[event.id]" :key="c.id" class="flex gap-3 group">
                                    <div class="w-10 h-10 rounded-xl bg-slate-200 shrink-0 overflow-hidden border border-white shadow-sm">
                                        <img v-if="c.user?.photo" :src="c.user?.photo" class="w-full h-full object-cover">
                                        <app-icon v-else name="user" class-name="w-full h-full text-slate-400 p-2"></app-icon>
                                    </div>
                                    <div class="flex-1 bg-white rounded-3xl p-5 border border-slate-100 shadow-sm relative group-hover:border-blue-100 transition-colors">
                                        <div class="flex items-center justify-between mb-1.5">
                                            <span class="text-xs font-black text-slate-800 cursor-pointer hover:text-blue-600 transition-colors" @click="openProfile(c.user?.uid || c.user_id)">@{{ c.user?.name || '匿名球友' }}</span>
                                            <div class="flex items-center gap-2">
                                                <button v-if="currentUser && c.user?.uid === currentUser.uid" 
                                                    @click="$emit('delete-comment', c.id, event.id)"
                                                    class="p-1 text-slate-400 hover:text-red-500 transition-colors">
                                                    <app-icon name="trash" class-name="w-3 h-3"></app-icon>
                                                </button>
                                                <span class="text-[9px] font-bold text-slate-400 uppercase">@{{ formatDate(c.at) }}</span>
                                            </div>
                                        </div>
                                        <p class="text-sm font-bold text-slate-600 leading-relaxed">@{{ c.text }}</p>
                                    </div>
                                </div>
                            </div>
                            <div v-else-if="!event.loading" class="text-center py-10 bg-white/30 rounded-[32px] border-2 border-dashed border-slate-200">
                                <p class="text-slate-400 font-bold text-sm">目前尚無問答，快來當第一個發問的人吧！</p>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Fixed Bottom Action Bar --}}
                <div class="shrink-0 p-6 bg-white/80 backdrop-blur-xl border-t border-slate-100 flex items-center gap-4 relative z-[10]">
                    <template v-if="event.is_organizer">
                        <div class="flex-1 h-16 bg-blue-50 text-blue-600 rounded-3xl font-black uppercase tracking-[0.2em] text-sm flex items-center justify-center gap-3 border border-blue-100">
                            <app-icon name="star" class-name="w-6 h-6"></app-icon>
                            您是此活動的主辦人
                        </div>
                    </template>
                    <template v-else-if="event.has_joined">
                        <button type="button" @click="$emit('leave', event.id)" 
                            class="flex-1 h-16 bg-red-50 hover:bg-red-500 hover:text-white active:scale-95 text-red-500 rounded-3xl font-black uppercase tracking-[0.2em] text-sm shadow-xl shadow-red-100/50 transition-all border border-red-100 flex items-center justify-center gap-3">
                            <app-icon name="x" class-name="w-6 h-6"></app-icon>
                            取消報名參加
                        </button>
                    </template>
                    <template v-else>
                        <button type="button" @click="$emit('join', event.id)" 
                            :disabled="event.status === 'completed' || event.status === 'cancelled'"
                            class="flex-1 h-16 bg-slate-900 hover:bg-blue-600 active:scale-95 text-white rounded-3xl font-black uppercase tracking-[0.2em] text-sm shadow-2xl transition-all flex items-center justify-center gap-3 disabled:opacity-50 disabled:cursor-not-allowed">
                            <app-icon name="calendar-plus" class-name="w-6 h-6"></app-icon>
                            <span v-if="event.status === 'completed'">活動已結束</span>
                            <span v-else-if="event.status === 'cancelled'">活動已取消</span>
                            <span v-else>立即報名參加 @{{ event.spots_left === 0 ? '(候補)' : '' }}</span>
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </transition>
</script>

{{-- Match Modal --}}
<script type="text/x-template" id="match-modal-template">
    <transition name="modal">
        <div v-if="open && player" class="fixed inset-0 z-[300] flex items-center justify-center p-4 bg-slate-900/90 backdrop-blur-md modal-content" @click.self="$emit('update:open', false)">
            <div class="bg-white w-full max-w-md rounded-[40px] overflow-hidden shadow-2xl">
                <div class="bg-slate-900 p-8 text-white flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <img v-if="photoUrl" :src="photoUrl" class="w-12 h-12 rounded-full border-2 border-blue-500 object-cover shadow-lg">
                        <div v-else class="w-12 h-12 rounded-full bg-slate-800 flex items-center justify-center border-2 border-slate-700">
                            <app-icon name="user" class-name="w-6 h-6 text-slate-500"></app-icon>
                        </div>
                        <div>
                            <h3 class="font-black italic uppercase text-xl italic tracking-tight">約打邀約信</h3>
                            <p class="text-[9px] font-bold text-blue-400 tracking-widest uppercase">To: @{{player.name}}</p>
                        </div>
                    </div>
                    <button type="button" @click="$emit('update:open', false)"><app-icon name="x" class-name="w-6 h-6 opacity-50"></app-icon></button>
                </div>
                <div class="p-8 space-y-6">
                    <div class="bg-blue-50 p-5 rounded-2xl border border-blue-100 flex gap-4 text-xs text-blue-800 font-bold uppercase leading-normal">
                        <app-icon name="shield-check" class-name="w-6 h-6 text-blue-600 shrink-0"></app-icon>
                        安全提示：LoveTennis 建議在公開且有監視設備的球場會面，祝您球技進步。
                    </div>
                    <textarea v-model="textModel" class="w-full h-40 p-5 bg-slate-50 border-2 border-transparent rounded-[28px] focus:border-blue-500 outline-none font-bold text-base leading-relaxed" 
                        :placeholder="'Hi ' + (player.name || '') + '，看到你的 LoveTennis 檔案後非常想跟你交流，請問... '"></textarea>
                    <button type="button" @click="$emit('submit', textModel)" class="w-full bg-slate-950 text-white py-5 rounded-3xl font-black uppercase tracking-[0.2em] hover:bg-blue-600 shadow-2xl transition-all text-lg">
                        發送站內訊息
                    </button>
                </div>
            </div>
        </div>
    </transition>
</script>

{{-- NTRP Guide Modal --}}
<script type="text/x-template" id="ntrp-guide-modal-template">
    <transition name="modal">
        <div v-if="open" class="fixed inset-0 z-[600] flex items-center justify-center p-4 sm:p-6 bg-slate-900/80 backdrop-blur-md modal-content" @click.self="$emit('update:open', false)">
            <div class="bg-slate-950 w-full max-w-2xl max-h-[85vh] rounded-[32px] overflow-hidden shadow-2xl border border-white/10 flex flex-col animate__animated animate__zoomIn animate__faster">
                <div class="px-6 py-5 flex items-center justify-between border-b border-white/10 shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="bg-blue-600 p-2 rounded-xl shadow-lg shadow-blue-600/20">
                            <app-icon name="help" class-name="w-5 h-5 text-white"></app-icon>
                        </div>
                        <h3 class="text-lg font-black italic uppercase tracking-wider text-white">NTRP 等級說明</h3>
                    </div>
                    <button type="button" @click="$emit('update:open', false)" class="p-2 bg-white/5 hover:bg-white/10 rounded-xl transition-all border border-white/10">
                        <app-icon name="x" class-name="w-5 h-5 text-white/50"></app-icon>
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto p-4 sm:p-6 no-scrollbar grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div v-for="(desc, lvl) in descs" :key="lvl" class="bg-white/5 border border-white/5 p-4 rounded-2xl hover:bg-blue-600/5 transition-colors group">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="bg-white text-slate-950 px-2 py-0.5 rounded-lg font-black italic text-sm">@{{lvl}}</span>
                            <div class="h-px flex-1 bg-white/10"></div>
                        </div>
                        <p class="text-slate-400 font-bold text-xs leading-relaxed">@{{desc}}</p>
                    </div>
                </div>
                <div class="p-5 border-t border-white/10 bg-slate-950/50 flex justify-center shrink-0">
                    <button type="button" @click="$emit('update:open', false)" class="w-full bg-blue-600 text-white py-4 rounded-2xl font-black uppercase tracking-widest hover:bg-blue-500 transition-all shadow-lg text-sm">
                        了解並返回
                    </button>
                </div>
            </div>
        </div>
    </transition>
</script>

{{-- Quick Edit Modal --}}
<script type="text/x-template" id="quick-edit-modal-template">
    <transition name="modal">
        <div v-if="open" class="fixed inset-0 z-[700] flex items-center justify-center p-4 bg-slate-900/90 backdrop-blur-md modal-content" @click.self="$emit('update:open', false)">
            <div class="bg-white w-full max-w-2xl rounded-[40px] overflow-hidden shadow-2xl flex flex-col max-h-[90vh] animate__animated animate__zoomIn animate__faster">
                <div class="bg-slate-900 p-6 text-white flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-4">
                        <div class="bg-blue-600 p-2 rounded-xl">
                            <app-icon name="edit-3" class-name="w-5 h-5 text-white"></app-icon>
                        </div>
                        <h3 class="font-black italic uppercase text-xl tracking-tight">快速修改資料</h3>
                    </div>
                    <button type="button" @click="$emit('update:open', false)" class="p-2 hover:bg-white/10 rounded-full transition-all">
                        <app-icon name="x" class-name="w-6 h-6 opacity-50"></app-icon>
                    </button>
                </div>
                
                <div class="p-8 overflow-y-auto no-scrollbar space-y-8">
                    {{-- Name & Gender --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="space-y-3">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">選手姓名</label>
                            <input type="text" v-model="form.name" class="w-full px-5 py-3.5 bg-slate-50 border-2 border-transparent rounded-2xl focus:border-blue-500 outline-none font-black italic text-base transition-all">
                        </div>
                        <div class="space-y-3">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">生理性別</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button v-for="g in ['男', '女']" :key="g" type="button" @click="form.gender = g" 
                                    :class="['py-3 rounded-xl font-black text-xs transition-all border-2', form.gender === g ? 'bg-slate-900 text-white border-slate-900 shadow-lg' : 'bg-slate-50 text-slate-400 border-transparent hover:border-slate-200']">
                                    @{{ g }}
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- NTRP Level --}}
                    <div class="space-y-3">
                        <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">NTRP 程度</label>
                        <div class="grid grid-cols-4 sm:grid-cols-6 gap-2">
                            <button v-for="l in levels" :key="l" type="button" @click="form.level = l"
                                :class="['py-2.5 rounded-xl font-black text-[10px] transition-all border-2', form.level === l ? 'bg-blue-600 text-white border-blue-600 shadow-md' : 'bg-slate-50 text-slate-400 border-transparent hover:border-slate-200']">
                                @{{l}}
                            </button>
                        </div>
                    </div>

                    {{-- Handed & Backhand --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="space-y-3">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">持拍手</label>
                            <div class="flex gap-2">
                                <button type="button" v-for="h in ['右手', '左手']" :key="h" @click="form.handed = h"
                                    :class="['flex-1 py-3 rounded-xl font-black text-xs transition-all border-2', form.handed === h ? 'bg-slate-900 text-white border-slate-900 shadow-lg' : 'bg-slate-50 text-slate-400 border-transparent hover:border-slate-200']">
                                    @{{h}}
                                </button>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">反手類型</label>
                            <div class="flex gap-2">
                                <button type="button" v-for="b in ['單反', '雙反']" :key="b" @click="form.backhand = b"
                                    :class="['flex-1 py-3 rounded-xl font-black text-xs transition-all border-2', form.backhand === b ? 'bg-slate-900 text-white border-slate-900 shadow-lg' : 'bg-slate-50 text-slate-400 border-transparent hover:border-slate-200']">
                                    @{{b}}
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Region --}}
                    <div class="space-y-3">
                        <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">活動地區</label>
                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 max-h-[150px] overflow-y-auto no-scrollbar p-1">
                            <button v-for="r in regions" :key="r" type="button" @click="form.region = r"
                                :class="['py-2.5 px-2 rounded-xl font-bold text-[10px] transition-all border-2', form.region === r ? 'bg-slate-900 text-white border-slate-900 shadow-md' : 'bg-slate-50 text-slate-400 border-transparent hover:border-slate-200']">
                                @{{r}}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="p-6 border-t border-slate-100 bg-slate-50 shrink-0">
                    <button type="button" @click="$emit('update:open', false)" class="w-full bg-blue-600 text-white py-4 rounded-2xl font-black uppercase tracking-widest hover:bg-blue-500 transition-all shadow-xl text-sm">
                        確認修改並返回
                    </button>
                </div>
            </div>
        </div>
    </transition>
</script>
