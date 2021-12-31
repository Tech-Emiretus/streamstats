<template>
    <nav-bar :user="user"></nav-bar>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <median-view-count></median-view-count>
            <games-by-stream-count></games-by-stream-count>
        </div>

        <div>
            <min-view-count-needed></min-view-count-needed>
        </div>
    </div>
</template>

<script>
    import { onMounted, ref } from 'vue';
    import NavBar from './components/NavBar.vue';
    import { user, getUserDetails } from './composables/user_details';
    import { refreshStreams } from './composables/refresh_streams';
    import MedianViewCount from './components/MedianViewCount.vue';
    import MinViewCountNeeded from './components/MinViewCountNeeded.vue';
    import GamesByStreamCount from './components/GamesByStreamCount.vue';

    export default {
        name: 'App',

        components: {
            NavBar,
            MedianViewCount,
            MinViewCountNeeded,
            GamesByStreamCount,
        },

        setup() {
            onMounted(async () => {
                await getUserDetails();
                await refreshStreams(true);
            });

            return {
                user
            }
        }
    }
</script>
