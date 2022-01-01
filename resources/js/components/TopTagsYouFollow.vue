<template>
    <div class="bg-white shadow rounded-lg p-8 w-100% ml-5 mb-10">
        <h2 class="text-2xl text-center font-normal mb-6 text-90">Top Tags You Follow</h2>

        <svg class="block mx-auto mb-6" xmlns="http://www.w3.org/2000/svg" width="100" height="2" viewBox="0 0 100 2">
            <path fill="#D8E3EC" d="M0 0h100v2H0z"></path>
        </svg>

        <template v-if="tags && tags.length">
            <tool-bar
                :sortFields="sortFields"
                :showPerPage="true"
                defaultSortField="name"
                defaultSortOrder="ASC"
                :defaultPerPage="20"
                @filtersChanged="data => Object.assign(filters, data)"
                ></tool-bar>

            <app-table>
                <thead class="bg-gray-50">
                    <tr>
                        <table-header>Tag</table-header>
                        <table-header>Description</table-header>
                        <table-header>Auto?</table-header>
                    </tr>
                </thead>

                <tbody class="bg-white">
                    <tr v-for="tag in tags" class="whitespace-nowrap" :key="tag.id">
                        <table-data>{{ tag.name }}</table-data>
                        <table-data>{{ tag.description }}</table-data>
                        <table-data>{{ tag.is_auto == 1 ? 'Yes' : 'No' }}</table-data>
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

    export default {
        name: 'TopTagsYouFollow',

        setup() {
            const sortFields = ref([
                {field: 'name', name: 'Tag'},
            ]);

            const filters = reactive({
                sort_field: 'name',
                sort_order: 'ASC',
                per_page: 20
            });

            const page = reactive({
                page: 1
            });

            const tags = ref([]);
            const pageDetails = reactive({});

            const getSharedTags = async () => {
                try {
                    const response = await streamstatsService.getSharedTags({ ...filters, ...page });

                    tags.value = response.data.data;
                    Object.assign(pageDetails, pickBy(response.data, (v, key) => key !== 'data'));
                } catch (err) {
                    console.log(`Fetch Shared Tags Error: ${err}`);
                }
            };

            onMounted(async () => {
                await getSharedTags();
            });

            watch(filters, () => {
                page.page = 1;
                getSharedTags();
            });
            watch(page, getSharedTags);

            return {
                tags,
                sortFields,
                filters,
                page,
                getSharedTags,
                pageDetails,
            }
        }
    }
</script>
