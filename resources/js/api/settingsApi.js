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


// Fiat Settings  API calls
export const createFiat = async (payload) => {
    console.log('payload', payload)
    const response = await settingsApi.post(`/fiats/create`, payload);
    return response.data.data;
}


export default {
  getFiatList,
  createFiat
}