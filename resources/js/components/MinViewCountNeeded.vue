<template>
    <div class="bg-white shadow rounded-lg p-8 max-w-md mx-auto mb-10">
        <h2 class="text-2xl text-center font-normal mb-6 text-90">Views Needed To Make Top Stream</h2>

        <svg class="block mx-auto mb-6" xmlns="http://www.w3.org/2000/svg" width="100" height="2" viewBox="0 0 100 2">
            <path fill="#D8E3EC" d="M0 0h100v2H0z"></path>
        </svg>

        <div class="flex justify-center">
            <p class="text-purple-700 text-2xl">
                <span v-if="minViewCount === null">No streams available. Refresh streams.</span>
                <span v-else-if="minViewCount === 0">All user streams are top streams.</span>
                <span v-else>{{ minViewCount }}</span>
            </p>
        </div>
    </div>
</template>

<script>
    import { onMounted, ref } from "vue";
    import streamstatsService from "../services/streamstats";

    export default {
        name: 'MinViewCountNeeded',

        setup() {
            const minViewCount = ref(null);

            onMounted(async () => {
                try {
                    const response = await streamstatsService.getMinViewerCountNeeded();
                    minViewCount.value = response.data;
                } catch (err) {
                    console.log(`Fetch Min View Count Error: ${err}`);
                }
            });

            return {
                minViewCount
            }
        }
    }
</script>
