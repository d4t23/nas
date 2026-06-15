document.addEventListener('DOMContentLoaded', function () {
    const alerts = document.querySelectorAll('.alert');
    if (!alerts.length) {
        return;
    }

    setTimeout(function () {
        alerts.forEach(function (alert) {
            alert.style.transition = 'opacity 0.5s ease, max-height 0.5s ease, margin 0.5s ease, padding 0.5s ease';
            alert.style.opacity = '0';
            alert.style.maxHeight = '0';
            alert.style.margin = '0';
            alert.style.paddingTop = '0';
            alert.style.paddingBottom = '0';

            setTimeout(function () {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
            }, 500);
        });
    }, 2000);
});
