/**
 * Profile Page JavaScript Module
 * Handles all interactive functionality for the profile page
 */

(function() {
    'use strict';

    // ==================== Chart Module ====================
    const ProfileChart = (function() {
        let chart = null;
        let currentPeriod = 'month';
        let chartData = null;

        function toPersianDate(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('fa-IR', { 
                year: 'numeric', 
                month: '2-digit', 
                day: '2-digit' 
            });
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

            const ctx = document.getElementById('growthChart');
            if (!ctx) return;
            
            const context = ctx.getContext('2d');
            if (chart) chart.destroy();

            // Create gradient
            const gradient = context.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(168, 85, 247, 0.3)');
            gradient.addColorStop(1, 'rgba(168, 85, 247, 0.0)');

            chart = new Chart(context, {
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

            const btn = event.target.closest('button');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span>⏳</span> در حال ساخت تصویر...';
            btn.disabled = true;

            try {
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

                // Draw chart area
                const chartEl = document.getElementById('growthChart');
                if (chartEl) {
                    ctx.drawImage(chartEl, 100, 300, 880, 600);
                }

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
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', renderChart);
        } else {
            renderChart();
        }

        return {
            updatePeriod,
            downloadStory,
            refresh
        };
    })();

    // ==================== Bio Counter Module ====================
    const BioCounter = (function() {
        function init() {
            const bioTextarea = document.getElementById('bio');
            const bioCounter = document.getElementById('bio_counter');
            
            if (!bioTextarea || !bioCounter) return;

            bioTextarea.addEventListener('input', function() {
                const count = this.value.length;
                bioCounter.textContent = `${count}/500`;
                if (count > 500) {
                    bioCounter.classList.add('text-red-400');
                } else {
                    bioCounter.classList.remove('text-red-400');
                }
            });

            // Initialize counter
            bioCounter.textContent = `${bioTextarea.value.length}/500`;
        }

        return { init };
    })();

    // ==================== Image Preview Module ====================
    const ImagePreview = (function() {
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewImg = document.getElementById('preview_img');
                    const imagePreview = document.getElementById('image_preview');
                    
                    if (previewImg && imagePreview) {
                        previewImg.src = e.target.result;
                        imagePreview.classList.remove('hidden');
                    }
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        return { previewImage };
    })();

    // ==================== Password Strength Module ====================
    const PasswordStrength = (function() {
        function init() {
            const passwordInput = document.getElementById('password');
            const strengthContainer = document.getElementById('password_strength');
            const strengthBars = document.querySelectorAll('.strength-bar');
            const strengthText = document.getElementById('strength_text');

            if (!passwordInput) return;

            passwordInput.addEventListener('input', function() {
                const value = this.value;
                if (value.length === 0) {
                    if (strengthContainer) strengthContainer.classList.add('hidden');
                    return;
                }
                
                if (strengthContainer) strengthContainer.classList.remove('hidden');
                
                let strength = 0;
                if (value.length >= 8) strength++;
                if (value.length >= 12) strength++;
                if (/[A-Z]/.test(value)) strength++;
                if (/[0-9]/.test(value)) strength++;
                if (/[^A-Za-z0-9]/.test(value)) strength++;
                
                const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-lime-500', 'bg-emerald-500'];
                const texts = ['خیلی ضعیف', 'ضعیف', 'متوسط', 'خوب', 'قوی'];
                
                strengthBars.forEach((bar, index) => {
                    if (index < Math.min(strength, 4)) {
                        bar.className = `strength-bar flex-1 ${colors[Math.min(strength - 1, 4)]} rounded-full`;
                    } else {
                        bar.className = 'strength-bar flex-1 bg-white/10 rounded-full';
                    }
                });
                
                if (strengthText) {
                    strengthText.textContent = texts[Math.min(Math.max(strength - 1, 0), 4)];
                    strengthText.className = `text-[10px] ${strength >= 4 ? 'text-emerald-400' : strength >= 2 ? 'text-yellow-400' : 'text-red-400'}`;
                }
            });
        }

        return { init };
    })();

    // ==================== Delete Confirmation Module ====================
    const DeleteConfirmation = (function() {
        function confirmDelete() {
            const passwordInput = document.getElementById('delete_password');
            if (!passwordInput || !passwordInput.value) {
                alert('لطفاً رمز عبور خود را وارد کنید');
                if (passwordInput) passwordInput.focus();
                return false;
            }
            
            const confirmed = confirm(
                '⚠️ آیا کاملاً مطمئن هستید؟\n\n' +
                'این عملیات:\n' +
                '• غیرقابل بازگشت است\n' +
                '• تمام داده‌های شما را حذف می‌کند\n' +
                '• حساب شما برای همیشه بسته می‌شود\n\n' +
                'برای تایید نهایی OK را بزنید.'
            );
            
            return confirmed;
        }

        function init() {
            const deletePasswordInput = document.getElementById('delete_password');
            if (!deletePasswordInput) return;

            deletePasswordInput.addEventListener('focus', function() {
                this.parentElement.classList.add('ring-2', 'ring-red-500/30');
            });
            
            deletePasswordInput.addEventListener('blur', function() {
                this.parentElement.classList.remove('ring-2', 'ring-red-500/30');
            });
        }

        return { confirmDelete, init };
    })();

    // ==================== Achievements Toggle Module ====================
    const AchievementsToggle = (function() {
        function toggleAllAchievements() {
            const container = document.querySelector('.custom-scrollbar');
            const toggleText = document.getElementById('toggle_text');
            
            if (!container) return;
            
            if (container.style.maxHeight === 'none') {
                container.style.maxHeight = '24rem';
                if (toggleText) toggleText.textContent = 'نمایش همه';
            } else {
                container.style.maxHeight = 'none';
                if (toggleText) toggleText.textContent = 'نمایش کمتر';
            }
        }

        function init() {
            const container = document.querySelector('.custom-scrollbar');
            const achievementsCount = window.achievementsCount || 0;
            
            if (container && achievementsCount > 5) {
                container.style.maxHeight = '24rem';
            }
        }

        return { toggleAllAchievements, init };
    })();

    // ==================== Initialize All Modules ====================
    function init() {
        BioCounter.init();
        PasswordStrength.init();
        DeleteConfirmation.init();
        AchievementsToggle.init();
        
        // Expose modules globally for inline handlers
        window.profileChart = ProfileChart;
        window.previewImage = ImagePreview.previewImage;
        window.confirmDelete = DeleteConfirmation.confirmDelete;
        window.toggleAllAchievements = AchievementsToggle.toggleAllAchievements;
    }

    // Run initialization
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
