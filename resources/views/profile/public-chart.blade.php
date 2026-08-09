<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>نمودار رشد {{ $user->name }} - espira</title>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>
<body class="font-body antialiased min-h-screen">
    <div class="fixed inset-0 -z-10 bg-slate-950">
        <div class="absolute top-0 -left-40 w-96 h-96 bg-purple-600 rounded-full mix-blend-screen filter blur-[100px] opacity-20 animate-blob"></div>
        <div class="absolute top-0 -right-40 w-96 h-96 bg-cyan-600 rounded-full mix-blend-screen filter blur-[100px] opacity-20 animate-blob" style="animation-delay: 2s"></div>
    </div>

    <div class="min-h-screen py-8 px-4 max-w-4xl mx-auto">
        <!-- هدر -->
        <div class="glass-card p-6 mb-6 text-center">
            <div class="font-logo text-3xl mb-2">espira</div>
            <h1 class="text-xl font-heading mb-1">نمودار رشد {{ $user->name }}</h1>
            <p class="text-white/50 text-sm">پیشرفت واقعی، قدم به قدم</p>
        </div>

        <!-- نمودار -->
        <div class="glass-card p-6 mb-6">
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
            <div class="relative h-72 bg-gradient-to-br from-slate-900 to-slate-800 rounded-2xl p-4" id="chartContainer">
                <canvas id="growthChart"></canvas>
            </div>

        </div>

        <!-- ری‌اکشن‌ها (کوچک مثل تلگرام) -->
        <div class="glass-card p-6 mb-6">
            <h2 class="text-lg font-heading mb-4 text-center">به این مسیر ری‌اکشن بده</h2>
            
            <div class="flex justify-center gap-2 mb-6 flex-wrap">
                <button onclick="react('👍')" class="reaction-btn glass-card px-3 py-2 text-lg hover:scale-110 transition flex items-center gap-1">
                    <span>👍</span>
                    <span class="text-xs text-white/60" id="count-👍">{{ $reactionsByType['👍'] ?? 0 }}</span>
                </button>
                <button onclick="react('🔥')" class="reaction-btn glass-card px-3 py-2 text-lg hover:scale-110 transition flex items-center gap-1">
                    <span>🔥</span>
                    <span class="text-xs text-white/60" id="count-🔥">{{ $reactionsByType['🔥'] ?? 0 }}</span>
                </button>
                <button onclick="react('💪')" class="reaction-btn glass-card px-3 py-2 text-lg hover:scale-110 transition flex items-center gap-1">
                    <span>💪</span>
                    <span class="text-xs text-white/60" id="count-💪">{{ $reactionsByType['💪'] ?? 0 }}</span>
                </button>
                <button onclick="react('⭐')" class="reaction-btn glass-card px-3 py-2 text-lg hover:scale-110 transition flex items-center gap-1">
                    <span>⭐</span>
                    <span class="text-xs text-white/60" id="count-⭐">{{ $reactionsByType['⭐'] ?? 0 }}</span>
                </button>
                <button onclick="react('❤️')" class="reaction-btn glass-card px-3 py-2 text-lg hover:scale-110 transition flex items-center gap-1">
                    <span>❤️</span>
                    <span class="text-xs text-white/60" id="count-❤️">{{ $reactionsByType['❤️'] ?? 0 }}</span>
                </button>
            </div>

            <form id="reactForm" class="space-y-3" onsubmit="submitReaction(event)">
                <input type="text" name="user_name" class="glass-input" placeholder="نام شما (اختیاری)" value="{{ old('user_name') }}">
                <button type="submit" class="btn-primary w-full text-sm">ثبت ری‌اکشن ✨</button>
            </form>
        </div>

        <!-- لیست ری‌اکشن‌ها -->
        <div class="glass-card p-6 mb-6">
            <h2 class="text-lg font-heading mb-4">ری‌اکشن‌ها ({{ $reactions->count() }})</h2>
            <div id="reactionsList" class="flex flex-wrap gap-2">
                @foreach($reactions as $reaction)
                    <div class="glass-card px-3 py-1.5 flex items-center gap-2 text-sm">
                        <span class="text-lg">{{ $reaction->reaction_type }}</span>
                        <span class="text-xs text-white/60">{{ $reaction->user_name }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- ارسال پیام -->
        <div class="glass-card p-6 mb-6">
            <h2 class="text-lg font-heading mb-4">💬 پیام به {{ $user->name }}</h2>
            <form id="messageForm" class="space-y-3" onsubmit="submitMessage(event)">
                <textarea name="message" class="glass-input" rows="3" placeholder="پیام تشویقی خود را بنویسید..." required></textarea>
                <input type="text" name="sender_name" class="glass-input" placeholder="نام شما (اختیاری)">
                <button type="submit" class="btn-primary w-full text-sm">ارسال پیام 📨</button>
            </form>
        </div>

        <!-- پیام‌های تایید شده -->
        <div class="glass-card p-6 mb-6">
            <h2 class="text-lg font-heading mb-4">💌 پیام‌ها</h2>
            <div id="messagesList" class="space-y-3">
                <p class="text-white/50 text-sm text-center">در حال بارگذاری...</p>
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

        <div class="text-center mt-8 text-white/30 text-xs font-logo">
            espira.top — مسیر پیشرفت تو
        </div>
    </div>

    <script>
        let chart = null;
        let currentPeriod = 'month';
        let selectedReaction = null;
        const username = '{{ $user->name }}';

        // تبدیل تاریخ میلادی به شمسی
        function toPersianDate(dateStr) {
            const date = new Date(dateStr);
            const options = { year: 'numeric', month: '2-digit', day: '2-digit' };
            return date.toLocaleDateString('fa-IR', options);
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
                
                // تبدیل تاریخ‌ها به شمسی
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
                            pointBackgroundColor: '#a855f7',
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
                                grid: { color: 'rgba(255,255,255,0.1)' },
                                ticks: { 
                                    color: 'rgba(255,255,255,0.6)', 
                                    font: { family: 'Vazirmatn', size: 10 }, 
                                    maxTicksLimit: 10 
                                }
                            },
                            y: {
                                grid: { color: 'rgba(255,255,255,0.1)' },
                                ticks: { 
                                    color: 'rgba(255,255,255,0.6)', 
                                    font: { family: 'Vazirmatn', size: 10 } 
                                },
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

        function react(emoji) {
            selectedReaction = emoji;
            document.querySelectorAll('.reaction-btn').forEach(btn => {
                btn.classList.remove('ring-2', 'ring-purple-400');
            });
            event.currentTarget.classList.add('ring-2', 'ring-purple-400');
        }

        async function submitReaction(e) {
            e.preventDefault();
            if (!selectedReaction) {
                alert('لطفاً یک ایموجی انتخاب کنید!');
                return;
            }

            const formData = new FormData(e.target);
            formData.append('reaction', selectedReaction);

            try {
                const response = await fetch(`/chart/${username}/react`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(Object.fromEntries(formData))
                });

                const result = await response.json();
                if (result.success) {
                    alert('✅ ری‌اکشن شما ثبت شد!');
                    location.reload();
                } else {
                    alert('❌ ' + result.message);
                }
            } catch (error) {
                alert('خطا در ثبت ری‌اکشن');
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
                    // Escape HTML to prevent XSS
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

        async function downloadChart() {
            const container = document.getElementById('chartContainer');
            const btn = event.target;
            btn.innerText = '⏳ در حال ساخت تصویر...';
            
            const canvas = await html2canvas(container, {
                scale: 3,
                backgroundColor: '#0f172a',
                useCORS: true,
                logging: false
            });
            
            const link = document.createElement('a');
            link.download = `espira-chart-${username}.png`;
            link.href = canvas.toDataURL('image/png');
            link.click();
            btn.innerText = '📸 دانلود نمودار برای استوری';
        }

        // لود اولیه
        renderChart();
        loadMessages();
    </script>
</body>
</html>
