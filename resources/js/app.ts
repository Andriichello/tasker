import './bootstrap.ts';
import {createApp} from 'vue';
import {createPinia} from 'pinia';
import App from './App.vue';
import router from './router';
import {useAuthStore} from './stores';

// Create Pinia
const pinia = createPinia();

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
