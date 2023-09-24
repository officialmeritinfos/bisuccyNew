<template>
    <h2 class="text-lg font-medium mr-auto">{{ title }}</h2>
</template>
<script setup>
const props = defineProps({
    title: { type: String, default: "Page" }
})
</script>