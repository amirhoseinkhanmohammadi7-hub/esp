<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-6" :status="session('status')" />

    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold text-white">{{ __('Welcome Back') }}</h2>
        <p class="text-white/60 text-sm mt-2">{{ __('Sign in to continue your journey') }}</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm font-medium text-white/80 mb-2">{{ __('Email Address') }}</label>
            <input id="email" 
                   type="email" 
                   name="email" 
                   value="{{ old('email') }}" 
                   required 
                   autofocus 
                   autocomplete="username"
                   placeholder="you@example.com"
                   class="auth-input w-full px-4 py-3 rounded-xl text-white text-sm" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-medium text-white/80 mb-2">{{ __('Password') }}</label>
            <input id="password" 
                   type="password" 
                   name="password" 
                   required 
                   autocomplete="current-password"
                   placeholder="••••••••"
                   class="auth-input w-full px-4 py-3 rounded-xl text-white text-sm" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" 
                       type="checkbox" 
                       class="w-4 h-4 rounded border-white/20 bg-white/10 text-purple-500 focus:ring-purple-500 focus:ring-2" 
                       name="remember">
                <span class="ms-2 text-sm text-white/60">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm auth-link hover:text-purple-400" href="{{ route('password.request') }}">
                    {{ __('Forgot password?') }}
                </a>
            @endif
        </div>

        <!-- Submit Button -->
        <button type="submit" class="auth-btn-gradient w-full py-3.5 rounded-xl text-white font-semibold text-sm">
            {{ __('Sign In') }}
        </button>

        <!-- Register Link -->
        <div class="text-center pt-4 border-t border-white/10">
            <p class="text-sm text-white/60">
                {{ __("Don't have an account?") }}
                <a href="{{ route('register') }}" class="auth-link font-medium hover:text-purple-400">
                    {{ __('Sign up') }}
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
