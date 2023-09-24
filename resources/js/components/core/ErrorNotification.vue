<template>
    <!-- BEGIN: Basic Non Sticky Notification Content -->
    <Notification
        refKey="basicNonStickyNotification"
        :options="{
            duration: 3000,
        }"
        class="flex"
    >
        <SlashIcon class="text-danger" />
        <div class="ml-4 mr-4">
            <div class="font-medium">{{$t('error')}}!</div>
            <div class="mt-1 text-slate-500">
                {{errorMessage}}
            </div>
        </div>
    </Notification>
    <!-- END: Basic Non Sticky Notification Content -->
</template>

<script setup>
import { ref, provide, watch, computed } from "vue";
import { useGlobalStore } from "../../stores/global";

const globalStore = useGlobalStore();

const errorMessage = computed(() => globalStore.errorMessage)

// Basic non sticky notification
const basicNonStickyNotification = ref();
provide("bind[basicNonStickyNotification]", (el) => {
  // Binding
  basicNonStickyNotification.value = el;
});

watch(errorMessage, () => {
    if(errorMessage.value.length > 0) {
        basicNonStickyNotification.value.showToast();
    }
})


</script>
