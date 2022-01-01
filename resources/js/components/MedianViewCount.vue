<template>
    <div class="bg-white shadow rounded-lg p-8 max-w-md md:mx-auto mb-10 mx-5">
        <h2 class="text-2xl text-center font-normal mb-6 text-90">Median View Count</h2>

        <svg class="block mx-auto mb-6" xmlns="http://www.w3.org/2000/svg" width="100" height="2" viewBox="0 0 100 2">
            <path fill="#D8E3EC" d="M0 0h100v2H0z"></path>
        </svg>

        <div class="flex justify-center">
            <p class="text-purple-700 text-2xl">{{ median }}</p>
        </div>
    </div>
</template>

<script>
    import { onMounted, ref } from "vue";
    import streamstatsService from "../services/streamstats";

    export default {
        name: 'MedianViewCount',

        setup() {
            const median = ref(0);

            onMounted(async () => {
                try {
                    const response = await streamstatsService.getMedianViewCount();
                    median.value = response.data;
                } catch (err) {
                    console.log(`Fetch Median Error: ${err}`);
                }
            });

            return {
                median
            }
        }
    }
</script>
