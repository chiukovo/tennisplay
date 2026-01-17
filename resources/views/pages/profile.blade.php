<div v-if="view === 'profile'" v-show="!isProfileLoading || profileData.user" class="bg-slate-50/50">
    <!-- Sticky Header -->
    <div class="bg-white/80 backdrop-blur-xl border-b sticky top-20 z-[90]">
        <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
            <div class="flex items-center gap-2 sm:gap-4">
                <button @click="navigateTo('home')" class="p-2 hover:bg-slate-100 rounded-xl transition-colors">
                    <app-icon name="arrow-left" class-name="w-5 h-5 text-slate-600"></app-icon>
                </button>
                <h1 class="text-base sm:text-lg font-black italic uppercase tracking-tight text-slate-900 whitespace-nowrap">個人主頁</h1>
            </div>
            <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
                {{-- Share Button (For everyone if player card exists) --}}
                <button v-if="profileData.user?.player" 
                        @click="shareModal.player = profileData.user.player; shareModal.open = true"
                        class="p-2 bg-white border border-slate-200 text-slate-400 hover:text-blue-600 rounded-xl transition-all shadow-sm group">
                    <app-icon name="share-2" class-name="w-5 h-5 group-hover:scale-110 transition-transform"></app-icon>
                </button>

                <div v-if="profileData.status?.is_me" class="flex items-center gap-1.5 sm:gap-2">
                    <template v-if="isEditingProfile">
                        <button @click="isEditingProfile = false" class="px-3 sm:px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-xl text-[10px] sm:text-xs font-black uppercase tracking-widest hover:bg-slate-50 transition-all shadow-sm whitespace-nowrap">
                            取消
                        </button>
                        <button @click="saveProfile" class="px-3 sm:px-4 py-2 bg-blue-600 text-white rounded-xl text-[10px] sm:text-xs font-black uppercase tracking-widest hover:bg-blue-700 transition-all shadow-sm whitespace-nowrap">
                            保存
                        </button>
                    </template>
                    <template v-else>
                        <button @click="isEditingProfile = true" class="p-2 sm:px-4 sm:py-2 bg-white border border-slate-200 text-slate-700 rounded-xl text-[10px] sm:text-xs font-black uppercase tracking-widest hover:bg-slate-50 transition-all shadow-sm whitespace-nowrap flex items-center gap-2 group" title="編輯資料">
                            <app-icon name="edit-3" class-name="w-5 h-5 sm:w-4 sm:h-4 text-slate-600 group-hover:text-blue-600 transition-colors"></app-icon>
                            <span class="hidden sm:inline">編輯資料</span>
                        </button>
                        <button v-if="profileData.user?.player" @click="editCard(profileData.user.player)" class="p-2 sm:px-4 sm:py-2 bg-blue-600 text-white rounded-xl text-[10px] sm:text-xs font-black uppercase tracking-widest hover:bg-blue-700 transition-all shadow-sm whitespace-nowrap flex items-center gap-2 group" title="編輯卡片樣式">
                            <app-icon name="zap" class-name="w-5 h-5 sm:w-4 sm:h-4 text-white"></app-icon>
                            <span class="hidden sm:inline">編輯卡片樣式</span>
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 py-8 space-y-8">
        <!-- Unified Profile Header -->
        <div class="bg-white rounded-[40px] p-6 sm:p-10 border border-slate-100 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 right-0 w-96 h-96 bg-blue-50/50 blur-3xl rounded-full -mr-48 -mt-48"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row gap-8 lg:gap-12">
                <!-- Left: Player Card -->
                <div class="w-full md:w-[350px] lg:w-[420px] shrink-0" :class="{'hidden md:block': !isProfileLoading && !profileData.user?.player}">
                    <!-- Loading Skeleton -->
                    <div v-if="isProfileLoading" class="animate-pulse">
                        <div class="aspect-[450/684] bg-slate-200 rounded-[28px]"></div>
                    </div>
                    
                    <!-- Player Card (only show after loading) -->
                    <div v-else-if="profileData.user.player" class="relative group transition-all duration-500 hover:-translate-y-2">
                        <player-card :player="profileData.user.player" @click="showDetail(profileData.user.player)"></player-card>
                        
                        <!-- Quick Edit Overlay for Me -->
                        <div v-if="profileData.status?.is_me" class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-3 z-20 rounded-2xl">
                            <button @click="editCard(profileData.user.player)" class="px-6 py-3 bg-white text-slate-900 rounded-xl font-black text-xs uppercase tracking-widest shadow-xl hover:scale-105 transition-all">更換照片/樣式</button>
                            <button @click="isEditingProfile = true" class="px-6 py-3 bg-blue-600 text-white rounded-xl font-black text-xs uppercase tracking-widest shadow-xl hover:scale-105 transition-all">修改文字資訊</button>
                        </div>
                    </div>
                    
                    <!-- Empty State (only show after loading confirms no player card) -->
                    <!-- Placeholder Card for Empty State -->
                    <div v-else-if="profileData.status?.is_me" class="relative group">
                        <player-card :is-placeholder="true"></player-card>
                        <div class="absolute inset-0 flex flex-col items-center justify-center p-6 text-center z-[80]">
                            <div class="bg-white/80 backdrop-blur-md p-6 rounded-[32px] shadow-2xl border border-white/20 transform group-hover:scale-105 transition-all duration-500">
                                <div class="w-12 h-12 bg-blue-600 text-white rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-lg shadow-blue-500/30">
                                    <app-icon name="plus" class-name="w-6 h-6"></app-icon>
                                </div>
                                <p class="text-xs font-black text-slate-900 uppercase tracking-widest mb-1">尚未建立球友卡</p>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">建立後即可開啟約打功能</p>
                                <button @click="navigateTo('create')" class="w-full py-3 bg-blue-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-blue-500/20 hover:bg-blue-700 transition-all">
                                    立即建立
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Info & Stats -->
                <div class="flex-1 flex flex-col">
                    <div v-if="!isEditingProfile" class="h-full flex flex-col">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-8">
                            <div class="space-y-4">
                                <div class="flex flex-wrap gap-2">
                                    <span v-if="profileData.user?.gender" class="px-4 py-1.5 bg-slate-100 rounded-full text-xs font-black text-slate-600 uppercase tracking-widest flex items-center gap-1.5">
                                        <app-icon name="gender" class-name="w-3.5 h-3.5"></app-icon>@{{ profileData.user?.gender }}
                                    </span>
                                    <template v-if="profileData.user?.region">
                                        <span v-for="r in profileData.user.region.split(',')" :key="r" class="px-4 py-1.5 bg-blue-50 rounded-full text-xs font-black text-blue-600 uppercase tracking-widest flex items-center gap-1.5">
                                            <app-icon name="map-pin" class-name="w-3.5 h-3.5"></app-icon>@{{ r }}
                                        </span>
                                    </template>
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div v-if="!profileData.status?.is_me" class="flex flex-nowrap gap-2 sm:gap-3">
                                <button @click="toggleFollow" 
                                        :class="profileData.status.is_following ? 'bg-slate-100 text-slate-600' : 'bg-blue-600 text-white shadow-lg shadow-blue-500/20'"
                                        class="px-4 sm:px-8 py-3 sm:py-3.5 rounded-2xl font-black uppercase tracking-widest text-[10px] sm:text-xs transition-all active:scale-95 whitespace-nowrap">
                                    @{{ profileData.status.is_following ? '已追蹤' : '追蹤球友' }}
                                </button>
                                <button v-if="profileData.user?.player" @click="toggleLike"
                                        :class="profileData.status.is_liked ? 'bg-rose-50 text-rose-500' : 'bg-white border border-slate-200 text-slate-600 hover:text-rose-500'"
                                        class="px-4 sm:px-6 py-3 sm:py-3.5 rounded-2xl font-black uppercase tracking-widest text-[10px] sm:text-xs transition-all flex items-center gap-2 shrink-0">
                                    <app-icon name="heart" class-name="w-4 h-4"></app-icon>
                                    <span>@{{ profileData.status.is_liked ? '已按讚' : '按讚' }}</span>
                                </button>
                                <button @click="openMessage({from_user_id: profileData.user.id, sender: profileData.user})" class="px-4 sm:px-6 py-3 sm:py-3.5 bg-white border border-slate-200 text-slate-700 rounded-2xl font-black uppercase tracking-widest text-[10px] sm:text-xs hover:bg-slate-50 transition-all flex items-center gap-2 shrink-0">
                                    <app-icon name="mail" class-name="w-4 h-4"></app-icon>
                                </button>
                                <button v-if="profileData.user?.player" 
                                        @click="shareModal.player = profileData.user.player; shareModal.open = true"
                                        class="px-5 sm:px-6 py-3.5 bg-white border border-slate-200 text-slate-400 rounded-2xl font-black uppercase tracking-widest text-[10px] sm:text-xs hover:text-blue-600 transition-all flex items-center gap-2 shrink-0">
                                    <app-icon name="share-2" class-name="w-4 h-4"></app-icon>
                                </button>
                            </div>
                        </div>

                        <!-- Stats (Clean Icon Row) -->
                        <div class="flex flex-nowrap items-center gap-6 sm:gap-10 border-b border-slate-100 pb-8 mb-8 overflow-x-auto no-scrollbar">
                            <div class="flex items-center gap-2.5 shrink-0 group">
                                <div class="w-9 h-9 sm:w-11 sm:h-11 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400 group-hover:bg-blue-50 group-hover:text-blue-600 transition-all duration-300">
                                    <app-icon name="user" class-name="w-4 h-4 sm:w-5 sm:h-5"></app-icon>
                                </div>
                                <div class="flex flex-col">
                                    <div class="text-lg sm:text-xl font-black text-slate-950 tracking-tight leading-none whitespace-nowrap">@{{ profileData.stats.following_count }}</div>
                                    <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-0.5 whitespace-nowrap">追蹤</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2.5 shrink-0 group">
                                <div class="w-9 h-9 sm:w-11 sm:h-11 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400 group-hover:bg-blue-50 group-hover:text-blue-600 transition-all duration-300">
                                    <app-icon name="users" class-name="w-4 h-4 sm:w-5 sm:h-5"></app-icon>
                                </div>
                                <div class="flex flex-col">
                                    <div class="text-lg sm:text-xl font-black text-slate-950 tracking-tight leading-none whitespace-nowrap">@{{ profileData.stats.followers_count }}</div>
                                    <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-0.5 whitespace-nowrap">追蹤者</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2.5 shrink-0 group">
                                <div class="w-9 h-9 sm:w-11 sm:h-11 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400 group-hover:bg-rose-50 group-hover:text-rose-500 transition-all duration-300">
                                    <app-icon name="heart" class-name="w-4 h-4 sm:w-5 sm:h-5"></app-icon>
                                </div>
                                <div class="flex flex-col">
                                    <div class="text-lg sm:text-xl font-black text-slate-950 tracking-tight leading-none whitespace-nowrap">@{{ profileData.stats.likes_count }}</div>
                                    <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-0.5 whitespace-nowrap">按讚</div>
                                </div>
                            </div>
                        </div>

                        <!-- Bio -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-1.5 h-4 bg-blue-600 rounded-full"></div>
                                    <span class="text-xs font-black uppercase tracking-widest text-slate-400 italic">個人檔案 / Profile Bio</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <button v-if="profileData.status?.is_me && !isEditingProfile" @click="isEditingProfile = true" class="md:hidden text-slate-600 font-black text-[11px] uppercase tracking-widest flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 rounded-lg">
                                        <app-icon name="edit-3" class-name="w-3.5 h-3.5"></app-icon>編輯
                                    </button>
                                    <button v-if="profileData.status?.is_me && !profileData.user?.player" @click="navigateTo('create')" class="md:hidden text-blue-600 font-black text-[11px] uppercase tracking-widest flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 rounded-lg">
                                        <app-icon name="plus" class-name="w-3.5 h-3.5"></app-icon>製作卡片
                                    </button>
                                </div>
                            </div>
                            <div class="bg-slate-50/50 p-7 rounded-[32px] border border-slate-100">
                                <p class="text-lg text-slate-700 font-bold leading-relaxed italic whitespace-pre-line">
                                    「@{{ profileData.user?.bio || '尚未填寫' }}」
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Mode -->
                    <div v-else class="space-y-6 animate__animated animate__fadeIn">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">顯示暱稱</label>
                                <input v-model="profileForm.name" type="text" class="w-full px-5 py-3.5 bg-slate-50 border-2 border-transparent rounded-2xl focus:border-blue-500 outline-none font-black italic text-base transition-all">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">生理性別</label>
                                <div class="flex gap-2">
                                    <button v-for="g in ['男', '女']" :key="g" @click="profileForm.gender = g"
                                            :class="profileForm.gender === g ? 'bg-slate-900 text-white shadow-lg' : 'bg-slate-50 text-slate-400'"
                                            class="flex-1 py-3.5 rounded-2xl font-black text-xs uppercase tracking-widest transition-all">
                                        @{{ g }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">主要活動地區 <span class="text-blue-500">(可複選)</span></label>
                            <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-6 gap-2 max-h-[280px] overflow-y-auto no-scrollbar p-1">
                                <button v-for="r in regions" :key="r" @click="toggleProfileRegion(r)"
                                        :class="selectedProfileRegions.includes(r) ? 'bg-blue-600 text-white shadow-md' : 'bg-slate-50 text-slate-400'"
                                        class="py-3.5 rounded-xl font-black text-[10px] transition-all">
                                    @{{ r }}
                                </button>
                            </div>
                            <p v-if="selectedProfileRegions.length > 0" class="text-xs text-blue-600 font-bold ml-1">已選擇: @{{ selectedProfileRegions.join('、') }}</p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">個人簡介 (Bio)</label>
                            <textarea v-model="profileForm.bio" rows="3" class="w-full px-5 py-4 bg-slate-50 border-2 border-transparent rounded-2xl focus:border-blue-500 outline-none font-bold text-sm leading-relaxed" placeholder="介紹一下你的網球經歷吧..."></textarea>
                        </div>

                        {{-- Player Card Specific Fields --}}
                        <div v-if="profileData.user?.player" class="pt-6 border-t border-slate-100 space-y-6">
                            <h4 class="text-xs font-black text-slate-900 uppercase tracking-widest">球友卡詳細設定</h4>
                            
                            <div class="space-y-4">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">NTRP 程度</label>
                                <div class="grid grid-cols-4 sm:grid-cols-6 gap-2">
                                    <button v-for="l in levels" :key="l" @click="profileForm.level = l"
                                            :class="profileForm.level === l ? 'bg-blue-600 text-white shadow-md' : 'bg-slate-50 text-slate-400'"
                                            class="py-2.5 rounded-xl font-black text-[10px] transition-all">
                                        @{{ l }}
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">持拍手</label>
                                    <div class="flex gap-2">
                                        <button v-for="h in ['右手', '左手']" :key="h" @click="profileForm.handed = h"
                                                :class="profileForm.handed === h ? 'bg-slate-900 text-white shadow-lg' : 'bg-slate-50 text-slate-400'"
                                                class="flex-1 py-3 rounded-xl font-black text-xs uppercase tracking-widest transition-all">
                                            @{{ h }}
                                        </button>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">反手類型</label>
                                    <div class="flex gap-2">
                                        <button v-for="b in ['單反', '雙反']" :key="b" @click="profileForm.backhand = b"
                                                :class="profileForm.backhand === b ? 'bg-slate-900 text-white shadow-lg' : 'bg-slate-50 text-slate-400'"
                                                class="flex-1 py-3 rounded-xl font-black text-xs uppercase tracking-widest transition-all">
                                            @{{ b }}
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">約打宣告 (Intro)</label>
                                <textarea v-model="profileForm.intro" rows="3" class="w-full px-5 py-4 bg-slate-50 border-2 border-transparent rounded-2xl focus:border-blue-500 outline-none font-bold text-sm leading-relaxed" placeholder="分享一下您的打法特色..."></textarea>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">約打費用說明</label>
                                <input v-model="profileForm.fee" type="text" class="w-full px-5 py-3.5 bg-slate-50 border-2 border-transparent rounded-2xl focus:border-blue-500 outline-none font-black italic text-base transition-all">
                            </div>
                        </div>

                        <div class="pt-4 flex gap-3">
                            <button @click="saveProfile" class="flex-1 py-4 bg-blue-600 text-white rounded-2xl font-black uppercase tracking-widest text-sm shadow-xl shadow-blue-500/20 hover:bg-blue-700 transition-all active:scale-95">
                                儲存變更
                            </button>
                            <button @click="isEditingProfile = false" class="px-8 py-4 bg-slate-100 text-slate-500 rounded-2xl font-black uppercase tracking-widest text-sm hover:bg-slate-200 transition-all">
                                取消
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Events Section -->
        <div class="space-y-6">
            <div class="flex items-center justify-between px-2 overflow-x-auto no-scrollbar border-b border-slate-100">
                <div class="flex flex-nowrap gap-x-6 sm:gap-x-8">
                    <button @click="profileTab = 'active'" 
                            :class="profileTab === 'active' ? 'text-blue-600' : 'text-slate-400 hover:text-slate-600'"
                            class="relative py-4 text-[11px] sm:text-sm font-black uppercase tracking-[0.2em] transition-all group whitespace-nowrap">
                        正在揪球
                        <div :class="['absolute bottom-0 left-0 right-0 h-1 bg-blue-600 rounded-full transition-all duration-300', profileTab === 'active' ? 'opacity-100 w-full' : 'opacity-0 w-0']"></div>
                    </button>
                    <button @click="profileTab = 'past'" 
                            :class="profileTab === 'past' ? 'text-blue-600' : 'text-slate-400 hover:text-slate-600'"
                            class="relative py-4 text-[11px] sm:text-sm font-black uppercase tracking-[0.2em] transition-all group whitespace-nowrap">
                        歷史紀錄
                        <div :class="['absolute bottom-0 left-0 right-0 h-1 bg-blue-600 rounded-full transition-all duration-300', profileTab === 'past' ? 'opacity-100 w-full' : 'opacity-0 w-0']"></div>
                    </button>
                    <button @click="profileTab = 'comments'; loadProfileComments()" 
                            :class="profileTab === 'comments' ? 'text-blue-600' : 'text-slate-400 hover:text-slate-600'"
                            class="relative py-4 text-[11px] sm:text-sm font-black uppercase tracking-[0.2em] transition-all group whitespace-nowrap flex items-center gap-1.5">
                        留言板
                        <div v-if="profileData.user?.player?.comments_count > 0" 
                             :class="profileTab === 'comments' ? 'bg-blue-100 text-blue-600' : 'bg-slate-100 text-slate-400'"
                             class="px-1.5 py-0.5 rounded-lg text-[10px] sm:text-[11px] font-black tracking-normal transition-colors">
                            @{{ profileData.user.player.comments_count }}
                        </div>
                        <div :class="['absolute bottom-0 left-0 right-0 h-1 bg-blue-600 rounded-full transition-all duration-300', profileTab === 'comments' ? 'opacity-100 w-full' : 'opacity-0 w-0']"></div>
                    </button>
                    <button v-if="profileData.status?.is_me" @click="profileTab = 'following'; loadFollowing()" 
                            :class="profileTab === 'following' ? 'text-blue-600' : 'text-slate-400 hover:text-slate-600'"
                            class="relative py-4 text-[11px] sm:text-sm font-black uppercase tracking-[0.2em] transition-all group whitespace-nowrap">
                        我的追蹤
                        <div :class="['absolute bottom-0 left-0 right-0 h-1 bg-blue-600 rounded-full transition-all duration-300', profileTab === 'following' ? 'opacity-100 w-full' : 'opacity-0 w-0']"></div>
                    </button>
                </div>
            </div>

            <!-- Event List (Active/Past) -->
            <div v-if="['active', 'past'].includes(profileTab)">
                <div v-if="profileEvents.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 animate__animated animate__fadeIn">
                    <div v-for="event in profileEvents" :key="event.id" 
                         @click="openEventDetail(event)"
                         class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-sm hover:border-blue-200 hover:shadow-md transition-all cursor-pointer group">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-center gap-2">
                                <span :class="event.match_type === 'match' ? 'bg-blue-50 text-blue-600' : 'bg-emerald-50 text-emerald-600'" 
                                      class="px-2 py-1 text-[9px] font-black rounded-lg uppercase tracking-widest italic">
                                    @{{ event.match_type === 'match' ? '比賽' : (event.match_type === 'practice' ? '練習' : '不限') }}
                                </span>
                            </div>
                            <span class="text-[9px] font-black text-slate-300 uppercase tracking-widest">@{{ formatDate(event.event_date) }}</span>
                        </div>
                        <h4 class="font-black text-slate-900 text-lg mb-4 group-hover:text-blue-600 transition-colors leading-tight">@{{ event.title }}</h4>
                        <div class="flex items-center justify-between pt-4 border-t border-slate-50">
                            <div class="flex items-center gap-1.5 text-[10px] font-bold text-slate-400">
                                <app-icon name="map-pin" class-name="w-3.5 h-3.5"></app-icon>
                                @{{ event.location }}
                            </div>
                            <div class="flex items-center gap-1.5 text-[10px] font-black text-blue-600">
                                <app-icon name="users" class-name="w-3.5 h-3.5"></app-icon>
                                @{{ event.confirmed_participants_count }}/@{{ event.max_participants === 0 ? '∞' : event.max_participants }}
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="py-20 text-center bg-white rounded-[40px] border border-slate-100 shadow-sm animate__animated animate__fadeIn">
                    <div class="w-20 h-20 bg-slate-50 rounded-[32px] flex items-center justify-center mx-auto mb-6">
                        <app-icon name="calendar" class-name="w-10 h-10 text-slate-200"></app-icon>
                    </div>
                    <p class="text-slate-400 font-black uppercase tracking-widest text-xs">目前沒有相關活動</p>
                </div>
                <!-- Load More -->
                <button v-if="profileEventsHasMore" @click="loadProfileEvents(true)" 
                        class="w-full mt-6 py-5 bg-white border border-slate-100 text-blue-600 text-xs font-black uppercase tracking-[0.2em] hover:bg-blue-50 rounded-[28px] transition-all shadow-sm">
                    載入更多活動
                </button>
            </div>

            <!-- Comments Tab -->
            <div v-if="profileTab === 'comments'" class="space-y-6 animate__animated animate__fadeIn">
                <div class="bg-white p-6 sm:p-7 rounded-[32px] border border-slate-100 shadow-sm">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-2">
                            <div class="w-1 h-3 bg-blue-600 rounded-full"></div>
                            <span class="text-[10px] font-black uppercase tracking-[0.1em] text-slate-400 italic">球友留言板 / Comments</span>
                        </div>
                        <span class="text-[9px] font-black text-slate-300">@{{ profileComments.length }} COMMENTS</span>
                    </div>

                    <!-- Comment Input -->
                    <div class="bg-white rounded-[32px] p-2 sm:p-4">
                        <div class="flex gap-3 mb-6 px-2">
                            <div class="w-10 h-10 rounded-full overflow-hidden bg-slate-100 shrink-0 border border-slate-100">
                                <img v-if="currentUser?.line_picture_url" :src="currentUser.line_picture_url" class="w-full h-full object-cover">
                                <app-icon v-else name="user" class-name="w-full h-full text-slate-300 p-2"></app-icon>
                            </div>
                            <div class="flex-1 relative">
                                <textarea v-model="playerCommentDraft" 
                                    rows="1" maxlength="200" :disabled="!isLoggedIn"
                                    @keyup.enter.prevent="submitPlayerComment(profileData.user.player.id)"
                                    placeholder="對這位球友有什麼想說的嗎..." 
                                    class="w-full bg-slate-50 border-none rounded-2xl px-5 py-3 pr-12 text-sm font-bold focus:bg-slate-100 outline-none transition-all placeholder:text-slate-300 resize-none overflow-hidden disabled:opacity-60"></textarea>
                                <button @click="submitPlayerComment(profileData.user.player.id)" :disabled="!playerCommentDraft.trim()" class="absolute right-2 top-1.5 p-2 text-blue-600 disabled:opacity-20 transition-all">
                                    <app-icon name="send" class-name="w-5 h-5"></app-icon>
                                </button>
                            </div>
                        </div>

                        <!-- Comment List -->
                        <div class="max-h-[500px] overflow-y-auto pr-2 no-scrollbar">
                            <div v-if="profileComments.length > 0" class="space-y-1">
                                <div v-for="(c, index) in profileComments" :key="c.id" class="comment-threads">
                                    <div class="avatar-container">
                                        <div class="avatar cursor-pointer" @click="openProfile(c.user.uid)">
                                            <img v-if="c.user.line_picture_url" :src="c.user.line_picture_url" class="w-full h-full object-cover">
                                            <app-icon v-else name="user" class-name="w-full h-full text-slate-200 p-2"></app-icon>
                                        </div>
                                        <div v-if="index < profileComments.length - 1" class="thread-line"></div>
                                    </div>
                                    <div class="content-container">
                                        <div class="header">
                                            <div class="flex items-center gap-2">
                                                <span class="username cursor-pointer hover:underline" @click="openProfile(c.user.uid)">@{{ c.user.name }}</span>
                                                <span class="timestamp">@{{ formatDate(c.at) }}</span>
                                            </div>
                                            <button v-if="currentUser && c.user?.uid === currentUser.uid" type="button"
                                                @click="deletePlayerComment(c.id)"
                                                class="p-1 text-slate-300 hover:text-red-500 transition-colors">
                                                <app-icon name="trash" class-name="w-3 h-3"></app-icon>
                                            </button>
                                        </div>
                                        <div class="text">@{{ c.text }}</div>
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

            <!-- Following Tab -->
            <div v-if="profileTab === 'following'" class="animate__animated animate__fadeIn">
                <div v-if="followingUsers.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div v-for="u in followingUsers" :key="u.id" 
                         @click="showDetail(u)"
                         class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-sm hover:border-blue-200 hover:shadow-md transition-all cursor-pointer flex items-center gap-4">
                        <div class="w-16 h-16 rounded-2xl overflow-hidden bg-slate-50 border-2 border-white shadow-sm shrink-0">
                            <img v-if="u.photo_url || u.photo" :src="u.photo_url || u.photo" class="w-full h-full object-cover">
                            <app-icon v-else name="user" class-name="w-full h-full text-slate-200 p-3"></app-icon>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <div class="text-slate-900 font-black text-lg leading-tight">@{{ u.name }}</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 bg-blue-50 text-blue-600 text-[8px] font-black rounded uppercase italic" v-if="u.level">NTRP @{{ u.level }}</span>
                                <span class="text-slate-400 text-[9px] font-bold">@{{ u.region }}</span>
                            </div>
                        </div>
                        <app-icon name="chevron-right" class-name="w-5 h-5 text-slate-300"></app-icon>
                    </div>
                </div>
                <div v-else class="py-20 text-center bg-white rounded-[40px] border border-slate-100">
                    <app-icon name="users" class-name="w-12 h-12 text-slate-100 mx-auto mb-4"></app-icon>
                    <p class="text-slate-400 font-black uppercase tracking-widest text-xs">目前沒有追蹤任何人</p>
                </div>
            </div>
        </div>
    </div>
</div>
