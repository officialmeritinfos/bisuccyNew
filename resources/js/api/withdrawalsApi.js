import BASE_URL from "./baseUrl";

const withdrawalApi = axios.create({
    baseURL: BASE_URL,
    withCredentials: false,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    }
});

// Withdrawals  API calls
// export const getWithdrawalList = async (index = 0) => {
//     const response = await withdrawalApi.get(`/withdrawals/all/${index}`);
//     return response.data.data;
// }

export const getFiatWithdrawalList = async (index = 0) => {
    const response = await withdrawalApi.get(`/fiat-withdrawals/all/${index}`);
    return response.data.data;
}

export const getFiatWithdrawalsDetail = async (id) => {
    const response = await withdrawalApi.get(`/fiat-withdrawals/${id}`);
    return response.data.data;
}
export const approveFiatWithdrawal = async (payload) => {
    const response = await withdrawalApi.post('/fiat-withdrawals/approve', payload);
    return response.data.data;
}
export const rejectFiatWithdrawal = async (payload) => {
    const response = await withdrawalApi.post('/fiat-withdrawals/cancel', payload);
    return response.data.data;
}
// CRYPTO WITHDRAWALS
export const getAllCryptoWithdrawals = async (index = 0) => {
    const response = await withdrawalApi.get(`/withdrawals/all/${index}`);
    return response.data.data;
}

export default {
    getFiatWithdrawalList,
    getFiatWithdrawalsDetail,
    approveFiatWithdrawal,
    getAllCryptoWithdrawals,
    rejectFiatWithdrawal
}