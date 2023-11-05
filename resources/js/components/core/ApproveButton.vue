<template>
    <button class="btn btn-success text-white w-32 mr-2 mb-2">
        <CheckIcon class="w-4 h-4 mr-2" /> {{$t(text)}}
    </button>
</template>
<script setup>
defineProps({
    text: { type: String, default: 'approve'},
})

</script>
