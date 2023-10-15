import { defineStore } from "pinia";
import purchasesApi from "../api/purchasesApi";
import { useGlobalStore } from "./global";

export const usePurchasesStore = defineStore("purchasesStore", () => {
    const globalStore = useGlobalStore();

    // Perform some actions
    const getPurchaseList = async () => {
        // Fetch all system fiats
        globalStore.loading = true;
        try {
            const response = await purchasesApi.getPurchaseList();
            globalStore.loading = false;
            return response;
        } catch(err) {
            globalStore.loading = false;
            globalStore.setErrorMessage(err.response.data?.data?.error ? err.response.data.data.error : err.response.data.message )
        }
    };

    // expose necessary data
    return { 
        getPurchaseList
    };
});
