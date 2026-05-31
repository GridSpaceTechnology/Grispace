<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your profile information and photo.") }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div x-data="{ photoPreview: null }">
            <x-input-label :value="__('Profile Photo')" />

            <div class="mt-2 flex items-center gap-6">
                <div class="relative shrink-0">
                    <div class="w-20 h-20 rounded-full overflow-hidden bg-gray-100 ring-2 ring-gray-200">
                        <template x-if="!photoPreview">
                            <img
                                src="{{ $user->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&color=EB5233&background=FFF0ED' }}"
                                alt="{{ $user->name }}"
                                class="w-full h-full object-cover"
                            >
                        </template>
                        <template x-if="photoPreview">
                            <img :src="photoPreview" alt="Preview" class="w-full h-full object-cover">
                        </template>
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="cursor-pointer inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#EB5233] transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z"/>
                        </svg>
                        {{ __('Choose Photo') }}
                        <input
                            type="file"
                            name="profile_photo"
                            accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                            class="sr-only"
                            @change="photoPreview = URL.createObjectURL($event.target.files[0])"
                        >
                    </label>

                    @if ($user->profile_photo_path)
                        <button
                            type="button"
                            class="text-sm text-red-600 hover:text-red-800 font-medium transition-colors text-left"
                            x-data
                            @click="document.getElementById('remove-photo-form').submit()"
                        >
                            {{ __('Remove Photo') }}
                        </button>
                    @endif
                </div>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('profile_photo')" />
        </div>

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>

    @if ($user->profile_photo_path)
        <form
            id="remove-photo-form"
            method="post"
            action="{{ route('profile.photo.destroy') }}"
            class="hidden"
        >
            @csrf
            @method('delete')
        </form>
    @endif
</section>