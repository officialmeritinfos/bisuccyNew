import { defineStore } from "pinia";
import messagesApi from "../api/messagesApi.js";
import { useGlobalStore } from "./global.js";

export const useMessagesStore = defineStore("messagesStore", () => {
    const globalStore = useGlobalStore();

    // Perform some actions
    const getMessagesList = async () => {
        // Fetch all messages
        globalStore.loading = true;
        try {
            const response = await messagesApi.getMessagesList();
            globalStore.loading = false;
            return response;
        } catch(err) {
            globalStore.loading = false;
            globalStore.setErrorMessage(err.response.data?.data?.error ? err.response.data.data.error : err.response.data.message )
        }
    };

    const createMessage = async (payload) => {
        // Create message
        globalStore.loading = true;
        try {
            const response = await messagesApi.createMessage(payload);
            globalStore.loading = false;
            globalStore.setSuccessMessage(response?.message ? response.message : "Success" )
        } catch(err) {
            globalStore.loading = false;
            globalStore.setErrorMessage(err.response.data?.data?.error ? err.response.data.data.error : err.response.data.message )
        }
    };

    // expose necessary data
    return { 
        getMessagesList,
        createMessage
    };
});
