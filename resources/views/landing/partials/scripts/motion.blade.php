@verbatim
<script>
    (() => {
        const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
        /* muncul saat scroll */
        const rv = document.querySelectorAll('.rv');
        if ('IntersectionObserver' in window && !reduce) {
            const io = new IntersectionObserver(es => es.forEach(e => { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } }), { threshold: .15 });
            rv.forEach((el, i) => { el.style.transitionDelay = (i % 3) * 80 + 'ms'; io.observe(el); });
        } else rv.forEach(el => el.classList.add('in'));

        /* tab preview */
        const tabs = [...document.querySelectorAll('[data-tab]')], panes = [...document.querySelectorAll('[data-pane]')];
        if (tabs.length) {
            let cur = 0, timer;
            const show = i => {
                cur = i;
                tabs.forEach((t, k) => { t.classList.toggle('on', k === i); t.setAttribute('aria-selected', k === i); });
                panes.forEach((p, k) => { p.classList.remove('on'); p.hidden = k !== i; });
                setTimeout(() => panes[i].classList.add('on'), 40);
            };
            tabs.forEach((t, k) => t.addEventListener('click', () => { clearInterval(timer); show(k); }));
            show(0);
            if (!reduce) timer = setInterval(() => show((cur + 1) % tabs.length), 6500);
        }

        /* demo kasir di hero */
        const rows = [...document.querySelectorAll('#pos-rows .pos-row')];
        const total = document.getElementById('pos-total');
        const done = document.getElementById('pos-done');
        const fmt = n => 'Rp ' + n.toLocaleString('id-ID');
        const sum = n => rows.slice(0, n).reduce((a, r) => a + +r.dataset.p, 0);
        if (!rows.length) return;
        if (reduce) { rows.forEach(r => r.classList.add('on')); total.textContent = fmt(sum(rows.length)); done.classList.add('on'); return; }
        const count = (from, to) => {
            const t0 = performance.now();
            (function step(t) {
                const k = Math.min((t - t0) / 600, 1);
                total.textContent = fmt(Math.round(from + (to - from) * (1 - Math.pow(1 - k, 3))));
                if (k < 1) requestAnimationFrame(step);
            })(t0);
        };
        const wait = ms => new Promise(r => setTimeout(r, ms));
        (async function loop() {
            for (;;) {
                rows.forEach(r => r.classList.remove('on')); done.classList.remove('on'); total.textContent = fmt(0);
                await wait(700);
                for (let i = 0; i < rows.length; i++) { rows[i].classList.add('on'); count(sum(i), sum(i + 1)); await wait(1100); }
                await wait(500); done.classList.add('on');
                await wait(3200);
            }
        })();
    })();
</script>
@endverbatim
