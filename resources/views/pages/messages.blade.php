{{-- Messages View --}}
<div v-if="view === 'messages'" class="max-w-4xl mx-auto space-y-8 pb-20 animate__animated animate__fadeIn">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-4xl sm:text-5xl font-black italic uppercase tracking-tighter">約打訊息</h2>
            <p class="text-slate-400 font-bold text-sm uppercase tracking-[0.2em] mt-2">My Messages</p>
        </div>
        <div v-if="messages.length > 0" class="text-right">
            <div class="text-2xl font-black text-blue-600">@{{ messages.filter(m => m.unread || !m.read_at).length }}</div>
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">未讀</div>
        </div>
    </div>

    {{-- Login Required --}}
    <div v-if="!isLoggedIn" class="bg-white rounded-[48px] shadow-2xl border border-slate-100 p-16 text-center">
        <div class="bg-slate-100 w-24 h-24 rounded-3xl flex items-center justify-center mx-auto mb-8">
            <app-icon name="mail" class-name="w-12 h-12 text-slate-300"></app-icon>
        </div>
        <h3 class="text-2xl font-black italic uppercase tracking-tight mb-4">登入以查看訊息</h3>
        <p class="text-slate-400 font-medium mb-8">登入後即可查看與管理您的約打訊息</p>
        <a href="/auth" @click.prevent="navigateTo('auth')" class="inline-block bg-blue-600 text-white px-10 py-4 rounded-2xl font-black uppercase tracking-widest hover:bg-blue-700 transition-all shadow-xl">
            立即登入
        </a>
    </div>

    {{-- Messages List (LINE-Style) --}}
    <div v-else-if="messages.length > 0" class="bg-white rounded-[40px] shadow-2xl border border-slate-100 overflow-hidden">
        <div class="divide-y divide-slate-50">
            <div v-for="m in paginatedMessages" :key="m.id" @click="openMessage(m)" class="p-5 sm:p-6 hover:bg-slate-50 transition-all cursor-pointer relative group flex items-center gap-4 border-l-4 border-transparent" :class="m.unread_count > 0 ? 'bg-blue-50/20 border-l-blue-600' : ''">
                {{-- Avatar with Unread Dot --}}
                <div class="relative shrink-0">
                    <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center text-slate-500 text-xl font-black uppercase shadow-inner border-2 border-white">
                        @{{ (m.sender?.uid === currentUser.uid ? m.receiver?.name : m.sender?.name)?.[0] || '?' }}
                    </div>
                    <div v-if="m.unread_count > 0" class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 border-2 border-white rounded-full shadow-sm animate-pulse"></div>
                </div>
                
                {{-- Content --}}
                <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-center mb-1">
                        <h4 class="font-black italic uppercase tracking-tight text-base sm:text-lg text-slate-800 truncate pr-2">
                            @{{ m.sender?.uid === currentUser.uid ? m.receiver?.name : m.sender?.name }}
                        </h4>
                        <span class="text-[11px] font-bold text-slate-400 whitespace-nowrap">@{{ formatDate(m.created_at) }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <p class="text-sm font-medium text-slate-500 truncate group-hover:text-slate-900 transition-colors leading-snug">
                            <span v-if="m.sender?.uid === currentUser.uid" class="text-blue-500 font-bold mr-1">您:</span>
                            @{{ m.content }}
                        </p>
                        <div v-if="m.unread_count > 0" class="bg-blue-600 text-white text-[10px] font-black px-2.5 py-1 rounded-full shadow-md shrink-0">
                            @{{ m.unread_count }}
                        </div>
                    </div>
                    <div v-if="m.player" class="mt-1 flex items-center gap-1">
                        <span class="text-[10px] bg-slate-100 text-slate-400 font-black px-2 py-0.5 rounded-md uppercase tracking-tighter italic">關於卡片: @{{ m.player.name }}</span>
                    </div>
                </div>

                {{-- Hover Indicator --}}
                <div class="absolute right-4 text-slate-200 opacity-0 group-hover:opacity-100 transition-all -translate-x-2 group-hover:translate-x-0">
                    <app-icon name="chevron-right" class-name="w-5 h-5"></app-icon>
                </div>
            </div>
        </div>

        {{-- Load More --}}
        <div v-if="hasMoreMessages" class="p-6 text-center bg-slate-50/50 border-t border-slate-50">
            <button @click="loadMoreMessages" class="bg-white border border-slate-200 text-blue-600 text-xs font-black uppercase tracking-widest hover:bg-blue-600 hover:text-white hover:border-blue-600 transition-all py-3 px-8 rounded-full shadow-sm">
                查看較早對話
            </button>
        </div>
    </div>

    {{-- Empty State --}}
    <div v-else class="bg-white rounded-[48px] shadow-2xl border border-slate-100 p-16 text-center">
        <div class="bg-blue-50 w-24 h-24 rounded-3xl flex items-center justify-center mx-auto mb-8">
            <app-icon name="mail" class-name="w-12 h-12 text-blue-300"></app-icon>
        </div>
        <h3 class="text-2xl font-black italic uppercase tracking-tight mb-4">還沒有訊息</h3>
        <p class="text-slate-400 font-medium mb-8">開始瀏覽球友列表，找到心儀的球友後發送約打邀請！</p>
        <a href="/list" @click.prevent="navigateTo('list')" class="inline-block bg-blue-600 text-white px-10 py-4 rounded-2xl font-black uppercase tracking-widest hover:bg-blue-700 transition-all shadow-xl">
            探索球友
        </a>
    </div>
</div>
