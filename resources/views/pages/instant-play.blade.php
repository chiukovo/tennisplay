{{-- Instant Play View --}}
<div v-if="view === 'instant-play'" class="h-[calc(100vh-200px)] sm:h-[calc(100vh-160px)] flex flex-col">
    
    {{-- Lobby View: Room Selection --}}
    <div v-if="!currentRoom" class="space-y-6 overflow-y-auto no-scrollbar pb-10">
        {{-- Header & Stats --}}
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <h2 class="text-3xl sm:text-5xl font-black italic uppercase tracking-tighter leading-tight text-slate-900">
                    我要打球 <span class="text-blue-600 block sm:inline">RIGHT NOW</span>
                </h2>
                <div class="flex items-center gap-2 mt-2">
                    <!-- Avatars Stack -->
                    <div class="flex items-center shrink-0 -space-x-2.5 mr-1" v-if="globalInstantStats.avatars && globalInstantStats.avatars.length">
                        <img v-for="(u, idx) in globalInstantStats.avatars.slice(0, 5)" :key="idx" 
                            :src="u.avatar" 
                            @click="openProfile(u.uid)"
                            class="w-8 h-8 rounded-full border-2 border-white shadow-sm object-cover bg-slate-100 cursor-pointer hover:scale-110 hover:z-20 transition-all"
                            :style="{ zIndex: 10 - idx }">
                        <div v-if="globalInstantStats.display_count > 5" 
                            class="w-8 h-8 rounded-full border-2 border-white bg-slate-900 flex items-center justify-center text-[9px] font-black text-white shadow-sm z-0">
                            +@{{ globalInstantStats.display_count - 5 }}
                        </div>
                    </div>
                    
                    <div class="flex h-2 w-2 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                    </div>
                    <p class="text-slate-500 font-bold text-xs sm:text-sm uppercase tracking-widest">
                        目前有 <span class="text-blue-600">@{{ globalInstantStats.display_count }}</span> 位球友很想打球(✪ω✪)
                    </p>
                </div>
            </div>
        </div>

        {{-- Region Grid --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
            <button v-for="room in instantRooms" :key="room.id" 
                @click="selectRoom(room)"
                class="group bg-white border border-slate-100 p-4 sm:p-6 rounded-[32px] shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all text-left relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50 rounded-full -mr-12 -mt-12 group-hover:bg-blue-100 transition-colors"></div>
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

    {{-- Chat View: Active Chat Room --}}
    <div v-else class="flex-grow flex flex-col bg-white rounded-[32px] border border-slate-200 shadow-2xl overflow-hidden relative">
        {{-- Chat Header --}}
        <div class="px-4 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div class="flex items-center gap-3">
                <button @click="currentRoom = null" class="w-10 h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-600 hover:text-blue-600 hover:border-blue-200 transition-all shadow-sm">
                    <app-icon name="arrow-left" class-name="w-5 h-5"></app-icon>
                </button>
                <div>
                    <h3 class="text-base sm:text-lg font-black text-slate-900 leading-tight">@{{ currentRoom.name }} 揪球室</h3>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <div class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                            本房間有 @{{ currentRoom.active_count || 0 }} 位球友在線
                        </p>
                    </div>
                </div>
            </div>
            <!-- Avatars Stack (Inside Room) -->
            <div class="flex items-center -space-x-2" v-if="globalInstantStats.avatars && globalInstantStats.avatars.length">
                <img v-for="(u, idx) in globalInstantStats.avatars.slice(0, 3)" :key="idx" 
                    :src="u.avatar" 
                    @click="openProfile(u.uid)"
                    class="w-8 h-8 rounded-full border-2 border-white shadow-sm object-cover bg-slate-100 ring-1 ring-slate-100 cursor-pointer hover:scale-110 active:scale-95 transition-all"
                    :style="{ zIndex: 10 - idx }">
                <div v-if="globalInstantStats.display_count > 3" 
                    class="w-8 h-8 rounded-full border-2 border-white bg-slate-900 flex items-center justify-center text-[9px] font-black text-white shadow-sm z-0">
                    +@{{ globalInstantStats.display_count - 3 }}
                </div>
            </div>
        </div>

        {{-- Messages Container --}}
        <div id="instant-messages-container" class="flex-grow overflow-y-auto p-4 sm:p-6 space-y-4 no-scrollbar bg-slate-50/30">
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
        <div class="p-4 pb-24 sm:p-6 border-t border-slate-100 bg-white">
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
