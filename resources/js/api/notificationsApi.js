import BASE_URL from "./baseUrl";

const notificationsApi = axios.create({
    baseURL: BASE_URL,
    withCredentials: false,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    }
});

// Notifications  API calls
export const getNotificationsList = async (index = 0) => {
    const response = await notificationsApi.get(`/notifications/all/`);
    return response.data.data;
}

export const createNotification = async (payload) => {
    const response = await notificationsApi.post('notifications/create', payload);
    return response.data.data;
}


export default {
    getNotificationsList,
    createNotification
}