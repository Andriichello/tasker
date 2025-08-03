<template>
  <div
    :class="[
      'p-4 hover:bg-gray-50 cursor-pointer',
      isUserTask ? 'border-l-4 border-blue-500' : ''
    ]"
    @click="$emit('click', task.id)"
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
    <p class="text-gray-600 mb-2">{{ truncatedDescription }}</p>
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
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { Task } from '../api/models/task';

// Props
interface Props {
  task: Task;
  currentUserId?: number | null;
}

const props = defineProps<Props>();

// Computed properties
const isUserTask = computed(() => {
  return props.currentUserId && props.task.user_id === props.currentUserId;
});

const truncatedDescription = computed(() => {
  if (!props.task.description) return '';
  return props.task.description.length > 100
    ? props.task.description.substring(0, 100) + '...'
    : props.task.description;
});

// Emits
defineEmits(['click']);
</script>
