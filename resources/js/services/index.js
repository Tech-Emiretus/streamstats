import Axios from 'axios';
import { ref } from 'vue';

const baseURL = '/';
export const isFetching = ref(false);

const axios = Axios.create({
    baseURL,
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
    }
});

axios.interceptors.request.use((request) => {
    isFetching.value = true;
    return request
});

/**
 * Intercept response and redirect user to error page if 403
 */
axios.interceptors.response.use(
    (response) => {
        isFetching.value = false;
        return response;
    },
    (error) => {
        isFetching.value = false;

        if (error?.response?.status === 401) {
            window.location.href = '/login';
            return;
        }

        return Promise.reject(error);
    }
);

export default axios;
