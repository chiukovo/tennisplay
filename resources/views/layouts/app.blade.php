<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LoveTennis | 專業網球約打媒合與球友卡社群</title>
    <!-- SEO Meta Tags -->
    <meta name="description" content="LoveTennis 是全台最專業的網球約打平台，提供職業級球友卡製作、透明約打費用與安全站內信媒合系統。">
    <style>[v-cloak] { display: none !important; }</style>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    {{-- External Scripts (Loaded in head for reliability) --}}
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://unpkg.com/moveable/dist/moveable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    
    @include('partials.styles')
</head>
<body class="bg-slate-50 text-slate-900 leading-normal">

@include('components.vue-templates')

<div id="app" v-cloak>
    @include('partials.navigation')

    <!-- Main Content -->
    <main class="max-w-6xl mx-auto px-4 py-10">
        @yield('content')
    </main>
</div>

@include('partials.vue-scripts')

</body>
</html>
