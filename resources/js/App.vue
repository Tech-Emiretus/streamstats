<template>
    <nav-bar :user="user"></nav-bar>
    <div>
        <h1>Welcome to the vue app.</h1>
    </div>
</template>

<script>
    import { onMounted, ref } from 'vue';
    import NavBar from './components/NavBar.vue';
    import { user, getUserDetails } from './composables/user_details';

    export default {
        name: 'App',

        components: {
            NavBar
        },

        setup() {
            onMounted(async () => await getUserDetails());

            return {
                user
            }
        }
    }
</script>
