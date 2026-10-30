{{-- Show/hide password buttons, plus a simple strength meter on the register page --}}
<script>
document.querySelectorAll('[data-toggle-password]').forEach(btn => {
    const input = document.getElementById(btn.dataset.togglePassword);
    btn.addEventListener('click', () => {
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        btn.querySelector('i').className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
    });
});
document.querySelectorAll('[data-strength-for]').forEach(meter => {
    const input = document.getElementById(meter.dataset.strengthFor);
    const labels = ['', 'Too short', 'Weak', 'Okay', 'Strong'];
    input.addEventListener('input', () => {
        const v = input.value;
        let score = v.length === 0 ? 0 : v.length < 8 ? 1 : 2;
        if (score === 2 && /[A-Z]/.test(v) && /\d/.test(v)) score = 3;
        if (score === 3 && /[^A-Za-z0-9]/.test(v) && v.length >= 10) score = 4;
        meter.dataset.score = score;
        meter.querySelector('small').textContent = labels[score];
    });
});
</script>
