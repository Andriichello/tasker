<template>
  <div class="min-h-[calc(100vh-116px)] flex bg-gradient-to-br from-gray-50 to-gray-100">
    <div class="container mx-auto px-6 py-8">
      <div class="max-w-3xl mx-auto">
        <!-- Loading state -->
        <div v-if="loading" class="flex justify-center items-center py-16">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        </div>

        <!-- Error state -->
        <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-xl p-6 mb-6">
          <div class="flex items-center gap-2 mb-4">
            <AlertCircle class="h-5 w-5 text-red-500" />
            <p class="text-red-700 font-medium">{{ error }}</p>
          </div>
          <button
            @click="goBack"
            class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors flex items-center gap-2"
          >
            <ArrowLeft class="h-4 w-4" />
            Back to Tasks
          </button>
        </div>

        <div v-if="!loading && !error">
          <!-- Page Header -->
          <div class="mb-8">
            <button
              @click="goBack"
              class="flex items-center gap-2 text-gray-600 hover:text-gray-900 transition-colors mb-4"
            >
              <ArrowLeft class="h-4 w-4" />
              Back to Tasks
            </button>
          </div>

          <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Task Header -->
            <div class="px-8 pt-8 pb-6 border-b border-gray-200">
              <div class="flex-1">
                <div class="flex items-start justify-between gap-2 mb-6">
                  <h1 class="text-3xl font-bold text-gray-900">{{ task.title }}</h1>
                  <div class="flex gap-2"
                       v-if="isAuthenticated && currentUser && currentUser.id === task.user_id">
                    <button
                      @click="editTask"
                      class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all cursor-pointer"
                    >
                      <EditIcon class="h-4 w-4"/>
                    </button>
                    <button
                      @click="confirmDelete"
                      class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all cursor-pointer"
                    >
                      <Trash2Icon class="h-4 w-4"/>
                    </button>
                  </div>
                </div>

                <div class="flex flex-wrap items-center justify-start gap-4">
                  <div class="flex items-center gap-4">
                    <span
                    :class="[
                    'px-4 py-2 text-sm font-medium rounded-full flex items-center gap-2',
                    getStatusStyle(task.status)
                  ]"
                  >
                  <component :is="getStatusIcon(task.status)" class="h-4 w-4" />
                  {{ formatStatus(task.status) }}
                </span>
                    <span
                      :class="[
                    'px-4 py-2 text-sm font-medium rounded-full flex items-center gap-2',
                    task.visibility === 'private'
                      ? 'bg-purple-100 text-purple-700'
                      : 'bg-green-100 text-green-700'
                  ]"
                    >
                  <component
                    :is="task.visibility === 'private' ? LockIcon : GlobeIcon"
                    class="h-4 w-4"
                  />
                  {{ task.visibility }}
                </span>
                  </div>

                  <!-- Author Info -->
                  <div v-if="task.user" class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center">
                      <span class="text-sm font-medium text-white">{{ task.user.name.charAt(0) }}</span>
                    </div>
                    <div>
                      <p class="text-sm font-medium text-gray-900">{{ task.user.name }}</p>
                      <p class="text-xs text-gray-500">{{ task.user.email }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Task Content -->
            <div class="px-8 py-6">
              <!-- Description Section -->
              <div class="mb-5">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Description</h2>
                <div class="prose prose-gray max-w-none">
                  <p class="text-gray-700 leading-relaxed whitespace-pre-line">
                    {{ task.description || 'No description provided.' }}
                  </p>
                </div>
              </div>



              <div class="flex justify-between items-end mt-3">
                <!-- Tags Section -->
                <div v-if="task.tags && task.tags.length > 0">
                  <h2 class="text-xl font-semibold text-gray-900 mb-4">Tags</h2>
                  <div class="flex flex-wrap gap-2">
                  <span
                    v-for="tag in task.tags"
                    :key="tag"
                    class="px-2 py-1 text-md bg-gray-200 text-gray-600 rounded-md font-medium"
                  >
                    {{ tag }}
                  </span>
                  </div>
                </div>

                <div v-else>
                  <!-- Placeholder -->
                </div>

                <span class="text-md text-gray-500" v-if="task.created_at || task.updated_at">
                  {{ formatDate(task.updated_at || task.created_at) }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Delete confirmation modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black/80 bg-opacity-50 flex items-center justify-center z-50">
          <div class="bg-white p-8 rounded-xl shadow-xl max-w-md w-full">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Confirm Delete</h2>
            <p class="text-gray-600 mb-6">Are you sure you want to delete this task? This action cannot be undone.</p>
            <div class="flex justify-end gap-4">
              <button
                @click="showDeleteModal = false"
                class="px-4 py-2 border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
              >
                Cancel
              </button>
              <button
                @click="deleteTask"
                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
              >
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {computed, onMounted, ref} from 'vue';
import {
  AlertCircle,
  ArrowLeft,
  CheckCircleIcon,
  CircleIcon,
  CircleXIcon,
  EditIcon,
  Globe as GlobeIcon,
  Lock as LockIcon,
  PlayIcon,
  Trash2Icon
} from 'lucide-vue-next';
import {useAuthStore, useTasksStore} from '../stores';
import type {Task} from '../api/models/task';
import {useRoute, useRouter} from 'vue-router';

// Get stores, router and route
const authStore = useAuthStore();
const tasksStore = useTasksStore();
const router = useRouter();
const route = useRoute();

// State
const showDeleteModal = ref(false);
const newStatus = ref('');

// Get task ID from route params
const taskId = parseInt(route.params.taskId as string, 10);

// Computed properties
const isAuthenticated = computed(() => authStore.isAuthenticated);
const currentUser = computed(() => authStore.user);
const task = computed(() => tasksStore.getTask || {} as Task);
const loading = computed(() => tasksStore.isLoading);
const error = computed(() => tasksStore.getError);

// Fetch task details
const fetchTask = async () => {
  // First check if user is authenticated, but don't wait for it if we already have user data
  if (!authStore.user) {
    await authStore.getMe();
  }

  // Fetch task details
  const fetchedTask = await tasksStore.fetchTask(taskId);
  if (fetchedTask) {
    newStatus.value = fetchedTask.status;
  }
};

// Format date (simple version)
const formatDate = (dateString: string | null): string => {
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

// Format date (full version)
const formatFullDate = (dateString: string | null): string => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

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

// Navigate back to tasks list
const goBack = (): void => {
  router.push('/');
};

// Navigate to edit page
const editTask = (): void => {
  router.push(`/${taskId}/edit`);
};

// Show delete confirmation modal
const confirmDelete = (): void => {
  showDeleteModal.value = true;
};

// Delete task
const deleteTask = async (): Promise<void> => {
  try {
    const success = await tasksStore.deleteTask(taskId);
    if (success) {
      router.push('/');
    }
  } catch (err) {
    console.error('Error deleting task:', err);
    showDeleteModal.value = false;
  }
};

// Fetch task on component mount
onMounted(fetchTask);
</script>

<style scoped>
.prose {
  max-width: none;
}
</style>
