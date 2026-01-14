{{-- Message Detail Modal (Chat Interface) --}}
<script type="text/x-template" id="message-detail-modal-template">
    <transition name="modal">
        <div v-if="open" class="fixed inset-0 z-[200] flex items-center justify-center p-0 sm:p-6 premium-blur modal-content" @click.self="$emit('update:open', false)">
            <div class="bg-white w-full h-[100dvh] sm:h-[80vh] max-w-2xl sm:rounded-[32px] shadow-2xl overflow-hidden flex flex-col relative">
                {{-- Header --}}
                <div class="bg-slate-900 px-3 py-3 sm:p-6 text-white flex items-center justify-between shrink-0 shadow-lg z-10">
                    <div class="flex items-center gap-2 sm:gap-4">
                        <button @click="$emit('update:open', false)" class="sm:hidden p-1.5 -ml-1 hover:bg-white/10 rounded-xl transition-colors">
                            <app-icon name="chevron-left" class-name="w-5 h-5 text-white"></app-icon>
                        </button>
                        <div @click="goToProfile" class="flex items-center gap-2 sm:gap-4 cursor-pointer hover:opacity-80 transition-opacity">
                            <div class="w-9 h-9 sm:w-12 sm:h-12 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white text-sm sm:text-lg font-black uppercase shadow-lg border-2 border-white/20">
                                @{{ targetUser?.name?.[0] || '?' }}
                            </div>
                            <div>
                                <h3 class="text-base sm:text-xl font-black italic uppercase tracking-tight">@{{ targetUser?.name || 'Loading...' }}</h3>
                                <p v-if="targetUser?.player" class="text-[10px] sm:text-xs text-blue-200 font-bold">關於: @{{ targetUser.player.name }}</p>
                            </div>
                        </div>
                    </div>
                    <button @click="$emit('update:open', false)" class="hidden sm:block p-2 hover:bg-white/10 rounded-full transition-colors">
                        <app-icon name="x" class-name="w-6 h-6 text-white/60 hover:text-white"></app-icon>
                    </button>
                </div>

                {{-- Chat History --}}
                <div ref="chatContainer" class="flex-1 overflow-y-auto p-3 sm:p-6 bg-slate-50 space-y-3">
                    {{-- Load More Button --}}
                    <div v-if="hasMore" class="text-center py-2">
                        <button @click="loadMore" :disabled="loading" class="text-xs text-slate-400 hover:text-blue-600 font-bold bg-white px-3 py-1.5 rounded-full shadow-sm border border-slate-200 transition-all">
                            @{{ loading ? '載入中...' : '載入更早的訊息' }}
                        </button>
                    </div>

                    <div v-if="loading && messages.length === 0" class="flex justify-center py-8">
                        <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <div v-else-if="messages.length === 0" class="text-center py-10 text-slate-400 font-bold text-sm">
                        尚無對話紀錄，發送第一則訊息吧！
                    </div>
                    <div v-for="msg in messages" :key="msg.id" :class="['flex', msg.is_me ? 'justify-end' : 'justify-start']">
                        <div :class="['max-w-[75%] rounded-2xl px-3.5 py-2.5 sm:p-4 shadow-sm relative', 
                            msg.is_me ? 'bg-blue-600 text-white rounded-br-md' : 'bg-white text-slate-700 rounded-bl-md border border-slate-100']">
                            <p class="text-[13px] sm:text-base font-medium whitespace-pre-line leading-relaxed">@{{ msg.content }}</p>
                            <div :class="['text-[9px] sm:text-[10px] font-bold mt-1 text-right', msg.is_me ? 'text-blue-200' : 'text-slate-400']">
                                @{{ formatDate(msg.created_at) }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Input Area (with safe-area support) --}}
                <div class="p-2.5 sm:p-6 bg-white border-t border-slate-100 shrink-0" style="padding-bottom: max(0.625rem, env(safe-area-inset-bottom));">
                    <form @submit.prevent="sendMessage" class="flex gap-2 sm:gap-3 items-end">
                        <div class="flex-1 relative">
                            <textarea v-model="newMessage" placeholder="輸入訊息..." rows="1"
                                @keydown.enter="handleEnterKey"
                                @input="$event.target.style.height = 'auto'; $event.target.style.height = Math.min($event.target.scrollHeight, 120) + 'px'"
                                class="w-full bg-slate-100 border-0 rounded-2xl px-4 py-2.5 sm:py-3 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all resize-none overflow-hidden"
                                style="min-height: 42px; max-height: 120px;"
                                :disabled="sending"></textarea>
                        </div>
                        <button type="submit" :disabled="!newMessage.trim() || sending" 
                            class="bg-blue-600 text-white w-10 h-10 sm:w-auto sm:h-auto sm:px-6 sm:py-3 rounded-xl sm:rounded-xl font-black uppercase tracking-widest shadow-lg shadow-blue-600/20 hover:bg-blue-700 active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 shrink-0">
                            <app-icon name="send" class-name="w-5 h-5 sm:hidden"></app-icon>
                            <span class="hidden sm:inline" v-if="!sending">發送</span>
                            <app-icon v-if="sending" name="loader" class-name="w-5 h-5 animate-spin"></app-icon>
                        </button>
                    </form>
                    <p class="text-[9px] text-slate-400 text-center mt-1.5 hidden sm:block">按 Enter 發送 · Shift+Enter 換行</p>
                </div>
            </div>
        </div>
    </transition>
</script>
