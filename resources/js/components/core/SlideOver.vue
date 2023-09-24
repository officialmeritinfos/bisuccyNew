<template>
    <!-- BEGIN: Slide Over With Close Button -->
    <div id="button-slide-over" class="p-5">
        <!-- BEGIN: Modal Content -->
        <Modal backdrop="static" :slideOver="true" :show="launch" @hidden="launch = false">
            <button @click="hideSlideOver" class="absolute top-0 left-0 right-auto mt-4 -ml-12">
                <XIcon class="w-8 h-8 text-slate-400" />
            </button>
            <ModalHeader class="p-5">
                <slot name="header"></slot>
            </ModalHeader>
            <ModalBody>
                <slot name="body"></slot>
            </ModalBody>
        </Modal>
        <!-- END: Modal Content -->

    </div>
    <!-- END: Slide Over With Close Button -->
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    showSlideOver: { type: Boolean, default: false },
})

const launch = ref(props.showSlideOver ? props.showSlideOver : false );

const emit = defineEmits(["hideSlideOver"])

const hideSlideOver = () => {
    launch.value = false;
}

defineExpose({
    hideSlideOver
});

watch(launch, () => {
    if(!launch.value) {
        emit("hideSlideOver")
    }
})

watch(() => props.showSlideOver, () => {
    if(props.showSlideOver) {
        launch.value = true;
    }
})

</script>