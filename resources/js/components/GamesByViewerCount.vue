<template>
    <div class="bg-white shadow rounded-lg p-8 w-100% mr-5 mb-10">
        <h2 class="text-2xl text-center font-normal mb-6 text-90">Games By Viewer Count</h2>

        <svg class="block mx-auto mb-6" xmlns="http://www.w3.org/2000/svg" width="100" height="2" viewBox="0 0 100 2">
            <path fill="#D8E3EC" d="M0 0h100v2H0z"></path>
        </svg>

        <template v-if="games && games.length">
            <tool-bar
                :sortFields="sortFields"
                :showPerPage="true"
                defaultSortField="viewer_count"
                defaultSortOrder="DESC"
                :defaultPerPage="20"
                @filtersChanged="data => Object.assign(filters, data)"
                ></tool-bar>

            <app-table>
                <thead class="bg-gray-50">
                    <tr>
                        <table-header>Twitch ID</table-header>
                        <table-header>Name</table-header>
                        <table-header>No. of Views</table-header>
                    </tr>
                </thead>

                <tbody class="bg-white">
                    <tr v-for="game in games" class="whitespace-nowrap" :key="game.id">
                        <table-data>{{ game.twitch_id || 'N/A' }}</table-data>
                        <table-data>{{ game.name || 'N/A' }}</table-data>
                        <table-data>{{ game.viewer_count }}</table-data>
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
    import gamesService from "../services/games";

    export default {
        name: 'GamesByViewerCount',

        setup() {
            const sortFields = ref([
                {field: 'name', name: 'Name'},
                {field: 'viewer_count', name: 'No. of Views'}
            ]);

            const filters = reactive({
                sort_field: 'viewer_count',
                sort_order: 'DESC',
                per_page: 20
            });

            const page = reactive({
                page: 1
            });

            const games = ref([]);
            const pageDetails = reactive({});

            const getGames = async () => {
                try {
                    const response = await gamesService.getByViewerCount({ ...filters, ...page });

                    games.value = response.data.data;
                    Object.assign(pageDetails, pickBy(response.data, (v, key) => key !== 'data'));
                } catch (err) {
                    console.log(`Fetch Games By Viewer Count Error: ${err}`);
                }
            };

            onMounted(async () => {
                await getGames();
            });

            watch(filters, () => {
                page.page = 1;
                getGames();
            });
            watch(page, getGames);

            return {
                games,
                sortFields,
                filters,
                page,
                getGames,
                pageDetails
            }
        }
    }
</script>
