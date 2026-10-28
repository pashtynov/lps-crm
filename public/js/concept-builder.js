// Концепт: мастер создания объекта и работа с созданным объектом (копирование, схемы этажей).
// Хранится только в браузере (localStorage) — в базу CRM не пишется.
(function () {
    const KEY = 'lps-concept-objects';
    const H = 2.7;                       // высота помещения по умолчанию, м
    const DOOR = 0.8 * 2.1, WINDOW = 1.5 * 1.4;
    const PALETTE = ['#F4B13E', '#467CF4', '#9A52DF', '#1DBA6F', '#E8913A', '#2BB3C0', '#E94410', '#8B9298'];
    // МОП: стены / пол, м² — как в концепте ЖК «Новая Высота»
    const MOP = {
        hall: { name: 'Лифтовой холл и коридор', walls: 47.1, floor: 25.2, perFloor: true },
        stairs: { name: 'Лестничная клетка', walls: 43.4, floor: 15.7, perFloor: true },
        vestibule: { name: 'Тамбур и вестибюль', walls: 48.0, floor: 24.0 },
        basement: { name: 'Техподполье', walls: 93.2, floor: 72.0 },
        roof: { name: 'Выход на кровлю', walls: 21.4, floor: 7.5 },
    };

    const clone = (x) => JSON.parse(JSON.stringify(x));
    const num = (v) => { const n = parseFloat(String(v ?? '').replace(',', '.')); return isNaN(n) ? 0 : n; };
    const fmt = (v, d = 0) => Number(v).toLocaleString('ru-RU', { minimumFractionDigits: d, maximumFractionDigits: d });

    function roomAreas(r) {
        const a = num(r.a), b = num(r.b);
        return { floor: a * b, walls: Math.max(0, 2 * (a + b) * H - num(r.doors) * DOOR - num(r.windows) * WINDOW) };
    }
    function layoutAreas(l) {
        return (l.rooms || []).reduce((s, r) => { const x = roomAreas(r); s.floor += x.floor; s.walls += x.walls; return s; }, { floor: 0, walls: 0 });
    }

    // Схема каждого этажа подъезда из диапазонов: [этаж] => [коды планировок]
    function floorSchemes(section) {
        const out = {};
        for (let f = 1; f <= section.floors; f++) {
            const r = section.ranges.find((x) => f >= x.from && f <= x.to);
            out[f] = r ? [...r.scheme] : [];
        }
        return out;
    }
    // Обратно: одинаковые соседние этажи склеиваются в диапазоны
    function compress(section, schemes) {
        const ranges = [];
        for (let f = 1; f <= section.floors; f++) {
            const s = schemes[f] || [], last = ranges[ranges.length - 1];
            if (last && last.to === f - 1 && JSON.stringify(last.scheme) === JSON.stringify(s)) last.to = f;
            else ranges.push({ from: f, to: f, scheme: [...s] });
        }
        section.ranges = ranges;
    }

    // Развёртка: квартиры с номерами (сквозная нумерация по дому), МОП, итоги
    function expand(obj) {
        const houses = obj.houses.map((h) => {
            let n = 1;
            const sections = h.sections.map((s, si) => {
                const schemes = floorSchemes(s), floors = [];
                for (let f = 1; f <= s.floors; f++) {
                    floors.push({ floor: f, flats: schemes[f].map((code, i) => ({ n: n++, code, i, floor: f })) });
                }
                const mop = [];
                for (const [k, m] of Object.entries(MOP)) {
                    if (!s.mop[k]) continue;
                    if (m.perFloor) for (let f = 1; f <= s.floors; f++) mop.push({ name: `${m.name}, эт. ${f}`, walls: m.walls, floor: m.floor });
                    else mop.push({ name: m.name, walls: m.walls, floor: m.floor });
                }
                return { ...s, index: si, floorsList: floors.reverse(), mop };
            });
            return { ...h, sectionsX: sections };
        });
        return { houses, totals: totals(obj, houses) };
    }
    function totals(obj, houses) {
        const t = { houses: houses.length, sections: 0, flats: 0, mop: 0, rooms: 0, walls: 0, floor: 0, byLayout: {} };
        houses.forEach((h) => h.sectionsX.forEach((s) => {
            t.sections++;
            s.floorsList.forEach((fl) => fl.flats.forEach((f) => {
                const l = obj.layouts[f.code]; if (!l) return;
                const a = layoutAreas(l);
                t.flats++; t.rooms += l.rooms.length; t.walls += a.walls; t.floor += a.floor;
                t.byLayout[f.code] = (t.byLayout[f.code] || 0) + 1;
            }));
            s.mop.forEach((m) => { t.mop++; t.walls += m.walls; t.floor += m.floor; });
        }));
        return t;
    }

    function defaultObject() {
        return {
            id: 'o' + Date.now().toString(36), name: 'ЖК «Солнечный»', address: 'Нижний Новгород', customer: '', inn: '', created: null,
            layouts: {
                '1К-38': { code: '1К-38', rooms: [
                    { name: 'Комната', a: 4.3, b: 4.0, doors: 1, windows: 1 }, { name: 'Кухня', a: 3.5, b: 2.6, doors: 1, windows: 1 },
                    { name: 'Прихожая', a: 3.0, b: 1.8, doors: 4, windows: 0 }, { name: 'Санузел', a: 2.0, b: 1.8, doors: 1, windows: 0 }] },
                '2К-54': { code: '2К-54', rooms: [
                    { name: 'Комната 1', a: 4.2, b: 3.9, doors: 1, windows: 1 }, { name: 'Комната 2', a: 3.6, b: 3.2, doors: 1, windows: 1 },
                    { name: 'Кухня', a: 3.4, b: 2.9, doors: 1, windows: 1 }, { name: 'Прихожая', a: 4.2, b: 1.6, doors: 6, windows: 0 },
                    { name: 'Ванная', a: 2.0, b: 1.7, doors: 1, windows: 0 }, { name: 'Туалет', a: 1.5, b: 0.9, doors: 1, windows: 0 }] },
                '3К-72': { code: '3К-72', rooms: [
                    { name: 'Комната 1', a: 4.5, b: 4.0, doors: 1, windows: 1 }, { name: 'Комната 2', a: 3.9, b: 3.3, doors: 1, windows: 1 },
                    { name: 'Комната 3', a: 3.6, b: 3.0, doors: 1, windows: 1 }, { name: 'Кухня', a: 3.6, b: 3.1, doors: 1, windows: 1 },
                    { name: 'Прихожая', a: 5.0, b: 1.7, doors: 7, windows: 0 }, { name: 'Ванная', a: 2.2, b: 1.7, doors: 1, windows: 0 },
                    { name: 'Туалет', a: 1.5, b: 0.9, doors: 1, windows: 0 }] },
            },
            houses: [{ name: 'Дом 1', sections: [newSection()] }],
        };
    }
    function newSection() {
        return { floors: 9, ranges: [{ from: 1, to: 9, scheme: ['1К-38', '2К-54', '2К-54', '1К-38'] }],
            mop: { hall: true, stairs: true, vestibule: true, basement: true, roof: true } };
    }

    function load() { try { return JSON.parse(localStorage.getItem(KEY) || '[]'); } catch (e) { return []; } }
    function save(list) { try { localStorage.setItem(KEY, JSON.stringify(list)); return true; } catch (e) { return false; } }

    // Общие методы для мастера и созданного объекта
    const shared = {
        fmt, num,
        // развёртка считается заново на каждый вызов: данных немного, зато всегда свежая
        ex() { return expand(this.obj); },
        layoutCodes() { return Object.keys(this.obj.layouts); },
        color(code) { return PALETTE[this.layoutCodes().indexOf(code) % PALETTE.length] || '#c9ced2'; },
        areas(code) { const l = this.obj.layouts[code]; return l ? layoutAreas(l) : { floor: 0, walls: 0 }; },
        roomAreas,
        sectionFlats(s) { return s.floorsList.reduce((k, f) => k + f.flats.length, 0); },
        addHouse() { this.obj.houses.push({ name: `Дом ${this.obj.houses.length + 1}`, sections: [newSection()] }); },
        copyHouse(hi) { const h = clone(this.obj.houses[hi]); h.name = `Дом ${this.obj.houses.length + 1}`; this.obj.houses.push(h); },
        removeHouse(hi) { if (this.obj.houses.length > 1) this.obj.houses.splice(hi, 1); },
        addSection(hi) { this.obj.houses[hi].sections.push(newSection()); },
        copySection(hi, si) { this.obj.houses[hi].sections.splice(si + 1, 0, clone(this.obj.houses[hi].sections[si])); },
        removeSection(hi, si) { const h = this.obj.houses[hi]; if (h.sections.length > 1) h.sections.splice(si, 1); },
        setFloors(s, v) {
            const n = Math.max(1, Math.min(40, parseInt(v) || 1)), schemes = floorSchemes(s), top = schemes[s.floors] || [];
            for (let f = s.floors + 1; f <= n; f++) schemes[f] = [...top];     // новые этажи — как верхний
            s.floors = n; compress(s, schemes);
        },
        addRange(s) { const last = s.ranges[s.ranges.length - 1]; if (last && last.to < s.floors) s.ranges.push({ from: last.to + 1, to: s.floors, scheme: [...last.scheme] }); else s.ranges.push({ from: s.floors, to: s.floors, scheme: [] }); },
        normalize(s) {
            s.ranges.forEach((r) => { r.from = Math.max(1, parseInt(r.from) || 1); r.to = Math.max(r.from, Math.min(s.floors, parseInt(r.to) || r.from)); });
            const schemes = {};
            for (let f = 1; f <= s.floors; f++) { const r = [...s.ranges].reverse().find((x) => f >= x.from && f <= x.to); schemes[f] = r ? [...r.scheme] : []; }
            compress(s, schemes);
        },
        emptyFloors(s) { const sc = floorSchemes(s); return Object.keys(sc).filter((f) => !sc[f].length).map(Number); },
    };

    document.addEventListener('alpine:init', () => {
        Alpine.data('conceptWizard', (builtUrl) => ({
            ...shared,
            step: 1, obj: defaultObject(), editLayout: '1К-38', saved: false,
            steps: ['Объект', 'Планировки', 'Дома и подъезды', 'Проверка'],
            next() { if (this.step < 4) this.step++; window.scrollTo({ top: 0, behavior: 'smooth' }); },
            prev() { if (this.step > 1) this.step--; window.scrollTo({ top: 0, behavior: 'smooth' }); },
            addLayout() {
                const base = this.obj.layouts[this.editLayout] || { rooms: [] };
                let code = 'Новая', k = 1; while (this.obj.layouts[code + (k > 1 ? ' ' + k : '')]) k++;
                code = code + (k > 1 ? ' ' + k : '');
                this.obj.layouts[code] = { code, rooms: clone(base.rooms) }; this.editLayout = code;
            },
            renameLayout(oldCode, newCode) {
                newCode = String(newCode).trim(); if (!newCode || newCode === oldCode || this.obj.layouts[newCode]) return;
                const l = this.obj.layouts[oldCode]; delete this.obj.layouts[oldCode]; l.code = newCode; this.obj.layouts[newCode] = l;
                this.obj.houses.forEach((h) => h.sections.forEach((s) => s.ranges.forEach((r) => { r.scheme = r.scheme.map((c) => c === oldCode ? newCode : c); })));
                this.editLayout = newCode;
            },
            removeLayout(code) {
                if (this.layoutCodes().length < 2 || this.ex().totals.byLayout[code]) return;
                delete this.obj.layouts[code]; this.editLayout = this.layoutCodes()[0];
            },
            create() {
                this.obj.created = new Date().toISOString();
                const list = load().filter((o) => o.id !== this.obj.id); list.push(this.obj);
                this.saved = save(list);
                window.location.href = builtUrl + '?id=' + encodeURIComponent(this.obj.id);
            },
        }));

        Alpine.data('conceptBuilt', (indexUrl) => ({
            ...shared,
            obj: null, house: 0, selected: [], copyFrom: null, copyTo: null, toast: '',
            init() {
                const id = new URLSearchParams(location.search).get('id');
                this.obj = load().find((o) => o.id === id) || null;
                this.$watch('obj', () => { if (this.obj) { const list = load().map((o) => o.id === this.obj.id ? this.obj : o); save(list); } }, { deep: true });
            },
            say(t) { this.toast = t; clearTimeout(this._t); this._t = setTimeout(() => this.toast = '', 2600); },
            key(si, f, i) { return `${si}:${f}:${i}`; },
            isSel(si, f, i) { return this.selected.includes(this.key(si, f, i)); },
            toggle(si, f, i) { const k = this.key(si, f, i); this.selected = this.isSel(si, f, i) ? this.selected.filter((x) => x !== k) : [...this.selected, k]; },
            selectFloor(si, f, count) { const keys = [...Array(count).keys()].map((i) => this.key(si, f, i)); const all = keys.every((k) => this.selected.includes(k)); this.selected = all ? this.selected.filter((k) => !keys.includes(k)) : [...new Set([...this.selected, ...keys])]; },
            // Сменить планировку выделенным квартирам: правим схемы этажей и склеиваем обратно
            applyLayout(code) {
                if (!code || !this.selected.length) return;
                const h = this.obj.houses[this.house], bySec = {};
                this.selected.forEach((k) => { const [si, f, i] = k.split(':').map(Number); (bySec[si] ||= []).push([f, i]); });
                for (const [si, list] of Object.entries(bySec)) {
                    const s = h.sections[si], sc = floorSchemes(s);
                    list.forEach(([f, i]) => { if (sc[f][i] !== undefined) sc[f][i] = code; });
                    compress(s, sc);
                }
                this.say(`Планировка ${code} — ${this.selected.length} кв.`); this.selected = [];
            },
            removeFlats() {
                const h = this.obj.houses[this.house], bySec = {};
                this.selected.forEach((k) => { const [si, f, i] = k.split(':').map(Number); (bySec[si] ||= []).push([f, i]); });
                for (const [si, list] of Object.entries(bySec)) {
                    const s = h.sections[si], sc = floorSchemes(s);
                    list.sort((a, b) => b[1] - a[1]).forEach(([f, i]) => sc[f].splice(i, 1));
                    compress(s, sc);
                }
                this.say(`Убрано квартир: ${this.selected.length}`); this.selected = [];
            },
            // «Этажи с … по … как этаж N»
            copyFloor(si) {
                const s = this.obj.houses[this.house].sections[si], sc = floorSchemes(s);
                const from = parseInt(this.copyFrom), [a, b] = String(this.copyTo || '').split(/[-–—]/).map((v) => parseInt(v));
                if (!sc[from] || !a) return this.say('Укажите этаж-образец и этажи, например 3–9');
                for (let f = a; f <= (b || a); f++) if (f >= 1 && f <= s.floors) sc[f] = [...sc[from]];
                compress(s, sc); this.say(`Этажи ${a}${b ? '–' + b : ''} сделаны как этаж ${from}`); this.copyFrom = this.copyTo = null;
            },
            addFloor(si) { const s = this.obj.houses[this.house].sections[si]; this.setFloors(s, s.floors + 1); this.say(`Добавлен этаж ${s.floors}`); },
            copySectionHere(si) { this.copySection(this.house, si); this.say('Подъезд скопирован'); },
            copyHouseHere() { this.copyHouse(this.house); this.house = this.obj.houses.length - 1; this.say('Дом скопирован'); },
            addHouseHere() { this.addHouse(); this.house = this.obj.houses.length - 1; },
            removeObject() { if (!confirm('Удалить объект из концепта?')) return; save(load().filter((o) => o.id !== this.obj.id)); location.href = indexUrl; },
        }));

        // Созданные в мастере объекты — карточками в общем списке
        Alpine.data('conceptBuiltList', () => ({
            items: [],
            init() { this.items = load().map((o) => ({ id: o.id, name: o.name, t: totals(o, expand(o).houses) })); },
            fmt,
        }));
    });
})();
