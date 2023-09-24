import BASE_URL from "./baseUrl";

const depositsApi = axios.create({
    baseURL: BASE_URL,
    withCredentials: false,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    }
});

// Deposits  API calls
export const getDepositsList = async (index = 0) => {
    const response = await depositsApi.get(`/deposits/all/${index}`);
    return response.data.data;
}

export const getFiatDepositList = async (index = 0) => {
    const response = await depositsApi.get(`/fiat-deposits/all/${index}`);
    return response.data.data;
}

export const getFiatDeposit = async (id) => {
    const response = await depositsApi.get(`/fiat-deposits/${id}`);
    return response.data.data;
}

export const approveFiatDeposit = async (payload) => {
    const response = await depositsApi.post('fiat-deposits/approve', payload);
    return response.data.data;
}

export const rejectFiatDeposit = async (payload) => {
    const response = await depositsApi.post('fiat-deposits/cancel', payload);
    return response.data.data;
}

export const getAllCryptoDeposits = async (index = 0) => {
    const response = await depositsApi.get(`deposits/all/${index}`);
    return response.data.data;
}

export default {
    getDepositsList,
    getFiatDepositList,
    getFiatDeposit,
    approveFiatDeposit,
    rejectFiatDeposit,
    getAllCryptoDeposits
}