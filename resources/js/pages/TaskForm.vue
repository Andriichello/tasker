<template>
  <div class="min-h-[calc(100vh-116px)]  bg-gradient-to-br from-gray-50 to-gray-100">
    <div class="container mx-auto px-6 py-8">
      <div class="max-w-3xl mx-auto">
        <!-- Page Header -->
        <div class="mb-8">
          <button
            @click="goBack"
            class="flex items-center gap-2 text-gray-600 hover:text-gray-900 transition-colors mb-4"
          >
            <ArrowLeft class="h-4 w-4" />
            Back
          </button>
          <h2 class="text-3xl font-bold text-gray-900 mb-2">{{ isEditMode ? 'Edit Task' : 'Create New Task' }}</h2>
          <p class="text-gray-600">{{ isEditMode ? 'Update your task details' : 'Add a new task to your list' }}</p>
        </div>

        <!-- Loading state (for edit mode) -->
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
            Back
          </button>
        </div>

        <!-- Form -->
        <div v-else class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
          <form @submit.prevent="submitForm" class="space-y-6">
            <div>
              <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Task Title *</label>
              <input
                v-model="form.title"
                type="text"
                id="title"
                placeholder="Enter a descriptive title for your task"
                class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                required
              />
            </div>

            <div>
              <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
              <textarea
                v-model="form.description"
                id="description"
                rows="4"
                placeholder="Provide more details about this task..."
                class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none"
              ></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select
                  v-model="form.status"
                  id="status"
                  class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                  required
                >
                  <option value="to-do">To Do</option>
                  <option value="in-progress">In Progress</option>
                  <option value="done">Done</option>
                  <option value="canceled">Canceled</option>
                </select>
              </div>

              <div>
                <label for="visibility" class="block text-sm font-medium text-gray-700 mb-2">Visibility</label>
                <select
                  v-model="form.visibility"
                  id="visibility"
                  class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                  required
                >
                  <option value="public">Public</option>
                  <option value="private">Private</option>
                </select>
              </div>
            </div>

            <!-- Interactive Tags Input -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Tags</label>
              <div class="space-y-3">
                <!-- Tag Input -->
                <div class="flex gap-2">
                  <input
                    v-model="newTag"
                    type="text"
                    placeholder="Enter a tag"
                    class="flex-1 px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                    @keydown.enter.prevent="addTag"
                  />
                  <button
                    type="button"
                    @click="addTag"
                    class="px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
                  >
                    Add
                  </button>
                </div>
                <p class="text-xs text-gray-500">Press Enter or click Add to add a tag</p>

                <!-- Tag Display -->
                <div v-if="form.tags && form.tags.length > 0" class="flex flex-wrap gap-2">
                  <span
                    v-for="(tag, index) in form.tags"
                    :key="index"
                    class="inline-flex items-center gap-1 px-2 py-1 bg-gray-200 text-gray-600 rounded-md text-s, font-medium"
                  >
                    {{ tag }}
                    <button
                      type="button"
                      @click="removeTag(index)"
                      class="ml-1 text-gray-500 hover:text-gray-700 transition-colors"
                    >
                      <X class="h-3 w-3" />
                    </button>
                  </span>
                </div>
              </div>
            </div>

            <div class="flex gap-4 pt-6">
              <button
                type="button"
                @click="goBack"
                class="flex-1 px-6 py-3 border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="submitting"
                class="flex-1 bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
              >
                <Loader v-if="submitting" class="h-5 w-5 animate-spin" />
                {{ submitting ? 'Saving...' : (isEditMode ? 'Update Task' : 'Create Task') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import {
  ArrowLeft,
  AlertCircle,
  X,
  Loader
} from 'lucide-vue-next';
import { useAuthStore, useTasksStore } from '../stores';
import type { Task } from '../api/models/task';
import type { StoreTaskRequest } from '../api/models/storeTaskRequest';
import type { UpdateTaskRequest } from '../api/models/updateTaskRequest';
import { useRouter, useRoute } from 'vue-router';

// Get stores, router and route
const authStore = useAuthStore();
const tasksStore = useTasksStore();
const router = useRouter();
const route = useRoute();

// State
const form = ref<StoreTaskRequest | UpdateTaskRequest>({
  title: '',
  description: '',
  status: 'to-do',
  visibility: 'public',
  tags: []
});
const newTag = ref('');
const submitting = ref(false);

// Computed properties
const isAuthenticated = computed(() => authStore.isAuthenticated);
const loading = computed(() => tasksStore.isLoading);
const error = computed(() => tasksStore.getError);

// Determine if we're in edit mode based on the route
const isEditMode = computed(() => {
  return route.name === 'TaskEdit';
});

// Get task ID from route params if in edit mode
const taskId = computed(() => {
  if (!isEditMode.value) return null;
  return parseInt(route.params.taskId as string, 10);
});

// Fetch task data if in edit mode
const fetchTask = async (): Promise<void> => {
  if (!isEditMode.value || !taskId.value) return;

  const task = await tasksStore.fetchTask(taskId.value);

  if (task) {
    // Populate form with task data
    form.value = {
      title: task.title,
      description: task.description || '',
      status: task.status,
      visibility: task.visibility,
      tags: task.tags || []
    };
  }
};

// Add a tag to the list
const addTag = (): void => {
  if (newTag.value.trim() && !form.value.tags?.includes(newTag.value.trim())) {
    if (!form.value.tags) {
      form.value.tags = [];
    }
    form.value.tags.push(newTag.value.trim());
    newTag.value = '';
  }
};

// Remove a tag from the list
const removeTag = (index: number): void => {
  if (form.value.tags) {
    form.value.tags.splice(index, 1);
  }
};

// Submit the form
const submitForm = async (): Promise<void> => {
  submitting.value = true;
  tasksStore.clearError();

  try {
    if (isEditMode.value && taskId.value) {
      // Update existing task
      const updatedTask = await tasksStore.updateTask(taskId.value, form.value as UpdateTaskRequest);
      if (updatedTask) {
        router.push(`/${taskId.value}`);
      }
    } else {
      // Create new task
      const newTask = await tasksStore.createTask(form.value as StoreTaskRequest);
      if (newTask) {
        router.push(`/${newTask.id}`);
      }
    }
  } catch (err) {
    console.error('Error saving task:', err);
  } finally {
    submitting.value = false;
  }
};

// Navigate back
const goBack = (): void => {
  if (isEditMode.value && taskId.value) {
    router.push(`/${taskId.value}`);
  } else {
    router.push('/');
  }
};

// Initialize component
onMounted(async () => {
  // Check authentication first
  const user = await authStore.getMe();

  // Redirect to home if not authenticated
  if (!user) {
    await router.push('/');
    return;
  }

  // Fetch task data if in edit mode
  if (isEditMode.value) {
    await fetchTask();
  }
});
</script>

<style scoped>
.container {
  max-width: 1400px;
}
</style>
