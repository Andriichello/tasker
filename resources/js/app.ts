import './bootstrap.ts';
import {createApp} from 'vue';
import {createPinia} from 'pinia';
import App from './App.vue';
import router from './router';

// Create the Vue app
const app = createApp(App);

// Use Pinia
app.use(createPinia());
app.use(router);

// Mount the app
app.mount('#app');
