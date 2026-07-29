<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=540">
    <title>Card Render</title>
    <style>
        * { box-sizing: border-box; }
        html, body { width: 540px; height: 470px; margin: 0; overflow: hidden; background: transparent; font-family: Arial, "Noto Sans TC", sans-serif; }
        .card { position: relative; width: 540px; height: 470px; overflow: hidden; border: 1px solid rgba(167, 139, 250, .6); border-radius: 28px; background: #020617; color: white; }
        .base { position: absolute; inset: 0; background: linear-gradient(135deg, #0f172a, #020617 65%, #000); }
        .glow { position: absolute; top: -48px; right: -48px; width: 208px; height: 208px; border-radius: 50%; background: rgba(139, 92, 246, .3); filter: blur(48px); }
        .photo { position: absolute; inset: 0; overflow: hidden; }
        .photo img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; object-position: 70% top; }
        .shade { position: absolute; inset: 0; background: linear-gradient(90deg, rgba(2, 6, 23, .92) 0%, rgba(2, 6, 23, .78) 25%, rgba(2, 6, 23, .45) 50%, rgba(2, 6, 23, .12) 72%, transparent 100%); }
        .shade-bottom { position: absolute; inset: 0; background: linear-gradient(0deg, rgba(2, 6, 23, .8), transparent 55%); }
        .content { position: relative; z-index: 10; display: flex; flex-direction: column; width: 78%; height: 100%; padding: 36px; }
        h1 { max-width: 320px; margin: 0; overflow: hidden; font-size: 36px; line-height: 1; font-weight: 900; letter-spacing: -.03em; text-overflow: ellipsis; white-space: nowrap; }
        .region { display: flex; align-items: center; gap: 8px; margin-top: 8px; color: #cbd5e1; font-size: 18px; font-weight: 700; }
        .stats { margin-top: 20px; }
        .ntrp { display: flex; align-items: baseline; gap: 16px; }
        .ntrp-label { color: #ddd6fe; font-size: 14px; font-weight: 900; letter-spacing: .12em; }
        .level { color: #fde68a; font-size: 44px; line-height: 1; font-weight: 900; }
        .style { display: flex; gap: 12px; margin-top: 8px; font-size: 18px; font-weight: 700; }
        .muted { color: #94a3b8; }
        .handed { color: #fda4af; }
        .backhand { color: #67e8f9; }
        .tags { margin-top: 20px; }
        .label { margin-bottom: 8px; color: #cbd5e1; font-size: 14px; font-weight: 900; letter-spacing: .12em; }
        .tag-list { display: flex; flex-wrap: wrap; gap: 8px; }
        .tag { max-width: 180px; padding: 6px 16px; overflow: hidden; border-radius: 999px; background: rgba(255, 255, 255, .1); font-size: 14px; font-weight: 700; text-overflow: ellipsis; white-space: nowrap; }
        .intro { margin-top: auto; padding-right: 16px; }
        .intro p { display: -webkit-box; margin: 4px 0 0; overflow: hidden; color: rgba(255, 255, 255, .9); font-size: 18px; font-weight: 500; line-height: 1.5; -webkit-box-orient: vertical; -webkit-line-clamp: 2; }
        .signature { position: absolute; z-index: 100; height: auto; transform-origin: center; filter: drop-shadow(0 2px 4px rgba(0, 0, 0, .3)); }
    </style>
</head>
<body>
    @php
        $photoUrl = $player->photo_url ?: 'https://images.unsplash.com/photo-1614743758466-e569f4791116?q=80&w=650&auto=format&fit=crop';
        $signatureUrl = $player->signature_url;
    @endphp
    <div class="card">
        <div class="base"></div>
        <div class="glow"></div>
        <div class="photo">
            <img src="{{ $photoUrl }}" crossorigin="anonymous" alt=""
                style="transform: translate({{ $player->photo_x ?? 0 }}%, {{ $player->photo_y ?? 0 }}%) scale({{ $player->photo_scale ?? 1 }});">
        </div>
        <div class="shade"></div>
        <div class="shade-bottom"></div>

        <div class="content">
            <div>
                <h1>{{ $player->name }}</h1>
                <div class="region">⌖ {{ $player->region ?: '全台' }}</div>
            </div>

            <div class="stats">
                <div class="ntrp">
                    <span class="ntrp-label">NTRP</span>
                    <span class="level">{{ $player->level ?: '-' }}</span>
                </div>
                <div class="style">
                    <span class="muted">打法</span>
                    <span class="handed">{{ $player->handed ?: '未填寫' }}</span>
                    <span class="muted">/</span>
                    <span class="backhand">{{ $player->backhand ?: '未填寫' }}</span>
                </div>
            </div>

            <div class="tags">
                <div class="label">球友標籤</div>
                <div class="tag-list">
                    @foreach(array_filter([$player->gender, $player->handed ? $player->handed.'持拍' : null, $player->backhand, $player->fee]) as $tag)
                        <span class="tag">{{ $tag }}</span>
                    @endforeach
                </div>
            </div>

            <div class="intro">
                <div class="label">一句話訊息</div>
                <p>{{ $player->intro ?: '一起來打球！' }}</p>
            </div>
        </div>

        @if($signatureUrl)
            <img class="signature" src="{{ $signatureUrl }}" crossorigin="anonymous"
                style="width: {{ $player->sig_width ?? 100 }}%; left: {{ $player->sig_x ?? 50 }}%; top: {{ $player->sig_y ?? 50 }}%; transform: translate(-50%, -50%) scale({{ $player->sig_scale ?? 1 }}) rotate({{ $player->sig_rotate ?? 0 }}deg);">
        @endif
    </div>
</body>
</html>
