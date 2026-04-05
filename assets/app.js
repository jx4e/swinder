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
    const lat    = localStorage.getItem('swinder_lat')    || 49.2827;
    const lon    = localStorage.getItem('swinder_lon')    || -123.1207;
    const radius = localStorage.getItem('swinder_radius') || 10;
    const params = new URLSearchParams({ seen: seenIds.join(','), lat, lon, radius });
    const res    = await fetch('/api/next.php?' + params);
    const data   = await res.json();

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

// ── Location ─────────────────────────────────────────────────────────────────

const locationLabel  = document.getElementById('location-label');
const locationStatus = document.getElementById('location-status');
const cityInput      = document.getElementById('city-input');
const radiusInput    = document.getElementById('radius-input');
const radiusLabel    = document.getElementById('radius-label');

radiusInput.addEventListener('input', () => {
    radiusLabel.textContent = radiusInput.value + ' km';
    localStorage.setItem('swinder_radius', radiusInput.value);
});

// Restore saved radius
const savedRadius = localStorage.getItem('swinder_radius');
if (savedRadius) { radiusInput.value = savedRadius; radiusLabel.textContent = savedRadius + ' km'; }

function openLocationModal() {
    document.getElementById('location-modal').classList.remove('hidden');
    cityInput.value = '';
    locationStatus.textContent = '';
    cityInput.focus();
}

function closeLocationModal(e) {
    if (e && e.target !== document.getElementById('location-modal')) return;
    document.getElementById('location-modal').classList.add('hidden');
}

async function applyLocation(lat, lon, name) {
    setLocationStatus('⏳ Fetching pools…');
    const radius = radiusInput.value || 10;
    try {
        const res  = await fetch(`/api/set_location.php?lat=${lat}&lon=${lon}&radius=${radius}`);
        const data = await res.json();
        // Save to localStorage and reset seen pools
        localStorage.setItem('swinder_lat',  lat);
        localStorage.setItem('swinder_lon',  lon);
        localStorage.setItem('swinder_location', name);
        localStorage.setItem('swinder_seen', '[]');
        seenIds.length = 0;
        locationLabel.textContent = name;
        document.getElementById('location-modal').classList.add('hidden');
        await loadNextPool();
    } catch {
        setLocationStatus('❌ Something went wrong, try again');
    }
}

function setLocationStatus(msg) {
    locationStatus.textContent = msg;
}

// Geocode a city name via Nominatim (free, no API key)
async function geocodeCity(query) {
    const url = 'https://nominatim.openstreetmap.org/search?' + new URLSearchParams({
        q: query, format: 'json', limit: 1,
    });
    const res  = await fetch(url, { headers: { 'Accept-Language': 'en' } });
    const data = await res.json();
    if (!data.length) return null;
    return {
        lat:  parseFloat(data[0].lat),
        lon:  parseFloat(data[0].lon),
        name: data[0].display_name.split(',').slice(0, 2).join(',').trim(),
    };
}

document.getElementById('btn-geolocate').addEventListener('click', () => {
    if (!navigator.geolocation) {
        setLocationStatus('❌ Geolocation not supported by your browser');
        return;
    }
    setLocationStatus('⏳ Getting your location…');
    navigator.geolocation.getCurrentPosition(
        async pos => {
            const { latitude: lat, longitude: lon } = pos.coords;
            // Reverse geocode to get a readable name
            const res  = await fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lon}&format=json`);
            const data = await res.json();
            const name = data.address?.city || data.address?.town || data.address?.county || 'Here';
            await applyLocation(lat, lon, name);
        },
        () => setLocationStatus('❌ Location access denied')
    );
});

document.getElementById('btn-city-search').addEventListener('click', searchCity);
cityInput.addEventListener('keydown', e => { if (e.key === 'Enter') searchCity(); });

async function searchCity() {
    const query = cityInput.value.trim();
    if (!query) return;
    setLocationStatus('⏳ Searching…');
    const result = await geocodeCity(query);
    if (!result) {
        setLocationStatus('❌ City not found, try again');
        return;
    }
    await applyLocation(result.lat, result.lon, result.name);
}

// Restore saved location label on load
const savedLocation = localStorage.getItem('swinder_location');
if (savedLocation) locationLabel.textContent = savedLocation;

// ── Kick things off ──────────────────────────────────────────────────────────
loadNextPool();
