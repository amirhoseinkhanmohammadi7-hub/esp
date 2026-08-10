@extends('layouts.app')
@section('title', 'پروفایل من')

@section('content')
<div class="max-w-5xl mx-auto space-y-8">
    <!-- هدر صفحه -->
    <div class="text-center mb-8">
        <h1 class="text-3xl font-heading bg-gradient-to-r from-purple-400 via-pink-400 to-cyan-400 bg-clip-text text-transparent">
            پروفایل من
        </h1>
        <p class="text-white/50 text-sm mt-2 font-quote">مسیر پیشرفت و رشد شخصی تو</p>
    </div>

    <!-- کارت اصلی پروفایل -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- ستون راست: اطلاعات کاربری -->
        <div class="lg:col-span-2 space-y-6">
            @include('profile.partials.profile-info-card')
            @include('profile.partials.update-password-section')
        </div>

        <!-- ستون چپ: آمار و دستاوردها -->
        <div class="space-y-6">
            @include('profile.partials.stats-sidebar')
            @include('profile.partials.achievements-card')
        </div>
    </div>

    <!-- نمودار رشد -->
    @include('profile.partials.growth-chart-section')

    <!-- منطقه خطر: حذف حساب -->
    @include('profile.partials.delete-account-section')
</div>

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
@endpush

@push('scripts')
<script src="{{ asset('js/profile.js') }}"></script>
@endpush
@endsection
