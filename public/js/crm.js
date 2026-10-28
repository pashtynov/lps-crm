// Подсветка чисел, которые изменились после пересчёта: элементы с data-flash="уникальный-ключ".
// Ключ, а не сам элемент — Livewire при обновлении может заменить узел.
(function () {
    const seen = new Map();
    let queued = false;

    function scan() {
        queued = false;
        document.querySelectorAll('[data-flash]').forEach(function (el) {
            const key = el.dataset.flash, text = el.textContent.trim();
            if (seen.has(key) && seen.get(key) !== text) {
                el.classList.remove('crm-flash-num');
                void el.offsetWidth; // перезапуск анимации
                el.classList.add('crm-flash-num');
            }
            seen.set(key, text);
        });
    }

    function queue() {
        if (!queued) { queued = true; requestAnimationFrame(scan); }
    }

    document.addEventListener('DOMContentLoaded', function () {
        scan();
        new MutationObserver(queue).observe(document.body, { subtree: true, childList: true, characterData: true });
    });
})();

// Крошки на телефоне прокручиваются вбок — показываем конец пути (текущую страницу)
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.page-head .breadcrumb').forEach(function (b) {
        b.scrollLeft = b.scrollWidth;
        var edge = function () { b.classList.toggle('is-start', b.scrollLeft < 4); };
        b.addEventListener('scroll', edge, { passive: true });
        edge();
    });
});
