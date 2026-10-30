{{-- Hover shows the second image via CSS; this adds tap (touch screens) and Enter/Space (keyboard) toggling --}}
<script>
document.querySelectorAll('.sl-promo-media').forEach(media => {
    if (!media.querySelector('.sl-promo-img--alt')) return;
    const toggle = () => {
        const on = media.classList.toggle('is-swapped');
        media.setAttribute('aria-pressed', on);
    };
    media.setAttribute('role', 'button');
    media.setAttribute('aria-pressed', 'false');
    media.addEventListener('click', e => { if (e.pointerType !== 'mouse') toggle(); });
    media.addEventListener('keydown', e => {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggle(); }
    });
});
</script>
