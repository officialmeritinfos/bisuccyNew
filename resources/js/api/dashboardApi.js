import BASE_URL from "./baseUrl";

const dashboardApi = axios.create({
    baseURL: BASE_URL,
    withCredentials: false,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    }
});

// Get admin profile
export const getAdminDetails = async () => {
    const response = await dashboardApi.get(`/dashboard/admin-details`);
    return response.data.data;
}

// Set pin for approving transactions
export const setUserPin = async (payload) => {
    const response = await dashboardApi.post(`/dashboard/set-pin`, payload);
    return response.data;
}

// Change account password
const changePassword = async (payload) => {
    const response = await dashboardApi.post('/dashboard/do-change-password', payload);
    return response.data.data;
};

// Get dashboard stats
export const getDashboardData = async () => {
    const response = await dashboardApi.get(`/dashboard/get-dashboard-data`);
    return response.data.data;
}

// Get dashboard stats
export const getDashboardTransactions = async () => {
    const response = await dashboardApi.get(`/dashboard/get-transactions-data`);
    return response.data.data;
}

export default {
    getAdminDetails,
    setUserPin,
    changePassword,
    getDashboardData,
    getDashboardTransactions
}