{{--
    Brand video ads slider (videos listed in config/shop.php 'videos').
    One YouTube player sits over the slides and switches video when the slide changes; it plays
    muted (browsers only allow muted autoplay) and moves on when a video ends.
    If YouTube won't start playback (e.g. its "confirm you're not a bot" check on some networks),
    the player is hidden and the slider falls back to thumbnails with a "Watch on YouTube" link.
--}}
@php $videos = config('shop.videos', []); @endphp
@if (count($videos))
<section class="sl-videos" aria-labelledby="videos-title" aria-roledescription="carousel">
    <p class="sl-eyebrow text-center mb-1">On screen</p>
    <h2 id="videos-title" class="sl-section-title text-center">Brand Campaigns</h2>

    <div class="sl-videos-stage">
        <div class="sl-videos-track">
            @foreach ($videos as $v)
                <div class="sl-video-slide" role="group" aria-roledescription="slide" aria-label="{{ $loop->iteration }} of {{ count($videos) }}: {{ $v['brand'] }}, {{ $v['title'] }}" data-video="{{ $v['id'] }}" @unless ($loop->first) inert @endunless>
                    <div class="sl-video-frame">
                        <img src="https://i.ytimg.com/vi/{{ $v['id'] }}/maxresdefault.jpg" alt="" loading="{{ $loop->first ? 'eager' : 'lazy' }}" width="1280" height="720">
                        <button type="button" class="sl-video-play" aria-label="Play {{ $v['brand'] }}: {{ $v['title'] }}">
                            <i class="bi bi-play-fill" aria-hidden="true"></i>
                        </button>
                        <a class="sl-video-watch" href="https://www.youtube.com/watch?v={{ $v['id'] }}" target="_blank" rel="noopener">
                            <i class="bi bi-youtube" aria-hidden="true"></i> Watch on YouTube
                        </a>
                        <div class="sl-video-caption">
                            <span class="sl-video-brand">{{ $v['brand'] }}</span>
                            <strong>{{ $v['title'] }}</strong>
                            <small>{{ $v['credit'] }}</small>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        {{-- The single shared player --}}
        <div class="sl-videos-player"><div id="sl-videos-yt"></div></div>
    </div>

    {{-- Our controls sit under the video, so they never cover YouTube's own buttons --}}
    <div class="sl-videos-bar">
        <p class="sl-videos-now" aria-live="polite">
            <span class="sl-videos-count">1 / {{ count($videos) }}</span>
            @php $firstVideo = \Illuminate\Support\Arr::first($videos); @endphp
            <strong class="sl-videos-now-title">{{ $firstVideo['brand'] }} · {{ $firstVideo['title'] }}</strong>
        </p>
        <div class="sl-videos-buttons">
            <button type="button" class="sl-videos-sound" aria-pressed="false" aria-label="Turn sound on">
                <i class="bi bi-volume-mute-fill" aria-hidden="true"></i><span>Sound off</span>
            </button>
            <button type="button" class="sl-videos-nav sl-videos-prev" aria-label="Previous video"><i class="bi bi-chevron-left" aria-hidden="true"></i></button>
            <button type="button" class="sl-videos-nav sl-videos-next" aria-label="Next video"><i class="bi bi-chevron-right" aria-hidden="true"></i></button>
        </div>
    </div>

    {{-- Thumbnails double as the slide picker; the bar shows how far the current video has played --}}
    <div class="sl-videos-thumbs" role="tablist" aria-label="Choose a video">
        @foreach ($videos as $v)
            <button type="button" role="tab" class="sl-videos-thumb" aria-selected="{{ $loop->first ? 'true' : 'false' }}" data-go="{{ $loop->index }}">
                <img src="https://i.ytimg.com/vi/{{ $v['id'] }}/mqdefault.jpg" alt="" loading="lazy" width="320" height="180">
                <span><strong>{{ $v['brand'] }}</strong><span class="sl-videos-thumb-title">{{ $v['title'] }}</span></span>
                <i class="sl-videos-progress" aria-hidden="true"></i>
            </button>
        @endforeach
    </div>
</section>

<script>
(() => {
    const root = document.querySelector('.sl-videos');
    const stage = root.querySelector('.sl-videos-stage');
    const track = root.querySelector('.sl-videos-track');
    const slides = [...root.querySelectorAll('.sl-video-slide')];
    const thumbs = [...root.querySelectorAll('.sl-videos-thumb')];
    const soundBtn = root.querySelector('.sl-videos-sound');
    const ids = slides.map(s => s.dataset.video);
    const reduceMotion = matchMedia('(prefers-reduced-motion: reduce)').matches;
    const START_TIMEOUT = 8000;   // a video that hasn't started by then is treated as blocked
    const IMAGE_SLIDE_TIME = 6000; // slide time once we fall back to thumbnails

    let current = 0, player = null, ready = false, wanted = !reduceMotion, soundOn = false, inView = false;
    let blocked = false, watchdog = null, progressTimer = null, imageTimer = null, startX = null;

    const countEl = root.querySelector('.sl-videos-count');
    const nowEl = root.querySelector('.sl-videos-now-title');
    const setState = state => { stage.dataset.state = state; root.dataset.state = state; };   // idle | loading | playing | blocked

    // ---- Fallback: YouTube refused to play here, so become an image slider with links to YouTube
    function fallBack() {
        blocked = true;
        clearTimeout(watchdog); clearInterval(progressTimer);
        if (player && player.stopVideo) player.stopVideo();
        setState('blocked');
        clearInterval(imageTimer);
        if (!reduceMotion) imageTimer = setInterval(() => { if (inView) go(current + 1); }, IMAGE_SLIDE_TIME);
    }

    function play() {
        if (blocked || !ready || !inView) return;
        setState('loading');
        player.loadVideoById(ids[current]);
        soundOn ? player.unMute() : player.mute();
        clearTimeout(watchdog);
        watchdog = setTimeout(() => { if (stage.dataset.state !== 'playing') fallBack(); }, START_TIMEOUT);
    }

    function go(i) {
        current = (i + slides.length) % slides.length;
        track.style.transform = `translateX(-${current * 100}%)`;
        slides.forEach((s, n) => s.inert = n !== current);
        countEl.textContent = `${current + 1} / ${slides.length}`;
        nowEl.textContent = thumbs[current].querySelector('strong').textContent + ' · ' + thumbs[current].querySelector('.sl-videos-thumb-title').textContent;
        thumbs.forEach((t, n) => {
            t.setAttribute('aria-selected', n === current ? 'true' : 'false');
            t.querySelector('.sl-videos-progress').style.transform = 'scaleX(0)';
        });
        if (blocked) return;
        clearInterval(progressTimer);
        if (wanted) play(); else { player && player.stopVideo && player.stopVideo(); setState('idle'); }
    }

    // ---- YouTube player (standard youtube.com domain: it can use the visitor's YouTube cookies,
    //      which makes the "not a bot" check much less likely than the no-cookie domain)
    const api = document.createElement('script');
    api.src = 'https://www.youtube.com/iframe_api';
    api.onerror = fallBack;   // YouTube unreachable
    document.head.append(api);
    window.onYouTubeIframeAPIReady = () => {
        player = new YT.Player('sl-videos-yt', {
            host: 'https://www.youtube.com',
            playerVars: { mute: 1, playsinline: 1, rel: 0, modestbranding: 1, controls: 1 },
            events: {
                onReady: () => { ready = true; if (wanted) play(); },
                onStateChange: e => {
                    if (e.data === YT.PlayerState.PLAYING) {
                        clearTimeout(watchdog);
                        setState('playing');
                        clearInterval(progressTimer);
                        progressTimer = setInterval(() => {
                            const d = player.getDuration();
                            if (d > 0) thumbs[current].querySelector('.sl-videos-progress').style.transform = `scaleX(${player.getCurrentTime() / d})`;
                        }, 250);
                    }
                    if (e.data === YT.PlayerState.ENDED) go(current + 1);
                },
                onError: () => { clearTimeout(watchdog); setTimeout(() => go(current + 1), 1200); },
            },
        });
        // If the player never becomes ready (blocked before it loads), fall back as well
        setTimeout(() => { if (!ready) fallBack(); }, START_TIMEOUT + 4000);
    };

    // ---- Controls
    slides.forEach(s => s.querySelector('.sl-video-play').addEventListener('click', () => { wanted = true; play(); }));
    root.querySelector('.sl-videos-prev').addEventListener('click', () => go(current - 1));
    root.querySelector('.sl-videos-next').addEventListener('click', () => go(current + 1));
    thumbs.forEach(t => t.addEventListener('click', () => go(+t.dataset.go)));
    root.addEventListener('keydown', e => {
        if (e.target.tagName === 'IFRAME') return;
        if (e.key === 'ArrowLeft') go(current - 1);
        if (e.key === 'ArrowRight') go(current + 1);
    });
    soundBtn.addEventListener('click', () => {
        soundOn = !soundOn;
        soundBtn.setAttribute('aria-pressed', soundOn);
        soundBtn.setAttribute('aria-label', soundOn ? 'Turn sound off' : 'Turn sound on');
        soundBtn.querySelector('i').className = soundOn ? 'bi bi-volume-up-fill' : 'bi bi-volume-mute-fill';
        soundBtn.querySelector('span').textContent = soundOn ? 'Sound on' : 'Sound off';
        if (ready) soundOn ? player.unMute() : player.mute();
        if (soundOn && !wanted) { wanted = true; play(); }
    });

    // Play only while the slider is on screen
    new IntersectionObserver(([entry]) => {
        inView = entry.isIntersecting;
        if (blocked || !ready) return;
        if (inView && wanted) stage.dataset.state === 'playing' ? player.playVideo() : play();
        if (!inView && player.pauseVideo) player.pauseVideo();
    }, { threshold: 0.4 }).observe(stage);

    // Swipe on touch screens
    stage.addEventListener('touchstart', e => { startX = e.touches[0].clientX; }, { passive: true });
    stage.addEventListener('touchend', e => {
        if (startX === null) return;
        const dx = e.changedTouches[0].clientX - startX;
        if (Math.abs(dx) > 50) go(current + (dx < 0 ? 1 : -1));
        startX = null;
    });

    setState('idle');
})();
</script>
@endif
