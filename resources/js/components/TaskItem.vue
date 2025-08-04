<template>
  <div @click="$emit('click', task.id)">
    <div class="rounded-lg p-5 border border-gray-200 hover:shadow-md transition-all duration-200 cursor-pointer flex flex-col gap-4">
      <!-- Task Header -->
      <div class="flex items-start justify-between gap-2">
        <div class="flex flex-col flex-1 gap-2">
          <h3 class="text-lg font-semibold text-gray-900 hover:text-blue-600 transition-colors">
            {{ task.title }}
          </h3>
          <p class="text-gray-600 text-sm leading-relaxed line-clamp-2">{{ task.description }}</p>
        </div>

        <div class="flex gap-2"
             @click.stop
             v-if="isOwner">
          <button
            @click="$emit('edit', task)"
            class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all cursor-pointer"
          >
            <EditIcon class="h-4 w-4"/>
          </button>
          <button
            @click="$emit('delete', task.id)"
            class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all cursor-pointer"
          >
            <Trash2Icon class="h-4 w-4"/>
          </button>
        </div>
      </div>

      <!-- Task Meta -->
      <div class="flex flex-wrap items-center justify-between gap-y-3 gap-5">
        <div class="flex items-center gap-2">
          <span class="px-3 py-1 text-xs font-medium rounded-full flex items-center gap-1.5"
                :class="[getStatusStyle(task.status)]">
            <component :is="getStatusIcon(task.status)" class="h-3 w-3"/>
            {{ formatStatus(task.status) }}
          </span>
          <span class="px-2 py-1.5 text-xs font-medium rounded-full flex items-center gap-1.5"
                :class="[task.visibility === 'private'   ? 'bg-purple-100 text-purple-700' : 'bg-green-100 text-green-700']">
            <component
              :is="task.visibility === 'private' ? LockIcon : GlobeIcon"
              class="h-3 w-3"
            />
          </span>
          <!-- Author Display -->
          <div class="flex items-center gap-2">
            <div
              class="w-6 h-6 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center">
              <span class="text-xs font-medium text-white">{{ userInitials }}</span>
            </div>
            <span class="text-xs text-gray-600 font-medium">{{ userName }}</span>
          </div>
        </div>

        <div class="grow flex justify-end items-center gap-5">
          <div v-if="tags.length > 0" class="flex justify-end grow gap-1">
              <span v-for="tag in tags.slice(0, 3)" :key="tag"
                    class="px-2 py-1 text-xs bg-gray-200 text-gray-600 rounded-md font-medium">
                {{ tag }}
              </span>
            <span v-if="tags.length > 3"
                  class="px-2 py-1 text-xs bg-gray-200 text-gray-500 rounded-md">
              +{{ tags.length - 3 }}
            </span>
          </div>

          <span class="w-fit text-xs text-gray-500"
                v-if="task.created_at || task.updated_at">
            {{ formatDate(task.updated_at || task.created_at as string) }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {computed} from 'vue';
import type {Task} from '@/api';
import {
  CheckCircleIcon,
  CircleIcon,
  CircleXIcon,
  EditIcon,
  GlobeIcon,
  LockIcon,
  PlayIcon,
  Trash2Icon
} from 'lucide-vue-next';

// Emits
defineEmits(['click', 'edit', 'delete']);

// Props
interface Props {
  task: Task;
  currentUserId?: number | null;
}

const props = defineProps<Props>();

// Computed properties
const tags = computed(() => {
  return props.task.tags ?? [];
});

const user = computed(() => {
  return props.task.user;
});

const userName = computed(() => {
  return user.value ? user.value.name : 'Unknown';
});

const userInitials = computed(() => {
  return userName.value?.charAt(0) || 'U';
});

const isOwner = computed(() => {
  return props.currentUserId && props.task.user_id === props.currentUserId;
});

// Helper functions
const getStatusStyle = (status: string) => {
  switch (status) {
    case 'done':
      return 'bg-green-100 text-green-700 border border-green-200';
    case 'in-progress':
      return 'bg-blue-100 text-blue-700 border border-blue-200';
    case 'to-do':
      return 'bg-gray-100 text-gray-700 border border-gray-200';
    case 'canceled':
      return 'bg-red-100 text-red-700 border border-red-200';
    default:
      return 'bg-gray-100 text-gray-700 border border-gray-200';
  }
};

const getStatusIcon = (status: string) => {
  switch (status) {
    case 'done':
      return CheckCircleIcon;
    case 'in-progress':
      return PlayIcon;
    case 'canceled':
      return CircleXIcon;
    case 'to-do':
      return CircleIcon;
    default:
      return CircleIcon;
  }
};

const formatStatus = (status: string) => {
  switch (status) {
    case 'to-do':
      return 'To Do';
    case 'in-progress':
      return 'In Progress';
    case 'canceled':
      return 'Canceled';
    case 'done':
      return 'Done';
    default:
      return status;
  }
};

const formatDate = (dateString?: string) => {
  if (!dateString) return '';

  const date = new Date(dateString);
  const now = new Date();
  const diffTime = Math.abs(now.getTime() - date.getTime());
  const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));

  if (diffDays === 0) return 'Today';
  if (diffDays === 1) return 'Yesterday';
  if (diffDays < 7) return `${diffDays} days ago`;

  return date.toLocaleDateString('en-US', {month: 'short', day: 'numeric'});
};
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
