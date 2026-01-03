{{-- Messages View --}}
<div v-if="view === 'messages'" class="max-w-4xl mx-auto space-y-10 pb-20 animate__animated animate__fadeInRight">
    <h2 class="text-5xl font-black italic uppercase tracking-tighter">約打收件匣</h2>
    <div class="bg-white rounded-[48px] shadow-2xl border border-slate-100 overflow-hidden">
        <div class="divide-y divide-slate-100">
            <div v-for="m in messages" :key="m.id" class="p-10 hover:bg-slate-50 transition-colors cursor-pointer relative" :class="m.unread ? 'bg-blue-50/30' : ''">
                <div v-if="m.unread" class="absolute left-0 top-0 bottom-0 w-2 bg-blue-600"></div>
                <div class="flex justify-between items-start mb-4">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-2xl bg-slate-900 flex items-center justify-center text-white text-base font-black uppercase leading-none shadow-lg">
                            @{{m.from[0]}}
                        </div>
                        <span class="font-black italic uppercase tracking-tight text-xl">@{{m.from}}</span>
                    </div>
                    <span class="text-xs font-bold text-slate-400">@{{m.date}}</span>
                </div>
                <p class="text-base font-medium text-slate-600 leading-relaxed mb-6">@{{m.content}}</p>
                <button class="px-6 py-3 bg-slate-950 text-white text-xs font-black uppercase tracking-widest rounded-2xl shadow-xl hover:bg-blue-600 transition-all">
                    回覆訊息
                </button>
            </div>
        </div>
    </div>
</div>
