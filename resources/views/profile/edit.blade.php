<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-blue-600/20 border border-blue-500/30">
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <h2 class="font-bold text-xl text-white tracking-tight">
                {{ __('Profile') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <p class="text-xs font-semibold uppercase tracking-widest text-blue-400 px-1">
                👤 Account Settings
            </p>

            {{-- Update Profile Information --}}
            <div class="rounded-2xl overflow-hidden shadow-xl border border-white/10 bg-white/5 backdrop-blur-md">
                <div class="px-5 py-4 border-b border-white/10 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-400 inline-block"></span>
                    <span class="text-sm font-semibold text-white tracking-wide">Profile Information</span>
                </div>
                <div class="p-6 sm:p-8">
                    <div class="max-w-xl">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>
            </div>

            {{-- Update Password --}}
            <div class="rounded-2xl overflow-hidden shadow-xl border border-white/10 bg-white/5 backdrop-blur-md">
                <div class="px-5 py-4 border-b border-white/10 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-yellow-400 inline-block"></span>
                    <span class="text-sm font-semibold text-white tracking-wide">Update Password</span>
                </div>
                <div class="p-6 sm:p-8">
                    <div class="max-w-xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>
            </div>

            {{-- Delete Account --}}
            <div class="rounded-2xl overflow-hidden shadow-xl border border-red-500/20 bg-red-500/5 backdrop-blur-md">
                <div class="px-5 py-4 border-b border-red-500/20 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-400 inline-block"></span>
                    <span class="text-sm font-semibold text-red-300/80 tracking-wide">Danger Zone</span>
                </div>
                <div class="p-6 sm:p-8">
                    <div class="max-w-xl">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>