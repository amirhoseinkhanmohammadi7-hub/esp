# راهنمای ساختار پروژه Espira

این سند توضیح می‌دهد که هر فایل و پوشه در این پروژه چه مسئولیتی دارد. پروژه **Espira** یک اپلیکیشن وب مبتنی بر **Laravel** است که برای ردیابی و مدیریت عادت‌ها (Habit Tracking) طراحی شده است.

---

## 📁 ساختار کلی پروژه

```
/workspace
├── app/                  # کدهای اصلی برنامه
├── bootstrap/            # فایل‌های راه‌اندازی Laravel
├── config/               # فایل‌های پیکربندی
├── database/             # مهاجرت‌ها، فکتوری‌ها و سیدرها
├── public/               # فایل‌های عمومی (CSS, JS, تصاویر)
├── resources/            # ویوها، CSS و JS خام
├── routes/               # تعریف مسیرهای برنامه
├── storage/              # فایل‌های تولید شده (لاگ، کش، آپلودها)
├── tests/                # تست‌های واحد و یکپارچگی
├── vendor/               # وابستگی‌های Composer
├── node_modules/         # وابستگی‌های NPM
├── composer.json         # وابستگی‌های PHP
├── package.json          # وابستگی‌های JavaScript
├── vite.config.js        # پیکربندی Vite
└── tailwind.config.js    # پیکربندی Tailwind CSS
```

---

## 📂 پوشه `app/` - منطق اصلی برنامه

### `app/Models/` - مدل‌های داده (Eloquent ORM)

| فایل | مسئولیت |
|------|---------|
| `User.php` | مدل کاربر - مدیریت اطلاعات کاربران، احراز هویت و ارتباط با سایر جداول |
| `Habit.php` | مدل عادت - تعریف ساختار عادت‌ها و روابط با HabitLog و Achievement |
| `HabitLog.php` | ثبت روزانه عادت‌ها - ذخیره وضعیت انجام هر عادت در هر روز |
| `Achievement.php` | دستاوردها - مدل مربوط به موفقیت‌ها و نشان‌های کاربر |
| `Signature.php` | امضاها - ذخیره امضاهای کاربران روی عادت‌های اشتراک‌گذاری شده |
| `Reaction.php` | ری‌اکشن‌ها - ذخیره واکنش‌های احساسی (🔥, 💪, ⭐, ❤️) روی نمودارها |
| `Message.php` | پیام‌ها - مدل پیام‌های چت بین کاربران |
| `Chat.php` | چت‌ها - مدیریت مکالمات بین کاربران |
| `ChatRequest.php` | درخواست‌های چت - مدیریت درخواست‌های ارسال شده برای شروع چت |

### `app/Http/Controllers/` - کنترل‌کننده‌ها

#### کنترل‌کننده‌های اصلی:
| فایل | مسئولیت |
|------|---------|
| `Controller.php` | کنترل‌کننده پایه - تمام کنترل‌کننده‌های دیگر از این کلاس ارث‌بری می‌کنند |
| `HabitController.php` | مدیریت CRUD عادت‌ها - ایجاد، نمایش، ویرایش و حذف عادت‌های کاربران |
| `ProfileController.php` | مدیریت پروفایل کاربر - ویرایش اطلاعات، رمز عبور و حذف حساب |
| `ShareController.php` | اشتراک‌گذاری عادت‌ها - تولید لینک‌های عمومی برای عادت‌ها |
| `SignatureController.php` | مدیریت امضاها - ذخیره امضای کاربران روی صفحات اشتراک‌گذاری |
| `ChatController.php` | مدیریت چت - نمایش پیام‌ها، ارسال پیام و مدیریت درخواست‌های چت |

#### کنترل‌کننده‌های احراز هویت (`Auth/`):
| فایل | مسئولیت |
|------|---------|
| `RegisteredUserController.php` | ثبت‌نام کاربران جدید |
| `AuthenticatedSessionController.php` | مدیریت لاگین و خروج کاربران |
| `PasswordResetLinkController.php` | ارسال لینک بازنشانی رمز عبور |
| `NewPasswordController.php` | تنظیم رمز عبور جدید پس از کلیک روی لینک |
| `EmailVerificationPromptController.php` | نمایش صفحه تأیید ایمیل |
| `VerifyEmailController.php` | پردازش تأیید ایمیل |
| `EmailVerificationNotificationController.php` | ارسال مجدد ایمیل تأیید |
| `ConfirmablePasswordController.php` | تأیید رمز عبور قبل از عملیات حساس |
| `PasswordController.php` | تغییر رمز عبور کاربر وارد شده |

### `app/Http/Requests/` - اعتبارسنجی درخواست‌ها

| فایل | مسئولیت |
|------|---------|
| `Auth/LoginRequest.php` | اعتبارسنجی داده‌های فرم ورود |
| `ProfileUpdateRequest.php` | اعتبارسنجی داده‌های به‌روزرسانی پروفایل |

### `app/Providers/` - سرویس‌پروایدرها

| فایل | مسئولیت |
|------|---------|
| `AppServiceProvider.php` | ثبت سرویس‌ها و bindingهای سفارشی در کانتینر سرویس Laravel |

### `app/View/Components/` - کامپوننت‌های Blade

| فایل | مسئولیت |
|------|---------|
| `AppLayout.php` | لی‌اوت اصلی برنامه برای کاربران وارد شده |
| `GuestLayout.php` | لی‌اوت برای صفحات مهمان (لاگین، ثبت‌نام) |

### `app/Console/Commands/` - دستورات سفارشی Artisan

| فایل | مسئولیت |
|------|---------|
| `LogMissedHabits.php` | دستور زمان‌بندی شده برای ثبت عادت‌های انجام نشده |

---

## 📂 پوشه `database/` - پایگاه داده

### `database/migrations/` - مهاجرت‌های پایگاه داده

| فایل | مسئولیت |
|------|---------|
| `0001_01_01_000000_create_users_table.php` | ایجاد جدول کاربران |
| `0001_01_01_000001_create_cache_table.php` | ایجاد جدول کش |
| `0001_01_01_000002_create_jobs_table.php` | ایجاد جدول job queue |
| `2026_08_09_100406_create_habits_table.php` | ایجاد جدول عادت‌ها |
| `2026_08_09_100407_create_habit_logs_table.php` | ایجاد جدول ثبت روزانه عادت‌ها |
| `2026_08_09_100408_create_signatures_table.php` | ایجاد جدول امضاها |
| `2026_08_09_103627_add_completion_fields_to_habits_table.php` | افزودن فیلدهای تکمیلی به جدول عادت‌ها |
| `2026_08_09_104638_create_achievements_table.php` | ایجاد جدول دستاوردها |
| `2026_08_09_111247_create_reactions_table.php` | ایجاد جدول ری‌اکشن‌ها |
| `2026_08_09_112610_create_messages_table.php` | ایجاد جدول پیام‌ها |
| `2026_08_09_120000_add_profile_picture_to_users_table.php` | افزودن فیلد عکس پروفایل به کاربران |
| `2026_08_09_130000_add_bio_to_users_table.php` | افزودن فیلد بیوگرافی به کاربران |
| `2026_08_09_140000_create_chat_requests_table.php` | ایجاد جدول درخواست‌های چت |
| `2026_08_09_140001_create_chats_table.php` | ایجاد جدول چت‌ها |

### `database/factories/` - فکتوری‌ها برای تست

| فایل | مسئولیت |
|------|---------|
| `UserFactory.php` | تولید داده‌های تستی برای مدل User |

### `database/seeders/` - سیدرها برای پر کردن داده‌های اولیه

| فایل | مسئولیت |
|------|---------|
| `DatabaseSeeder.php` | نقطه ورود برای اجرای سایر سیدرها |

---

## 📂 پوشه `resources/` - فایل‌های خام

### `resources/views/` - قالب‌های Blade

#### صفحات اصلی:
| فایل | مسئولیت |
|------|---------|
| `welcome.blade.php` | صفحه اصلی سایت (برای کاربران مهمان) |
| `dashboard.blade.php` | داشبورد کاربر پس از ورود |

#### پوشه `auth/` - صفحات احراز هویت:
| فایل | مسئولیت |
|------|---------|
| `register.blade.php` | فرم ثبت‌نام |
| `login.blade.php` | فرم ورود |
| `forgot-password.blade.php` | درخواست بازنشانی رمز عبور |
| `reset-password.blade.php` | فرم تنظیم رمز عبور جدید |
| `verify-email.blade.php` | صفحه تأیید ایمیل |
| `confirm-password.blade.php` | تأیید رمز عبور |

#### پوشه `habits/` - صفحات مدیریت عادت‌ها:
| فایل | مسئولیت |
|------|---------|
| `index.blade.php` | لیست عادت‌های کاربر |
| `create.blade.php` | فرم ایجاد عادت جدید |
| `show.blade.php` | نمایش جزئیات یک عادت |
| `share.blade.php` | صفحه اشتراک‌گذاری عادت |

#### پوشه `profile/` - صفحات پروفایل:
| فایل | مسئولیت |
|------|---------|
| `edit.blade.php` | صفحه اصلی ویرایش پروفایل |
| `user-page.blade.php` | نمایش پروفایل عمومی کاربران |
| `manage-messages.blade.php` | مدیریت پیام‌ها |
| `partials/update-profile-information-form.blade.php` | فرم به‌روزرسانی اطلاعات پروفایل |
| `partials/update-password-form.blade.php` | فرم تغییر رمز عبور |
| `partials/delete-user-form.blade.php` | فرم حذف حساب کاربری |

#### پوشه `chat/` - صفحات چت:
| فایل | مسئولیت |
|------|---------|
| `index.blade.php` | لیست گفتگوهای کاربر |
| `show.blade.php` | نمایش یک گفتگوی خاص |
| `partials/message_bubble.blade.php` | کامپوننت نمایش پیام |
| `partials/message_preview.blade.php` | پیش‌نمایش پیام |
| `partials/voice_message.blade.php` | نمایش پیام صوتی |

#### پوشه `layouts/` - قالب‌های پایه:
| فایل | مسئولیت |
|------|---------|
| `app.blade.php` | لی‌اوت اصلی برای کاربران وارد شده |
| `guest.blade.php` | لی‌اوت برای صفحات مهمان |
| `navigation.blade.php` | منوی ناوبری |

#### پوشه `components/` - کامپوننت‌های قابل استفاده مجدد:
| فایل | مسئولیت |
|------|---------|
| `application-logo.blade.php` | لوگوی برنامه |
| `primary-button.blade.php` | دکمه اصلی |
| `secondary-button.blade.php` | دکمه ثانویه |
| `danger-button.blade.php` | دکمه خطر (حذف و ...) |
| `input-label.blade.php` | لیبل فیلد ورودی |
| `text-input.blade.php` | فیلد متنی |
| `input-error.blade.php` | نمایش خطای اعتبارسنجی |
| `dropdown.blade.php` | منوی کشویی |
| `dropdown-link.blade.php` | لینک داخل منوی کشویی |
| `modal.blade.php` | پنجره مودال |
| `nav-link.blade.php` | لینک ناوبری |
| `responsive-nav-link.blade.php` | لینک ناوبری ریسپانسیو |
| `alert.blade.php` | نمایش پیام هشدار |
| `empty-state.blade.php` | نمایش حالت خالی (وقتی داده‌ای نیست) |
| `auth-session-status.blade.php` | نمایش وضعیت session احراز هویت |

### `resources/css/` - فایل‌های استایل

| فایل | مسئولیت |
|------|---------|
| `app.css` | فایل اصلی CSS شامل دایرکتیوهای Tailwind و استایل‌های سفارشی |

### `resources/js/` - فایل‌های JavaScript

| فایل | مسئولیت |
|------|---------|
| `app.js` | فایل اصلی JavaScript - import کردن کتابخانه‌ها و کامپوننت‌ها |

---

## 📂 پوشه `routes/` - مسیریابی

| فایل | مسئولیت |
|------|---------|
| `web.php` | تعریف مسیرهای وب برنامه (احراز هویت، عادت‌ها، پروفایل، چت) |
| `api.php` | تعریف مسیرهای API (در صورت وجود) |
| `auth.php` | مسیرهای احراز هویت Laravel Breeze |
| `console.php` | تعریف دستورات زمان‌بندی شده Artisan |

---

## 📂 پوشه `config/` - پیکربندی

این پوشه حاوی فایل‌های پیکربندی Laravel است از جمله:
- `app.php` - پیکربندی اصلی برنامه
- `database.php` - تنظیمات پایگاه داده
- `auth.php` - تنظیمات احراز هویت
- `session.php` - تنظیمات session
- `cache.php` - تنظیمات کش
- و سایر فایل‌های پیکربندی

---

## 📂 پوشه `bootstrap/` - راه‌اندازی

| فایل | مسئولیت |
|------|---------|
| `app.php` | اسکریپت راه‌اندازی برنامه Laravel |
| `providers.php` | ثبت سرویس‌پروایدرها |

---

## 📂 پوشه `public/` - فایل‌های عمومی

این پوشه نقطه ورود تمام درخواست‌های وب است و شامل:
- `index.php` - نقطه ورود اصلی Laravel
- فایل‌های CSS و JS کامپایل شده
- تصاویر و سایر فایل‌های استاتیک

---

## 📂 پوشه `storage/` - فایل‌های تولید شده

شامل زیرپوشه‌های:
- `app/` - فایل‌های آپلود شده توسط کاربران
- `framework/` - کش، session و logهای Laravel
- `logs/` - فایل‌های لاگ برنامه

---

## 📂 پوشه `tests/` - تست‌ها

| پوشه | مسئولیت |
|------|---------|
| `Feature/` | تست‌های یکپارچگی که بخش‌های بزرگتری از کد را تست می‌کنند |
| `Unit/` | تست‌های واحد برای توابع و متدهای کوچک |

---

## 📄 فایل‌های پیکربندی اصلی

| فایل | مسئولیت |
|------|---------|
| `composer.json` | تعریف وابستگی‌های PHP و اسکریپت‌های Composer |
| `package.json` | تعریف وابستگی‌های JavaScript و اسکریپت‌های NPM |
| `vite.config.js` | پیکربندی Vite برای بیلد کردن assetها |
| `tailwind.config.js` | پیکربندی Tailwind CSS |
| `postcss.config.js` | پیکربندی PostCSS |
| `phpunit.xml` | پیکربندی PHPUnit برای تست |
| `.env.example` | نمونه فایل محیطی برای تنظیمات |
| `.gitignore` | فایل‌هایی که نباید در Git commit شوند |
| `.editorconfig` | تنظیمات ویرایشگر کد |

---

## 🔄 گردش کار کلی برنامه

1. **کاربر وارد می‌شود** → `AuthenticatedSessionController`
2. **عادت‌های خود را می‌بیند** → `HabitController@index`
3. **عادت جدید ایجاد می‌کند** → `HabitController@create/store`
4. **وضعیت روزانه را ثبت می‌کند** → `HabitLog`
5. **می‌تواند عادت را به اشتراک بگذارد** → `ShareController`
6. **دیگران می‌توانند امضا کنند** → `SignatureController`
7. **می‌تواند با دیگران چت کند** → `ChatController`
8. **پروفایل خود را مدیریت می‌کند** → `ProfileController`

---

## 🛠 تکنولوژی‌های استفاده شده

- **Backend**: Laravel 11+
- **Frontend**: Blade Templates + Tailwind CSS
- **Build Tool**: Vite
- **Database**: MySQL/PostgreSQL/SQLite
- **Authentication**: Laravel Breeze
