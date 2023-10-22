import BASE_URL from "./baseUrl";

const messagesApi = axios.create({
    baseURL: BASE_URL,
    withCredentials: false,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    }
});

// Messages  API calls
export const getMessagesList = async (index = 0) => {
    const response = await messagesApi.get(`/messages/all/`);
    return response.data.data;
}

export const createMessage = async (payload) => {
    const response = await messagesApi.post('messages/create', payload);
    return response.data.data;
}


export default {
    getMessagesList,
    createMessage
}