<template>
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
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useTasksStore, useTagsStore } from '../stores';
import { Search, X } from 'lucide-vue-next';
import Multiselect from '@vueform/multiselect';
import '@vueform/multiselect/themes/default.css';

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
