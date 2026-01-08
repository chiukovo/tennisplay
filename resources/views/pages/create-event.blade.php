{{-- Create Event View --}}
<div v-if="view === 'create-event'" class="max-w-xl mx-auto pb-24 px-4 pt-4 sm:pt-0">
    {{-- Simple Header --}}
    <div class="mb-8">
        <button @click="navigateTo('events')" class="flex items-center gap-1.5 text-slate-400 hover:text-blue-600 transition-colors mb-4 group">
            <app-icon name="arrow-left" class-name="w-4 h-4"></app-icon>
            <span class="font-bold text-xs uppercase tracking-widest">返回列表</span>
        </button>
        <h2 class="text-3xl font-black text-slate-900">刊登招募</h2>
        <p class="text-slate-400 font-bold text-sm">刊登您的活動，讓球友主動找上門</p>
    </div>

    {{-- Form --}}
    <form @submit.prevent="createEvent" class="space-y-6">
        {{-- Card 1: What & Where --}}
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100 space-y-6">
            <div>
                <label class="block text-sm font-black uppercase tracking-widest text-slate-400 mb-3 ml-1">活動主題 <span class="text-red-500">*</span></label>
                <input v-model="eventForm.title" type="text" required maxlength="100"
                    class="w-full px-6 py-4 bg-slate-50 border-2 border-transparent rounded-2xl text-lg font-black focus:bg-white focus:border-blue-500 transition-all text-slate-900 placeholder:font-bold"
                    placeholder="例如：週六下午休閒網球">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-black uppercase tracking-widest text-slate-400 mb-3 ml-1">活動地區 <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <select v-model="eventForm.region" required
                            class="w-full px-6 py-4 bg-slate-50 border-2 border-transparent rounded-2xl text-lg font-black focus:bg-white focus:border-blue-500 transition-all text-slate-900 appearance-none">
                            <option value="" disabled selected>請選擇地區</option>
                            <option v-for="region in regions" :key="region" :value="region">@{{ region }}</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-400">
                            <app-icon name="chevron-down" class-name="w-5 h-5"></app-icon>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-black uppercase tracking-widest text-slate-400 mb-3 ml-1">球場名稱 <span class="text-red-500">*</span></label>
                    <input v-model="eventForm.location" type="text" required
                        class="w-full px-6 py-4 bg-slate-50 border-2 border-transparent rounded-2xl text-lg font-black focus:bg-white focus:border-blue-500 transition-all text-slate-900 placeholder:font-bold"
                        placeholder="例如：內湖運動中心">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-black uppercase tracking-widest text-slate-400 mb-3 ml-1">球場詳細地址</label>
                    <input v-model="eventForm.address" type="text"
                        class="w-full px-6 py-4 bg-slate-50 border-2 border-transparent rounded-2xl text-lg font-black focus:bg-white focus:border-blue-500 transition-all text-slate-900 placeholder:font-bold"
                        placeholder="例如：114台北市內湖區...">
                </div>
                <div>
                    <label class="block text-sm font-black uppercase tracking-widest text-slate-400 mb-3 ml-1">每人費用 <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-6 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-lg">$</span>
                        <input v-model.number="eventForm.fee" type="number" required min="0"
                            class="w-full pl-12 pr-6 py-4 bg-slate-50 border-2 border-transparent rounded-2xl text-lg font-black focus:bg-white focus:border-blue-500 transition-all text-slate-900 placeholder:font-bold"
                            placeholder="0">
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 2: When --}}
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-black uppercase tracking-widest text-slate-400 mb-3 ml-1">開始時間 <span class="text-red-500">*</span></label>
                    <input v-model="eventForm.event_date" type="datetime-local" required :min="minEventDate"
                        class="w-full px-6 py-4 bg-slate-50 border-2 border-transparent rounded-2xl text-lg font-black focus:bg-white focus:border-blue-500 transition-all text-slate-900">
                </div>
                <div>
                    <label class="block text-sm font-black uppercase tracking-widest text-slate-400 mb-3 ml-1">預計結束</label>
                    <input v-model="eventForm.end_date" type="datetime-local" :min="eventForm.event_date"
                        class="w-full px-6 py-4 bg-slate-50 border-2 border-transparent rounded-2xl text-lg font-black focus:bg-white focus:border-blue-500 transition-all text-slate-900">
                </div>
            </div>
        </div>

        {{-- Card 3: Who --}}
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100 space-y-6">
            <div>
                <label class="block text-sm font-black uppercase tracking-widest text-slate-400 mb-4 ml-1">賽制類型</label>
                <div class="flex flex-wrap gap-3">
                    <button v-for="type in [{v:'all', l:'🌐 不限'}, {v:'singles', l:'🎾 單打'}, {v:'doubles', l:'👥 雙打'}, {v:'mixed', l:'👫 混雙'}]" 
                        :key="type.v" type="button" @click="eventForm.match_type = type.v"
                        :class="['px-6 py-3.5 rounded-2xl font-black text-sm transition-all border-2', 
                            eventForm.match_type === type.v ? 'bg-slate-900 border-slate-900 text-white shadow-lg' : 'bg-slate-50 border-transparent text-slate-500 hover:border-slate-200']">
                        @{{ type.l }}
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-2">
                <div>
                    <label class="block text-sm font-black uppercase tracking-widest text-slate-400 mb-3 ml-1">招募人數 <span class="text-red-500">*</span></label>
                    <select v-model.number="eventForm.max_participants" required
                        class="w-full px-6 py-4 bg-slate-50 border-2 border-transparent rounded-2xl text-lg font-black focus:bg-white focus:border-blue-500 transition-all text-slate-900 appearance-none">
                        <option :value="0">∞ 不限人數</option>
                        <option v-for="n in [1,2,3,4,5,6,7,8,12,16]" :key="n" :value="n">徵 @{{ n }} 位球友</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-black uppercase tracking-widest text-slate-400 mb-3 ml-1">程度要求 (NTRP+)</label>
                    <select v-model="eventForm.level_min" class="w-full px-6 py-4 bg-slate-50 border-2 border-transparent rounded-2xl text-lg font-black focus:bg-white focus:border-blue-500 transition-all text-slate-900 appearance-none">
                        <option value="">🌱 不限程度</option>
                        <option v-for="l in LEVELS" :key="l" :value="l">🏆 @{{ l }} 以上</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Section 4: Notes --}}
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100 space-y-4">
            <label class="block text-sm font-black uppercase tracking-widest text-slate-400 ml-1">活動叮嚀 / 備註</label>
            <textarea v-model="eventForm.notes" rows="4"
                class="w-full px-6 py-5 bg-slate-50 border-2 border-transparent rounded-2xl text-lg font-black focus:bg-white focus:border-blue-500 transition-all placeholder:text-slate-300 text-slate-900 leading-relaxed"
                placeholder="例如：第 3、4 號場，供球，落敗者下場..."></textarea>
        </div>

        {{-- Submit Button --}}
        <div class="pt-4 pb-12">
            <button type="submit" :disabled="eventSubmitting"
                class="w-full py-5 bg-slate-900 text-white rounded-[24px] text-lg font-black tracking-widest hover:bg-blue-600 transition-all flex items-center justify-center gap-2 disabled:opacity-50">
                <span v-if="!eventSubmitting">確認刊登招募</span>
                <span v-else class="flex items-center gap-2">
                    <app-icon name="loader" class-name="animate-spin w-5 h-5"></app-icon>
                    處理中...
                </span>
            </button>
        </div>
    </form>
</div>
