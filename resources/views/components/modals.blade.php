{{-- Modal Templates --}}

{{-- Player Detail Modal --}}
<script type="text/x-template" id="player-detail-modal-template">
    <transition name="modal">
        <div v-if="player" class="fixed inset-0 z-[200] flex items-center justify-center p-4 sm:p-10 premium-blur modal-content" @click.self="$emit('close')">
            <div class="bg-white w-full max-w-5xl h-full sm:h-auto max-h-[92vh] rounded-[32px] sm:rounded-[48px] overflow-hidden shadow-[0_0_100px_rgba(0,0,0,0.5)] flex flex-col md:flex-row relative">
                <button type="button" @click="$emit('close')" class="absolute top-6 right-6 z-50 p-2 bg-white/80 backdrop-blur-md hover:bg-red-50 hover:text-red-500 rounded-full shadow-lg transition-all">
                    <app-icon name="x" class-name="w-5 h-5"></app-icon>
                </button>

                {{-- Left: Card Display --}}
                <div class="w-full md:w-1/2 p-6 sm:p-10 flex items-center justify-center bg-slate-50 border-r border-slate-100 shrink-0">
                    <div class="w-full max-w-[280px] sm:max-w-[340px] transform hover:scale-105 transition-transform duration-500">
                        <player-card :player="player" />
                    </div>
                </div>

                {{-- Right: Detailed Stats --}}
                <div class="w-full md:w-1/2 p-8 sm:p-14 overflow-y-auto bg-white flex flex-col no-scrollbar">
                    <div class="mb-8">
                        <h3 class="text-5xl font-black italic uppercase tracking-tighter text-slate-900 mb-2 leading-tight">@{{player.name}}</h3>
                        <div class="flex items-center gap-3">
                            <span class="px-4 py-1.5 bg-blue-600 text-white text-[10px] font-black rounded-lg uppercase tracking-widest italic">Verified Player</span>
                            <span class="text-sm font-bold text-slate-400 flex items-center gap-1"><app-icon name="map-pin" class-name="w-4 h-4"></app-icon> @{{player.region}}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6 mb-10">
                        <div v-for="s in stats" :key="s.label" class="p-6 bg-slate-50 rounded-3xl border border-slate-100 shadow-inner">
                            <div class="flex items-center gap-2 opacity-50 mb-1">
                                <app-icon :name="s.icon" class-name="w-4 h-4"></app-icon>
                                <span class="text-[10px] font-black uppercase tracking-widest">@{{s.label}}</span>
                            </div>
                            <div class="text-xl font-black text-slate-900">@{{s.value}}</div>
                        </div>
                    </div>

                    <div class="bg-slate-900 p-8 rounded-[32px] text-white relative overflow-hidden mb-10 shadow-2xl">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/20 blur-[60px] rounded-full"></div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-blue-400 mb-2 block italic underline">約打宣告 / 個人特色</span>
                        <p class="text-lg text-slate-300 leading-relaxed italic whitespace-pre-line">
                            「@{{player.intro || '這位球友很懶，什麼都沒留下... 希望能找到實力相當的球友進行約打與練習。'}}」
                        </p>
                    </div>

                    <div class="mt-auto flex flex-col sm:flex-row gap-4 pt-6 border-t border-slate-100">
                        <button type="button" @click="$emit('open-match', player)" class="flex-1 bg-blue-600 text-white py-5 rounded-2xl font-black uppercase tracking-widest text-base shadow-xl hover:bg-blue-500 transition-all flex items-center justify-center gap-3">
                            <app-icon name="message-circle" class-name="w-6 h-6"></app-icon> 立即發送約打信
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</script>

{{-- Match Modal --}}
<script type="text/x-template" id="match-modal-template">
    <transition name="modal">
        <div v-if="open && player" class="fixed inset-0 z-[300] flex items-center justify-center p-4 bg-slate-900/90 backdrop-blur-md modal-content" @click.self="$emit('update:open', false)">
            <div class="bg-white w-full max-w-md rounded-[40px] overflow-hidden shadow-2xl">
                <div class="bg-slate-900 p-8 text-white flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <img v-if="player.photo" :src="player.photo" class="w-12 h-12 rounded-full border-2 border-blue-500 object-cover shadow-lg">
                        <div v-else class="w-12 h-12 rounded-full bg-slate-800 flex items-center justify-center border-2 border-slate-700">
                            <app-icon name="user" class-name="w-6 h-6 text-slate-500"></app-icon>
                        </div>
                        <div>
                            <h3 class="font-black italic uppercase text-xl italic tracking-tight">約打邀約信</h3>
                            <p class="text-[9px] font-bold text-blue-400 tracking-widest uppercase">To: @{{player.name}}</p>
                        </div>
                    </div>
                    <button type="button" @click="$emit('update:open', false)"><app-icon name="x" class-name="w-6 h-6 opacity-50"></app-icon></button>
                </div>
                <div class="p-8 space-y-6">
                    <div class="bg-blue-50 p-5 rounded-2xl border border-blue-100 flex gap-4 text-xs text-blue-800 font-bold uppercase leading-normal">
                        <app-icon name="shield-check" class-name="w-6 h-6 text-blue-600 shrink-0"></app-icon>
                        安全提示：AceMate 建議在公開且有監視設備的球場會面，祝您球技進步。
                    </div>
                    <textarea v-model="textModel" class="w-full h-40 p-5 bg-slate-50 border-2 border-transparent rounded-[28px] focus:border-blue-500 outline-none font-bold text-base leading-relaxed" 
                        :placeholder="'Hi ' + (player.name || '') + '，看到你的 AceMate 檔案後非常想跟你交流，請問... '"></textarea>
                    <button type="button" @click="$emit('submit', textModel)" class="w-full bg-slate-950 text-white py-5 rounded-3xl font-black uppercase tracking-[0.2em] hover:bg-blue-600 shadow-2xl transition-all text-lg">
                        發送站內訊息
                    </button>
                </div>
            </div>
        </div>
    </transition>
</script>

{{-- NTRP Guide Modal --}}
<script type="text/x-template" id="ntrp-guide-modal-template">
    <transition name="modal">
        <div v-if="open" class="fixed inset-0 z-[600] flex items-center justify-center p-4 sm:p-6 bg-slate-900/80 backdrop-blur-md modal-content" @click.self="$emit('update:open', false)">
            <div class="bg-slate-950 w-full max-w-2xl max-h-[85vh] rounded-[32px] overflow-hidden shadow-2xl border border-white/10 flex flex-col animate__animated animate__zoomIn animate__faster">
                <div class="px-6 py-5 flex items-center justify-between border-b border-white/10 shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="bg-blue-600 p-2 rounded-xl shadow-lg shadow-blue-600/20">
                            <app-icon name="help" class-name="w-5 h-5 text-white"></app-icon>
                        </div>
                        <h3 class="text-lg font-black italic uppercase tracking-wider text-white">NTRP 等級說明</h3>
                    </div>
                    <button type="button" @click="$emit('update:open', false)" class="p-2 bg-white/5 hover:bg-white/10 rounded-xl transition-all border border-white/10">
                        <app-icon name="x" class-name="w-5 h-5 text-white/50"></app-icon>
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto p-4 sm:p-6 no-scrollbar grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div v-for="(desc, lvl) in descs" :key="lvl" class="bg-white/5 border border-white/5 p-4 rounded-2xl hover:bg-blue-600/5 transition-colors group">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="bg-white text-slate-950 px-2 py-0.5 rounded-lg font-black italic text-sm">@{{lvl}}</span>
                            <div class="h-px flex-1 bg-white/10"></div>
                        </div>
                        <p class="text-slate-400 font-bold text-xs leading-relaxed">@{{desc}}</p>
                    </div>
                </div>
                <div class="p-5 border-t border-white/10 bg-slate-950/50 flex justify-center shrink-0">
                    <button type="button" @click="$emit('update:open', false)" class="w-full bg-blue-600 text-white py-4 rounded-2xl font-black uppercase tracking-widest hover:bg-blue-500 transition-all shadow-lg text-sm">
                        了解並返回
                    </button>
                </div>
            </div>
        </div>
    </transition>
</script>

{{-- Quick Edit Modal --}}
<script type="text/x-template" id="quick-edit-modal-template">
    <transition name="modal">
        <div v-if="open" class="fixed inset-0 z-[700] flex items-center justify-center p-4 bg-slate-900/90 backdrop-blur-md modal-content" @click.self="$emit('update:open', false)">
            <div class="bg-white w-full max-w-2xl rounded-[40px] overflow-hidden shadow-2xl flex flex-col max-h-[90vh] animate__animated animate__zoomIn animate__faster">
                <div class="bg-slate-900 p-6 text-white flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-4">
                        <div class="bg-blue-600 p-2 rounded-xl">
                            <app-icon name="edit-3" class-name="w-5 h-5 text-white"></app-icon>
                        </div>
                        <h3 class="font-black italic uppercase text-xl tracking-tight">快速修改資料</h3>
                    </div>
                    <button type="button" @click="$emit('update:open', false)" class="p-2 hover:bg-white/10 rounded-full transition-all">
                        <app-icon name="x" class-name="w-6 h-6 opacity-50"></app-icon>
                    </button>
                </div>
                
                <div class="p-8 overflow-y-auto no-scrollbar space-y-8">
                    {{-- Name & Gender --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="space-y-3">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">選手姓名</label>
                            <input type="text" v-model="form.name" class="w-full px-5 py-3.5 bg-slate-50 border-2 border-transparent rounded-2xl focus:border-blue-500 outline-none font-black italic text-base transition-all">
                        </div>
                        <div class="space-y-3">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">生理性別</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button v-for="g in ['男', '女']" :key="g" type="button" @click="form.gender = g" 
                                    :class="['py-3 rounded-xl font-black text-xs transition-all border-2', form.gender === g ? 'bg-slate-900 text-white border-slate-900 shadow-lg' : 'bg-slate-50 text-slate-400 border-transparent hover:border-slate-200']">
                                    @{{ g }}
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- NTRP Level --}}
                    <div class="space-y-3">
                        <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">NTRP 程度</label>
                        <div class="grid grid-cols-4 sm:grid-cols-6 gap-2">
                            <button v-for="l in levels" :key="l" type="button" @click="form.level = l"
                                :class="['py-2.5 rounded-xl font-black text-[10px] transition-all border-2', form.level === l ? 'bg-blue-600 text-white border-blue-600 shadow-md' : 'bg-slate-50 text-slate-400 border-transparent hover:border-slate-200']">
                                @{{l}}
                            </button>
                        </div>
                    </div>

                    {{-- Handed & Backhand --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="space-y-3">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">持拍手</label>
                            <div class="flex gap-2">
                                <button type="button" v-for="h in ['右手', '左手']" :key="h" @click="form.handed = h"
                                    :class="['flex-1 py-3 rounded-xl font-black text-xs transition-all border-2', form.handed === h ? 'bg-slate-900 text-white border-slate-900 shadow-lg' : 'bg-slate-50 text-slate-400 border-transparent hover:border-slate-200']">
                                    @{{h}}
                                </button>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">反手類型</label>
                            <div class="flex gap-2">
                                <button type="button" v-for="b in ['單反', '雙反']" :key="b" @click="form.backhand = b"
                                    :class="['flex-1 py-3 rounded-xl font-black text-xs transition-all border-2', form.backhand === b ? 'bg-slate-900 text-white border-slate-900 shadow-lg' : 'bg-slate-50 text-slate-400 border-transparent hover:border-slate-200']">
                                    @{{b}}
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Region --}}
                    <div class="space-y-3">
                        <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">活動地區</label>
                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 max-h-[150px] overflow-y-auto no-scrollbar p-1">
                            <button v-for="r in regions" :key="r" type="button" @click="form.region = r"
                                :class="['py-2.5 px-2 rounded-xl font-bold text-[10px] transition-all border-2', form.region === r ? 'bg-slate-900 text-white border-slate-900 shadow-md' : 'bg-slate-50 text-slate-400 border-transparent hover:border-slate-200']">
                                @{{r}}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="p-6 border-t border-slate-100 bg-slate-50 shrink-0">
                    <button type="button" @click="$emit('update:open', false)" class="w-full bg-blue-600 text-white py-4 rounded-2xl font-black uppercase tracking-widest hover:bg-blue-500 transition-all shadow-xl text-sm">
                        確認修改並返回
                    </button>
                </div>
            </div>
        </div>
    </transition>
</script>
