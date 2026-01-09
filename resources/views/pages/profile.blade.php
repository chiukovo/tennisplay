<div v-if="view === 'profile'" class="min-h-screen bg-gray-50 pb-20">
    <!-- Profile Header -->
    <div class="bg-white border-b sticky top-0 z-10">
        <div class="max-w-4xl mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <button @click="navigateTo('home')" class="p-2 hover:bg-gray-100 rounded-full">
                    <app-icon name="arrow-left" class-name="w-5 h-5 text-gray-600"></app-icon>
                </button>
                <h1 class="text-xl font-bold text-gray-800">個人主頁</h1>
            </div>
            <div v-if="profileData.status && profileData.status.is_me" class="flex space-x-2">
                <button v-if="!isEditingProfile" @click="isEditingProfile = true" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-lg text-sm font-bold hover:bg-gray-50 transition">
                    編輯資料
                </button>
                <button v-if="isEditingProfile" @click="saveProfile" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 transition">
                    儲存資料
                </button>
                <button v-if="isEditingProfile" @click="isEditingProfile = false" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm font-bold hover:bg-gray-200 transition">
                    取消
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 py-6 space-y-6">
        <!-- User Info Card -->
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100">
            <div class="p-6 flex flex-col md:flex-row items-center md:items-start gap-6">
                <div class="relative shrink-0">
                    <img :src="profileData.user.line_picture_url || 'https://via.placeholder.com/100'" 
                         class="w-24 h-24 rounded-full border-4 border-blue-50 shadow-sm object-cover" alt="">
                    <div v-if="profileData.user.player && profileData.user.player.is_verified" 
                         class="absolute bottom-0 right-0 bg-blue-500 text-white p-1 rounded-full border-2 border-white">
                        <app-icon name="check" class-name="w-3 h-3"></app-icon>
                    </div>
                </div>

                <div class="flex-1 text-center md:text-left space-y-4">
                    <div v-if="!isEditingProfile">
                        <h2 class="text-2xl font-bold text-gray-900">@{{ profileData.user.name }}</h2>
                        <div class="flex flex-wrap justify-center md:justify-start gap-2 mt-2">
                            <span v-if="profileData.user.gender" class="px-2 py-1 bg-slate-100 rounded-lg text-[10px] font-bold text-slate-600">
                                <app-icon name="gender" class-name="w-3 h-3 inline mr-1"></app-icon>@{{ profileData.user.gender }}
                            </span>
                            <span v-if="profileData.user.region" class="px-2 py-1 bg-slate-100 rounded-lg text-[10px] font-bold text-slate-600">
                                <app-icon name="map-pin" class-name="w-3 h-3 inline mr-1"></app-icon>@{{ profileData.user.region }}
                            </span>
                        </div>
                        <p class="text-gray-600 text-sm mt-3 italic">@{{ profileData.user.bio || '尚未填寫自我介紹...' }}</p>
                    </div>

                    <!-- Edit Mode -->
                    <div v-else class="space-y-4 animate__animated animate__fadeIn">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">暱稱</label>
                                <input v-model="profileForm.name" type="text" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:border-blue-500 outline-none font-bold">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">性別</label>
                                <div class="flex gap-2">
                                    <button v-for="g in ['男', '女']" :key="g" @click="profileForm.gender = g"
                                            :class="profileForm.gender === g ? 'bg-blue-600 text-white' : 'bg-gray-50 text-gray-400'"
                                            class="flex-1 py-2 rounded-xl font-bold text-sm transition-all">
                                        @{{ g }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">活動地區</label>
                            <select v-model="profileForm.region" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:border-blue-500 outline-none font-bold">
                                <option v-for="r in regions" :key="r" :value="r">@{{ r }}</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">自我介紹</label>
                            <textarea v-model="profileForm.bio" rows="3" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:border-blue-500 outline-none font-bold text-sm" placeholder="介紹一下你的網球經歷吧..."></textarea>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="flex items-center space-x-8 pt-4 justify-center md:justify-start border-t border-gray-50">
                        <div class="text-center md:text-left">
                            <div class="text-lg font-black text-gray-900">@{{ profileData.stats.followers_count }}</div>
                            <div class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">追蹤者</div>
                        </div>
                        <div class="text-center md:text-left">
                            <div class="text-lg font-black text-gray-900">@{{ profileData.stats.following_count }}</div>
                            <div class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">追蹤中</div>
                        </div>
                        <div class="text-center md:text-left">
                            <div class="text-lg font-black text-gray-900">@{{ profileData.stats.likes_count }}</div>
                            <div class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">獲讚數</div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons (for others) -->
                <div v-if="profileData.status && !profileData.status.is_me" class="flex flex-col gap-3 shrink-0">
                    <button @click="toggleFollow" 
                            :class="profileData.status.is_following ? 'bg-gray-100 text-gray-700' : 'bg-blue-600 text-white'"
                            class="px-8 py-3 rounded-xl font-bold transition-all active:scale-95 shadow-lg shadow-blue-500/10">
                        @{{ profileData.status.is_following ? '已追蹤' : '追蹤球友' }}
                    </button>
                    <button @click="openMessage({from_user_id: profileData.user.id, sender: profileData.user})" class="px-8 py-3 bg-white border border-gray-200 text-gray-700 rounded-xl font-bold hover:bg-gray-50 transition-all active:scale-95 flex items-center justify-center gap-2">
                        <app-icon name="mail" class-name="w-4 h-4"></app-icon> 發送訊息
                    </button>
                </div>
            </div>
        </div>

        <!-- Player Card Section -->
        <div v-if="profileData.user.player" class="space-y-4">
            <div class="flex items-center justify-between px-1">
                <div class="flex items-center gap-2">
                    <h3 class="text-lg font-bold text-gray-800">專屬球友卡</h3>
                    <span class="px-2 py-0.5 bg-blue-600 text-white text-[8px] font-black rounded uppercase tracking-widest italic">PRO CARD</span>
                </div>
                <div class="flex items-center gap-3">
                    <button v-if="profileData.status.is_me" @click="deleteCard(profileData.user.player.id)" class="p-2 text-gray-300 hover:text-red-500 transition-colors" title="刪除球友卡">
                        <app-icon name="trash" class-name="w-5 h-5"></app-icon>
                    </button>
                    <button @click="toggleLike" class="flex items-center space-x-1 text-sm" :class="profileData.status.is_liked ? 'text-pink-500' : 'text-gray-400'">
                        <app-icon :name="profileData.status.is_liked ? 'star' : 'star'" :fill="profileData.status.is_liked ? 'currentColor' : 'none'" class-name="w-5 h-5"></app-icon>
                        <span class="font-bold">@{{ profileData.stats.likes_count }}</span>
                    </button>
                </div>
            </div>
            
            <div class="max-w-sm mx-auto relative group">
                <player-card :player="profileData.user.player"></player-card>
                
                <!-- Edit Overlay for Me -->
                <div v-if="profileData.status.is_me" class="absolute inset-0 bg-black/40 backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center rounded-[24px] z-20">
                    <button @click="editMyCard" class="bg-white text-slate-900 px-6 py-3 rounded-xl font-black uppercase tracking-widest text-xs shadow-2xl hover:scale-105 transition-all flex items-center gap-2">
                        <app-icon name="edit-3" class-name="w-4 h-4"></app-icon> 編輯球友卡樣式
                    </button>
                </div>
            </div>
        </div>
        <div v-else-if="profileData.status && profileData.status.is_me" class="bg-blue-50 rounded-3xl p-10 text-center border-2 border-dashed border-blue-200">
            <div class="w-20 h-20 bg-blue-100 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-inner">
                <app-icon name="plus" class-name="w-10 h-10 text-blue-500"></app-icon>
            </div>
            <h3 class="text-xl font-black text-blue-900 mb-2">尚未建立球友卡</h3>
            <p class="text-blue-600 font-medium mb-8 max-w-xs mx-auto">建立一張專屬球友卡，展現你的網球實力，讓更多球友找到你！</p>
            
            <div v-if="!profileData.user.gender || !profileData.user.region" class="mb-6 p-4 bg-amber-50 border border-amber-100 rounded-2xl text-amber-700 text-xs font-bold">
                <app-icon name="help" class-name="w-4 h-4 inline mr-1"></app-icon>
                請先填寫上方「基本資料」後再創立球友卡
            </div>

            <button @click="navigateTo('create')" 
                    :disabled="!profileData.user.gender || !profileData.user.region"
                    :class="(!profileData.user.gender || !profileData.user.region) ? 'opacity-50 cursor-not-allowed' : 'hover:bg-blue-700 shadow-blue-200'"
                    class="px-10 py-4 bg-blue-600 text-white rounded-2xl font-black uppercase tracking-widest shadow-xl transition-all active:scale-95">
                立即建立球友卡
            </button>
        </div>

        <!-- Events Section -->
        <div class="space-y-4">
            <!-- Tabs -->
            <div class="flex border-b border-gray-200">
                <button @click="profileTab = 'active'" 
                        :class="profileTab === 'active' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        class="flex-1 py-4 text-center text-sm font-black border-b-2 transition-all uppercase tracking-widest">
                    正在揪球
                </button>
                <button @click="profileTab = 'past'" 
                        :class="profileTab === 'past' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        class="flex-1 py-4 text-center text-sm font-black border-b-2 transition-all uppercase tracking-widest">
                    歷史紀錄
                </button>
            </div>

            <!-- Event List -->
            <div v-if="profileEvents.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div v-for="event in profileEvents" :key="event.id" 
                     @click="openEventDetail(event)"
                     class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:border-blue-200 transition-all active:scale-[0.98] group">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center gap-2">
                            <span :class="event.match_type === 'match' ? 'bg-blue-50 text-blue-600' : 'bg-emerald-50 text-emerald-600'" 
                                  class="px-2 py-1 text-[10px] font-black rounded uppercase tracking-widest">
                                @{{ event.match_type === 'match' ? '比賽' : (event.match_type === 'practice' ? '練習' : '不限') }}
                            </span>
                            <span v-if="event.status === 'completed'" class="px-2 py-1 bg-gray-100 text-gray-500 text-[10px] font-black rounded uppercase tracking-widest">
                                已結束
                            </span>
                        </div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">@{{ formatEventDate(event.event_date) }}</span>
                    </div>
                    <h4 class="font-black text-gray-900 text-lg mb-3 group-hover:text-blue-600 transition-colors">@{{ event.title }}</h4>
                    <div class="flex items-center gap-4 text-xs font-bold text-gray-400">
                        <div class="flex items-center gap-1">
                            <app-icon name="map-pin" class-name="w-3.5 h-3.5"></app-icon>
                            @{{ event.location }}
                        </div>
                        <div class="flex items-center gap-1">
                            <app-icon name="users" class-name="w-3.5 h-3.5"></app-icon>
                            @{{ event.confirmed_participants_count }}/@{{ event.max_participants === 0 ? '∞' : event.max_participants }}
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="py-16 text-center bg-white rounded-3xl border border-gray-100">
                <div class="text-gray-200 mb-4"><app-icon name="calendar" class-name="w-12 h-12 mx-auto"></app-icon></div>
                <p class="text-gray-400 font-bold text-sm">目前沒有相關活動</p>
            </div>

            <!-- Load More -->
            <button v-if="profileEventsHasMore" @click="loadProfileEvents(true)" 
                    class="w-full py-4 text-blue-600 text-sm font-black hover:bg-blue-50 rounded-2xl transition uppercase tracking-widest">
                載入更多活動
            </button>
        </div>
    </div>
</div>
