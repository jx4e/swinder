const SWIPE_THRESHOLD = 90;

const card       = document.getElementById('card');
const cardImage  = document.getElementById('card-image');
const poolName   = document.getElementById('pool-name');
const poolAddr   = document.getElementById('pool-address');
const poolRating = document.getElementById('pool-rating');
const likeStamp  = document.getElementById('like-stamp');
const nopeStamp  = document.getElementById('nope-stamp');
const emptyState = document.getElementById('empty-state');
const buttons    = document.getElementById('buttons');

// Persist seen pool IDs across page refreshes so users don't see repeats
const seenIds = JSON.parse(localStorage.getItem('swinder_seen') || '[]');

let currentPool       = null;
let currentPhotoIndex = 0;
let isDragging        = false;
let startX            = 0;
let currentX          = 0;
let isSwiping         = false; // prevent double-swipe

// ── Load a pool ──────────────────────────────────────────────────────────────

async function loadNextPool() {
    const res  = await fetch('/api/next.php?seen=' + seenIds.join(','));
    const data = await res.json();

    if (!data.pool) {
        showEmpty();
        return;
    }

    currentPool       = data.pool;
    currentPhotoIndex = 0;

    poolName.textContent   = currentPool.name;
    poolAddr.textContent   = currentPool.address || '';
    poolRating.textContent = currentPool.rating ? '⭐ ' + currentPool.rating : '';

    setPhoto(0);
    renderDots();

    // Reset card
    card.style.transition = 'none';
    card.style.transform  = 'translateX(0) rotate(0deg)';
    likeStamp.style.opacity = '0';
    nopeStamp.style.opacity = '0';
    card.classList.remove('hidden');
    isSwiping = false;
}

function showEmpty() {
    card.classList.add('hidden');
    buttons.classList.add('hidden');
    emptyState.classList.remove('hidden');
}

// ── Photo cycling ────────────────────────────────────────────────────────────

function setPhoto(index) {
    const refs = currentPool?.photo_refs ?? [];
    if (refs.length > 0 && refs[index]) {
        cardImage.style.backgroundImage = `url('/api/photo.php?ref=${encodeURIComponent(refs[index])}')`;
    } else if (currentPool?.photo_url) {
        cardImage.style.backgroundImage = `url('${currentPool.photo_url}')`;
    } else {
        cardImage.style.backgroundImage = 'linear-gradient(135deg, #1a1a4e 0%, #2979ff 100%)';
    }
}

function renderDots() {
    const dotsEl = document.getElementById('photo-dots');
    const count  = currentPool?.photo_refs?.length ?? 0;
    if (count <= 1) { dotsEl.innerHTML = ''; return; }
    dotsEl.innerHTML = Array.from({ length: count }, (_, i) =>
        `<span class="dot ${i === currentPhotoIndex ? 'active' : ''}"></span>`
    ).join('');
}

function cyclePhoto(e) {
    if (isDragging || isSwiping) return;
    const refs = currentPool?.photo_refs ?? [];
    if (refs.length <= 1) return;
    currentPhotoIndex = (currentPhotoIndex + 1) % refs.length;
    setPhoto(currentPhotoIndex);
    renderDots();
}

cardImage.addEventListener('click', cyclePhoto);

// ── Record a swipe ───────────────────────────────────────────────────────────

async function recordSwipe(direction) {
    if (!currentPool) return;

    seenIds.push(currentPool.id);
    localStorage.setItem('swinder_seen', JSON.stringify(seenIds));

    fetch('/api/swipe.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ pool_id: currentPool.id, direction }),
    });

    await loadNextPool();
}

// ── Animate out, then record ─────────────────────────────────────────────────

function animateSwipe(direction) {
    if (isSwiping) return;
    isSwiping = true;

    const x      = direction === 'right' ? window.innerWidth * 1.5 : -window.innerWidth * 1.5;
    const rotate = direction === 'right' ? 30 : -30;

    card.style.transition = 'transform 0.38s ease';
    card.style.transform  = `translateX(${x}px) rotate(${rotate}deg)`;

    if (direction === 'right') {
        likeStamp.style.opacity = '1';
        nopeStamp.style.opacity = '0';
    } else {
        nopeStamp.style.opacity = '1';
        likeStamp.style.opacity = '0';
    }

    setTimeout(() => recordSwipe(direction), 380);
}

// Public helpers for the buttons
function swipeLeft()  { animateSwipe('left');  }
function swipeRight() { animateSwipe('right'); }

// ── Drag logic ───────────────────────────────────────────────────────────────

function onDragStart(x) {
    if (isSwiping) return;
    isDragging = true;
    startX     = x;
    card.style.transition = 'none';
}

function onDragMove(x) {
    if (!isDragging || isSwiping) return;
    currentX = x - startX;
    const rotate   = currentX * 0.07;
    const progress = Math.abs(currentX) / SWIPE_THRESHOLD;

    card.style.transform = `translateX(${currentX}px) rotate(${rotate}deg)`;

    if (currentX > 20) {
        likeStamp.style.opacity = Math.min(progress, 1);
        nopeStamp.style.opacity = '0';
    } else if (currentX < -20) {
        nopeStamp.style.opacity = Math.min(progress, 1);
        likeStamp.style.opacity = '0';
    } else {
        likeStamp.style.opacity = '0';
        nopeStamp.style.opacity = '0';
    }
}

function onDragEnd() {
    if (!isDragging) return;
    isDragging = false;

    if (currentX > SWIPE_THRESHOLD) {
        animateSwipe('right');
    } else if (currentX < -SWIPE_THRESHOLD) {
        animateSwipe('left');
    } else {
        card.style.transition = 'transform 0.3s ease';
        card.style.transform  = 'translateX(0) rotate(0deg)';
        likeStamp.style.opacity = '0';
        nopeStamp.style.opacity = '0';
    }
}

// Mouse
card.addEventListener('mousedown',  e => onDragStart(e.clientX));
document.addEventListener('mousemove', e => { if (isDragging) onDragMove(e.clientX); });
document.addEventListener('mouseup',   ()  => onDragEnd());

// Touch
card.addEventListener('touchstart', e => onDragStart(e.touches[0].clientX), { passive: true });
card.addEventListener('touchmove',  e => onDragMove(e.touches[0].clientX),  { passive: true });
card.addEventListener('touchend',   ()  => onDragEnd());

// ── Keyboard shortcuts (left/right arrows) ───────────────────────────────────
document.addEventListener('keydown', e => {
    if (e.key === 'ArrowRight') swipeRight();
    if (e.key === 'ArrowLeft')  swipeLeft();
});

// ── Kick things off ──────────────────────────────────────────────────────────
loadNextPool();
