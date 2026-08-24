<x-guest-layout>
    <div class="bg-white shadow-xl rounded-2xl p-8">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-gray-900">Verify Your Email</h2>
            <p class="text-gray-600 mt-2">
                Thanks for signing up! Before getting started, could you verify your email address by clicking on the
                link we just emailed to you? If you didn't receive the email, we will gladly send you another.
            </p>
        </div>

        @if (session('suspended'))
            <div class="mb-4 text-sm font-medium text-red-600 bg-red-50 border border-red-200 rounded-lg p-3">
                {{ session('suspended') }}
            </div>
        @endif

        @if (session('status') == 'verification-link-sent')
            <div class="mb-4 text-sm font-medium text-green-600 bg-green-50 border border-green-200 rounded-lg p-3">
                A new verification link has been sent to the email address you provided during registration.
            </div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}" class="mb-3">
            @csrf

            <x-primary-button class="w-full justify-center">
                {{ __('Resend Verification Email') }}
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="w-full text-sm text-gray-600 underline underline-offset-4 hover:text-gray-900 transition-colors py-2">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
