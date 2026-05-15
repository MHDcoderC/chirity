(function () {
    var form = document.getElementById('jehadiRegisterForm');
    if (!form) {
        return;
    }

    var alertEl = document.getElementById('jehadiFormAlert');
    var submitBtn = document.getElementById('jehadiSubmitBtn');
    var birthHidden = document.getElementById('birthDateCombined');
    var modalEl = document.getElementById('jehadiRegisterModal');

    function pad2(n) {
        return n < 10 ? '0' + n : String(n);
    }

    function combineBirthDate() {
        var y = form.birth_year.value;
        var m = form.birth_month.value;
        var d = form.birth_day.value;
        if (!y || !m || !d) {
            birthHidden.value = '';
            return '';
        }
        birthHidden.value = y + '/' + pad2(parseInt(m, 10)) + '/' + pad2(parseInt(d, 10));
        return birthHidden.value;
    }

    function showAlert(type, message) {
        if (!alertEl) return;
        alertEl.className = 'alert alert-' + type;
        alertEl.textContent = message;
        alertEl.classList.remove('d-none');
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        var name = (form.full_name.value || '').trim();
        if (name.length < 3) {
            showAlert('danger', 'نام و نام خانوادگی را کامل وارد کنید.');
            form.full_name.focus();
            return;
        }

        var birth = combineBirthDate();
        if (!birth) {
            showAlert('danger', 'تاریخ تولد شمسی را کامل انتخاب کنید.');
            return;
        }

        var nationalId = (form.national_id.value || '').replace(/\D/g, '');
        if (!/^\d{10}$/.test(nationalId)) {
            showAlert('danger', 'کد ملی باید ۱۰ رقم باشد.');
            form.national_id.focus();
            return;
        }

        var mobile = (form.mobile.value || '').replace(/\D/g, '');
        if (!/^09\d{9}$/.test(mobile)) {
            showAlert('danger', 'شماره همراه باید ۱۱ رقم و با ۰۹ شروع شود.');
            form.mobile.focus();
            return;
        }

        submitBtn.disabled = true;
        var fd = new FormData(form);
        fd.set('birth_date', birth);
        fd.set('national_id', nationalId);
        fd.set('mobile', mobile);

        fetch((window.KH_BASE || '') + '/jehadi-register.php', {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.ok) {
                    showAlert('success', data.message);
                    form.reset();
                    birthHidden.value = '';
                    setTimeout(function () {
                        if (modalEl && typeof bootstrap !== 'undefined') {
                            bootstrap.Modal.getInstance(modalEl)?.hide();
                        }
                    }, 3500);
                } else {
                    showAlert(data.full ? 'warning' : 'danger', data.message);
                }
            })
            .catch(function () {
                showAlert('danger', 'خطا در ارتباط با سرور. دوباره تلاش کنید.');
            })
            .finally(function () {
                submitBtn.disabled = false;
            });
    });

    if (modalEl) {
        modalEl.addEventListener('hidden.bs.modal', function () {
            alertEl.classList.add('d-none');
        });
    }
})();
