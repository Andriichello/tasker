import './bootstrap';
import {createApp} from 'vue';
import {createPinia} from 'pinia';
import TaskList from "./pages/TaskList.vue";
import TaskDetail from "./pages/TaskDetail.vue";
import TaskForm from "./pages/TaskForm.vue";
import Login from "./pages/Login.vue";
import BaseLayout from "./layouts/BaseLayout.vue";
import { authMiddleware } from './middleware';

// Create Pinia
const pinia = createPinia();

// Add a plugin to persist state changes to localStorage
pinia.use(({ store }) => {
  // Restore state from localStorage when store is initialized
  const persistedState = localStorage.getItem(`pinia-${store.$id}`);
  if (persistedState) {
    try {
      store.$patch(JSON.parse(persistedState));
    } catch (error) {
      console.error(`Error restoring state for ${store.$id}:`, error);
    }
  }
});

// Create the Vue app
const app = createApp({
  data() {
    return {
      currentPath: window.location.pathname,
      canAccess: true
    };
  },
  computed: {
    // Determine which component to render based on the current path
    currentView() {
      const path = this.currentPath;

      // Task detail page - matches /{id} where id is a number
      if (/^\/\d+$/.test(path)) {
        return 'task-detail';
      }

      // Task create page
      if (path === '/create') {
        return 'task-form';
      }

      // Task edit page - matches /{id}/edit where id is a number
      if (/^\/\d+\/edit$/.test(path)) {
        return 'task-form';
      }

      // Login page
      if (path === '/login') {
        return 'login';
      }

      // Default to home page
      return 'task-list';
    },
    // Determine if the current route requires authentication
    requiresAuth() {
      const path = this.currentPath;

      // Routes that require authentication
      return path === '/create' || /^\/\d+\/edit$/.test(path);
    }
  },
  methods: {
    // Apply middleware before rendering
    async applyMiddleware() {
      this.canAccess = await authMiddleware(this.requiresAuth);
    }
  },
  mounted() {
    // Apply middleware when component is mounted
    this.applyMiddleware();
  },
  template: `<component :is="currentView"></component>`
});

// Use Pinia
app.use(pinia);

// Register components
app.component('BaseLayout', BaseLayout);
app.component('task-list', TaskList);
app.component('task-detail', TaskDetail);
app.component('task-form', TaskForm);
app.component('login', Login);

// Mount the app
app.mount('#app');
