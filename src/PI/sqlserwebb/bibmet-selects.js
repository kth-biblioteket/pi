(function () {
    function enhanceSelect(select) {
        if (!window.TomSelect || select.tomselect) {
            return;
        }

        new TomSelect(select, {
            allowEmptyOption: true,
            create: false,
            sortField: [{ field: 'text', direction: 'asc' }],
            plugins: ['dropdown_input']
        });
    }

    function init() {
        document.querySelectorAll('select.js-bibmet-select').forEach(enhanceSelect);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
