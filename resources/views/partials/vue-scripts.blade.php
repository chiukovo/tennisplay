<script>
// --- API Configuration ---
const API_BASE = '/api'; // Keep as /api, but we'll handle relative base if needed
// Detect base path for API
const getApiBase = () => {
    const path = window.location.pathname;
    if (path.includes('/public/')) {
        return path.split('/public/')[0] + '/public/api';
    }
    // Fallback or root deployment
    return '/api';
};

const api = axios.create({
    baseURL: getApiBase(),
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    }
});

// Add auth token to requests if available
api.interceptors.request.use(config => {
    const token = localStorage.getItem('auth_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// Handle auth errors
api.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('auth_user');
        }
        return Promise.reject(error);
    }
);

// --- Constants ---
const REGIONS = [
    '台北市', '新北市', '基隆市', '桃園市', '新竹市', '新竹縣', '苗栗縣', 
    '台中市', '彰化縣', '南投縣', '雲林縣', '嘉義市', '嘉義縣', '台南市', 
    '高雄市', '屏東縣', '宜蘭縣', '花蓮縣', '台東縣', '澎湖縣', '金門縣', '連江縣'
];
const LEVELS = ['1.0', '1.5', '2.0', '2.5', '3.0', '3.5', '4.0', '4.5', '5.0', '5.5', '6.0', '7.0'];

// No initial players - will be loaded from API
const INITIAL_PLAYERS = [];

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
  'edit-3': '<path d="M12 20h9" /><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z" />',
  'move': '<path d="m5 9-3 3 3 3M9 5l3-3 3 3M15 19l-3 3-3-3M19 9l3 3-3 3M2 12h20M12 2v20" />',
  'check': '<polyline points="20 6 9 17 4 12" />',
  'users': '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
  'filter': '<polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>',
  'chevron-left': '<polyline points="15 18 9 12 15 6"/>',
  'chevron-right': '<polyline points="9 18 15 12 9 6"/>',
  'chevron-down': '<polyline points="6 9 12 15 18 9"/>',
  'rotate-3d': '<path d="M3.5 13h6V7"/><path d="M20.5 13h-6v6"/><path d="M6.5 13c0-4.42 3.58-8 8-8s8 3.58 8 8-3.58 8-8 8c-1.22 0-2.37-.27-3.4-.75"/>',
  'star': '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
  'line': '<path d="M24 10.304c0-5.369-5.383-9.738-12-9.738-6.616 0-12 4.369-12 9.738 0 4.814 4.269 8.846 10.036 9.608.391.084.922.258 1.057.592.121.303.079.778.039 1.085l-.171 1.027c-.052.303-.242 1.186 1.039.647 1.281-.54 6.911-4.069 9.428-6.967 1.739-1.907 2.571-3.943 2.571-5.992zm-17.888 4.044c-.166 0-.303-.135-.303-.302v-3.722h-1.12c-.166 0-.303-.135-.303-.302v-.476c0-.166.137-.302.303-.302h2.9c.166 0 .303.135.303.302v4.5c0 .166-.137.302-.303.302h-1.48zm2.852 0c-.166 0-.303-.135-.303-.302v-4.5c0-.166.137-.302.303-.302h.507c.166 0 .303.135.303.302v4.5c0 .166-.137.302-.303.302h-.507zm6.39 0c-.166 0-.302-.135-.302-.302v-2.1l-1.977-2.4c-.042-.051-.061-.097-.061-.153v-1.847c0-.166.137-.302.303-.302h.506c.166 0 .303.135.303.302v1.388l1.862 2.259v-3.647c0-.166.137-.302.303-.302h.507c.166 0 .303.135.303.302v4.5c0 .166-.137.302-.303.302h-.507l-.938-1.14-1.002 1.216c-.042.051-.061.097-.061.153v.073zm3.693-1.353h-1.12v-1.01h1.12c.166 0 .303-.135.303-.302v-.475c0-.166-.137-.302-.303-.302h-1.12v-.91h1.12c.166 0 .303-.135.303-.302v-.476c0-.166-.137-.302-.303-.302h-2.9c-.166 0-.303.135-.303.302v4.5c0 .166.137.302.303.302h2.9c.166 0 .303-.135.303-.302v-.476c.001-.166-.136-.302-.303-.302z"/>'
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

const { createApp, ref, reactive, computed, onMounted, onUnmounted, watch, nextTick } = Vue;

const LEVEL_TAGS = {
    '1.0': '網球初學者', '1.5': '基礎擊球員', '2.0': '入門球友', '2.5': '進階入門', 
    '3.0': '中級程度', '3.5': '中高級員', '4.0': '高級球員', '4.5': '高強度球友', 
    '5.0': '專家級員', '5.5': '資深教練', '6.0': '職業水準', '7.0': '世界級球星'
};

// --- Vue Components ---
const AppIcon = {
  props: ['name', 'className', 'fill', 'stroke', 'strokeWidth'],
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
                const ratio = window.devicePixelRatio || 1;
                const width = canvas.value.offsetWidth;
                const height = canvas.value.offsetHeight;
                
                canvas.value.width = width * ratio;
                canvas.value.height = height * ratio;
                canvas.value.style.width = `${width}px`;
                canvas.value.style.height = `${height}px`;
                
                ctx.scale(ratio, ratio);
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
            // rect.left/top are in CSS pixels, which matches getPos's return expectations
            return { x: clientX - rect.left, y: clientY - rect.top };
        };
        const start = (e) => { if (!ctx) return; isDrawing = true; ctx.beginPath(); const p = getPos(e); ctx.moveTo(p.x, p.y); };
        const draw = (e) => { if (!isDrawing || !ctx) return; const p = getPos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); };
        const stop = () => { isDrawing = false; };
        const startTouch = (e) => { e.preventDefault(); start(e); };
        const moveTouch = (e) => { e.preventDefault(); draw(e); };
        const clear = () => { if (ctx) ctx.clearRect(0, 0, canvas.value.width, canvas.value.height); };
        
        const getTrimmedCanvas = (sourceCanvas) => {
            const tempCtx = sourceCanvas.getContext('2d');
            const width = sourceCanvas.width;
            const height = sourceCanvas.height;
            const imageData = tempCtx.getImageData(0, 0, width, height);
            const data = imageData.data;
            
            let minX = width, minY = height, maxX = 0, maxY = 0;
            let found = false;

            for (let y = 0; y < height; y++) {
                for (let x = 0; x < width; x++) {
                    const alpha = data[(y * width + x) * 4 + 3];
                    if (alpha > 0) {
                        if (x < minX) minX = x;
                        if (y < minY) minY = y;
                        if (x > maxX) maxX = x;
                        if (y > maxY) maxY = y;
                        found = true;
                    }
                }
            }

            if (!found) return null;

            // Add padding
            const padding = 10;
            minX = Math.max(0, minX - padding);
            minY = Math.max(0, minY - padding);
            maxX = Math.min(width, maxX + padding);
            maxY = Math.min(height, maxY + padding);

            const trimmedWidth = maxX - minX;
            const trimmedHeight = maxY - minY;

            const trimmedCanvas = document.createElement('canvas');
            trimmedCanvas.width = trimmedWidth;
            trimmedCanvas.height = trimmedHeight;
            const trimmedCtx = trimmedCanvas.getContext('2d');
            trimmedCtx.drawImage(sourceCanvas, minX, minY, trimmedWidth, trimmedHeight, 0, 0, trimmedWidth, trimmedHeight);
            
            return {
                dataUrl: trimmedCanvas.toDataURL('image/png'),
                widthPct: (trimmedWidth / width) * 100,
                heightPct: (trimmedHeight / height) * 100,
                xPct: ((minX + trimmedWidth / 2) / width) * 100,
                yPct: ((minY + trimmedHeight / 2) / height) * 100
            };
        };

        const confirm = () => { 
            if (canvas.value) { 
                const trimmed = getTrimmedCanvas(canvas.value);
                if (trimmed) {
                    emit('save', trimmed); 
                } else {
                    // If empty, just close or show warning
                    emit('close');
                }
            } 
        };
        return { canvas, start, draw, stop, startTouch, moveTouch, clear, confirm };
    }
};

const PlayerCard = {
    props: ['player', 'size', 'isSigning', 'isAdjustingSig'],
    components: { AppIcon, SignaturePad },
    template: '#player-card-template',
    setup(props) {
        const cardContainer = ref(null);
        const themes = {
            gold: { 
                border: 'from-yellow-600 via-yellow-200 to-yellow-700', accent: 'text-yellow-500', bg: 'bg-slate-900',
                logoBg: 'bg-yellow-500/10', logoBorder: 'border-yellow-500/10', logoIcon: 'text-yellow-400/40', logoText: 'text-yellow-200/30'
            },
            platinum: { 
                border: 'from-slate-400 via-white to-slate-500', accent: 'text-blue-400', bg: 'bg-slate-900',
                logoBg: 'bg-white/10', logoBorder: 'border-white/10', logoIcon: 'text-white/40', logoText: 'text-white/30'
            },
            holographic: { 
                border: 'from-pink-500 via-cyan-300 via-yellow-200 to-purple-600', accent: 'text-cyan-400', bg: 'bg-slate-900',
                logoBg: 'bg-cyan-500/10', logoBorder: 'border-cyan-500/10', logoIcon: 'text-cyan-300/40', logoText: 'text-cyan-100/30'
            },
            onyx: { 
                border: 'from-slate-900 via-slate-600 to-black', accent: 'text-slate-400', bg: 'bg-black',
                logoBg: 'bg-white/5', logoBorder: 'border-white/5', logoIcon: 'text-slate-400/30', logoText: 'text-slate-500/20'
            },
            sakura: { 
                border: 'from-pink-400 via-pink-100 to-pink-500', accent: 'text-pink-400', bg: 'bg-slate-900',
                logoBg: 'bg-pink-500/10', logoBorder: 'border-pink-500/10', logoIcon: 'text-pink-300/40', logoText: 'text-pink-100/30'
            },
            standard: { 
                border: 'from-blue-600 via-indigo-400 to-blue-800', accent: 'text-blue-500', bg: 'bg-slate-900',
                logoBg: 'bg-blue-500/10', logoBorder: 'border-blue-500/10', logoIcon: 'text-blue-400/40', logoText: 'text-blue-200/30'
            }
        };
        
        // Normalize player data (snake_case to camelCase)
        const p = computed(() => {
            const raw = props.player;
            if (!raw) return null;

            const getFullUrl = (path) => {
                if (!path) return null;
                if (path.startsWith('http') || path.startsWith('data:')) return path;
                // Handle paths that already start with /storage/
                if (path.startsWith('/storage/')) return path;
                return `/storage/${path}`;
            };

            return {
                ...raw,
                photo: getFullUrl(raw.photo_url || raw.photo),
                signature: getFullUrl(raw.signature_url || raw.signature),
                merged_photo: getFullUrl(raw.merged_photo_url || raw.merged_photo),
                photoX: raw.photoX ?? raw.photo_x ?? 0,
                photoY: raw.photoY ?? raw.photo_y ?? 0,
                photoScale: raw.photoScale ?? raw.photo_scale ?? 1,
                sigX: raw.sigX ?? raw.sig_x ?? 50,
                sigY: raw.sigY ?? raw.sig_y ?? 50,
                sigScale: raw.sigScale ?? raw.sig_scale ?? 1,
                sigRotate: raw.sigRotate ?? raw.sig_rotate ?? 0,
                sigWidth: raw.sigWidth ?? raw.sig_width ?? 100,
                sigHeight: raw.sigHeight ?? raw.sig_height ?? 100,
            };
        });
        
        const themeStyle = computed(() => {
            if (!p.value) return themes.standard;
            return themes[p.value.theme || 'standard'] || themes.standard;
        });
        const getLevelTag = (lvl) => LEVEL_TAGS[lvl] || '網球愛愛好者';
        return { cardContainer, p, themeStyle, getLevelTag };
    }
};

const PlayerDetailModal = {
    props: ['player', 'stats'],
    components: { AppIcon, PlayerCard },
    template: '#player-detail-modal-template',
    emits: ['close', 'open-match'],
    setup(props) {
        const isFlipped = ref(false);
        
        // Reset flip state when player changes
        watch(() => props.player, () => {
            isFlipped.value = false;
        });

        const backStats = computed(() => {
            const p = props.player;
            if (!p) return [];
            return [
                { label: '程度 (NTRP)', value: p.level || '3.5', icon: 'zap' },
                { label: '慣用手', value: p.handed || '右手', icon: 'target' },
                { label: '反手類型', value: p.backhand || '雙反', icon: 'edit-3' },
                { label: '性別', value: p.gender || '男', icon: 'gender' }
            ];
        });

        const getThemeStyle = (theme) => {
            const themes = {
                gold: { bg: 'bg-slate-900', logoBg: 'bg-yellow-500/10' },
                platinum: { bg: 'bg-slate-900', logoBg: 'bg-white/10' },
                holographic: { bg: 'bg-slate-900', logoBg: 'bg-cyan-500/10' },
                onyx: { bg: 'bg-black', logoBg: 'bg-white/5' },
                sakura: { bg: 'bg-slate-900', logoBg: 'bg-pink-500/10' },
                standard: { bg: 'bg-slate-900', logoBg: 'bg-blue-500/10' }
            };
            return themes[theme] || themes.standard;
        };

        const formatDate = (date) => {
            if (!date) return '2026/01/01';
            return new Date(date).toLocaleDateString('zh-TW', { year: 'numeric', month: '2-digit', day: '2-digit' });
        };

        return { isFlipped, backStats, getThemeStyle, formatDate };
    }
};
const MessageDetailModal = {
    props: ['open', 'targetUser', 'currentUser'],
    components: { AppIcon },
    template: '#message-detail-modal-template',
    emits: ['update:open', 'message-sent'],
    setup(props, { emit }) {
        const messages = ref([]);
        const loading = ref(false);
        const sending = ref(false);
        const newMessage = ref('');
        const chatContainer = ref(null);

        const formatDate = (dateString) => {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleDateString('zh-TW', { month: 'numeric', day: 'numeric', hour: '2-digit', minute: '2-digit' });
        };

        const scrollToBottom = () => {
            nextTick(() => {
                if (chatContainer.value) {
                    chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
                }
            });
        };

        const hasMore = ref(false);
        const page = ref(1);

        const loadChat = async (isPolling = false) => {
            if (!props.targetUser) return;
            if (!isPolling) loading.value = true;
            
            try {
                let url = `/messages/chat/${props.targetUser.id}`;
                const lastMsg = messages.value.length > 0 ? messages.value[messages.value.length - 1] : null;
                
                if (isPolling && lastMsg) {
                    url += `?after_id=${lastMsg.id}`;
                } else {
                    url += `?page=${page.value}`;
                }

                const response = await api.get(url);
                if (response.data.success) {
                    let newMessages = [];
                    
                    if (isPolling) {
                        // Polling returns array directly (from get())
                        newMessages = response.data.data.map(m => ({
                            ...m,
                            is_me: m.from_user_id === props.currentUser.id
                        }));
                        
                        if (newMessages.length > 0) {
                            messages.value = [...messages.value, ...newMessages];
                            scrollToBottom();
                        }
                    } else {
                        // Pagination returns paginated object (from paginate())
                        const data = response.data.data;
                        const rawMessages = data.data || [];
                        hasMore.value = data.next_page_url !== null;
                        
                        newMessages = rawMessages.map(m => ({
                            ...m,
                            is_me: m.from_user_id === props.currentUser.id
                        })).reverse(); // Reverse because backend gives desc
                        
                        if (page.value === 1) {
                            messages.value = newMessages;
                            scrollToBottom();
                        } else {
                            // Prepend for load more
                            const currentHeight = chatContainer.value.scrollHeight;
                            messages.value = [...newMessages, ...messages.value];
                            nextTick(() => {
                                // Restore scroll position
                                chatContainer.value.scrollTop = chatContainer.value.scrollHeight - currentHeight;
                            });
                        }
                    }
                }
            } catch (error) {
                console.error('Load chat error:', error);
            } finally {
                if (!isPolling) loading.value = false;
            }
        };

        const loadMore = () => {
            if (!hasMore.value || loading.value) return;
            page.value++;
            loadChat(false);
        };

        const sendMessage = async () => {
            if (!newMessage.value.trim() || sending.value) return;
            sending.value = true;
            try {
                const response = await api.post('/messages', {
                    to_user_id: props.targetUser.id,
                    content: newMessage.value
                });

                if (response.data.success) {
                    const msg = response.data.data;
                    messages.value.push({
                        ...msg,
                        is_me: true
                    });
                    newMessage.value = '';
                    scrollToBottom();
                    emit('message-sent');
                }
            } catch (error) {
                console.error('Send message error:', error);
                alert('發送失敗，請稍後再試');
            } finally {
                sending.value = false;
            }
        };

        let pollInterval;

        watch(() => props.open, (newVal) => {
            if (newVal) {
                document.body.style.overflow = 'hidden';
                page.value = 1;
                loadChat(false);
                pollInterval = setInterval(() => loadChat(true), 5000);
            } else {
                document.body.style.overflow = '';
                messages.value = [];
                page.value = 1;
                if (pollInterval) clearInterval(pollInterval);
            }
        });

        onUnmounted(() => {
            document.body.style.overflow = '';
            if (pollInterval) clearInterval(pollInterval);
        });

        return { messages, loading, sending, newMessage, chatContainer, formatDate, sendMessage, hasMore, loadMore };
    }
};

const MatchModal = {
    props: ['open', 'player'],
    components: { AppIcon },
    template: '#match-modal-template',
    emits: ['update:open', 'submit'],
    setup(props, { emit }) {
        const textModel = ref('');
        const photoUrl = computed(() => {
            const path = props.player?.photo_url || props.player?.photo;
            if (!path) return null;
            if (path.startsWith('http') || path.startsWith('data:')) return path;
            if (path.startsWith('/storage/')) return path;
            return `/storage/${path}`;
        });
        return { textModel, photoUrl };
    }
};

const NtrpGuideModal = {
    props: ['open', 'descs'],
    components: { AppIcon },
    template: '#ntrp-guide-modal-template',
    emits: ['update:open']
};

const QuickEditModal = {
    props: ['open', 'form', 'levels', 'regions'],
    components: { AppIcon },
    template: '#quick-edit-modal-template',
    emits: ['update:open']
};

// --- Main App ---
createApp({
    components: { SignaturePad, PlayerCard, AppIcon, PlayerDetailModal, MatchModal, NtrpGuideModal, QuickEditModal },
    setup() {
        // Route Configuration
        const routes = {
            '/': 'home',
            '/list': 'list',
            '/create': 'create',
            '/messages': 'messages',
            '/auth': 'auth',
            '/mycards': 'mycards'
        };
        const routePaths = Object.fromEntries(Object.entries(routes).map(([k, v]) => [v, k]));
        
        const view = ref('home');
        const isLoggedIn = ref(false);
        const currentUser = ref(null);
        const isLoginMode = ref(true);
        const showUserMenu = ref(false);
        const messageTab = ref('inbox');
        const regions = REGIONS; const levels = LEVELS;
        const players = ref(INITIAL_PLAYERS);
        const messages = ref([]);
        const isPlayersLoading = ref(false);
        const isPlayersError = ref(false);
        
        // Confirm Dialog State
        const confirmDialog = reactive({
            open: false,
            title: '',
            message: '',
            confirmText: '確認',
            cancelText: '取消',
            onConfirm: null,
            type: 'danger' // 'danger' | 'warning' | 'info'
        });
        
        const showConfirm = (options) => {
            Object.assign(confirmDialog, {
                open: true,
                title: options.title || '確認操作',
                message: options.message || '確定要執行此操作嗎？',
                confirmText: options.confirmText || '確認',
                cancelText: options.cancelText || '取消',
                type: options.type || 'danger',
                onConfirm: options.onConfirm
            });
        };
        
        const hideConfirm = () => {
            confirmDialog.open = false;
            confirmDialog.onConfirm = null;
        };
        
        const executeConfirm = () => {
            if (confirmDialog.onConfirm) confirmDialog.onConfirm();
            hideConfirm();
        };
        
        // Computed: Has unread messages
        const hasUnread = computed(() => {
            if (!Array.isArray(messages.value)) return false;
            return messages.value.some(m => m.unread || !m.read_at);
        });
        
        // Get current user ID from localStorage
        const getCurrentUserId = () => {
            try {
                const user = JSON.parse(localStorage.getItem('auth_user'));
                return user?.id;
            } catch (e) { return null; }
        };
        
        // Computed: My cards (cards created by current user)
        const myCards = computed(() => {
            if (Array.isArray(myPlayers.value) && myPlayers.value.length > 0) {
                return myPlayers.value;
            }
            const userId = getCurrentUserId();
            if (!userId || !Array.isArray(players.value)) return [];
            return players.value.filter(p => p.user_id === userId);
        });

        // Load my cards from API
        const loadMyCards = async () => {
            if (!isLoggedIn.value) return;
            try {
                const response = await api.get('/my-cards');
                if (response.data.success) {
                    myPlayers.value = response.data.data;
                }
            } catch (error) {

            }
        };
        
        // Reset form to default values
        const resetForm = () => {
            const user = currentUser.value;
            Object.assign(form, {
                id: null,
                name: user?.name || '', 
                region: '台北市', level: '3.5', handed: '右手', backhand: '雙反', gender: '男',
                intro: '', fee: '免費 (交流為主)', 
                photo: null, // User requested NOT to pre-fill photo
                signature: null, theme: 'standard',
                merged_photo: null,
                photoX: 0, photoY: 0, photoScale: 1, 
                sigX: 50, sigY: 50, sigScale: 1, sigRotate: 0,
                sigWidth: 100, sigHeight: 100
            });
            currentStep.value = 1;
            stepAttempted.value = {};
            isAdjustingPhoto.value = false;
            isAdjustingSig.value = false;
        };

        // Edit card - populate form and go to create page
        const editCard = (card) => {
            resetForm(); // Clear first
            Object.assign(form, {
                id: card.id,
                name: card.name,
                region: card.region,
                level: card.level,
                gender: card.gender,
                handed: card.handed,
                backhand: card.backhand,
                intro: card.intro,
                fee: card.fee,
                photo: card.photo_url || card.photo,
                signature: card.signature_url || card.signature,
                merged_photo: null, // Clear merged_photo to show dynamic edit preview
                theme: card.theme || 'standard',
                photoX: card.photo_x || 0,
                photoY: card.photo_y || 0,
                photoScale: card.photo_scale || 1,
                sigX: card.sig_x ?? 50,
                sigY: card.sig_y ?? 50,
                sigScale: card.sig_scale || 1,
                sigRotate: card.sig_rotate || 0,
                sigWidth: card.sig_width || 100,
                sigHeight: card.sig_height || 100,
            });
            currentStep.value = 4; // Go to final step for review
            navigateTo('create', false);
        };
        
        // Delete card with custom confirm dialog
        const deleteCard = (cardId) => {
            showConfirm({
                title: '刪除球友卡',
                message: '確定要刪除這張球友卡嗎？此操作無法復原。',
                confirmText: '確認刪除',
                cancelText: '取消',
                type: 'danger',
                onConfirm: async () => {
                    isLoading.value = true;
                    try {
                        await api.delete(`/players/${cardId}`);
                        players.value = players.value.filter(p => p.id !== cardId);
                        showToast('球友卡已刪除', 'info');
                    } catch (error) {

                        showToast('刪除失敗，請稍後再試', 'error');
                    } finally {
                        isLoading.value = false;
                    }
                }
            });
        };
        // Format date helper
        const formatDate = (dateStr) => {
            if (!dateStr) return '';
            const date = new Date(dateStr);
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMs / 3600000);
            const diffDays = Math.floor(diffMs / 86400000);
            
            if (diffMins < 1) return '剛剛';
            if (diffMins < 60) return `${diffMins} 分鐘前`;
            if (diffHours < 24) return `${diffHours} 小時前`;
            if (diffDays < 7) return `${diffDays} 天前`;
            return date.toLocaleDateString('zh-TW');
        };
        const features = [
            { icon: 'zap', title: '快速約球陪打', desc: '精準媒合 NTRP 等級，輕鬆找到實力相當的球友或專業陪打夥伴。' },
            { icon: 'shield-check', title: '製作專屬球友卡', desc: '建立專業視覺風格的數位球友卡，在社群大廳展現您的網球實力與風格。' },
            { icon: 'dollar-sign', title: '刊登完全免費', desc: '建立檔案、刊登曝光、發送約打訊息完全不收費，讓網球社交更簡單。' }
        ];
        
        // Toast Notifications
        const toasts = ref([]);
        let lastToastMessage = '';
        let lastToastTime = 0;
        
        const showToast = (message, type = 'info', duration = 4000) => {
            // Prevent duplicate toasts within 500ms
            const now = Date.now();
            if (message === lastToastMessage && now - lastToastTime < 500) {
                return;
            }
            lastToastMessage = message;
            lastToastTime = now;
            
            const id = now;
            toasts.value.push({ id, message, type });
            setTimeout(() => removeToast(id), duration);
        };
        const removeToast = (id) => {
            const index = toasts.value.findIndex(t => t.id === id);
            if (index > -1) toasts.value.splice(index, 1);
        };

        // Helper to get full URL for storage paths
        const getUrl = (path) => {
            if (!path) return null;
            if (path.startsWith('http') || path.startsWith('data:')) return path;
            return `/storage/${path}`;
        };
        // Navigation function with History API
        const navigateTo = (viewName, shouldReset = true) => {
            if (viewName === 'create' && shouldReset) {
                resetForm();
            }
            view.value = viewName;
            const path = routePaths[viewName] || '/';
            window.history.pushState({ view: viewName }, '', path);
            // Scroll to top smoothly
            window.scrollTo({ top: 0, behavior: 'smooth' });
        };
        
        // Parse current URL on mount - Improved to handle subdirectories
        const parseRoute = () => {
            const path = window.location.pathname;

            // Priority 1: Exact match in routes
            let viewName = routes[path];
            
            // Priority 2: Match by suffix (for subdirectories)
            if (!viewName) {
                const matchedKey = Object.keys(routes).find(r => r !== '/' && path.endsWith(r));
                if (matchedKey) viewName = routes[matchedKey];
            }
            
            // Priority 3: Default to home if at root of project
            if (!viewName) viewName = 'home';
            
            view.value = viewName;

            if (viewName === 'create') {
                resetForm();
            }

            return viewName;
        };

        const form = reactive({
            name: '', region: '台北市', level: '3.5', handed: '右手', backhand: '雙反', gender: '男',
            intro: '', fee: '免費 (交流為主)', photo: null, signature: null, theme: 'standard',
            merged_photo: null,
            photoX: 0, photoY: 0, photoScale: 1, 
            sigX: 50, sigY: 50, sigScale: 1, sigRotate: 0,
            sigWidth: 100, sigHeight: 100
        });
        const matchModal = reactive({ open: false, player: null, text: '' });
        const detailPlayer = ref(null);
        const isSigning = ref(false);
        const showNtrpGuide = ref(false);
        const levelDescs = LEVEL_DESCS;
        const cardThemes = {
            gold: { border: 'from-yellow-600 via-yellow-200 to-yellow-700', accent: 'text-yellow-500', bg: 'bg-slate-900', label: '黃金版' },
            platinum: { border: 'from-slate-400 via-white to-slate-500', accent: 'text-blue-400', bg: 'bg-slate-900', label: '白金版' },
            holographic: { border: 'from-pink-500 via-cyan-300 via-yellow-200 to-purple-600', accent: 'text-cyan-400', bg: 'bg-slate-900', label: '幻彩版' },
            onyx: { border: 'from-slate-900 via-slate-600 to-black', accent: 'text-slate-400', bg: 'bg-black', label: '曜石黑' },
            sakura: { border: 'from-pink-400 via-pink-100 to-pink-500', accent: 'text-pink-400', bg: 'bg-slate-900', label: '櫻花粉' },
            standard: { border: 'from-blue-600 via-indigo-400 to-blue-800', accent: 'text-blue-500', bg: 'bg-slate-900', label: '專業版' }
        };
        const stepTitles = [
            '上傳您的專業形象照並填寫姓名',
            '設定您的 NTRP 分級與擊球技術',
            '選擇活動地區並撰寫約打宣告',
            '切換視覺主題並完成手寫簽名'
        ];
        const genders = ['男', '女'];
        const currentStep = ref(1);
        const showPreview = ref(false);
        const showQuickEditModal = ref(false);
        const isAdjustingPhoto = ref(false);
        const isAdjustingSig = ref(false);
        const myPlayers = ref([]);
        
        // Search, Filter, Pagination State
        const searchQuery = ref('');
        const selectedRegion = ref('全部');
        const currentPage = ref(1);
        const perPage = 8;
        
        // Computed: Active regions (regions that have players)
        const activeRegions = computed(() => {
            if (!Array.isArray(players.value)) return [];
            const regionsWithPlayers = new Set(players.value.map(p => p?.region).filter(Boolean));
            return REGIONS.filter(r => regionsWithPlayers.has(r));
        });
        
        // Computed: Filtered players based on search and region
        const filteredPlayers = computed(() => {
            try {
                let result = Array.isArray(players.value) ? players.value : [];
                
                // Filter by region
                if (selectedRegion.value !== '全部') {
                    result = result.filter(p => p && p.region === selectedRegion.value);
                }
                
                // Filter by search query
                const query = (searchQuery.value || '').trim().toLowerCase();
                if (query) {
                    result = result.filter(p => {
                        if (!p) return false;
                        return (String(p.name || '').toLowerCase().includes(query)) ||
                               (String(p.region || '').toLowerCase().includes(query)) ||
                               (String(p.level || '').includes(query)) ||
                               (String(p.intro || '').toLowerCase().includes(query));
                    });
                }
                
                return result;
            } catch (e) {

                return [];
            }
        });
        
        // Computed: Total pages
        const totalPages = computed(() => {
            const count = Array.isArray(filteredPlayers.value) ? filteredPlayers.value.length : 0;
            return Math.ceil(count / perPage);
        });
        
        // Computed: Paginated players
        const paginatedPlayers = computed(() => {
            if (!Array.isArray(filteredPlayers.value)) return [];
            const start = (currentPage.value - 1) * perPage;
            return filteredPlayers.value.slice(start, start + perPage);
        });
        
        // Computed: Display pages for pagination - Fixed for duplicate keys
        const displayPages = computed(() => {
            const total = totalPages.value;
            const current = currentPage.value;
            if (total <= 5) return Array.from({ length: total }, (_, i) => i + 1);
            
            if (current <= 3) return [1, 2, 3, 4, { type: 'dot', id: 'dot1' }, total];
            if (current >= total - 2) return [1, { type: 'dot', id: 'dot1' }, total - 3, total - 2, total - 1, total];
            return [1, { type: 'dot', id: 'dot1' }, current - 1, current, current + 1, { type: 'dot', id: 'dot2' }, total];
        });
        
        // Reset page when filter changes
        watch([searchQuery, selectedRegion], () => {
            currentPage.value = 1;
        });
        
        // Step Validation - Check if each step requirements are met
        const canProceedStep1 = computed(() => {
            return form.photo && form.name && form.name.trim().length > 0;
        });
        
        const canProceedStep2 = computed(() => {
            return form.level && form.handed && form.backhand;
        });
        
        const canProceedStep3 = computed(() => {
            return form.region && form.region.trim().length > 0;
        });
        
        // Check if user can go to a specific step (for step indicator clicks)
        const canGoToStep = (targetStep) => {
            // Can always go back to previous steps
            if (targetStep < currentStep.value) return true;
            // Can stay on current step
            if (targetStep === currentStep.value) return true;
            // Can only go forward if all previous steps are completed
            if (targetStep === 2) return canProceedStep1.value;
            if (targetStep === 3) return canProceedStep1.value && canProceedStep2.value;
            if (targetStep === 4) return canProceedStep1.value && canProceedStep2.value && canProceedStep3.value;
            return false;
        };
        
        // Try to go to next step with validation
        const stepAttempted = reactive({ 1: false, 2: false, 3: false, 4: false });
        let isValidating = false;

        const tryNextStep = () => {
            // Prevent double-click or rapid clicks
            if (isValidating) return;
            isValidating = true;
            setTimeout(() => { isValidating = false; }, 500);
            
            stepAttempted[currentStep.value] = true;
            
            if (currentStep.value === 1 && !canProceedStep1.value) {
                showToast('請上傳照片並填寫姓名', 'error');
                return;
            }
            if (currentStep.value === 2 && !canProceedStep2.value) {
                showToast('請選擇 NTRP 等級和技術設定', 'error');
                return;
            }
            if (currentStep.value === 3 && !canProceedStep3.value) {
                showToast('請選擇您的活動地區', 'error');
                return;
            }
            // Reset attempted state for next step
            stepAttempted[currentStep.value + 1] = false;
            currentStep.value++;
        };
        
        // Try to go to a specific step (for step indicator clicks)
        const tryGoToStep = (targetStep) => {
            if (canGoToStep(targetStep)) {
                currentStep.value = targetStep;
            } else {
                // Show appropriate error message
                if (targetStep >= 2 && !canProceedStep1.value) {
                    showToast('請先完成第一步：上傳照片與填寫姓名', 'error');
                } else if (targetStep >= 3 && !canProceedStep2.value) {
                    showToast('請先完成第二步：選擇 NTRP 等級', 'error');
                } else if (targetStep >= 4 && !canProceedStep3.value) {
                    showToast('請先完成第三步：選擇活動地區', 'error');
                }
            }
        };

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
            
            // Store rect for percentage calculation
            const rect = e.currentTarget.getBoundingClientRect();
            dragInfo.rect = rect;

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
            if (!dragInfo.active || !dragInfo.rect) return;
            const clientX = e.clientX || (e.touches && e.touches[0].clientX);
            const clientY = e.clientY || (e.touches && e.touches[0].clientY);
            const dx = clientX - dragInfo.startX;
            const dy = clientY - dragInfo.startY;

            const { width, height } = dragInfo.rect;

            if (dragInfo.target === 'photo') {
                form.photoX = dragInfo.initialX + (dx / width) * 100;
                form.photoY = dragInfo.initialY + (dy / height) * 100;
            } else if (dragInfo.target === 'sig') {
                form.sigX = dragInfo.initialX + (dx / width) * 100;
                form.sigY = dragInfo.initialY + (dy / height) * 100;
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
        const initMoveable = (target, retryCount = 0) => {
            if (typeof Moveable === 'undefined') {
                if (retryCount < 10) {
                    setTimeout(() => initMoveable(target, retryCount + 1), 200);
                } else {

                }
                return;
            }
            if (moveableInstance) moveableInstance.destroy();
            if (!target || !isAdjustingSig.value) return;

            moveableInstance = new Moveable(document.body, {
                target: target,
                draggable: true,
                scalable: true,
                rotatable: true,
                pinchable: true, // Support pinch zoom on mobile
                pinchOutside: true,
                controlSize: 14, // Slightly larger for fingers
                throttleDrag: 0,
                throttleScale: 0,
                throttleRotate: 0,
                origin: false,
                edge: true,
                keepRatio: true,
            }).on("drag", ({ target, beforeDelta }) => {
                const parent = target.parentElement;
                if (!parent) return;
                
                const rect = parent.getBoundingClientRect();
                if (rect.width > 0 && rect.height > 0) {
                    // Update state using percentage relative to parent
                    form.sigX += (beforeDelta[0] / rect.width) * 100;
                    form.sigY += (beforeDelta[1] / rect.height) * 100;
                }
            }).on("scale", ({ delta }) => {
                form.sigScale *= delta[0];
            }).on("rotate", ({ delta }) => {
                form.sigRotate += delta;
            }).on("pinch", ({ delta }) => {
                // Handle pinch zoom (scaling)
                form.sigScale *= delta[0];
            });
        };

        // Watch for adjustment mode toggle
        watch(isAdjustingSig, (val) => {
            if (val) {
                nextTick(() => {
                    // Try to find the target in the current view
                    const target = document.querySelector('.capture-target #target-signature') || 
                                 document.getElementById('target-signature');
                    if (target) initMoveable(target);
                });
            } else {
                if (moveableInstance) {
                    moveableInstance.destroy();
                    moveableInstance = null;
                }
            }
        });

        watch(() => form.signature, (val) => {
            if (!val) isAdjustingSig.value = false;
        });

        watch(currentStep, (val) => {
            if (val !== 4) isAdjustingSig.value = false;
        });

        watch(showPreview, (val) => {
            if (val) isAdjustingSig.value = false;
        });

        // Watch for view changes to refresh data
        watch(view, (newView) => {
            if (newView === 'home' || newView === 'list') {
                loadPlayers();
            } else if (newView === 'messages') {
                loadMessages();
            } else if (newView === 'mycards') {
                loadMyCards();
            }
        });

        const toggleAdjustSig = () => {
            isAdjustingSig.value = !isAdjustingSig.value;
        };

        const handleSignatureUpdate = (sigData) => {
            if (sigData && typeof sigData === 'object' && sigData.dataUrl) {
                form.signature = sigData.dataUrl;
                form.sigX = sigData.xPct;
                form.sigY = sigData.yPct;
                form.sigWidth = sigData.widthPct;
                form.sigHeight = sigData.heightPct;
                form.sigScale = 1;
                form.sigRotate = 0;
                
                // Clear merged_photo to ensure the new signature is visible
                form.merged_photo = null;
                isAdjustingSig.value = true;
                isSigning.value = false; // Close signing mode
            } else {
                form.signature = sigData;
                if (sigData) {
                    form.sigX = 50;
                    form.sigY = 50;
                    form.sigScale = 1;
                    form.sigRotate = 0;
                    form.sigWidth = 100;
                    form.sigHeight = 100;
                    form.merged_photo = null;
                    isAdjustingSig.value = true;
                    isSigning.value = false; // Close signing mode
                } else {
                    isAdjustingSig.value = false;
                    if (moveableInstance) {
                        moveableInstance.destroy();
                        moveableInstance = null;
                    }
                }
            }
        };
        const scrollToSubmit = () => {
            document.getElementById('create-form').scrollIntoView({ behavior: 'smooth', block: 'end' });
        };
        // --- API Functions ---
        const isLoading = ref(false);
        const authError = ref('');
        const authForm = reactive({ name: '', email: '', password: '', password_confirmation: '' });

        // Load players from API
        const loadPlayers = async () => {
            isPlayersLoading.value = true;

            try {
                const response = await api.get('/players?per_page=1000');
                if (response.data.success) {
                    const data = response.data.data;
                    if (data) {
                        const rawPlayers = Array.isArray(data.data) ? data.data : (Array.isArray(data) ? data : []);
                        // Filter out any null/undefined players
                        players.value = rawPlayers.filter(p => p && p.id);
                    } else {
                        players.value = [];
                    }
                }
            } catch (error) {

            } finally {
                isPlayersLoading.value = false;
            }
        };

        // Load messages from API
        const loadMessages = async () => {
            if (!isLoggedIn.value) return;

            try {
                const response = await api.get('/messages');

                if (response.data.success) {
                    const data = response.data.data;
                    if (data) {
                        messages.value = Array.isArray(data.data) ? data.data : (Array.isArray(data) ? data : []);
                    } else {
                        messages.value = [];
                    }

                }
            } catch (error) {

            }
        };

        // Check for saved auth on mount (including LINE callback)
        const checkAuth = () => {
            // Check for LINE callback token in URL
            const urlParams = new URLSearchParams(window.location.search);
            const lineToken = urlParams.get('line_token');
            const lineUser = urlParams.get('line_user');
            
            if (lineToken && lineUser) {
                try {
                    const userData = JSON.parse(lineUser);
                    localStorage.setItem('auth_token', lineToken);
                    localStorage.setItem('auth_user', lineUser);
                    isLoggedIn.value = true;
                    currentUser.value = userData;
                    showToast('LINE 登入成功！歡迎來到 LoveTennis', 'success');
                    loadMessages();
                    loadMyCards();
                    // Clean URL
                    window.history.replaceState({}, document.title, '/');
                    return;
                } catch (e) {
                    console.error('LINE callback parse error:', e);
                }
            }

            // Check for LINE error in URL
            const lineError = urlParams.get('error');
            if (lineError) {
                showToast(lineError, 'error');
                window.history.replaceState({}, document.title, '/auth');
            }

            // Normal auth check
            const token = localStorage.getItem('auth_token');
            const user = localStorage.getItem('auth_user');
            if (token && user) {
                isLoggedIn.value = true;
                try {
                    currentUser.value = JSON.parse(user);
                } catch (e) {}
                loadMessages();
                loadMyCards();
            }
        };



        // Logout
        const logout = async () => {
            try {
                await api.post('/logout');
            } catch (error) {}
            localStorage.removeItem('auth_token');
            localStorage.removeItem('auth_user');
            isLoggedIn.value = false;
            currentUser.value = null;
            showToast('已成功登出', 'info');
            navigateTo('home');
        };

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

        // Capture static card image
        const captureCardImage = async () => {
            if (typeof html2canvas === 'undefined') {

                for (let i = 0; i < 10; i++) {
                    await new Promise(r => setTimeout(r, 500));
                    if (typeof html2canvas !== 'undefined') break;
                }
                if (typeof html2canvas === 'undefined') {
                    showToast('截圖組件載入失敗，請重新整理頁面', 'error');
                    return null;
                }
            }
            const cardEl = document.querySelector('.capture-target') || document.querySelector('[ref="cardContainer"]');
            if (!cardEl) return null;
            
            // 0. Ensure we're not in adjustment mode
            if (typeof isAdjustingSig !== 'undefined') isAdjustingSig.value = false;

            // 1. Store original styles and identify layers
            const originalStyle = cardEl.getAttribute('style') || '';
            const originalClassName = cardEl.className;
            const mergedLayer = cardEl.querySelector('.merged-photo-layer');
            const originalMergedDisplay = mergedLayer ? mergedLayer.style.display : '';
            
            try {
                // 2. Ensure all images are loaded
                const images = cardEl.querySelectorAll('img');
                await Promise.all(Array.from(images).map(img => {
                    if (img.complete) return Promise.resolve();
                    return new Promise(resolve => { img.onload = resolve; img.onerror = resolve; });
                }));

                // 3. Hide merged layer to capture raw content
                if (mergedLayer) mergedLayer.style.display = 'none';

                // 4. Force static size and disable animations for capture
                const targetWidth = 320;
                const targetHeight = (targetWidth / 2.5) * 3.8;
                
                cardEl.style.width = `${targetWidth}px`;
                cardEl.style.height = `${targetHeight}px`;
                cardEl.style.maxWidth = 'none';
                cardEl.style.transform = 'none';
                cardEl.style.transition = 'none';
                cardEl.style.position = 'fixed';
                cardEl.style.top = '0';
                cardEl.style.left = '0';
                cardEl.style.zIndex = '9999';
                cardEl.style.pointerEvents = 'none';

                // 5. Wait for layout settling
                await new Promise(resolve => setTimeout(resolve, 100));

                // 6. Perform capture
                const canvas = await html2canvas(cardEl, {
                    useCORS: true,
                    allowTaint: true,
                    backgroundColor: null,
                    scale: 2,
                    width: targetWidth,
                    height: targetHeight,
                    logging: false,
                    onclone: (clonedDoc) => {
                        const clonedCard = clonedDoc.querySelector('.capture-target');
                        if (clonedCard) {
                            clonedCard.style.transform = 'none';
                            clonedCard.style.transition = 'none';
                            const clonedMerged = clonedCard.querySelector('.merged-photo-layer');
                            if (clonedMerged) clonedMerged.style.display = 'none';
                        }
                    }
                });

                return canvas.toDataURL('image/png');
            } catch (e) {

                return null;
            } finally {
                // 7. Restore original state
                cardEl.setAttribute('style', originalStyle);
                cardEl.className = originalClassName;
                if (mergedLayer) mergedLayer.style.display = originalMergedDisplay;
            }
        };

        // Save card to API
        // Save card to API
        const saveCard = async () => {
            // Check if user is logged in
            if (!isLoggedIn.value) {
                showToast('請先登入才能製作球友卡', 'error');
                navigateTo('auth');
                return;
            }
            
            // Final validation before saving
            if (!canProceedStep1.value || !canProceedStep2.value || !canProceedStep3.value) {
                showToast('請確認所有必填欄位都已填寫', 'error');
                return;
            }
            
            isLoading.value = true;
            try {
                const payload = {
                    name: form.name,
                    region: form.region,
                    level: form.level,
                    gender: form.gender,
                    handed: form.handed,
                    backhand: form.backhand,
                    intro: form.intro,
                    fee: form.fee,
                    theme: form.theme,
                    photo: form.photo,
                    signature: form.signature,
                    photo_x: form.photoX,
                    photo_y: form.photoY,
                    photo_scale: form.photoScale,
                    sig_x: form.sigX,
                    sig_y: form.sigY,
                    sig_scale: form.sigScale,
                    sig_rotate: form.sigRotate,
                    sig_width: form.sigWidth,
                    sig_height: form.sigHeight,
                };

                let response;
                if (form.id) {
                    response = await api.put(`/players/${form.id}`, payload);
                } else {
                    response = await api.post('/players', payload);
                }

                if (response.data.success) {
                    showToast(form.id ? '球友卡已更新' : '球友卡建立成功！', 'success');
                    
                    // Refresh lists
                    await loadPlayers();
                    await loadMyCards();
                    
                    // Reset and navigate
                    resetForm();
                    navigateTo('mycards');
                }
            } catch (error) {
                console.error('Save card error:', error);
                showToast('儲存失敗，請稍後再試', 'error');
            } finally {
                isLoading.value = false;
            }
        };
        const getPlayersByRegion = (r) => players.value.filter(p => p.region === r);
        const openMatchModal = (p) => { matchModal.player = p; matchModal.open = true; };

        // Send match request via API
        const sendMatchRequest = async () => {
            if (!isLoggedIn.value) {
                // Redirect to auth if not logged in
                matchModal.open = false;
                navigateTo('auth');
                return;
            }

            try {
                const response = await api.post('/messages', {
                    to_player_id: matchModal.player.id,
                    content: matchModal.text || `Hi ${matchModal.player.name}，我想跟你約打！`,
                });

                if (response.data.success) {
                    // Add to local messages
                    messages.value.unshift({
                        id: response.data.data.id,
                        from: '我',
                        content: matchModal.text,
                        date: '剛剛',
                        unread: false,
                    });
                }
            } catch (error) {

                // Fallback: show local confirmation
                messages.value.unshift({ 
                    id: Date.now(), 
                    from: '系統', 
                    content: `已發送邀約給 ${matchModal.player.name}`, 
                    date: '剛剛', 
                    unread: true 
                });
            }
            
            matchModal.open = false;
            matchModal.text = '';
            showToast(`已成功發送約打邀請給 ${matchModal.player.name}`, 'success');
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

        // Mark message as read
        const markMessageRead = async (messageId) => {
            try {
                await api.put(`/messages/${messageId}/read`);
                const msg = messages.value.find(m => m.id === messageId);
                if (msg) {
                    msg.read_at = new Date().toISOString();
                    msg.unread = false;
                }
            } catch (error) {

            }
        };

        // Handle browser back/forward
        onMounted(() => {
            checkAuth();
            parseRoute();
            loadPlayers();
            window.addEventListener('popstate', (event) => {
                if (event.state && event.state.view) {
                    view.value = event.state.view;
                } else {
                    parseRoute();
                }
            });
        });

        // Poll messages when on messages view
        let messagePollInterval;
        watch(view, (newView) => {
            if (newView === 'messages') {
                loadMessages();
                if (!messagePollInterval) {
                    messagePollInterval = setInterval(loadMessages, 5000);
                }
            } else {
                if (messagePollInterval) {
                    clearInterval(messagePollInterval);
                    messagePollInterval = null;
                }
            }
        });

        // Message Pagination (Client-side)
        const messagesLimit = ref(20);
        const paginatedMessages = computed(() => {
            if (!Array.isArray(messages.value)) return [];
            return messages.value.slice(0, messagesLimit.value);
        });
        const hasMoreMessages = computed(() => {
            return Array.isArray(messages.value) && messagesLimit.value < messages.value.length;
        });
        const loadMoreMessages = () => {
            messagesLimit.value += 20;
        };

        const selectedChatUser = ref(null);
        const showMessageDetail = ref(false);

        const openMessage = (message) => {
            // Determine the "other" user in the conversation
            const otherUser = message.from_user_id === currentUser.value.id ? message.receiver : message.sender;
            // If we have player info attached to the message, we can use it to enrich the user object
            if (message.player) {
                otherUser.player = message.player;
            }
            
            selectedChatUser.value = otherUser;
            showMessageDetail.value = true;
            
            // Mark as read locally if needed (backend handles it on chat load)
            if (message.unread_count > 0) {
                message.unread_count = 0;
            }
        };

        const onMessageSent = () => {
            // Refresh conversation list to show latest message
            loadMessages();
        };

        return {
            view, isLoggedIn, currentUser, isLoginMode, hasUnread, regions, levels, players, messages, features, form, 
            matchModal, detailPlayer, isSigning, showNtrpGuide, levelDescs, cardThemes, currentStep, showPreview, showQuickEditModal, stepTitles, genders,
            isAdjustingPhoto, isAdjustingSig, toggleAdjustSig, handleSignatureUpdate, dragInfo, startDrag, handleDrag, stopDrag, initMoveable,
            // UI State
            showUserMenu, messageTab, formatDate, toasts, showToast, removeToast,
            // Confirm Dialog
            confirmDialog, showConfirm, hideConfirm, executeConfirm,
            // My Cards
            myCards, editCard, deleteCard,
            // Search, Filter, Pagination
            searchQuery, selectedRegion, currentPage, perPage, activeRegions, filteredPlayers, totalPages, paginatedPlayers, displayPages,
            // Step Validation
            canProceedStep1, canProceedStep2, canProceedStep3, canGoToStep, tryNextStep, tryGoToStep, stepAttempted,
            // Navigation
            navigateTo, getUrl,
            // Auth & API
            isLoading, isPlayersLoading, authError, authForm, logout, loadPlayers, loadMessages, loadMyCards,
            triggerUpload, handleFileUpload, saveCard, resetForm, getPlayersByRegion, 
            openMatchModal, sendMatchRequest, showDetail, getDetailStats, scrollToSubmit, markMessageRead,
            selectedChatUser, showMessageDetail, openMessage, onMessageSent,
            // Message Pagination
            paginatedMessages, hasMoreMessages, loadMoreMessages
        };
    },
    components: {
        'app-icon': AppIcon,
        'player-card': PlayerCard,
        'player-detail-modal': PlayerDetailModal,
        'match-modal': MatchModal,
        'ntrp-guide-modal': NtrpGuideModal,
        'quick-edit-modal': QuickEditModal,
        'message-detail-modal': MessageDetailModal
    }
}).mount('#app');
</script>
