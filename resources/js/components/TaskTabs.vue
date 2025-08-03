<template>
  <!-- Tabs Navigation -->
  <div class="bg-white rounded-xl shadow-sm border border-gray-100">
    <div class="border-b border-gray-200">
      <nav class="flex flex-wrap px-4 gap-x-4">
        <button
          @click="setActiveTab('all')"
          :class="[
            'grow py-4 px-2 border-b-2 font-medium text-sm transition-colors',
            !isAuthenticated || activeTab === 'all'
              ? 'border-blue-500 text-blue-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
          ]"
        >
          All ({{ counts.all }})
        </button>
        <button
          v-if="isAuthenticated"
          @click="setActiveTab('public')"
          :class="[
            'grow py-4 px-2 border-b-2 font-medium text-sm transition-colors',
            activeTab === 'public'
              ? 'border-blue-500 text-blue-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
          ]"
        >
          Public ({{ counts.public }})
        </button>
        <button
          v-if="isAuthenticated"
          @click="setActiveTab('private')"
          :class="[
            'grow py-4 px-2 border-b-2 font-medium text-sm transition-colors',
            activeTab === 'private'
              ? 'border-blue-500 text-blue-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
          ]"
        >
          Private ({{ counts.private }})
        </button>
        <button
          v-if="isAuthenticated"
          @click="setActiveTab('my')"
          :class="[
            'grow py-4 px-2 border-b-2 font-medium text-sm transition-colors',
            activeTab === 'my'
              ? 'border-blue-500 text-blue-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
          ]"
        >
          My Tasks ({{ counts.my }})
        </button>
      </nav>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

// Props
interface Props {
  activeTab: 'all' | 'public' | 'private' | 'my';
  counts: {
    all: number;
    public: number;
    private: number;
    my: number;
  };
  isAuthenticated: boolean;
}

const props = defineProps<Props>();

// Emits
const emit = defineEmits(['update:activeTab']);

// Set active tab and emit event to parent
const setActiveTab = (tab: 'all' | 'public' | 'private' | 'my') => {
  emit('update:activeTab', tab);
};
</script>
