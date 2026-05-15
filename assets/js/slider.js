/**
 * همگام‌سازی تامب‌های اسلایدر و نوار پیشرفت
 */
(function () {
    var carousel = document.getElementById('newsHeroCarousel');
    if (!carousel || typeof bootstrap === 'undefined') {
        return;
    }

    var thumbs = document.querySelectorAll('.news-thumb-item');
    var progressBar = carousel.querySelector('.news-slide-progress-bar');
    var interval = parseInt(carousel.getAttribute('data-bs-interval') || '6000', 10);

    function setActiveThumb(index) {
        thumbs.forEach(function (btn, i) {
            btn.classList.toggle('active', i === index);
        });
    }

    function restartProgress() {
        if (!progressBar) {
            return;
        }
        progressBar.style.animation = 'none';
        progressBar.offsetHeight;
        progressBar.style.animation = 'newsProgress ' + interval + 'ms linear forwards';
    }

    carousel.addEventListener('slid.bs.carousel', function (e) {
        setActiveThumb(e.to);
        restartProgress();
    });

    setActiveThumb(0);
    restartProgress();
})();
