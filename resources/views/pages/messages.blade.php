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

    {{-- Messages List --}}
    <div v-else-if="messages.length > 0" class="bg-white rounded-[48px] shadow-2xl border border-slate-100 overflow-hidden">
        {{-- Tabs --}}
        <div class="flex border-b border-slate-100">
            <button @click="messageTab = 'inbox'" :class="['flex-1 py-5 text-sm font-black uppercase tracking-widest transition-all', messageTab === 'inbox' ? 'text-blue-600 border-b-2 border-blue-600 bg-blue-50/30' : 'text-slate-400 hover:text-slate-600']">
                收件匣 (@{{ messages.filter(m => m.to_user_id || !m.from_user_id).length }})
            </button>
            <button @click="messageTab = 'sent'" :class="['flex-1 py-5 text-sm font-black uppercase tracking-widest transition-all', messageTab === 'sent' ? 'text-blue-600 border-b-2 border-blue-600 bg-blue-50/30' : 'text-slate-400 hover:text-slate-600']">
                已發送 (@{{ messages.filter(m => m.from_user_id).length }})
            </button>
        </div>
        
        <div class="divide-y divide-slate-100">
            <div v-for="m in paginatedMessages" :key="m.id" @click="openMessage(m)" class="p-4 sm:p-6 hover:bg-slate-50 transition-colors cursor-pointer relative group" :class="m.unread_count > 0 ? 'bg-blue-50/30' : ''">
                <div v-if="m.unread_count > 0" class="absolute left-0 top-0 bottom-0 w-1 bg-blue-600"></div>
                <div class="flex items-center gap-4">
                    {{-- Avatar --}}
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-gradient-to-br from-slate-800 to-slate-900 flex items-center justify-center text-white text-sm sm:text-base font-black uppercase leading-none shadow-md shrink-0">
                        @{{ (m.from_user_id === currentUser.id ? m.receiver?.name : m.sender?.name)?.[0] || '?' }}
                    </div>
                    
                    {{-- Content Preview --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-baseline mb-1">
                            <h4 class="font-black italic uppercase tracking-tight text-sm sm:text-base truncate pr-2">
                                @{{ m.from_user_id === currentUser.id ? m.receiver?.name : m.sender?.name }}
                                <span v-if="m.player" class="text-[10px] text-slate-400 font-bold ml-1 font-sans not-italic">關於: @{{ m.player.name }}</span>
                            </h4>
                            <span class="text-[10px] font-bold text-slate-400 shrink-0">@{{ formatDate(m.created_at) }}</span>
                        </div>
                        <p class="text-xs sm:text-sm font-medium text-slate-500 truncate group-hover:text-slate-700 transition-colors">
                            <span v-if="m.from_user_id === currentUser.id" class="text-slate-400 mr-1">你:</span>
                            @{{ m.content }}
                        </p>
                    </div>

                    {{-- Unread Badge or Arrow --}}
                    <div v-if="m.unread_count > 0" class="bg-blue-600 text-white text-[10px] font-black px-2 py-0.5 rounded-full shadow-sm">
                        @{{ m.unread_count }}
                    </div>
                    <div v-else class="text-slate-300 group-hover:text-blue-600 transition-colors">
                        <app-icon name="chevron-right" class-name="w-4 h-4"></app-icon>
                    </div>
                </div>
            </div>
        </div>

        {{-- Load More Button --}}
        <div v-if="hasMoreMessages" class="p-4 text-center">
            <button @click="loadMoreMessages" class="text-blue-600 text-sm font-black uppercase tracking-widest hover:text-blue-700 transition-colors py-2 px-4 rounded-full hover:bg-blue-50">
                載入更多訊息
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
