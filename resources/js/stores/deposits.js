import { defineStore } from "pinia";
import { ref } from "vue";
import depositsApi from "../api/depositsApi";
import { useGlobalStore } from "./global";

export const useDepositsStore = defineStore("depositsStore", () => {
    const globalStore = useGlobalStore();
    // Set the default states
    const depositsList = ref([]);
    const fiatDepositsList = ref([]);
    const cryptoDepositsList = ref([]);

    // Perform some actions
    const getDepositsList = async () => {
        // Fetch all deposits
        await globalStore.setLoading(true);
        depositsList.value = await depositsApi.getDepositsList();
        await globalStore.setLoading(false);
    };

    const getFiatDepositList = async () => {
        // Fetch all fiat deposits
        await globalStore.setLoading(true);
        fiatDepositsList.value = await depositsApi.getFiatDepositList();
        await globalStore.setLoading(false);
    };

    const getFiatDeposit = async (id) => {
        // Fetch single fiat deposits
        await globalStore.setLoading(true);
        const response = await depositsApi.getFiatDeposit(id);
        await globalStore.setLoading(false);
        return response;
    };

    const approveFiatDeposit = async (payload) => {
        globalStore.approvalLoader = true;
        try{
            const response = await depositsApi.approveFiatDeposit(payload);
            globalStore.approvalLoader = false;
            await globalStore.showApprovalPinModal(false)
            globalStore.setSuccessMessage(response?.message ? response.message : "Success" )
        }catch(err) {
            globalStore.approvalLoader = false;
            globalStore.setErrorMessage(err.response.data?.data?.error ? err.response.data.data.error : err.response.data.message )
        }
    };

    const rejectFiatDeposit = async (payload) => {
        globalStore.approvalLoader = true;
        try{
            const response = await depositsApi.rejectFiatDeposit(payload);
            globalStore.approvalLoader = false;
            await globalStore.showApprovalPinModal(false)
            globalStore.setSuccessMessage(response?.message ? response.message : "Success" )
        }catch(err) {
            globalStore.approvalLoader = false;
            globalStore.setErrorMessage(err.response.data?.data?.error ? err.response.data.data.error : err.response.data.message )
        }
    };

    // crypto deposits
    const getCryptoDepositList = async () => {
        // Fetch all crypto withdrawal
        globalStore.loading = true;
        try {
            cryptoDepositsList.value = await depositsApi.getAllCryptoDeposits();
            globalStore.loading = false;
        } catch(err) {
            globalStore.loading = false;
            globalStore.setErrorMessage(err.response.data?.data?.error ? err.response.data.data.error : err.response.data.message )
        }
        
    }

    // expose necessary data
    return { 
        getDepositsList, 
        depositsList, 
        getFiatDepositList,
        fiatDepositsList,
        getFiatDeposit,
        approveFiatDeposit,
        rejectFiatDeposit,
        cryptoDepositsList,
        getCryptoDepositList
    };
});
