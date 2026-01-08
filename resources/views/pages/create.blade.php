{{-- Create View (Refactored Wizard) --}}
<div v-if="view === 'create'" class="max-w-4xl mx-auto">
    {{-- Clickable Progress Bar --}}
    <div class="mb-10 px-4">
        <div class="flex justify-between mb-4">
            <button v-for="s in 4" :key="s" type="button" @click="tryGoToStep(s)"
                :class="['text-[10px] font-black uppercase tracking-widest transition-all duration-300 px-3 py-1.5 rounded-full border-2', 
                currentStep === s ? 'bg-blue-600 text-white border-blue-600 shadow-lg' : 
                currentStep > s ? 'text-blue-600 border-blue-200 bg-blue-50 hover:bg-blue-100' : 
                canGoToStep(s) ? 'text-slate-400 border-slate-200 hover:text-slate-500 hover:border-slate-300' :
                'text-slate-300 border-transparent cursor-not-allowed opacity-60']">
                Step @{{s}}
            </button>
        </div>
        <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
            <div class="h-full bg-blue-600 rounded-full transition-all duration-700 ease-out" :style="{ width: (currentStep / 4) * 100 + '%' }"></div>
        </div>
    </div>

    <div class="bg-white p-6 sm:p-12 rounded-[40px] shadow-2xl border border-slate-100 relative min-h-[600px] flex flex-col">
        <div class="flex items-center justify-between mb-10">
            <div>
                <h2 class="text-3xl font-black italic uppercase tracking-tighter text-slate-900">
                    <span v-if="currentStep === 1">1. 形象與姓名</span>
                    <span v-if="currentStep === 2">2. 技巧與分級</span>
                    <span v-if="currentStep === 3">3. 地區與資訊</span>
                    <span v-if="currentStep === 4">4. 預覽與發佈</span>
                </h2>
                <p class="text-xs text-slate-400 font-bold mt-1 uppercase tracking-widest">@{{ stepTitles[currentStep-1] }}</p>
            </div>
        </div>

        <form @submit.prevent="saveCard" class="flex-1 flex flex-col">
            {{-- <transition name="step" mode="out-in"> --}}
                <div :key="currentStep" class="flex-1">
                    {{-- Step 1: Photo & Name --}}
                    <div v-if="currentStep === 1" class="space-y-8">
                        {{-- Photo Section (always visible) --}}
                        <div class="flex flex-col items-center gap-6">
                            {{-- If photo exists, show preview with controls --}}
                            <div v-if="form.photo" class="w-full">
                                {{-- Photo Adjustment Mode --}}
                                <div v-if="isAdjustingPhoto" class="space-y-4 animate__animated animate__fadeIn">
                                    <div class="flex items-center justify-between px-2">
                                        <h4 class="text-sm font-black uppercase tracking-widest text-slate-900">調整相片版面</h4>
                                        <button type="button" @click="isAdjustingPhoto = false" class="bg-blue-600 text-white px-5 py-2 rounded-full text-xs font-black uppercase tracking-widest shadow-lg hover:bg-blue-500 transition-all">✓ 完成調整</button>
                                    </div>
                                    {{-- Larger, interactive preview --}}
                                    <div :class="['relative w-full max-w-[320px] mx-auto aspect-[2.5/3.5] rounded-2xl overflow-hidden shadow-2xl border-4 border-white cursor-move touch-none bg-slate-100', isAdjustingPhoto ? 'touch-none' : '']"
                                        @mousedown="startDrag($event, 'photo')" @touchstart="startDrag($event, 'photo')">
                                        <img :src="getUrl(form.photo)" 
                                            class="absolute inset-0 w-full h-full object-contain pointer-events-none"
                                            :style="{ transform: `translate(${form.photoX}%, ${form.photoY}%) scale(${form.photoScale})` }">
                                        <div class="absolute inset-0 border-2 border-blue-500/50 pointer-events-none"></div>
                                        <div class="absolute bottom-3 left-0 right-0 text-center">
                                            <span class="bg-blue-600 text-white text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest shadow-lg">拖動調整位置</span>
                                        </div>
                                    </div>
                                    {{-- Zoom Slider --}}
                                    <div class="space-y-2 px-4 max-w-[320px] mx-auto">
                                        <div class="flex justify-between text-[10px] font-black uppercase tracking-widest text-slate-400">
                                            <span>縮放大小</span>
                                            <span class="text-blue-600">@{{ Math.round(form.photoScale * 100) }}%</span>
                                        </div>
                                        <input type="range" v-model.number="form.photoScale" min="0.5" max="3" step="0.01" class="w-full h-2 bg-slate-100 rounded-full appearance-none cursor-pointer accent-blue-600">
                                    </div>
                                </div>
                                {{-- Normal Photo Preview (smaller) --}}
                                <div v-else class="flex flex-col items-center gap-4">
                                    <div class="relative group">
                                        <div class="w-40 h-52 rounded-[28px] overflow-hidden border-4 border-white shadow-2xl bg-slate-100">
                                            <img :src="getUrl(form.photo)" class="w-full h-full object-contain" :style="{ transform: `translate(${form.photoX}%, ${form.photoY}%) scale(${form.photoScale})` }">
                                        </div>
                                        <div class="absolute -bottom-3 left-1/2 -translate-x-1/2 flex gap-2">
                                            <button type="button" @click="isAdjustingPhoto = true" class="bg-slate-900 text-white px-4 py-2 rounded-full text-[10px] font-black uppercase tracking-widest shadow-xl whitespace-nowrap">調整版面</button>
                                            <button type="button" @click="triggerUpload" class="bg-white text-slate-900 px-4 py-2 rounded-full text-[10px] font-black uppercase tracking-widest shadow-xl border border-slate-100 whitespace-nowrap">更換照片</button>
                                            <button v-if="currentUser?.line_picture_url" type="button" @click="useLinePhoto()" class="bg-[#06C755] text-white px-4 py-2 rounded-full text-[10px] font-black uppercase tracking-widest shadow-xl whitespace-nowrap">使用 LINE 照片</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- Upload Placeholder --}}
                            <div v-else @click="triggerUpload" 
                                :class="['w-full h-56 border-4 border-dashed rounded-[40px] flex flex-col items-center justify-center gap-4 cursor-pointer hover:bg-blue-50/50 transition-all group',
                                stepAttempted[1] && !form.photo ? 'border-red-300 hover:border-red-400' : 'border-slate-200 hover:border-blue-400']">
                                <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 group-hover:scale-110 transition-transform duration-500 shadow-inner">
                                    <app-icon name="upload" class-name="w-8 h-8"></app-icon>
                                </div>
                                <div class="text-center space-y-1">
                                    <p class="text-lg font-black italic uppercase tracking-tighter">點擊上傳形象照 <span class="text-red-500">*</span></p>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">建議直式照片 (會自動適應)</p>
                                    <p v-if="stepAttempted[1] && !form.photo" class="text-red-500 text-xs font-bold">請上傳您的形象照</p>
                                </div>
                                <button v-if="currentUser?.line_picture_url" type="button" @click.stop="useLinePhoto()" class="mt-2 bg-[#06C755] text-white px-6 py-3 rounded-2xl text-xs font-black uppercase tracking-widest shadow-xl hover:scale-105 transition-all flex items-center gap-2">
                                    <app-icon name="line" fill="currentColor" stroke="none" class-name="w-4 h-4"></app-icon>
                                    使用 LINE 大頭貼
                                </button>
                            </div>
                            <input type="file" id="photo-upload" class="hidden" @change="handleFileUpload" accept="image/*">
                        </div>

                        {{-- Name & Gender (always visible) --}}
                        <div class="space-y-6">
                            <div class="space-y-3">
                                <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">您的姓名 <span class="text-red-500">*</span></label>
                                <input type="text" v-model="form.name" placeholder="請輸入姓名或暱稱" 
                                    :class="['w-full px-6 py-4 bg-slate-50 border-2 rounded-2xl outline-none font-black italic text-lg transition-all',
                                    stepAttempted[1] && !form.name ? 'border-red-300 focus:border-red-500' : 'border-transparent focus:border-blue-500']">
                                <p v-if="stepAttempted[1] && !form.name" class="text-red-500 text-xs font-bold mt-1">請填寫您的姓名</p>
                            </div>
                            <div class="space-y-3">
                                <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">生理性別</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <button v-for="g in genders" :key="g" type="button" @click="form.gender = g" 
                                        :class="['py-4 rounded-2xl font-black text-sm transition-all border-2', form.gender === g ? 'bg-slate-900 text-white border-slate-900 shadow-xl' : 'bg-slate-50 text-slate-400 border-transparent hover:border-slate-200']">
                                        @{{ g }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Step 2: Stats --}}
                    <div v-if="currentStep === 2" class="space-y-8">
                        <div class="space-y-4">
                            <label class="flex items-center justify-between text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                                NTRP 程度
                                <button type="button" @click="showNtrpGuide = true" class="text-blue-600 hover:scale-110 transition-transform flex items-center gap-1">
                                    <app-icon name="help" class-name="w-3.5 h-3.5"></app-icon>
                                    程度列表
                                </button>
                            </label>
                            <div class="grid grid-cols-4 gap-3">
                                <button v-for="l in levels" :key="l" type="button" @click="form.level = l"
                                    :class="['py-3 rounded-2xl font-black text-sm transition-all', form.level === l ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/30' : 'bg-slate-50 text-slate-400 hover:bg-slate-100']">
                                    @{{l}}
                                </button>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">持拍手</label>
                                <div class="flex gap-2">
                                    <button type="button" v-for="h in ['右手', '左手']" :key="h" @click="form.handed = h"
                                        :class="['flex-1 py-4 rounded-2xl font-black text-sm transition-all', form.handed === h ? 'bg-slate-900 text-white' : 'bg-slate-50 text-slate-400']">
                                        @{{h}}
                                    </button>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">反手類型</label>
                                <div class="flex gap-2">
                                    <button type="button" v-for="b in ['單反', '雙反']" :key="b" @click="form.backhand = b"
                                        :class="['flex-1 py-4 rounded-2xl font-black text-sm transition-all', form.backhand === b ? 'bg-slate-900 text-white' : 'bg-slate-50 text-slate-400']">
                                        @{{b}}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Step 3: Location & Intro --}}
                    <div v-if="currentStep === 3" class="space-y-8">
                        <div class="space-y-4">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">主要活動地區</label>
                            <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 max-h-[220px] overflow-y-auto no-scrollbar p-1">
                                <button v-for="r in regions" :key="r" type="button" @click="form.region = r"
                                    :class="['py-3 px-2 rounded-xl font-bold text-xs transition-all border-2', form.region === r ? 'bg-slate-900 text-white border-slate-900 shadow-md' : 'bg-slate-50 text-slate-400 border-transparent hover:border-slate-200']">
                                    @{{r}}
                                </button>
                            </div>
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest italic text-center">上下滑動查看更多地區</p>
                        </div>
                        <div class="space-y-4">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">約打宣告 / 個人特色</label>
                            <textarea v-model="form.intro" class="w-full px-6 py-5 bg-slate-50 border-2 border-transparent rounded-[24px] focus:border-blue-500 outline-none font-bold text-base shadow-inner h-40 resize-none" placeholder="分享一下您的打法特色，或想找什麼樣的球友..."></textarea>
                        </div>
                    </div>

                    {{-- Step 4: Final Preview, Theme & Signature --}}
                    <div v-if="currentStep === 4" class="space-y-10 flex flex-col items-center animate__animated animate__fadeIn">
                        
                        {{-- Sub-step 1: Theme Selection --}}
                        <div class="w-full space-y-4">
                            <div class="flex items-center gap-3">
                                <span class="flex items-center justify-center w-6 h-6 bg-blue-600 text-white rounded-full text-[10px] font-black">1</span>
                                <h4 class="text-sm font-black uppercase tracking-widest text-slate-900">選擇樣式</h4>
                            </div>
                            <div class="w-full flex overflow-x-auto no-scrollbar gap-2 pb-2 justify-start sm:justify-center">
                                <button v-for="(t, key) in cardThemes" :key="key" type="button" @click="form.theme = key"
                                    :class="['px-5 py-2.5 rounded-full whitespace-nowrap text-xs font-black transition-all border-2', form.theme === key ? 'bg-slate-900 text-white border-slate-900 shadow-lg' : 'bg-slate-50 text-slate-400 border-transparent hover:border-slate-200']">
                                    @{{ t.label }}
                                </button>
                            </div>
                        </div>

                        <div class="w-full max-w-[300px] transform hover:scale-[1.02] transition-all duration-500 shadow-[0_30px_60px_-15px_rgba(0,0,0,0.5)] rounded-2xl relative">
                            <player-card :player="form" :is-signing="isSigning" :is-adjusting-sig="isAdjustingSig"
                                @update-signature="handleSignatureUpdate" 
                                @edit-signature="isSigning = true"
                                @close-signing="isSigning = false" 
                                @drag-start="startDrag"
                                @sig-ready="initMoveable"></player-card>
                        </div>
                        
                        {{-- Sub-step 2: Signature --}}
                        <div class="w-full space-y-4">
                            <div class="flex items-center gap-3">
                                <span class="flex items-center justify-center w-6 h-6 bg-blue-600 text-white rounded-full text-[10px] font-black">2</span>
                                <h4 class="text-sm font-black uppercase tracking-widest text-slate-900">在您卡片上簽名</h4>
                            </div>
                            <div class="space-y-3">
                                <button type="button" @click="isSigning = true" class="w-full bg-slate-900 text-white py-4 rounded-2xl font-black text-xs uppercase tracking-[0.2em] flex items-center justify-center gap-3 hover:bg-slate-800 transition-all shadow-xl">
                                    <app-icon name="eraser" class-name="w-5 h-5 text-blue-400"></app-icon>
                                    @{{ form.signature ? '重新手寫簽名' : '點擊此處開始簽名 (推薦)' }}
                                </button>

                                <button v-if="form.signature" type="button" @click="toggleAdjustSig" 
                                    :class="['w-full py-4 rounded-2xl font-black text-xs uppercase tracking-[0.2em] flex items-center justify-center gap-3 transition-all shadow-xl', isAdjustingSig ? 'bg-blue-600 text-white hover:bg-blue-500' : 'bg-white text-slate-900 border border-slate-100 hover:bg-slate-50']">
                                    <app-icon :name="isAdjustingSig ? 'check' : 'move'" class-name="w-5 h-5"></app-icon>
                                    @{{ isAdjustingSig ? '✓ 完成調整' : '調整簽名位置' }}
                                </button>

                                <button type="button" @click="showQuickEditModal = true" class="w-full bg-white text-slate-900 border border-slate-100 py-4 rounded-2xl font-black text-xs uppercase tracking-[0.2em] flex items-center justify-center gap-3 hover:bg-slate-50 transition-all shadow-xl">
                                    <app-icon name="edit-3" class-name="w-5 h-5 text-blue-600"></app-icon>
                                    快速修改資料
                                </button>
                                <p class="text-[9px] text-slate-400 font-bold italic text-center uppercase tracking-widest">確認內容無誤後，即可點擊發佈</p>
                            </div>
                        </div>
                    </div>
                </div>
            {{-- </transition> --}}

            {{-- Navigation Buttons --}}
            <div class="mt-12 flex gap-4">
                <button v-if="currentStep > 1" type="button" @click="currentStep--" class="flex-1 py-5 rounded-2xl font-black text-xs uppercase tracking-widest text-slate-400 hover:bg-slate-50 transition-all border border-slate-100">
                    上一步
                </button>
                <button v-if="currentStep < 4" type="button" @click="tryNextStep" 
                    :class="['flex-[2] py-5 rounded-2xl font-black text-xs uppercase tracking-widest transition-all shadow-xl',
                    (currentStep === 1 && canProceedStep1) || (currentStep === 2 && canProceedStep2) || (currentStep === 3 && canProceedStep3) 
                    ? 'bg-blue-600 text-white hover:bg-blue-700' : 'bg-slate-950 text-white hover:bg-slate-800']">
                    下一步
                </button>
                <button v-if="currentStep === 4" type="submit" class="flex-[2] bg-blue-600 text-white py-5 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-blue-700 transition-all shadow-xl shadow-blue-600/20">
                    發佈球友卡
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Full Screen Preview Card (Refactored) --}}
{{-- <transition name="modal"> --}}
    <div v-if="showPreview" class="fixed inset-0 z-[600] bg-slate-950/95 backdrop-blur-2xl flex items-center justify-center p-6" @click.self="showPreview = false">
        <div class="w-full max-w-[340px] animate__animated animate__zoomIn animate__faster">
            <div class="flex justify-center mb-8">
                <span class="bg-blue-600 text-white text-[10px] font-black px-6 py-2 rounded-full uppercase tracking-widest italic shadow-xl shadow-blue-600/40">Premium Card Preview</span>
            </div>
            
            <player-card :player="form" :is-signing="isSigning" :is-adjusting-sig="false" @update-signature="handleSignatureUpdate" @close-signing="isSigning = false"></player-card>
            
            <button type="button" @click="showPreview = false" class="w-full mt-10 bg-white/10 text-white border border-white/10 py-5 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-white/20 transition-all backdrop-blur-md">
                返回編輯
            </button>
        </div>
    </div>
{{-- </transition> --}}

{{-- Quick Edit Modal --}}
<quick-edit-modal v-model:open="showQuickEditModal" :form="form" :levels="levels" :regions="regions"></quick-edit-modal>
