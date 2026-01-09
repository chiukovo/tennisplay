<div v-if="view === 'profile'" class="min-h-screen bg-slate-50/50 pb-20">
    <!-- Sticky Header -->
    <div class="bg-white/80 backdrop-blur-xl border-b sticky top-0 z-[100]">
        <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <button @click="navigateTo('home')" class="p-2 hover:bg-slate-100 rounded-xl transition-colors">
                    <app-icon name="arrow-left" class-name="w-5 h-5 text-slate-600"></app-icon>
                </button>
                <h1 class="text-lg font-black italic uppercase tracking-tight text-slate-900">個人主頁</h1>
            </div>
            <div v-if="profileData.status?.is_me" class="flex items-center gap-2">
                <button v-if="!isEditingProfile" @click="isEditingProfile = true" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-slate-50 transition-all shadow-sm">
                    編輯資料
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 py-8 space-y-8">
        <!-- Unified Profile Header -->
        <div class="bg-white rounded-[40px] p-6 sm:p-10 border border-slate-100 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 right-0 w-96 h-96 bg-blue-50/50 blur-3xl rounded-full -mr-48 -mt-48"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row gap-8 lg:gap-12">
                <!-- Left: Player Card -->
                <div class="w-full md:w-[260px] lg:w-[300px] shrink-0">
                    <div v-if="profileData.user.player" class="relative group">
                        <player-card :player="profileData.user.player" @click="showDetail(profileData.user.player)"></player-card>
                        
                        <!-- Edit Overlay for Me -->
                        <div v-if="profileData.status.is_me" class="absolute inset-0 bg-black/40 backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center rounded-[24px] z-20">
                            <button @click="editCard(profileData.user.player)" class="bg-white text-slate-900 px-6 py-3 rounded-xl font-black uppercase tracking-widest text-[10px] shadow-2xl hover:scale-105 transition-all flex items-center gap-2">
                                <app-icon name="edit-3" class-name="w-4 h-4"></app-icon> 編輯樣式
                            </button>
                        </div>
                        
                        <!-- Zoom Hint -->
                        <div class="absolute bottom-4 right-4 p-2 bg-white/90 backdrop-blur rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-all transform translate-y-2 group-hover:translate-y-0 z-10 pointer-events-none">
                            <app-icon name="maximize" class-name="w-3 h-3 text-slate-600"></app-icon>
                        </div>
                    </div>
                    
                    <!-- Empty State -->
                    <div v-else-if="profileData.status?.is_me" class="aspect-[2.5/3.8] bg-slate-50 rounded-[24px] flex flex-col items-center justify-center border-2 border-dashed border-slate-200 p-6 text-center">
                        <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center mb-3">
                            <app-icon name="plus" class-name="w-6 h-6 text-blue-500"></app-icon>
                        </div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">尚未建立球友卡</p>
                        <button @click="navigateTo('create')" class="px-6 py-2.5 bg-blue-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-blue-500/20">立即建立</button>
                    </div>

                    <!-- Card Stats for Others -->
                    <div v-if="profileData.user.player" class="mt-4 flex items-center justify-between px-2">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 bg-blue-600 text-white text-[8px] font-black rounded uppercase tracking-widest italic">PRO CARD</span>
                        </div>
                        <button @click="toggleLike" class="flex items-center gap-1.5 text-xs font-black" :class="profileData.status.is_liked ? 'text-pink-500' : 'text-slate-400'">
                            <app-icon name="heart" :fill="profileData.status.is_liked ? 'currentColor' : 'none'" class-name="w-4 h-4"></app-icon>
                            <span>@{{ profileData.stats.likes_count }}</span>
                        </button>
                    </div>
                </div>

                <!-- Right: Info & Stats -->
                <div class="flex-1 flex flex-col">
                    <div v-if="!isEditingProfile" class="h-full flex flex-col">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-8">
                            <div class="space-y-3">
                                <h2 class="text-4xl sm:text-5xl lg:text-6xl font-black italic uppercase tracking-tighter text-slate-900 leading-none">@{{ profileData.user.name }}</h2>
                                <div class="flex flex-wrap gap-2">
                                    <span v-if="profileData.user.gender" class="px-3 py-1 bg-slate-100 rounded-full text-[10px] font-black text-slate-600 uppercase tracking-widest flex items-center gap-1.5">
                                        <app-icon name="gender" class-name="w-3 h-3"></app-icon>@{{ profileData.user.gender }}
                                    </span>
                                    <span v-if="profileData.user.region" class="px-3 py-1 bg-blue-50 rounded-full text-[10px] font-black text-blue-600 uppercase tracking-widest flex items-center gap-1.5">
                                        <app-icon name="map-pin" class-name="w-3 h-3"></app-icon>@{{ profileData.user.region }}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div v-if="!profileData.status?.is_me" class="flex gap-3">
                                <button @click="toggleFollow" 
                                        :class="profileData.status.is_following ? 'bg-slate-100 text-slate-600' : 'bg-blue-600 text-white shadow-lg shadow-blue-500/20'"
                                        class="px-8 py-3.5 rounded-2xl font-black uppercase tracking-widest text-xs transition-all active:scale-95">
                                    @{{ profileData.status.is_following ? '已追蹤' : '追蹤球友' }}
                                </button>
                                <button @click="openMessage({from_user_id: profileData.user.id, sender: profileData.user})" class="px-6 py-3.5 bg-white border border-slate-200 text-slate-700 rounded-2xl font-black uppercase tracking-widest text-xs hover:bg-slate-50 transition-all flex items-center gap-2">
                                    <app-icon name="mail" class-name="w-4 h-4"></app-icon>
                                </button>
                            </div>
                        </div>

                        <!-- Bio -->
                        <div class="bg-slate-50/50 p-6 rounded-[28px] border border-slate-100/50 flex-1 mb-8">
                            <div class="flex items-center gap-2 mb-3">
                                <div class="w-1 h-3 bg-blue-600 rounded-full"></div>
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 italic">自我介紹 / BIO</span>
                            </div>
                            <p class="text-slate-600 font-bold leading-relaxed italic whitespace-pre-line">
                                「@{{ profileData.user.bio || '這位球友很低調，什麼都沒留下...' }}」
                            </p>
                        </div>

                        <!-- Stats -->
                        <div class="flex gap-8 sm:gap-12">
                            <div class="text-center">
                                <div class="text-2xl sm:text-3xl font-black text-slate-900">@{{ profileData.stats.followers_count }}</div>
                                <div class="text-[9px] text-slate-400 font-black uppercase tracking-widest">粉絲</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl sm:text-3xl font-black text-slate-900">@{{ profileData.stats.following_count }}</div>
                                <div class="text-[9px] text-slate-400 font-black uppercase tracking-widest">追蹤</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl sm:text-3xl font-black text-slate-900">@{{ profileData.stats.likes_count }}</div>
                                <div class="text-[9px] text-slate-400 font-black uppercase tracking-widest">獲讚</div>
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
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">主要活動地區</label>
                            <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-6 gap-2 max-h-[160px] overflow-y-auto no-scrollbar p-1">
                                <button v-for="r in regions" :key="r" @click="profileForm.region = r"
                                        :class="profileForm.region === r ? 'bg-blue-600 text-white shadow-md' : 'bg-slate-50 text-slate-400'"
                                        class="py-2.5 rounded-xl font-bold text-[10px] transition-all">
                                    @{{ r }}
                                </button>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">個人簡介</label>
                            <textarea v-model="profileForm.bio" rows="3" class="w-full px-5 py-4 bg-slate-50 border-2 border-transparent rounded-2xl focus:border-blue-500 outline-none font-bold text-sm leading-relaxed" placeholder="介紹一下你的網球經歷吧..."></textarea>
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
            <div class="flex items-center justify-between px-2">
                <div class="flex gap-8">
                    <button @click="profileTab = 'active'" 
                            :class="profileTab === 'active' ? 'text-blue-600' : 'text-slate-400 hover:text-slate-600'"
                            class="relative py-2 text-sm font-black uppercase tracking-[0.2em] transition-all group">
                        正在揪球
                        <div :class="['absolute bottom-0 left-0 right-0 h-1 bg-blue-600 rounded-full transition-all duration-300', profileTab === 'active' ? 'opacity-100 w-full' : 'opacity-0 w-0']"></div>
                    </button>
                    <button @click="profileTab = 'past'" 
                            :class="profileTab === 'past' ? 'text-blue-600' : 'text-slate-400 hover:text-slate-600'"
                            class="relative py-2 text-sm font-black uppercase tracking-[0.2em] transition-all group">
                        歷史紀錄
                        <div :class="['absolute bottom-0 left-0 right-0 h-1 bg-blue-600 rounded-full transition-all duration-300', profileTab === 'past' ? 'opacity-100 w-full' : 'opacity-0 w-0']"></div>
                    </button>
                </div>
            </div>

            <!-- Event List -->
            <div v-if="profileEvents.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
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
            
            <div v-else class="py-20 text-center bg-white rounded-[40px] border border-slate-100 shadow-sm">
                <div class="w-20 h-20 bg-slate-50 rounded-[32px] flex items-center justify-center mx-auto mb-6">
                    <app-icon name="calendar" class-name="w-10 h-10 text-slate-200"></app-icon>
                </div>
                <p class="text-slate-400 font-black uppercase tracking-widest text-xs">目前沒有相關活動</p>
            </div>

            <!-- Load More -->
            <button v-if="profileEventsHasMore" @click="loadProfileEvents(true)" 
                    class="w-full py-5 bg-white border border-slate-100 text-blue-600 text-xs font-black uppercase tracking-[0.2em] hover:bg-blue-50 rounded-[28px] transition-all shadow-sm">
                載入更多活動
            </button>
        </div>
    </div>
</div>
