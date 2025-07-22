import './bootstrap';
import { createApp } from 'vue';
import TaskList from "./pages/TaskList.vue";
import TaskDetail from "./pages/TaskDetail.vue";
import TaskForm from "./pages/TaskForm.vue";
import Layout from "./layouts/Layout.vue";

// Create the Vue app
const app = createApp({
    data() {
        return {
            currentPath: window.location.pathname
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

            // Default to home page
            return 'task-list';
        }
    },
    template: `
        <Layout>
            <component :is="currentView"></component>
        </Layout>
    `
});

// Register components
app.component('Layout', Layout);
app.component('task-list', TaskList);
app.component('task-detail', TaskDetail);
app.component('task-form', TaskForm);

// Mount the app
app.mount('#app');
