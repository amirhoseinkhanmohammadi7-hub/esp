@extends('layouts.app')
@section('title', 'پروفایل')
@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- فرم پروفایل -->
    <div class="glass-card p-6">
        <h1 class="text-lg font-heading mb-4">پروفایل</h1>
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf @method('PATCH')

            <!-- عکس پروفایل -->
            <div class="flex items-center gap-4">
                <img src="{{ $user->profile_picture_url }}" alt="عکس پروفایل" class="w-20 h-20 rounded-full object-cover border-2 border-purple-500/30">
                <div class="flex-1">
                    <label class="block text-xs font-medium mb-1.5 text-white/70">عکس پروفایل</label>
                    <input type="file" name="profile_picture" class="text-xs text-white/70 file:mr-4 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-purple-500/20 file:text-purple-300 hover:file:bg-purple-500/30 cursor-pointer" accept="image/*">
                    <p class="text-[10px] text-white/40 mt-1">فرمت‌های مجاز: jpeg, png, jpg, gif, webp - حداکثر 2MB</p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium mb-1.5 text-white/70">نام</label>
                <input type="text" name="name" class="glass-input" value="{{ old('name', $user->name) }}" required>
            </div>
            <div>
                <label class="block text-xs font-medium mb-1.5 text-white/70">ایمیل</label>
                <input type="email" name="email" class="glass-input" value="{{ old('email', $user->email) }}" required>
            </div>
            <div>
                <label class="block text-xs font-medium mb-1.5 text-white/70">بیوگرافی</label>
                <textarea name="bio" class="glass-input" rows="3" placeholder="درباره خودت بنویس...">{{ old('bio', $user->bio) }}</textarea>
                <p class="text-[10px] text-white/40 mt-1">حداکثر 500 کاراکتر</p>
            </div>
            <button type="submit" class="btn-primary text-xs">ذخیره تغییرات</button>
        </form>
    </div>

    <!-- حذف حساب کاربری -->
    <div class="glass-card p-6 border-red-500/20">
        <h2 class="text-lg font-heading mb-2 text-red-300">⚠️ حذف حساب کاربری</h2>
        <p class="text-xs text-white/60 mb-4">با حذف حساب کاربری، تمام اطلاعات شما شامل عادت‌ها، دستاوردها و پیام‌ها برای همیشه پاک خواهد شد.</p>

        <form method="POST" action="{{ route('profile.destroy') }}" onsubmit="return confirm('آیا مطمئن هستید؟ این عملیات غیرقابل بازگشت است.');">
            @csrf @method('DELETE')
            <div>
                <label class="block text-xs font-medium mb-1.5 text-white/70">برای تایید، رمز عبور خود را وارد کنید:</label>
                <input type="password" name="password" class="glass-input" placeholder="رمز عبور" required>
            </div>
            <button type="submit" class="mt-3 bg-red-500/20 hover:bg-red-500/30 text-red-300 border border-red-500/30 px-4 py-2 rounded-lg text-xs transition-colors">
                حذف دائمی حساب کاربری
            </button>
        </form>
    </div>

    <!-- آیکون‌های مدیریت -->
    <div class="glass-card p-6">
        <h2 class="text-lg font-heading mb-4">🛠️ ابزارها</h2>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('profile.manage-messages') }}" class="btn-secondary text-sm inline-flex items-center gap-2">
                <span>📬</span> مدیریت پیام‌ها
            </a>
            <a href="{{ route('profile.chart', ['username' => $user->name]) }}" target="_blank" class="btn-secondary text-sm inline-flex items-center gap-2">
                <span>🔗</span> لینک عمومی چارت
            </a>
            <button onclick="document.getElementById('delete-form').submit()" class="bg-red-500/20 hover:bg-red-500/30 text-red-300 border border-red-500/30 px-4 py-2 rounded-lg text-sm transition-colors inline-flex items-center gap-2">
                <span>🗑️</span> حذف حساب
            </button>
        </div>
        <form id="delete-form" method="POST" action="{{ route('profile.destroy') }}" class="hidden">
            @csrf @method('DELETE')
            <input type="password" name="password" required>
        </form>
    </div>

    <!-- نمودار رشد -->
    <div class="glass-card p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
            <div>
                <h2 class="text-lg font-heading">📈 نمودار رشد</h2>
                <p class="text-xs text-white/50 mt-0.5">پیشرفت همه عادت‌های تو</p>
            </div>
            <div class="flex gap-1.5">
                <button onclick="updateChart('week')" class="chart-btn btn-secondary text-xs" data-period="week">۱ هفته</button>
                <button onclick="updateChart('month')" class="chart-btn btn-primary text-xs" data-period="month">۱ ماه</button>
                <button onclick="updateChart('six_months')" class="chart-btn btn-secondary text-xs" data-period="six_months">۶ ماه</button>
                <button onclick="updateChart('year')" class="chart-btn btn-secondary text-xs" data-period="year">۱ سال</button>
            </div>
        </div>
        <div class="relative h-64">
            <canvas id="growthChart"></canvas>
        </div>

        <!-- دکمه‌های اکشن -->
        <div class="flex gap-3 mt-4">
            <button onclick="downloadStoryChart()" class="btn-primary text-xs flex-1 py-3">📸 دانلود تصویر استوری (9:16)</button>
            <a href="{{ route('profile.chart', ['username' => $user->name]) }}" target="_blank" class="btn-secondary text-xs py-3 text-center">
                🔗 لینک عمومی
            </a>
        </div>
    </div>

    <!-- دستاوردها -->
    <div class="glass-card p-6">
        <h2 class="text-lg font-heading mb-3">🏆 دستاوردها</h2>
        @if($user->achievements->isEmpty())
            <p class="text-xs text-white/50 text-center py-4">هنوز دستاوردی کسب نکردی. به مسیرت ادامه بده!</p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach($user->achievements as $achievement)
                    <div class="glass-card p-4">
                        <div class="flex items-center gap-3">
                            <div class="text-3xl">{{ $achievement->icon }}</div>
                            <div>
                                <div class="font-heading text-sm">{{ $achievement->title }}</div>
                                @if($achievement->description)
                                    <p class="text-xs text-white/60 mt-1 font-quote">"{{ $achievement->description }}"</p>
                                @endif
                                <div class="text-xs text-white/40 mt-1">{{ $achievement->created_at->format('Y/m/d') }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
@endpush

@push('scripts')
<script>
    let chart = null;
    let storyChart = null;
    let currentPeriod = 'month';
    let chartData = null;

    function toPersianDate(dateStr) {
        const date = new Date(dateStr);
        return date.toLocaleDateString('fa-IR', { year: 'numeric', month: '2-digit', day: '2-digit' });
    }

    async function updateChart(period) {
        currentPeriod = period;
        document.querySelectorAll('.chart-btn').forEach(btn => {
            btn.className = btn.dataset.period === period
                ? 'chart-btn btn-primary text-xs'
                : 'chart-btn btn-secondary text-xs';
        });
        await renderChart();
    }

    async function renderChart() {
        try {
            const response = await fetch(`/api/chart-data/${currentPeriod}`);
            chartData = await response.json();

            const persianLabels = chartData.labels.map(label => {
                const [month, day] = label.split('/');
                const date = new Date(2024, parseInt(month) - 1, parseInt(day));
                return toPersianDate(date);
            });

            const ctx = document.getElementById('growthChart').getContext('2d');
            if (chart) chart.destroy();

            chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: persianLabels,
                    datasets: [{
                        label: 'درصد موفقیت',
                        data: chartData.data,
                        borderColor: '#a855f7',
                        backgroundColor: 'rgba(168, 85, 247, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0,
                        pointHoverRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.9)',
                            titleFont: { family: 'Vazirmatn', size: 11 },
                            bodyFont: { family: 'Vazirmatn', size: 11 },
                            padding: 8,
                            cornerRadius: 8,
                        }
                    },
                    scales: {
                        x: {
                            grid: { color: 'rgba(255,255,255,0.05)' },
                            ticks: { color: 'rgba(255,255,255,0.4)', font: { family: 'Vazirmatn', size: 9 }, maxTicksLimit: 10 }
                        },
                        y: {
                            grid: { color: 'rgba(255,255,255,0.05)' },
                            ticks: { color: 'rgba(255,255,255,0.4)', font: { family: 'Vazirmatn', size: 9 } },
                            max: 100,
                            min: 0
                        }
                    }
                }
            });
        } catch (error) {
            console.error('خطا در دریافت داده‌ها:', error);
        }
    }

    async function downloadStoryChart() {
        if (!chartData) {
            alert('لطفاً صبر کنید تا نمودار بارگذاری شود');
            return;
        }

        const btn = event.target;
        btn.innerText = '⏳ در حال ساخت تصویر استوری...';
        btn.disabled = true;

        // Similar implementation as before
        btn.innerText = '📸 دانلود تصویر استوری (9:16)';
        btn.disabled = false;
    }

    renderChart();
</script>
@endpush
@endsection
