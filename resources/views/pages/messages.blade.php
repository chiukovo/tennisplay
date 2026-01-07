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
            <div v-for="m in messages" :key="m.id" class="p-8 hover:bg-slate-50 transition-colors cursor-pointer relative" :class="(m.unread || !m.read_at) ? 'bg-blue-50/30' : ''">
                <div v-if="m.unread || !m.read_at" class="absolute left-0 top-0 bottom-0 w-1.5 bg-blue-600"></div>
                <div class="flex justify-between items-start mb-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-slate-800 to-slate-900 flex items-center justify-center text-white text-lg font-black uppercase leading-none shadow-lg">
                            @{{ (m.from || m.sender?.name || '系統')[0] }}
                        </div>
                        <div>
                            <span class="font-black italic uppercase tracking-tight text-lg block">@{{ m.from || m.sender?.name || '系統通知' }}</span>
                            <span v-if="m.player" class="text-xs text-slate-400 font-bold">關於: @{{ m.player.name }}</span>
                        </div>
                    </div>
                    <span class="text-xs font-bold text-slate-400">@{{ m.date || formatDate(m.created_at) }}</span>
                </div>
                <p class="text-base font-medium text-slate-600 leading-relaxed mb-6 line-clamp-2">@{{ m.content }}</p>
                <div class="flex gap-3">
                    <button class="px-5 py-2.5 bg-slate-950 text-white text-xs font-black uppercase tracking-widest rounded-xl shadow-lg hover:bg-blue-600 transition-all">
                        回覆
                    </button>
                    <button v-if="m.unread || !m.read_at" @click.stop="markMessageRead(m.id)" class="px-5 py-2.5 bg-slate-100 text-slate-600 text-xs font-black uppercase tracking-widest rounded-xl hover:bg-slate-200 transition-all">
                        標為已讀
                    </button>
                </div>
            </div>
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
