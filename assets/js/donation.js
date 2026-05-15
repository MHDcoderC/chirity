(function () {
    var modal = document.getElementById('donationModal');
    if (modal) {
        modal.addEventListener('show.bs.modal', function () {
            document.body.classList.add('donation-modal-open');
        });
        modal.addEventListener('hidden.bs.modal', function () {
            document.body.classList.remove('donation-modal-open');
        });
    }

    var btn = document.getElementById('donationCopyBtn');
    if (!btn) {
        return;
    }
    var feedback = document.getElementById('donationCopyFeedback');

    btn.addEventListener('click', function () {
        var raw = btn.getAttribute('data-card') || '';
        if (!raw) {
            return;
        }

        function done() {
            if (feedback) {
                feedback.classList.remove('d-none');
                setTimeout(function () {
                    feedback.classList.add('d-none');
                }, 2500);
            }
            var span = btn.querySelector('span:last-child');
            if (span && !span.classList.contains('btn-donation-copy-icon')) {
                var orig = span.textContent;
                span.textContent = 'کپی شد!';
                setTimeout(function () {
                    span.textContent = orig;
                }, 2000);
            }
        }

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(raw).then(done).catch(fallback);
        } else {
            fallback();
        }

        function fallback() {
            var ta = document.createElement('textarea');
            ta.value = raw;
            ta.style.position = 'fixed';
            ta.style.left = '-9999px';
            document.body.appendChild(ta);
            ta.select();
            try {
                document.execCommand('copy');
                done();
            } catch (e) { /* ignore */ }
            document.body.removeChild(ta);
        }
    });
})();
