<x-app-layout>
    <div class="mb-4">
        <h1 class="text-lg font-bold uppercase tracking-wider opacity-70">Profile Settings</h1>
    </div>

    <div class="space-y-4">
        <div class="p-4 sm:p-6 bg-base-100 shadow-sm border border-base-300 rounded-sm">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="p-4 sm:p-6 bg-base-100 shadow-sm border border-base-300 rounded-sm">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="p-4 sm:p-6 bg-base-100 shadow-sm border border-base-300 rounded-sm">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>
