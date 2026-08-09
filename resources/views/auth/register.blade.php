<x-guest-layout>
    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold text-white">{{ __('Create Account') }}</h2>
        <p class="text-white/60 text-sm mt-2">{{ __('Join us and start building better habits') }}</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <!-- Name -->
        <div>
            <label for="name" class="block text-sm font-medium text-white/80 mb-2">{{ __('Full Name') }}</label>
            <input id="name" 
                   type="text" 
                   name="name" 
                   value="{{ old('name') }}" 
                   required 
                   autofocus 
                   autocomplete="name"
                   placeholder="John Doe"
                   class="auth-input w-full px-4 py-3 rounded-xl text-white text-sm" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm font-medium text-white/80 mb-2">{{ __('Email Address') }}</label>
            <input id="email" 
                   type="email" 
                   name="email" 
                   value="{{ old('email') }}" 
                   required 
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
                   autocomplete="new-password"
                   placeholder="••••••••"
                   class="auth-input w-full px-4 py-3 rounded-xl text-white text-sm" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
            <p class="text-xs text-white/40 mt-2">{{ __('Must be at least 8 characters') }}</p>
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-white/80 mb-2">{{ __('Confirm Password') }}</label>
            <input id="password_confirmation" 
                   type="password" 
                   name="password_confirmation" 
                   required 
                   autocomplete="new-password"
                   placeholder="••••••••"
                   class="auth-input w-full px-4 py-3 rounded-xl text-white text-sm" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Submit Button -->
        <button type="submit" class="auth-btn-gradient w-full py-3.5 rounded-xl text-white font-semibold text-sm mt-2">
            {{ __('Create Account') }}
        </button>

        <!-- Login Link -->
        <div class="text-center pt-4 border-t border-white/10">
            <p class="text-sm text-white/60">
                {{ __('Already have an account?') }}
                <a href="{{ route('login') }}" class="auth-link font-medium hover:text-purple-400">
                    {{ __('Sign in') }}
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
