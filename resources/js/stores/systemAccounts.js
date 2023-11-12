import { defineStore } from "pinia";
import systemAccountsApi from "../api/systemAccountsApi.js";
import { useGlobalStore } from "./global.js";

export const useSystemAccountsStore = defineStore("systemAccountsStore", () => {
    const globalStore = useGlobalStore();

    // Perform some actions
    const getSystemAccountsList = async () => {
        // Fetch all systemAccounts
        globalStore.loading = true;
        try {
            const response = await systemAccountsApi.getSystemAccountsList();
            globalStore.loading = false;
            return response;
        } catch(err) {
            globalStore.loading = false;
            globalStore.setErrorMessage(err.response.data?.data?.error ? err.response.data.data.error : err.response.data.message )
        }
    };

    const getSystemAccount = async (id) => {
        // Fetch single systemAccount
        globalStore.loading = true;
        try {
            const response = await systemAccountsApi.getSystemAccount(id);
            globalStore.loading = false;
            return response;
        } catch(err) {
            globalStore.loading = false;
            globalStore.setErrorMessage(err.response.data?.data?.error ? err.response.data.data.error : err.response.data.message )
        }
    };

    const getSystemAccountWithdrawals = async () => {
        // Fetch all systemAccounts
        globalStore.loading = true;
        try {
            const response = await systemAccountsApi.getSystemAccountWithdrawals();
            globalStore.loading = false;
            return response;
        } catch(err) {
            globalStore.loading = false;
            globalStore.setErrorMessage(err.response.data?.data?.error ? err.response.data.data.error : err.response.data.message )
        }
    };

    const withdrawFromSystemAccount = async (payload) => {
        globalStore.approvalLoader = true;
        try{
            const response = await systemAccountsApi.withdrawFromSystemAccount(payload);
            globalStore.approvalLoader = false;
            globalStore.setSuccessMessage(response?.message ? response.message : "Success" )
        }catch(err) {
            globalStore.approvalLoader = false;
            globalStore.setErrorMessage(err.response.data?.data?.error ? err.response.data.data.error : err.response.data.message )
        }
        await globalStore.showApprovalPinModal(false)
    };

    const approveSystemWithdrawal = async (payload) => {
        globalStore.approvalLoader = true;
        try{
            const response = await systemAccountsApi.approveSystemWithdrawal(payload);
            globalStore.approvalLoader = false;
            globalStore.setSuccessMessage(response?.message ? response.message : "Success" )
        }catch(err) {
            globalStore.approvalLoader = false;
            globalStore.setErrorMessage(err.response.data?.data?.error ? err.response.data.data.error : err.response.data.message )
        }
        await globalStore.showApprovalPinModal(false)
    };

    const getSystemFiatAccounts = async () => {
        // Fetch all system fiat accounts
        globalStore.loading = true;
        try {
            const response = await systemAccountsApi.getSystemFiatAccounts();
            globalStore.loading = false;
            return response;
        } catch(err) {
            globalStore.loading = false;
            globalStore.setErrorMessage(err.response.data?.data?.error ? err.response.data.data.error : err.response.data.message )
        }
    };

    const addSystemFiatAccount = async (payload) => {
        globalStore.approvalLoader = true;
        try{
            const response = await systemAccountsApi.addSystemFiatAccount(payload);
            globalStore.approvalLoader = false;
            globalStore.setSuccessMessage(response?.message ? response.message : "Success" )
        }catch(err) {
            globalStore.approvalLoader = false;
            globalStore.setErrorMessage(err.response.data?.data?.error ? err.response.data.data.error : err.response.data.message )
        }
        await globalStore.showApprovalPinModal(false)
    };


    // expose necessary data
    return { 
        getSystemAccountsList,
        getSystemAccount,
        getSystemAccountWithdrawals,
        approveSystemWithdrawal,
        withdrawFromSystemAccount,
        getSystemFiatAccounts,
        addSystemFiatAccount
    };
});
