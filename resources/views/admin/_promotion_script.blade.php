{{-- Type switch (Promotion / Limited Edition shows only its own fields) and the live
     "20% off 250,000 = 200,000 MMK" preview from original price + discount. --}}
<script>
document.querySelectorAll('form').forEach(form => {
    const type = form.querySelector('[data-promo-type]');
    if (!type) return;

    // Hidden fieldsets are also disabled, so their fields are neither required nor sent
    const applyType = () => {
        form.querySelectorAll('[data-for]').forEach(el => {
            const on = el.dataset.for === type.value;
            el.hidden = !on;
            if (el.tagName === 'FIELDSET') el.disabled = !on;
        });
    };
    type.addEventListener('change', applyType);
    applyType();

    const price = form.querySelector('[data-promo-price]');
    const percent = form.querySelector('[data-promo-percent]');
    const preview = form.querySelector('[data-promo-preview]');
    if (!price || !percent || !preview) return;

    const fmt = n => Math.round(n).toLocaleString('en-US');
    const update = () => {
        const full = parseFloat(price.value);
        const pct = parseInt(percent.value, 10);
        if (!full || !(pct >= 1 && pct <= 90)) { preview.textContent = ''; return; }
        const sale = Math.round(full * (100 - pct) / 100);
        preview.innerHTML = `<span class="text-muted text-decoration-line-through">${fmt(full)}</span> &rarr; `
            + `<strong class="text-danger">${fmt(sale)} MMK</strong> <span class="text-muted">(saves ${fmt(full - sale)} MMK)</span>`;
    };
    price.addEventListener('input', update);
    percent.addEventListener('input', update);
    update();
});
</script>
@include('partials._promo_swap_script')
