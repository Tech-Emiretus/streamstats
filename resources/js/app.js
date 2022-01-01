require('./bootstrap');

import { createApp } from 'vue';
import App from './App.vue';
import easySpinner from 'vue-easy-spinner';
import Table from './components/Table.vue';
import TableHeader from './components/TableHeader.vue';
import TableData from './components/TableData.vue';
import ToolBar from './components/ToolBar.vue';
import Pagination from './components/Pagination.vue';

createApp(App)
    .use(easySpinner, { prefix: 'easy' })
    .component('AppTable', Table)
    .component('TableHeader', TableHeader)
    .component('TableData', TableData)
    .component('ToolBar', ToolBar)
    .component('Pagination', Pagination)
    .mount('#app');
