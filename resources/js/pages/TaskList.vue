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
      <div>
        <button
          v-if="isAuthenticated"
          @click="createTask"
          class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
        >
          Create Task
        </button>
      </div>
    </div>

    <!-- Filters Row -->
    <div class="flex flex-wrap justify-start gap-4 mb-6">
      <!-- Status Filter -->
      <div class="w-64">
        <label for="statusFilter" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
        <Multiselect
          id="statusFilter"
          v-model="statusFilter"
          :options="statusOptions"
          :searchable="true"
          :close-on-select="true"
          :create-option="false"
          mode="single"
          placeholder="All Statuses"
          class="border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
          @update:model-value="handleSearch"
        />
      </div>

      <!-- Tag Filter -->
      <div class="w-64">
        <label for="tagFilter" class="block text-sm font-medium text-gray-700 mb-1">Tag</label>
        <Multiselect
          id="tagFilter"
          v-model="tagFilter"
          :options="availableTags"
          :searchable="true"
          :close-on-select="true"
          :create-option="false"
          mode="single"
          placeholder="All Tags"
          class="border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
          @update:model-value="handleSearch"
        />
      </div>
    </div>

    <!-- Centered Search Bar -->
    <div class="flex justify-start mb-4">
      <div class="flex items-center w-full max-w-md">
        <input
          type="text"
          v-model="searchQuery"
          @keyup.enter="handleSearch"
          placeholder="Search tasks..."
          class="border border-gray-300 rounded-l px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
        <button
          @click="handleSearch"
          class="bg-blue-500 text-white p-[11px] rounded-r hover:bg-blue-600 flex items-center justify-center"
          style="aspect-ratio: 1/1;"
        >
          <Search size="20" />
        </button>
      </div>
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
                  class="bg-gray-700 text-white text-xs px-2 py-1 rounded"
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
import { useAuthStore, useTasksStore, useTagsStore } from '../stores';
import type { Task } from '../api/models/task';
import { Search } from 'lucide-vue-next';
import Multiselect from '@vueform/multiselect';
import '@vueform/multiselect/themes/default.css';

// Get stores
const authStore = useAuthStore();
const tasksStore = useTasksStore();
const tagsStore = useTagsStore();

// State
const activeTab = ref<'all' | 'public' | 'private' | 'my'>('all');
const searchQuery = ref('');
const statusFilter = ref(null);
const tagFilter = ref(null);
const lastSearchTime = ref(0);
const debounceTime = 500;

// Status options for the status filter
const statusOptions = [
  { value: 'to-do', label: 'To Do' },
  { value: 'in-progress', label: 'In Progress' },
  { value: 'canceled', label: 'Canceled' },
  { value: 'done', label: 'Done' }
];

// Get tags from the tags store for the tag filter dropdown
const availableTags = computed(() => {
  // Format tags for Multiselect component
  return [
    { value: '', label: 'All Tags' },
    ...tagsStore.getTags.map(tag => ({ value: tag.name, label: tag.name }))
  ];
});

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

// Computed property for displayed tasks based on active tab, status filter, and tag filter
const displayedTasks = computed(() => {
  // First, filter by active tab
  let filteredTasks;
  switch (activeTab.value) {
    case 'all':
      filteredTasks = tasks.value;
      break;
    case 'public':
      filteredTasks = publicTasks.value;
      break;
    case 'private':
      filteredTasks = privateTasks.value;
      break;
    case 'my':
      filteredTasks = myTasks.value;
      break;
    default:
      filteredTasks = tasks.value;
  }

  // Then, filter by status if a status filter is selected
  if (statusFilter.value) {
    // Handle both string and object formats for statusFilter
    const statusValue = typeof statusFilter.value === 'object' && statusFilter.value !== null
      ? statusFilter.value.value
      : statusFilter.value;

    if (statusValue) {
      filteredTasks = filteredTasks.filter((task: Task) => task.status === statusValue);
    }
  }

  // Finally, filter by tag if a tag filter is selected
  if (tagFilter.value) {
    // Handle both string and object formats for tagFilter
    const tagValue = typeof tagFilter.value === 'object' && tagFilter.value !== null
      ? tagFilter.value.value || tagFilter.value
      : tagFilter.value;

    filteredTasks = filteredTasks.filter((task: Task) => {
      if (!task.tags || !Array.isArray(task.tags)) return false;
      // Check if task has the selected tag
      return task.tags.includes(tagValue);
    });
  }

  return filteredTasks;
});

// Fetch tasks from store
const fetchTasks = async (search?: string, status?: string, tag?: string) => {
  // First check if user is authenticated
  await authStore.getMe();

  // Fetch tasks using the store with server-side filtering
  await tasksStore.fetchTasks(search, status, tag);
};

// Handle search with debounce
const handleSearch = () => {
  const currentTime = Date.now();
  const timeSinceLastSearch = currentTime - lastSearchTime.value;

  // Only perform search if debounce time has passed
  if (timeSinceLastSearch >= debounceTime) {
    // Don't send empty search queries
    const searchTerm = searchQuery.value.trim() || undefined;

    // Get status filter value
    const statusValue = statusFilter.value ?
      (typeof statusFilter.value === 'object' && statusFilter.value !== null ?
        statusFilter.value.value :
        statusFilter.value) :
      undefined;

    // Get tag filter value
    const tagValue = tagFilter.value ?
      (typeof tagFilter.value === 'object' && tagFilter.value !== null ?
        tagFilter.value.value :
        tagFilter.value) :
      undefined;

    // Send all filters to the backend
    fetchTasks(searchTerm, statusValue, tagValue);
    lastSearchTime.value = currentTime;
  } else {
    // If debounce time hasn't passed, show a message or visual feedback
    console.log(`Please wait ${Math.ceil((debounceTime - timeSinceLastSearch) / 1000)} seconds before searching again`);
  }
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

// Fetch tasks and tags on component mount
onMounted(async () => {
  // Initial fetch without filters
  fetchTasks();

  // Fetch all available tags
  await tagsStore.fetchTags();

  // Set default tab based on authentication status
  if (!isAuthenticated.value) {
    activeTab.value = 'all';
  }
});
</script>
