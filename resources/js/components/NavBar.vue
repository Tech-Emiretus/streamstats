<template>
    <nav class="flex items-center justify-between flex-wrap bg-purple-900 py-3 px-6 mb-10">
        <div class="flex items-center flex-shrink-0 text-white mr-6">
            <img class="w-12 h-12 mr-2" :src="user.profile_image" />
            <span class="font-semibold text-xl tracking-tight"
                >StreamStats</span
            >
        </div>
        <div class="w-full block flex-grow lg:flex lg:items-center lg:w-auto">
            <div class="text-sm lg:flex-grow">
                <easy-spinner v-show="isFetching" type="dots" size="30" color="#ffffff" />
            </div>
            <div>
                <a
                    href="#"
                    class="
                        inline-block
                        text-sm
                        px-4
                        py-2
                        mr-2
                        leading-none
                        border
                        rounded
                        hover:text-white
                        hover:border-white
                        border-transparent
                        hover:bg-transparent
                        text-purple-500
                        bg-white
                        mt-4
                        lg:mt-0
                    "
                    @click.prevent="refreshStreams(true)"
                    >Refresh Your Streams</a
                >

                <a
                    href="/logout"
                    class="
                        inline-block
                        text-sm
                        px-4
                        py-2
                        leading-none
                        border
                        rounded
                        text-white
                        border-white
                        hover:border-transparent
                        hover:text-purple-500
                        hover:bg-white
                        mt-4
                        lg:mt-0
                    "
                    >Logout | {{ user.name }}</a
                >
            </div>
        </div>
    </nav>
</template>

<script>
    import { isFetching } from '../services';
    import { refreshStreams } from '../composables/refresh_streams';

    export default {
        name: 'NavBar',
        props: ['user'],

        setup() {
            return {
                isFetching,
                refreshStreams
            }
        }
    }
</script>
