{{-- Vue Component Templates --}}

{{-- App Icon Template --}}
<script type="text/x-template" id="app-icon-template">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" :fill="fill || 'none'" :stroke="stroke || 'currentColor'" :stroke-width="strokeWidth || 2" stroke-linecap="round" stroke-linejoin="round" :class="className" v-html="iconPath"></svg>
</script>

{{-- Signature Pad Template --}}
<script type="text/x-template" id="signature-pad-template">
    <teleport to="body">
        <transition name="fade">
            <div v-if="active" @click.stop class="fixed inset-0 z-[1000] bg-slate-950/95 backdrop-blur-2xl flex flex-col items-center justify-center p-6">
                {{-- Header --}}
                <div class="w-full max-w-[400px] flex items-center justify-between mb-8">
                    <div class="flex items-center gap-3">
                        <div class="w-1.5 h-6 bg-blue-600 rounded-full"></div>
                        <h4 class="text-lg font-black uppercase tracking-widest text-white italic">手寫簽名</h4>
                    </div>
                    <button type="button" @click.stop="$emit('close')" class="p-2.5 bg-white/10 hover:bg-white/20 text-white rounded-xl transition-all border border-white/10">
                        <app-icon name="x" class-name="w-5 h-5"></app-icon>
                    </button>
                </div>

                {{-- Interactive Pad Area --}}
                <div class="relative w-full max-w-[500px] aspect-video rounded-3xl overflow-hidden shadow-2xl border-4 border-white/20 bg-slate-900/50 touch-none">
                    <canvas ref="canvas" 
                        @mousedown="start" @mousemove="draw" @mouseup="stop" @mouseleave="stop" 
                        @touchstart="startTouch" @touchmove="moveTouch" @touchend="stop" 
                        class="absolute inset-0 w-full h-full"></canvas>
                    
                    {{-- Grid Guide Overlay (Subtle) --}}
                    <div class="absolute inset-0 pointer-events-none opacity-5 flex items-center justify-center">
                        <div class="w-full h-px bg-white"></div>
                    </div>
                </div>

                {{-- Footer Instructions & Actions --}}
                <div class="mt-10 flex flex-col items-center gap-6 w-full max-w-[400px]">
                    <div class="text-center space-y-2">
                        <p class="text-white font-black uppercase tracking-[0.2em] text-xs">請在上方區域橫向簽名</p>
                        <p class="text-white/40 font-bold text-[10px]">完成後點擊確認儲存</p>
                    </div>

                    <div class="flex gap-4 w-full">
                        <button type="button" @click.stop="clear" class="flex-1 py-4 bg-white/5 hover:bg-white/10 text-white rounded-2xl font-black uppercase tracking-widest text-xs border border-white/10 transition-all">
                            清除重寫
                        </button>
                        <button type="button" @click.stop="confirm" class="flex-[2] py-4 bg-blue-600 hover:bg-blue-500 text-white rounded-2xl font-black uppercase tracking-widest text-xs shadow-xl shadow-blue-500/30 transition-all hover:scale-[1.02] active:scale-95">
                            確認簽名並保存
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>
</script>


{{-- Player Card Template --}}
<script type="text/x-template" id="player-card-template">
    <div v-if="p || isPlaceholder" ref="cardContainer"
        class="holo-container overflow-visible relative transition-opacity duration-150"
        :class="isScaleReady ? 'opacity-100' : 'opacity-0'"
        :style="{ height: containerHeight + 'px' }">
        
        {{-- Internal Scalable Card --}}
        <div :class="['holo-card-wrapper card-holo transition-all duration-300 absolute top-0 left-0 origin-top-left', p?.theme ? `theme-${p.theme}` : '', size === 'sm' ? 'card-sm' : '']" 
             :style="[{ width: '450px', height: '684px', transform: `scale(${cardScale}) translateZ(0)` }, holoStyle]">
             
            {{-- Shine Effect Layer --}}
            <div v-if="['gold', 'platinum', 'holographic'].includes(p?.theme)" class="card-shine"></div>

            <div :class="['group capture-target relative overflow-hidden rounded-[32px] w-full h-full z-10 ring-1 ring-white/10 shadow-[0_24px_60px_-40px_rgba(15,23,42,0.6)]', 
                         isPlaceholder ? 'opacity-30 grayscale hover:opacity-100 hover:grayscale-0' : '',
                         isCapturing ? 'is-capturing' : '',
                         p?.is_coach ? 'shadow-[0_30px_80px_-40px_rgba(251,191,36,0.6)]' : '']">
                
                <div :class="['relative h-full rounded-[32px] overflow-hidden flex flex-col border transition-colors shadow-inner', (isPlaceholder && !p?.theme) ? 'bg-slate-50 border-white/20' : `${themeStyle.bg} border-white/10`]">
                    
                    {{-- Main Image Area --}}
                    <div class="h-[484px] relative overflow-hidden bg-slate-200 z-10 flex items-center justify-center">

                        {{-- Player Photo --}}
                        <div :class="['absolute inset-0 z-10 overflow-hidden', isAdjustingSig ? 'pointer-events-none select-none' : '']">
                            <img v-if="isVisible" :src="photoUrl"
                                loading="lazy"
                                decoding="async"
                                crossorigin="anonymous"
                                :class="['absolute inset-0 w-full h-full object-cover transition-transform duration-500', isPhotoLoaded ? 'opacity-100' : 'opacity-0']"
                                :style="{ transform: `translate(${p?.photoX || 0}%, ${p?.photoY || 0}%) scale(${p?.photoScale || 1})` }"
                                v-on:load="isPhotoLoaded = true"
                                v-on:error="isPhotoLoaded = true">
                            <div v-if="!isPhotoLoaded" class="absolute inset-0 bg-slate-200"></div>
                        </div>
                        
                        {{-- Gradient Overlay --}}
                        <div class="absolute inset-0 bg-gradient-to-b from-white/15 via-transparent to-transparent opacity-50 pointer-events-none z-[14]"></div>
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-transparent to-transparent opacity-60 pointer-events-none z-[15]"></div>
                        
                        {{-- NTRP / Coach Badges --}}
                        <div class="absolute inset-x-0 top-[22px] px-[22px] flex justify-end items-start z-30 pointer-events-none">
                            <div v-if="p?.is_coach" class="bg-gradient-to-r from-amber-400 to-amber-600 px-[20px] py-[10px] rounded-[14px] shadow-2xl border border-white/40 flex items-center gap-[10px] animate__animated animate__fadeInRight">
                                <app-icon name="shield-check" class-name="w-[20px] h-[20px] text-white"></app-icon>
                                <span class="text-white font-bold uppercase tracking-[0.2em] text-[16px] italic" style="text-shadow: 0 2px 4px rgba(0,0,0,0.3);">專業教練</span>
                            </div>
                        </div>

                        {{-- Bottom Left Area (NTRP) --}}
                        <div class="absolute bottom-[18px] left-[22px] flex flex-col items-start gap-[7px] z-20">
                            <div class="relative group/badge">
                                <div class="absolute inset-0 bg-white/10 blur-xl rounded-full"></div>
                                <div :class="['relative flex items-center gap-[9px] p-[3px] rounded-[18px] shadow-lg border border-white/30 overflow-hidden', isPlaceholder ? 'bg-slate-400' : themeStyle.border]">
                                    <div class="absolute inset-0 bg-gradient-to-br from-white/20 to-transparent opacity-60"></div>
                                    <div class="bg-slate-900/95 px-[18px] py-[9px] rounded-[16px] flex items-center gap-[9px] relative z-10">
                                        <span class="font-bold text-white/40 uppercase tracking-widest leading-none text-[16px]">NTRP</span>
                                        <span class="font-black text-white leading-none italic tracking-tighter text-[50px]" style="text-shadow: 0 4px 8px rgba(0,0,0,0.6);">@{{ p?.level || '3.5' }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Level Tag --}}
                            <div class="bg-black/60 px-[18px] py-[9px] rounded-[11px] border border-white/20 max-w-[350px] shadow-lg">
                                <p class="font-bold text-white uppercase tracking-[0.15em] italic leading-none text-[25px]" style="text-shadow: 0 2px 4px rgba(0,0,0,0.3);">@{{ p ? getLevelTag(p.level) : '尚未認證' }}</p>
                            </div>
                        </div>
                    </div>

                    <signature-pad :active="isSigning" @save="sig => $emit('update-signature', sig)" @close="$emit('close-signing')"></signature-pad>
                    
                    {{-- Bottom Info Section --}}
                    <div class="h-[200px] px-[27px] pt-[14px] pb-[30px] flex flex-col justify-end gap-2 relative overflow-hidden z-[80] rounded-b-[32px]">
                        <div class="absolute inset-0 bg-black/70 border-t border-white/20 rounded-b-[32px]"></div>
                        <div class="absolute top-0 left-0 right-0 h-px bg-white/20"></div>
                        <div class="absolute inset-0 bg-gradient-to-b from-transparent to-black/60 opacity-80 rounded-b-[32px]"></div>
                        
                        <div class="relative z-10 pt-1">
                            <h3 :class="['font-black uppercase tracking-tighter italic leading-[1.05] pb-[5px] text-left drop-shadow-[0_2px_4px_rgba(0,0,0,0.5)] overflow-hidden line-clamp-2', isPlaceholder ? 'opacity-50 text-white' : themeStyle.name]" 
                                :style="{ fontSize: nameFontSize, textShadow: '0 4px 12px rgba(0,0,0,0.5)' }">
                                @{{ p?.name || '請更新卡片' }}
                            </h3>
                            <div class="flex items-center justify-between">
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-[14px] text-white/80">
                                        <div class="flex items-center gap-[7px]">
                                            <app-icon name="map-pin" class-name="w-[22px] h-[22px]" :class="themeStyle.accent"></app-icon>
                                            <span class="font-bold uppercase tracking-wider italic text-[27px]">@{{ displayRegion }}</span>
                                        </div>
                                        <div class="w-[2px] h-[18px] bg-white/30 rounded-full"></div>
                                        <div class="flex items-center gap-[7px]">
                                            <app-icon :name="p?.gender === '女' ? 'female' : 'male'" class-name="w-[22px] h-[22px]" :class="themeStyle.accent"></app-icon>
                                            <span class="font-bold uppercase tracking-wider italic text-[27px]">@{{ p?.gender || '男' }}</span>
                                        </div>
                                    </div>
                                    <div v-if="p?.average_rating" class="flex items-center gap-[7px] mt-1">
                                        <span class="text-[22px] font-black text-white leading-none">@{{ p.average_rating }}</span>
                                        <div class="flex gap-0.5">
                                            <app-icon v-for="i in 5" :key="i" name="star" :class-name="i <= Math.round(p.average_rating) ? 'w-[18px] h-[18px] text-amber-400' : 'w-[18px] h-[18px] text-slate-600'" :fill="i <= Math.round(p.average_rating) ? 'currentColor' : 'none'"></app-icon>
                                        </div>
                                        <span class="text-[18px] font-bold text-slate-400 leading-none">(@{{ p.ratings_count }})</span>
                                    </div>
                                    <div v-if="!isPlaceholder" class="flex items-center gap-4 text-white/80 mt-1">
                                        <div class="flex items-center gap-1.5">
                                            <app-icon name="heart" class-name="w-[18px] h-[18px] text-white"></app-icon>
                                            <span class="text-[17px] font-black leading-none">@{{ p?.likes_count || 0 }}</span>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <app-icon name="message-circle" class-name="w-[18px] h-[18px] text-white"></app-icon>
                                            <span class="text-[17px] font-black leading-none">@{{ p?.comments_count || 0 }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    
                    {{-- Signature Display (Must be on top of everything) --}}
                    <div v-if="p?.signature" :class="['absolute inset-0 z-[100] group/sig signature-layer', isAdjustingSig ? 'pointer-events-auto' : 'pointer-events-none']">
                        <div class="relative w-full h-full">
                            <img :src="p.signature" 
                                loading="lazy"
                                decoding="async"
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
        <div v-if="modelValue" class="fixed inset-0 z-[300] flex items-center justify-center p-4 sm:p-6">
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
                            我們承諾不會在未經您許可的情況下，將您的個人資料提供給第三方，或用於非本平台服務之用途。您的球友卡資訊（如 NTRP 等級、地區）將公開顯示於找球友，以便其他球友與您聯繫。
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
        <div v-if="modelValue" class="fixed inset-0 z-[400] flex items-center justify-center p-4 sm:p-6 bg-slate-900/60 backdrop-blur-sm modal-content">
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
