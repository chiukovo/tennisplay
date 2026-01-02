<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AceMate | 專業網球約打媒合與球員卡社群</title>
    <!-- SEO Meta Tags -->
    <meta name="description" content="AceMate 是全台最專業的網球約打平台，提供職業級球員卡製作、透明約打費用與安全站內信媒合系統。">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&family=Noto+Sans+TC:wght@400;700;900&display=swap');
        
        body { 
            font-family: 'Noto Sans TC', 'Inter', sans-serif; 
            font-size: 16px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
        }
        
        [v-cloak] { display: none; }
        
        .card-shadow {
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.3);
        }

        .premium-blur {
            backdrop-filter: blur(12px);
            background: rgba(15, 23, 42, 0.75);
            will-change: opacity;
        }

        /* 自定義捲軸 */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #94a3b8; border-radius: 10px; }

        /* 動畫優化 */
        .modal-enter-active, .modal-leave-active {
            transition: opacity 0.3s ease, transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .modal-enter-from, .modal-leave-to {
            opacity: 0;
            transform: scale(0.95) translateY(10px);
        }
        
        .modal-content {
            will-change: transform, opacity;
        }
        /* 隱藏捲軸但保留滾動功能 */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 leading-normal">

<!-- Vue Component Templates -->
<script type="text/x-template" id="app-icon-template">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="className" v-html="iconPath"></svg>
</script>

<script type="text/x-template" id="signature-pad-template">
    <div v-if="active" class="absolute inset-0 z-[100] bg-black/20 backdrop-blur-[2px] cursor-crosshair overflow-hidden rounded-2xl">
        <canvas ref="canvas" @mousedown="start" @mousemove="draw" @mouseup="stop" @mouseleave="stop" @touchstart="startTouch" @touchmove="moveTouch" @touchend="stop" class="w-full h-full touch-none"></canvas>
        <div class="absolute top-4 right-4 flex gap-2">
            <button @click.stop="clear" class="p-2 bg-white/90 hover:bg-red-50 text-slate-900 rounded-full shadow-xl transition-all">
                <app-icon name="eraser" class-name="w-4 h-4"></app-icon>
            </button>
            <button @click.stop="$emit('close')" class="p-2 bg-slate-900 text-white rounded-full shadow-xl transition-all">
                <app-icon name="check-circle" class-name="w-4 h-4"></app-icon>
            </button>
        </div>
        <div class="absolute bottom-10 left-0 right-0 text-center pointer-events-none">
            <span class="bg-black/60 text-white text-[10px] font-black px-4 py-1.5 rounded-full uppercase tracking-widest italic">請在此處手寫簽名</span>
        </div>
    </div>
</script>

<script type="text/x-template" id="player-card-template">
    <div :class="['relative group cursor-pointer transition-all duration-500 hover:scale-[1.02]', size === 'sm' ? 'w-full aspect-[2.5/3.5]' : 'w-full max-w-[320px] aspect-[2.5/3.5]']">
        <div :class="['absolute -inset-1 bg-gradient-to-br rounded-[24px] blur-[2px] group-hover:blur-[6px] transition-all duration-700', themeStyle.border]"></div>
        <div :class="['relative h-full rounded-2xl overflow-hidden card-shadow flex flex-col border border-white/20', themeStyle.bg]">
            
            <signature-pad :active="isSigning" @save="sig => $emit('update-signature', sig)" @close="$emit('close-signing')" />
            
            <!-- 簽名顯示 (覆蓋全卡以達成 1:1) -->
            <img v-if="player.signature" :src="player.signature" class="absolute inset-0 w-full h-full opacity-90 filter brightness-200 pointer-events-none object-contain z-30">

            <div class="absolute top-4 left-4 right-4 z-20 flex justify-end items-center">
                <!-- Logo Watermark -->
                <div class="flex items-center gap-2 filter drop-shadow-lg transition-all duration-500">
                    <div :class="['backdrop-blur-md p-1.5 rounded-xl border transition-all duration-500', themeStyle.logoBg, themeStyle.logoBorder]">
                        <app-icon name="trophy" :class-name="['w-4 h-4 transition-all duration-500', themeStyle.logoIcon]"></app-icon>
                    </div>
                    <span :class="['font-black text-sm tracking-tighter italic uppercase transition-all duration-500', themeStyle.logoText]">AceMate</span>
                </div>
            </div>

            <div class="h-[80%] relative overflow-hidden">
                <img :src="player.photo || 'https://images.unsplash.com/photo-1614743758466-e569f4791116?q=80&w=650&auto=format&fit=crop'" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-transparent to-transparent opacity-90"></div>
                
                <!-- 自介浮層 (支援多行) -->
                <div v-if="player.intro" class="absolute top-16 left-4 right-4 bg-black/50 backdrop-blur-lg p-5 rounded-2xl border border-white/10 transform -rotate-1 shadow-2xl z-20">
                    <p class="text-[15px] text-white font-bold leading-relaxed italic whitespace-pre-line">「@{{ player.intro }}」</p>
                </div>

                <div class="absolute bottom-4 left-4 flex flex-col items-start gap-2">
                    <div :class="['flex items-center gap-2 p-0.5 rounded-xl shadow-2xl transform -rotate-2', themeStyle.border]">
                       <div class="bg-slate-900 px-3 py-1.5 rounded-[10px] flex items-center gap-2">
                          <span class="text-[10px] font-bold text-white/60 uppercase tracking-widest leading-none">NTRP</span>
                          <span class="text-2xl font-black text-white leading-none italic">@{{ player.level || '3.5' }}</span>
                       </div>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md px-3.5 py-2 rounded-lg border border-white/10 max-w-[200px]">
                        <p class="text-[11px] font-bold text-white uppercase tracking-widest italic leading-tight">@{{ getLevelDesc(player.level) }}</p>
                    </div>
                </div>
            </div>

            <div class="h-[20%] px-6 py-3 flex flex-col justify-center relative bg-gradient-to-b from-transparent to-black/30">
                <h3 :class="['text-3xl sm:text-4xl font-black uppercase tracking-tighter italic leading-[0.9] whitespace-nowrap pb-1 bg-gradient-to-r bg-clip-text text-transparent drop-shadow-2xl', themeStyle.border]">
                    @{{ player.name || 'ANONYMOUS' }}
                </h3>
                <div class="flex items-center gap-2 text-white/70">
                    <app-icon name="map-pin" class-name="w-4 h-4" :class="themeStyle.accent"></app-icon>
                    <span class="text-[13px] font-bold uppercase tracking-wider italic">@{{ player.region || '全台' }}</span>
                </div>
            </div>
        </div>
    </div>
</script>

<div id="app" v-cloak>
    <!-- Navigation -->
    <nav class="bg-white/90 backdrop-blur-xl border-b sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 h-20 flex items-center justify-between">
            <div class="flex items-center gap-3 cursor-pointer" @click="view = 'home'">
                <div class="bg-blue-600 p-2 rounded-xl shadow-lg">
                    <app-icon name="trophy" class-name="text-white w-6 h-6"></app-icon>
                </div>
                <div class="flex flex-col leading-none">
                    <span class="font-black text-2xl tracking-tighter italic uppercase text-slate-900">Ace<span class="text-blue-600">Mate</span></span>
                    <span class="text-[10px] font-bold text-slate-400 tracking-[0.2em] uppercase">愛思拍檔</span>
                </div>
            </div>
            
            <div class="hidden md:flex gap-10 text-sm font-black uppercase tracking-[0.2em] text-slate-400">
                <button @click="view = 'list'" :class="view === 'list' ? 'text-blue-600 border-b-2 border-blue-600 pb-1' : 'hover:text-slate-900 transition-colors'">發現球友</button>
                <button @click="view = 'messages'" :class="['relative', view === 'messages' ? 'text-blue-600 border-b-2 border-blue-600 pb-1' : 'hover:text-slate-900 transition-colors']">
                    約打訊息
                    <div v-if="hasUnread" class="absolute -top-1 -right-3 w-2.5 h-2.5 bg-red-500 rounded-full animate-pulse border-2 border-white"></div>
                </button>
            </div>

            <div class="flex items-center gap-4">
                <button v-if="!isLoggedIn" @click="view = 'auth'" class="text-slate-400 hover:text-slate-900 text-xs font-black uppercase tracking-widest transition-all">登入 / 註冊</button>
                <button @click="view = 'create'" class="bg-slate-950 text-white px-6 py-3 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-blue-600 transition-all flex items-center gap-2 shadow-xl">
                    <app-icon name="plus" class-name="w-5 h-5"></app-icon> 製作球員卡
                </button>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-6xl mx-auto px-4 pt-6 sm:pt-10 min-h-screen pb-48 sm:pb-32">
        
        <!-- Auth View -->
        <div v-if="view === 'auth'" class="flex items-center justify-center py-10 animate__animated animate__fadeIn">
            <div class="w-full max-w-md bg-white rounded-[40px] shadow-2xl p-10 border border-slate-100">
                <div class="text-center mb-10">
                    <div class="inline-block bg-blue-50 p-4 rounded-3xl mb-4">
                        <app-icon name="user" class-name="text-blue-600 w-10 h-10"></app-icon>
                    </div>
                    <h2 class="text-3xl font-black italic uppercase tracking-tighter leading-tight">
                        @{{ isLoginMode ? '歡迎回來' : '建立 AceMate 帳號' }}
                    </h2>
                    <p class="text-slate-500 text-base font-medium mt-2">啟動您的專業網球社交生活</p>
                </div>
                <form class="space-y-6" @submit.prevent="login">
                    <div v-if="!isLoginMode">
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">真實姓名</label>
                        <input type="text" required class="w-full px-6 py-4 bg-slate-50 border-2 border-transparent rounded-2xl focus:border-blue-500 outline-none font-bold text-lg" placeholder="例如: Roger Chen">
                    </div>
                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">電子郵件</label>
                        <input type="email" required class="w-full px-6 py-4 bg-slate-50 border-2 border-transparent rounded-2xl focus:border-blue-500 outline-none font-bold text-lg" placeholder="your@email.com">
                    </div>
                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">密碼</label>
                        <input type="password" required class="w-full px-6 py-4 bg-slate-50 border-2 border-transparent rounded-2xl focus:border-blue-500 outline-none font-bold text-lg" placeholder="••••••••">
                    </div>
                    <button type="submit" class="w-full bg-slate-950 text-white py-5 rounded-2xl font-black uppercase tracking-widest hover:bg-blue-600 transition-all shadow-xl text-lg">
                        @{{ isLoginMode ? '進入系統' : '完成註冊' }}
                    </button>
                </form>
                <div class="mt-8 text-center">
                    <button @click="isLoginMode = !isLoginMode" class="text-base font-bold text-slate-400 hover:text-blue-600">
                        @{{ isLoginMode ? '還沒有帳號？立即註冊' : '已有帳號？直接登入' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Home View -->
        <div v-if="view === 'home'" class="space-y-20">
            <!-- Hero -->
            <div class="bg-slate-950 rounded-[48px] sm:rounded-[64px] p-10 sm:p-24 text-center text-white relative overflow-hidden shadow-2xl">
                <div class="absolute inset-0 opacity-20 pointer-events-none bg-[radial-gradient(circle_at_50%_0%,rgba(59,130,246,0.3),transparent)]"></div>
                <div class="relative z-10 space-y-8">
                    <div class="inline-flex items-center gap-3 px-5 py-2.5 bg-white/5 rounded-full border border-white/10 text-white text-xs font-black uppercase tracking-[0.3em]">
                        <app-icon name="shield-check" class-name="w-5 h-5 text-blue-400"></app-icon> 全台網球媒合新標竿
                    </div>
                    <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black italic uppercase tracking-tighter leading-none">
                        找球友，<span class="text-blue-600">就上 AceMate</span>
                    </h1>
                    <p class="text-slate-400 max-w-2xl mx-auto text-lg sm:text-xl font-medium leading-relaxed">
                        全台最專業的網球約打媒合平台。製作專屬球員卡，在社群大廳獲得曝光，完全免費刊登。
                    </p>
                    <div class="flex flex-col sm:flex-row gap-5 justify-center pt-8 px-4">
                        <button @click="view = 'create'" class="bg-blue-600 text-white px-12 py-5 rounded-3xl font-black text-xl hover:scale-105 transition-all shadow-2xl shadow-blue-500/40">製作球員卡</button>
                        <button @click="view = 'list'" class="bg-white/5 text-white border border-white/10 px-12 py-5 rounded-3xl font-black text-xl hover:bg-white/10 transition-all backdrop-blur-md">瀏覽球員大廳</button>
                    </div>
                </div>
            </div>

            <!-- Featured Players (Moved Up) -->
            <section>
                <div class="flex items-center justify-between mb-12">
                    <h2 class="text-3xl font-black italic uppercase tracking-tighter flex items-center gap-4">
                        <div class="w-1.5 h-10 bg-blue-600 rounded-full"></div> 推薦戰友
                    </h2>
                    <button @click="view = 'list'" class="text-blue-600 text-sm font-black uppercase tracking-widest border-b-2 border-blue-600/10 pb-1">顯示更多</button>
                </div>
                <div class="flex overflow-x-auto no-scrollbar snap-x snap-mandatory gap-8 pb-8 -mx-4 px-4 sm:mx-0 sm:px-0 sm:grid sm:grid-cols-2 lg:grid-cols-3 sm:gap-12">
                    <player-card v-for="p in players.slice(0, 3)" :key="p.id" :player="p" @click="showDetail(p)" class="min-w-[280px] sm:min-w-0 snap-center" />
                </div>
            </section>

            <!-- Features -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                <div v-for="f in features" :key="f.title" class="bg-white p-10 rounded-[40px] shadow-sm border border-slate-100 hover:shadow-xl hover:-translate-y-2 transition-all">
                    <div class="bg-blue-50 w-16 h-16 rounded-3xl flex items-center justify-center text-blue-600 mb-8 shadow-inner">
                        <app-icon :name="f.icon" class-name="w-8 h-8"></app-icon>
                    </div>
                    <h3 class="text-xl font-black italic uppercase tracking-tighter mb-4">@{{ f.title }}</h3>
                    <p class="text-slate-500 text-base font-medium leading-relaxed">@{{ f.desc }}</p>
                </div>
            </div>

            <!-- Professional Standards -->
            <div class="bg-white rounded-[48px] p-10 sm:p-20 border border-slate-100 shadow-sm relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-blue-50/50 blur-3xl rounded-full -mr-32 -mt-32"></div>
                <div class="relative z-10 grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                    <div class="space-y-8">
                        <h2 class="text-4xl font-black italic uppercase tracking-tighter leading-tight">
                            專業網球社交<br><span class="text-blue-600">從這裡開始</span>
                        </h2>
                        <p class="text-slate-500 text-lg font-medium leading-relaxed">
                            AceMate 不僅僅是一個約球網站，我們致力於建立一個高品質、誠信且專業的網球社群。透過數位球員卡，您可以更直觀地展示實力，並找到志同道合的夥伴。
                        </p>
                        <div class="space-y-6">
                            <div class="flex items-start gap-4">
                                <div class="bg-blue-600 p-2 rounded-lg mt-1">
                                    <app-icon name="shield-check" class-name="w-5 h-5 text-white"></app-icon>
                                </div>
                                <div>
                                    <h4 class="font-black uppercase italic tracking-tight text-lg">實名與實力認證</h4>
                                    <p class="text-slate-400 font-medium">鼓勵使用者上傳真實照片與詳細 NTRP 說明，建立互信基礎。</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-4">
                                <div class="bg-slate-900 p-2 rounded-lg mt-1">
                                    <app-icon name="mail" class-name="w-5 h-5 text-white"></app-icon>
                                </div>
                                <div>
                                    <h4 class="font-black uppercase italic tracking-tight text-lg">隱私保護通訊</h4>
                                    <p class="text-slate-400 font-medium">在確認約打意向之前，您的個人聯絡資訊將受到嚴格保護。</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-6">
                        <div class="bg-slate-50 p-8 rounded-[32px] text-center space-y-2">
                            <div class="text-4xl font-black italic text-blue-600">100%</div>
                            <div class="text-xs font-black uppercase tracking-widest text-slate-400">免費刊登</div>
                        </div>
                        <div class="bg-slate-50 p-8 rounded-[32px] text-center space-y-2">
                            <div class="text-4xl font-black italic text-slate-900">24/7</div>
                            <div class="text-xs font-black uppercase tracking-widest text-slate-400">即時媒合</div>
                        </div>
                        <div class="bg-slate-50 p-8 rounded-[32px] text-center space-y-2">
                            <div class="text-4xl font-black italic text-slate-900">NTRP</div>
                            <div class="text-xs font-black uppercase tracking-widest text-slate-400">精準分級</div>
                        </div>
                        <div class="bg-slate-50 p-8 rounded-[32px] text-center space-y-2">
                            <div class="text-4xl font-black italic text-blue-600">SAFE</div>
                            <div class="text-xs font-black uppercase tracking-widest text-slate-400">安全社群</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create View -->
        <div v-if="view === 'create'" class="max-w-6xl mx-auto animate__animated animate__fadeInUp">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <!-- Mobile Preview (Sticky) -->
                <div class="lg:hidden sticky top-20 z-40 bg-slate-50/90 backdrop-blur-md py-3 -mx-4 px-4 border-b border-slate-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white shadow-lg">
                                <app-icon name="zap" class-name="w-4 h-4"></app-icon>
                            </div>
                            <div>
                                <h3 class="text-xs font-black italic uppercase tracking-tight">Live Preview</h3>
                            </div>
                        </div>
                        <button @click="scrollToSubmit" class="bg-slate-950 text-white px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg">發佈卡片</button>
                    </div>
                </div>

                <div class="lg:col-span-7 space-y-6">
                    <div class="bg-white p-6 sm:p-12 rounded-[40px] shadow-2xl border border-slate-100">
                        <div class="flex items-center justify-between mb-8">
                            <h2 class="text-3xl font-black italic uppercase tracking-tighter">球員卡編輯器</h2>
                            <span class="px-4 py-1.5 bg-blue-50 text-blue-600 text-[10px] font-black rounded-full uppercase tracking-widest italic">Professional Mode</span>
                        </div>
                        
                        <form class="space-y-8" @submit.prevent="saveCard" id="create-form">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-4">
                                    <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">1. 上傳形象照</label>
                                    <div @click="triggerUpload" class="w-full aspect-[4/5] rounded-[32px] bg-slate-50 border-2 border-dashed border-slate-200 flex flex-col items-center justify-center cursor-pointer hover:bg-blue-50 hover:border-blue-200 transition-all overflow-hidden shadow-inner group">
                                        <img v-if="form.photo" :src="form.photo" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                                        <div v-else class="text-center p-6">
                                            <div class="w-16 h-16 bg-white rounded-2xl shadow-sm flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                                                <app-icon name="upload" class-name="text-blue-600 w-8 h-8"></app-icon>
                                            </div>
                                            <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Upload Image</span>
                                        </div>
                                    </div>
                                    <input id="photo-input" type="file" class="hidden" accept="image/*" @change="handleFileUpload">
                                </div>
                                <div class="space-y-6">
                                    <div>
                                        <label class="block text-[12px] font-black uppercase tracking-[0.2em] text-slate-400 mb-3">球員姓名</label>
                                        <input type="text" v-model="form.name" required class="w-full px-6 py-4 bg-slate-50 border-2 border-transparent rounded-2xl focus:border-blue-500 outline-none font-bold text-lg shadow-inner" placeholder="例如: Roger Chen">
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-[12px] font-black uppercase tracking-[0.2em] text-slate-400 mb-3">主要地區</label>
                                            <select v-model="form.region" class="w-full px-4 py-4 bg-slate-50 rounded-2xl font-bold text-base outline-none shadow-inner">
                                                <option v-for="r in regions" :key="r" :value="r">@{{r}}</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="flex items-center justify-between text-[12px] font-black uppercase tracking-[0.2em] text-slate-400 mb-3">
                                                NTRP 程度
                                                <button type="button" @click="showNtrpGuide = true" class="text-blue-600 hover:scale-110 transition-transform">
                                                    <app-icon name="help" class-name="w-4 h-4"></app-icon>
                                                </button>
                                            </label>
                                            <select v-model="form.level" class="w-full px-4 py-4 bg-slate-50 rounded-2xl font-bold text-base outline-none shadow-inner">
                                                <option v-for="l in levels" :key="l" :value="l">@{{l}}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[12px] font-black uppercase tracking-[0.2em] text-slate-400 mb-3">個人簡介 / 約打宣告</label>
                                        <textarea v-model="form.intro" class="w-full px-6 py-4 bg-slate-50 border-2 border-transparent rounded-2xl focus:border-blue-500 outline-none font-bold text-base shadow-inner h-40" placeholder="例如: 
擅長底線抽球，希望能找球友練習。
平日晚上或週末下午皆可約！"></textarea>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-2">持拍手</label>
                                            <select v-model="form.handed" class="w-full px-4 py-4 bg-slate-50 rounded-2xl font-bold text-base outline-none shadow-inner">
                                                <option value="右手">右手</option><option value="左手">左手</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-2">反手類型</label>
                                            <select v-model="form.backhand" class="w-full px-4 py-4 bg-slate-50 rounded-2xl font-bold text-base outline-none shadow-inner">
                                                <option value="單手反拍">單手反拍</option><option value="雙手反拍">雙手反拍</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-2">約打費用</label>
                                        <select v-model="form.fee" class="w-full px-4 py-4 bg-blue-50 text-blue-900 border-2 border-blue-100 rounded-2xl font-black text-base outline-none">
                                            <option value="免費 (交流為主)">免費 (交流為主)</option>
                                            <option value="500 元 / hr (分攤場租)">500 元 / hr (分攤場租)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">3. 卡面風格與認證</label>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                                    <button v-for="(t, key) in cardThemes" :key="key" type="button" @click="form.theme = key" 
                                        :class="['p-4 rounded-2xl border-2 transition-all text-left relative overflow-hidden', form.theme === key ? 'border-blue-600 bg-blue-50' : 'border-slate-100 bg-white hover:border-slate-200']">
                                        <div class="text-[10px] font-black uppercase tracking-widest mb-1">@{{ t.label.split(' ')[0] }}</div>
                                        <div class="w-full h-1 rounded-full bg-gradient-to-r" :class="t.border"></div>
                                        <div v-if="form.theme === key" class="absolute top-2 right-2 text-blue-600">
                                            <app-icon name="check-circle" class-name="w-4 h-4"></app-icon>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <div class="bg-slate-900 p-6 rounded-[32px] text-white space-y-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <app-icon name="eraser" class-name="w-5 h-5 text-blue-400"></app-icon>
                                        <h4 class="font-black italic uppercase tracking-tight">手寫認證簽名</h4>
                                    </div>
                                    <button type="button" @click="isSigning = true" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                                        @{{ form.signature ? '重新簽名' : '點擊開始簽名' }}
                                    </button>
                                </div>
                                <p class="text-[10px] text-slate-400 font-medium italic">點擊按鈕後，請直接在右側（或下方）的卡片預覽區進行手寫簽名。</p>
                            </div>

                            <button type="submit" class="w-full bg-slate-950 text-white py-6 rounded-[28px] font-black text-xl hover:bg-blue-600 transition-all shadow-2xl flex items-center justify-center gap-4 group">
                                <app-icon name="check-circle" class-name="w-6 h-6 group-hover:scale-110 transition-transform"></app-icon> 完成並發佈卡片
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="lg:col-span-5 lg:sticky lg:top-28 flex flex-col items-center">
                    <div class="hidden lg:block mb-8 text-center">
                        <h3 class="text-[10px] font-black uppercase tracking-[0.4em] text-blue-600 mb-2">Live Preview</h3>
                        <p class="text-xs text-slate-400 font-bold italic">即時預覽您的職業球員卡</p>
                    </div>
                    <player-card :player="form" :is-signing="isSigning" @update-signature="sig => form.signature = sig" @close-signing="isSigning = false" />
                    <div v-if="isSigning" class="mt-6 lg:hidden animate__animated animate__pulse animate__infinite">
                        <span class="bg-blue-600 text-white text-[10px] font-black px-6 py-2 rounded-full uppercase tracking-widest italic shadow-xl">請直接在上方卡片簽名</span>
                    </div>
                </div>
            </div>

            <!-- NTRP Guide Modal (Full Screen Panorama) -->
            <transition name="modal">
                <div v-if="showNtrpGuide" class="fixed inset-0 z-[600] bg-slate-950 flex flex-col modal-content" @click.self="showNtrpGuide = false">
                    <!-- Header -->
                    <div class="px-8 py-8 flex items-center justify-between border-b border-white/10 shrink-0">
                        <div class="flex items-center gap-6">
                            <div class="bg-blue-600 p-4 rounded-3xl shadow-2xl shadow-blue-600/20">
                                <app-icon name="help" class-name="w-8 h-8 text-white"></app-icon>
                            </div>
                            <div>
                                <h3 class="text-3xl font-black italic uppercase tracking-[0.2em] text-white">NTRP 等級對照表</h3>
                                <p class="text-slate-500 font-bold text-sm mt-1 uppercase tracking-widest">National Tennis Rating Program Guide</p>
                            </div>
                        </div>
                        <button @click="showNtrpGuide = false" class="group flex items-center gap-3 px-6 py-3 bg-white/5 hover:bg-white/10 rounded-2xl transition-all border border-white/10">
                            <span class="text-xs font-black uppercase tracking-widest text-white/50 group-hover:text-white">關閉說明</span>
                            <app-icon name="x" class-name="w-6 h-6 text-white/50 group-hover:text-white"></app-icon>
                        </button>
                    </div>

                    <!-- Content Grid -->
                    <div class="flex-1 overflow-y-auto p-8 sm:p-12 no-scrollbar">
                        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                            <div v-for="(desc, lvl) in levelDescs" :key="lvl" class="bg-white/5 border border-white/10 p-8 rounded-[32px] hover:bg-blue-600/10 hover:border-blue-500/50 transition-all duration-500 group relative overflow-hidden">
                                <div class="absolute -top-4 -right-4 w-24 h-24 bg-blue-600/5 rounded-full blur-3xl group-hover:bg-blue-600/20 transition-all"></div>
                                <div class="flex items-center justify-between mb-6">
                                    <div class="bg-white text-slate-950 w-16 h-12 rounded-2xl flex items-center justify-center font-black italic text-2xl shadow-xl">@{{lvl}}</div>
                                    <div class="w-2 h-2 rounded-full bg-blue-500 animate-pulse opacity-0 group-hover:opacity-100"></div>
                                </div>
                                <p class="text-slate-300 font-bold text-base leading-relaxed">@{{desc}}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="p-8 border-t border-white/10 bg-slate-950/80 backdrop-blur-xl flex justify-center shrink-0">
                        <button @click="showNtrpGuide = false" class="w-full max-w-md bg-blue-600 text-white py-6 rounded-3xl font-black uppercase tracking-[0.3em] hover:bg-blue-500 transition-all shadow-[0_20px_50px_rgba(37,99,235,0.3)] text-lg">
                            了解，返回編輯
                        </button>
                    </div>
                </div>
            </transition>
        </div>

        <!-- List View -->
        <div v-if="view === 'list'" class="space-y-16 pb-24">
            <div class="flex flex-col md:flex-row justify-between items-end gap-10">
                <div>
                    <h2 class="text-5xl font-black italic uppercase tracking-tighter leading-tight">球員大廳</h2>
                    <p class="text-slate-400 font-bold text-base uppercase tracking-[0.2em] mt-2">Find your matching AceMate</p>
                </div>
                <div class="relative w-full md:w-80">
                    <app-icon name="search" class-name="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 w-6 h-6"></app-icon>
                    <input type="text" placeholder="搜尋姓名或程度..." class="w-full pl-14 pr-8 py-5 bg-white border border-slate-200 rounded-[28px] outline-none focus:ring-4 focus:ring-blue-500/10 font-bold text-lg">
                </div>
            </div>

            <div v-for="region in regions" :key="region">
                <div v-if="getPlayersByRegion(region).length > 0" class="mb-16">
                    <div class="flex items-center gap-8 mb-10">
                        <span class="bg-slate-900 text-white text-xs font-black px-6 py-2.5 rounded-2xl uppercase tracking-[0.3em]">@{{region}}</span>
                        <div class="h-px flex-1 bg-slate-200"></div>
                    </div>
                    <div class="flex overflow-x-auto no-scrollbar snap-x snap-mandatory gap-8 pb-8 -mx-4 px-4 sm:mx-0 sm:px-0 sm:grid sm:grid-cols-2 lg:grid-cols-4 sm:gap-12">
                        <div v-for="player in getPlayersByRegion(region)" :key="player.id" class="flex flex-col min-w-[240px] sm:min-w-0 snap-center">
                            <player-card :player="player" size="sm" @click="showDetail(player)" />
                            <button @click="openMatchModal(player)" class="mt-8 py-5 bg-white border-2 border-slate-950 rounded-3xl font-black text-xs uppercase tracking-[0.2em] hover:bg-slate-950 hover:text-white transition-all shadow-lg flex items-center justify-center gap-3">
                                <app-icon name="message-circle" class-name="w-5 h-5"></app-icon> 發送約打信
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Messages View -->
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
    </main>

    <!-- Player Detail Overlay -->
    <transition name="modal">
        <div v-if="detailPlayer" class="fixed inset-0 z-[200] flex items-center justify-center p-4 sm:p-10 premium-blur modal-content" @click.self="detailPlayer = null">
            <div class="bg-white w-full max-w-5xl h-full sm:h-auto max-h-[92vh] rounded-[32px] sm:rounded-[48px] overflow-hidden shadow-[0_0_100px_rgba(0,0,0,0.5)] flex flex-col md:flex-row relative">
                <button @click="detailPlayer = null" class="absolute top-6 right-6 z-50 p-2 bg-white/80 backdrop-blur-md hover:bg-red-50 hover:text-red-500 rounded-full shadow-lg transition-all">
                    <app-icon name="x" class-name="w-5 h-5"></app-icon>
                </button>

                <!-- Left: Card Display -->
                <div class="w-full md:w-1/2 p-6 sm:p-10 flex items-center justify-center bg-slate-50 border-r border-slate-100 shrink-0">
                    <div class="w-full max-w-[280px] sm:max-w-[340px] transform hover:scale-105 transition-transform duration-500">
                        <player-card :player="detailPlayer" />
                    </div>
                </div>

                <!-- Right: Detailed Stats -->
                <div class="w-full md:w-1/2 p-8 sm:p-14 overflow-y-auto bg-white flex flex-col no-scrollbar">
                    <div class="mb-8">
                        <h3 class="text-5xl font-black italic uppercase tracking-tighter text-slate-900 mb-2 leading-tight">@{{detailPlayer.name}}</h3>
                        <div class="flex items-center gap-3">
                            <span class="px-4 py-1.5 bg-blue-600 text-white text-[10px] font-black rounded-lg uppercase tracking-widest italic">Verified Player</span>
                            <span class="text-sm font-bold text-slate-400 flex items-center gap-1"><app-icon name="map-pin" class-name="w-4 h-4"></app-icon> @{{detailPlayer.region}}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6 mb-10">
                        <div v-for="s in getDetailStats(detailPlayer)" :key="s.label" class="p-6 bg-slate-50 rounded-3xl border border-slate-100 shadow-inner">
                            <div class="flex items-center gap-2 opacity-50 mb-1">
                                <app-icon :name="s.icon" class-name="w-4 h-4"></app-icon>
                                <span class="text-[10px] font-black uppercase tracking-widest">@{{s.label}}</span>
                            </div>
                            <div class="text-xl font-black text-slate-900">@{{s.value}}</div>
                        </div>
                    </div>

                    <div class="bg-slate-900 p-8 rounded-[32px] text-white relative overflow-hidden mb-10 shadow-2xl">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/20 blur-[60px] rounded-full"></div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-blue-400 mb-2 block italic underline">Scouting Report</span>
                        <p class="text-lg text-slate-300 leading-relaxed italic">
                            「擅長底線抽球，擊球頻率穩定。目前主要在@{{detailPlayer.region}}區域活動，希望能找到實力 NTRP @{{detailPlayer.level}} 左右的球友進行約打與練習。」
                        </p>
                    </div>

                    <div class="mt-auto flex flex-col sm:flex-row gap-4 pt-6 border-t border-slate-100">
                        <button @click="openMatchModal(detailPlayer)" class="flex-1 bg-blue-600 text-white py-5 rounded-2xl font-black uppercase tracking-widest text-base shadow-xl hover:bg-blue-500 transition-all flex items-center justify-center gap-3">
                            <app-icon name="message-circle" class-name="w-6 h-6"></app-icon> 立即發送約打信
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </transition>

    <!-- Match Modal -->
    <transition name="modal">
        <div v-if="matchModal.open" class="fixed inset-0 z-[300] flex items-center justify-center p-4 bg-slate-900/90 backdrop-blur-md modal-content">
            <div class="bg-white w-full max-w-md rounded-[40px] overflow-hidden shadow-2xl">
            <div class="bg-slate-900 p-8 text-white flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <img :src="matchModal.player.photo" class="w-12 h-12 rounded-full border-2 border-blue-500 object-cover shadow-lg">
                    <div>
                        <h3 class="font-black italic uppercase text-xl italic tracking-tight">約打邀約信</h3>
                        <p class="text-[9px] font-bold text-blue-400 tracking-widest uppercase">To: @{{matchModal.player.name}}</p>
                    </div>
                </div>
                <button @click="matchModal.open = false"><app-icon name="x" class-name="w-6 h-6 opacity-50"></app-icon></button>
            </div>
            <div class="p-8 space-y-6">
                <div class="bg-blue-50 p-5 rounded-2xl border border-blue-100 flex gap-4 text-xs text-blue-800 font-bold uppercase leading-normal">
                    <app-icon name="shield-check" class-name="w-6 h-6 text-blue-600 shrink-0"></app-icon>
                    安全提示：AceMate 建議在公開且有監視設備的球場會面，祝您球技進步。
                </div>
                <textarea v-model="matchModal.text" class="w-full h-40 p-5 bg-slate-50 border-2 border-transparent rounded-[28px] focus:border-blue-500 outline-none font-bold text-base leading-relaxed" 
                    :placeholder="'Hi ' + matchModal.player.name + '，看到你的 AceMate 檔案後非常想跟你交流，請問... '"></textarea>
                <button @click="sendMatchRequest" class="w-full bg-slate-950 text-white py-5 rounded-3xl font-black uppercase tracking-[0.2em] hover:bg-blue-600 shadow-2xl transition-all text-lg">
                    發送站內訊息
                </button>
            </div>
        </div>
    </div>
</transition>

    <!-- Mobile Navigation Dock -->
    <div class="fixed bottom-6 left-1/2 -translate-x-1/2 w-[94%] max-w-md bg-slate-950/95 backdrop-blur-3xl border border-white/10 rounded-[32px] p-2 flex justify-between items-center shadow-[0_20px_50px_rgba(0,0,0,0.6)] z-[150]">
        <button @click="view = 'home'" class="flex-1 flex flex-col items-center gap-1 py-3 rounded-2xl transition-all" :class="view === 'home' ? 'text-blue-400 bg-white/5' : 'text-slate-500'">
            <app-icon name="home" class-name="w-5.5 h-5.5"></app-icon>
            <span class="text-[10px] font-black uppercase tracking-widest">Home</span>
        </button>
        <button @click="view = 'list'" class="flex-1 flex flex-col items-center gap-1 py-3 rounded-2xl transition-all" :class="view === 'list' ? 'text-blue-400 bg-white/5' : 'text-slate-500'">
            <app-icon name="search" class-name="w-5.5 h-5.5"></app-icon>
            <span class="text-[10px] font-black uppercase tracking-widest">Hall</span>
        </button>
        <button @click="view = 'create'" class="relative -mt-10 group px-2">
            <div class="absolute inset-0 bg-blue-600 rounded-full blur-2xl opacity-40 group-hover:opacity-100 transition-opacity"></div>
            <div class="relative bg-blue-600 text-white w-14 h-14 rounded-2xl flex items-center justify-center border-4 border-slate-950 shadow-2xl transition-all hover:scale-110">
                <app-icon name="plus" class-name="w-8 h-8"></app-icon>
            </div>
        </button>
        <button @click="view = 'messages'" class="flex-1 flex flex-col items-center gap-1 py-3 rounded-2xl transition-all" :class="view === 'messages' ? 'text-blue-400 bg-white/5' : 'text-slate-500'">
            <div class="relative">
                <app-icon name="mail" class-name="w-5.5 h-5.5"></app-icon>
                <div v-if="hasUnread" class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-slate-950"></div>
            </div>
            <span class="text-[10px] font-black uppercase tracking-widest">Mail</span>
        </button>
        <button @click="isLoggedIn ? view = 'profile' : view = 'auth'" class="flex-1 flex flex-col items-center gap-1 py-3 text-slate-500">
            <app-icon name="user" class-name="w-5.5 h-5.5"></app-icon>
            <span class="text-[10px] font-black uppercase tracking-widest">Me</span>
        </button>
    </div>
</div>

<script>
// --- Constants ---
const REGIONS = ['台北市', '新北市', '桃園市', '台中市', '台南市', '高雄市', '新竹縣市'];
const LEVELS = ['1.0', '1.5', '2.0', '2.5', '3.0', '3.5', '4.0', '4.5', '5.0', '5.5', '6.0', '7.0'];
const INITIAL_PLAYERS = [
  { id: '1', name: 'Novak Djokovic', region: '台北市', level: '7.0', handed: '右手', fee: '免費 (交流為主)', photo: 'https://images.unsplash.com/photo-1622279457486-62dcc4a4bd13?q=80&w=400&auto=format&fit=crop', theme: 'gold' },
  { id: '2', name: 'Roger Federer', region: '台北市', level: '7.0', handed: '右手', fee: '免費 (交流為主)', photo: 'https://images.unsplash.com/photo-1595435063510-482208034433?q=80&w=400&auto=format&fit=crop', theme: 'holographic' },
  { id: '3', name: 'Rafael Nadal', region: '新北市', level: '7.0', handed: '左手', fee: '免費 (交流為主)', photo: 'https://images.unsplash.com/photo-1531315630201-bb15bbeb1663?q=80&w=400&auto=format&fit=crop', theme: 'onyx' }
];

const SVG_ICONS = {
  trophy: '<path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6M18 9h1.5a2.5 2.5 0 0 0 0-5H18M4 22h16M10 14.66V17c0 .55.45 1 1 1h2c.55 0 1-.45 1-1v-2.34M12 2v12.66" /><path d="M6 4v7a6 6 0 0 0 12 0V4H6Z" />',
  plus: '<path d="M5 12h14M12 5v14" />',
  search: '<circle cx="11" cy="11" r="8" /><path d="m21 21-4.3-4.3" />',
  mail: '<rect width="20" height="16" x="2" y="4" rx="2" /><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />',
  user: '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" /><circle cx="12" cy="7" r="4" />',
  home: '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" /><polyline points="9 22 9 12 15 12 15 22" />',
  'shield-check': '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10" /><path d="m9 12 2 2 4-4" />',
  zap: '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" />',
  upload: '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" /><polyline points="17 8 12 3 7 8" /><line x1="12" x2="12" y1="3" y2="15" />',
  'check-circle': '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" /><polyline points="22 4 12 14.01 9 11.01" />',
  'message-circle': '<path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z" />',
  x: '<path d="M18 6 6 18M6 6l12 12" />',
  eraser: '<path d="m7 21-4.3-4.3c-1-1-1-2.5 0-3.4l9.9-9.9c1-1 2.5-1 3.4 0l4.4 4.4c1 1 1 2.5 0 3.4L10.5 21z" /><path d="m15 5 4 4" />',
  'bar-chart-3': '<path d="M3 3v18h18" /><path d="M18 17V9" /><path d="M13 17V5" /><path d="M8 17v-3" />',
  'qr-code': '<rect width="5" height="5" x="3" y="3" rx="1" /><rect width="5" height="5" x="16" y="3" rx="1" /><rect width="5" height="5" x="3" y="16" rx="1" /><path d="M21 16h-3a2 2 0 0 0-2 2v3" /><path d="M21 21v.01" /><path d="M12 7v3a2 2 0 0 1-2 2H7" /><path d="M3 12h.01" /><path d="M12 3h.01" /><path d="M12 16v.01" /><path d="M16 12h1" /><path d="M21 12v.01" /><path d="M12 21v-1" />',
  target: '<circle cx="12" cy="12" r="10" /><circle cx="12" cy="12" r="6" /><circle cx="12" cy="12" r="2" />',
  'dollar-sign': '<line x1="12" x2="12" y1="2" y2="22" /><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />',
  'map-pin': '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" /><circle cx="12" cy="10" r="3" />',
  clock: '<circle cx="12" cy="12" r="10" /><polyline points="12 6 12 12 16 14" />',
  help: '<circle cx="12" cy="12" r="10" /><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" /><line x1="12" x2="12.01" y1="17" y2="17" />'
};

const LEVEL_DESCS = {
    '1.0': '初學者，剛開始接觸網球。',
    '1.5': '正在練習基本擊球，具備簡單比賽概念。',
    '2.0': '能進行簡單來回球，發球尚不穩定。',
    '2.5': '能維持慢速來回球，開始嘗試網前截擊。',
    '3.0': '擊球穩定度提高，能控制方向，有比賽策略。',
    '3.5': '具備良好的擊球控制與力量，能穩定發球。',
    '4.0': '擊球有明顯威力與深度，能應對各種球路。',
    '4.5': '具備強力的發球與底線，能進行高強度比賽。',
    '5.0': '具備職業水準技術，能應對各種戰術變化。',
    '5.5': '職業球員或資深教練。',
    '6.0': '頂尖職業球員 (ATP/WTA 排名)。',
    '7.0': '世界頂尖職業球員。'
};

const { createApp, ref, reactive, computed, onMounted, watch, nextTick } = Vue;

const AppIcon = {
  props: ['name', 'className'],
  template: '#app-icon-template',
  setup(props) {
    const iconPath = computed(() => SVG_ICONS[props.name] || '');
    return { iconPath };
  }
};

const SignaturePad = {
    props: ['active'],
    components: { AppIcon },
    template: '#signature-pad-template',
    emits: ['save', 'close'],
    setup(props, { emit }) {
        const canvas = ref(null); let ctx = null; let isDrawing = false;
        
        const initCanvas = async () => {
            await nextTick();
            if (canvas.value) {
                ctx = canvas.value.getContext('2d');
                canvas.value.width = canvas.value.offsetWidth;
                canvas.value.height = canvas.value.offsetHeight;
                ctx.strokeStyle = '#ffffff'; ctx.lineWidth = 3; ctx.lineCap = 'round';
                ctx.shadowBlur = 2; ctx.shadowColor = 'rgba(0,0,0,0.5)';
            }
        };

        watch(() => props.active, (val) => { if (val) initCanvas(); });
        onMounted(() => { if (props.active) initCanvas(); });

        const getPos = (e) => {
            const rect = canvas.value.getBoundingClientRect();
            const clientX = e.clientX || (e.touches && e.touches[0].clientX);
            const clientY = e.clientY || (e.touches && e.touches[0].clientY);
            return { x: clientX - rect.left, y: clientY - rect.top };
        };
        const start = (e) => { if (!ctx) return; isDrawing = true; ctx.beginPath(); const p = getPos(e); ctx.moveTo(p.x, p.y); };
        const draw = (e) => { if (!isDrawing || !ctx) return; const p = getPos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); };
        const stop = () => { if (isDrawing) { isDrawing = false; emit('save', canvas.value.toDataURL()); } };
        const startTouch = (e) => { e.preventDefault(); start(e); };
        const moveTouch = (e) => { e.preventDefault(); draw(e); };
        const clear = () => { if (ctx) ctx.clearRect(0, 0, canvas.value.width, canvas.value.height); emit('save', null); };
        return { canvas, start, draw, stop, startTouch, moveTouch, clear };
    }
};

const PlayerCard = {
    props: ['player', 'size', 'isSigning'],
    components: { AppIcon, SignaturePad },
    template: '#player-card-template',
    setup(props) {
        const themes = {
            gold: { 
                border: 'from-yellow-600 via-yellow-200 to-yellow-700', accent: 'text-yellow-500', bg: 'bg-slate-900',
                logoBg: 'bg-yellow-500/20', logoBorder: 'border-yellow-500/30', logoIcon: 'text-yellow-400', logoText: 'text-yellow-200/80'
            },
            platinum: { 
                border: 'from-slate-400 via-white to-slate-500', accent: 'text-blue-400', bg: 'bg-slate-900',
                logoBg: 'bg-white/20', logoBorder: 'border-white/30', logoIcon: 'text-white', logoText: 'text-white/80'
            },
            holographic: { 
                border: 'from-pink-500 via-cyan-300 via-yellow-200 to-purple-600', accent: 'text-cyan-400', bg: 'bg-slate-900',
                logoBg: 'bg-cyan-500/20', logoBorder: 'border-cyan-500/30', logoIcon: 'text-cyan-300', logoText: 'text-cyan-100/80'
            },
            onyx: { 
                border: 'from-slate-900 via-slate-600 to-black', accent: 'text-slate-400', bg: 'bg-black',
                logoBg: 'bg-white/10', logoBorder: 'border-white/10', logoIcon: 'text-slate-400', logoText: 'text-slate-500'
            },
            sakura: { 
                border: 'from-pink-400 via-pink-100 to-pink-500', accent: 'text-pink-400', bg: 'bg-slate-900',
                logoBg: 'bg-pink-500/20', logoBorder: 'border-pink-500/30', logoIcon: 'text-pink-300', logoText: 'text-pink-100/80'
            },
            standard: { 
                border: 'from-blue-600 via-indigo-400 to-blue-800', accent: 'text-blue-500', bg: 'bg-slate-900',
                logoBg: 'bg-blue-500/20', logoBorder: 'border-blue-500/30', logoIcon: 'text-blue-400', logoText: 'text-blue-200/80'
            }
        };
        const themeStyle = computed(() => themes[props.player.theme || 'standard']);
        const getLevelDesc = (lvl) => LEVEL_DESCS[lvl] || '網球愛好者';
        return { themeStyle, getLevelDesc };
    }
};

createApp({
    components: { SignaturePad, PlayerCard, AppIcon },
    setup() {
        const view = ref('home');
        const isLoggedIn = ref(false);
        const isLoginMode = ref(true);
        const hasUnread = ref(true);
        const regions = REGIONS; const levels = LEVELS;
        const players = ref(INITIAL_PLAYERS);
        const messages = ref([
            { id: 1, from: 'Roger Chen', content: '哈囉！看到你在台北市出沒，這週末下午要約打球嗎？', date: '2023-10-24', unread: true },
            { id: 2, from: '系統', content: '歡迎來到 AceMate！開始建立您的球員檔案。', date: '2023-10-23', unread: false }
        ]);
        const features = [
            { icon: 'zap', title: '快速約球陪打', desc: '精準媒合 NTRP 等級，輕鬆找到實力相當的球友或專業陪打夥伴。' },
            { icon: 'shield-check', title: '製作專屬球員卡', desc: '建立專業視覺風格的數位球員卡，在社群大廳展現您的網球實力與風格。' },
            { icon: 'dollar-sign', title: '刊登完全免費', desc: '建立檔案、刊登曝光、發送約打訊息完全不收費，讓網球社交更簡單。' }
        ];
        const form = reactive({
            name: '', region: '台北市', level: '3.5', handed: '右手', backhand: '雙手反拍',
            intro: '', fee: '免費 (交流為主)', photo: null, signature: null, theme: 'standard'
        });
        const isSigning = ref(false);
        const showNtrpGuide = ref(false);
        const levelDescs = LEVEL_DESCS;
        const cardThemes = {
            gold: { border: 'from-yellow-600 via-yellow-200 to-yellow-700', accent: 'text-yellow-500', bg: 'bg-slate-900', label: 'GOLD EDITION' },
            platinum: { border: 'from-slate-400 via-white to-slate-500', accent: 'text-blue-400', bg: 'bg-slate-900', label: 'PLATINUM RARE' },
            holographic: { border: 'from-pink-500 via-cyan-300 via-yellow-200 to-purple-600', accent: 'text-cyan-400', bg: 'bg-slate-900', label: 'HOLO FOIL' },
            onyx: { border: 'from-slate-900 via-slate-600 to-black', accent: 'text-slate-400', bg: 'bg-black', label: 'ONYX BLACK' },
            sakura: { border: 'from-pink-400 via-pink-100 to-pink-500', accent: 'text-pink-400', bg: 'bg-slate-900', label: 'SAKURA BLOOM' },
            standard: { border: 'from-blue-600 via-indigo-400 to-blue-800', accent: 'text-blue-500', bg: 'bg-slate-900', label: 'PRO CARD' }
        };

        const scrollToSubmit = () => {
            document.getElementById('create-form').scrollIntoView({ behavior: 'smooth', block: 'end' });
        };
        const matchModal = reactive({ open: false, player: null, text: '' });
        const detailPlayer = ref(null);

        const login = () => { isLoggedIn.value = true; view.value = 'home'; };
        const triggerUpload = () => document.getElementById('photo-input').click();
        const handleFileUpload = (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (u) => form.photo = u.target.result;
                reader.readAsDataURL(file);
            }
        };
        const saveCard = () => { 
            if (!isLoggedIn.value) {
                alert('卡片製作成功！請先登入或註冊以正式發佈您的球員卡。');
                view.value = 'auth';
                return;
            }
            players.value.unshift({ ...form, id: Date.now() }); 
            view.value = 'list'; 
        };
        const getPlayersByRegion = (r) => players.value.filter(p => p.region === r);
        const openMatchModal = (p) => { matchModal.player = p; matchModal.open = true; };
        const sendMatchRequest = () => {
            messages.value.unshift({ id: Date.now(), from: '系統', content: `已發送邀約給 ${matchModal.player.name}`, date: '剛剛', unread: true });
            matchModal.open = false;
        };
        const showDetail = (p) => { detailPlayer.value = p; };
        const getDetailStats = (p) => [
            { label: '程度 (NTRP)', value: p.level || '3.5', icon: 'zap' },
            { label: '慣用手', value: p.handed || '右手', icon: 'target' },
            { label: '約打費用', value: p.fee || '免費', icon: 'dollar-sign' },
            { label: '主要地區', value: p.region || '全台', icon: 'map-pin' }
        ];

        return { 
            view, isLoggedIn, isLoginMode, hasUnread, regions, levels, players, messages, features, form, 
            matchModal, detailPlayer, isSigning, showNtrpGuide, levelDescs, cardThemes, login, triggerUpload, handleFileUpload, saveCard, getPlayersByRegion, 
            openMatchModal, sendMatchRequest, showDetail, getDetailStats, scrollToSubmit 
        };
    }
}).mount('#app');
</script>
</body>
</html>