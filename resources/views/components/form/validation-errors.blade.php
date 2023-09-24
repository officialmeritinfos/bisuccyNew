
@if (session('errors'))
<div class="block text-sm text-red-500 bg-red-50 bg-opacity-25 border border-red-100 items-center px-4 pt-2 rounded-lg relative transition duration-500 ease-in-out mb-4" role="alert">
    <div class="block">
        <strong class="mr-1">{{ __('error')}}</strong>
    </div>
    <div class="block">
        @if( Arr::accessible(session('errors')) )
            <ul class="mt-3 list-none list-inside text-xs text-red-500">
                @foreach ($errors as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @else
            {{$errors}}
        @endif
    </div>
    <button type="button" data-dismiss="alert" aria-label="Close" onclick="this.parentElement.remove();">
        <span class="absolute top-1.5 right-0 text-2xl px-3 py-1 hover:text-red-900" aria-hidden="true">
            <!-- Close button -->
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </span>
    </button>
</div>
@endif