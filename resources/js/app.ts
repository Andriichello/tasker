import './bootstrap.ts';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import type { PiniaPluginContext } from 'pinia';
import App from './App.vue';
import router from './router';
import {useAuthStore} from './stores';

// Create Pinia
const pinia = createPinia();

// Add a plugin to persist state changes to localStorage
pinia.use(({ store }: PiniaPluginContext) => {
  if (store.$id === 'auth') {
    // process access token here...
  }
});

router.beforeEach((to, from, next) => {
  const isOpen = to.matched.some(record => !record.meta.requiresAuth);
  const isLogin = to.matched.some(record => record.name === 'login');

  const authStore = useAuthStore();
  const hasToken = !!authStore.token;

  if (hasToken && isLogin) {
    return next('/');
  }

  if (!isOpen && !hasToken) {
    return next('/login');
  }

  return next();
});

// Create the Vue app
const app = createApp(App);

// Use Pinia
app.use(pinia);
app.use(router);

// Mount the app
app.mount('#app');
