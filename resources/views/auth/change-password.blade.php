<x-guest-layout>
    <x-slot name="pageTitle">
        {{ __('change password') }}
    </x-slot>
    <div class="flex content-center items-center justify-center h-full">
        <div class="w-full md:w-5/12 xl:w-4/12 px-4">
            <div class="relative flex flex-col justify-center items-center mb-6">
                <x-branding.logo-default />
            </div>
            <x-form.validation-errors class="mb-4" />
            <div class="relative flex flex-col min-w-0 break-words w-full mb-6 rounded-xl bg-white border-0">
                <div class="flex-auto px-4 lg:px-10 py-11">
                    <form class="auth" method="POST" action="{{ route('auth.recoverPassword') }}">
                        @csrf
                        <div class="relative w-full mb-6">
                            <x-form.label for="password" value="{{ __('new password') }}" />
                            <x-form.input id="password" class="bisuccy-auth-input" type="password" name="password" required autofocus placeholder="{{ __('new password') }}" />
                        </div>
                        <div class="relative w-full mb-6">
                            <x-form.label for="password_confirmation" value="{{ __('password confirmation') }}" />
                            <x-form.input id="password_confirmation" class="bisuccy-auth-input" type="password" name="password_confirmation" required placeholder="{{ __('password confirmation') }}." />
                        </div>

                        <div class="text-center mt-6">
                            <x-form.button class="block bisuccy-primary-button">
                                {{ __('change password') }}
                            </x-form.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>