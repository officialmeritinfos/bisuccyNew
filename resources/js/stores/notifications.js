import { defineStore } from "pinia";
import notificationsApi from "../api/notificationsApi.js";
import { useGlobalStore } from "./global.js";

export const useNotificationsStore = defineStore("notificationsStore", () => {
    const globalStore = useGlobalStore();

    // Perform some actions
    const getNotificationsList = async () => {
        // Fetch all notifications
        globalStore.loading = true;
        try {
            const response = await notificationsApi.getNotificationsList();
            globalStore.loading = false;
            return response;
        } catch(err) {
            globalStore.loading = false;
            globalStore.setErrorNotification(err.response.data?.data?.error ? err.response.data.data.error : err.response.data.notification )
        }
    };

    const createNotification = async (payload) => {
        // Fetch all notifications
        globalStore.loading = true;
        try {
            const response = await notificationsApi.createNotification(payload);
            globalStore.loading = false;
            return response;
        } catch(err) {
            globalStore.loading = false;
            globalStore.setErrorNotification(err.response.data?.data?.error ? err.response.data.data.error : err.response.data.notification )
        }
    };

    // expose necessary data
    return { 
        getNotificationsList,
        createNotification
    };
});
