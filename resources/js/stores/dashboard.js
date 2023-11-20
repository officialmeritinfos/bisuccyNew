import { defineStore } from "pinia";
import { ref } from "vue";
import dashboardApi from "../api/dashboardApi";
import { useGlobalStore } from "./global";

export const useDashboardStore = defineStore("dashboard", () => {
    const globalStore = useGlobalStore();
    // Set the default states
    const adminDetails = ref(null);
    const dashboardData = ref(null);
    const dashboardTransactions = ref(null);

    // Perform some actions
    const getAdminDetails = async () => {
        await globalStore.setLoading(true);
        adminDetails.value = await dashboardApi.getAdminDetails();
        await globalStore.setLoading(false);
    };

    const setUserPin = async (payload) => {
        await globalStore.setLoading(true);
        try{
            const response = await dashboardApi.setUserPin(payload);
            globalStore.setSuccessMessage(response?.message ? response.message : "Success" )
            globalStore.showSetPinModal(false);
        }catch(err) {
            globalStore.setErrorMessage(err.response.data?.data?.error ? err.response.data.data.error : err.response.data.message )
        }
        await globalStore.setLoading(false);
        
    }

    const changePassword = async (payload) => {
        await globalStore.setLoading(true);
        try{
            const response = await dashboardApi.changePassword(payload);
            globalStore.setSuccessMessage(response?.message ? response.message : "Success" )
            globalStore.showSetPinModal(false);
        }catch(err) {
            globalStore.setErrorMessage(err.response.data?.data?.error ? err.response.data.data.error : err.response.data.message )
        }
        await globalStore.setLoading(false);
        
    }

    const getDashboardData = async () => {
        await globalStore.setLoading(true);
        dashboardData.value = await dashboardApi.getDashboardData();
        await globalStore.setLoading(false);
    };

    
    const getDashboardTransactions = async () => {
        await globalStore.setLoading(true);
        dashboardTransactions.value = await dashboardApi.getDashboardTransactions();
        await globalStore.setLoading(false);
    };


    // expose necessary data
    return { 
        getAdminDetails, 
        adminDetails, 
        setUserPin,
        changePassword,
        getDashboardData,
        dashboardData,
        getDashboardTransactions,
        dashboardTransactions 
    };
});
