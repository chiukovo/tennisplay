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
    props: ['player', 'size', 'isSigning', 'isAdjustingSig', 'isPlaceholder', 'isCapturing'],
    components: { AppIcon, SignaturePad },
    template: '#player-card-template',
    setup(props) {
        const cardContainer = ref(null);
        const themes = {
            gold: { 
                border: 'from-amber-500 via-yellow-300 to-amber-600', accent: 'text-yellow-500', bg: 'bg-slate-900',
                logoBg: 'bg-yellow-500/10', logoBorder: 'border-yellow-500/10', logoIcon: 'text-yellow-400/40', logoText: 'text-yellow-200/30'
            },
            platinum: { 
                border: 'from-slate-400 via-white to-slate-500', accent: 'text-blue-400', bg: 'bg-slate-900',
                logoBg: 'bg-white/10', logoBorder: 'border-white/10', logoIcon: 'text-white/40', logoText: 'text-white/30'
            },
            holographic: { 
                border: 'from-pink-500 via-cyan-400 via-yellow-300 to-purple-600', accent: 'text-cyan-400', bg: 'bg-slate-900',
                logoBg: 'bg-cyan-500/10', logoBorder: 'border-cyan-500/10', logoIcon: 'text-cyan-300/40', logoText: 'text-cyan-100/30'
            },
            onyx: { 
                border: 'from-slate-800 via-slate-600 to-slate-900', accent: 'text-slate-400', bg: 'bg-black',
                logoBg: 'bg-white/5', logoBorder: 'border-white/5', logoIcon: 'text-slate-400/30', logoText: 'text-slate-500/20'
            },
            sakura: { 
                border: 'from-pink-400 via-pink-200 to-pink-500', accent: 'text-pink-400', bg: 'bg-slate-900',
                logoBg: 'bg-pink-500/10', logoBorder: 'border-pink-500/10', logoIcon: 'text-pink-300/40', logoText: 'text-pink-100/30'
            },
            standard: { 
                border: 'from-blue-500 via-sky-400 to-indigo-600', accent: 'text-blue-500', bg: 'bg-slate-900',
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

        // Holo Effect Logic (Refined Math & Auto-Animation)
        const tilt = reactive({ 
            lp: 50, tp: 50,    // Light position
            spx: 50, spy: 50,  // Sparkle position
            opc: 0,            // Opacity
            rX: 0, rY: 0       // Rotation
        });
        const isAnimated = ref(true);
        let rafId = null;
        let resumeTimeout = null;

        const handleMove = (e) => {
            isAnimated.value = false;
            if (resumeTimeout) clearTimeout(resumeTimeout);
            if (rafId) cancelAnimationFrame(rafId);
            
            rafId = requestAnimationFrame(() => {
                const card = cardContainer.value;
                if (!card) return;
                const rect = card.getBoundingClientRect();
                let x, y;

                if (e.type === 'touchmove') {
                    x = e.touches[0].clientX - rect.left;
                    y = e.touches[0].clientY - rect.top;
                } else {
                    x = e.clientX - rect.left;
                    y = e.clientY - rect.top;
                }

                // Normalise mouse position (0-100)
                const px = Math.abs(Math.floor(100 / rect.width * x) - 100);
                const py = Math.abs(Math.floor(100 / rect.height * y) - 100);
                const pa = (50 - px) + (50 - py);

                // Math for light / sparkle positions
                tilt.lp = (50 + (px - 50) / 1.5);
                tilt.tp = (50 + (py - 50) / 1.5);
                tilt.spx = (50 + (px - 50) / 7);
                tilt.spy = (50 + (py - 50) / 7);
                tilt.opc = (15 + (Math.abs(pa) * 0.5)) / 100; // Subtler baseline

                // Math for rotation (Softer tilt for non-aggressive look)
                tilt.rX = ((tilt.tp - 50) / 6) * -1; 
                tilt.rY = ((tilt.lp - 50) / 4.5) * 0.5;
            });
        };

        const handleLeave = () => {
            if (rafId) cancelAnimationFrame(rafId);
            tilt.rX = 0;
            tilt.rY = 0;
            tilt.opc = 0;
            
            // Resume animation after delay
            resumeTimeout = setTimeout(() => {
                isAnimated.value = true;
            }, 2500);
        };

        const isHoloTheme = computed(() => {
            if (!p.value) return false;
            return ['gold', 'platinum', 'holographic'].includes(p.value.theme);
        });

        const holoStyle = computed(() => {
            if (!p.value) return {};
            const id = p.value.id || 0;

            if (props.isCapturing) {
                // Fixed "perfect" light for capture
                return {
                    '--lp': '50%', '--tp': '50%', '--spx': '50%', '--spy': '50%',
                    '--opc': '0.4', '--int': '1.2', '--delay': '0s', '--duration': '10s'
                };
            }

            // Stable "random" values based on ID
            const delay = (id % 12) * -1.5; // -0s to -16.5s
            const duration = 10 + (id % 6);  // 10s to 15s
            const intensity = 0.7 + (id % 4) * 0.15; // 0.7 to 1.15 multiplier
            
            return {
                '--delay': `${delay}s`,
                '--duration': `${duration}s`,
                '--int': intensity,
                '--lp': `${tilt.lp}%`, 
                '--tp': `${tilt.tp}%`,
                '--spx': `${tilt.spx}%`,
                '--spy': `${tilt.spy}%`,
                '--opc': tilt.opc
            };
        });

        return { cardContainer, p, themeStyle, getLevelTag, tilt, handleMove, handleLeave, isHoloTheme, isAnimated, holoStyle };
    }
};

const PlayerDetailModal = {
    props: ['player', 'stats', 'players', 'isLoggedIn', 'showToast', 'navigateTo'],
    components: { AppIcon, PlayerCard },
    template: '#player-detail-modal-template',
    emits: ['close', 'open-match', 'update:player', 'open-profile', 'open-ntrp-guide', 'share'],
    setup(props, { emit }) {
        const currentIndex = computed(() => {
            if (!props.player || !props.players) return -1;
            return props.players.findIndex(p => p.id === props.player.id);
        });

        const hasPrev = computed(() => props.players && props.players.length > 1);
        const hasNext = computed(() => props.players && props.players.length > 1);
        const transitionName = ref('slide-next');
        const comments = ref([]);
        const commentDraft = ref('');
        const isLoadingComments = ref(false);
        const socialStatus = reactive({ is_liked: false, is_following: false, likes_count: 0 });

        const loadComments = async () => {
            if (!props.player) return;
            isLoadingComments.value = true;
            try {
                const response = await api.get(`/players/${props.player.id}/comments`);
                comments.value = response.data;
            } catch (error) {}
            finally { isLoadingComments.value = false; }
        };

        const toggleFollowModal = async () => {
            if (!props.isLoggedIn) { props.showToast('請先登入', 'error'); props.navigateTo('auth'); return; }
            const uid = props.player.user?.uid || props.player.user_id;
            try {
                const action = socialStatus.is_following ? 'unfollow' : 'follow';
                const response = await api.post(`/${action}/${uid}`);
                socialStatus.is_following = !socialStatus.is_following;
                emit('update:player', { ...props.player, is_following: socialStatus.is_following });
                props.showToast(response.data.message, 'success');
            } catch (error) {
                const msg = error.response?.data?.error || error.response?.data?.message || '操作失敗';
                props.showToast(msg, 'error');
            }
        };

        const toggleLikeModal = async () => {
            if (!props.isLoggedIn) { props.showToast('請先登入', 'error'); props.navigateTo('auth'); return; }
            try {
                const action = socialStatus.is_liked ? 'unlike' : 'like';
                const response = await api.post(`/${action}/${props.player.id}`);
                socialStatus.is_liked = !socialStatus.is_liked;
                socialStatus.likes_count = response.data.likes_count;
                emit('update:player', { ...props.player, is_liked: socialStatus.is_liked, likes_count: socialStatus.likes_count });
                props.showToast(response.data.message, 'success');
            } catch (error) {
                const msg = error.response?.data?.error || error.response?.data?.message || '操作失敗';
                props.showToast(msg, 'error');
            }
        };

        const postComment = async () => {
            if (!props.isLoggedIn) { props.showToast('請先登入', 'error'); props.navigateTo('auth'); return; }
            const text = commentDraft.value.trim();
            if (!text) return;
            try {
                const response = await api.post(`/players/${props.player.id}/comments`, { content: text });
                comments.value.unshift(response.data.comment);
                commentDraft.value = '';
                emit('update:player', { ...props.player, comments_count: (props.player.comments_count || 0) + 1 });
                props.showToast('留言成功', 'success');
            } catch (error) {
                props.showToast('發送失敗', 'error');
            }
        };

        watch(() => props.player, (newP) => {
            if (newP) {
                socialStatus.is_liked = newP.is_liked || false;
                socialStatus.is_following = newP.is_following || false;
                socialStatus.likes_count = newP.likes_count || 0;
                loadComments();
            }
        }, { immediate: true });

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

        const formatDate = (dateStr) => {
            if (!dateStr) return '';
            const d = new Date(dateStr);
            return `${d.getMonth() + 1}/${d.getDate()} ${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`;
        };

        const getLevelDesc = (lvl) => LEVEL_DESCS[lvl] || '享受網球樂趣，持續精進球技。';

        return { 
            currentIndex, hasPrev, hasNext, transitionName, navigate, 
            handleTouchStart, handleTouchEnd, backStats, getThemeStyle, formatDate, getLevelDesc,
            comments, commentDraft, isLoadingComments, socialStatus,
            toggleFollowModal, toggleLikeModal, postComment
        };
    }
};

const MessageDetailModal = {
    props: ['open', 'targetUser', 'currentUser'],
    components: { AppIcon },
    template: '#message-detail-modal-template',
    emits: ['update:open', 'message-sent', 'navigate-to-profile'],
    setup(props, { emit }) {
        const messages = ref([]);
        const loading = ref(false);
        const sending = ref(false);
        const newMessage = ref('');
        const chatContainer = ref(null);

        // 導航到用戶個人頁面
        const goToProfile = () => {
            if (props.targetUser?.uid) {
                emit('update:open', false);
                emit('navigate-to-profile', props.targetUser.uid);
            }
        };

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

        const isFetching = ref(false);
        const loadChat = async (isPolling = false) => {
            if (!props.targetUser || isFetching.value) return;
            if (!isPolling) loading.value = true;
            isFetching.value = true;
            
            try {
                let url = `/messages/chat/${props.targetUser.uid}`;
                const maxId = messages.value.reduce((max, m) => Math.max(max, m.id), 0);
                
                if (isPolling && maxId > 0) {
                    url += `?after_id=${maxId}`;
                } else {
                    url += `?page=${page.value}`;
                }

                const response = await api.get(url);
                if (response.data.success) {
                    let newItems = [];
                    
                    if (isPolling) {
                        // Polling returns array directly
                        newItems = response.data.data.map(m => ({
                            ...m,
                            is_me: m.sender?.uid === props.currentUser.uid
                        }));
                        
                        if (newItems.length > 0) {
                            // Deduplicate by ID
                            const existingIds = new Set(messages.value.map(m => m.id));
                            const uniqueNewItems = newItems.filter(m => !existingIds.has(m.id));
                            
                            if (uniqueNewItems.length > 0) {
                                messages.value = [...messages.value, ...uniqueNewItems];
                                scrollToBottom();
                            }
                        }
                    } else {
                        // Pagination returns paginated object
                        const data = response.data.data;
                        const rawMessages = data.data || [];
                        hasMore.value = data.next_page_url !== null;
                        
                        newItems = rawMessages.map(m => ({
                            ...m,
                            is_me: m.sender?.uid === props.currentUser.uid
                        })).reverse(); // Reverse because backend gives desc
                        
                        if (page.value === 1) {
                            messages.value = newItems;
                            scrollToBottom();
                        } else {
                            // Prepend for load more, but also deduplicate just in case
                            const existingIds = new Set(messages.value.map(m => m.id));
                            const uniqueNewItems = newItems.filter(m => !existingIds.has(m.id));
                            
                            if (uniqueNewItems.length > 0) {
                                const currentHeight = chatContainer.value.scrollHeight;
                                messages.value = [...uniqueNewItems, ...messages.value];
                                nextTick(() => {
                                    chatContainer.value.scrollTop = chatContainer.value.scrollHeight - currentHeight;
                                });
                            }
                        }
                    }
                }
            } catch (error) {
                console.error('Load chat error:', error);
            } finally {
                isFetching.value = false;
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
                const payload = {
                    content: newMessage.value
                };
                
                // Use both id and uid if available for maximum resilience
                if (props.targetUser.id) payload.to_user_id = props.targetUser.id;
                if (props.targetUser.uid) payload.to_user_uid = props.targetUser.uid;
                
                // If this is a conversation about a specific player, include it
                if (props.targetUser.player?.id) {
                    payload.to_player_id = props.targetUser.player.id;
                }

                const response = await api.post('/messages', payload);

                if (response.data.success) {
                    const msg = response.data.data;
                    messages.value.push({
                        ...msg,
                        is_me: true
                    });
                    newMessage.value = '';
                    // Reset textarea height
                    nextTick(() => {
                        const textarea = document.querySelector('.message-textarea');
                        if (textarea) textarea.style.height = '42px';
                    });
                    scrollToBottom();
                    // Emit but specify this is a chat-reply to avoid closing modal
                    emit('message-sent', { type: 'chat-reply' });
                }
            } catch (error) {
                console.error('Send message error:', error);
                alert('發送失敗，請稍後再試');
            } finally {
                sending.value = false;
            }
        };

        // 處理 Enter 鍵：手機版換行，桌面版發送（Shift+Enter 換行）
        const handleEnterKey = (e) => {
            const isMobile = window.matchMedia('(max-width: 640px)').matches || 
                            ('ontouchstart' in window) || 
                            (navigator.maxTouchPoints > 0);
            
            if (isMobile) {
                // 手機版：Enter 換行，讓預設行為發生（不做任何事）
                return;
            } else {
                // 桌面版：Enter 發送，Shift+Enter 換行
                if (!e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            }
        };

        let pollInterval;

        // 處理手機返回鍵
        const handlePopState = (e) => {
            if (props.open) {
                emit('update:open', false);
            }
        };

        watch(() => props.open, (newVal) => {
            if (newVal) {
                document.body.style.overflow = 'hidden';
                page.value = 1;
                loadChat(false);
                pollInterval = setInterval(() => loadChat(true), 5000);
                
                // 加入 history state 以支援手機返回鍵
                history.pushState({ modal: 'message-detail' }, '');
                window.addEventListener('popstate', handlePopState);
            } else {
                document.body.style.overflow = '';
                messages.value = [];
                page.value = 1;
                if (pollInterval) clearInterval(pollInterval);
                
                // 移除返回鍵監聽
                window.removeEventListener('popstate', handlePopState);
            }
        });

        onUnmounted(() => {
            document.body.style.overflow = '';
            if (pollInterval) clearInterval(pollInterval);
            window.removeEventListener('popstate', handlePopState);
        });

        return { messages, loading, sending, newMessage, chatContainer, formatDate, sendMessage, handleEnterKey, hasMore, loadMore, goToProfile };
    }
};

const ShareModal = {
    props: ['modelValue', 'player'],
    components: { AppIcon, PlayerCard },
    template: '#share-modal-template',
    emits: ['update:modelValue'],
    setup(props, { emit }) {
        const { showToast } = useUtils();
        const isCapturing = ref(false);
        
        const shareUrl = computed(() => {
            if (!props.player) return window.location.origin;
            const uid = props.player.user_uid || props.player.user?.uid || props.player.user_id;
            return `${window.location.origin}/profile/${uid}`;
        });

        const copyLink = () => {
            navigator.clipboard.writeText(shareUrl.value);
            showToast('連結已複製到剪貼簿', 'success');
        };

        const captureCardImage = async () => {
            isCapturing.value = true;
            await nextTick();
            // Wait for Vue to update and styles to apply (increased for safety)
            await new Promise(resolve => setTimeout(resolve, 800));

            let container = null;
            try {
                // 1. Find the original card element
                const originalCard = document.querySelector('.modal-content .capture-target');
                if (!originalCard) throw new Error('找不到卡片元素');

                // 2. Create a hidden container with standard card width (450px)
                container = document.createElement('div');
                container.id = 'capture-temp-container';
                container.style.position = 'fixed';
                container.style.left = '-9999px';
                container.style.top = '0';
                container.style.width = '450px';
                container.style.height = '684px';
                container.style.containerType = 'inline-size';
                container.style.backgroundColor = 'transparent';
                document.body.appendChild(container);

                // 3. Clone the card into the container
                const clonedCard = originalCard.cloneNode(true);
                clonedCard.style.width = '450px';
                clonedCard.style.height = '684px';
                clonedCard.style.transform = 'none';
                clonedCard.style.margin = '0';
                clonedCard.style.padding = '0';
                container.appendChild(clonedCard);

                // 4. Capture using dom-to-image-more
                // We use a higher scale via style if needed, but dom-to-image-more 
                // usually produces good quality. For even higher quality, we can scale the container.
                const dataUrl = await domtoimage.toPng(container, {
                    width: 450,
                    height: 684,
                    style: {
                        transform: 'none',
                        left: '0',
                        top: '0',
                        visibility: 'visible'
                    },
                    cacheBust: true,
                    imagePlaceholder: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg=='
                });

                return dataUrl;
            } catch (error) {
                console.error('Capture error:', error);
                showToast('圖片生成失敗，請稍後再試', 'error');
                return null;
            } finally {
                if (container && container.parentNode) {
                    document.body.removeChild(container);
                }
                isCapturing.value = false;
            }
        };

        const downloadCard = async () => {
            const dataUrl = await captureCardImage();
            if (!dataUrl) return;

            try {
                // Convert dataUrl to Blob and File for sharing
                const res = await fetch(dataUrl);
                const blob = await res.blob();
                const fileName = `player-card-${props.player.name || 'tennis'}.png`;
                const file = new File([blob], fileName, { type: 'image/png' });

                // 1. Try Web Share API (Best for Mobile)
                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                    try {
                        await navigator.share({
                            files: [file],
                            title: '我的球員卡',
                            text: '🎾 這是我的網球球員卡！'
                        });
                        return; // Success
                    } catch (shareErr) {
                        if (shareErr.name !== 'AbortError') {
                            console.error('Share failed:', shareErr);
                        } else {
                            return; // User cancelled share
                        }
                    }
                }

                // 2. Fallback for Mobile (especially iOS)
                const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) || 
                             (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
                const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

                if (isIOS || isMobile) {
                    // Open in new tab for manual save
                    const newTab = window.open();
                    if (newTab) {
                        newTab.document.write(`
                            <html>
                                <head><title>儲存球員卡</title></head>
                                <body style="margin:0; display:flex; flex-direction:column; align-items:center; justify-content:center; background:#f1f5f9; font-family:sans-serif;">
                                    <img src="${dataUrl}" style="max-width:90%; max-height:80vh; border-radius:20px; shadow:0 20px 25px -5px rgb(0 0 0 / 0.1);">
                                    <p style="margin-top:20px; font-weight:bold; color:#64748b;">請長按圖片並選擇「儲存圖片」</p>
                                    <button onclick="window.close()" style="margin-top:20px; padding:10px 20px; background:#2563eb; color:white; border:none; border-radius:10px; font-weight:bold;">關閉視窗</button>
                                </body>
                            </html>
                        `);
                        showToast('請長按圖片儲存', 'info');
                        return;
                    }
                }

                // 3. Standard Download for Desktop
                const link = document.createElement('a');
                link.download = fileName;
                link.href = dataUrl;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                showToast('圖片已開始下載', 'success');
            } catch (error) {
                console.error('Download error:', error);
                // Last resort fallback
                window.open(dataUrl, '_blank');
                showToast('請長按圖片儲存', 'info');
            }
        };

        const shareToLine = () => {
            const text = `🎾 來看我的網球球友卡！\n${shareUrl.value}`;
            window.open(`https://line.me/R/msg/text/?${encodeURIComponent(text)}`, '_blank');
        };

        const shareNative = async () => {
            if (navigator.share) {
                try {
                    await navigator.share({
                        title: 'LoveTennis 球友個人資料',
                        text: `🎾 這是 ${props.player.name} 的網球個人資料，快來跟我約打吧！`,
                        url: shareUrl.value
                    });
                } catch (err) {}
            } else {
                copyLink();
            }
        };

        const shareToInstagram = () => {
            copyLink();
            showToast('連結已複製，您可以開啟 IG 發布限時動態', 'info');
        };

        const shareToThreads = () => {
            copyLink();
            showToast('連結已複製，您可以開啟 Threads 發布貼文', 'info');
        };

        const shareToFacebook = () => {
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl.value)}`, '_blank');
        };

        const shareToX = () => {
            const text = `🎾 來看我的網球個人資料！`;
            window.open(`https://twitter.com/intent/tweet?url=${encodeURIComponent(shareUrl.value)}&text=${encodeURIComponent(text)}`, '_blank');
        };

        const shareToWhatsApp = () => {
            const text = `🎾 來看我的網球個人資料！\n${shareUrl.value}`;
            window.open(`https://api.whatsapp.com/send?text=${encodeURIComponent(text)}`, '_blank');
        };

        const shareToTelegram = () => {
            const text = `🎾 來看我的網球個人資料！`;
            window.open(`https://t.me/share/url?url=${encodeURIComponent(shareUrl.value)}&text=${encodeURIComponent(text)}`, '_blank');
        };

        return { 
            shareUrl, copyLink, shareToLine, shareNative, shareToInstagram, shareToThreads, 
            shareToFacebook, shareToX, shareToWhatsApp, shareToTelegram,
            isCapturing, downloadCard 
        };
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
    emits: ['update:open', 'save', 'trigger-upload'],
    setup(props) {
        const { getUrl } = useUtils();
        return { getUrl };
    }
};

const EventDetailModal = {
    props: ['open', 'event', 'likes', 'comments', 'commentDraft', 'currentUser'],
    components: { AppIcon },
    template: '#event-detail-modal-template',
    emits: ['update:open', 'like', 'join', 'comment', 'leave', 'update:comment-draft', 'delete-comment', 'open-profile'],
    setup(props, { emit }) {
        const { formatEventDate, formatDate } = useUtils();
        
        const openProfile = (uid) => emit('open-profile', uid);

        return { formatEventDate, formatDate, openProfile };
    }
};
const PrivacyModal = {
    props: ['modelValue', 'navigateTo'],
    components: { AppIcon },
    template: '#privacy-modal-template',
    emits: ['update:modelValue']
};
