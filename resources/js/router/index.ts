import {createRouter, createWebHistory} from 'vue-router';
import type {RouteRecordRaw} from 'vue-router';
import Login from '../pages/Login.vue';
import TaskDetail from '../pages/TaskDetail.vue';
import TaskList from '../pages/TaskList.vue';
import TaskForm from '../pages/TaskForm.vue';
import {beforeGuard} from "@/middleware.ts";

const routes: Array<RouteRecordRaw> = [
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: {
      requiresAuth: false,
    },
  },
  {
    path: '/',
    name: 'TaskList',
    component: TaskList,
    meta: {
      requiresAuth: false,
    },
  },
  {
    path: '/:taskId(\\d+)',
    name: 'TaskDetail',
    component: TaskDetail,
    meta: {
      requiresAuth: false,
    },
  },
  {
    path: '/:taskId(\\d+)/edit',
    name: 'TaskEdit',
    component: TaskForm,
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/create',
    name: 'TaskCreate',
    component: TaskForm,
    meta: {
      requiresAuth: true,
    },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

router.beforeEach(beforeGuard);

export default router;
