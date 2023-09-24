<template>
    <!-- BEGIN: Modal Content -->
    <Modal :show="isModalVisible" @hidden="hideModal">
        <ModalHeader>
            <h2 class="font-medium text-base mr-auto">{{ $t("set pin") }}</h2>
        </ModalHeader>
        <ModalBody class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 md:col-span-6">
                <label for="pin" class="form-label">{{ $t("new pin") }}</label>
                <input
                    id="pin"
                    type="password"
                    class="form-control"
                    :placeholder="$t('pin placeholder')"
                    v-model="userPin"
                />
            </div>
            <div class="col-span-12 md:col-span-6">
                <label for="pin" class="form-label">{{
                    $t("confirm pin")
                }}</label>
                <input
                    id="confirmPin"
                    type="password"
                    class="form-control"
                    :placeholder="$t('confirm pin')"
                    v-model="confirmUserPin"
                />
            </div>
            <div class="col-span-12 md:col-span-6">
                <label for="pin" class="form-label">{{ $t("password") }}</label>
                <input
                    id="password"
                    type="password"
                    class="form-control"
                    :placeholder="$t('password')"
                    v-model="password"
                />
            </div>
        </ModalBody>
        <ModalFooter>
            <button
                type="button"
                @click="hideModal"
                class="btn btn-outline-secondary w-20 mr-1"
            >
                {{ $t("cancel") }}
            </button>
            <button
                type="button"
                class="btn btn-primary w-20"
                @click="submitUserPin"
            >
                {{ $t("submit") }}
            </button>
        </ModalFooter>
    </Modal>
    <!-- END: Modal Content -->
</template>

<script setup>
import { computed, onMounted, ref } from "vue";
import { useDashboardStore } from "../../stores/dashboard";
import { useGlobalStore } from "../../stores/global";

const globalStore = useGlobalStore();
const dashboardStore = useDashboardStore();

const isModalVisible = computed(() => globalStore.isPinModalVisible);

const hideModal = () => {
    globalStore.showSetPinModal(false);
};

// Form handling
const userPin = ref("");
const confirmUserPin = ref("");
const password = ref("");

const submitUserPin = async () => {
    await dashboardStore
        .setUserPin({
            pin: userPin.value,
            confirm_pin: confirmUserPin.value,
            password: password.value,
        })
        .then(() => {
            clearInput();
        });
};
const clearInput = () => {
    userPin.value = "";
    confirmUserPin.value = "";
    password.value = "";
};
onMounted(() => {
    clearInput();
});
</script>
