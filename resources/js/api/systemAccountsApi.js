import BASE_URL from "./baseUrl";

const systemAccountsApi = axios.create({
    baseURL: BASE_URL,
    withCredentials: false,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    }
});

// SystemAccounts  API calls
export const getSystemAccountsList = async (index = 0) => {
    const response = await systemAccountsApi.get(`/system-accounts/all/`);
    return response.data.data;
}

export const getSystemAccount = async (id) => {
    const response = await systemAccountsApi.get(`/system-accounts/${id}`);
    return response.data.data;
}

export const getSystemAccountWithdrawals = async () => {
    const response = await systemAccountsApi.get(`/system-accounts/allWithdrawals`);
    return response.data.data;
}

export const withdrawFromSystemAccount = async (payload) => {
    const response = await systemAccountsApi.post('/system-accounts/withdraw', payload);
    return response.data.data;
}

export const approveSystemAccountWithdrawal = async (payload) => {
    const response = await systemAccountsApi.post('/system-accounts/approveWithdrawal', payload);
    return response.data.data;
}

export default {
    getSystemAccountsList,
    getSystemAccount,
    getSystemAccountWithdrawals,
    withdrawFromSystemAccount
}