<template>
  <div class="container mx-auto py-6">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold">Tasks</h1>
        <p v-if="isAuthenticated" class="text-sm text-gray-600 mt-1">
          <span class="inline-block w-3 h-3 bg-blue-500 mr-1"></span>
          Tasks with blue border are yours
        </p>
      </div>
      <button
        v-if="isAuthenticated"
        @click="createTask"
        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
      >
        Create Task
      </button>
    </div>

    <!-- Loading state -->
    <div v-if="loading" class="flex justify-center items-center py-10">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-500"></div>
    </div>

    <!-- Error state -->
    <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
      {{ error }}
    </div>

    <!-- Empty state -->
    <div v-else-if="tasks.length === 0" class="text-center py-10 bg-gray-50 rounded">
      <p class="text-gray-500">No tasks found</p>
    </div>

    <!-- Task lists with tabs -->
    <div v-else class="space-y-6">
      <!-- Tabs Navigation -->
      <div class="border-b border-gray-200">
        <nav class="flex -mb-px">
          <button
            @click="activeTab = 'all'"
            :class="[
              'py-2 px-4 text-center border-b-2 font-medium text-sm',
              activeTab === 'all'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            All ({{ tasks.length }})
          </button>
          <button
            @click="activeTab = 'public'"
            :class="[
              'py-2 px-4 text-center border-b-2 font-medium text-sm',
              activeTab === 'public'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            Public ({{ publicTasks.length }})
          </button>
          <button
            v-if="isAuthenticated"
            @click="activeTab = 'private'"
            :class="[
              'py-2 px-4 text-center border-b-2 font-medium text-sm',
              activeTab === 'private'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            Private ({{ privateTasks.length }})
          </button>
          <button
            v-if="isAuthenticated"
            @click="activeTab = 'my'"
            :class="[
              'py-2 px-4 text-center border-b-2 font-medium text-sm',
              activeTab === 'my'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            My Tasks ({{ myTasks.length }})
          </button>
        </nav>
      </div>

      <!-- Task List -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="divide-y">
          <div
            v-for="task in displayedTasks"
            :key="task.id"
            :class="[
              'p-4 hover:bg-gray-50 cursor-pointer',
              authStore.user && task.user_id === authStore.user.id ? 'border-l-4 border-blue-500' : ''
            ]"
            @click="viewTask(task.id)"
          >
            <div class="flex justify-between items-start mb-2">
              <h3 class="text-lg font-semibold">{{ task.title }}</h3>
              <span
                :class="[
                  'whitespace-nowrap text-xs px-2 py-1 rounded-full',
                  task.status === 'to-do' ? 'bg-yellow-100 text-yellow-800' :
                  task.status === 'in-progress' ? 'bg-blue-100 text-blue-800' :
                  task.status === 'done' ? 'bg-green-100 text-green-800' :
                  'bg-red-100 text-red-800'
                ]"
              >
                {{ task.status }}
              </span>
            </div>
            <p class="text-gray-600 mb-2">{{ truncateDescription(task.description) }}</p>
            <div class="flex justify-between items-end mt-2">
              <!-- User name in the down left corner -->
              <div class="text-sm font-semibold" v-if="task.user">
                {{ task.user.name }}
              </div>
              <!-- Tags in the right down corner -->
              <div class="flex flex-wrap gap-1 justify-end">
                <span
                  v-for="tag in task.tags"
                  :key="tag"
                  class="bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded"
                >
                  {{ tag }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useAuthStore, useTasksStore } from '../stores';
import type { Task } from '../api/models/task';

// Get stores
const authStore = useAuthStore();
const tasksStore = useTasksStore();

// State
const activeTab = ref<'all' | 'public' | 'private' | 'my'>('all');

// Computed properties
const isAuthenticated = computed(() => authStore.isAuthenticated);
const tasks = computed(() => tasksStore.getTasks);
const loading = computed(() => tasksStore.isLoading);
const error = computed(() => tasksStore.getError);

// Computed properties for filtered tasks
const privateTasks = computed(() => {
  return tasks.value.filter((task: Task) => task.visibility === 'private');
});

const publicTasks = computed(() => {
  return tasks.value.filter((task: Task) => task.visibility === 'public');
});

const myTasks = computed(() => {
  if (!authStore.user) return [];
  return tasks.value.filter((task: Task) => task.user_id === authStore.user?.id);
});

// Computed property for displayed tasks based on active tab
const displayedTasks = computed(() => {
  switch (activeTab.value) {
    case 'all':
      return tasks.value;
    case 'public':
      return publicTasks.value;
    case 'private':
      return privateTasks.value;
    case 'my':
      return myTasks.value;
    default:
      return tasks.value;
  }
});

// Fetch tasks from store
const fetchTasks = async () => {
  // First check if user is authenticated
  await authStore.getMe();

  // Fetch tasks using the store
  await tasksStore.fetchTasks();
};

// Truncate description to 100 characters
const truncateDescription = (description: string | null): string => {
  if (!description) return '';
  return description.length > 100
    ? description.substring(0, 100) + '...'
    : description;
};

// Navigate to task detail page
const viewTask = (taskId: number): void => {
  window.location.href = `/${taskId}`;
};

// Navigate to task creation page
const createTask = (): void => {
  window.location.href = '/create';
};

// Fetch tasks on component mount
onMounted(() => {
  fetchTasks();

  // Set default tab based on authentication status
  if (!isAuthenticated.value) {
    activeTab.value = 'all';
  }
});
</script>
