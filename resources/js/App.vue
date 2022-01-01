<template>
    <nav-bar :user="user"></nav-bar>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <median-view-count></median-view-count>
        </div>

        <div>
            <min-view-count-needed></min-view-count-needed>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <games-by-stream-count></games-by-stream-count>
        </div>

        <div>
            <games-by-viewer-count></games-by-viewer-count>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <streams-by-hour></streams-by-hour>
        </div>

        <div>
            <top-tags-you-follow></top-tags-you-follow>
        </div>
    </div>

    <div class="mb-5">
        <top-streams></top-streams>
        <stream-you-follow></stream-you-follow>
    </div>
</template>

<script>
    import { onMounted } from 'vue';
    import NavBar from './components/NavBar.vue';
    import { user, getUserDetails } from './composables/user_details';
    import { refreshStreams } from './composables/refresh_streams';
    import MedianViewCount from './components/MedianViewCount.vue';
    import MinViewCountNeeded from './components/MinViewCountNeeded.vue';
    import GamesByStreamCount from './components/GamesByStreamCount.vue';
    import GamesByViewerCount from './components/GamesByViewerCount.vue';
    import TopStreams from './components/TopStreams.vue';
    import StreamYouFollow from './components/StreamYouFollow.vue';
    import StreamsByHour from './components/StreamsByHour.vue';
    import TopTagsYouFollow from './components/TopTagsYouFollow.vue';

    export default {
        name: 'App',

        components: {
            NavBar,
            MedianViewCount,
            MinViewCountNeeded,
            GamesByStreamCount,
            GamesByViewerCount,
            TopStreams,
            StreamYouFollow,
            StreamsByHour,
            TopTagsYouFollow
        },

        setup() {
            onMounted(async () => {
                await getUserDetails();
                await refreshStreams();
            });

            return {
                user
            }
        }
    }
</script>
