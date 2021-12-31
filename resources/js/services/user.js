import Axios from './index';

export default {
    getDetails() {
        return Axios.get('user')
            .then(response => Promise.resolve(response.data))
            .catch(error => Promise.reject(error.response.data));
    },

    refreshStreams() {
        return Axios.get('refresh-streams')
            .then(response => Promise.resolve(response.data))
            .catch(error => Promise.reject(error.response.data));
    },

}
