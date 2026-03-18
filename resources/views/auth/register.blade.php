<x-guest-layout>
    <div class="bg-white shadow-xl rounded-2xl p-8">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-gray-900">Create Your Account</h2>
            <p class="text-gray-600 mt-2">Join Gridspace today</p>
        </div>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="space-y-5">
                <div>
                    <x-input-label for="name" :value="__('Full Name')" />
                    <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="email" :value="__('Email Address')" />
                    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password" :value="__('Password')" />
                    <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                    <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <div>
                    <x-input-label :value="__('I am a...')" />
                    <div class="grid grid-cols-2 gap-4 mt-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="role" value="candidate" class="peer sr-only" {{ old('role') === 'candidate' ? 'checked' : '' }} required>
                            <div class="p-4 rounded-lg border-2 border-gray-200 peer-checked:border-indigo-600 peer-checked:bg-indigo-50 hover:border-gray-300 transition-all text-center">
                                <svg class="w-8 h-8 mx-auto text-gray-400 peer-checked:text-indigo-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <div class="font-medium text-gray-900">Candidate</div>
                                <div class="text-xs text-gray-500 mt-1">Looking for jobs</div>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="role" value="employer" class="peer sr-only" {{ old('role') === 'employer' ? 'checked' : '' }} required>
                            <div class="p-4 rounded-lg border-2 border-gray-200 peer-checked:border-indigo-600 peer-checked:bg-indigo-50 hover:border-gray-300 transition-all text-center">
                                <svg class="w-8 h-8 mx-auto text-gray-400 peer-checked:text-indigo-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                <div class="font-medium text-gray-900">Employer</div>
                                <div class="text-xs text-gray-500 mt-1">Hiring talent</div>
                            </div>
                        </label>
                    </div>
                    <x-input-error :messages="$errors->get('role')" class="mt-2" />
                </div>
            </div>

            <x-primary-button class="w-full justify-center mt-6">
                {{ __('Create Account') }}
            </x-primary-button>
        </form>

        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
                Already have an account?
                <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-700 font-medium">
                    Sign in
                </a>
            </p>
        </div>
    </div>
</x-guest-layout>
