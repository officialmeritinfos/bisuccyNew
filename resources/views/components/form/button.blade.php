<button {{ $attributes->merge(['type' => 'submit', 'class' => 'flex w-full justify-center rounded-md border border-transparent bg-bisuccy-primary py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 ease-linear transition-all duration-150 disabled:opacity-25 transition']) }}>
    {{ $slot }}
</button>


