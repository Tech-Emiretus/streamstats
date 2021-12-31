import Axios from './index';

export default {
    getTopStreams(params) {
        return Axios.get('streams/top-streams', { params })
            .then(response => Promise.resolve(response.data))
            .catch(error => Promise.reject(error.response.data));
    },

    getStreamsFollowedByUser(params) {
        return Axios.get('streams/followed-by-user', { params })
            .then(response => Promise.resolve(response.data))
            .catch(error => Promise.reject(error.response.data));
    },

    getMedianViewCount() {
        return Axios.get('streams/median-view-count')
            .then(response => Promise.resolve(response.data))
            .catch(error => Promise.reject(error.response.data));
    },

    getMinViewerCountNeeded() {
        return Axios.get('streams/min-viewer-count-needed')
            .then(response => Promise.resolve(response.data))
            .catch(error => Promise.reject(error.response.data));
    },

    getStreamsByHour(params) {
        return Axios.get('streams/count-by-hour', { params })
            .then(response => Promise.resolve(response.data))
            .catch(error => Promise.reject(error.response.data));
    },

    getSharedTags(params) {
        return Axios.get('streams/shared-tags', { params })
            .then(response => Promise.resolve(response.data))
            .catch(error => Promise.reject(error.response.data));
    }
}
