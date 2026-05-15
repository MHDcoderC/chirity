<?php
/** @var array $call */
$jalaliYears = jalali_year_options(1320);
$jalaliMonths = jalali_month_names();
?>
<div class="modal fade" id="jehadiRegisterModal" tabindex="-1" aria-labelledby="jehadiRegisterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="jehadiRegisterModalLabel">ثبت‌نام در فراخوان</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
            </div>
            <form id="jehadiRegisterForm" class="modal-body pt-2">
                <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="call_id" value="<?= (int) $call['id'] ?>">
                <div id="jehadiFormAlert" class="alert d-none" role="alert"></div>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">نام و نام خانوادگی <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control" required maxlength="120"
                               autocomplete="name" placeholder="نام و نام خانوادگی">
                    </div>
                    <div class="col-12">
                        <label class="form-label">تاریخ تولد (شمسی) <span class="text-danger">*</span></label>
                        <div class="row g-2 jalali-date-picker">
                            <div class="col-4">
                                <select name="birth_year" class="form-select" required aria-label="سال تولد">
                                    <option value="">سال</option>
                                    <?php foreach ($jalaliYears as $y): ?>
                                    <option value="<?= $y ?>"><?= persian_digits((string) $y) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-4">
                                <select name="birth_month" class="form-select" required aria-label="ماه تولد">
                                    <option value="">ماه</option>
                                    <?php foreach ($jalaliMonths as $num => $name): ?>
                                    <option value="<?= $num ?>"><?= e($name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-4">
                                <select name="birth_day" class="form-select" required aria-label="روز تولد">
                                    <option value="">روز</option>
                                    <?php for ($d = 1; $d <= 31; $d++): ?>
                                    <option value="<?= $d ?>"><?= persian_digits((string) $d) ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        <input type="hidden" name="birth_date" id="birthDateCombined">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">کد ملی <span class="text-danger">*</span></label>
                        <input type="text" name="national_id" class="form-control" required maxlength="10" minlength="10"
                               inputmode="numeric" pattern="[0-9]{10}" title="۱۰ رقم عددی"
                               placeholder="۱۰ رقم">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">شماره همراه <span class="text-danger">*</span></label>
                        <input type="tel" name="mobile" class="form-control" required maxlength="11" minlength="11"
                               inputmode="numeric" pattern="09[0-9]{9}" title="مثال: ۰۹۱۲۳۴۵۶۷۸۹"
                               placeholder="۰۹۱۲۳۴۵۶۷۸۹">
                    </div>
                </div>
                <p class="form-text small text-muted mb-0 mt-2">همه فیلدها الزامی هستند.</p>
                <div class="modal-footer border-0 px-0 pb-0 pt-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-danger" id="jehadiSubmitBtn">
                        <i class="bi bi-send"></i> ارسال ثبت‌نام
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
