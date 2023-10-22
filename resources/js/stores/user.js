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

    const getUserWallets = async () => {
        // Fetch all user wallets
        globalStore.loading = true;
        try {
            const response = await userApi.getUserWallets();
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

    const getUserBanks = async () => {
        // Fetch all user banks
        globalStore.loading = true;
        try {
            const response = await userApi.getUserBanks();
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
        getUserWallets,
        getUserBanks
    };
});
