{{-- Delete Account Section --}}
<div class="glass-card p-6 border-red-500/20 bg-gradient-to-br from-red-950/30 to-slate-900/50">
    <div class="flex items-center gap-3 mb-4">
        <div class="w-12 h-12 rounded-full bg-red-500/20 flex items-center justify-center">
            <span class="text-2xl">⚠️</span>
        </div>
        <div>
            <h2 class="text-xl font-heading text-red-300">منطقه خطر</h2>
            <p class="text-xs text-white/50">اقدامات غیرقابل بازگشت</p>
        </div>
    </div>

    <div class="space-y-4">
        {{-- Warning Message --}}
        <div class="p-4 bg-red-500/10 border border-red-500/30 rounded-xl">
            <p class="text-sm text-red-200 leading-relaxed">
                <span class="font-bold">توجه:</span> با حذف حساب کاربری، تمام اطلاعات شما شامل:
            </p>
            <ul class="text-xs text-red-300/80 mt-2 space-y-1 mr-4 list-disc">
                <li>تمام عادت‌ها و پیگیری‌ها</li>
                <li>دستاوردها و افتخارات</li>
                <li>پیام‌ها و تعاملات</li>
                <li>اطلاعات پروفایل</li>
            </ul>
            <p class="text-sm text-red-200 mt-3 font-bold">
                برای همیشه پاک خواهد شد و قابل بازگشت نیست!
            </p>
        </div>

        {{-- Delete Form --}}
        <form method="POST" action="{{ route('profile.destroy') }}" 
              onsubmit="return confirmDelete()"
              class="space-y-4">
            @csrf
            @method('DELETE')
            
            <div>
                <label for="delete_password" class="block text-sm font-medium mb-2 text-white/70">
                    برای تایید، رمز عبور خود را وارد کنید:
                </label>
                <input type="password" 
                       id="delete_password" 
                       name="password" 
                       class="glass-input @error('userDeletion.password', 'password') border-red-500/50 @enderror"
                       placeholder="رمز عبور خود را وارد کنید..."
                       required>
                @error('password')
                    <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                @enderror
                @error('userDeletion.password')
                    <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" 
                    class="w-full bg-red-500/20 hover:bg-red-500/30 text-red-300 border border-red-500/30 px-5 py-3.5 rounded-xl text-sm font-medium transition-all duration-300 flex items-center justify-center gap-2 group">
                <span class="group-hover:scale-110 transition-transform">🗑️</span>
                <span>حذف دائمی حساب کاربری</span>
            </button>
        </form>

        {{-- Alternative Actions --}}
        <div class="pt-4 mt-4 border-t border-white/10">
            <p class="text-xs text-white/40 text-center mb-3">یا می‌توانید:</p>
            <div class="flex flex-col sm:flex-row gap-2">
                <a href="{{ route('habits.index') }}" 
                   class="btn-secondary text-xs flex-1 inline-flex items-center justify-center gap-2">
                    <span>📋</span> مدیریت عادت‌ها
                </a>
                <a href="#" 
                   onclick="alert('به زودی امکان غیرفعال کردن موقت حساب فراهم می‌شود')"
                   class="btn-secondary text-xs flex-1 inline-flex items-center justify-center gap-2">
                    <span>⏸️</span> غیرفعال کردن موقت
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete() {
    const passwordInput = document.getElementById('delete_password');
    if (!passwordInput.value) {
        alert('لطفاً رمز عبور خود را وارد کنید');
        passwordInput.focus();
        return false;
    }
    
    // Double confirmation
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

// Add visual feedback on password input
const deletePasswordInput = document.getElementById('delete_password');
if (deletePasswordInput) {
    deletePasswordInput.addEventListener('focus', function() {
        this.parentElement.classList.add('ring-2', 'ring-red-500/30');
    });
    
    deletePasswordInput.addEventListener('blur', function() {
        this.parentElement.classList.remove('ring-2', 'ring-red-500/30');
    });
}
</script>
