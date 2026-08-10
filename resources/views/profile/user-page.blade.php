@extends('layouts.app')
@section('title', $user->name)
@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- هدر پروفایل -->
    <div class="glass-card p-8 text-center">
        <img src="{{ $user->profile_picture_url }}" alt="{{ $user->name }}" class="w-32 h-32 rounded-full mx-auto object-cover border-4 border-purple-500/30 mb-4">
        <h1 class="text-2xl font-heading mb-2">{{ $user->name }}</h1>
        @if($user->bio)
            <p class="text-white/60 text-sm mb-3 max-w-lg mx-auto">{{ e($user->bio) }}</p>
        @endif
        <p class="text-white/40 text-xs">عضو از {{ $user->joined_at ? $user->joined_at->format('Y/m/d') : 'اخیراً' }}</p>
    </div>

    <!-- نمودار رشد -->
    <div class="glass-card p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
            <div>
                <h2 class="text-lg font-heading">📈 نمودار رشد</h2>
                <p class="text-xs text-white/50 mt-0.5">پیشرفت همه عادت‌های {{ $user->name }}</p>
            </div>
            <div class="flex gap-1.5">
                <button onclick="updateChart('week')" class="chart-btn btn-secondary text-xs" data-period="week">۱ هفته</button>
                <button onclick="updateChart('month')" class="chart-btn btn-primary text-xs" data-period="month">۱ ماه</button>
                <button onclick="updateChart('six_months')" class="chart-btn btn-secondary text-xs" data-period="six_months">۶ ماه</button>
                <button onclick="updateChart('year')" class="chart-btn btn-secondary text-xs" data-period="year">۱ سال</button>
            </div>
        </div>
        <div class="relative h-72 bg-gradient-to-br from-slate-900 to-slate-800 rounded-2xl p-4">
            <canvas id="growthChart"></canvas>
        </div>
    </div>

    <!-- دستاوردها -->
    <div class="glass-card p-6">
        <h2 class="text-lg font-heading mb-4">🏆 دستاوردها</h2>
        @php
            $achievements = \App\Models\Achievement::where('user_id', $user->id)->get();
        @endphp
        @if($achievements->isEmpty())
            <p class="text-white/50 text-sm text-center py-4">هنوز دستاوردی ثبت نشده</p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach($achievements as $achievement)
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

    <!-- پیام‌ها -->
    <div class="glass-card p-6">
        <h2 class="text-lg font-heading mb-4">💌 پیام‌ها به {{ $user->name }}</h2>
        @auth
            <form id="messageForm" class="space-y-3 mb-6" onsubmit="submitMessage(event)">
                <textarea name="message" class="glass-input" rows="3" placeholder="پیام تشویقی خود را بنویسید..." required></textarea>
                <input type="text" name="sender_name" class="glass-input" placeholder="نام شما (اختیاری)">
                <button type="submit" class="btn-primary w-full text-sm">ارسال پیام 📨</button>
            </form>
        @else
            <p class="text-white/50 text-sm text-center mb-4">برای ارسال پیام باید وارد شوید</p>
        @endauth
        
        <div id="messagesList" class="space-y-3">
            <p class="text-white/50 text-sm text-center">در حال بارگذاری...</p>
        </div>
    </div>
</div>

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@push('scripts')
<script>
let chart = null;
let currentPeriod = 'month';
const username = '{{ $user->name }}';
const userId = {{ $user->id }};

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
        const result = await response.json();
        const persianLabels = result.labels.map(label => {
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
                    data: result.data,
                    borderColor: '#a855f7',
                    backgroundColor: 'rgba(168, 85, 247, 0.2)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 2,
                    pointHoverRadius: 5,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: 'rgba(255,255,255,0.1)' }, ticks: { color: 'rgba(255,255,255,0.6)', font: { family: 'Vazirmatn', size: 10 }, maxTicksLimit: 10 } },
                    y: { grid: { color: 'rgba(255,255,255,0.1)' }, ticks: { color: 'rgba(255,255,255,0.6)', font: { family: 'Vazirmatn', size: 10 } }, max: 100, min: 0 }
                }
            }
        });
    } catch (error) {
        console.error('خطا در دریافت داده‌ها:', error);
    }
}

async function submitMessage(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    try {
        const response = await fetch(`/chart/${username}/message`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(Object.fromEntries(formData))
        });
        const result = await response.json();
        if (result.success) {
            alert('✅ پیام شما ارسال شد! پس از تایید نمایش داده می‌شود.');
            e.target.reset();
        } else {
            alert('❌ ' + result.message);
        }
    } catch (error) {
        alert('خطا در ارسال پیام');
    }
}

async function loadMessages() {
    try {
        const response = await fetch(`/chart/${username}/messages`);
        const result = await response.json();
        const container = document.getElementById('messagesList');
        if (result.messages.length === 0) {
            container.innerHTML = '<p class="text-white/50 text-sm text-center">هنوز پیامی ثبت نشده</p>';
        } else {
            const escapeHtml = (text) => {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            };
            container.innerHTML = result.messages.map(msg => `
                <div class="glass-card p-4">
                    <div class="font-heading text-sm text-cyan-300 mb-1">${escapeHtml(msg.sender_name)}</div>
                    <p class="text-sm text-white/80 font-quote">"${escapeHtml(msg.message)}"</p>
                    <div class="text-xs text-white/40 mt-2">${new Date(msg.created_at).toLocaleDateString('fa-IR')}</div>
                </div>
            `).join('');
        }
    } catch (error) {
        console.error('خطا در بارگذاری پیام‌ها:', error);
    }
}

renderChart();
loadMessages();
</script>
@endpush
@endsection
