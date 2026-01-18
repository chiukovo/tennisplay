{{-- Instant Play View --}}
<div v-if="view === 'instant-play'" class="h-[calc(100vh-140px)] sm:h-[calc(100vh-160px)] flex flex-col -mx-4 sm:mx-0">
    
    {{-- Lobby View: Room Selection --}}
    <div v-if="!currentRoom" class="flex-grow overflow-y-auto no-scrollbar pb-10 px-2 sm:px-0 overscroll-contain touch-pan-y">
        <div class="space-y-4 pt-2">
            {{-- Header & Stats --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 pb-2">
                <div>
                    <h2 class="text-[40px] sm:text-6xl font-[1000] italic uppercase tracking-tighter leading-[0.85] text-slate-900 mb-2">
                        我要打球 <span class="text-blue-600 block sm:inline">RIGHT NOW</span>
                    </h2>
                    <div class="flex items-center gap-2">
                        <div class="flex h-2 w-2 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                        </div>
                        <p class="text-slate-500 font-bold text-xs uppercase tracking-[0.2em]">
                            目前有 <span class="text-blue-600">@{{ globalInstantStats.display_count }}</span> 位球友正在線等待中
                        </p>
                    </div>
                </div>

                {{-- LFG Toggle Switch --}}
                <div @click="toggleLfg()" 
                    class="group relative flex items-center gap-4 bg-white border-2 p-3 pr-5 rounded-[24px] cursor-pointer transition-all duration-300 active:scale-95 shadow-sm"
                    :class="isLfg ? 'border-blue-500 bg-blue-50/30' : 'border-slate-100 hover:border-slate-200'">
                    <div class="relative w-12 h-6 rounded-full transition-colors duration-300"
                        :class="isLfg ? 'bg-blue-600' : 'bg-slate-200'">
                        <div class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full transition-transform duration-300 shadow-sm"
                            :class="isLfg ? 'translate-x-6' : 'translate-x-0'"></div>
                    </div>
                    <div>
                        <div class="text-[10px] font-black uppercase tracking-widest leading-none mb-0.5"
                            :class="isLfg ? 'text-blue-600' : 'text-slate-400'">Connect Status</div>
                        <div class="text-sm font-black text-slate-900 leading-none">
                            @{{ isLfg ? '我現在想打球！' : '開啟「想打球」狀態' }}
                        </div>
                    </div>
                    <!-- Pulsing Glow for LFG -->
                    <div v-if="isLfg" class="absolute -inset-0.5 bg-blue-500/20 rounded-[24px] animate-pulse -z-10"></div>
                </div>
            </div>

            {{-- Global Hub Area (New) --}}
            <div class="space-y-4 mb-8">
                {{-- Global Activity Ticker --}}
                <div class="bg-slate-900 rounded-[32px] p-1 pr-6 flex items-center gap-4 overflow-hidden border border-slate-800 shadow-xl">
                    <div class="shrink-0 bg-blue-600 text-white px-4 py-3 rounded-[28px] flex items-center gap-2 font-black italic tracking-tighter text-sm uppercase">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-white"></span>
                        </span>
                        LIVE FEED
                    </div>
                    <div class="flex-grow overflow-hidden relative h-6">
                        <transition-group name="slide-up">
                            <div v-for="(msg, idx) in globalData.recent_messages.slice(0, 1)" :key="msg.id" 
                                @click="joinBySlug(msg.room.slug)"
                                class="flex items-center gap-2 cursor-pointer hover:opacity-80 transition-all absolute inset-0">
                                <span class="text-[10px] font-black text-blue-400 uppercase tracking-widest shrink-0">[@{{ msg.room.name }}]</span>
                                <span class="text-xs font-bold text-slate-400 shrink-0">@{{ msg.user.name }}:</span>
                                <span class="text-xs font-semibold text-white truncate">@{{ msg.content }}</span>
                            </div>
                        </transition-group>
                        <div v-if="!globalData.recent_messages.length" class="text-xs font-bold text-slate-500 italic">全台灣目前正在暖身中...</div>
                    </div>
                    <app-icon name="chevron-right" class-name="w-4 h-4 text-slate-600 shrink-0"></app-icon>
                </div>

                {{-- Live Players Row --}}
                <div class="relative">
                    <div class="flex items-center justify-between mb-2 px-1">
                        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em]">Currently Online</h4>
                        <div class="text-[9px] font-bold text-blue-500 uppercase">@{{ globalData.lfg_users.length }} 人想打球</div>
                    </div>
                    <div class="flex gap-4 overflow-x-auto no-scrollbar pb-4 px-4 -mx-4">
                        {{-- LFG Users First (Glowing) --}}
                        <div v-for="user in globalData.lfg_users" :key="'lfg-'+user.id" 
                            @click="openProfile(user.uid)"
                            class="flex flex-col items-center gap-2 shrink-0 group cursor-pointer p-1">
                            <div class="relative">
                                <div class="absolute -inset-1 bg-gradient-to-tr from-blue-600 to-indigo-400 rounded-full animate-spin-slow opacity-70 blur-[2px]"></div>
                                <img :src="user.avatar" class="relative w-12 h-12 rounded-full border-2 border-white object-cover shadow-sm bg-slate-100 group-hover:scale-110 transition-transform">
                                <div class="absolute bottom-0 right-0 bg-blue-600 text-[7px] font-black text-white px-1.5 py-0.5 rounded-full border-2 border-white uppercase shadow-lg z-20">LIVE</div>
                            </div>
                            <span class="text-[9px] font-black text-slate-900 max-w-[56px] truncate">@{{ user.name }}</span>
                        </div>

                        {{-- Others (Presence) --}}
                        <div v-for="user in globalInstantStats.avatars.filter(a => !globalData.lfg_users.some(l => String(l.uid) === String(a.uid)))" :key="'pres-'+user.uid" 
                            @click="openProfile(user.uid)"
                            class="flex flex-col items-center gap-2 shrink-0 group cursor-pointer p-1 opacity-60 hover:opacity-100 transition-opacity">
                            <img :src="user.avatar" class="w-12 h-12 rounded-full border-2 border-white object-cover shadow-sm bg-slate-100 group-hover:scale-110 transition-transform">
                            <span class="text-[9px] font-bold text-slate-500 max-w-[56px] truncate">@{{ user.name || '球友' }}</span>
                        </div>

                        <div v-if="!globalData.lfg_users.length && !globalInstantStats.avatars.length" class="flex items-center gap-3 p-3 bg-slate-50 rounded-[20px] border border-dashed border-slate-200 w-full">
                            <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center">
                                <app-icon name="users" class-name="w-3 h-3 text-slate-300"></app-icon>
                            </div>
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">目前沒有人在線上</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Smart Lobby: Search & Tabs --}}
            <div class="space-y-4 mb-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    {{-- Search Bar --}}
                    <div class="relative flex-grow max-w-md group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <app-icon name="search" class-name="w-4 h-4 text-slate-400 group-focus-within:text-blue-500 transition-colors"></app-icon>
                        </div>
                        <input v-model="roomSearch" 
                            type="text" 
                            placeholder="搜尋區域 (例：台北...)" 
                            class="w-full bg-slate-100/50 border-2 border-transparent focus:border-blue-500/20 focus:bg-white rounded-[20px] pl-11 pr-4 py-3 text-sm font-bold text-slate-700 placeholder-slate-400 transition-all outline-none">
                        <button v-if="roomSearch" @click="roomSearch = ''" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-300 hover:text-slate-500 transition-colors">
                            <app-icon name="x" class-name="w-4 h-4"></app-icon>
                        </button>
                    </div>

                    {{-- Category Tabs --}}
                    <div class="flex items-center gap-1 bg-slate-100/80 p-1 rounded-[22px] overflow-x-auto no-scrollbar shrink-0">
                        <button v-for="cat in ['全部', '北部', '中部', '南部', '東部/離島']" :key="cat"
                            @click="roomCategory = cat"
                            :class="['px-5 py-2.5 rounded-[18px] text-xs font-black uppercase tracking-widest transition-all whitespace-nowrap',
                                roomCategory === cat ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-400 hover:text-slate-600']">
                            @{{ cat }}
                        </button>
                    </div>
                </div>
            </div>

            {{-- Region Grid --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 pb-20">
                <button v-for="room in sortedAndFilteredRooms" :key="room.id" 
                    @click="selectRoom(room)"
                    class="group bg-white border border-slate-100 p-4 sm:p-5 rounded-[32px] shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all text-left relative overflow-hidden"
                    :class="{'border-blue-500/30 ring-4 ring-blue-500/5': room.active_count > 0 || room.last_message}">
                    
                    {{-- Activity Indicator for Active Rooms --}}
                    <div v-if="room.active_count > 0 || room.last_message" class="absolute top-0 left-0 w-1 h-full bg-blue-500"></div>

                    <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50/50 rounded-full -mr-12 -mt-12 group-hover:scale-125 transition-transform duration-500"></div>
                    <div class="relative">
                        <div class="text-[10px] font-black text-blue-600 uppercase tracking-[0.2em] mb-1">REGION</div>
                        <div class="text-lg sm:text-xl font-black text-slate-900 mb-1">@{{ room.name }}</div>
                        
                        {{-- Last Message Preview --}}
                        <div v-if="room.last_message" class="mt-2 mb-3 p-2 bg-slate-50 rounded-xl border border-slate-100">
                            <p class="text-xs text-slate-600 font-semibold line-clamp-2 leading-relaxed">
                                <span class="text-blue-600">@{{ room.last_message_by }}:</span> @{{ room.last_message }}
                            </p>
                            <p class="text-[9px] text-slate-400 font-bold mt-1">@{{ formatDate(room.last_message_at) }}</p>
                        </div>
                        <div v-else class="mt-2 mb-3 p-2 bg-slate-50/50 rounded-xl border border-dashed border-slate-200 text-center">
                            <p class="text-[10px] text-slate-300 font-bold uppercase tracking-widest">尚無訊息</p>
                        </div>
                        
                        <div class="flex flex-col gap-2">
                            <!-- Room Avatars -->
                            <div class="flex items-center -space-x-2" v-if="room.active_avatars && room.active_avatars.length">
                                <img v-for="(u, idx) in room.active_avatars" :key="idx" 
                                    :src="u.avatar" 
                                    class="w-6 h-6 rounded-full border-2 border-white shadow-sm object-cover bg-slate-100"
                                    :style="{ zIndex: 10 - idx }">
                            </div>
                            
                            <div class="flex items-center gap-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                <div class="w-1.5 h-1.5 rounded-full" :class="room.active_count > 0 ? 'bg-green-500 animate-pulse' : 'bg-slate-300'"></div>
                                <span>@{{ room.active_count || 0 }} 位在線</span>
                            </div>
                        </div>
                    </div>
                </button>
            </div>
        </div>
    </div><div v-else class="fixed sm:relative inset-0 sm:inset-auto z-[300] sm:z-auto flex-grow flex flex-col bg-white sm:rounded-[32px] sm:border border-slate-200 sm:shadow-2xl overflow-hidden overscroll-none" @touchmove.stop>
        {{-- Chat Header (Blue like message modal) --}}
        <div class="px-4 sm:px-6 py-3 sm:py-4 flex items-center justify-between bg-gradient-to-r from-slate-800 to-slate-900 shrink-0">
            <div class="flex items-center gap-3">
                <button @click="currentRoom = null" class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-white/10 flex items-center justify-center text-white hover:bg-white/20 transition-all">
                    <app-icon name="chevron-left" class-name="w-5 h-5"></app-icon>
                </button>
                <div>
                    <h3 class="text-base sm:text-lg font-black text-white leading-tight">@{{ currentRoom.name }} 揪球室</h3>
                    <p class="text-[10px] font-bold text-white/60 uppercase tracking-widest">
                        本房間有 @{{ currentRoom.active_count || 0 }} 位球友在線
                    </p>
                </div>
            </div>
            {{-- Avatars Stack (Inside Room) --}}
            <div class="flex items-center -space-x-2" v-if="globalInstantStats.avatars && globalInstantStats.avatars.length">
                <img v-for="(u, idx) in globalInstantStats.avatars.slice(0, 3)" :key="idx" 
                    :src="u.avatar" 
                    @click="openProfile(u.uid)"
                    class="w-7 h-7 sm:w-8 sm:h-8 rounded-full border-2 border-slate-800 shadow-sm object-cover bg-slate-100 cursor-pointer hover:scale-110 active:scale-95 transition-all"
                    :style="{ zIndex: 10 - idx }">
                <div v-if="globalInstantStats.display_count > 3" 
                    class="w-7 h-7 sm:w-8 sm:h-8 rounded-full border-2 border-slate-800 bg-white/20 flex items-center justify-center text-[9px] font-black text-white shadow-sm z-0">
                    +@{{ globalInstantStats.display_count - 3 }}
                </div>
            </div>
        </div>

        {{-- Messages Container --}}
        <div id="instant-messages-container" class="flex-grow overflow-y-auto p-4 sm:p-6 space-y-4 no-scrollbar bg-slate-50/30 overscroll-contain touch-pan-y">
            <div v-if="instantMessages.length === 0" class="h-full flex flex-col items-center justify-center text-center opacity-50">
                <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mb-4 shadow-sm">
                    <app-icon name="message-square" class-name="w-8 h-8 text-slate-300"></app-icon>
                </div>
                <p class="text-sm font-bold text-slate-400 uppercase tracking-widest">目前還沒有訊息<br>成為第一個揪球的人吧！</p>
            </div>
            <div v-for="msg in instantMessages" :key="msg.id" 
                :class="['flex gap-3', msg.user_id === currentUser?.id ? 'flex-row-reverse' : '']">
                {{-- Avatar --}}
                <div class="shrink-0 pt-1">
                    <div @click="openProfile(msg.user.uid)" class="w-8 h-8 sm:w-10 sm:h-10 rounded-full border-2 border-white shadow-sm overflow-hidden cursor-pointer">
                        <img :src="msg.user.line_picture_url" class="w-full h-full object-cover">
                    </div>
                </div>
                {{-- Content --}}
                <div :class="['max-w-[80%]', msg.user_id === currentUser?.id ? 'text-right' : '']">
                    <div v-if="msg.user_id !== currentUser?.id" class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">
                        @{{ msg.user.name }}
                    </div>
                    <div :class="['px-4 py-2.5 rounded-[20px] text-sm font-semibold shadow-sm inline-block break-words text-left whitespace-pre-line', 
                        msg.user_id === currentUser?.id ? 'bg-blue-600 text-white rounded-tr-none' : 'bg-white text-slate-700 border border-slate-100 rounded-tl-none']">
                        @{{ msg.content }}
                    </div>
                    <div class="text-[9px] font-bold text-slate-300 uppercase mt-1 mx-1">
                        @{{ formatDate(msg.created_at) }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Chat Footer / Input --}}
        <div class="p-4 sm:p-6 border-t border-slate-100 bg-white shadow-lg shrink-0" style="padding-bottom: max(1rem, env(safe-area-inset-bottom));">
            {{-- Quick Templates --}}
            <div class="flex gap-2 overflow-x-auto no-scrollbar pb-3 mb-1">
                <button v-for="t in [
                    '有人現在想打球嗎？',
                    '我在球場缺一人！',
                    '等等有人有空嗎？',
                    '新手可以一起打嗎？',
                    '+1 跟我約打'
                ]" 
                    @click="instantMessageDraft = t"
                    class="px-3 py-1.5 rounded-full bg-slate-50 border border-slate-100 text-[10px] font-black text-slate-500 hover:text-blue-600 hover:border-blue-200 hover:bg-blue-50 transition-all whitespace-nowrap">
                    @{{ t }}
                </button>
            </div>

            <div class="flex items-center gap-2">
                <div class="flex-grow relative flex items-center">
                    <textarea v-model="instantMessageDraft" 
                        @keydown.enter.exact.prevent="sendInstantMessage()"
                        rows="1"
                        placeholder="輸入訊息或使用上方模板..." 
                        class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-4 py-3 pr-10 text-sm font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all resize-none overflow-hidden"></textarea>
                    <div class="absolute right-1 top-1/2 -translate-y-1/2">
                        <emoji-picker @select="e => instantMessageDraft += e"></emoji-picker>
                    </div>
                </div>
                <button @click="sendInstantMessage()" 
                    :disabled="!instantMessageDraft || isSending"
                    class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center hover:bg-blue-700 active:scale-95 disabled:opacity-50 disabled:active:scale-100 transition-all shadow-lg shadow-blue-500/20">
                    <app-icon v-if="!isSending" name="send" class-name="w-5 h-5"></app-icon>
                    <div v-else class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                </button>
            </div>
        </div>
    </div>
</div>
