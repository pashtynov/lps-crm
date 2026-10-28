// Проблемы по квартирам в концепте: исходные — с сервера (window.LPS_PROBLEMS), правки — в этом браузере.
// Любая правка рассылает событие lps-problems — шахматка, сводки и списки перерисовываются.
(function () {
    const KEY = 'lps-concept-problems';
    const data = () => window.LPS_PROBLEMS || { defaults: {}, flats: {}, url: '' };
    const saved = () => { try { return JSON.parse(localStorage.getItem(KEY) || '{}'); } catch (e) { return {}; } };

    const api = window.lpsProblems = {
        get(h, n) { const k = `${h}:${n}`, s = saved(); return (k in s ? s[k] : data().defaults[k]) || ''; },
        set(h, n, text) {
            const s = saved(); s[`${h}:${n}`] = String(text || '').trim();
            try { localStorage.setItem(KEY, JSON.stringify(s)); } catch (e) { return false; }
            document.dispatchEvent(new CustomEvent('lps-problems'));
            return true;
        },
        // все проблемы в области: весь объект, дом или подъезд
        list(house, section) {
            return Object.entries(data().flats).map(([k, [sec, floor]]) => {
                const [h, n] = k.split(':').map(Number);
                return { house: h, n, section: sec, floor, text: api.get(h, n) };
            }).filter(p => p.text && (!house || p.house === +house) && (!section || p.section === +section));
        },
        url(h, n) { return data().url.replace('__H__', h).replace('__N__', n); },
        render() {
            const esc = (t) => t.replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
            document.querySelectorAll('[data-problems-count]').forEach((el) => {
                const n = api.list(el.dataset.house, el.dataset.section).length;
                el.querySelector('b').textContent = n || 'нет';
                el.classList.toggle('bad', n > 0);
            });
            document.querySelectorAll('[data-problems-list]').forEach((el) => {
                const items = api.list(el.dataset.house, el.dataset.section);
                el.hidden = !items.length;
                el.innerHTML = items.length ? `<b>⚠ Проблемы — ${items.length}</b>` + items.map(p =>
                    `<a href="${api.url(p.house, p.n)}"><span>Дом ${p.house} · подъезд ${p.section} · эт. ${p.floor} · кв. ${p.n}</span>${esc(p.text)}</a>`).join('') : '';
            });
        },
    };

    document.addEventListener('DOMContentLoaded', api.render);
    document.addEventListener('lps-problems', api.render);

    // Поле «Проблема»: ns — одна квартира или несколько (задание прораба на этаж)
    document.addEventListener('alpine:init', () => {
        Alpine.data('problemEdit', (house, ns) => ({
            house, ns: [].concat(ns), rev: 0, editing: false, n: null, draft: '',
            items() { this.rev; return this.ns.map(n => ({ n, text: api.get(this.house, n) })).filter(i => i.text); },
            many() { return this.ns.length > 1; },
            edit(n) { this.n = n ?? this.ns[0]; this.draft = api.get(this.house, this.n); this.editing = true; this.$nextTick(() => this.$refs.ta && this.$refs.ta.focus()); },
            pick() { this.draft = api.get(this.house, this.n); },
            save() { if (api.set(this.house, this.n, this.draft)) this.editing = false; },
            clear(n) { if (confirm(`Снять проблему с кв. ${n}?`)) api.set(this.house, n, ''); },
        }));
    });
})();
