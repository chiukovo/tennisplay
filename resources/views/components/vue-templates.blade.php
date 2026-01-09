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
        :class="['group transition-all duration-500 capture-target relative overflow-hidden rounded-[24px]', 
                 size === 'sm' ? 'w-full aspect-[2.5/3.8]' : 'w-full max-w-[320px] aspect-[2.5/3.8]',
                 isPlaceholder ? 'opacity-30 grayscale hover:opacity-100 hover:grayscale-0' : 'hover:scale-[1.02] shadow-2xl']"
        style="container-type: inline-size;">
        
        {{-- Animated Border Glow --}}
        <div v-if="!isPlaceholder" :class="['absolute -inset-[2px] bg-gradient-to-br rounded-[24px] blur-[3px] group-hover:blur-[8px] transition-all duration-700 opacity-80 group-hover:opacity-100', themeStyle.border]"></div>
        <div v-else class="absolute -inset-[2px] bg-slate-200 rounded-[24px] opacity-30 border-2 border-dashed border-slate-300 transition-opacity group-hover:opacity-0"></div>
        
        <div :class="['relative h-full rounded-2xl overflow-hidden flex flex-col border border-white/10 transition-colors', isPlaceholder ? 'bg-slate-50' : themeStyle.bg]">
            {{-- Noise Texture Overlay --}}
            <div class="absolute inset-0 opacity-[0.03] pointer-events-none z-[5]" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 200 200%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noiseFilter%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.65%22 numOctaves=%223%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noiseFilter)%22/%3E%3C/svg%3E');"></div>

            {{-- Shine Effect --}}
            <div class="absolute inset-0 z-[60] pointer-events-none opacity-0 group-hover:opacity-20 transition-opacity duration-700 bg-gradient-to-tr from-transparent via-white to-transparent" style="transform: skewX(-20deg) translateX(-100%); transition: transform 0.8s;"></div>

            {{-- Logo Watermark --}}
            <div class="absolute top-[5cqw] right-[5cqw] z-20">
                <div class="opacity-20 group-hover:opacity-40 transition-all duration-700">
                    <app-icon name="trophy" :class-name="['w-[6cqw] h-[6cqw] drop-shadow-md', isPlaceholder ? 'text-slate-300' : themeStyle.logoIcon]"></app-icon>
                </div>
            </div>

            {{-- Main Image Area --}}
            <div class="h-[75%] relative overflow-hidden bg-slate-200 z-10 flex items-center justify-center">
                <img :src="(p?.photo) || 'https://images.unsplash.com/photo-1614743758466-e569f4791116?q=80&w=650&auto=format&fit=crop'" 
                    :class="['w-full h-full object-contain group-hover:scale-105 transition-transform duration-1000', isAdjustingSig ? 'pointer-events-none select-none' : '']"
                    :style="{ transform: `translate(${p?.photoX || 0}%, ${p?.photoY || 0}%) scale(${p?.photoScale || 1})` }">
                
                {{-- Gradient Overlay --}}
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-transparent to-transparent opacity-80 pointer-events-none"></div>
                
                {{-- NTRP Badge (Premium Style) --}}
                <div class="absolute bottom-[4cqw] left-[5cqw] flex flex-col items-start gap-[1.5cqw]">
                    <div class="relative group/badge">
                        <div class="absolute inset-0 bg-white/20 blur-md rounded-full"></div>
                        <div :class="['relative flex items-center gap-[2cqw] p-[0.5cqw] rounded-[3cqw] shadow-[0_4px_12px_rgba(0,0,0,0.3)] border border-white/20 backdrop-blur-md overflow-hidden', isPlaceholder ? 'bg-slate-400' : themeStyle.border]">
                            <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent opacity-50"></div>
                            <div class="bg-slate-900/90 px-[3.5cqw] py-[1.5cqw] rounded-[2.5cqw] flex items-center gap-[2cqw] relative z-10">
                                <span class="font-bold text-white/50 uppercase tracking-widest leading-none" style="font-size: 4cqw;">NTRP</span>
                                <span class="font-black text-white leading-none italic tracking-tighter" style="font-size: 11cqw; text-shadow: 0 2px 4px rgba(0,0,0,0.5);">@{{ p?.level || '3.5' }}</span>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Level Tag --}}
                    <div class="bg-white/5 backdrop-blur-md px-[3cqw] py-[1.5cqw] rounded-[1.5cqw] border border-white/10 max-w-[60cqw] shadow-lg">
                        <p class="font-bold text-white/90 uppercase tracking-widest italic leading-tight" style="font-size: 5.5cqw;">@{{ p ? getLevelTag(p.level) : '尚未認證' }}</p>
                    </div>
                </div>
            </div>

            <signature-pad :active="isSigning" @save="sig => $emit('update-signature', sig)" @close="$emit('close-signing')"></signature-pad>
            
            {{-- Bottom Info Section (Glassmorphism) --}}
            <div class="h-[25%] px-[6cqw] py-[3cqw] flex flex-col justify-center relative overflow-hidden">
                {{-- Background Blur & Gradient --}}
                <div class="absolute inset-0 bg-white/5 backdrop-blur-xl border-t border-white/10"></div>
                <div class="absolute inset-0 bg-gradient-to-b from-transparent to-black/60 opacity-80"></div>
                
                {{-- Content --}}
                <div class="relative z-10">
                    <h3 :class="['font-black uppercase tracking-tighter italic leading-[0.9] whitespace-nowrap pb-[1.5cqw] bg-gradient-to-r bg-clip-text text-transparent text-left drop-shadow-sm', isPlaceholder ? 'bg-slate-400' : themeStyle.border]" style="font-size: 11cqw;">
                        @{{ p?.name || '請更新卡片' }}
                    </h3>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-[1.5cqw] text-white/80">
                            <app-icon name="map-pin" class-name="w-[5cqw] h-[5cqw]" :class="themeStyle.accent"></app-icon>
                            <span class="font-bold uppercase tracking-wider italic" style="font-size: 6cqw;">@{{ p?.region || '全台' }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Signature Display (Promoted Layer) --}}
            <div v-if="p?.signature" :class="['absolute inset-0 z-[70] group/sig signature-layer', isAdjustingSig ? 'pointer-events-auto' : 'pointer-events-none']">
                <div class="relative w-full h-full">
                    <img :src="p.signature" 
                        id="target-signature"
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
</script>
