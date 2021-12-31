require('./bootstrap');

import { createApp } from 'vue';
import App from './App.vue';
import easySpinner from 'vue-easy-spinner';

createApp(App)
    .use(easySpinner, { prefix: 'easy' })
    .mount('#app');
