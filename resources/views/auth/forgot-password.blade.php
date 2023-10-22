<x-guest-layout>
    <x-slot name="pageTitle">
        {{ __('forgot password') }}
    </x-slot>
    <div class="flex content-center items-center justify-center h-full">
        <div class="w-full md:w-5/12 xl:w-4/12 px-4">
            <div class="relative flex flex-col justify-center items-center mb-6">
                <x-branding.logo-default />
            </div>
            <x-form.validation-errors class="mb-4" />
            <div class="relative flex flex-col min-w-0 break-words w-full mb-6 rounded-xl bg-white border-0">
                <div class="flex-auto px-4 lg:px-10 py-11">
                    <form class="auth" method="POST" action="{{ route('auth.authenticatePassword') }}">
                        @csrf
                        <div class="relative w-full mb-6">
                            <x-form.label for="email" value="{{ __('email') }}" />
                            <x-form.input id="email" class="bisuccy-auth-input" type="email" name="email" :value="old('email')" required autofocus placeholder="{{ __('enter your email') }}" />
                        </div>

                        <div class="text-center mt-6">
                            <x-form.button class="block bisuccy-primary-button">
                                {{ __('reset my password') }}
                            </x-form.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>