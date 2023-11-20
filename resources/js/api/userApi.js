import BASE_URL from "./baseUrl";

const userApi = axios.create({
    baseURL: BASE_URL,
    withCredentials: false,
    headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
    },
});

// Users API calls
export const getUsersList = async () => {
    const response = await userApi.get(`/users/all`);
    return response.data.data;
};

export const createUser = async (payload) => {
    const response = await settingsApi.post(`/fiats/create`, payload);
    return response.data.data;
};

export const getUsersWallets = async () => {
    const response = await userApi.get(`/user-wallets/all`);
    return response.data.data;
};

export const getUsersBanks = async () => {
    const response = await userApi.get(`/user-banks/all`);
    return response.data.data;
};

export const getUserProfile = async (id) => {
    const response = await userApi.get(`/api/users/${id}`);
    return response.data.data;
};

export const getUserWithdrawals = async (id) => {
    const response = await userApi.get(`/api/users/withdrawals/${id}`);
    return response.data.data;
};

export const getUserDeposits = async (id) => {
    const response = await userApi.get(`/api/users/deposits/${id}`);
    return response.data.data;
};

export const getUserSwaps = async (id) => {
    const response = await userApi.get(`/api/users/swaps/${id}`);
    return response.data.data;
};

export const getUserPurchases = async (id) => {
    const response = await userApi.get(`/api/users/purchases/${id}`);
    return response.data.data;
};

export const getUserSales = async (id) => {
    const response = await userApi.get(`/api/users/sales/${id}`);
    return response.data.data;
};

export const getUserSignals = async (id) => {
    const response = await userApi.get(`/api/users/signal-payments/${id}`);
    return response.data.data;
};

export const getUserFiatWithdrawals = async (id) => {
    const response = await userApi.get(`/api/users/fiat-withdrawals/${id}`);
    return response.data.data;
};

export const getUserBanks = async (id) => {
    const response = await userApi.get(`/api/users/banks/${id}`);
    return response.data.data;
};

export const getUserReferrals = async (id) => {
    const response = await userApi.get(`/api/users/referrals/${id}`);
    return response.data.data;
};

export const getUserVerification = async (id) => {
    const response = await userApi.get(`/api/users/documents/${id}`);
    return response.data.data;
};

export default {
    getUsersList,
    createUser,
    getUsersWallets,
    getUsersBanks,
    getUserProfile,
    getUserWithdrawals,
    getUserDeposits,
    getUserSwaps,
    getUserPurchases,
    getUserSales,
    getUserSignals,
    getUserFiatWithdrawals,
    getUserFiatWithdrawals,
    getUserBanks,
    getUserReferrals,
    getUserVerification
};
