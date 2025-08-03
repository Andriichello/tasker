<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8">
    <div class="container mx-auto px-6">
      <!-- Page Header -->
      <div class="flex items-center justify-between mb-8">
        <div>
          <h2 class="text-3xl font-bold text-gray-900 mb-2">My Tasks</h2>
          <p class="text-gray-600">Manage your tasks efficiently</p>
        </div>
        <button
          v-if="isAuthenticated"
          @click="createTask"
          class="bg-blue-600 text-white text-lg px-6 py-3 rounded-xl hover:bg-blue-700 transition-all duration-200 shadow-lg hover:shadow-xl flex items-center gap-2 font-medium"
        >
          <PlusIcon class="h-5 w-5" />
          Create
        </button>
      </div>

      <!-- Task Filters Component -->
      <TaskFilters class="mb-8"
        @search="handleFilterSearch"
        @clearFilters="clearAllFilters"/>

      <!-- Loading state -->
      <div v-if="loading" class="flex justify-center items-center py-10">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-500"></div>
      </div>

      <!-- Error state -->
      <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        {{ error }}
      </div>

      <!-- Empty state -->
      <div v-else-if="tasks.length === 0" class="text-center py-16">
        <div class="w-16 h-16 bg-gray-200/60 rounded-full flex items-center justify-center mx-auto mb-4">
          <ListIcon class="h-8 w-8 text-gray-400" />
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No tasks found</h3>

        <p class="text-gray-500 mb-6">
          {{ isAuthenticated ? 'But you can always create one...' : 'Log in and you will be able to create tasks...' }}
        </p>
      </div>

      <!-- Task lists with tabs -->
      <div v-else class="space-y-6">
        <!-- Task Tabs Component -->
        <TaskTabs
          v-model:activeTab="activeTab"
          :counts="{
            all: tasks.length,
            public: publicTasks.length,
            private: privateTasks.length,
            my: myTasks.length
          }"
          :isAuthenticated="isAuthenticated"
        />

        <!-- Task List -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="flex flex-col gap-4 p-4">
            <TaskItem
              v-for="task in displayedTasks"
              :key="task.id"
              :task="task"
              :currentUserId="authStore.user?.id"
              @click="viewTask"
            />
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
import { useRouter } from 'vue-router';
import TaskFilters from '../components/TaskFilters.vue';
import TaskTabs from '../components/TaskTabs.vue';
import TaskItem from '../components/TaskItem.vue';
import { PlusIcon, ListIcon } from 'lucide-vue-next';

// Get stores and router
const authStore = useAuthStore();
const tasksStore = useTasksStore();
const tagsStore = useTagsStore();
const router = useRouter();

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

/**
 * Handle filter search event from TaskFilters component
 * @param filters Object containing searchTerm, statusValue, and tagValue
 */
const handleFilterSearch = (filters: { searchTerm?: string, statusValue?: string, tagValue?: string }) => {
  const { searchTerm, statusValue, tagValue } = filters;

  // Get previous filter values from the store
  const previousSearchQuery = tasksStore.getSearchQuery;
  const previousStatusFilter = tasksStore.getStatusFilter;
  const previousTagFilter = tasksStore.getTagFilter;

  // Check if each filter has been changed
  const searchChanged = previousSearchQuery !== searchTerm;
  const statusChanged = previousStatusFilter !== statusValue;
  const tagChanged = previousTagFilter !== tagValue;

  // Force reload if any filter has been changed
  const forceReload = searchChanged || statusChanged || tagChanged;

  // Send all filters to the backend, with forceReload if filters were changed
  fetchTasks(searchTerm, statusValue, tagValue, forceReload);
  lastSearchTime.value = Date.now();
};

// Navigate to task detail page
const viewTask = (taskId: number): void => {
  router.push(`/${taskId}`);
};

// Navigate to task creation page
const createTask = (): void => {
  router.push('/create');
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
