<template>
    <div class="w-full flex items-center justify-between">
        <h2 class="text-lg font-medium mr-auto">{{ title }}</h2>
        <slot />
    </div>
</template>
<script setup>
const props = defineProps({
    title: { type: String, default: "Page" }
})
</script>