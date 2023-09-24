import { defineStore } from "pinia";
import { ref } from "vue";
import withdrawalsApi from "../api/withdrawalsApi";
import { useGlobalStore } from "./global";

export const useWithdrawalStore = defineStore("withdrawalStore", () => {
    const globalStore = useGlobalStore();
    // Set the default states
    // const withdrawalsList = ref([]);
    const fiatWithdrawalsList = ref([]);
    const cryptoWithdrawalsList = ref([]);

    // Perform some actions
    const getFiatWithdrawalList = async () => {
        // Fetch all fiat withdrawals
        globalStore.loading = true;
        fiatWithdrawalsList.value = await withdrawalsApi.getFiatWithdrawalList();
        globalStore.loading = false;
    };

    const getFiatWithdrawalbyId = async (id) => {
        // Fetch single fiat withdrawals
        globalStore.loading = true;
        const response = await withdrawalsApi.getFiatWithdrawalsDetail(id);
        globalStore.loading = false;
        return response;
    };
    const getCryptoWithdrawalList = async () => {
        // Fetch all crypto withdrawal
        globalStore.loading = true;
        try {
            cryptoWithdrawalsList.value = await withdrawalsApi.getAllCryptoWithdrawals();
            globalStore.loading = false;
            globalStore.setSuccessMessage(response?.message ? response.message : "Success" )
        } catch(err) {
            globalStore.loading = false;
            globalStore.setErrorMessage(err.response.data?.data?.error ? err.response.data.data.error : err.response.data.message )
        }
        
    }
    // approve fiat withdrawal
    const approveFiatWithdrawal = async (payload) => {
        globalStore.approvalLoader = true;
        try{
            const response = await withdrawalsApi.approveFiatWithdrawal(payload);
            globalStore.approvalLoader = false;
            await globalStore.showApprovalPinModal(false)
            globalStore.setSuccessMessage(response?.message ? response.message : "Success" )
        }catch(err) {
            globalStore.approvalLoader = false;
            globalStore.setErrorMessage(err.response.data?.data?.error ? err.response.data.data.error : err.response.data.message )
        }
    };
    // reject fiat withdrawal
    const rejectFiatWithdrawal = async (payload) => {
        globalStore.loading = true;
        try{
            const response = await withdrawalsApi.rejectFiatWithdrawal(payload);
            globalStore.loading = false;
            globalStore.setSuccessMessage(response?.message ? response.message : "Success" )
        }catch(err) {
            globalStore.loading = false;
            globalStore.setErrorMessage(err.response.data?.data?.error ? err.response.data.data.error : err.response.data.message )
        }
    };

    // expose necessary data
    return {
        fiatWithdrawalsList,
        getFiatWithdrawalList,
        getFiatWithdrawalbyId,
        approveFiatWithdrawal,
        cryptoWithdrawalsList,
        getCryptoWithdrawalList,
        rejectFiatWithdrawal
    };
});
