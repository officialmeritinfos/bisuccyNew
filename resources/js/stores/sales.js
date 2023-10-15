import { defineStore } from "pinia";
import salesApi from "../api/salesApi";
import { useGlobalStore } from "./global";

export const useSalesStore = defineStore("salesStore", () => {
    const globalStore = useGlobalStore();

    // Perform some actions
    const getSalesList = async () => {
        // Fetch all system fiats
        globalStore.loading = true;
        try {
            const response = await salesApi.getSalesList();
            globalStore.loading = false;
            return response;
        } catch(err) {
            globalStore.loading = false;
            globalStore.setErrorMessage(err.response.data?.data?.error ? err.response.data.data.error : err.response.data.message )
        }
    };

    // expose necessary data
    return { 
        getSalesList
    };
});
