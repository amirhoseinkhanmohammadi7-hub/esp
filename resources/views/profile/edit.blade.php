@extends('layouts.app')
@section('title', 'پروفایل من')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- هدر صفحه -->
    <div class="text-center mb-8">
        <h1 class="text-3xl font-heading bg-gradient-to-r from-purple-400 via-pink-400 to-cyan-400 bg-clip-text text-transparent">
            پروفایل من
        </h1>
        <p class="text-white/50 text-sm mt-2 font-quote">مدیریت تنظیمات و اطلاعات شخصی</p>
    </div>

    <!-- کارت اصلی پروفایل - فقط دکمه‌ها -->
    <div class="glass-card p-6">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-500/20 to-purple-600/20 flex items-center justify-center">
                <span class="text-2xl">👤</span>
            </div>
            <div class="flex-1">
                <h2 class="text-lg font-heading text-white">{{ $user->name }}</h2>
                <p class="text-xs text-white/50">{{ $user->email }}</p>
            </div>
            <button onclick="openModal('profile-modal')" class="btn-primary text-sm">
                <span>✏️</span> ویرایش
            </button>
        </div>

        <!-- دکمه‌های سریع -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <button onclick="openModal('password-modal')" class="p-4 bg-white/5 hover:bg-white/10 rounded-xl border border-white/10 transition-all group">
                <div class="text-2xl mb-2 group-hover:scale-110 transition-transform">🔒</div>
                <p class="text-xs text-white/70">تغییر رمز</p>
            </button>
            
            <button onclick="openModal('stats-modal')" class="p-4 bg-white/5 hover:bg-white/10 rounded-xl border border-white/10 transition-all group">
                <div class="text-2xl mb-2 group-hover:scale-110 transition-transform">📊</div>
                <p class="text-xs text-white/70">آمار من</p>
            </button>
            
            <button onclick="openModal('achievements-modal')" class="p-4 bg-white/5 hover:bg-white/10 rounded-xl border border-white/10 transition-all group">
                <div class="text-2xl mb-2 group-hover:scale-110 transition-transform">🏆</div>
                <p class="text-xs text-white/70">دستاوردها</p>
            </button>
            
            <button onclick="openModal('chart-modal')" class="p-4 bg-white/5 hover:bg-white/10 rounded-xl border border-white/10 transition-all group">
                <div class="text-2xl mb-2 group-hover:scale-110 transition-transform">📈</div>
                <p class="text-xs text-white/70">نمودار رشد</p>
            </button>
        </div>

        <!-- منطقه خطر -->
        <div class="mt-6 pt-6 border-t border-white/10">
            <button onclick="openModal('delete-modal')" class="w-full p-4 bg-red-500/10 hover:bg-red-500/20 rounded-xl border border-red-500/30 transition-all flex items-center justify-center gap-2 group">
                <span class="group-hover:scale-110 transition-transform">⚠️</span>
                <span class="text-sm text-red-300">حذف حساب کاربری</span>
            </button>
        </div>
    </div>
</div>

<!-- Modal: Edit Profile -->
<div id="profile-modal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden" onclick="closeModal(event, 'profile-modal')">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-slate-900 rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto custom-scrollbar border border-white/10" onclick="event.stopPropagation()">
            @include('profile.partials.profile-info-card')
        </div>
    </div>
</div>

<!-- Modal: Change Password -->
<div id="password-modal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden" onclick="closeModal(event, 'password-modal')">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-slate-900 rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto custom-scrollbar border border-white/10" onclick="event.stopPropagation()">
            @include('profile.partials.update-password-section')
        </div>
    </div>
</div>

<!-- Modal: Stats -->
<div id="stats-modal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden" onclick="closeModal(event, 'stats-modal')">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-slate-900 rounded-2xl max-w-md w-full max-h-[90vh] overflow-y-auto custom-scrollbar border border-white/10" onclick="event.stopPropagation()">
            @include('profile.partials.stats-sidebar')
        </div>
    </div>
</div>

<!-- Modal: Achievements -->
<div id="achievements-modal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden" onclick="closeModal(event, 'achievements-modal')">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-slate-900 rounded-2xl max-w-md w-full max-h-[90vh] overflow-y-auto custom-scrollbar border border-white/10" onclick="event.stopPropagation()">
            @include('profile.partials.achievements-card')
        </div>
    </div>
</div>

<!-- Modal: Growth Chart -->
<div id="chart-modal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden" onclick="closeModal(event, 'chart-modal')">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-slate-900 rounded-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto custom-scrollbar border border-white/10" onclick="event.stopPropagation()">
            @include('profile.partials.growth-chart-section')
        </div>
    </div>
</div>

<!-- Modal: Delete Account -->
<div id="delete-modal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden" onclick="closeModal(event, 'delete-modal')">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-slate-900 rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto custom-scrollbar border border-white/10" onclick="event.stopPropagation()">
            @include('profile.partials.delete-account-section')
        </div>
    </div>
</div>

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<style>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(168, 85, 247, 0.4);
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(168, 85, 247, 0.6);
}

/* Modal Animation */
.modal-content {
    animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-20px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}
</style>
@endpush

@push('scripts')
<script>
// Modal Functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        // Add animation class
        const content = modal.querySelector('.bg-slate-900');
        if (content) content.classList.add('modal-content');
        
        // Initialize chart if opening chart modal
        if (modalId === 'chart-modal' && window.profileChart) {
            setTimeout(() => window.profileChart.refresh(), 100);
        }
    }
}

function closeModal(event, modalId) {
    if (!event || event.target === document.getElementById(modalId)) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }
}

// Close modal with ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const openModals = document.querySelectorAll('[id$="-modal"]:not(.hidden)');
        openModals.forEach(modal => {
            modal.classList.add('hidden');
        });
        document.body.style.overflow = '';
    }
});

// Auto-open modal on status message
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const modal = urlParams.get('modal');
    if (modal) {
        openModal(modal + '-modal');
    }
});
</script>
<script src="{{ asset('js/profile.js') }}"></script>
@endpush
@endsection
