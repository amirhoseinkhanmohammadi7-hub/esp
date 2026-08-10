{{-- Profile Information Card --}}
<div class="glass-card p-6">
    <div class="flex items-center gap-4 mb-6">
        <h2 class="text-xl font-heading text-white">
            <span class="text-purple-400">👤</span> اطلاعات پروفایل
        </h2>
    </div>

    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-5">
        @csrf
        @method('PATCH')

        {{-- Profile Picture Upload --}}
        <div class="flex flex-col sm:flex-row items-center gap-5 pb-5 border-b border-white/10">
            <div class="relative group">
                <img src="{{ $user->profile_picture_url }}" 
                     alt="{{ $user->name }}" 
                     class="w-24 h-24 rounded-full object-cover border-2 border-purple-500/30 shadow-lg shadow-purple-500/10">
                <div class="absolute inset-0 bg-black/50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center cursor-pointer">
                    <span class="text-white text-xs">تغییر</span>
                </div>
            </div>
            <div class="flex-1 w-full">
                <label class="block text-sm font-medium mb-2 text-white/80">عکس پروفایل</label>
                <input type="file" 
                       name="profile_picture" 
                       id="profile_picture"
                       class="hidden" 
                       accept="image/*"
                       onchange="previewImage(this)">
                <button type="button" 
                        onclick="document.getElementById('profile_picture').click()"
                        class="btn-secondary text-xs inline-flex items-center gap-2">
                    <span>📷</span> انتخاب عکس جدید
                </button>
                <p class="text-[10px] text-white/40 mt-2">فرمت‌های مجاز: JPEG, PNG, JPG, GIF, WebP — حداکثر ۲ مگابایت</p>
                
                {{-- Image Preview --}}
                <div id="image_preview" class="hidden mt-3">
                    <img id="preview_img" class="w-16 h-16 rounded-full object-cover border border-white/20">
                </div>
            </div>
        </div>

        {{-- Name Field --}}
        <div>
            <label for="name" class="block text-sm font-medium mb-2 text-white/80">نام و نام خانوادگی</label>
            <input type="text" 
                   id="name" 
                   name="name" 
                   class="glass-input @error('name') border-red-500/50 @enderror" 
                   value="{{ old('name', $user->name) }}" 
                   required
                   placeholder="مثال: علی محمدی">
            @error('name')
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Email Field --}}
        <div>
            <label for="email" class="block text-sm font-medium mb-2 text-white/80">آدرس ایمیل</label>
            <input type="email" 
                   id="email" 
                   name="email" 
                   class="glass-input @error('email') border-red-500/50 @enderror" 
                   value="{{ old('email', $user->email) }}" 
                   required
                   placeholder="example@email.com">
            @error('email')
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
            @enderror
            
            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2 p-3 bg-yellow-500/10 border border-yellow-500/30 rounded-lg">
                    <p class="text-yellow-300 text-xs">
                        ⚠️ ایمیل شما تایید نشده است.
                        <button form="send-verification" class="underline hover:text-yellow-200">ارسال مجدد ایمیل تایید</button>
                    </p>
                </div>
            @endif
        </div>

        {{-- Bio Field --}}
        <div>
            <label for="bio" class="block text-sm font-medium mb-2 text-white/80">بیوگرافی</label>
            <textarea id="bio" 
                      name="bio" 
                      rows="4" 
                      class="glass-input @error('bio') border-red-500/50 @enderror" 
                      placeholder="درباره خودت بنویس...">{{ old('bio', $user->bio) }}</textarea>
            <div class="flex justify-between items-center mt-1">
                <p class="text-[10px] text-white/40">حداکثر ۵۰۰ کاراکتر</p>
                <span id="bio_counter" class="text-[10px] text-white/40">0/500</span>
            </div>
            @error('bio')
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Submit Button --}}
        <div class="flex items-center gap-3 pt-3">
            <button type="submit" class="btn-primary text-sm flex-1 sm:flex-none">
                <span>💾</span> ذخیره تغییرات
            </button>
            
            @if (session('status') === 'profile-updated')
                <span class="text-emerald-400 text-xs flex items-center gap-1"
                      x-data="{ show: true }"
                      x-show="show"
                      x-transition
                      x-init="setTimeout(() => show = false, 3000)">
                    ✅ ذخیره شد
                </span>
            @endif
        </div>
    </form>
</div>

{{-- Hidden verification form --}}
<form id="send-verification" method="post" action="{{ route('verification.send') }}" class="hidden">
    @csrf
</form>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview_img').src = e.target.result;
            document.getElementById('image_preview').classList.remove('hidden');
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Bio character counter
const bioTextarea = document.getElementById('bio');
const bioCounter = document.getElementById('bio_counter');
if (bioTextarea) {
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
</script>
