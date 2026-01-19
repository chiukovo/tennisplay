{{-- Modal Templates --}}

{{-- Player Detail Modal --}}
<script type="text/x-template" id="player-detail-modal-template">
    <transition name="modal">
        <div v-if="player" class="fixed inset-0 z-[200] flex items-center justify-center p-2 sm:p-10 premium-blur modal-content overflow-hidden" @click.self="$emit('close')">
            <div class="bg-white w-full max-w-5xl h-full sm:h-auto max-h-[96vh] sm:max-h-[92vh] rounded-[32px] sm:rounded-[48px] overflow-hidden shadow-[0_0_100px_rgba(0,0,0,0.5)] flex flex-col md:flex-row relative">
                {{-- Close Button --}}
                <button type="button" @click="$emit('close')" class="absolute top-4 right-4 sm:top-8 sm:right-8 z-[120] p-2.5 bg-white/90 backdrop-blur-md hover:bg-red-50 hover:text-red-500 rounded-full shadow-xl transition-all border border-slate-100 group">
                    <app-icon name="x" class-name="w-5 h-5 group-hover:scale-110 transition-transform"></app-icon>
                </button>

                {{-- Page Counter with Mobile Navigation --}}
                <div v-if="players && players.length > 1" class="absolute top-4 left-4 md:top-8 md:left-1/2 md:-translate-x-1/2 z-[120] flex items-center gap-1 md:gap-0">
                    {{-- Mobile Prev Button --}}
                    <button @click.stop="navigate(-1)" class="sm:hidden w-8 h-8 bg-slate-900/10 backdrop-blur-md rounded-full border border-slate-200/50 flex items-center justify-center text-slate-600 active:scale-95 transition-all">
                        <app-icon name="chevron-left" class-name="w-4 h-4"></app-icon>
                    </button>
                    {{-- Counter --}}
                    <div class="px-3 py-1.5 md:px-4 md:py-2 bg-slate-900/10 backdrop-blur-md rounded-full border border-slate-200/50 flex items-center gap-2 md:gap-3">
                        <span class="text-[8px] md:text-[10px] font-black text-slate-400 uppercase tracking-widest">球友</span>
                        <span class="text-xs md:text-sm font-black italic text-slate-900">@{{ currentIndex + 1 }} / @{{ players.length }}</span>
                    </div>
                    {{-- Mobile Next Button --}}
                    <button @click.stop="navigate(1)" class="sm:hidden w-8 h-8 bg-slate-900/10 backdrop-blur-md rounded-full border border-slate-200/50 flex items-center justify-center text-slate-600 active:scale-95 transition-all">
                        <app-icon name="chevron-right" class-name="w-4 h-4"></app-icon>
                    </button>
                </div>

                {{-- Navigation Arrows (Desktop/Tablet Only - Hidden on Mobile to avoid interference with scrolling) --}}
                <button v-if="hasPrev" @click.stop="navigate(-1)" class="hidden sm:flex absolute left-4 md:left-6 top-1/2 -translate-y-1/2 z-[130] w-10 h-10 md:w-12 md:h-12 bg-white/40 backdrop-blur-sm hover:bg-white/80 text-slate-500 hover:text-blue-600 rounded-full shadow-md hover:shadow-lg transition-all border border-white/60 items-center justify-center animate-subtle-pulse">
                    <app-icon name="chevron-left" class-name="w-5 h-5 md:w-6 md:h-6"></app-icon>
                </button>
                <button v-if="hasNext" @click.stop="navigate(1)" class="hidden sm:flex absolute right-4 md:right-6 top-1/2 -translate-y-1/2 z-[130] w-10 h-10 md:w-12 md:h-12 bg-white/40 backdrop-blur-sm hover:bg-white/80 text-slate-500 hover:text-blue-600 rounded-full shadow-md hover:shadow-lg transition-all border border-white/60 items-center justify-center animate-subtle-pulse">
                    <app-icon name="chevron-right" class-name="w-5 h-5 md:w-6 md:h-6"></app-icon>
                </button>


                {{-- Main Scrollable Area (Mobile) / Split Layout (Desktop) --}}
                <div @touchstart.passive="handleTouchStart" 
                    @touchend="handleTouchEnd"
                    :class="['flex-1 flex flex-col md:flex-row overflow-y-auto overflow-x-hidden md:overflow-hidden no-scrollbar pb-24 md:pb-0 overscroll-contain transition-opacity duration-100', isTransitioning ? 'opacity-0' : 'opacity-100']">
                        {{-- Left: Card Display --}}
                        <div class="w-full md:w-1/2 p-6 sm:p-10 flex items-center justify-center bg-slate-50 border-r border-slate-100 shrink-0 relative min-h-[400px] sm:min-h-0">
                            <div class="w-full max-w-[260px] sm:max-w-[340px]">
                                <player-card :player="player" />
                            </div>
                        </div>

                        {{-- Right: Detailed Stats --}}
                        <div class="w-full md:w-1/2 p-8 sm:p-14 md:overflow-y-auto bg-white flex flex-col no-scrollbar">
                            <div class="space-y-4 mb-8">
                                {{-- Row 1: Status, Level, Gender, Handedness --}}
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-2 py-1 bg-blue-600 text-white text-[8px] font-black rounded-lg uppercase tracking-widest italic shrink-0">已認證</span>
                                    <div class="flex items-center gap-1 px-2 py-1 bg-blue-50 rounded-lg text-[10px] font-black text-blue-600 italic shrink-0">
                                        <app-icon name="zap" class-name="w-3 h-3"></app-icon>
                                        NTRP @{{player.level}}
                                        <button @click.stop="$emit('open-ntrp-guide')" class="ml-1 text-blue-400 hover:text-blue-600 transition-colors">
                                            <app-icon name="help-circle" class-name="w-3 h-3 opacity-60"></app-icon>
                                        </button>
                                    </div>
                                    <div class="flex items-center gap-1 px-2 py-1 bg-slate-100 rounded-lg text-[10px] font-bold text-slate-600 shrink-0">
                                        <app-icon :name="player.gender === '女' ? 'female' : 'male'" class-name="w-3 h-3 text-slate-400"></app-icon>
                                        @{{player.gender}}
                                    </div>
                                    <div class="flex items-center gap-1 px-2 py-1 bg-slate-100 rounded-lg text-[10px] font-bold text-slate-600 shrink-0">
                                        @{{player.handed || '右手'}}
                                    </div>
                                    <div class="flex items-center gap-1 px-2 py-1 bg-slate-100 rounded-lg text-[10px] font-bold text-slate-600 shrink-0">
                                        @{{player.backhand || '雙反'}}
                                    </div>
                                </div>

                                {{-- Row 2: Regions (Independent Line) --}}
                                <div v-if="player.region" class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-50">
                                    <template v-for="r in (player.region || '').split(',').filter(x => x)" :key="r">
                                        <div class="flex items-center gap-1.5 px-2.5 py-1.5 bg-slate-50 rounded-xl text-[10px] font-bold text-slate-600 shrink-0 border border-slate-200/50">
                                            <app-icon name="map-pin" class-name="w-3 h-3 text-blue-500/70"></app-icon>
                                            @{{r}}
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div class="space-y-6">
                                {{-- Basic Info Section --}}
                                <div class="space-y-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-1 h-3 bg-blue-600 rounded-full"></div>
                                        <span class="text-[10px] font-black uppercase tracking-[0.1em] text-slate-400 italic">基本資料 / Basic</span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div v-for="item in backStats" :key="item.label" class="flex items-center gap-2 p-3 rounded-2xl bg-slate-50 border border-slate-100">
                                            <div class="w-7 h-7 rounded-xl bg-white border border-slate-100 flex items-center justify-center">
                                                <app-icon :name="item.icon" class-name="w-3.5 h-3.5 text-slate-400"></app-icon>
                                            </div>
                                            <div class="space-y-0.5">
                                                <div class="text-[9px] font-black uppercase tracking-widest text-slate-300">@{{ item.label }}</div>
                                                <div class="text-sm font-black text-slate-700">@{{ item.value }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Intro Section --}}
                                <div class="bg-slate-50 p-6 rounded-[24px] border border-slate-100 relative overflow-hidden mb-6">
                                    <div class="flex items-center gap-2 mb-3">
                                        <div class="w-1 h-3 bg-blue-600 rounded-full"></div>
                                        <span class="text-[10px] font-black uppercase tracking-[0.1em] text-slate-400 italic">個人特色 / 約打宣告</span>
                                    </div>
                                    <p class="text-base text-slate-700 font-bold leading-relaxed italic whitespace-pre-line relative z-10">
                                        @{{player.intro || ''}}
                                    </p>
                                </div>

                                {{-- Events Section --}}
                                <div class="space-y-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-1 h-3 bg-blue-600 rounded-full"></div>
                                        <span class="text-[10px] font-black uppercase tracking-[0.1em] text-slate-400 italic">場次 / Events</span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div class="flex items-center gap-2 p-3 rounded-2xl bg-white border border-slate-100">
                                            <div class="w-7 h-7 rounded-xl bg-blue-50 flex items-center justify-center">
                                                <app-icon name="calendar" class-name="w-3.5 h-3.5 text-blue-600"></app-icon>
                                            </div>
                                            <div>
                                                <div class="text-[9px] font-black uppercase tracking-widest text-slate-300">場次</div>
                                                <div class="text-sm font-black text-slate-700">@{{ stats?.matches || 0 }}</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 p-3 rounded-2xl bg-white border border-slate-100">
                                            <div class="w-7 h-7 rounded-xl bg-rose-50 flex items-center justify-center">
                                                <app-icon name="heart" class-name="w-3.5 h-3.5 text-rose-500"></app-icon>
                                            </div>
                                            <div>
                                                <div class="text-[9px] font-black uppercase tracking-widest text-slate-300">按讚</div>
                                                <div class="text-sm font-black text-slate-700">@{{ stats?.likes || 0 }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Floating Action Bar (Desktop) --}}
                                <div class="hidden md:block sticky bottom-0 mt-8 rounded-[24px] border border-slate-100 overflow-hidden shadow-[0_-6px_24px_rgba(15,23,42,0.06)] bg-white/95 backdrop-blur">
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
                                        <button type="button" @click="$emit('open-profile', player.user_uid || player.user?.uid || player.user_id)" 
                                                class="flex-1 py-5 flex flex-col items-center justify-center gap-2 hover:bg-slate-50 transition-all group">
                                            <app-icon name="user" class-name="w-5 h-5 text-slate-400 group-hover:text-slate-900 group-hover:scale-110 transition-all"></app-icon>
                                            <span class="text-[11px] font-black uppercase tracking-widest text-slate-400 group-hover:text-slate-900 transition-colors">主頁</span>
                                        </button>
                                        <button type="button" @click="$emit('share', player)" 
                                                class="flex-1 py-5 flex flex-col items-center justify-center gap-2 hover:bg-slate-50 text-slate-400 transition-all group">
                                            <app-icon name="share-2" class-name="w-5 h-5 group-hover:text-blue-600 group-hover:scale-110 transition-all"></app-icon>
                                            <span class="text-[11px] font-black uppercase tracking-widest text-slate-400 group-hover:text-blue-600 transition-colors">分享</span>
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
                                        <span class="text-[10px] font-black uppercase tracking-[0.1em] text-slate-400 italic">留言 / Comments</span>
                                    </div>
                                    <span class="text-[9px] font-black text-slate-300">@{{ comments.length }} COMMENTS</span>
                                </div>

                                 <!-- Combined Comment Unit -->
                                <div class="bg-white rounded-[32px] p-2 sm:p-4">
                                    <!-- Comment Input -->
                                    <div class="flex gap-3 mb-8 px-2">
                                        <div class="w-10 h-10 rounded-full overflow-hidden bg-slate-100 shrink-0 border border-slate-100">
                                            <img v-if="currentUser?.line_picture_url" :src="currentUser.line_picture_url" class="w-full h-full object-cover">
                                            <app-icon v-else name="user" class-name="w-full h-full text-slate-300 p-2"></app-icon>
                                        </div>
                                        <div class="flex-1 relative">
                                            <!-- Rating Selector -->
                                            <div v-if="currentUser && player.user_id !== currentUser.id" class="flex items-center justify-between mb-2 pl-1">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                                        @{{ myCommentId ? '更新評分' : (existingRatedComment ? '您已評分' : '評分 (選填)') }}
                                                    </span>
                                                    <button v-if="existingRatedComment && !myCommentId" @click="startEditRating" class="text-[10px] text-blue-500 font-bold hover:underline">
                                                        (修改)
                                                    </button>
                                                    <button v-if="myCommentId" @click="cancelEdit" class="text-[10px] text-slate-400 font-bold hover:underline">
                                                        (取消)
                                                    </button>
                                                </div>
                                                <div class="flex gap-1">
                                                    <template v-if="!myCommentId && existingRatedComment">
                                                        <div class="flex gap-0.5" title="點擊星星即可修改">
                                                            <app-icon v-for="i in 5" :key="i" name="star" 
                                                                :class-name="i <= existingRatedComment.rating ? 'w-4 h-4 text-amber-400' : 'w-4 h-4 text-slate-200'" 
                                                                :fill="i <= existingRatedComment.rating ? 'currentColor' : 'none'"
                                                                @click="startEditRating(); playerCommentRating = i"
                                                                class="cursor-pointer hover:scale-110 transition-transform"></app-icon>
                                                        </div>
                                                    </template>
                                                    <template v-else>
                                                        <button v-for="i in 5" :key="i" @click="playerCommentRating = (playerCommentRating === i ? 0 : i)" class="p-1 hover:scale-110 transition-transform">
                                                            <app-icon name="star" :class-name="i <= playerCommentRating ? 'w-4 h-4 text-amber-400' : 'w-4 h-4 text-slate-200'" :fill="i <= playerCommentRating ? 'currentColor' : 'none'"></app-icon>
                                                        </button>
                                                    </template>
                                                </div>
                                            </div>

                                            <textarea v-model="commentDraft" 
                                                rows="1"
                                                @keydown.enter.exact.prevent="postComment"
                                                :placeholder="myCommentId ? '更新您的留言...' : '留個言打聲招呼吧...'" 
                                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-3 pr-20 text-sm font-bold focus:bg-slate-100 outline-none transition-all placeholder:text-slate-300 resize-none overflow-hidden"></textarea>
                                            <div class="absolute right-10 bottom-1.5">
                                                <emoji-picker @select="e => commentDraft += e"></emoji-picker>
                                            </div>
                                            <button @click="postComment" :disabled="(!commentDraft.trim() && playerCommentRating === 0) || isSubmitting" class="absolute right-2 bottom-1.5 p-2 text-blue-600 disabled:opacity-20 transition-all">
                                                <app-icon v-if="!isSubmitting" :name="myCommentId ? 'edit' : 'send'" class-name="w-5 h-5"></app-icon>
                                                <div v-else class="w-4 h-4 border-2 border-blue-600/30 border-t-blue-600 rounded-full animate-spin"></div>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Scrollable Comment List -->
                                    <div class="max-h-[500px] overflow-y-auto pr-2 no-scrollbar">
                                        <template v-if="commentsReady">
                                            <div v-if="comments.length > 0" class="space-y-1">
                                                <div v-for="(c, index) in comments" :key="c.id" class="comment-threads">
                                                    <div class="avatar-container">
                                                        <div class="avatar cursor-pointer" @click="$emit('open-profile', c.user.uid)">
                                                            <img v-if="c.user.line_picture_url" :src="c.user.line_picture_url" class="w-full h-full object-cover">
                                                            <app-icon v-else name="user" class-name="w-full h-full text-slate-200 p-2"></app-icon>
                                                        </div>
                                                        <div v-if="index < comments.length - 1" class="thread-line"></div>
                                                    </div>
                                                    <div class="content-container">
                                                        <div class="header flex items-center justify-between">
                                                            <div class="flex items-center gap-2">
                                                                <span class="username cursor-pointer hover:underline" @click="$emit('open-profile', c.user?.uid || c.user_id)">@{{ c.user?.name || '匿名球友' }}</span>
                                                                <span class="timestamp">@{{ formatDate(c.at) }}</span>
                                                            </div>
                                                            <div class="flex items-center gap-2">
                                                                <div v-if="c.rating" class="flex text-amber-400 gap-0.5">
                                                                    <app-icon v-for="i in 5" :key="i" name="star" :class-name="i <= c.rating ? 'w-3 h-3 text-amber-400' : 'w-3 h-3 text-slate-200'" :fill="i <= c.rating ? 'currentColor' : 'none'"></app-icon>
                                                                </div>
                                                                <div v-if="currentUser" class="flex items-center gap-1">
                                                                    <!-- Reply Button (Owner Only, but not on own comment) -->
                                                                    <button v-if="isOwner && !c.reply && (c.user && c.user.uid !== currentUser.uid)" type="button"
                                                                        @click="toggleReply(c.id)"
                                                                        class="p-1 text-slate-300 hover:text-blue-500 transition-colors"
                                                                        title="回覆留言">
                                                                        <app-icon name="message-square" class-name="w-3 h-3"></app-icon>
                                                                    </button>
                                                                    <!-- Delete Button (Author Only) -->
                                                                    <button v-if="c.user && c.user.uid && currentUser.uid && c.user.uid === currentUser.uid" type="button"
                                                                        @click="deleteComment(c.id)"
                                                                        class="p-1 text-slate-300 hover:text-red-500 transition-colors"
                                                                        title="刪除留言">
                                                                        <app-icon name="trash" class-name="w-3 h-3"></app-icon>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="text whitespace-pre-line">@{{ c.text }}</div>
                                                        
                                                        <!-- Owner Reply Display -->
                                                        <div v-if="c.reply" class="mt-2 ml-2 p-3 bg-blue-50/50 rounded-xl border border-blue-100 relative">
                                                            <div class="flex items-center justify-between mb-1">
                                                                <div class="flex items-center gap-2">
                                                                    <div class="w-4 h-4 rounded-full bg-blue-600 flex items-center justify-center">
                                                                        <app-icon name="user" class-name="w-2.5 h-2.5 text-white"></app-icon>
                                                                    </div>
                                                                    <span class="text-[10px] font-bold text-blue-800">版主回覆</span>
                                                                    <span class="text-[10px] text-blue-400">@{{ formatDate(c.replied_at) }}</span>
                                                                </div>
                                                                <button v-if="isOwner" 
                                                                    @click="deleteReply(c.id)"
                                                                    class="p-1 text-blue-300 hover:text-red-500 transition-colors">
                                                                    <app-icon name="trash" class-name="w-3 h-3"></app-icon>
                                                                </button>
                                                            </div>
                                                            <div class="text-xs text-slate-600 whitespace-pre-line pl-6">@{{ c.reply }}</div>
                                                        </div>

                                                        <!-- Owner Reply Input -->
                                                        <div v-if="isOwner && !c.reply && activeReplyId === c.id" class="mt-2">
                                                            <div class="flex gap-2">
                                                                <input v-model="replyDrafts[c.id]" 
                                                                    @keydown.enter.prevent="submitReply(c.id)"
                                                                    type="text" 
                                                                    placeholder="回覆這則留言..." 
                                                                    class="flex-1 bg-slate-50 border-none rounded-lg px-3 py-2 text-xs font-bold focus:bg-slate-100 outline-none transition-all placeholder:text-slate-300">
                                                                <button @click="submitReply(c.id)" :disabled="!replyDrafts[c.id]?.trim()" class="p-2 text-blue-600 disabled:opacity-20 hover:bg-blue-50 rounded-lg transition-all">
                                                                    <app-icon name="send" class-name="w-4 h-4"></app-icon>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div v-else class="py-10 text-center">
                                                <p class="text-slate-300 font-black italic text-xs uppercase tracking-widest">目前還沒有留言...</p>
                                            </div>
                                        </template>
                                        <div v-else class="py-10 text-center">
                                            <p class="text-slate-300 font-black italic text-xs uppercase tracking-widest">載入中...</p>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>

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
                        <button type="button" @click="$emit('open-profile', player.user_uid || player.user?.uid || player.user_id)" 
                                class="flex-1 bg-slate-50 text-slate-600 py-3.5 rounded-2xl font-black uppercase tracking-widest text-[10px] flex flex-col items-center justify-center gap-1 active:scale-95 transition-all">
                            <app-icon name="user" class-name="w-4 h-4"></app-icon>
                            主頁
                        </button>
                        <button type="button" @click="$emit('share', player)" 
                                class="flex-1 bg-slate-50 text-slate-600 py-3.5 rounded-2xl font-black uppercase tracking-widest text-[10px] flex flex-col items-center justify-center gap-1 active:scale-95 transition-all">
                            <app-icon name="share-2" class-name="w-4 h-4"></app-icon>
                            分享
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
                        <div class="flex items-center justify-between bg-white p-6 rounded-[32px] border border-slate-100 shadow-sm hover:border-blue-200 transition-all cursor-pointer" @click="$emit('open-profile', event.user_uid || event.user?.uid || event.user_id)">
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
                            <div class="px-2">
                                <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3 ml-2">提問、留言、或打個招呼...</div>
                                <div class="flex gap-3 mb-10">
                                    <div class="w-10 h-10 rounded-full overflow-hidden bg-slate-100 shrink-0 border border-slate-100">
                                        <img v-if="currentUser?.line_picture_url" :src="currentUser.line_picture_url" class="w-full h-full object-cover">
                                        <app-icon v-else name="user" class-name="w-full h-full text-slate-300 p-2"></app-icon>
                                    </div>
                                    <div class="flex-1 relative">
                                        <textarea :value="commentDraft" 
                                            @input="$emit('update:comment-draft', $event.target.value)"
                                            @keydown.enter.exact.prevent="$emit('comment', event.id)"
                                            rows="1" placeholder="寫下你的訊息..." 
                                            class="w-full bg-slate-50 border-none rounded-2xl px-5 py-3 pr-20 text-sm font-bold focus:bg-slate-100 outline-none transition-all placeholder:text-slate-300 resize-none overflow-hidden min-h-[48px]"></textarea>
                                        <div class="absolute right-10 top-1.5">
                                            <emoji-picker @select="e => $emit('update:comment-draft', (commentDraft || '') + e)"></emoji-picker>
                                        </div>
                                        <button type="button" @click="$emit('comment', event.id)" :disabled="!commentDraft?.trim() || isSubmitting" class="absolute right-2 top-1.5 p-2 text-blue-600 disabled:opacity-20 transition-all">
                                            <app-icon v-if="!isSubmitting" name="send" class-name="w-5 h-5"></app-icon>
                                            <div v-else class="w-4 h-4 border-2 border-blue-600/30 border-t-blue-600 rounded-full animate-spin"></div>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Comments List --}}
                            <div class="space-y-1" v-if="comments[event.id]?.length">
                                <div v-for="(c, index) in comments[event.id]" :key="c.id" class="comment-threads">
                                    <div class="avatar-container">
                                        <div class="avatar cursor-pointer" @click="openProfile(c.user?.uid || c.user_id)">
                                            <img v-if="c.user?.photo" :src="c.user?.photo" class="w-full h-full object-cover">
                                            <app-icon v-else name="user" class-name="w-full h-full text-slate-400 p-2"></app-icon>
                                        </div>
                                        <div v-if="index < comments[event.id].length - 1" class="thread-line"></div>
                                    </div>
                                    <div class="content-container">
                                        <div class="header">
                                            <div class="flex items-center gap-2">
                                                <span class="username cursor-pointer hover:underline" @click="openProfile(c.user?.uid || c.user_id)">@{{ c.user?.name || '匿名球友' }}</span>
                                                <span class="timestamp">@{{ formatDate(c.at) }}</span>
                                            </div>
                                            <button v-if="currentUser && c.user?.uid === currentUser.uid" 
                                                @click="$emit('delete-comment', c.id, event.id)"
                                                class="p-1 text-slate-300 hover:text-red-500 transition-colors">
                                                <app-icon name="trash" class-name="w-3 h-3"></app-icon>
                                            </button>
                                        </div>
                                        <div class="text whitespace-pre-line">@{{ c.text }}</div>
                                    </div>
                                </div>
                            </div>
                            <div v-else-if="!event.loading" class="text-center py-10 bg-slate-50 rounded-[32px] border-2 border-dashed border-slate-100">
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
                            <h3 class="font-black italic uppercase text-xl tracking-tight">約打邀約信</h3>
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
                        :placeholder="'Hi ' + (player.name || '') + '，我想跟你約打！'"></textarea>
                    <button type="button" @click="$emit('submit', textModel)" :disabled="isSending" 
                        :class="['w-full py-5 rounded-3xl font-black uppercase tracking-[0.2em] shadow-2xl transition-all text-lg', 
                            isSending ? 'bg-slate-400 text-slate-200 cursor-not-allowed' : 'bg-slate-950 text-white hover:bg-blue-600']">
                        @{{ isSending ? '發送中...' : '發送站內訊息' }}
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
                        <h3 class="font-black italic uppercase text-xl tracking-tight">編輯球友卡資訊</h3>
                    </div>
                    <button type="button" @click="$emit('update:open', false)" class="p-2 hover:bg-white/10 rounded-full transition-all">
                        <app-icon name="x" class-name="w-6 h-6 opacity-50"></app-icon>
                    </button>
                </div>
                
                <div class="p-8 overflow-y-auto no-scrollbar space-y-8">
                    {{-- Photo Section --}}
                    <div class="flex flex-col items-center gap-4 pb-4 border-b border-slate-100">
                        <div class="relative group">
                            <div class="w-32 h-40 rounded-2xl overflow-hidden border-4 border-white shadow-xl bg-slate-900 relative">
                                <img :src="getUrl(form.photo)" class="relative w-full h-full object-cover z-10" :style="{ transform: `translate(${form.photoX}%, ${form.photoY}%) scale(${form.photoScale})` }">
                            </div>
                            <button type="button" @click="$emit('trigger-upload')" class="absolute -bottom-2 -right-2 w-10 h-10 bg-blue-600 text-white rounded-xl flex items-center justify-center shadow-lg hover:scale-110 transition-all">
                                <app-icon name="upload" class-name="w-5 h-5"></app-icon>
                            </button>
                        </div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">點擊按鈕更換形象照片</p>
                        
                        {{-- Photo Adjustments --}}
                        <div v-if="form.photo" class="w-full max-w-xs space-y-3 pt-2">
                            <div class="space-y-1">
                                <div class="flex justify-between text-[9px] font-black uppercase tracking-widest text-slate-400">
                                    <span>縮放大小</span>
                                    <span class="text-blue-600">@{{ Math.round(form.photoScale * 100) }}%</span>
                                </div>
                                <input type="range" v-model.number="form.photoScale" min="0.5" max="3" step="0.01" class="w-full h-1.5 bg-slate-100 rounded-full appearance-none cursor-pointer accent-blue-600">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <div class="text-[9px] font-black uppercase tracking-widest text-slate-400">水平位置</div>
                                    <input type="range" v-model.number="form.photoX" min="-100" max="100" step="1" class="w-full h-1.5 bg-slate-100 rounded-full appearance-none cursor-pointer accent-slate-600">
                                </div>
                                <div class="space-y-1">
                                    <div class="text-[9px] font-black uppercase tracking-widest text-slate-400">垂直位置</div>
                                    <input type="range" v-model.number="form.photoY" min="-100" max="100" step="1" class="w-full h-1.5 bg-slate-100 rounded-full appearance-none cursor-pointer accent-slate-600">
                                </div>
                            </div>
                        </div>
                    </div>
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

                    {{-- Region (Multi-select) --}}
                    <div class="space-y-3">
                        <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">主要活動地區 <span class="text-blue-500">(可複選)</span></label>
                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 max-h-[150px] overflow-y-auto no-scrollbar p-1">
                            <button v-for="r in regions" :key="r" type="button" @click="toggleRegion(r)"
                                :class="['py-2.5 px-2 rounded-xl font-bold text-[10px] transition-all border-2', selectedRegions.includes(r) ? 'bg-blue-600 text-white border-blue-600 shadow-md' : 'bg-slate-50 text-slate-400 border-transparent hover:border-slate-200']">
                                @{{r}}
                            </button>
                        </div>
                        <p v-if="selectedRegions.length > 0" class="text-xs text-blue-600 font-bold">已選擇: @{{ selectedRegions.join('、') }}</p>
                    </div>

                    {{-- Intro --}}
                    <div class="space-y-3">
                        <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">約打宣告 / 個人特色</label>
                        <textarea v-model="form.intro" rows="3" class="w-full px-5 py-4 bg-slate-50 border-2 border-transparent rounded-2xl focus:border-blue-500 outline-none font-bold text-sm leading-relaxed" placeholder="分享一下您的打法特色..."></textarea>
                    </div>
                </div>

                <div class="p-6 border-t border-slate-100 bg-slate-50 shrink-0 flex gap-3">
                    <button type="button" @click="$emit('save')" class="flex-[2] bg-blue-600 text-white py-4 rounded-2xl font-black uppercase tracking-widest hover:bg-blue-500 transition-all shadow-xl text-sm">
                        儲存並發佈
                    </button>
                    <button type="button" @click="$emit('update:open', false)" class="flex-1 bg-white border border-slate-200 text-slate-400 py-4 rounded-2xl font-black uppercase tracking-widest hover:bg-slate-50 transition-all text-sm">
                        取消
                    </button>
                </div>
            </div>
        </div>
    </transition>
</script>

{{-- NTRP Level Guide Modal Template --}}
<script type="text/x-template" id="ntrp-guide-modal-template">
    <transition name="modal">
        <div v-if="open" class="fixed inset-0 z-[500] flex items-center justify-center p-4 sm:p-6 bg-slate-900/60 backdrop-blur-sm" @click.self="$emit('update:open', false)">
            <div class="bg-white w-full max-w-lg rounded-[40px] shadow-2xl overflow-hidden flex flex-col max-h-[85vh] animate__animated animate__zoomIn animate__faster">
                {{-- Header --}}
                <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <div>
                        <h3 class="text-2xl font-black italic uppercase tracking-tight text-slate-900">NTRP 等級說明</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">NTRP Level Guide</p>
                    </div>
                    <button @click="$emit('update:open', false)" class="w-10 h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:text-slate-600 hover:border-slate-300 transition-all shadow-sm">
                        <app-icon name="x" class-name="w-5 h-5"></app-icon>
                    </button>
                </div>

                {{-- Content --}}
                <div class="flex-1 overflow-y-auto p-6 sm:p-8 space-y-4 custom-scrollbar">
                    <div v-for="(desc, level) in descs" :key="level" 
                        class="p-4 rounded-2xl border border-slate-100 hover:border-blue-200 hover:bg-blue-50/30 transition-all group">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-xl bg-slate-900 flex flex-col items-center justify-center shrink-0 shadow-lg group-hover:scale-110 transition-transform">
                                <span class="text-[10px] font-bold text-white/40 leading-none mb-1">NTRP</span>
                                <span class="text-xl font-black text-white italic leading-none">@{{ level }}</span>
                            </div>
                            <div>
                                <h4 class="font-black text-slate-900 text-sm mb-1">@{{ LEVEL_TAGS[level] }}</h4>
                                <p class="text-slate-500 text-xs leading-relaxed font-medium">@{{ desc }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-8 py-6 bg-slate-50 border-t border-slate-100">
                    <button @click="$emit('update:open', false)" class="w-full py-4 bg-slate-900 text-white rounded-2xl font-black uppercase tracking-widest text-xs hover:bg-blue-600 transition-all shadow-lg active:scale-95">
                        我瞭解了
                    </button>
                </div>
            </div>
        </div>
    </transition>
</script>

{{-- Emoji Picker Component Template --}}
<script type="text/x-template" id="emoji-picker-template">
    <div class="relative" v-click-outside="() => open = false">
        <button type="button" @click.stop="open = !open" class="p-2 text-slate-400 hover:text-yellow-500 transition-colors" title="插入表情符號">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M8 14s1.5 2 4 2 4-2 4-2"></path>
                <line x1="9" y1="9" x2="9.01" y2="9"></line>
                <line x1="15" y1="9" x2="15.01" y2="9"></line>
            </svg>
        </button>
        <transition name="fade">
            <div v-if="open" class="absolute bottom-full right-0 mb-2 p-2.5 bg-white rounded-2xl shadow-2xl border border-slate-100 grid grid-cols-8 gap-0.5 z-[999] w-72 max-h-48 overflow-y-auto">
                <button v-for="e in emojis" :key="e" @click="selectEmoji(e)" type="button" class="text-xl hover:bg-blue-50 rounded-lg p-1.5 transition-colors active:scale-90">
                    @{{ e }}
                </button>
            </div>
        </transition>
    </div>
</script>
