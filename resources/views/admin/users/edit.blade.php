<x-app-layout>
    <div class="mb-4">
        <h1 class="text-lg font-bold uppercase tracking-wider opacity-70">Edit User: {{ $user->name }}</h1>
    </div>

    <div class="card bg-base-100 shadow-sm rounded-sm border border-base-300 max-w-2xl">
        <div class="card-body p-6">
            <form method="POST" action="{{ route('admin.users.update', $user) }}">
                @csrf
                @method('PATCH')

                <!-- Name -->
                <div class="mb-4">
                    <x-input-label for="name" :value="__('Name')" />
                    <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $user->name)" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <!-- Email Address -->
                <div class="mb-4">
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $user->email)" required />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Role -->
                <div class="mb-4">
                    <x-input-label for="role" :value="__('Role')" />
                    <select id="role" name="role" class="select select-bordered select-sm w-full mt-1 rounded-sm">
                        <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>User (Staff)</option>
                        <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin (Branch Manager)</option>
                        <option value="super_admin" {{ old('role', $user->role) == 'super_admin' ? 'selected' : '' }}>Super Admin (Full Access)</option>
                    </select>
                    <x-input-error :messages="$errors->get('role')" class="mt-2" />
                </div>

                <div class="divider text-xs opacity-50 uppercase tracking-tighter">Change Password (optional)</div>

                <!-- Password -->
                <div class="mb-4">
                    <x-input-label for="password" :value="__('New Password')" />
                    <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Confirm Password -->
                <div class="mb-4">
                    <x-input-label for="password_confirmation" :value="__('Confirm New Password')" />
                    <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <div class="flex items-center justify-end mt-4 gap-2">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-ghost btn-sm">Cancel</a>
                    <x-primary-button>
                        {{ __('Update User') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
