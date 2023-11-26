import { defineStore } from "pinia";
import staffApi from "../api/staffApi.js";
import { useGlobalStore } from "./global.js";

export const useStaffStore = defineStore("staffStore", () => {
    const globalStore = useGlobalStore();

    // Perform some actions
    const getStaffList = async () => {
        // Fetch all system staff
        globalStore.loading = true;
        try {
            const response = await staffApi.getStaffList();
            globalStore.loading = false;
            return response;
        } catch(err) {
            globalStore.loading = false;
            globalStore.setErrorMessage(err.response.data?.data?.error ? err.response.data.data.error : err.response.data.message )
        }
    };

    const createStaff = async (payload) => {
        // Create staff
        globalStore.loading = true;
        try {
            const response = await staffApi.createStaff(payload);
            globalStore.loading = false;
            globalStore.setSuccessMessage(response?.message ? response.message : "Success" )
            return response;
        } catch(err) {
            globalStore.loading = false;
            globalStore.setErrorMessage(err.response.data?.data?.error ? err.response.data.data.error : err.response.data.message )
        }
    };

    const getRoles = async () => {
        // Fetch all system roles
        globalStore.loading = true;
        try {
            const response = await staffApi.getRoles();
            globalStore.loading = false;
            return response;
        } catch(err) {
            globalStore.loading = false;
            globalStore.setErrorMessage(err.response.data?.data?.error ? err.response.data.data.error : err.response.data.message )
        }
    };


    const createRole = async (payload) => {
        // Create role
        globalStore.loading = true;
        try {
            const response = await staffApi.createRole(payload);
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
        getStaffList,
        createStaff,
        getRoles,
        createRole
    };
});
