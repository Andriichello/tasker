<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8">
    <div class="container mx-auto">
      <!-- Page Header -->
      <div class="flex items-center justify-between mb-8 px-4">
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
      <TaskFilters class="mb-8 px-4"
        :search-query="searchQuery"
        :status-filter="statusFilter"
        :tag-filter="tagFilter"
        @update:searchQuery="updateSearchQuery"
        @update:statusFilter="updateStatusFilter"
        @update:tagFilter="updateTagFilter"
        @statusFilterChange="updateStatusFilter"
        @tagFilterChange="updateTagFilter"
        @search="handleFilterSearch"
        @clearFilters="clearAllFilters"/>

      <!-- Loading state -->
      <div v-if="loading" class="flex justify-center items-center py-10 px-4">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-500"></div>
      </div>

      <!-- Error state -->
      <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        {{ error }}
      </div>

      <!-- Empty state -->
      <div v-else-if="tasks.length === 0" class="text-center py-16 px-4">
        <div class="w-16 h-16 bg-gray-200/60 rounded-full flex items-center justify-center mx-auto mb-4">
          <ListIcon class="h-8 w-8 text-gray-400" />
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No tasks found</h3>

        <p class="text-gray-500 mb-6">
          {{ isAuthenticated ? 'But you can always create one...' : 'Log in and you will be able to create tasks...' }}
        </p>
      </div>

      <!-- Task lists with tabs -->
      <div v-else class="space-y-2">
        <!-- Task Tabs Component -->
        <TaskTabs class="mb-4 mx-4"
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
        <div class="flex flex-col gap-3 overflow-hidden px-4 pb-20">
          <TaskItem v-for="task in displayedTasks" :key="task.id"
                    class="bg-white shadow-xs rounded-lg overflow-hidden hover:shadow-xl transition-shadow duration-200"
                    :task="task"
                    :currentUserId="authStore.user?.id"
                    @click="viewTask"/>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useAuthStore, useTasksStore, useTagsStore } from '@/stores';
import type { Task } from '@/api';
import { useRouter } from 'vue-router';
import TaskFilters from '../components/TaskFilters.vue';
import TaskTabs from '../components/TaskTabs.vue';
import TaskItem from '../components/TaskItem.vue';
import { PlusIcon, ListIcon } from 'lucide-vue-next';
import {debounce} from "lodash";

// Get stores and router
const authStore = useAuthStore();
const tasksStore = useTasksStore();
const tagsStore = useTagsStore();
const router = useRouter();

// State
const activeTab = ref<'all' | 'public' | 'private' | 'my'>('all');
const lastSearchTime = ref(0);

// Last used filter values for caching
const lastSearchQuery = ref<string | undefined>(undefined);
const lastStatusFilter = ref<string | undefined>(undefined);
const lastTagFilter = ref<string | undefined>(undefined);

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
    const statusValue = typeof statusFilter.value === 'object'
      ? statusFilter.value.value
      : statusFilter.value;

    if (statusValue) {
      filteredTasks = filteredTasks.filter((task: Task) => task.status === statusValue);
    }
  }

  // Finally, filter by tag if a tag filter is selected
  if (tagFilter.value) {
    // Handle both string and object formats for tagFilter
    const tagValue = typeof tagFilter.value === 'object'
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

  // Check if filter values have changed or if forceReload is true
  if (forceReload ||
      search !== lastSearchQuery.value ||
      status !== lastStatusFilter.value ||
      tag !== lastTagFilter.value) {

    // Update last used filter values
    lastSearchQuery.value = search;
    lastStatusFilter.value = status;
    lastTagFilter.value = tag;

    // Fetch tasks using the store with server-side filtering
    await tasksStore.fetchTasks(search, status, tag, forceReload);
  }
};

/**
 * Handle filter search event from TaskFilters component
 * @param filters Object containing searchTerm, statusValue, and tagValue
 */
const handleFilterSearch = (filters: { searchTerm?: string, statusValue?: string, tagValue?: string }, forceReload: boolean = false) => {
  const { searchTerm, statusValue, tagValue } = filters;

  // The filter values have already been updated in the store via the update events
  // We just need to fetch tasks with the current filter values
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

const debouncedHandleFilterSearch = (timeout: number = 500) => {
  debounce(() => {
    handleFilterSearch({
      searchTerm: tasksStore.searchQuery,
      statusValue: tasksStore.statusFilter,
      tagValue: tasksStore.tagFilter,
    });
  }, timeout)();
}

/**
 * Update search query filter
 * @param value New search query value
 */
const updateSearchQuery = async (value: string) => {
  console.log('updateSearchQuery', value);
  tasksStore.updateFilters(value, undefined, undefined);

  debouncedHandleFilterSearch(500);
};

/**
 * Update status filter
 * @param value New status filter value
 */
const updateStatusFilter = (value: string | null) => {
  console.log('updateStatusFilter', value);
  tasksStore.updateFilters(undefined, value, undefined);

  debouncedHandleFilterSearch(50);
};

/**
 * Update tag filter
 * @param value New tag filter value
 */
const updateTagFilter = (value: string | null) => {
  console.log('updateTagFilter', value);
  tasksStore.updateFilters(undefined, undefined, value);

  debouncedHandleFilterSearch(50);
};

const clearAllFilters = () => {
  // Reset all filter values in the store
  tasksStore.clearFilters();

  debouncedHandleFilterSearch(10);
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
      (typeof storedStatusFilter === 'object' ?
        storedStatusFilter.value :
        storedStatusFilter) :
      undefined;

    const tagValue = storedTagFilter ?
      (typeof storedTagFilter === 'object' ?
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
