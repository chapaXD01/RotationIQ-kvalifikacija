<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden shadow-sm sm:rounded-lg" style="background-color: rgba(59, 130, 246, 0.15); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2);">
                <div class="p-6 text-white">
                    <h1>Start building ur volleyball rotations today!</h1>
                </div>
            </div>
        </div>
    </div>
    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden shadow-sm sm:rounded-lg" style="background-color: rgba(59, 130, 246, 0.15); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2);">
                <div class="p-6 text-white">
                    <h1>How to use the app?</h1>
                    <p>To start, on the navigation bar there ar 2 choices defence and attack chose one by clicking on it. After that press on CREATE NEW ROTATION.
                        then u can start creating your own rotation you can drag each player in any position by clicking and holding on the player. And the all u gotta do is press save rotation.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
