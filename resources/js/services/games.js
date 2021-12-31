import Axios from './index';

export default {
    getByStreamCount(params) {
        return Axios.get('games/by-stream-count', { params })
            .then(response => Promise.resolve(response.data))
            .catch(error => Promise.reject(error.response.data));
    },

    getByViewerCount(params) {
        return Axios.get('games/by-viewer-count', { params })
            .then(response => Promise.resolve(response.data))
            .catch(error => Promise.reject(error.response.data));
    }
}
