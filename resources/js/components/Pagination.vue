<template>
	<div class="pagination my-5">
		<div class="flex items-center justify-center space-x-1">
            <a
                class="flex items-center px-4 py-2 text-gray-500 bg-gray-300  rounded-md hover:bg-blue-400 hover:text-white"
                :class="{ 'cursor-not-allowed': current_page == 1, 'cursor-pointer': current_page != 1 }"
                @click.prevent="navigate(prevPage, $event)"
                :disabled="current_page == 1">
                    <span aria-hidden="true">Previous</span>
            </a>

            <template v-if="last_page <= 10">
                <a
                    v-for="i  in last_page"
                    class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-blue-400 hover:text-white cursor-pointer"
                    :class="{'bg-blue-400': i == current_page}"
                    @click.prevent="navigate(i, $event)">
                        <span>{{ i }}</span>
                </a>
            </template>

            <template v-else>
                <a class="no-pages px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-blue-400 hover:text-white cursor-pointer">
                    <span>{{current_page}} of {{last_page}}</span>
                </a>
            </template>

            <a
                class="px-4 py-2 font-bold text-gray-500 bg-gray-300 rounded-md hover:bg-blue-400 hover:text-white"
                :class="{ 'cursor-not-allowed': current_page == last_page, 'cursor-pointer': current_page != last_page }"
                @click="navigate(nextPage, $event)"
                :disabled="current_page == last_page">
                    <span aria-hidden="true">Next</span>
            </a>
		</div>
	</div>
</template>

<script>
    import { computed, toRefs } from "vue";

	export default {
        name: 'Pagination',
		props: ['pageDetails'],

        setup(props, { emit }) {
            const { pageDetails } = toRefs(props);
            const { current_page, last_page, per_page } = toRefs(pageDetails.value);

            const prevPage = computed(() => current_page.value != 1 ? current_page.value - 1 : 1);
            const nextPage = computed(() => current_page.value != last_page.value ? current_page.value + 1 : last_page.value);
            const navigate = (page) => emit('navigate', { page });

            return {
                current_page,
                last_page,
                per_page,
                prevPage,
                nextPage,
                navigate
            }
        }
	}
</script>
