<template>
    <button :type="type" class="btn btn-success w-32" :class="customClass">{{text}}</button>
</template>
<script setup>
defineProps({
    text: { type: String, default: ''},
    type: { type: String, default: 'button'},
    customClass: { type: String, default: 'button'}
})
</script>
