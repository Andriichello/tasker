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
    <div class="flex justify-between items-center mb-4">
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

      <!-- Clear Filters Button -->
      <button
        v-if="hasActiveFilters"
        @click="clearAllFilters"
        class="bg-gray-200 text-gray-700 px-3 py-2 rounded hover:bg-gray-300 flex items-center"
      >
        <span class="mr-1">Clear</span>
        <X size="16" />
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
import { Search, X } from 'lucide-vue-next';
import Multiselect from '@vueform/multiselect';
import '@vueform/multiselect/themes/default.css';

// Get stores
const authStore = useAuthStore();
const tasksStore = useTasksStore();
const tagsStore = useTagsStore();

// State
const activeTab = ref<'all' | 'public' | 'private' | 'my'>('all');
const lastSearchTime = ref(0);
const debounceTime = 500;

// Use computed properties for filter values to sync with the store
const searchQuery = computed({
  get: () => tasksStore.getSearchQuery,
  set: (value) => tasksStore.updateFilters(value, undefined, undefined)
});

const statusFilter = computed({
  get: () => tasksStore.getStatusFilter,
  set: (value) => tasksStore.updateFilters(undefined, value, undefined)
});

const tagFilter = computed({
  get: () => tasksStore.getTagFilter,
  set: (value) => tasksStore.updateFilters(undefined, undefined, value)
});

// Status options for the status filter
const statusOptions = [
  { value: '', label: 'All Statuses' },
  { value: 'to-do', label: 'To Do' },
  { value: 'in-progress', label: 'In Progress' },
  { value: 'canceled', label: 'Canceled' },
  { value: 'done', label: 'Done' }
];

// Get tags from the tags store for the tag filter dropdown
const availableTags = computed(() => {
  // Format tags for Multiselect component
  return [
    ...tagsStore.getTags.map(tag => ({ value: tag.name, label: tag.name }))
  ];
});

// Computed properties
const isAuthenticated = computed(() => authStore.isAuthenticated);
const tasks = computed(() => tasksStore.getTasks);
const loading = computed(() => tasksStore.isLoading);
const error = computed(() => tasksStore.getError);

// Check if any filters are active
const hasActiveFilters = computed(() => {
  return (
    (searchQuery.value && searchQuery.value.trim() !== '') ||
    statusFilter.value !== null ||
    tagFilter.value !== null
  );
});

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
const fetchTasks = async (search?: string, status?: string, tag?: string, forceReload: boolean = false) => {
  // First check if user is authenticated, but don't wait for it if we already have user data
  if (!authStore.user) {
    await authStore.getMe();
  }

  // Fetch tasks using the store with server-side filtering
  await tasksStore.fetchTasks(search, status, tag, forceReload);
};

/**
 * Handle search with debounce and detect when filters are cleared
 * This function is called when:
 * - The search button is clicked
 * - Enter is pressed in the search input
 * - A status filter is changed
 * - A tag filter is changed
 */
const handleSearch = () => {
  const currentTime = Date.now();
  const timeSinceLastSearch = currentTime - lastSearchTime.value;

  // Only perform search if debounce time has passed
  if (timeSinceLastSearch >= debounceTime) {
    // Process search query - trim and convert empty string to undefined
    const searchTerm = searchQuery.value.trim() || undefined;

    // Extract the actual value from status filter (handling both string and object formats)
    const statusValue = statusFilter.value ?
      (typeof statusFilter.value === 'object' && statusFilter.value !== null ?
        statusFilter.value.value :
        statusFilter.value) :
      undefined;

    // Extract the actual value from tag filter (handling both string and object formats)
    const tagValue = tagFilter.value ?
      (typeof tagFilter.value === 'object' && tagFilter.value !== null ?
        tagFilter.value.value :
        tagFilter.value) :
      undefined;

    // IMPORTANT: Detect if any filters have been cleared to force a reload
    // This ensures tasks are loaded when filters are cleared, as required by the issue

    // Get previous filter values from the store
    const previousSearchQuery = tasksStore.getSearchQuery;
    const previousStatusFilter = tasksStore.getStatusFilter;
    const previousTagFilter = tasksStore.getTagFilter;

    // Check if each filter has been cleared
    const searchChanged = previousSearchQuery !== searchTerm;
    const statusChanged = previousStatusFilter !== statusValue;
    const tagChanged = previousTagFilter !== tagValue;

    // Force reload if any filter has been changed
    const forceReload = searchChanged || statusChanged || tagChanged;

    // Update the store with the current filter values
    tasksStore.updateFilters(
      searchTerm || '',
      statusFilter.value,
      tagFilter.value
    );

    // Send all filters to the backend, with forceReload if filters were cleared
    fetchTasks(searchTerm, statusValue, tagValue, forceReload);
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

/**
 * Clear all filters and fetch tasks without any filters
 * This function is called when the user clicks the "Clear Filters" button
 * It:
 * 1. Resets all filter values in the store to their defaults
 * 2. Forces a reload of tasks from the API (even though no filters are active)
 * This ensures fresh data is loaded when filters are cleared, as required by the issue
 */
const clearAllFilters = () => {
  // Reset all filter values in the store
  tasksStore.clearFilters();

  // Force reload tasks from API when filters are cleared
  // The true parameter ensures tasks are reloaded even when no filters are active
  fetchTasks(undefined, undefined, undefined, true);
};

/**
 * Initialize component on mount
 * This function:
 * 1. Fetches all available tags
 * 2. Retrieves any stored filter values from the store
 * 3. Fetches tasks with either stored filters or no filters
 * 4. Always forces a reload from the API to ensure fresh data
 * 5. Sets the default tab based on authentication status
 */
onMounted(async () => {
  // Step 1: Fetch all available tags - this will use cached tags if available
  await tagsStore.fetchTags();

  // Step 2: Get stored filter values from the store
  const storedSearchQuery = tasksStore.getSearchQuery;
  const storedStatusFilter = tasksStore.getStatusFilter;
  const storedTagFilter = tasksStore.getTagFilter;

  // Step 3: Fetch tasks with either stored filters or no filters
  if (storedSearchQuery || storedStatusFilter || storedTagFilter) {
    // Extract actual values for API call
    const searchTerm = storedSearchQuery.trim() || undefined;

    const statusValue = storedStatusFilter ?
      (typeof storedStatusFilter === 'object' && storedStatusFilter !== null ?
        storedStatusFilter.value :
        storedStatusFilter) :
      undefined;

    const tagValue = storedTagFilter ?
      (typeof storedTagFilter === 'object' && storedTagFilter !== null ?
        storedTagFilter.value || storedTagFilter :
        storedTagFilter) :
      undefined;

    // Step 4: Always force reload on initial mount to ensure we have fresh data
    // This ensures tasks are loaded even when filters are applied
    fetchTasks(searchTerm, statusValue, tagValue, true);
  } else {
    // No stored filters - fetch all tasks
    // Step 4: Always force reload on initial mount to ensure we have fresh data
    fetchTasks(undefined, undefined, undefined, true);
  }

  // Step 5: Set default tab based on authentication status
  if (!isAuthenticated.value) {
    activeTab.value = 'all';
  }
});
</script>
