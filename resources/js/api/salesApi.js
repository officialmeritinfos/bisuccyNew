import BASE_URL from "./baseUrl";

const salesApi = axios.create({
    baseURL: BASE_URL,
    withCredentials: false,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    }
});

// Sales  API calls
export const getSalesList = async (index = 0) => {
    const response = await salesApi.get(`/sales/all/${index}`);
    return response.data.data;
}


export default {
  getSalesList,
}