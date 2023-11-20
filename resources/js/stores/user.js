import { defineStore } from "pinia";
import userApi from "../api/userApi.js";
import { useGlobalStore } from "./global";

export const useUserStore = defineStore("userStore", () => {
    const globalStore = useGlobalStore();

    // Perform some actions
    const getUsersList = async () => {
        // Fetch all users
        globalStore.loading = true;
        try {
            const response = await userApi.getUsersList();
            globalStore.loading = false;
            return response;
        } catch (err) {
            globalStore.loading = false;
            globalStore.setErrorMessage(
                err.response.data?.data?.error
                    ? err.response.data.data.error
                    : err.response.data.message
            );
        }
    };

    const getUsersWallets = async () => {
        // Fetch all wallets
        globalStore.loading = true;
        try {
            const response = await userApi.getUsersWallets();
            globalStore.loading = false;
            return response;
        } catch (err) {
            globalStore.loading = false;
            globalStore.setErrorMessage(
                err.response.data?.data?.error
                    ? err.response.data.data.error
                    : err.response.data.message
            );
        }
    };

    const getUsersBanks = async () => {
        // Fetch all banks
        globalStore.loading = true;
        try {
            const response = await userApi.getUsersBanks();
            globalStore.loading = false;
            return response;
        } catch (err) {
            globalStore.loading = false;
            globalStore.setErrorMessage(
                err.response.data?.data?.error
                    ? err.response.data.data.error
                    : err.response.data.message
            );
        }
    };

    const getUserWithdrawals = async (id) => {
        // Fetch all banks
        globalStore.loading = true;
        try {
            const response = await userApi.getUserWithdrawals(id);
            globalStore.loading = false;
            return response;
        } catch (err) {
            globalStore.loading = false;
            globalStore.setErrorMessage(
                err.response.data?.data?.error
                    ? err.response.data.data.error
                    : err.response.data.message
            );
        }
    };

    const getUserProfile = async (id) => {
        // Fetch profile
        globalStore.loading = true;
        try {
            const response = await userApi.getUserProfile(id);
            globalStore.loading = false;
            return response;
        } catch (err) {
            globalStore.loading = false;
            globalStore.setErrorMessage(
                err.response.data?.data?.error
                    ? err.response.data.data.error
                    : err.response.data.message
            );
        }
    };

    const getUserDeposits = async (id) => {
        // Fetch all user deposits
        globalStore.loading = true;
        try {
            const response = await userApi.getUserDeposits(id);
            globalStore.loading = false;
            return response;
        } catch (err) {
            globalStore.loading = false;
            globalStore.setErrorMessage(
                err.response.data?.data?.error
                    ? err.response.data.data.error
                    : err.response.data.message
            );
        }
    };

    const getUserSwaps = async (id) => {
        // Fetch all user swaps
        globalStore.loading = true;
        try {
            const response = await userApi.getUserSwaps(id);
            globalStore.loading = false;
            return response;
        } catch (err) {
            globalStore.loading = false;
            globalStore.setErrorMessage(
                err.response.data?.data?.error
                    ? err.response.data.data.error
                    : err.response.data.message
            );
        }
    };

    const getUserPurchases = async (id) => {
        // Fetch all user purchases
        globalStore.loading = true;
        try {
            const response = await userApi.getUserPurchases(id);
            globalStore.loading = false;
            return response;
        } catch (err) {
            globalStore.loading = false;
            globalStore.setErrorMessage(
                err.response.data?.data?.error
                    ? err.response.data.data.error
                    : err.response.data.message
            );
        }
    };

    const getUserSales = async (id) => {
        // Fetch all user sales
        globalStore.loading = true;
        try {
            const response = await userApi.getUserSales(id);
            globalStore.loading = false;
            return response;
        } catch (err) {
            globalStore.loading = false;
            globalStore.setErrorMessage(
                err.response.data?.data?.error
                    ? err.response.data.data.error
                    : err.response.data.message
            );
        }
    };

    const getUserSignals = async (id) => {
        // Fetch all user signal payments
        globalStore.loading = true;
        try {
            const response = await userApi.getUserSignals(id);
            globalStore.loading = false;
            return response;
        } catch (err) {
            globalStore.loading = false;
            globalStore.setErrorMessage(
                err.response.data?.data?.error
                    ? err.response.data.data.error
                    : err.response.data.message
            );
        }
    };

    const getUserFiatWithdrawals = async (id) => {
        // Fetch all fiat withdrawals
        globalStore.loading = true;
        try {
            const response = await userApi.getUserFiatWithdrawals(id);
            globalStore.loading = false;
            return response;
        } catch (err) {
            globalStore.loading = false;
            globalStore.setErrorMessage(
                err.response.data?.data?.error
                    ? err.response.data.data.error
                    : err.response.data.message
            );
        }
    };

    const getUserBanks = async (id) => {
        // Fetch all user banks
        globalStore.loading = true;
        try {
            const response = await userApi.getUserBanks(id);
            globalStore.loading = false;
            return response;
        } catch (err) {
            globalStore.loading = false;
            globalStore.setErrorMessage(
                err.response.data?.data?.error
                    ? err.response.data.data.error
                    : err.response.data.message
            );
        }
    };

    const getUserReferrals = async (id) => {
        // Fetch all user referrals
        globalStore.loading = true;
        try {
            const response = await userApi.getUserReferrals(id);
            globalStore.loading = false;
            return response;
        } catch (err) {
            globalStore.loading = false;
            globalStore.setErrorMessage(
                err.response.data?.data?.error
                    ? err.response.data.data.error
                    : err.response.data.message
            );
        }
    };

    const getUserVerification = async (id) => {
        // Fetch all user referrals
        globalStore.loading = true;
        try {
            const response = await userApi.getUserVerification(id);
            globalStore.loading = false;
            return response;
        } catch (err) {
            globalStore.loading = false;
            globalStore.setErrorMessage(
                err.response.data?.data?.error
                    ? err.response.data.data.error
                    : err.response.data.message
            );
        }
    };

    // expose necessary data
    return {
        getUsersList,
        getUsersWallets,
        getUsersBanks,
        getUserProfile,
        getUserWithdrawals,
        getUserDeposits,
        getUserSwaps,
        getUserPurchases,
        getUserSales,
        getUserSignals,
        getUserFiatWithdrawals,
        getUserBanks,
        getUserReferrals,
        getUserVerification
    };
});
