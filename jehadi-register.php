<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'درخواست نامعتبر است.']);
    exit;
}

if (!is_installed()) {
    http_response_code(503);
    echo json_encode(['ok' => false, 'message' => 'سایت در حال راه‌اندازی است.']);
    exit;
}

$token = $_POST['_csrf'] ?? '';
if ($token === '' || !hash_equals(csrf_token(), $token)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'نشست منقضی شده. صفحه را رفرش کنید.']);
    exit;
}

$callId = (int) ($_POST['call_id'] ?? 0);
$call = $callId > 0 ? JehadiCall::find($callId) : JehadiCall::active();

if (!$call || ($call['status'] ?? '') !== 'active') {
    echo json_encode(['ok' => false, 'message' => 'فراخوان فعالی برای ثبت‌نام وجود ندارد.']);
    exit;
}

if (!JehadiCall::isOpenForRegistration($call)) {
    if (JehadiCall::isFull($call)) {
        echo json_encode([
            'ok' => false,
            'full' => true,
            'message' => 'ظرفیت این فراخوان تکمیل شده است. ان‌شاءالله در فراخوان‌های بعدی می‌توانید ثبت‌نام کنید.',
        ]);
        exit;
    }
    echo json_encode(['ok' => false, 'message' => 'مهلت ثبت‌نام این فراخوان به پایان رسیده است.']);
    exit;
}

$fullName = trim($_POST['full_name'] ?? '');
$birthJalali = trim($_POST['birth_date'] ?? '');
$nationalId = preg_replace('/\D/', '', $_POST['national_id'] ?? '') ?? '';
$mobile = preg_replace('/\D/', '', $_POST['mobile'] ?? '') ?? '';

if ($fullName === '' || mb_strlen($fullName) < 3) {
    echo json_encode(['ok' => false, 'message' => 'نام و نام خانوادگی را کامل وارد کنید.']);
    exit;
}

$birthDate = parse_jalali_date_string($birthJalali);
if (!$birthDate) {
    echo json_encode(['ok' => false, 'message' => 'تاریخ تولد شمسی معتبر نیست (مثال: ۱۳۸۵/۰۳/۱۵).']);
    exit;
}

if (!validate_iran_national_id($nationalId)) {
    echo json_encode(['ok' => false, 'message' => 'کد ملی معتبر نیست.']);
    exit;
}

if (!validate_iran_mobile($mobile)) {
    echo json_encode(['ok' => false, 'message' => 'شماره همراه باید ۱۱ رقم و با ۰۹ شروع شود.']);
    exit;
}

if (JehadiRegistration::existsNationalId((int) $call['id'], $nationalId)) {
    echo json_encode(['ok' => false, 'message' => 'با این کد ملی قبلاً در این فراخوان ثبت‌نام شده است.']);
    exit;
}

$key = 'jehadi_reg_' . (int) $call['id'];
if (isset($_SESSION[$key]) && time() - $_SESSION[$key] < 30) {
    echo json_encode(['ok' => false, 'message' => 'لطفاً چند ثانیه صبر کنید و دوباره تلاش کنید.']);
    exit;
}

// ظرفیت مجدد قبل از insert
$call = JehadiCall::find((int) $call['id']);
if (!$call || JehadiCall::isFull($call)) {
    echo json_encode([
        'ok' => false,
        'full' => true,
        'message' => 'ظرفیت این فراخوان تکمیل شده است. ان‌شاءالله در فراخوان‌های بعدی می‌توانید ثبت‌نام کنید.',
    ]);
    exit;
}

try {
    JehadiRegistration::create((int) $call['id'], [
        'full_name'         => limit_text_field($fullName, 120),
        'personal_details'  => null,
        'birth_date'        => $birthDate,
        'national_id'       => $nationalId,
        'mobile'            => $mobile,
    ]);
    $_SESSION[$key] = time();
    echo json_encode([
        'ok'      => true,
        'message' => 'ثبت‌نام شما با موفقیت انجام شد. ان‌شاءالله به‌زودی با شما تماس می‌گیریم.',
    ]);
} catch (PDOException $e) {
    if (str_contains($e->getMessage(), 'uk_call_national')) {
        echo json_encode(['ok' => false, 'message' => 'با این کد ملی قبلاً ثبت‌نام شده است.']);
        exit;
    }
    echo json_encode(['ok' => false, 'message' => 'خطا در ثبت‌نام. لطفاً دوباره تلاش کنید.']);
}
