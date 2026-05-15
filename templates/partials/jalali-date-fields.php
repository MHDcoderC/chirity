<?php
/**
 * انتخابگر تاریخ شمسی
 * @var string $prefix
 * @var string $label
 * @var string|null $gregorian
 */
$prefix = $prefix ?? 'date';
$label = $label ?? 'تاریخ';
$parts = gregorian_to_jalali_parts($gregorian ?? null);
[$currentJy] = gregorian_to_jalali((int) date('Y'), (int) date('n'), (int) date('j'));
$jalaliYears = jalali_year_options($currentJy - 1, $currentJy + 2);
$jalaliMonths = jalali_month_names();
?>
<div class="jalali-date-field mb-0">
    <label class="form-label"><?= e($label) ?></label>
    <div class="row g-2">
        <div class="col-4">
            <select name="<?= e($prefix) ?>_year" class="form-select form-select-sm" required>
                <option value="">سال</option>
                <?php foreach ($jalaliYears as $y): ?>
                <option value="<?= $y ?>" <?= (string) $parts['year'] === (string) $y ? 'selected' : '' ?>>
                    <?= persian_digits((string) $y) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-4">
            <select name="<?= e($prefix) ?>_month" class="form-select form-select-sm" required>
                <option value="">ماه</option>
                <?php foreach ($jalaliMonths as $num => $name): ?>
                <option value="<?= $num ?>" <?= (string) $parts['month'] === (string) $num ? 'selected' : '' ?>>
                    <?= e($name) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-4">
            <select name="<?= e($prefix) ?>_day" class="form-select form-select-sm" required>
                <option value="">روز</option>
                <?php for ($d = 1; $d <= 31; $d++): ?>
                <option value="<?= $d ?>" <?= (string) $parts['day'] === (string) $d ? 'selected' : '' ?>>
                    <?= persian_digits((string) $d) ?>
                </option>
                <?php endfor; ?>
            </select>
        </div>
    </div>
</div>
