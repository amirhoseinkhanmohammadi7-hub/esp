<nav x-data="{ open: false }" class="border-b border-white/10 bg-white/5 backdrop-blur-xl">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-14">
            <div class="flex items-center">
                <a href="{{ route('habits.index') }}" class="font-logo text-xl text-white/90 tracking-wider">
                    espira
                </a>
                <div class="hidden sm:flex sm:items-center sm:ms-8 space-x-6 space-x-reverse">
                    <a href="{{ route('habits.index') }}" class="text-sm text-white/70 hover:text-white transition {{ request()->routeIs('habits.index') ? 'text-white font-semibold' : '' }}">
                        عادت‌های من
                    </a>
                    <a href="{{ route('profile.edit') }}" class="text-sm text-white/70 hover:text-white transition {{ request()->routeIs('profile.edit') ? 'text-white font-semibold' : '' }}">
                        پروفایل
                    </a>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-lg text-white/80 bg-white/5 hover:bg-white/10 focus:outline-none transition">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-3 w-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('پروفایل') }}
                        </x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('خروج') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-white/60 hover:text-white hover:bg-white/10 focus:outline-none transition">
                    <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <a href="{{ route('habits.index') }}" class="block px-3 py-2 text-base font-medium text-white/70 hover:text-white hover:bg-white/5 rounded-lg">عادت‌های من</a>
            <a href="{{ route('profile.edit') }}" class="block px-3 py-2 text-base font-medium text-white/70 hover:text-white hover:bg-white/5 rounded-lg">پروفایل</a>
        </div>
        <div class="pt-4 pb-1 border-t border-white/10">
            <div class="px-4">
                <div class="font-medium text-sm text-white">{{ Auth::user()->name }}</div>
                <div class="font-medium text-xs text-white/60">{{ Auth::user()->email }}</div>
            </div>
            <div class="mt-3 space-y-1">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();" class="block px-3 py-2 text-base font-medium text-white/70 hover:text-white hover:bg-white/5 rounded-lg">خروج</a>
                </form>
            </div>
        </div>
    </div>
</nav>
