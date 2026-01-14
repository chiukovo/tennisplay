<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=450">
    <title>Card Render</title>
    
    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&family=Noto+Sans+TC:wght@400;700;900&display=swap" rel="stylesheet">
    
    {{-- Tailwind CDN for consistent styling --}}
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html, body {
            width: 450px;
            height: 684px;
            overflow: hidden;
            font-family: 'Noto Sans TC', 'Inter', sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            background: transparent;
        }
        
        /* Theme gradients */
        .theme-gold { --gradient: linear-gradient(135deg, #f59e0b, #fde68a, #d97706); }
        .theme-platinum { --gradient: linear-gradient(135deg, #94a3b8, #ffffff, #64748b); }
        .theme-holographic { --gradient: linear-gradient(135deg, #ec4899, #06b6d4, #fde047, #a855f7); }
        .theme-onyx { --gradient: linear-gradient(135deg, #1e293b, #475569, #0f172a); }
        .theme-sakura { --gradient: linear-gradient(135deg, #f472b6, #fbcfe8, #ec4899); }
        .theme-standard { --gradient: linear-gradient(135deg, #3b82f6, #38bdf8, #6366f1); }
        
        .card-border {
            background: var(--gradient);
        }
        
        /* Accent colors for themes */
        .theme-gold .accent { color: #eab308; }
        .theme-platinum .accent { color: #60a5fa; }
        .theme-holographic .accent { color: #22d3ee; }
        .theme-onyx .accent { color: #94a3b8; }
        .theme-sakura .accent { color: #f472b6; }
        .theme-standard .accent { color: #3b82f6; }
        
        /* SVG Text fallback */
        .name-svg {
            font-family: 'Noto Sans TC', 'Inter', sans-serif;
            font-weight: 900;
            font-style: italic;
        }
        
        /* SVG Icons inline */
        .icon {
            display: inline-block;
            width: 22px;
            height: 22px;
        }
    </style>
</head>
<body>
@php
    $player = $player ?? null;
    if (!$player) {
        echo '<div style="color:red;padding:20px;">Player not found</div>';
        return;
    }
    
    $theme = $player->theme ?? 'standard';
    $themeClass = 'theme-' . $theme;
    
    // Resolve photo URL
    $photoUrl = $player->photo_url;
    if (!$photoUrl) {
        $photoUrl = 'https://images.unsplash.com/photo-1614743758466-e569f4791116?q=80&w=650&auto=format&fit=crop';
    }
    
    // Resolve signature URL
    $signatureUrl = $player->signature_url;
    
    // Position values
    $photoX = $player->photo_x ?? 0;
    $photoY = $player->photo_y ?? 0;
    $photoScale = $player->photo_scale ?? 1;
    
    $sigX = $player->sig_x ?? 50;
    $sigY = $player->sig_y ?? 50;
    $sigScale = $player->sig_scale ?? 1;
    $sigRotate = $player->sig_rotate ?? 0;
    $sigWidth = $player->sig_width ?? 100;
    
    // NTRP level descriptions - Sync with config/tennis.php
    $levelTags = config('tennis.level_tags');
    $levelTag = $levelTags[$player->level] ?? '網球愛好者';

    // SVG Gradient Colors
    $gradients = [
        'gold' => ['#fde68a', '#f59e0b'],
        'platinum' => ['#ffffff', '#cbd5e1'],
        'holographic' => ['#ec4899', '#06b6d4'],
        'onyx' => ['#ffffff', '#94a3b8'],
        'sakura' => ['#fbcfe8', '#ec4899'],
        'standard' => ['#60a5fa', '#3b82f6'],
    ];
    $currentGradient = $gradients[$theme] ?? $gradients['standard'];
@endphp

<div class="{{ $themeClass }}" style="width: 450px; height: 684px; position: relative;">
    {{-- Border Glow --}}
    <div class="card-border absolute -inset-[3px] rounded-[32px] blur-[8px] opacity-50" style="z-index: 0;"></div>
    
    {{-- Main Card --}}
    <div class="absolute inset-0 rounded-[28px] overflow-hidden bg-slate-900 border border-white/20 flex flex-col" style="z-index: 10;">
        
        {{-- Noise Texture --}}
        <div class="absolute inset-0 opacity-[0.03] pointer-events-none" style="z-index: 5; background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 200 200%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noiseFilter%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.65%22 numOctaves=%223%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noiseFilter)%22/%3E%3C/svg%3E');"></div>
        
        {{-- Main Image Area --}}
        <div class="relative overflow-hidden bg-slate-200 flex items-center justify-center" style="height: 513px; z-index: 10;">
            {{-- Social Indicators --}}
            <div class="absolute top-[18px] right-[18px] z-20">
                <div class="bg-black/40 backdrop-blur-xl px-[16px] py-[9px] rounded-[14px] border border-white/20 flex items-center gap-[18px] shadow-2xl">
                    <div class="flex items-center gap-[7px]">
                        <svg class="w-[22px] h-[22px] text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                        <span class="text-white font-black text-[19px] leading-none">{{ $player->likes_count ?? 0 }}</span>
                    </div>
                    <div class="w-[2px] h-[14px] bg-white/20 rounded-full"></div>
                    <div class="flex items-center gap-[7px]">
                        <svg class="w-[22px] h-[22px] text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                        <span class="text-white font-black text-[19px] leading-none">{{ $player->comments_count ?? 0 }}</span>
                    </div>
                </div>
            </div>
            
            {{-- Player Photo --}}
            <div class="absolute inset-0 bg-no-repeat bg-center bg-contain"
                 style="background-image: url('{{ $photoUrl }}'); transform: translate({{ $photoX }}%, {{ $photoY }}%) scale({{ $photoScale }});"></div>
            
            {{-- Gradient Overlay --}}
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-transparent to-transparent opacity-80 pointer-events-none"></div>
            
            {{-- NTRP Badge --}}
            <div class="absolute bottom-[18px] left-[22px] flex flex-col items-start gap-[7px]">
                <div class="relative">
                    <div class="absolute inset-0 bg-white/10 blur-xl rounded-full"></div>
                    <div class="card-border relative flex items-center gap-[9px] p-[3px] rounded-[18px] shadow-[0_8px_20px_rgba(0,0,0,0.4)] border border-white/30 backdrop-blur-xl overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-br from-white/20 to-transparent opacity-60"></div>
                        <div class="bg-slate-900/95 px-[18px] py-[9px] rounded-[16px] flex items-center gap-[9px] relative z-10">
                            <span class="font-bold text-white/40 uppercase tracking-widest leading-none text-[16px]">NTRP</span>
                            <span class="font-black text-white leading-none italic tracking-tighter text-[50px]" style="text-shadow: 0 4px 8px rgba(0,0,0,0.6);">{{ $player->level ?? '3.5' }}</span>
                        </div>
                    </div>
                </div>
                
                {{-- Level Tag --}}
                <div class="bg-white/10 backdrop-blur-xl px-[18px] py-[9px] rounded-[11px] border border-white/20 max-w-[350px] shadow-xl">
                    <p class="font-bold text-white uppercase tracking-[0.15em] italic leading-none text-[25px]" style="text-shadow: 0 2px 4px rgba(0,0,0,0.3);">{{ $levelTag }}</p>
                </div>
            </div>
        </div>
        
        {{-- Bottom Info Section --}}
        <div class="relative overflow-hidden flex flex-col justify-center" style="height: 171px; padding: 14px 27px; z-index: 80;">
            <div class="absolute inset-0 bg-white/10 backdrop-blur-2xl border-t border-white/20"></div>
            <div class="absolute inset-0 bg-gradient-to-b from-transparent to-black/60 opacity-80"></div>
            
            <div class="relative z-10">
                <div style="margin-bottom: 8px;">
                    <svg width="400" height="60" viewBox="0 0 400 60" style="display: block;">
                        <defs>
                            <linearGradient id="nameGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%" style="stop-color:{{ $currentGradient[0] }};stop-opacity:1" />
                                <stop offset="100%" style="stop-color:{{ $currentGradient[1] }};stop-opacity:1" />
                            </linearGradient>
                        </defs>
                        <text x="0" y="48" class="name-svg" font-size="50" fill="url(#nameGradient)" style="font-style: italic; font-weight: 900;">
                            {{ $player->name ?? 'TENNIS PLAYER' }}
                        </text>
                    </svg>
                </div>
                <div class="flex items-center gap-[14px] text-white/95">
                    <div class="flex items-center gap-[7px]">
                        <svg class="w-[22px] h-[22px] accent" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        <span class="font-bold uppercase tracking-wider italic text-[27px]">{{ $player->region ?? '全台' }}</span>
                    </div>
                    <div class="w-[2px] h-[18px] bg-white/30 rounded-full"></div>
                    <div class="flex items-center gap-[7px]">
                        @if(($player->gender ?? '男') === '女')
                        <svg class="w-[22px] h-[22px] accent" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="5"></circle><path d="M12 13v8"></path><path d="M9 18h6"></path></svg>
                        @else
                        <svg class="w-[22px] h-[22px] accent" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="10" cy="14" r="5"></circle><path d="M19 5l-5.4 5.4"></path><path d="M15 5h4v4"></path></svg>
                        @endif
                        <span class="font-bold uppercase tracking-wider italic text-[27px]">{{ $player->gender ?? '男' }}</span>
                    </div>
                </div>
            </div>
            </div>
        </div>
        
        {{-- Signature Layer (Must be on top of everything) --}}
        @if($signatureUrl)
        <div class="absolute inset-0 pointer-events-none" style="z-index: 100;">
            <div class="relative w-full h-full">
                <img src="{{ $signatureUrl }}" 
                     crossorigin="anonymous"
                     class="absolute origin-center"
                     style="width: {{ $sigWidth }}%; height: auto; left: {{ $sigX }}%; top: {{ $sigY }}%; transform: translate(-50%, -50%) scale({{ $sigScale }}) rotate({{ $sigRotate }}deg); filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));">
            </div>
        </div>
        @endif

        
    </div>
</div>

</body>
</html>
