import { defineStore } from "pinia";
import swapsApi from "../api/swapsApi.js";
import { useGlobalStore } from "./global";

export const useSwapsStore = defineStore("swapsStore", () => {
    const globalStore = useGlobalStore();

    // Perform some actions
    const getSwapsList = async () => {
        // Fetch all system fiats
        globalStore.loading = true;
        try {
            const response = await swapsApi.getSwapsList();
            globalStore.loading = false;
            return response;
        } catch(err) {
            globalStore.loading = false;
            globalStore.setErrorMessage(err.response.data?.data?.error ? err.response.data.data.error : err.response.data.message )
        }
    };

    // expose necessary data
    return { 
        getSwapsList
    };
});
