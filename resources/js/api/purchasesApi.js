import BASE_URL from "./baseUrl";

const purchaseApi = axios.create({
    baseURL: BASE_URL,
    withCredentials: false,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    }
});

// Purchase  API calls
export const getPurchaseList = async (index = 0) => {
    const response = await purchaseApi.get(`/purchases/all/${index}`);
    return response.data.data;
}


export default {
  getPurchaseList,
}