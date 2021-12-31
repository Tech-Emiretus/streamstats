<x-layout>
    <x-slot name="title">
        Login
    </x-slot>

    <div class="h-full">
        <div class="px-10 py-32 mx-auto">
            <div class="bg-white shadow rounded-lg p-8 max-w-md mx-auto">
                <h2 class="text-2xl text-center font-normal mb-6 text-90">Welcome to StreamStats!</h2>

                <svg class="block mx-auto mb-6" xmlns="http://www.w3.org/2000/svg" width="100" height="2" viewBox="0 0 100 2">
                    <path fill="#D8E3EC" d="M0 0h100v2H0z"></path>
                </svg>

                <div class="flex justify-center">
                    <a href="{{ $login_url }}">
                        <button class="bg-purple-700 hover:bg-purple-900 rounded-sm py-2 px-4 text-white" type="submit">
                            Login with Twitch
                        </button>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layout>
