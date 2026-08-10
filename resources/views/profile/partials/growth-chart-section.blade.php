{{-- Growth Chart Section --}}
<div class="glass-card p-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-xl font-heading text-white flex items-center gap-2">
                <span class="text-cyan-400">📈</span> نمودار رشد
            </h2>
            <p class="text-xs text-white/50 mt-1 font-quote">پیشرفت همه عادت‌های تو در طول زمان</p>
        </div>
        
        {{-- Period Selector --}}
        <div class="flex flex-wrap gap-2">
            <button onclick="window.profileChart.updatePeriod('week')" 
                    class="chart-period-btn btn-secondary text-xs" 
                    data-period="week">
                ۱ هفته
            </button>
            <button onclick="window.profileChart.updatePeriod('month')" 
                    class="chart-period-btn btn-primary text-xs" 
                    data-period="month">
                ۱ ماه
            </button>
            <button onclick="window.profileChart.updatePeriod('six_months')" 
                    class="chart-period-btn btn-secondary text-xs" 
                    data-period="six_months">
                ۶ ماه
            </button>
            <button onclick="window.profileChart.updatePeriod('year')" 
                    class="chart-period-btn btn-secondary text-xs" 
                    data-period="year">
                ۱ سال
            </button>
        </div>
    </div>

    {{-- Chart Canvas --}}
    <div class="relative h-72 bg-gradient-to-br from-slate-900/50 to-slate-800/50 rounded-2xl p-4 border border-white/5">
        <canvas id="growthChart"></canvas>
    </div>

    {{-- Action Buttons --}}
    <div class="flex flex-col sm:flex-row gap-3 mt-6">
        <button onclick="window.profileChart.downloadStory()" 
                class="btn-primary text-sm flex-1 py-3 inline-flex items-center justify-center gap-2">
            <span>📸</span> دانلود تصویر استوری (9:16)
        </button>
        <button onclick="window.profileChart.refresh()" 
                class="btn-secondary text-sm inline-flex items-center justify-center gap-2">
            <span>🔄</span> بروزرسانی
        </button>
    </div>
</div>

<script>
// Profile Chart Module
(function() {
    let chart = null;
    let currentPeriod = 'month';
    let chartData = null;

    function toPersianDate(dateStr) {
        const date = new Date(dateStr);
        return date.toLocaleDateString('fa-IR', { year: 'numeric', month: '2-digit', day: '2-digit' });
    }

    async function fetchChartData(period) {
        try {
            const response = await fetch(`/api/chart-data/${period}`);
            if (!response.ok) throw new Error('Network response was not ok');
            return await response.json();
        } catch (error) {
            console.error('Error fetching chart data:', error);
            return null;
        }
    }

    async function updatePeriod(period) {
        currentPeriod = period;
        
        // Update button styles
        document.querySelectorAll('.chart-period-btn').forEach(btn => {
            btn.className = btn.dataset.period === period
                ? 'chart-period-btn btn-primary text-xs'
                : 'chart-period-btn btn-secondary text-xs';
        });
        
        await renderChart();
    }

    async function renderChart() {
        const data = await fetchChartData(currentPeriod);
        if (!data) return;
        
        chartData = data;
        
        const persianLabels = chartData.labels.map(label => {
            const [month, day] = label.split('/');
            const date = new Date(2024, parseInt(month) - 1, parseInt(day));
            return toPersianDate(date);
        });

        const ctx = document.getElementById('growthChart').getContext('2d');
        if (chart) chart.destroy();

        // Create gradient
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(168, 85, 247, 0.3)');
        gradient.addColorStop(1, 'rgba(168, 85, 247, 0.0)');

        chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: persianLabels,
                datasets: [{
                    label: 'درصد موفقیت',
                    data: chartData.data,
                    borderColor: '#a855f7',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#a855f7',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.95)',
                        titleFont: { family: 'Vazirmatn', size: 12, weight: 'bold' },
                        bodyFont: { family: 'Vazirmatn', size: 11 },
                        padding: 12,
                        cornerRadius: 10,
                        displayColors: false,
                        rtl: true,
                        titleAlign: 'center',
                        bodyAlign: 'center',
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y + '% موفقیت';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { 
                            color: 'rgba(255,255,255,0.05)',
                            drawBorder: false
                        },
                        ticks: { 
                            color: 'rgba(255,255,255,0.4)', 
                            font: { family: 'Vazirmatn', size: 10 },
                            maxTicksLimit: 8,
                            maxRotation: 0
                        }
                    },
                    y: {
                        grid: { 
                            color: 'rgba(255,255,255,0.05)',
                            drawBorder: false
                        },
                        ticks: { 
                            color: 'rgba(255,255,255,0.4)', 
                            font: { family: 'Vazirmatn', size: 10 },
                            callback: function(value) {
                                return value + '%';
                            }
                        },
                        max: 100,
                        min: 0
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
            }
        });
    }

    async function downloadStory() {
        if (!chartData) {
            alert('لطفاً صبر کنید تا نمودار بارگذاری شود');
            return;
        }

        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span>⏳</span> در حال ساخت تصویر...';
        btn.disabled = true;

        try {
            // Create story canvas
            const storyCanvas = document.createElement('canvas');
            storyCanvas.width = 1080;
            storyCanvas.height = 1920;
            const ctx = storyCanvas.getContext('2d');

            // Background gradient
            const bgGradient = ctx.createLinearGradient(0, 0, 0, 1920);
            bgGradient.addColorStop(0, '#1e1b4b');
            bgGradient.addColorStop(1, '#312e81');
            ctx.fillStyle = bgGradient;
            ctx.fillRect(0, 0, 1080, 1920);

            // Title
            ctx.font = 'bold 48px Vazirmatn';
            ctx.fillStyle = '#fff';
            ctx.textAlign = 'center';
            ctx.fillText('نمودار رشد من', 540, 150);

            // Draw chart area (simplified)
            ctx.drawImage(document.getElementById('growthChart'), 100, 300, 880, 600);

            // Download
            storyCanvas.toBlob(function(blob) {
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `growth-chart-${currentPeriod}.png`;
                a.click();
                URL.revokeObjectURL(url);
            });

            alert('✅ نمودار با موفقیت دانلود شد!');
        } catch (error) {
            console.error('Error downloading chart:', error);
            alert('❌ خطا در دانلود نمودار');
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }

    function refresh() {
        renderChart();
    }

    // Initialize on page load
    window.profileChart = {
        updatePeriod,
        downloadStory,
        refresh
    };

    // Auto-initialize
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', renderChart);
    } else {
        renderChart();
    }
})();
</script>
