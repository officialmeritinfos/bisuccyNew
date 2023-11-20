import BASE_URL from "./baseUrl";

const staffApi = axios.create({
    baseURL: BASE_URL,
    withCredentials: false,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    }
});

// Staff  API calls
export const getStaffList = async () => {
    const response = await staffApi.get(`/staff/all`);
    return response.data.data;
}

export const createStaff = async (payload) => {
    const response = await staffApi.post(`/api/staff/add`, payload);
    return response.data.data;
};

export const getRoles = async () => {
    const response = await staffApi.get(`/permissions/roles`);
    return response.data.data;
}

export const createRole = async (payload) => {
    const response = await staffApi.post(`/api/permissions/create`, payload);
    return response.data.data;
};

export default {
  getStaffList,
  createStaff,
  getRoles,
  createRole
}