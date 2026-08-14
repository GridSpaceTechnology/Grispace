<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Log Out') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Log out of your account on this device.') }}
        </p>
    </header>

    <form method="POST" action="{{ route('logout') }}" class="mt-6">
        @csrf
        <x-secondary-button type="submit">
            {{ __('Log Out') }}
        </x-secondary-button>
    </form>
</section>
