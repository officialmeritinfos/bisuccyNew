import BASE_URL from "./baseUrl";

const signalsApi = axios.create({
    baseURL: BASE_URL,
    withCredentials: false,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    }
});

// Signals  API calls
export const getSignalsList = async (index = 0) => {
    const response = await signalsApi.get(`/signals/all/`);
    return response.data.data;
}

export const createSignal = async (payload) => {
    const response = await signalsApi.post('signals/create', payload);
    return response.data.data;
}


export default {
    getSignalsList,
    createSignal
}