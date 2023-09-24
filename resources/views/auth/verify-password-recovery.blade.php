<x-guest-layout>
    <x-slot name="pageTitle">
        {{ __('Password recovery') }}
    </x-slot>
    <div class="flex content-center items-center justify-center h-full">
        <div class="w-full lg:w-4/12 px-4">
            <div class="relative flex flex-col justify-center items-center mb-6">
                <x-branding.logo-default />
            </div>
            <x-form.validation-errors class="mb-4" />
            <div class="relative flex flex-col min-w-0 break-words w-full mb-6 rounded-xl bg-white border-0">
                <div class="flex-auto px-4 lg:px-10 py-11">
                    <form class="auth" method="POST" action="{{ route('auth.processResetCode') }}">
                        @csrf
                        <div class="relative w-full mb-6">
                            <x-form.label for="code" value="{{ __('password reset code') }}" />
                            <x-form.input id="code" class="bisuccy-auth-input" type="text" name="code" :value="old('code')" required autofocus placeholder="{{ __('two factor code') }}" />
                        </div>

                        <div class="text-center mt-6">
                            <x-form.button class="block bisuccy-primary-button">
                                {{ __('send code') }}
                            </x-form.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>