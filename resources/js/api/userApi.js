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

export const getUserWallets = async () => {
    const response = await userApi.get(`/user-wallets/all`);
    return response.data.data;
};

export const getUserBanks = async () => {
    const response = await userApi.get(`/user-banks/all`);
    return response.data.data;
};

export default {
    getUsersList,
    createUser,
    getUserWallets,
    getUserBanks
};
