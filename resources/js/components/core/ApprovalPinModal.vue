<template>
    <!-- BEGIN: Modal Content -->
    <Modal :show="isModalVisible" @hidden="hideModal">
        <ModalHeader>
            <h2 class="font-medium text-base mr-auto">{{ $t("enter pin") }}</h2>
        </ModalHeader>
        <ModalBody class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 md:col-span-6">
                <label for="approvalPin" class="form-label">{{ $t("enter approval pin") }}</label>
                <input
                    id="approvalPin"
                    type="password"
                    class="form-control"
                    :placeholder="$t('pin placeholder')"
                    v-model="userPin"
                />
            </div>
        </ModalBody>
        <ModalFooter class="flex justify-end">
            <button
                type="button"
                @click="hideModal"
                class="btn btn-outline-secondary w-20 mr-1"
            >
                {{ $t("cancel") }}
            </button>
            <button
                type="button"
                :class="canSubmit ? 'btn btn-primary w-20' : 'btn btn-secondary bg-opacity-5 w-20 cursor-not-allowed'"
                @click="submitUserPin"
                :disabled="!canSubmit"
            >
               <LoadingIcon v-if="approvalLoader" icon="circles" color="fill-bisuccy-primary" class="w-[18px] text-bisuccy-primary fill-bisuccy-primary" />
               <div v-else>{{ $t("submit") }}</div>
            </button>
        </ModalFooter>
    </Modal>
    <!-- END: Modal Content -->
</template>

<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useGlobalStore } from "../../stores/global";

const globalStore = useGlobalStore();

const isModalVisible = computed(() => globalStore.isApprovalPinModalVisible);
const approvalLoader = computed(() => globalStore.approvalLoader);

const hideModal = () => {
    globalStore.showApprovalPinModal(false);
};

// Form handling
const userPin = ref("");
const canSubmit = ref(false);

const submitUserPin = async () => {
    await globalStore.setApprovalPin(userPin.value);
    // await globalStore.showApprovalPinModal(false)
    clearInput();
};
const clearInput = () => {
    userPin.value = "";
};
onMounted(() => {
    clearInput();
});
watch(userPin, () => {
    if(userPin.value.length > 3) {
        canSubmit.value = true;
    }else {
        canSubmit.value = false;
    }
})
</script>
