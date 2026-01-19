// --- Vue Components ---
const AppIcon = {
  props: ['name', 'className', 'fill', 'stroke', 'strokeWidth'],
  template: '#app-icon-template',
  setup(props) {
    const iconPath = computed(() => SVG_ICONS[props.name] || '');
    return { iconPath };
  }
};

// Emoji Picker Component (defined early so other components can use it)
const EmojiPicker = {
    template: '#emoji-picker-template',
    emits: ['select'],
    data() {
        return {
            open: false,
            emojis: [
                '😀','😂','🥹','😍','🤩','😎','🤔','😅','😊','🥰',
                '😘','😜','🤗','🙌','👏','🎾','🏸','⚽','🏓','🎯',
                '💪','🔥','✨','❤️','💙','👍','👋','🙏','🎉','🤝',
                '😭','🥺','😤','😈','🤣','😇','🤭','😏','🙂','😉',
                '👀','💯','⭐','🌟','💥','💫','🎊','🏆','🥇','🏅'
            ]
        };
    },
    methods: {
        selectEmoji(emoji) {
            this.$emit('select', emoji);
            this.open = false;
        }
    },
    directives: {
        'click-outside': {
            mounted(el, binding) {
                el._clickOutside = (e) => {
                    if (!el.contains(e.target)) binding.value();
                };
                document.addEventListener('click', el._clickOutside);
            },
            unmounted(el) {
                document.removeEventListener('click', el._clickOutside);
            }
        }
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

        let savedScrollY = 0;
        watch(() => props.active, (val) => { 
            if (val) {
                savedScrollY = window.scrollY;
                document.body.style.overflow = 'hidden';
                document.body.style.position = 'fixed';
                document.body.style.width = '100%';
                document.body.style.top = `-${savedScrollY}px`;
                initCanvas(); 
            } else {
                document.body.style.overflow = '';
                document.body.style.position = '';
                document.body.style.width = '';
                document.body.style.top = '';
                window.scrollTo(0, savedScrollY);
            }
        });
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
        const isVisible = ref(false);
        let io = null;
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

        const photoUrl = computed(() => {
            return p.value?.photo || 'https://images.unsplash.com/photo-1614743758466-e569f4791116?q=80&w=650&auto=format&fit=crop';
        });
        const isPhotoLoaded = ref(false);
        let photoToken = 0;
        
        const themeStyle = computed(() => {
            if (!p.value) return themes.standard;
            return themes[p.value.theme || 'standard'] || themes.standard;
        });

        const displayRegion = computed(() => {
            const raw = props.player?.region || '';
            if (!raw) return '全台';
            const regions = raw.split(',').filter(x => x.trim());
            if (regions.length === 0) return '全台';
            if (regions.length <= 2) return regions.join(' ');
            return regions.slice(0, 2).join(' ') + ' +';
        });
        const getLevelTag = (lvl) => LEVEL_TAGS[lvl] || '網球愛好者';

        const handleMove = () => {};
        const handleLeave = () => {};
        const holoStyle = computed(() => ({}));

        const cardScale = ref(props.size === 'sm' ? 0 : 1);
        const containerHeight = ref(props.size === 'sm' ? 0 : 684);
        const isScaleReady = ref(props.size !== 'sm');
        let resizeObserver = null;
        let rafId = null;
        
        const updateScale = () => {
            if (rafId) return; // Throttle with RAF
            rafId = requestAnimationFrame(() => {
                if (cardContainer.value) {
                    const containerWidth = cardContainer.value.offsetWidth;
                    if (containerWidth > 0) {
                        cardScale.value = containerWidth / 450;
                        containerHeight.value = 684 * cardScale.value;
                        isScaleReady.value = true;
                    }
                }
                rafId = null;
            });
        };

        onMounted(() => {
            if ('IntersectionObserver' in window) {
                io = new IntersectionObserver((entries) => {
                    if (entries.some(entry => entry.isIntersecting)) {
                        isVisible.value = true;
                        if (io) io.disconnect();
                        io = null;
                    }
                }, { rootMargin: '200px 0px', threshold: 0.01 });
                if (cardContainer.value) io.observe(cardContainer.value);
            } else {
                isVisible.value = true;
            }

            // Skip ResizeObserver in lite mode for better performance (used in Swiper)
            if (props.size === 'sm') {
                // Just set initial scale once, no observers
                nextTick(() => updateScale());
                return;
            }
            
            // Delay ResizeObserver setup to prioritize first paint
            requestAnimationFrame(() => {
                updateScale();
                window.addEventListener('resize', updateScale);
                if (window.ResizeObserver && cardContainer.value) {
                    resizeObserver = new ResizeObserver(() => { updateScale(); });
                    resizeObserver.observe(cardContainer.value);
                }
            });
        });
        onUnmounted(() => {
            window.removeEventListener('resize', updateScale);
            if (resizeObserver) resizeObserver.disconnect();
            if (rafId) cancelAnimationFrame(rafId);
            if (io) io.disconnect();
        });

        watch([isVisible, photoUrl], ([visible, url]) => {
            if (!visible) return;
            const currentToken = ++photoToken;
            isPhotoLoaded.value = false;
            const img = new Image();
            img.decoding = 'async';
            img.onload = () => {
                if (currentToken === photoToken) isPhotoLoaded.value = true;
            };
            img.onerror = () => {
                if (currentToken === photoToken) isPhotoLoaded.value = true;
            };
            img.src = url;
        }, { immediate: true });

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

        return { cardContainer, p, themeStyle, displayRegion, getLevelTag, handleMove, handleLeave, holoStyle, cardScale, containerHeight, nameFontSize, isVisible, isPhotoLoaded, photoUrl, isScaleReady };
    }
};

const PlayerDetailModal = {
    props: ['player', 'stats', 'players', 'isLoggedIn', 'showToast', 'navigateTo', 'currentUser'],
    components: { AppIcon, PlayerCard, EmojiPicker },
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
        const isTransitioning = ref(false);  // 轉場動畫狀態
        const comments = ref([]);
        const commentDraft = ref('');
        const isLoadingComments = ref(false);
        const socialStatus = reactive({ is_liked: false, is_following: false, likes_count: 0 });
        const commentsCache = reactive(new Map());  // 留言快取
        const isSubmitting = ref(false);

        const myCommentId = ref(null);
        const existingRatedComment = ref(null);

        const checkMyComment = () => {
            myCommentId.value = null;
            existingRatedComment.value = null;
            if (!props.currentUser || !comments.value.length) return;
            
            // Find if user has a RATED comment
            const rated = comments.value.find(c => 
                (c.user_id === props.currentUser.id || (c.user && c.user.uid === props.currentUser.uid)) && 
                c.rating > 0
            );
            
            if (rated) {
                existingRatedComment.value = rated;
            }
        };

        const startEditRating = () => {
            if (existingRatedComment.value) {
                myCommentId.value = existingRatedComment.value.id;
                commentDraft.value = existingRatedComment.value.text || '';
                playerCommentRating.value = existingRatedComment.value.rating || 0;
            }
        };

        const cancelEdit = () => {
            myCommentId.value = null;
            commentDraft.value = '';
            playerCommentRating.value = 0;
        };

        const loadComments = async () => {
            if (!props.player) return;
            
            // 檢查快取
            const cached = commentsCache.get(props.player.id);
            if (cached) {
                comments.value = cached;
                checkMyComment();
                return;
            }
            
            isLoadingComments.value = true;
            try {
                const response = await api.get(`/players/${props.player.id}/comments`);
                comments.value = response.data;
                commentsCache.set(props.player.id, response.data);  // 存入快取
                checkMyComment();
            } catch (error) {}
            finally { isLoadingComments.value = false; }
        };

        const toggleFollowModal = async () => {
            if (!props.isLoggedIn) { props.showToast('請先登入', 'error'); props.navigateTo('auth'); return; }
            if (isSubmitting.value) return;
            
            const uid = props.player.user?.uid || props.player.user_id;
            
            // Optimistic Update
            const prevFollowing = socialStatus.is_following;
            socialStatus.is_following = !prevFollowing;
            emit('update:player', { ...props.player, is_following: socialStatus.is_following });

            isSubmitting.value = true;
            try {
                const action = prevFollowing ? 'unfollow' : 'follow';
                await api.post(`/${action}/${uid}`);
            } catch (error) {
                // Rollback on failure
                socialStatus.is_following = prevFollowing;
                emit('update:player', { ...props.player, is_following: prevFollowing });
                props.showToast(error.response?.data?.message || '操作失敗', 'error');
            } finally {
                isSubmitting.value = false;
            }
        };

        const toggleLikeModal = async () => {
            if (!props.isLoggedIn) { props.showToast('請先登入', 'error'); props.navigateTo('auth'); return; }
            if (isSubmitting.value) return;
            
            // Optimistic Update
            const prevLiked = socialStatus.is_liked;
            const prevLikesCount = socialStatus.likes_count;
            socialStatus.is_liked = !prevLiked;
            socialStatus.likes_count = prevLiked ? prevLikesCount - 1 : prevLikesCount + 1;
            emit('update:player', { 
                ...props.player, 
                is_liked: socialStatus.is_liked, 
                likes_count: socialStatus.likes_count 
            });

            isSubmitting.value = true;
            try {
                const action = prevLiked ? 'unlike' : 'like';
                const response = await api.post(`/${action}/${props.player.id}`);
                // Sync with server's final count just in case
                socialStatus.likes_count = response.data.likes_count;
                emit('update:player', { ...props.player, likes_count: socialStatus.likes_count });
            } catch (error) {
                // Rollback on failure
                socialStatus.is_liked = prevLiked;
                socialStatus.likes_count = prevLikesCount;
                emit('update:player', { 
                    ...props.player, 
                    is_liked: prevLiked, 
                    likes_count: prevLikesCount 
                });
                props.showToast(error.response?.data?.message || '操作失敗', 'error');
            } finally {
                isSubmitting.value = false;
            }
        };

        const playerCommentRating = ref(0);
        
        // Auto-switch to edit mode if user clicks stars and has existing rating
        watch(playerCommentRating, (val) => {
            if (val > 0 && existingRatedComment.value && !myCommentId.value) {
                startEditRating();
                // Ensure the rating they just clicked is preserved (startEditRating overwrites it with old rating)
                playerCommentRating.value = val;
            }
        });

        const postComment = async () => {
            if (!props.isLoggedIn) { props.showToast('請先登入', 'error'); props.navigateTo('auth'); return; }
            if (isSubmitting.value) return;
            const text = commentDraft.value.trim();
            // Allow rating only (no text) if rating > 0
            if (!text && playerCommentRating.value === 0) return;
            
            isSubmitting.value = true;
            try {
                let response;
                if (myCommentId.value) {
                     // Update existing
                     response = await api.put(`/players/comments/${myCommentId.value}`, { 
                        content: text,
                        rating: playerCommentRating.value > 0 ? playerCommentRating.value : null
                    });
                    
                    // Update local list
                    const idx = comments.value.findIndex(c => c.id === myCommentId.value);
                    if (idx !== -1) {
                        comments.value[idx] = response.data.comment;
                    }
                    props.showToast('評價已更新', 'success');
                } else {
                    // Create new
                    response = await api.post(`/players/${props.player.id}/comments`, { 
                        content: text,
                        rating: playerCommentRating.value > 0 ? playerCommentRating.value : null
                    });
                    comments.value.unshift(response.data.comment);
                    myCommentId.value = response.data.comment.id;
                    props.showToast('評價已送出', 'success');
                }

                commentsCache.set(props.player.id, [...comments.value]);  // 更新快取
                
                const updatedPlayer = { 
                    ...props.player, 
                    comments_count: (props.player.comments_count || 0) + (myCommentId.value ? 0 : 1) // Only increment if new
                };
                
                if (response.data.player_stats) {
                    updatedPlayer.average_rating = response.data.player_stats.average_rating;
                    updatedPlayer.ratings_count = response.data.player_stats.ratings_count;
                }
                
                emit('update:player', updatedPlayer);
            } catch (error) { 
                if (error.response?.status === 409) {
                    props.showToast('您已經評價過此球友', 'error');
                } else if (error.response?.status === 403) {
                    props.showToast('不能評價自己', 'error');
                } else {
                    props.showToast('發送失敗', 'error'); 
                }
            }
            finally { isSubmitting.value = false; }
        };

        const deleteComment = async (commentId) => {
            if (!props.isLoggedIn) { props.showToast('請先登入', 'error'); props.navigateTo('auth'); return; }
            if (isSubmitting.value) return;
            if (!confirm('確定要刪除這則留言嗎？')) return;
            
            isSubmitting.value = true;
            try {
                const response = await api.delete(`/players/comments/${commentId}`);
                comments.value = comments.value.filter(c => c.id !== commentId);
                commentsCache.set(props.player.id, [...comments.value]);
                
                if (commentId === myCommentId.value) {
                    myCommentId.value = null;
                    commentDraft.value = '';
                    playerCommentRating.value = 0;
                }

                const updatedPlayer = { 
                    ...props.player, 
                    comments_count: Math.max(0, (props.player.comments_count || 0) - 1) 
                };

                if (response.data.player_stats) {
                    updatedPlayer.average_rating = response.data.player_stats.average_rating;
                    updatedPlayer.ratings_count = response.data.player_stats.ratings_count;
                }

                emit('update:player', updatedPlayer);
                props.showToast('留言已刪除', 'success');
            } catch (error) { props.showToast('刪除失敗', 'error'); }
            finally { isSubmitting.value = false; }
        };

        let savedScrollY = 0;
        watch(() => props.player, (newP) => {
            if (newP) {
                // Scroll lock
                savedScrollY = window.scrollY;
                document.body.style.overflow = 'hidden';
                document.body.style.position = 'fixed';
                document.body.style.width = '100%';
                document.body.style.top = `-${savedScrollY}px`;

                // 更新社交狀態
                socialStatus.is_liked = newP.is_liked || false;
                socialStatus.is_following = newP.is_following || false;
                socialStatus.likes_count = newP.likes_count || 0;
                
                // 重置狀態
                myCommentId.value = null;
                commentDraft.value = '';
                playerCommentRating.value = 0;

                // 檢查快取，如果有就直接用
                const cached = commentsCache.get(newP.id);
                if (cached) {
                    comments.value = cached;
                    checkMyComment();
                } else {
                    comments.value = [];
                    // 延遲載入留言，讓 UI 先渲染
                    requestAnimationFrame(() => {
                        setTimeout(() => loadComments(), 50);
                    });
                }
            } else {
                // Restore scroll
                document.body.style.overflow = '';
                document.body.style.position = '';
                document.body.style.width = '';
                document.body.style.top = '';
                window.scrollTo(0, savedScrollY);
            }
        }, { immediate: true });

        const navigate = (direction) => {
            if (!props.players || props.players.length <= 1 || isTransitioning.value) return;
            
            // 輕量淡出淡入轉場
            isTransitioning.value = true;
            transitionName.value = direction > 0 ? 'slide-next' : 'slide-prev';
            
            setTimeout(() => {
                let nextIndex = currentIndex.value + direction;
                if (nextIndex < 0) nextIndex = props.players.length - 1;
                if (nextIndex >= props.players.length) nextIndex = 0;
                emit('update:player', props.players[nextIndex]);
                
                setTimeout(() => {
                    isTransitioning.value = false;
                }, 150);
            }, 100);
        };

        const handleKeydown = (e) => {
            if (!props.player) return;
            if (e.key === 'ArrowLeft') navigate(-1);
            if (e.key === 'ArrowRight') navigate(1);
            if (e.key === 'Escape') emit('close');
        };

        const touchStartX = ref(0);
        const touchStartY = ref(0);
        const handleTouchStart = (e) => { 
            touchStartX.value = e.touches[0].clientX; 
            touchStartY.value = e.touches[0].clientY;
        };
        const handleTouchEnd = (e) => {
            // 手機版禁用左右滑動切換
            if (window.innerWidth < 640) return;
            
            const touchEndX = e.changedTouches[0].clientX;
            const touchEndY = e.changedTouches[0].clientY;
            const diffX = touchStartX.value - touchEndX;
            const diffY = touchStartY.value - touchEndY;
            
            // 只在明確的水平滑動時切換（水平位移 > 垂直位移，且水平位移 > 100px）
            // 這樣用戶在垂直滾動留言時不會誤觸發換頁
            if (Math.abs(diffX) > 100 && Math.abs(diffX) > Math.abs(diffY) * 2) {
                if (diffX > 0) navigate(1);
                else navigate(-1);
            }
        };

        // Lock body scroll when modal is open to prevent outer scroll interference
        watch(() => props.player, (newPlayer) => {
            if (newPlayer) {
                document.body.style.overflow = 'hidden';
                document.body.style.touchAction = 'none';
            } else {
                document.body.style.overflow = '';
                document.body.style.touchAction = '';
            }
        }, { immediate: true });

        onMounted(() => {
            window.addEventListener('keydown', handleKeydown);
        });
        onUnmounted(() => {
            window.removeEventListener('keydown', handleKeydown);
            // Ensure scroll is restored when component is destroyed
            document.body.style.overflow = '';
            document.body.style.touchAction = '';
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

        const formatDate = (dateStr) => {
            if (!dateStr) return '';
            const d = new Date(dateStr);
            return `${d.getMonth() + 1}/${d.getDate()} ${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`;
        };

        return { currentIndex, hasPrev, hasNext, transitionName, isTransitioning, navigate, handleTouchStart, handleTouchEnd, backStats, formatDate, comments, commentDraft, isLoadingComments, socialStatus, toggleFollowModal, toggleLikeModal, postComment, deleteComment, isSubmitting, playerCommentRating, myCommentId, existingRatedComment, startEditRating, cancelEdit };
    }
};

const MessageDetailModal = {
    props: ['open', 'targetUser', 'currentUser'],
    components: { AppIcon, EmojiPicker },
    template: '#message-detail-modal-template',
    emits: ['update:open', 'message-sent', 'navigate-to-profile'],
    setup(props, { emit }) {
        const messages = ref([]); const loading = ref(false); const sending = ref(false); const newMessage = ref(''); const chatContainer = ref(null);
        const hasMore = ref(false); const page = ref(1); const isFetching = ref(false);

        const goToProfile = () => { if (props.targetUser?.uid) { emit('update:open', false); emit('navigate-to-profile', props.targetUser.uid); } };
        const formatDate = (dateString) => { if (!dateString) return ''; const date = new Date(dateString); return date.toLocaleDateString('zh-TW', { month: 'numeric', day: 'numeric', hour: '2-digit', minute: '2-digit' }); };
        const scrollToBottom = () => {
            const doScroll = () => { if (chatContainer.value) chatContainer.value.scrollTop = chatContainer.value.scrollHeight; };
            nextTick(() => {
                doScroll();
                setTimeout(doScroll, 50);
                setTimeout(doScroll, 150);
                setTimeout(doScroll, 300);
            });
        };

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
                            const existingIds = new Set(messages.value.map(m => String(m.id)));
                            const uniqueNewItems = newItems.filter(m => !existingIds.has(String(m.id)));
                            if (uniqueNewItems.length > 0) { messages.value = [...messages.value, ...uniqueNewItems]; scrollToBottom(); }
                        }
                    } else {
                        const data = response.data.data; const rawMessages = data.data || []; hasMore.value = data.next_page_url !== null;
                        newItems = rawMessages.map(m => ({ ...m, is_me: m.sender?.uid === props.currentUser.uid })).reverse();
                        if (page.value === 1) { messages.value = newItems; scrollToBottom(); }
                        else {
                            const existingIds = new Set(messages.value.map(m => String(m.id)));
                            const uniqueNewItems = newItems.filter(m => !existingIds.has(String(m.id)));
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
        let savedScrollY = 0;
        watch(() => props.open, async (newVal) => {
            if (newVal) {
                // Full scroll lock for mobile
                savedScrollY = window.scrollY;
                document.body.style.overflow = 'hidden';
                document.body.style.position = 'fixed';
                document.body.style.width = '100%';
                document.body.style.top = `-${savedScrollY}px`;
                
                page.value = 1;
                await loadChat(false);
                scrollToBottom();
                pollInterval = setInterval(() => loadChat(true), 5000);
            } else {
                // Restore scroll
                document.body.style.overflow = '';
                document.body.style.position = '';
                document.body.style.width = '';
                document.body.style.top = '';
                window.scrollTo(0, savedScrollY);
                
                messages.value = [];
                if (pollInterval) clearInterval(pollInterval);
            }
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
        let savedScrollY = 0;
        watch(() => props.modelValue, (newVal) => {
            if (newVal) {
                savedScrollY = window.scrollY;
                document.body.style.overflow = 'hidden';
                document.body.style.position = 'fixed';
                document.body.style.width = '100%';
                document.body.style.top = `-${savedScrollY}px`;
            } else {
                document.body.style.overflow = '';
                document.body.style.position = '';
                document.body.style.width = '';
                document.body.style.top = '';
                window.scrollTo(0, savedScrollY);
            }
        });
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
        let savedScrollY = 0;
        watch(() => props.open, (newVal) => {
            if (newVal) {
                savedScrollY = window.scrollY;
                document.body.style.overflow = 'hidden';
                document.body.style.position = 'fixed';
                document.body.style.width = '100%';
                document.body.style.top = `-${savedScrollY}px`;
            } else {
                document.body.style.overflow = '';
                document.body.style.position = '';
                document.body.style.width = '';
                document.body.style.top = '';
                window.scrollTo(0, savedScrollY);
            }
        });
        return { textModel, photoUrl };
    }
};

const NtrpGuideModal = { 
    props: ['open', 'descs'], 
    components: { AppIcon }, 
    template: '#ntrp-guide-modal-template', 
    emits: ['update:open'],
    setup(props) {
        let savedScrollY = 0;
        watch(() => props.open, (newVal) => {
            if (newVal) {
                savedScrollY = window.scrollY;
                document.body.style.overflow = 'hidden';
                document.body.style.position = 'fixed';
                document.body.style.width = '100%';
                document.body.style.top = `-${savedScrollY}px`;
            } else {
                document.body.style.overflow = '';
                document.body.style.position = '';
                document.body.style.width = '';
                document.body.style.top = '';
                window.scrollTo(0, savedScrollY);
            }
        });
        return {};
    }
};

const QuickEditModal = {
    props: ['open', 'form', 'levels', 'regions'],
    components: { AppIcon },
    template: '#quick-edit-modal-template',
    emits: ['update:open', 'save', 'trigger-upload'],
    setup(props, { emit }) {
        // 多地區選擇
        const selectedRegions = ref([]);
        
        // 監聽 form.region 初始化已選地區
        watch(() => props.form.region, (newVal) => {
            if (newVal) {
                selectedRegions.value = newVal.split(',').filter(r => r.trim());
            } else {
                selectedRegions.value = [];
            }
        }, { immediate: true });
        
        // 切換地區選擇
        const toggleRegion = (region) => {
            const idx = selectedRegions.value.indexOf(region);
            if (idx > -1) {
                selectedRegions.value.splice(idx, 1);
            } else {
                selectedRegions.value.push(region);
            }
            // 同步到 form.region（逗點分隔）
            props.form.region = selectedRegions.value.join(',');
        };

        let savedScrollY = 0;
        watch(() => props.open, (newVal) => {
            if (newVal) {
                savedScrollY = window.scrollY;
                document.body.style.overflow = 'hidden';
                document.body.style.position = 'fixed';
                document.body.style.width = '100%';
                document.body.style.top = `-${savedScrollY}px`;
            } else {
                document.body.style.overflow = '';
                document.body.style.position = '';
                document.body.style.width = '';
                document.body.style.top = '';
                window.scrollTo(0, savedScrollY);
            }
        });
        
        return { selectedRegions, toggleRegion };
    }
};

const EventDetailModal = {
    props: ['open', 'event', 'likes', 'comments', 'commentDraft', 'currentUser', 'isSubmitting'],
    components: { AppIcon, EmojiPicker },
    template: '#event-detail-modal-template',
    emits: ['update:open', 'like', 'join', 'comment', 'leave', 'update:comment-draft', 'delete-comment', 'open-profile'],
    setup(props, { emit }) {
        const { formatEventDate, formatDate } = useUtils();
        let savedScrollY = 0;
        watch(() => props.open, (newVal) => {
            if (newVal) {
                savedScrollY = window.scrollY;
                document.body.style.overflow = 'hidden';
                document.body.style.position = 'fixed';
                document.body.style.width = '100%';
                document.body.style.top = `-${savedScrollY}px`;
            } else {
                document.body.style.overflow = '';
                document.body.style.position = '';
                document.body.style.width = '';
                document.body.style.top = '';
                window.scrollTo(0, savedScrollY);
            }
        });
        return { formatEventDate, formatDate, openProfile: (uid) => emit('open-profile', uid), isSubmitting: computed(() => props.isSubmitting) };
    }
};

const PrivacyModal = { 
    props: ['modelValue', 'navigateTo'], 
    components: { AppIcon }, 
    template: '#privacy-modal-template', 
    emits: ['update:modelValue'],
    setup(props) {
        let savedScrollY = 0;
        watch(() => props.modelValue, (newVal) => {
            if (newVal) {
                savedScrollY = window.scrollY;
                document.body.style.overflow = 'hidden';
                document.body.style.position = 'fixed';
                document.body.style.width = '100%';
                document.body.style.top = `-${savedScrollY}px`;
            } else {
                document.body.style.overflow = '';
                document.body.style.position = '';
                document.body.style.width = '';
                document.body.style.top = '';
                window.scrollTo(0, savedScrollY);
            }
        });
        return {};
    }
};
