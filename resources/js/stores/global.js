import { defineStore } from "pinia";
import { ref } from "vue";

export const useGlobalStore = defineStore("globalStore", () => {
    const loading = ref(false); // Use this to toggle loading state all through the app
    const isPinModalVisible = ref(false);
    const isApprovalPinModalVisible = ref(false);
    const errorMessage = ref('');
    const successMessage = ref('')
    const approvalPin = ref('')
    const approvalLoader = ref(false)

    const logMeOut = () => {
        window.location.assign('/sysadmin/logout')
    }

    const setLoading = (val) => {
        loading.value = val;
    }

    const showSetPinModal = (val) => {
        isPinModalVisible.value = val
    }

    const showApprovalPinModal = (val) => {
        isApprovalPinModalVisible.value = val
    }

    const setApprovalPin = (val) => {
        approvalPin.value = val;
    }

    const clearApprovalPin = () => {
        approvalPin.value = '';
    }

    const setErrorMessage = (message) => {
        errorMessage.value = message;
    }

    const setSuccessMessage = (message) => {
        successMessage.value = message;
    }
    const setApprovalLoader = (val) => {
        approvalLoader.value = val
    }


    return {
        loading,
        setLoading,
        isPinModalVisible,
        isApprovalPinModalVisible,
        showApprovalPinModal,
        showSetPinModal,
        approvalPin,
        setApprovalPin,
        clearApprovalPin,
        errorMessage,
        setErrorMessage,
        successMessage,
        setSuccessMessage,
        logMeOut,
        setApprovalLoader,
        approvalLoader
    };
});
