(function () {
    var LOGIN_PATH = '/PI/sqlserwebb/loggain.php';
    var LOGIN_URL = LOGIN_PATH + '?reason=timeout';

    if (window.location.pathname === LOGIN_PATH || /\/loggain\.php$/.test(window.location.pathname)) {
        return;
    }

    var WARNING_MS = 10 * 60 * 1000;
    var TIMEOUT_MS = 11 * 60 * 1000;
    var warnTimer;
    var logoutTimer;

    function removeWarning() {
        var warning = document.getElementById('session-warning');
        if (warning) {
            warning.parentNode.removeChild(warning);
        }
    }

    function showWarning() {
        if (document.getElementById('session-warning')) {
            return;
        }

        var remainingMinutes = Math.max(1, Math.round((TIMEOUT_MS - WARNING_MS) / 60000));
        var minuteText = remainingMinutes === 1 ? 'minut' : 'minuter';

        var warning = document.createElement('div');
        warning.id = 'session-warning';
        warning.innerHTML =
            '<div style="position:fixed;top:0;left:0;width:100%;padding:12px;' +
            'background:#c00;color:#fff;text-align:center;z-index:9999;font-size:1rem">' +
            'Din session löper ut om cirka ' + remainingMinutes + ' ' + minuteText + '. ' +
            '<a href="#" id="session-extend" style="color:#fff;font-weight:bold">Förläng</a>' +
            '</div>';

        document.body.insertBefore(warning, document.body.firstChild);

        document.getElementById('session-extend').onclick = function (event) {
            event.preventDefault();
            window.location.reload();
        };
    }

    function doLogout() {
        window.location.href = LOGIN_URL;
    }

    function resetTimers() {
        clearTimeout(warnTimer);
        clearTimeout(logoutTimer);
        warnTimer = setTimeout(showWarning, WARNING_MS);
        logoutTimer = setTimeout(doLogout, TIMEOUT_MS);
    }

    function registerActivityHandlers() {
        ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'].forEach(function (eventName) {
            document.addEventListener(eventName, function () {
                removeWarning();
                resetTimers();
            });
        });
    }

    function start() {
        if (!document.body) {
            return;
        }

        registerActivityHandlers();
        resetTimers();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();
