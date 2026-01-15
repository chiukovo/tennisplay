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
            // Wait for teleport and transition (multiple frames to be safe)
            await nextTick();
            await new Promise(r => requestAnimationFrame(() => requestAnimationFrame(r)));
            
            if (canvas.value) {
                const width = canvas.value.offsetWidth;
                const height = canvas.value.offsetHeight;
                
                if (width <= 0 || height <= 0) {
                    console.warn('Canvas dimensions are 0, retrying init...');
                    setTimeout(initCanvas, 100);
                    return;
                }

                ctx = canvas.value.getContext('2d');
                const ratio = window.devicePixelRatio || 1;
                
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
            
            // Calculate position relative to canvas
            const rawX = clientX - rect.left;
            const rawY = clientY - rect.top;
            
            // Account for CSS transform scale by comparing actual vs rendered size
            const scaleX = canvas.value.offsetWidth / rect.width;
            const scaleY = canvas.value.offsetHeight / rect.height;
            
            return { 
                x: rawX * scaleX, 
                y: rawY * scaleY 
            };
        };
        const start = (e) => { if (!ctx) return; isDrawing = true; ctx.beginPath(); const p = getPos(e); ctx.moveTo(p.x, p.y); };
        const draw = (e) => { if (!isDrawing || !ctx) return; const p = getPos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); };
        const stop = () => { isDrawing = false; };
        const startTouch = (e) => { e.preventDefault(); start(e); };
        const moveTouch = (e) => { e.preventDefault(); draw(e); };
        const clear = () => { if (ctx) ctx.clearRect(0, 0, canvas.value.width, canvas.value.height); };

        const getTrimmedCanvas = (sourceCanvas) => {
            if (!sourceCanvas || sourceCanvas.width <= 0 || sourceCanvas.height <= 0) return null;
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
            gold: { border: 'bg-gradient-to-br from-amber-500 via-yellow-300 to-amber-600', accent: 'text-yellow-500', name: 'text-yellow-400', bg: 'bg-slate-900' },
            platinum: { border: 'bg-gradient-to-br from-slate-400 via-white to-slate-500', accent: 'text-blue-400', name: 'text-slate-100', bg: 'bg-slate-900' },
            holographic: { border: 'bg-gradient-to-br from-pink-500 via-cyan-400 via-yellow-300 to-purple-600', accent: 'text-cyan-400', name: 'text-holo-gradient', bg: 'bg-slate-900' },
            onyx: { border: 'bg-gradient-to-br from-slate-800 via-slate-600 to-slate-900', accent: 'text-slate-400', name: 'text-slate-300', bg: 'bg-black' },
            sakura: { border: 'bg-gradient-to-br from-pink-400 via-pink-200 to-pink-500', accent: 'text-pink-400', name: 'text-pink-200', bg: 'bg-slate-900' },
            standard: { border: 'bg-gradient-to-br from-blue-500 via-sky-400 to-indigo-600', accent: 'text-blue-500', name: 'text-white', bg: 'bg-slate-900' }
        };
        
        const p = computed(() => {
            const raw = props.player;
            if (!raw) return null;
            const getFullUrl = (path) => {
                if (!path) return null;
                if (path.startsWith('http') || path.startsWith('data:')) return path;
                return `/storage/${path}`;
            };
            return {
                ...raw,
                photo: getFullUrl(raw.photo_url || raw.photo),
                signature: getFullUrl(raw.signature_url || raw.signature),
                photoX: raw.photoX ?? raw.photo_x ?? 0,
                photoY: raw.photoY ?? raw.photo_y ?? 0,
                photoScale: raw.photoScale ?? raw.photo_scale ?? 1,
                sigX: raw.sigX ?? raw.sig_x ?? 50,
                sigY: raw.sigY ?? raw.sig_y ?? 50,
                sigScale: raw.sigScale ?? raw.sig_scale ?? 1,
                sigRotate: raw.sigRotate ?? raw.sig_rotate ?? 0,
                sigWidth: raw.sigWidth ?? raw.sig_width ?? 100,
            };
        });
        
        const themeStyle = computed(() => {
            if (!p.value) return themes.standard;
            return themes[p.value.theme || 'standard'] || themes.standard;
        });
        const getLevelTag = (lvl) => LEVEL_TAGS[lvl] || '網球愛好者';

        const handleMove = () => {};
        const handleLeave = () => {};
        const holoStyle = computed(() => ({}));

        const cardScale = ref(1);
        const containerHeight = ref(684);
        let resizeObserver = null;
        
        const updateScale = () => {
            if (cardContainer.value) {
                const containerWidth = cardContainer.value.offsetWidth;
                if (containerWidth > 0) {
                    cardScale.value = containerWidth / 450;
                    containerHeight.value = 684 * cardScale.value;
                }
            }
        };

        onMounted(() => {
            updateScale();
            window.addEventListener('resize', updateScale);
            if (window.ResizeObserver && cardContainer.value) {
                resizeObserver = new ResizeObserver(() => { updateScale(); });
                resizeObserver.observe(cardContainer.value);
            }
        });
        onUnmounted(() => {
            window.removeEventListener('resize', updateScale);
            if (resizeObserver) resizeObserver.disconnect();
        });

        const nameFontSize = computed(() => {
            const name = p.value?.name || '';
            if (!name) return '50px';
            
            // Calculate visual length (Chinese characters count as 2, others as 1)
            let visualLength = 0;
            for (let i = 0; i < name.length; i++) {
                visualLength += name.charCodeAt(i) > 255 ? 2 : 1;
            }

            // More relaxed thresholds
            if (visualLength <= 12) return '50px'; // Up to 6 Chinese chars
            if (visualLength <= 16) return '44px'; // Up to 8 Chinese chars
            if (visualLength <= 20) return '38px'; // Up to 10 Chinese chars
            if (visualLength <= 24) return '34px'; // Up to 12 Chinese chars
            return '30px';
        });

        return { cardContainer, p, themeStyle, getLevelTag, handleMove, handleLeave, holoStyle, cardScale, containerHeight, nameFontSize };
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
            } catch (error) { props.showToast(error.response?.data?.message || '操作失敗', 'error'); }
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
            } catch (error) { props.showToast(error.response?.data?.message || '操作失敗', 'error'); }
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
            } catch (error) { props.showToast('發送失敗', 'error'); }
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
            transitionName.value = direction > 0 ? 'slide-next' : 'slide-prev';
            let nextIndex = currentIndex.value + direction;
            if (nextIndex < 0) nextIndex = props.players.length - 1;
            if (nextIndex >= props.players.length) nextIndex = 0;
            emit('update:player', props.players[nextIndex]);
        };

        const handleKeydown = (e) => {
            if (!props.player) return;
            if (e.key === 'ArrowLeft') navigate(-1);
            if (e.key === 'ArrowRight') navigate(1);
            if (e.key === 'Escape') emit('close');
        };

        const touchStartX = ref(0);
        const handleTouchStart = (e) => { touchStartX.value = e.touches[0].clientX; };
        const handleTouchEnd = (e) => {
            const touchEndX = e.changedTouches[0].clientX;
            const diff = touchStartX.value - touchEndX;
            if (Math.abs(diff) > 50) {
                if (diff > 0) navigate(1);
                else navigate(-1);
            }
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

        const formatDate = (dateStr) => {
            if (!dateStr) return '';
            const d = new Date(dateStr);
            return `${d.getMonth() + 1}/${d.getDate()} ${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`;
        };

        return { currentIndex, hasPrev, hasNext, transitionName, navigate, handleTouchStart, handleTouchEnd, backStats, formatDate, comments, commentDraft, isLoadingComments, socialStatus, toggleFollowModal, toggleLikeModal, postComment };
    }
};

const MessageDetailModal = {
    props: ['open', 'targetUser', 'currentUser'],
    components: { AppIcon },
    template: '#message-detail-modal-template',
    emits: ['update:open', 'message-sent', 'navigate-to-profile'],
    setup(props, { emit }) {
        const messages = ref([]); const loading = ref(false); const sending = ref(false); const newMessage = ref(''); const chatContainer = ref(null);
        const hasMore = ref(false); const page = ref(1); const isFetching = ref(false);

        const goToProfile = () => { if (props.targetUser?.uid) { emit('update:open', false); emit('navigate-to-profile', props.targetUser.uid); } };
        const formatDate = (dateString) => { if (!dateString) return ''; const date = new Date(dateString); return date.toLocaleDateString('zh-TW', { month: 'numeric', day: 'numeric', hour: '2-digit', minute: '2-digit' }); };
        const scrollToBottom = () => { nextTick(() => { if (chatContainer.value) chatContainer.value.scrollTop = chatContainer.value.scrollHeight; }); };

        const loadChat = async (isPolling = false) => {
            if (!props.targetUser || isFetching.value) return;
            if (!isPolling) loading.value = true;
            isFetching.value = true;
            try {
                let url = `/messages/chat/${props.targetUser.uid}`;
                const maxId = messages.value.reduce((max, m) => Math.max(max, m.id), 0);
                if (isPolling && maxId > 0) url += `?after_id=${maxId}`;
                else url += `?page=${page.value}`;
                const response = await api.get(url);
                if (response.data.success) {
                    let newItems = [];
                    if (isPolling) {
                        newItems = response.data.data.map(m => ({ ...m, is_me: m.sender?.uid === props.currentUser.uid }));
                        if (newItems.length > 0) {
                            const existingIds = new Set(messages.value.map(m => m.id));
                            const uniqueNewItems = newItems.filter(m => !existingIds.has(m.id));
                            if (uniqueNewItems.length > 0) { messages.value = [...messages.value, ...uniqueNewItems]; scrollToBottom(); }
                        }
                    } else {
                        const data = response.data.data; const rawMessages = data.data || []; hasMore.value = data.next_page_url !== null;
                        newItems = rawMessages.map(m => ({ ...m, is_me: m.sender?.uid === props.currentUser.uid })).reverse();
                        if (page.value === 1) { messages.value = newItems; scrollToBottom(); }
                        else {
                            const existingIds = new Set(messages.value.map(m => m.id));
                            const uniqueNewItems = newItems.filter(m => !existingIds.has(m.id));
                            if (uniqueNewItems.length > 0) {
                                const currentHeight = chatContainer.value.scrollHeight;
                                messages.value = [...uniqueNewItems, ...messages.value];
                                nextTick(() => { chatContainer.value.scrollTop = chatContainer.value.scrollHeight - currentHeight; });
                            }
                        }
                    }
                }
            } catch (error) {} finally { isFetching.value = false; if (!isPolling) loading.value = false; }
        };

        const loadMore = () => { if (!hasMore.value || loading.value) return; page.value++; loadChat(false); };
        const sendMessage = async () => {
            if (!newMessage.value.trim() || sending.value) return;
            sending.value = true;
            try {
                const payload = { content: newMessage.value };
                if (props.targetUser.id) payload.to_user_id = props.targetUser.id;
                if (props.targetUser.uid) payload.to_user_uid = props.targetUser.uid;
                if (props.targetUser.player?.id) payload.to_player_id = props.targetUser.player.id;
                const response = await api.post('/messages', payload);
                if (response.data.success) {
                    messages.value.push({ ...response.data.data, is_me: true });
                    newMessage.value = ''; scrollToBottom();
                    emit('message-sent', { type: 'chat-reply' });
                }
            } catch (error) { alert('發送失敗'); } finally { sending.value = false; }
        };

        const handleEnterKey = (e) => { if (!e.shiftKey && !(/Android|iPhone/i.test(navigator.userAgent))) { e.preventDefault(); sendMessage(); } };
        let pollInterval;
        watch(() => props.open, (newVal) => {
            if (newVal) { document.body.style.overflow = 'hidden'; page.value = 1; loadChat(false); pollInterval = setInterval(() => loadChat(true), 5000); }
            else { document.body.style.overflow = ''; messages.value = []; if (pollInterval) clearInterval(pollInterval); }
        });
        onUnmounted(() => { if (pollInterval) clearInterval(pollInterval); });
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

        const copyToClipboard = async (text) => {
            if (navigator.clipboard && window.isSecureContext) {
                try { await navigator.clipboard.writeText(text); return true; } catch (err) {}
            }
            const textArea = document.createElement("textarea");
            textArea.value = text; textArea.style.position = "fixed"; textArea.style.left = "-999999px"; textArea.style.top = "-999999px";
            document.body.appendChild(textArea); textArea.focus(); textArea.select();
            try { document.execCommand('copy'); textArea.remove(); return true; } catch (err) { textArea.remove(); return false; }
        };

        const copyLink = async () => { 
            const success = await copyToClipboard(shareUrl.value); 
            if (success) showToast('連結已複製', 'success'); 
            else showToast('複製失敗，請手動選取連結', 'error');
        };

        const downloadCard = async () => {
            if (!props.player?.id) { showToast('無法取得球員資料', 'error'); return; }
            isCapturing.value = true;
            try {
                const response = await api.get(`/players/${props.player.id}/card-image`);
                if (!response.data.success) throw new Error(response.data.message || '圖片生成失敗');
                const dataUrl = response.data.image;
                const fileName = response.data.filename || `player-card-${props.player.name || 'tennis'}.png`;
                const isMobile = /Android|iPhone|iPad/i.test(navigator.userAgent);
                if (!isMobile) {
                    const link = document.createElement('a'); link.download = fileName; link.href = dataUrl;
                    document.body.appendChild(link); link.click(); document.body.removeChild(link);
                    showToast('圖片已開始下載', 'success'); return;
                }
                const res = await fetch(dataUrl); const blob = await res.blob();
                const file = new File([blob], fileName, { type: 'image/png' });
                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                    try { await navigator.share({ files: [file], title: '我的球員卡' }); return; } catch (e) {}
                }
                window.open(dataUrl, '_blank'); showToast('請長按圖片儲存', 'info');
            } catch (error) { showToast(error.response?.data?.message || '圖片生成失敗，請稍後再試', 'error'); } finally { isCapturing.value = false; }
        };

        const shareToLine = () => {
            const text = `🎾 來看我的網球球友卡！\n${shareUrl.value}`;
            window.open(`https://line.me/R/msg/text/?${encodeURIComponent(text)}`, '_blank');
        };

        const shareNative = async () => {
            if (navigator.share) {
                try { await navigator.share({ title: 'LoveTennis 球友個人資料', text: `🎾 這是 ${props.player.name} 的網球個人資料，快來跟我約打吧！`, url: shareUrl.value }); } catch (err) {}
            } else { copyLink(); }
        };

        const shareToInstagram = async () => {
            await copyToClipboard(shareUrl.value); showToast('連結已複製，請至 IG 發布限時動態', 'info');
            setTimeout(() => { window.location.href = "instagram://camera"; }, 1500);
        };

        const shareToThreads = () => {
            const text = `🎾 來看我的網球球友卡！`;
            const url = `https://www.threads.net/intent/post?text=${encodeURIComponent(text + '\n' + shareUrl.value)}`;
            window.open(url, '_blank');
        };

        const shareToFacebook = () => { window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl.value)}`, '_blank'); };
        const shareToX = () => { window.open(`https://twitter.com/intent/tweet?url=${encodeURIComponent(shareUrl.value)}&text=${encodeURIComponent('🎾 來看我的網球個人資料！')}`, '_blank'); };
        const shareToWhatsApp = () => { window.open(`https://api.whatsapp.com/send?text=${encodeURIComponent('🎾 來看我的網球個人資料！\n' + shareUrl.value)}`, '_blank'); };
        const shareToTelegram = () => { window.open(`https://t.me/share/url?url=${encodeURIComponent(shareUrl.value)}&text=${encodeURIComponent('🎾 來看我的網球個人資料！')}`, '_blank'); };

        return { shareUrl, copyLink, shareToLine, shareNative, shareToInstagram, shareToThreads, shareToFacebook, shareToX, shareToWhatsApp, shareToTelegram, isCapturing, downloadCard };
    }
};

const MatchModal = {
    props: ['open', 'player', 'isSending'],
    components: { AppIcon },
    template: '#match-modal-template',
    emits: ['update:open', 'submit'],
    setup(props) {
        const textModel = ref('');
        const photoUrl = computed(() => {
            const path = props.player?.photo_url || props.player?.photo;
            if (!path) return null;
            if (path.startsWith('http') || path.startsWith('data:')) return path;
            return `/storage/${path}`;
        });
        return { textModel, photoUrl };
    }
};

const NtrpGuideModal = { props: ['open', 'descs'], components: { AppIcon }, template: '#ntrp-guide-modal-template', emits: ['update:open'] };

const QuickEditModal = {
    props: ['open', 'form', 'levels', 'regions'],
    components: { AppIcon },
    template: '#quick-edit-modal-template',
    emits: ['update:open', 'save', 'trigger-upload']
};

const EventDetailModal = {
    props: ['open', 'event', 'likes', 'comments', 'commentDraft', 'currentUser'],
    components: { AppIcon },
    template: '#event-detail-modal-template',
    emits: ['update:open', 'like', 'join', 'comment', 'leave', 'update:comment-draft', 'delete-comment', 'open-profile'],
    setup(props, { emit }) {
        const { formatEventDate, formatDate } = useUtils();
        return { formatEventDate, formatDate, openProfile: (uid) => emit('open-profile', uid) };
    }
};

const PrivacyModal = { props: ['modelValue', 'navigateTo'], components: { AppIcon }, template: '#privacy-modal-template', emits: ['update:modelValue'] };
