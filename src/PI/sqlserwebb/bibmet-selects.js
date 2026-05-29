(function () {
    function enhanceSelect(select) {
        if (!window.TomSelect || select.tomselect) {
            return;
        }

        var remoteUrl = select.dataset.remoteUrl;
        var config = {
            allowEmptyOption: true,
            create: false,
            sortField: [{ field: 'text', direction: 'asc' }],
            plugins: ['dropdown_input']
        };

        if (remoteUrl) {
            config.valueField = 'value';
            config.labelField = 'text';
            config.searchField = ['text'];
            config.preload = 'focus';
            config.load = function (query, callback) {
                fetch(remoteUrl + '?q=' + encodeURIComponent(query || ''), {
                    credentials: 'same-origin'
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Failed to load options');
                        }
                        return response.json();
                    })
                    .then(function (items) {
                        callback(items);
                    })
                    .catch(function () {
                        callback();
                    });
            };
        }

        new TomSelect(select, config);
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
