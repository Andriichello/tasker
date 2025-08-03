<template>
  <div class="">
    <div class="min-h-[30px] flex flex-wrap items-center justify-between mb-4">
      <h2 class="text-xl font-bold text-gray-900">Filters</h2>
      <!-- Clear Filters Button -->
      <button
        v-if="hasActiveFilters"
        @click="clearAllFilters"
        class="px-5 py-0.5 border border-red-500 text-red-500 rounded-lg hover:bg-red-100 transition-colors flex items-center justify-center gap-2"
      >
        Clear
      </button>
    </div>

    <div class="flex flex-wrap gap-4">
      <!-- Search Input -->
      <div class="flex-1">
        <div class="relative">
          <SearchIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
          <input
            type="text"
            v-model="searchQuery"
            @keyup.enter="handleSearch"
            @blur="handleSearch"
            placeholder="Search tasks..."
            class="w-full min-w-xs max-w-3xs pl-10 pr-4 py-3 border border-gray-300 rounded-lg transition-colors"
          />
        </div>
      </div>

      <div class="flex flex-row flex-wrap gap-3">
        <!-- Status Filter -->
        <select
          v-model="statusFilter"
          class="min-w-3xs px-4 py-3 border border-gray-300 rounded-lg transition-colors"
          @change="handleSearch"
        >
          <option :value="null">All Statuses</option>
          <option v-for="option in statusOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
        </select>

        <!-- Tag Filter -->
        <select
          v-model="tagFilter"
          class="min-w-3xs px-4 py-3 border border-gray-300 rounded-lg transition-colors"
          @change="handleSearch"
        >
          <option :value="null">All Tags</option>
          <option v-for="tag in availableTags" :key="tag.value" :value="tag.value">{{ tag.label }}</option>
        </select>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useTasksStore, useTagsStore } from '../stores';
import { SearchIcon, XIcon } from 'lucide-vue-next';

// Get stores
const tasksStore = useTasksStore();
const tagsStore = useTagsStore();

// Emits
const emit = defineEmits(['search', 'clearFilters']);

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

// Check if any filters are active
const hasActiveFilters = computed(() => {
  return (
    (searchQuery.value && searchQuery.value.trim() !== '') ||
    statusFilter.value !== null ||
    tagFilter.value !== null
  );
});

// Handle search
const handleSearch = () => {
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

  // Update the store with the current filter values
  tasksStore.updateFilters(
    searchTerm || '',
    statusFilter.value,
    tagFilter.value
  );

  // Emit search event to parent component
  emit('search', { searchTerm, statusValue, tagValue });
};

// Clear all filters
const clearAllFilters = () => {
  // Reset all filter values in the store
  tasksStore.clearFilters();

  // Emit clearFilters event to parent component
  emit('clearFilters');
};
</script>
