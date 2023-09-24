<template>
    <div class="absolute inset-0 h-screen w-full bg-slate-100 bg-opacity-50 backdrop-blur-sm z-50 pointer-events-none loading-screen">
        <div class="h-full flex flex-col justify-center items-center">
            <div class="col-span-6 sm:col-span-3 xl:col-span-2 flex flex-col justify-end items-center">
                <LoadingIcon icon="circles" color="fill-bisuccy-primary" class="w-10 h-10 text-bisuccy-primary fill-bisuccy-primary" />
            </div>
        </div>
    </div>
</template>

<style scoped>
    .loading-screen {
        z-index: 9999999;
        top: -50px;
        border-radius: 50px;
    }
</style>