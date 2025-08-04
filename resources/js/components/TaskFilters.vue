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
            v-model="localSearchQuery"
            placeholder="Search tasks..."
            class="w-full min-w-xs max-w-3xs pl-10 pr-4 py-3 border border-gray-300 rounded-lg transition-colors"
          />
        </div>
      </div>

      <div class="flex flex-row flex-wrap gap-3">
        <!-- Status Filter -->
        <Multiselect
          v-model="localStatus"
          :options="statusOptionsWithAll"
          :searchable="true"
          mode="single"
          placeholder="Select status"
          class="min-w-3xs"
        />

        <!-- Tag Filter -->
        <Multiselect
          v-model="localTag"
          :options="availableTags"
          :searchable="true"
          mode="single"
          placeholder="Select tag"
          class="min-w-3xs"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {computed, ref, watch} from 'vue';
import {useTagsStore} from '@/stores';
import {SearchIcon} from 'lucide-vue-next';
import Multiselect from '@vueform/multiselect';
import '@vueform/multiselect/themes/default.css';

// Define props
const props = defineProps({
  searchQuery: {
    type: String,
    default: ''
  },
  statusFilter: {
    type: [String, Object, null],
    default: null
  },
  tagFilter: {
    type: [String, Object, null],
    default: null
  }
});

// Get tags store
const tagsStore = useTagsStore();

// Emits
const emit = defineEmits(['update:searchQuery', 'update:statusFilter', 'update:tagFilter', 'search', 'clearFilters', 'statusFilterChange', 'tagFilterChange']);

const localSearchQuery = ref(props.searchQuery || '');
const localStatus = ref(props.tagFilter || null);
const localTag = ref(props.tagFilter || null);

// Watch for changes to localSearchQuery
watch(localSearchQuery, (newValue) => {
  // Emit update event immediately
  emit('update:searchQuery', newValue);
});

watch(localStatus, (newValue) => {
  // Emit update event immediately
  emit('update:statusFilter', newValue);
});

watch(localTag, (newValue) => {
  // Emit update event immediately
  emit('update:tagFilter', newValue);
});

// Set up watchers for props to update local state
watch(() => props.searchQuery, (newValue) => {
  if (newValue !== localSearchQuery.value) {
    localSearchQuery.value = newValue;
  }
}, {immediate: true});

watch(() => props.statusFilter, (newValue) => {
  if (newValue !== localStatus.value) {
    localStatus.value = newValue;
  }
}, {immediate: true});

watch(() => props.tagFilter, (newValue) => {
  if (newValue !== localTag.value) {
    localTag.value = newValue;
  }
}, {immediate: true});

// Status options for the status filter
const statusOptions = [
  { value: 'to-do', label: 'To Do' },
  { value: 'in-progress', label: 'In Progress' },
  { value: 'canceled', label: 'Canceled' },
  { value: 'done', label: 'Done' }
];

// Status options with "All Statuses" option
const statusOptionsWithAll = computed(() => {
  return [
    { value: null, label: 'All Statuses' },
    ...statusOptions
  ];
});

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
    (localSearchQuery.value && localSearchQuery.value.trim() !== '') ||
    localStatus.value !== null ||
    localTag.value !== null
  );
});

// Clear all filters
const clearAllFilters = () => {
  // Emit clearFilters event to parent component
  emit('clearFilters');
};
</script>
