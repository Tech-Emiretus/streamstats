<template>
    <div class="max-w-2xl mx-auto mt-2 mb-5 flex flex-grow items-center">
        <template v-if="sortFields.length">
            <div class="text-sm flex-grow">
                <label class="mr-2">Sort Field:</label>
                <select class="px-2 py-1 rounded-md" v-model="filters.sort_field">
                    <option v-for="field in sortFields" :value="field.field">
                        {{ field.name }}
                    </option>
                </select>
            </div>

            <div class="text-sm flex-grow">
                <label class="mr-2">Sort Order:</label>
                <select class="w-20 px-2 py-1 rounded-md" v-model="filters.sort_order">
                    <option>ASC</option>
                    <option>DESC</option>
                </select>
            </div>
        </template>

        <div v-if="showPerPage">
            <label class="mr-2">Per Page:</label>
            <select class="w-20 px-2 py-1 rounded-md" v-model="filters.per_page">
                <option>10</option>
                <option>20</option>
                <option>50</option>
                <option>100</option>
            </select>
        </div>
    </div>
</template>

<script>
    import { reactive, watch, toRefs } from "vue";

    export default {
        name: 'ToolBar',
        props: [
            'sortFields',
            'showPerPage',
            'defaultSortField',
            'defaultSortOrder',
            'defaultPerPage',
        ],

        setup(props, { emit }) {
            const { defaultSortOrder, defaultSortField, defaultPerPage } = toRefs(props);

            const filters = reactive({
                sort_field: defaultSortField.value || null,
                sort_order: defaultSortOrder.value || null,
                per_page: defaultPerPage.value || 20,
            });

            watch(filters, () => emit('filtersChanged', filters));

            return {
                filters
            }
        }
    }
</script>
