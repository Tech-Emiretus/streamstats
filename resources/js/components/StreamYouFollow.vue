<template>
    <div class="bg-white shadow rounded-lg p-8 w-100% mx-5 mb-10">
        <h2 class="text-2xl text-center font-normal mb-6 text-90">Top Streams You Follow</h2>

        <svg class="block mx-auto mb-6" xmlns="http://www.w3.org/2000/svg" width="100" height="2" viewBox="0 0 100 2">
            <path fill="#D8E3EC" d="M0 0h100v2H0z"></path>
        </svg>

        <template v-if="streams.length">
            <tool-bar
                :showPerPage="true"
                :sortFields="sortFields"
                defaultSortField="viewer_count"
                defaultSortOrder="DESC"
                :defaultPerPage="20"
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

            <pagination
                :pageDetails="pageDetails"
                @navigate="data => Object.assign(page, data)"
            ></pagination>
        </template>

        <template v-else>
            <p>No data available.</p>
        </template>
    </div>
</template>

<script>
    import { onMounted, ref, reactive, watch } from "vue";
    import { pickBy } from "lodash";
    import streamstatsService from "../services/streamstats";

    export default {
        name: 'StreamsYouFollow',

        setup() {
            const sortFields = ref([
                {field: 'title', name: 'Title'},
                {field: 'viewer_count', name: 'No. of Views'}
            ]);

            const filters = reactive({
                sort_field: 'viewer_count',
                sort_order: 'DESC',
            });

            const page = reactive({
                page: 1
            });

            const streams = ref([]);
            const pageDetails = reactive({});

            const getStreamsYouFollow = async () => {
                try {
                    const response = await streamstatsService.getStreamsFollowedByUser({ ...filters, ...page });
                    streams.value = response.data.data;
                    Object.assign(pageDetails, pickBy(response.data, (v, key) => key !== 'data'));
                } catch (err) {
                    console.log(`Fetch Streams You Follow Error: ${err}`);
                }
            };

            onMounted(async () => {
                await getStreamsYouFollow();
            });

            watch(filters, () => {
                page.page = 1;
                getStreamsYouFollow();
            });
            watch(page, getStreamsYouFollow);

            return {
                streams,
                sortFields,
                filters,
                page,
                pageDetails,
                getStreamsYouFollow,
            }
        }
    }
</script>
