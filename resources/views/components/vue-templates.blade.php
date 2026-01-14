{{-- Vue Component Templates --}}

{{-- App Icon Template --}}
<script type="text/x-template" id="app-icon-template">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" :fill="fill || 'none'" :stroke="stroke || 'currentColor'" :stroke-width="strokeWidth || 2" stroke-linecap="round" stroke-linejoin="round" :class="className" v-html="iconPath"></svg>
</script>

{{-- Signature Pad Template --}}
<script type="text/x-template" id="signature-pad-template">
    <div v-if="active" @click.stop class="absolute inset-0 z-[100] bg-black/40 backdrop-blur-[4px] cursor-crosshair overflow-hidden rounded-2xl animate__animated animate__fadeIn animate__faster">
        <canvas ref="canvas" @mousedown="start" @mousemove="draw" @mouseup="stop" @mouseleave="stop" @touchstart="startTouch" @touchmove="moveTouch" @touchend="stop" class="w-full h-full touch-none"></canvas>
        
        {{-- Controls --}}
        <div class="absolute top-4 right-4 flex flex-col gap-3">
            <button type="button" @click.stop="$emit('close')" class="p-2.5 bg-white/10 hover:bg-white/20 text-white rounded-full backdrop-blur-md border border-white/20 transition-all shadow-xl" title="取消">
                <app-icon name="x" class-name="w-5 h-5"></app-icon>
            </button>
            <button type="button" @click.stop="clear" class="p-2.5 bg-white/10 hover:bg-white/20 text-white rounded-full backdrop-blur-md border border-white/20 transition-all shadow-xl" title="清除">
                <app-icon name="eraser" class-name="w-5 h-5"></app-icon>
            </button>
        </div>
        
        {{-- Bottom Hint and Confirm --}}
        <div class="absolute bottom-6 left-0 right-0 flex flex-col items-center gap-4 pointer-events-none">
            <span class="bg-black/60 text-white text-[10px] font-black px-5 py-2 rounded-full uppercase tracking-[0.2em] italic border border-white/10">請在此處手寫簽名</span>
            <button type="button" @click.stop="confirm" class="pointer-events-auto bg-blue-600 hover:bg-blue-500 text-white px-8 py-3 rounded-2xl font-black text-xs uppercase tracking-widest shadow-2xl transition-all hover:scale-105">
                確認簽名並保存
            </button>
        </div>
    </div>
</script>

{{-- Player Card Template --}}
<script type="text/x-template" id="player-card-template">
    <div v-if="p || isPlaceholder" ref="cardContainer"
        class="holo-container overflow-visible"
        @mousemove="handleMove"
        @touchmove.passive="handleMove"
        @mouseleave="handleLeave"
        :class="[size === 'sm' ? 'w-full aspect-[2.5/3.8]' : 'w-full max-w-[450px] aspect-[2.5/3.8]']">
        
        <div :class="['holo-card-wrapper w-full h-full card-holo transition-all duration-300 relative', 
                      (isAnimated && !isCapturing) ? 'animated' : '',
                      isHoloTheme ? 'theme-' + p.theme : '']" 
             :style="[(!isCapturing ? { transform: `rotateX(${tilt.rX}deg) rotateY(${tilt.rY}deg)` } : {}), holoStyle]">
             
            {{-- Animated Border Glow (Moved outside overflow-hidden to prevent clipping) --}}
            <div v-if="!isPlaceholder" :class="['absolute -inset-[3px] bg-gradient-to-br rounded-[32px] blur-[8px] transition-all duration-700 opacity-50 z-0', themeStyle.border]"></div>
            <div v-else class="absolute -inset-[3px] bg-slate-200 rounded-[32px] opacity-30 border-2 border-dashed border-slate-300 transition-opacity z-0"></div>

            <div :class="['group capture-target relative overflow-hidden rounded-[28px] w-full h-full z-10', 
                         isPlaceholder ? 'opacity-30 grayscale hover:opacity-100 hover:grayscale-0' : '',
                         isCapturing ? 'is-capturing' : '']"
                 style="container-type: inline-size;">
                
                <div :class="['relative h-full rounded-[28px] overflow-hidden flex flex-col border border-white/20 transition-colors shadow-inner', isPlaceholder ? 'bg-slate-50' : themeStyle.bg]">
                    {{-- Noise Texture Overlay --}}
                    <div class="absolute inset-0 opacity-[0.03] pointer-events-none z-[5]" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 200 200%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noiseFilter%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.65%22 numOctaves=%223%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noiseFilter)%22/%3E%3C/svg%3E');"></div>

                    {{-- Main Image Area --}}
                    <div class="h-[75%] relative overflow-hidden bg-slate-200 z-10 flex items-center justify-center">
                        {{-- Social Indicators (Top Right) --}}
                        <div v-if="!isPlaceholder" class="absolute top-[4cqw] right-[4cqw] z-20">
                            <div class="bg-black/40 backdrop-blur-xl px-[3.5cqw] py-[2cqw] rounded-[3cqw] border border-white/20 flex items-center gap-[4cqw] shadow-2xl">
                                <div class="flex items-center gap-[1.5cqw] group/social">
                                    <app-icon name="heart" class-name="w-[5cqw] h-[5cqw] text-white group-hover/social:text-red-400 group-hover/social:scale-110 transition-all drop-shadow-sm"></app-icon>
                                    <span class="text-white font-black leading-none drop-shadow-sm" style="font-size: 4.2cqw;">@{{ p?.likes_count || 0 }}</span>
                                </div>
                                <div class="w-[0.5cqw] h-[3cqw] bg-white/20 rounded-full"></div>
                                <div class="flex items-center gap-[1.5cqw] group/social">
                                    <app-icon name="message-circle" class-name="w-[5cqw] h-[5cqw] text-white group-hover/social:text-blue-400 group-hover/social:scale-110 transition-all drop-shadow-sm"></app-icon>
                                    <span class="text-white font-black leading-none drop-shadow-sm" style="font-size: 4.2cqw;">@{{ p?.comments_count || 0 }}</span>
                                </div>
                            </div>
                        </div>

                        <img :src="(p?.photo) || 'https://images.unsplash.com/photo-1614743758466-e569f4791116?q=80&w=650&auto=format&fit=crop'" 
                            crossorigin="anonymous"
                            :class="['w-full h-full object-contain transition-transform duration-1000', isAdjustingSig ? 'pointer-events-none select-none' : '']"
                            :style="{ transform: `translate(${p?.photoX || 0}%, ${p?.photoY || 0}%) scale(${p?.photoScale || 1})` }">
                        
                        {{-- Gradient Overlay --}}
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-transparent to-transparent opacity-80 pointer-events-none"></div>
                        
                        {{-- NTRP Badge (Premium Style) --}}
                        <div class="absolute bottom-[4cqw] left-[5cqw] flex flex-col items-start gap-[1.5cqw]">
                            <div class="relative group/badge">
                                <div class="absolute inset-0 bg-white/10 blur-xl rounded-full"></div>
                                <div :class="['relative flex items-center gap-[2cqw] p-[0.6cqw] rounded-[4cqw] shadow-[0_8px_20px_rgba(0,0,0,0.4)] border border-white/30 backdrop-blur-xl overflow-hidden', isPlaceholder ? 'bg-slate-400' : themeStyle.border]">
                                    <div class="absolute inset-0 bg-gradient-to-br from-white/20 to-transparent opacity-60"></div>
                                    <div class="bg-slate-900/95 px-[4cqw] py-[2cqw] rounded-[3.5cqw] flex items-center gap-[2cqw] relative z-10">
                                        <span class="font-bold text-white/40 uppercase tracking-widest leading-none" style="font-size: 3.5cqw;">NTRP</span>
                                        <span class="font-black text-white leading-none italic tracking-tighter" style="font-size: 11cqw; text-shadow: 0 4px 8px rgba(0,0,0,0.6);">@{{ p?.level || '3.5' }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Level Tag (Softer) --}}
                            <div class="bg-white/10 backdrop-blur-xl px-[4cqw] py-[2cqw] rounded-[2.5cqw] border border-white/20 max-w-[60cqw] shadow-xl">
                                <p class="font-bold text-white uppercase tracking-[0.15em] italic leading-tight" style="font-size: 5.5cqw; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">@{{ p ? getLevelTag(p.level) : '尚未認證' }}</p>
                            </div>
                        </div>
                    </div>

                    <signature-pad :active="isSigning" @save="sig => $emit('update-signature', sig)" @close="$emit('close-signing')"></signature-pad>
                    
                    {{-- Bottom Info Section (Glassmorphism) --}}
                    <div class="h-[25%] px-[6cqw] py-[3cqw] flex flex-col justify-center relative overflow-hidden">
                        {{-- Background Blur & Gradient (More Glassy) --}}
                        <div class="absolute inset-0 bg-white/10 backdrop-blur-2xl border-t border-white/20"></div>
                        <div class="absolute inset-0 bg-gradient-to-b from-transparent to-black/60 opacity-80"></div>
                        
                        {{-- Content --}}
                        <div class="relative z-10">
                            <h3 :class="['font-black uppercase tracking-tighter italic leading-[0.9] whitespace-nowrap pb-[1.5cqw] bg-gradient-to-r bg-clip-text text-transparent text-left drop-shadow-sm', isPlaceholder ? 'bg-slate-400' : themeStyle.border]" style="font-size: 11cqw;">
                                @{{ p?.name || '請更新卡片' }}
                            </h3>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-[3cqw] text-white/80">
                                    <div class="flex items-center gap-[1.5cqw]">
                                        <app-icon name="map-pin" class-name="w-[5cqw] h-[5cqw]" :class="themeStyle.accent"></app-icon>
                                        <span class="font-bold uppercase tracking-wider italic" style="font-size: 6cqw;">@{{ p?.region || '全台' }}</span>
                                    </div>
                                    <div class="w-[0.5cqw] h-[4cqw] bg-white/30 rounded-full"></div>
                                    <div class="flex items-center gap-[1.5cqw]">
                                        <app-icon :name="p?.gender === '女' ? 'female' : 'male'" class-name="w-[5cqw] h-[5cqw]" :class="themeStyle.accent"></app-icon>
                                        <span class="font-bold uppercase tracking-wider italic" style="font-size: 6cqw;">@{{ p?.gender || '男' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Signature Display (Promoted Layer) --}}
                    <div v-if="p?.signature" :class="['absolute inset-0 z-[70] group/sig signature-layer', isAdjustingSig ? 'pointer-events-auto' : 'pointer-events-none']">
                        <div class="relative w-full h-full">
                            <img :src="p.signature" 
                                id="target-signature"
                                crossorigin="anonymous"
                                draggable="false"
                                :class="['absolute origin-center drop-shadow-[0_2px_4px_rgba(0,0,0,0.3)]', isAdjustingSig ? 'pointer-events-auto cursor-move' : 'pointer-events-none']"
                                :style="{ 
                                    width: `${p.sigWidth || 100}%`,
                                    height: 'auto',
                                    left: `${p.sigX ?? 50}%`, 
                                    top: `${p.sigY ?? 50}%`,
                                    transform: `translate(-50%, -50%) scale(${p.sigScale || 1}) rotate(${p.sigRotate || 0}deg)` 
                                }"
                                @load="$emit('sig-ready', $event.target)">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</script>

{{-- Privacy Policy Modal Template --}}
<script type="text/x-template" id="privacy-modal-template">
    <transition name="modal">
        <div v-if="modelValue" class="fixed inset-0 z-[300] flex items-center justify-center p-4 sm:p-6" @click.self="$emit('update:modelValue', false)">
            <div class="bg-white w-full max-w-2xl rounded-[40px] shadow-2xl overflow-hidden flex flex-col max-h-[90vh] animate__animated animate__zoomIn animate__faster">
                {{-- Header --}}
                <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <div>
                        <h3 class="text-2xl font-black italic uppercase tracking-tight text-slate-900">隱私權政策</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Privacy Policy & Terms</p>
                    </div>
                    <button @click="$emit('update:modelValue', false)" class="w-10 h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:text-slate-600 hover:border-slate-300 transition-all shadow-sm">
                        <app-icon name="x" class-name="w-5 h-5"></app-icon>
                    </button>
                </div>

                {{-- Content --}}
                <div class="flex-1 overflow-y-auto p-8 sm:p-10 space-y-8 custom-scrollbar">
                    <section class="space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-blue-100 rounded-xl flex items-center justify-center">
                                <app-icon name="shield" class-name="w-4 h-4 text-blue-600"></app-icon>
                            </div>
                            <h4 class="text-lg font-black text-slate-900">資料收集與使用</h4>
                        </div>
                        <p class="text-slate-500 text-sm leading-relaxed font-medium">
                            為了提供專業的網球約打服務，我們會透過 LINE 登入收集您的公開個人檔案資訊（包含 LINE 顯示名稱、頭像圖片及唯一識別碼）。這些資訊僅用於建立您的球友卡、處理約打邀請以及發送相關通知。
                        </p>
                    </section>

                    <section class="space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-green-100 rounded-xl flex items-center justify-center">
                                <app-icon name="lock" class-name="w-4 h-4 text-green-600"></app-icon>
                            </div>
                            <h4 class="text-lg font-black text-slate-900">個人資料保護</h4>
                        </div>
                        <p class="text-slate-500 text-sm leading-relaxed font-medium">
                            我們承諾不會在未經您許可的情況下，將您的個人資料提供給第三方，或用於非本平台服務之用途。您的球友卡資訊（如 NTRP 等級、地區）將公開顯示於平台大廳，以便其他球友與您聯繫。
                        </p>
                    </section>

                    <section class="space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-amber-100 rounded-xl flex items-center justify-center">
                                <app-icon name="user-check" class-name="w-4 h-4 text-amber-600"></app-icon>
                            </div>
                            <h4 class="text-lg font-black text-slate-900">使用者權利</h4>
                        </div>
                        <p class="text-slate-500 text-sm leading-relaxed font-medium">
                            您可以隨時透過「個人設定」更新您的球友卡資訊。若您希望刪除帳號及所有相關資料，請聯繫系統管理員（Email: <a href="mailto:q8156697@gmail.com" class="text-blue-600 hover:underline">q8156697@gmail.com</a>），我們將在核對身分後為您處理。
                        </p>
                    </section>

                    <section class="space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-purple-100 rounded-xl flex items-center justify-center">
                                <app-icon name="alert-circle" class-name="w-4 h-4 text-purple-600"></app-icon>
                            </div>
                            <h4 class="text-lg font-black text-slate-900">免責聲明</h4>
                        </div>
                        <p class="text-slate-500 text-sm leading-relaxed font-medium">
                            本平台僅提供約打媒合資訊，實際打球過程中的人身安全、場地糾紛或費用爭議，請由雙方自行協商解決，本平台不負法律責任。
                        </p>
                    </section>
                </div>

                {{-- Footer --}}
                <div class="px-8 py-6 bg-slate-50 border-t border-slate-100 flex flex-col items-center gap-4">
                    <button @click="$emit('update:modelValue', false)" class="w-full sm:w-auto px-10 py-3 bg-slate-900 text-white rounded-2xl font-black uppercase tracking-widest text-xs hover:bg-blue-600 transition-all shadow-lg active:scale-95">
                        我已瞭解
                    </button>
                    <a href="/privacy" @click.prevent="$emit('update:modelValue', false); navigateTo('privacy')" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest hover:text-blue-600 transition-colors">
                        查看完整版本政策
                    </a>
                </div>
            </div>
        </div>
    </transition>
</script>
{{-- Share Modal Template --}}
<script type="text/x-template" id="share-modal-template">
    <transition name="modal">
        <div v-if="modelValue" class="fixed inset-0 z-[400] flex items-center justify-center p-4 sm:p-6 bg-slate-900/60 backdrop-blur-sm modal-content" @click.self="$emit('update:modelValue', false)">
            <div class="bg-white w-full max-w-md rounded-[40px] shadow-2xl overflow-hidden flex flex-col animate__animated animate__zoomIn animate__faster">
                {{-- Header --}}
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <div>
                        <h3 class="text-xl font-black italic uppercase tracking-tight text-slate-900">分享個人資料</h3>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Share Profile</p>
                    </div>
                    <button @click="$emit('update:modelValue', false)" class="w-8 h-8 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:text-slate-600 hover:border-slate-300 transition-all shadow-sm">
                        <app-icon name="x" class-name="w-4 h-4"></app-icon>
                    </button>
                </div>

                {{-- Content --}}
                <div class="p-6 space-y-6 relative overflow-y-auto max-h-[80vh] custom-scrollbar">
                    {{-- Loading Overlay --}}
                    <div v-if="isCapturing" class="absolute inset-0 z-[100] bg-white/90 backdrop-blur-md flex flex-col items-center justify-center gap-4 animate__animated animate__fadeIn">
                        <div class="relative">
                            <div class="w-12 h-12 border-4 border-slate-100 rounded-full"></div>
                            <div class="absolute top-0 left-0 w-12 h-12 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                        </div>
                        <p class="text-[10px] font-black text-slate-900 uppercase tracking-widest">正在生成圖片...</p>
                    </div>

                    {{-- Large Preview & Download --}}
                    <div class="flex flex-col items-center gap-5">
                        <div class="w-44 aspect-[2.5/3.8] relative group">
                            <player-card :player="player" size="sm" :is-capturing="isCapturing" class="pointer-events-none"></player-card>
                        </div>
                        
                        <button @click="downloadCard" class="w-full py-3.5 bg-blue-600 text-white rounded-2xl font-black uppercase tracking-widest text-xs shadow-xl shadow-blue-500/20 hover:bg-blue-700 transition-all active:scale-95 flex items-center justify-center gap-2.5">
                            <app-icon name="upload" class-name="w-4 h-4"></app-icon>
                            下載球員卡圖片
                        </button>
                    </div>

                    {{-- Share Options Grid (Compact) --}}
                    <div class="grid grid-cols-4 gap-x-2 gap-y-5">
                        {{-- LINE --}}
                        <button @click="shareToLine" class="flex flex-col items-center gap-1.5 group">
                            <div class="w-11 h-11 bg-[#06C755] rounded-xl flex items-center justify-center shadow-sm group-hover:scale-110 transition-all">
                                <app-icon name="line" class-name="w-5 h-5 text-white"></app-icon>
                            </div>
                            <span class="text-[8px] font-bold text-slate-500 uppercase tracking-wider">LINE</span>
                        </button>

                        {{-- Instagram --}}
                        <button @click="shareToInstagram" class="flex flex-col items-center gap-1.5 group">
                            <div class="w-11 h-11 bg-gradient-to-tr from-[#F58529] via-[#DD2A7B] to-[#8134AF] rounded-xl flex items-center justify-center shadow-sm group-hover:scale-110 transition-all">
                                <app-icon name="instagram" class-name="w-5 h-5 text-white"></app-icon>
                            </div>
                            <span class="text-[8px] font-bold text-slate-500 uppercase tracking-wider">IG</span>
                        </button>

                        {{-- Threads --}}
                        <button @click="shareToThreads" class="flex flex-col items-center gap-1.5 group">
                            <div class="w-11 h-11 bg-black rounded-xl flex items-center justify-center shadow-sm group-hover:scale-110 transition-all">
                                <app-icon name="at-sign" class-name="w-5 h-5 text-white"></app-icon>
                            </div>
                            <span class="text-[8px] font-bold text-slate-500 uppercase tracking-wider">Threads</span>
                        </button>

                        {{-- Facebook --}}
                        <button @click="shareToFacebook" class="flex flex-col items-center gap-1.5 group">
                            <div class="w-11 h-11 bg-[#1877F2] rounded-xl flex items-center justify-center shadow-sm group-hover:scale-110 transition-all">
                                <app-icon name="facebook" class-name="w-5 h-5 text-white"></app-icon>
                            </div>
                            <span class="text-[8px] font-bold text-slate-500 uppercase tracking-wider">FB</span>
                        </button>

                        {{-- X (Twitter) --}}
                        <button @click="shareToX" class="flex flex-col items-center gap-1.5 group">
                            <div class="w-11 h-11 bg-slate-900 rounded-xl flex items-center justify-center shadow-sm group-hover:scale-110 transition-all">
                                <app-icon name="twitter" class-name="w-4 h-4 text-white"></app-icon>
                            </div>
                            <span class="text-[8px] font-bold text-slate-500 uppercase tracking-wider">X</span>
                        </button>

                        {{-- WhatsApp --}}
                        <button @click="shareToWhatsApp" class="flex flex-col items-center gap-1.5 group">
                            <div class="w-11 h-11 bg-[#25D366] rounded-xl flex items-center justify-center shadow-sm group-hover:scale-110 transition-all">
                                <app-icon name="whatsapp" class-name="w-5 h-5 text-white"></app-icon>
                            </div>
                            <span class="text-[8px] font-bold text-slate-500 uppercase tracking-wider">WhatsApp</span>
                        </button>

                        {{-- Telegram --}}
                        <button @click="shareToTelegram" class="flex flex-col items-center gap-1.5 group">
                            <div class="w-11 h-11 bg-[#0088cc] rounded-xl flex items-center justify-center shadow-sm group-hover:scale-110 transition-all">
                                <app-icon name="telegram" class-name="w-5 h-5 text-white"></app-icon>
                            </div>
                            <span class="text-[8px] font-bold text-slate-500 uppercase tracking-wider">Telegram</span>
                        </button>

                        {{-- Native Share / More --}}
                        <button @click="shareNative" class="flex flex-col items-center gap-1.5 group">
                            <div class="w-11 h-11 bg-slate-100 rounded-xl flex items-center justify-center shadow-sm group-hover:scale-110 transition-all">
                                <app-icon name="share-2" class-name="w-5 h-5 text-slate-600"></app-icon>
                            </div>
                            <span class="text-[8px] font-bold text-slate-500 uppercase tracking-wider">更多</span>
                        </button>
                    </div>

                    {{-- Link Display & Copy Area --}}
                    <div class="pt-4 border-t border-slate-100">
                        <div class="flex items-center gap-2 p-1.5 bg-slate-50 rounded-xl border border-slate-100 group">
                            <div class="flex-1 px-2 py-1 overflow-hidden">
                                <p class="text-[10px] font-bold text-slate-500 truncate select-all">@{{ shareUrl }}</p>
                            </div>
                            <button @click="copyLink" class="px-4 py-2 bg-white text-blue-600 rounded-lg text-[9px] font-black uppercase tracking-widest shadow-sm border border-slate-100 hover:bg-blue-600 hover:text-white hover:border-blue-600 transition-all active:scale-95">
                                複製連結
                            </button>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100">
                    <p class="text-[9px] font-bold text-slate-400 text-center leading-relaxed">
                        提示：您可以複製連結或使用社群按鈕快速分享。
                    </p>
                </div>
            </div>
        </div>
    </transition>
</script>
