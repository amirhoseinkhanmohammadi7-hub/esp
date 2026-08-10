{{-- Update Password Section - Modal Version --}}
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-heading text-white flex items-center gap-2">
            <span class="text-cyan-400">🔒</span> تغییر رمز عبور
        </h2>
        <button onclick="closeModal(null, 'password-modal')" class="text-white/50 hover:text-white transition-colors">
            <span class="text-2xl">×</span>
        </button>
    </div>
    
    <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        @method('PUT')

        {{-- Current Password --}}
        <div>
            <label for="current_password" class="block text-sm font-medium mb-2 text-white/80">رمز عبور فعلی</label>
            <input type="password" 
                   id="current_password" 
                   name="current_password" 
                   class="glass-input @error('updatePassword.current_password', 'password', 'current_password') border-red-500/50 @enderror"
                   required
                   autocomplete="current-password"
                   placeholder="••••••••">
            @error('current_password', 'password', 'current_password')
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- New Password --}}
        <div>
            <label for="password" class="block text-sm font-medium mb-2 text-white/80">رمز عبور جدید</label>
            <input type="password" 
                   id="password" 
                   name="password" 
                   class="glass-input @error('updatePassword.password', 'password') border-red-500/50 @enderror"
                   required
                   autocomplete="new-password"
                   placeholder="حداقل ۸ کاراکتر">
            @error('password')
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
            @enderror
            
            {{-- Password Strength Indicator --}}
            <div id="password_strength" class="mt-2 hidden">
                <div class="flex gap-1 h-1.5">
                    <div class="strength-bar flex-1 bg-white/10 rounded-full"></div>
                    <div class="strength-bar flex-1 bg-white/10 rounded-full"></div>
                    <div class="strength-bar flex-1 bg-white/10 rounded-full"></div>
                    <div class="strength-bar flex-1 bg-white/10 rounded-full"></div>
                </div>
                <p class="text-[10px] text-white/40 mt-1">قدرت رمز: <span id="strength_text">ضعیف</span></p>
            </div>
        </div>

        {{-- Confirm Password --}}
        <div>
            <label for="password_confirmation" class="block text-sm font-medium mb-2 text-white/80">تایید رمز عبور جدید</label>
            <input type="password" 
                   id="password_confirmation" 
                   name="password_confirmation" 
                   class="glass-input"
                   required
                   autocomplete="new-password"
                   placeholder="تکرار رمز عبور">
        </div>

        {{-- Submit Button --}}
        <div class="flex items-center gap-3 pt-3">
            <button type="submit" class="btn-primary text-sm flex-1 sm:flex-none">
                <span>🔐</span> بروزرسانی رمز عبور
            </button>
            
            @if (session('status') === 'password-updated')
                <span class="text-emerald-400 text-xs flex items-center gap-1"
                      x-data="{ show: true }"
                      x-show="show"
                      x-transition
                      x-init="setTimeout(() => show = false, 3000)">
                    ✅ رمز عبور تغییر کرد
                </span>
            @endif
        </div>
    </form>
</div>

<script>
// Password strength checker
const passwordInput = document.getElementById('password');
const strengthContainer = document.getElementById('password_strength');
const strengthBars = document.querySelectorAll('.strength-bar');
const strengthText = document.getElementById('strength_text');

if (passwordInput) {
    passwordInput.addEventListener('input', function() {
        const value = this.value;
        if (value.length === 0) {
            strengthContainer.classList.add('hidden');
            return;
        }
        
        strengthContainer.classList.remove('hidden');
        
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
        
        strengthText.textContent = texts[Math.min(Math.max(strength - 1, 0), 4)];
        strengthText.className = `text-[10px] ${strength >= 4 ? 'text-emerald-400' : strength >= 2 ? 'text-yellow-400' : 'text-red-400'}`;
    });
}
</script>
