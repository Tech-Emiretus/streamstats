<template>
    <div class="bg-white shadow rounded-lg p-8 w-100% md:ml-5 md:mr-0 mx-5 mb-10">
        <h2 class="text-2xl text-center font-normal mb-6 text-90">Streams By Hour</h2>

        <svg class="block mx-auto mb-6" xmlns="http://www.w3.org/2000/svg" width="100" height="2" viewBox="0 0 100 2">
            <path fill="#D8E3EC" d="M0 0h100v2H0z"></path>
        </svg>

        <template v-if="streams && streams.length">
            <tool-bar
                :sortFields="sortFields"
                :showPerPage="true"
                defaultSortField="stream_hour"
                defaultSortOrder="ASC"
                :defaultPerPage="20"
                @filtersChanged="data => Object.assign(filters, data)"
                ></tool-bar>

            <app-table>
                <thead class="bg-gray-50">
                    <tr>
                        <table-header>Hour</table-header>
                        <table-header>No. of Streams</table-header>
                    </tr>
                </thead>

                <tbody class="bg-white">
                    <tr v-for="stream in streams" class="whitespace-nowrap" :key="stream.id">
                        <table-data>{{ formatHour(stream.stream_hour) }}</table-data>
                        <table-data>{{ stream.streams_count }}</table-data>
                    </tr>
                </tbody>
            </app-table>

            <pagination
                :pageDetails="pageDetails"
                @navigate="data => Object.assign(page, data)"
            ></pagination>
        </template>

        <template v-else>
            <p class="text-center">No data available.</p>
        </template>
    </div>
</template>

<script>
    import { onMounted, ref, reactive, watch } from "vue";
    import { pickBy } from "lodash";
    import streamstatsService from "../services/streamstats";
    import moment from 'moment';

    export default {
        name: 'StreamsByHour',

        setup() {
            const sortFields = ref([
                {field: 'stream_hour', name: 'Hour'},
                {field: 'streams_count', name: 'No. of Streams'}
            ]);

            const filters = reactive({
                sort_field: 'stream_hour',
                sort_order: 'ASC',
                per_page: 20
            });

            const page = reactive({
                page: 1
            });

            const streams = ref([]);
            const pageDetails = reactive({});

            const getStreamsByHour = async () => {
                try {
                    const response = await streamstatsService.getStreamsByHour({ ...filters, ...page });

                    streams.value = response.data.data;
                    Object.assign(pageDetails, pickBy(response.data, (v, key) => key !== 'data'));
                } catch (err) {
                    console.log(`Fetch Streams By Hour Error: ${err}`);
                }
            };

            const formatHour = (time) => {
                return moment(time).format('MMM DD, HH:00 A');
            };

            onMounted(async () => {
                await getStreamsByHour();
            });

            watch(filters, () => {
                page.page = 1;
                getStreamsByHour();
            });
            watch(page, getStreamsByHour);

            return {
                streams,
                sortFields,
                filters,
                page,
                getStreamsByHour,
                pageDetails,
                formatHour
            }
        }
    }
</script>
