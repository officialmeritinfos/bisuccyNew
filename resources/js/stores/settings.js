import { defineStore } from "pinia";
import { ref } from "vue";
import settingsApi from "../api/settingsApi";
import { useGlobalStore } from "./global";

export const useSettingsStore = defineStore("settingsStore", () => {
    const globalStore = useGlobalStore();

    // Perform some actions

    const getFiatList = async () => {
        // Fetch all system fiats
        globalStore.loading = true;
        try {
            const response = await settingsApi.getFiatList();
            globalStore.loading = false;
            return response;
        } catch(err) {
            globalStore.loading = false;
            globalStore.setErrorMessage(err.response.data?.data?.error ? err.response.data.data.error : err.response.data.message )
        }
        
    } 

    const createFiat = async (payload) => {
        // Create a fiat
        globalStore.loading = true;
        try {
            const response = await settingsApi.createFiat(payload);
            globalStore.loading = false;
            return response;
        } catch(err) {
            globalStore.loading = false;
            globalStore.setErrorMessage(err.response.data?.data?.error ? err.response.data.data.error : err.response.data.message )
        }
        
    }

    // expose necessary data
    return { 
      getFiatList,
      createFiat
    };
});
