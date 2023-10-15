import BASE_URL from "./baseUrl";

const swapsApi = axios.create({
    baseURL: BASE_URL,
    withCredentials: false,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    }
});

// Swaps  API calls
export const getSwapsList = async (index = 0) => {
    const response = await swapsApi.get(`/swaps/all/${index}`);
    return response.data.data;
}


export default {
  getSwapsList,
}