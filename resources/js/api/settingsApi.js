import BASE_URL from "./baseUrl";

const settingsApi = axios.create({
    baseURL: BASE_URL,
    withCredentials: false,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    }
});

// Fiat Settings  API calls
export const getFiatList = async () => {
    const response = await settingsApi.get(`/fiats/all`);
    return response.data.data;
}

export const createFiat = async (payload) => {
    const response = await settingsApi.post('/fiats/create', payload);
    return response.data.data;
}

export const getSettings = async () => {
    const response = await settingsApi.get(`/settings/get`);
    return response.data.data;
}

export const updateSettings = async (payload) => {
    const response = await settingsApi.post('/settings/edit', payload);
    return response.data.data;
}


export default {
  getFiatList,
  createFiat,
  getSettings,
  updateSettings
}