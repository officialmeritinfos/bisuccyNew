import { defineStore } from "pinia";
import signalsApi from "../api/signalsApi.js";
import { useGlobalStore } from "./global.js";

export const useSignalsStore = defineStore("signalsStore", () => {
    const globalStore = useGlobalStore();

    // Perform some actions
    const getSignalsList = async () => {
        // Fetch all signals
        globalStore.loading = true;
        try {
            const response = await signalsApi.getSignalsList();
            globalStore.loading = false;
            return response;
        } catch(err) {
            globalStore.loading = false;
            globalStore.setErrorMessage(err.response.data?.data?.error ? err.response.data.data.error : err.response.data.message )
        }
    };

    const createSignal = async (payload) => {
        // Create signal
        globalStore.loading = true;
        try {
            const response = await signalsApi.createSignal(payload);
            globalStore.loading = false;
            globalStore.setSuccessMessage(response?.message ? response.message : "Success" )
            return response;
        } catch(err) {
            globalStore.loading = false;
            globalStore.setErrorMessage(err.response.data?.data?.error ? err.response.data.data.error : err.response.data.message )
        }
    };

    // expose necessary data
    return { 
        getSignalsList,
        createSignal
    };
});
