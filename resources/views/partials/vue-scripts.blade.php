<script>
// --- Constants ---
const REGIONS = [
    '台北市', '新北市', '基隆市', '桃園市', '新竹市', '新竹縣', '苗栗縣', 
    '台中市', '彰化縣', '南投縣', '雲林縣', '嘉義市', '嘉義縣', '台南市', 
    '高雄市', '屏東縣', '宜蘭縣', '花蓮縣', '台東縣', '澎湖縣', '金門縣', '連江縣'
];
const LEVELS = ['1.0', '1.5', '2.0', '2.5', '3.0', '3.5', '4.0', '4.5', '5.0', '5.5', '6.0', '7.0'];
const INITIAL_PLAYERS = [
  { id: '1', name: 'Novak Djokovic', region: '台北市', level: '7.0', handed: '右手', gender: '男', fee: '免費 (交流為主)', photo: 'https://images.unsplash.com/photo-1622279457486-62dcc4a4bd13?q=80&w=400&auto=format&fit=crop', theme: 'gold' },
  { id: '2', name: 'Roger Federer', region: '台北市', level: '7.0', handed: '右手', gender: '男', fee: '免費 (交流為主)', photo: 'https://images.unsplash.com/photo-1595435063510-482208034433?q=80&w=400&auto=format&fit=crop', theme: 'holographic' },
  { id: '3', name: 'Rafael Nadal', region: '新北市', level: '7.0', handed: '左手', gender: '男', fee: '免費 (交流為主)', photo: 'https://images.unsplash.com/photo-1531315630201-bb15bbeb1663?q=80&w=400&auto=format&fit=crop', theme: 'onyx' }
];

const SVG_ICONS = {
  gender: '<circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 0 0-16 0"/>',
  mars: '<circle cx="10" cy="14" r="5"/><path d="m19 5-5.4 5.4"/><path d="M15 5h4v4"/>',
  venus: '<circle cx="12" cy="9" r="5"/><path d="M12 14v7"/><path d="M9 18h6"/>',
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
  help: '<circle cx="12" cy="12" r="10" /><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" /><line x1="12" x2="12.01" y1="17" y2="17" />',
  trash: '<path d="M3 6h18m-2 0v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6m3 0V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" /><line x1="10" x2="10" y1="11" y2="17" /><line x1="14" x2="14" y1="11" y2="17" />',
  'edit-3': '<path d="M12 20h9" /><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z" />'
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
    '5.5': '職業球友或資深教練。',
    '6.0': '頂尖職業球友 (ATP/WTA 排名)。',
    '7.0': '世界頂尖職業球友。'
};

const { createApp, ref, reactive, computed, onMounted, watch, nextTick } = Vue;

const LEVEL_TAGS = {
    '1.0': '網球初學者', '1.5': '基礎擊球員', '2.0': '入門球友', '2.5': '進階入門', 
    '3.0': '中級程度', '3.5': '中高級員', '4.0': '高級球員', '4.5': '高強度球友', 
    '5.0': '專家級員', '5.5': '資深教練', '6.0': '職業水準', '7.0': '世界級球星'
};

// --- Vue Components ---
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
        const stop = () => { isDrawing = false; };
        const startTouch = (e) => { e.preventDefault(); start(e); };
        const moveTouch = (e) => { e.preventDefault(); draw(e); };
        const clear = () => { if (ctx) ctx.clearRect(0, 0, canvas.value.width, canvas.value.height); };
        const confirm = () => { if (canvas.value) { emit('save', canvas.value.toDataURL()); emit('close'); } };
        return { canvas, start, draw, stop, startTouch, moveTouch, clear, confirm };
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
        const getLevelTag = (lvl) => LEVEL_TAGS[lvl] || '網球愛好者';
        return { themeStyle, getLevelTag };
    }
};

const PlayerDetailModal = {
    props: ['player', 'stats'],
    components: { AppIcon, PlayerCard },
    template: '#player-detail-modal-template',
    emits: ['close', 'open-match']
};

const MatchModal = {
    props: ['open', 'player'],
    components: { AppIcon },
    template: '#match-modal-template',
    emits: ['update:open', 'submit'],
    setup(props) {
        const textModel = ref('');
        return { textModel };
    }
};

const NtrpGuideModal = {
    props: ['open', 'descs'],
    components: { AppIcon },
    template: '#ntrp-guide-modal-template',
    emits: ['update:open']
};

// --- Main App ---
createApp({
    components: { SignaturePad, PlayerCard, AppIcon, PlayerDetailModal, MatchModal, NtrpGuideModal },
    setup() {
        const view = ref('home');
        const isLoggedIn = ref(false);
        const isLoginMode = ref(true);
        const hasUnread = ref(true);
        const regions = REGIONS; const levels = LEVELS;
        const players = ref(INITIAL_PLAYERS);
        const messages = ref([
            { id: 1, from: 'Roger Chen', content: '哈囉！看到你在台北市出沒，這週末下午要約打球嗎？', date: '2023-10-24', unread: true },
            { id: 2, from: '系統', content: '歡迎來到 AceMate！開始建立您的球友檔案。', date: '2023-10-23', unread: false }
        ]);
        const features = [
            { icon: 'zap', title: '快速約球陪打', desc: '精準媒合 NTRP 等級，輕鬆找到實力相當的球友或專業陪打夥伴。' },
            { icon: 'shield-check', title: '製作專屬球友卡', desc: '建立專業視覺風格的數位球友卡，在社群大廳展現您的網球實力與風格。' },
            { icon: 'dollar-sign', title: '刊登完全免費', desc: '建立檔案、刊登曝光、發送約打訊息完全不收費，讓網球社交更簡單。' }
        ];
        const form = reactive({
            name: '', region: '台北市', level: '3.5', handed: '右手', backhand: '雙反', gender: '男',
            intro: '', fee: '免費 (交流為主)', photo: null, signature: null, theme: 'standard',
            photoX: 0, photoY: 0, photoScale: 1, 
            sigX: 0, sigY: 0, sigScale: 1, sigRotate: 0
        });
        const currentStep = ref(1);
        const showPreview = ref(false);
        const isAdjustingPhoto = ref(false);
        
        // Dragging State
        const dragInfo = reactive({
            active: false,
            target: null,
            startX: 0,
            startY: 0,
            initialX: 0,
            initialY: 0
        });

        const startDrag = (e, target) => {
            dragInfo.active = true;
            dragInfo.target = target;
            const clientX = e.clientX || (e.touches && e.touches[0].clientX);
            const clientY = e.clientY || (e.touches && e.touches[0].clientY);
            dragInfo.startX = clientX;
            dragInfo.startY = clientY;
            
            if (target === 'photo') {
                dragInfo.initialX = form.photoX;
                dragInfo.initialY = form.photoY;
            } else if (target === 'sig') {
                dragInfo.initialX = form.sigX;
                dragInfo.initialY = form.sigY;
            }

            window.addEventListener('mousemove', handleDrag);
            window.addEventListener('mouseup', stopDrag);
            window.addEventListener('touchmove', handleDrag, { passive: false });
            window.addEventListener('touchend', stopDrag);
        };

        const handleDrag = (e) => {
            if (!dragInfo.active) return;
            const clientX = e.clientX || (e.touches && e.touches[0].clientX);
            const clientY = e.clientY || (e.touches && e.touches[0].clientY);
            const dx = clientX - dragInfo.startX;
            const dy = clientY - dragInfo.startY;

            if (dragInfo.target === 'photo') {
                form.photoX = dragInfo.initialX + dx;
                form.photoY = dragInfo.initialY + dy;
            } else if (dragInfo.target === 'sig') {
                form.sigX = dragInfo.initialX + dx;
                form.sigY = dragInfo.initialY + dy;
            }
        };

        const stopDrag = () => {
            dragInfo.active = false;
            window.removeEventListener('mousemove', handleDrag);
            window.removeEventListener('mouseup', stopDrag);
            window.removeEventListener('touchmove', handleDrag);
            window.removeEventListener('touchend', stopDrag);
        };

        // Moveable Integration
        let moveableInstance = null;
        const initMoveable = (target) => {
            if (moveableInstance) moveableInstance.destroy();
            if (!target) return;

            moveableInstance = new Moveable(target.parentElement.parentElement, {
                target: target,
                draggable: true,
                resizable: false,
                scalable: true,
                rotatable: true,
                warpable: false,
                pinchable: true,
                origin: false,
                keepRatio: true,
                throttleDrag: 0,
                throttleScale: 0,
                throttleRotate: 0,
            }).on("drag", ({ target, transform, left, top, dist, delta, clientX, clientY }) => {
                const translate = delta;
                form.sigX += translate[0];
                form.sigY += translate[1];
            }).on("scale", ({ target, scale, dist, delta, transform }) => {
                form.sigScale *= delta[0];
            }).on("rotate", ({ target, beforeRotate, rotate, dist, delta, transform }) => {
                form.sigRotate += delta;
            });
        };

        watch(() => form.signature, (val) => {
            if (!val && moveableInstance) {
                moveableInstance.destroy();
                moveableInstance = null;
            }
        });

        watch(currentStep, (val) => {
            if (val !== 4 && moveableInstance) {
                moveableInstance.destroy();
                moveableInstance = null;
            }
        });
        const stepTitles = [
            '上傳您的專業形象照並填寫姓名',
            '設定您的 NTRP 分級與擊球技術',
            '選擇活動地區並撰寫約打宣告',
            '切換視覺主題並完成手寫簽名'
        ];
        const genders = ['男', '女'];
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
        const triggerUpload = () => document.getElementById('photo-upload').click();
        const handleFileUpload = (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (u) => {
                    form.photo = u.target.result;
                    form.photoX = 0;
                    form.photoY = 0;
                    form.photoScale = 1;
                    isAdjustingPhoto.value = true;
                };
                reader.readAsDataURL(file);
            }
        };
        const saveCard = () => { 
            // Skip login for preview - directly add to list
            players.value.unshift({ ...form, id: Date.now() }); 
            // Reset form for next card
            Object.assign(form, {
                name: '', region: '台北市', level: '3.5', handed: '右手', backhand: '雙反', gender: '男',
                intro: '', fee: '免費 (交流為主)', photo: null, signature: null, theme: 'standard',
                photoX: 0, photoY: 0, photoScale: 1, 
                sigX: 0, sigY: 0, sigScale: 1, sigRotate: 0
            });
            currentStep.value = 1;
            view.value = 'list'; 
        };
        const getPlayersByRegion = (r) => players.value.filter(p => p.region === r);
        const openMatchModal = (p) => { matchModal.player = p; matchModal.open = true; };
        const sendMatchRequest = () => {
            messages.value.unshift({ id: Date.now(), from: '系統', content: `已發送邀約給 ${matchModal.player.name}`, date: '剛剛', unread: true });
            matchModal.open = false;
        };
        const showDetail = (p) => { detailPlayer.value = p; };
        const getDetailStats = (p) => {
            if (!p) return [];
            return [
                { label: '程度 (NTRP)', value: p.level || '3.5', icon: 'zap' },
                { label: '性別', value: p.gender || '男', icon: 'gender' },
                { label: '慣用手', value: p.handed || '右手', icon: 'target' },
                { label: '主要地區', value: p.region || '全台', icon: 'map-pin' }
            ];
        };

        return { 
            view, isLoggedIn, isLoginMode, hasUnread, regions, levels, players, messages, features, form, 
            matchModal, detailPlayer, isSigning, showNtrpGuide, levelDescs, cardThemes, currentStep, showPreview, stepTitles, genders,
            isAdjustingPhoto, dragInfo, startDrag, handleDrag, stopDrag, initMoveable,
            login, triggerUpload, handleFileUpload, saveCard, getPlayersByRegion, 
            openMatchModal, sendMatchRequest, showDetail, getDetailStats, scrollToSubmit 
        };
    }
}).mount('#app');
</script>
