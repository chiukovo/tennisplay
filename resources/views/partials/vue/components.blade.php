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
    props: ['player', 'stats', 'players'],
    components: { AppIcon, PlayerCard },
    template: '#player-detail-modal-template',
    emits: ['close', 'open-match', 'update:player'],
    setup(props, { emit }) {
        const isFlipped = ref(false);
        
        // Reset flip state when player changes
        watch(() => props.player, () => {
            isFlipped.value = false;
        });

        const currentIndex = computed(() => {
            if (!props.player || !props.players) return -1;
            return props.players.findIndex(p => p.id === props.player.id);
        });

        const hasPrev = computed(() => props.players && props.players.length > 1);
        const hasNext = computed(() => props.players && props.players.length > 1);
        const transitionName = ref('slide-next');

        const navigate = (direction) => {
            if (!props.players || props.players.length <= 1) return;
            
            // Set transition direction
            transitionName.value = direction > 0 ? 'slide-next' : 'slide-prev';
            
            let nextIndex = currentIndex.value + direction;
            
            // Loop logic
            if (nextIndex < 0) nextIndex = props.players.length - 1;
            if (nextIndex >= props.players.length) nextIndex = 0;
            
            emit('update:player', props.players[nextIndex]);
        };

        // Swipe Support
        let touchStartX = 0;
        const handleTouchStart = (e) => { touchStartX = e.touches[0].clientX; };
        const handleTouchEnd = (e) => {
            const touchEndX = e.changedTouches[0].clientX;
            const diff = touchStartX - touchEndX;
            if (Math.abs(diff) > 50) { // Threshold
                if (diff > 0) navigate(1); // Swipe left -> next
                else navigate(-1); // Swipe right -> prev
            }
        };

        // Keyboard support
        const handleKeydown = (e) => {
            if (!props.player) return;
            if (e.key === 'ArrowLeft') navigate(-1);
            if (e.key === 'ArrowRight') navigate(1);
            if (e.key === 'Escape') emit('close');
        };

        onMounted(() => window.addEventListener('keydown', handleKeydown));
        onUnmounted(() => window.removeEventListener('keydown', handleKeydown));

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

        return { 
            isFlipped, backStats, getThemeStyle, formatDate, 
            hasPrev, hasNext, navigate, currentIndex, transitionName,
            handleTouchStart, handleTouchEnd 
        };
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
                let url = `/messages/chat/${props.targetUser.uid}`;
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
                            is_me: m.sender?.uid === props.currentUser.uid
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
                            is_me: m.sender?.uid === props.currentUser.uid
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

const EventDetailModal = {
    props: ['open', 'event', 'likes', 'comments', 'commentDraft', 'currentUser'],
    components: { AppIcon },
    template: '#event-detail-modal-template',
    emits: ['update:open', 'like', 'join', 'comment', 'leave', 'update:comment-draft', 'delete-comment']
};
