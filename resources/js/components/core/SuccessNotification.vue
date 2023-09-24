<template>
    <!-- BEGIN: Basic Non Sticky Notification Content -->
    <Notification
        refKey="basicNonStickyNotification"
        :options="{
            duration: 3000,
        }"
        class="flex"
    >
        <CheckIcon class="text-success" />
        <div class="ml-4 mr-4">
            <div class="font-medium">{{$t('success')}}!</div>
            <div class="mt-1 text-slate-500">
                {{successMessage}}
            </div>
        </div>
    </Notification>
    <!-- END: Basic Non Sticky Notification Content -->
</template>

<script setup>
import { ref, provide, watch, computed } from "vue";
import { useGlobalStore } from "../../stores/global";

const globalStore = useGlobalStore();

const successMessage = computed(() => globalStore.successMessage)

// Basic non sticky notification
const basicNonStickyNotification = ref();
provide("bind[basicNonStickyNotification]", (el) => {
  // Binding
  basicNonStickyNotification.value = el;
});

watch(successMessage, () => {
    if(successMessage.value.length > 0) {
        basicNonStickyNotification.value.showToast();
    }
})


</script>
