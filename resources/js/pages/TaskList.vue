<template>
  <div class="container mx-auto py-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Tasks</h1>
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

    <!-- Task lists -->
    <div v-else class="space-y-6">
      <!-- Private Tasks Section (only visible to authenticated users) -->
      <div v-if="isAuthenticated && privateTasks.length > 0" class="bg-white rounded-lg shadow-md overflow-hidden">
        <div
          @click="togglePrivateCollapse"
          class="bg-purple-100 p-4 flex justify-between items-center cursor-pointer hover:bg-purple-200"
        >
          <h2 class="text-xl font-semibold text-purple-800">Private Tasks ({{ privateTasks.length }})</h2>
          <ChevronDown
            class="h-5 w-5 text-purple-800 transition-transform"
            :class="{ 'rotate-180': !privateCollapsed }"
          />
        </div>

        <div v-show="!privateCollapsed" class="divide-y">
          <div
            v-for="task in privateTasks"
            :key="task.id"
            class="p-4 hover:bg-gray-50 cursor-pointer"
            @click="viewTask(task.id)"
          >
            <div class="flex justify-between items-start mb-2">
              <h3 class="text-lg font-semibold">{{ task.title }}</h3>
              <span
                :class="[
                  'text-xs px-2 py-1 rounded-full',
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
            <div class="flex flex-wrap gap-1 mt-2">
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

      <!-- Public Tasks Section -->
      <div v-if="publicTasks.length > 0" class="bg-white rounded-lg shadow-md overflow-hidden">
        <div
          @click="togglePublicCollapse"
          class="bg-green-100 p-4 flex justify-between items-center cursor-pointer hover:bg-green-200"
        >
          <h2 class="text-xl font-semibold text-green-800">Public ({{ publicTasks.length }})</h2>
          <ChevronDown
            class="h-5 w-5 text-green-800 transition-transform"
            :class="{ 'rotate-180': !publicCollapsed }"
          />
        </div>

        <div v-show="!publicCollapsed" class="divide-y">
          <div
            v-for="task in publicTasks"
            :key="task.id"
            class="p-4 hover:bg-gray-50 cursor-pointer"
            @click="viewTask(task.id)"
          >
            <div class="flex justify-between items-start mb-2">
              <h3 class="text-lg font-semibold">{{ task.title }}</h3>
              <span
                :class="[
                  'text-xs px-2 py-1 rounded-full',
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
            <div class="flex flex-wrap gap-1 mt-2">
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
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { ChevronDown } from 'lucide-vue-next';

// State
const tasks = ref([]);
const loading = ref(true);
const error = ref(null);
const isAuthenticated = ref(false);
const privateCollapsed = ref(false);
const publicCollapsed = ref(false);

// Computed properties for filtered tasks
const privateTasks = computed(() => {
  return tasks.value.filter(task => task.visibility === 'private');
});

const publicTasks = computed(() => {
  return tasks.value.filter(task => task.visibility === 'public');
});

// Toggle collapse state
const togglePrivateCollapse = () => {
  privateCollapsed.value = !privateCollapsed.value;
};

const togglePublicCollapse = () => {
  publicCollapsed.value = !publicCollapsed.value;
};

// Check if user is authenticated
const checkAuth = async () => {
  try {
    const response = await axios.get('/api/me');
    isAuthenticated.value = true;
    return response.data.data;
  } catch (err) {
    isAuthenticated.value = false;
    return null;
  }
};

// Fetch tasks from API
const fetchTasks = async () => {
  loading.value = true;
  error.value = null;

  try {
    // First check if user is authenticated
    await checkAuth();

    // Fetch tasks
    const response = await axios.get('/api/tasks');
    tasks.value = response.data.data;
  } catch (err) {
    console.error('Error fetching tasks:', err);

    // If unauthorized, try to fetch public tasks only
    if (err.response && err.response.status === 401) {
      try {
        const publicResponse = await axios.get('/api/tasks?visibility=public');
        tasks.value = publicResponse.data.data;
      } catch (publicErr) {
        error.value = 'Failed to load tasks. Please try again later.';
      }
    } else {
      error.value = 'Failed to load tasks. Please try again later.';
    }
  } finally {
    loading.value = false;
  }
};

// Truncate description to 100 characters
const truncateDescription = (description) => {
  if (!description) return '';
  return description.length > 100
    ? description.substring(0, 100) + '...'
    : description;
};

// Navigate to task detail page
const viewTask = (taskId) => {
  window.location.href = `/${taskId}`;
};

// Navigate to task creation page
const createTask = () => {
  window.location.href = '/create';
};

// Fetch tasks on component mount
onMounted(fetchTasks);
</script>
