{{-- Live "20% off 250,000 = 200,000 MMK" preview while the admin picks a product and discount --}}
<script>
document.querySelectorAll('form').forEach(form => {
    const product = form.querySelector('[data-promo-product]');
    const percent = form.querySelector('[data-promo-percent]');
    const preview = form.querySelector('[data-promo-preview]');
    if (!product || !percent || !preview) return;

    const fmt = n => Math.round(n).toLocaleString('en-US');
    const update = () => {
        const price = parseFloat(product.selectedOptions[0]?.dataset.price);
        const pct = parseInt(percent.value, 10);
        if (!price || !(pct >= 1 && pct <= 90)) { preview.textContent = ''; return; }
        const sale = Math.round(price * (100 - pct) / 100);
        preview.innerHTML = `<span class="text-muted text-decoration-line-through">${fmt(price)}</span> &rarr; `
            + `<strong class="text-danger">${fmt(sale)} MMK</strong> <span class="text-muted">(saves ${fmt(price - sale)} MMK)</span>`;
    };
    product.addEventListener('change', update);
    percent.addEventListener('input', update);
    update();
});
</script>
@include('partials._promo_swap_script')
