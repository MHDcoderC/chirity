# Chirity — Charity News & CMS

سامانهٔ خبری و مدیریت محتوا برای خیریه‌ها و سازمان‌های خیریه‌ای.  
ساختار ماژولار با **PHP 8.1+** و **MySQL**، رابط کاربری **RTL**، پنل مدیریت یکپارچه، و نصب مرحله‌ای شبیه نصب‌گرهای معروف CMS.

[![GitHub stars](https://img.shields.io/github/stars/MHDcoderC/chirity?style=social)](https://github.com/MHDcoderC/chirity)

---

## ⭐ شرط استفاده — حتماً بخوانید

**برای استفاده، نصب، استقرار یا توسعهٔ این پروژه، لطفاً مخزن را در GitHub Star کنید:**

### 👉 [https://github.com/MHDcoderC/chirity](https://github.com/MHDcoderC/chirity)

روی دکمه **Star** در بالای صفحهٔ مخزن کلیک کنید. این شرط در مرحلهٔ نصب (`install.php`) نیز تأیید می‌شود.

---

## فهرست

- [امکانات](#امکانات)
- [پشته فنی](#پشته-فنی)
- [معماری](#معماری)
- [نیازمندی‌ها](#نیازمندی‌ها)
- [نصب](#نصب)
- [پیکربندی](#پیکربندی)
- [امنیت](#امنیت)
- [ساختار پروژه](#ساختار-پروژه)
- [توسعه‌دهنده](#توسعه‌دهنده)

---

## امکانات

### وب‌سایت عمومی (فرانت‌اند)

| ماژول | توضیح |
|--------|--------|
| **صفحه اصلی** | اسلایدر اخبار شاخص، بلوک دسته‌های قابل تنظیم برای صفحهٔ اول، آخرین اخبار |
| **آرشیو اخبار** | لیست paginated با فیلتر دسته |
| **صفحه مطلب** | نمایش کامل خبر، گالری تصاویر، شمارنده بازدید، نظرات |
| **دسته‌بندی** | URL تمیز: `/category/{slug}` |
| **خبر تکی** | URL تمیز: `/news/{slug}` |
| **جستجو** | جستجو در عنوان و محتوای اخبار |
| **نوار اخبار فوری (Ticker)** | اسکرول افقی اخبار علامت‌خورده برای تیکر |
| **اسلایدر اعضا** | نمایش اعضای خیریه در کروسل |
| **فراخوان جهادی** | بنر فعال + صفحه جزئیات + مودال ثبت‌نام |
| **کمک مالی** | مودال شیشه‌ای (Glassmorphism) با نمایش شماره کارت و کپی یک‌کلیکی |
| **فوتر پویا** | نام سایت، زیرعنوان، متن معرفی، تلفن، ایمیل — قابل ویرایش از پنل |
| **تاریخ شمسی** | نمایش تاریخ جلالی در هدر و بخش‌های مرتبط |

### پنل مدیریت (ادمین)

| بخش | قابلیت‌ها |
|------|-----------|
| **اخبار (Posts)** | CRUD کامل، ویرایشگر **Quill** (WYSIWYG)، تصویر شاخص با resize خودکار (۱۶:۹)، گالری چندتصویری، وضعیت (پیش‌نویس / منتشر / در انتظار)، خلاصه و محدودیت طول عنوان، تیکر، فعال/غیرفعال نظرات |
| **دسته‌بندی‌ها** | سلسله‌مراتب والد/فرزند، slug یکتا، نمایش در منوی هدر، نمایش در صفحهٔ اصلی با سقف تعداد پست |
| **نظرات** | تأیید / رد / حذف، پاسخ ادمین (threaded) |
| **اعضای خیریه** | عکس، سمت، ترتیب نمایش، فعال/غیرفعال |
| **گروه جهادی** | فراخوان با بنر، بازه تاریخ شمسی، سقف ثبت‌نام، لغو فراخوان؛ مشاهده و مدیریت ثبت‌نام‌ها |
| **تنظیمات سایت** | نام، زیرعنوان، متن فوتر، موبایل، ایمیل |
| **کمک مالی** | شماره کارت و نام دارنده — نمایش در مودال فرانت |
| **احراز هویت** | ورود امن، سشن با `HttpOnly` / `SameSite` / `Secure` روی HTTPS |

### نصب و استقرار

- **نصب دو مرحله‌ای:** (۱) پذیرش شرایط + Star مخزن GitHub — (۲) اتصال دیتابیس و ساخت ادمین
- **سازگار با cPanel:** اتصال مستقیم به دیتابیس از پیش ساخته‌شده (بدون `CREATE DATABASE` اجباری)
- **تشخیص خودکار `APP_URL`** برای روت دامنه یا زیرپوشه
- **مهاجرت دیتابیس (Migrations):** به‌روزرسانی schema روی نصب‌های قدیمی (نسخه‌های db_version ۲ تا ۵)
- **فایل‌های نمونه:** `config/local.php.example` ، `config/local.app.php.example`

### تجربه کاربری و رابط

- طراحی **RTL** با فونت **Vazirmatn**
- Bootstrap 5 RTL
- محدودیت طول متن در فرم‌ها (عنوان، خلاصه، فوتر و …)
- UI مینیمال در هدر؛ مودال کمک مالی با افکت شیشه‌ای
- Responsive برای موبایل و دسکتاپ

---

## پشته فنی

| لایه | تکنولوژی |
|------|----------|
| Backend | PHP 8.1+ (`strict_types`) |
| Database | MySQL / MariaDB (utf8mb4) |
| Frontend | HTML5, CSS3, Bootstrap 5 RTL |
| Editor | Quill.js |
| Auth | Session-based + `password_hash` |
| Server | Apache + `mod_rewrite` (`.htaccess`) |

---

## معماری

```
├── config/          # app.php, database.php, env.php, credits.php
├── includes/        # bootstrap, helpers, auth, csrf, migrate, jalali, settings
├── models/          # Active Record سبک (Post, Category, User, …)
├── templates/       # partials (header, footer, admin-layout, …)
├── admin/           # پنل مدیریت
├── assets/          # CSS, JS, vendor (Bootstrap, Quill, Icons)
├── uploads/         # posts, members, jehadi (خارج از Git)
├── database/        # schema.sql
└── install.php      # نصب اولیه
```

- **PDO** با Prepared Statements
- **CSRF** در فرم‌های پنل
- **Slug یکتا** برای SEO
- **تنظیمات key-value** در جدول `settings`
- **محیط اجرا:** تشخیص local/production، HTTPS اجباری روی production، خطاها در production لاگ می‌شوند

---

## نیازمندی‌ها

- PHP **8.1+** (با extensions: `pdo_mysql`, `mbstring`, `json`, `gd` یا `imagick` برای تصاویر)
- MySQL **5.7+** یا MariaDB **10.3+**
- Apache با `mod_rewrite` (یا nginx با قوانین معادل)
- حداقل **128MB** memory_limit (پیشنهادی)
- پوشه‌های `uploads/` و `config/` قابل نوشتن هنگام نصب

---

## نصب

### ۱. آماده‌سازی هاست (cPanel)

1. فایل‌های پروژه را در `public_html` (یا زیرپوشه) آپلود کنید.
2. در cPanel → **MySQL Databases** یک دیتابیس و کاربر بسازید و کاربر را به دیتابیس **اضافه** کنید.
3. مجوز **755** (یا 775) برای `uploads/` و `config/`.

### ۲. نصب از مرورگر

1. مرورگر: `https://your-domain.com/install.php`
2. شرایط استفاده را بخوانید و **Star** مخزن GitHub را بزنید:  
   **[github.com/MHDcoderC/chirity](https://github.com/MHDcoderC/chirity)**
3. تیک «می‌پذیرم» را بزنید و ادامه دهید.
4. وارد کنید:
   - **APP_URL:** خالی برای روت دامنه، یا مثلاً `/kh` برای زیرپوشه
   - **هاست DB:** معمولاً `localhost`
   - **نام DB / کاربر / رمز:** از cPanel
5. نام سایت و حساب مدیر را تعریف کنید.

پس از نصب، فایل `config/installed.lock` ساخته می‌شود و `install.php` دیگر در دسترس عموم نیست (قفل نصب).

### ۳. پس از نصب

- پنل: `/admin/login.php`
- سایت: `/` یا مسیر نصب شما

---

## پیکربندی

| فایل | کاربرد |
|------|--------|
| `config/local.php` | اتصال دیتابیس (ساخته‌شده توسط نصب) |
| `config/local.app.php` | `app_url`, `env`, `force_https` |
| `config/local.php.example` | نمونه دستی |
| `.htaccess` | RewriteBase، HTTPS، مسدودسازی پوشه‌های حساس |

**توجه:** `config/local.php` و `installed.lock` در `.gitignore` هستند — روی GitHub آپلود نشوند.

---

## امنیت

- محافظت CSRF در پنل ادمین
- Hash رمز عبور با `PASSWORD_DEFAULT`
- مسدود کردن دسترسی HTTP مستقیم به `config/`, `includes/`, `models/`, `database/`
- جلوگیری از اجرای PHP در `uploads/`
- Sanitize HTML محتوای خبر (whitelist تگ‌ها)
- Headers امنیتی در `.htaccess` (`X-Frame-Options`, `X-Content-Type-Options`, …)
- اعتبار توسعه‌دهنده در سورس به‌صورت encoded (غیرقابل حذف ساده)

---

## ساختار دیتابیس (خلاصه)

- `users` — مدیران
- `categories` — دسته‌ها + تنظیمات نمایش در هدر/خانه
- `posts` + `post_gallery`
- `comments` — با `parent_id` برای پاسخ
- `charity_members`
- `jehadi_calls` + `jehadi_registrations`
- `settings` — تنظیمات سایت، کمک مالی، `db_version`

---

## مجوز و مشارکت

این پروژه **متن‌باز با شرط Star** است:

1. مخزن را Star کنید: **[MHDcoderC/chirity](https://github.com/MHDcoderC/chirity)**
2. اعتبار توسعه‌دهنده را حذف یا جایگزین نکنید
3. برای استفاده تجاری یا بازنشر، رعایت شرایط نصب کافی است

---

## توسعه‌دهنده

**محمد سجادی**  
وب‌سایت: [mmdcode.top](https://mmdcode.top)  
مخزن: [github.com/MHDcoderC/chirity](https://github.com/MHDcoderC/chirity)

---

اگر این پروژه برایتان مفید بود، ⭐ **Star** یادتان نرود — از حمایت شما متشکریم.
