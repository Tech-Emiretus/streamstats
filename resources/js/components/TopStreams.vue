<template>
    <div class="bg-white shadow rounded-lg p-8 w-100% mx-5 mb-10">
        <h2 class="text-2xl text-center font-normal mb-6 text-90">Top 100 Streams</h2>

        <svg class="block mx-auto mb-6" xmlns="http://www.w3.org/2000/svg" width="100" height="2" viewBox="0 0 100 2">
            <path fill="#D8E3EC" d="M0 0h100v2H0z"></path>
        </svg>

        <template v-if="streams.length">
            <tool-bar
                :sortFields="sortFields"
                :showPerPage="false"
                defaultSortField="viewer_count"
                defaultSortOrder="DESC"
                @filtersChanged="data => Object.assign(filters, data)"
                ></tool-bar>

            <app-table>
                <thead class="bg-gray-50">
                    <tr>
                        <table-header>Twitch ID</table-header>
                        <table-header>Title</table-header>
                        <table-header>Game</table-header>
                        <table-header>Broadcaster</table-header>
                        <table-header>Language</table-header>
                        <table-header>No. of Views</table-header>
                    </tr>
                </thead>

                <tbody class="bg-white">
                    <tr v-for="stream in streams" :key="stream.id">
                        <table-data>{{ stream.twitch_id || 'N/A' }}</table-data>
                        <table-data>{{ stream.title || 'N/A' }}</table-data>
                        <table-data>{{ stream.game?.name || 'N/A' }}</table-data>
                        <table-data>{{ stream.broadcaster?.name || 'N/A' }}</table-data>
                        <table-data>{{ stream.language || 'N/A' }}</table-data>
                        <table-data>{{ stream.viewer_count }}</table-data>
                    </tr>
                </tbody>
            </app-table>
        </template>

        <template v-else>
            <p>No data available.</p>
        </template>
    </div>
</template>

<script>
    import { onMounted, ref, reactive, watch } from "vue";
    import streamstatsService from "../services/streamstats";

    export default {
        name: 'TopStreams',

        setup() {
            const sortFields = ref([
                {field: 'viewer_count', name: 'No. of Views'}
            ]);

            const filters = reactive({
                sort_field: 'viewer_count',
                sort_order: 'DESC',
            });

            const streams = ref([]);

            const getTopStreams = async () => {
                try {
                    const response = await streamstatsService.getTopStreams({ ...filters });
                    streams.value = response.data;
                } catch (err) {
                    console.log(`Fetch Top streams Error: ${err}`);
                }
            };

            onMounted(async () => {
                await getTopStreams();
            });

            watch(filters, getTopStreams);

            return {
                streams,
                sortFields,
                filters,
                getTopStreams,
            }
        }
    }
</script>
