<template>
  <div class="container mx-auto py-6">
      <h1 class="text-3xl font-bold mb-6">{{ isEditMode ? 'Edit Task' : 'Create New Task' }}</h1>

      <!-- Loading state (for edit mode) -->
      <div v-if="loading" class="flex justify-center items-center py-10">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-500"></div>
      </div>

      <!-- Error state -->
      <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        {{ error }}
        <div class="mt-4">
          <button @click="goBack" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
            Back
          </button>
        </div>
      </div>

      <!-- Form -->
      <form v-else @submit.prevent="submitForm" class="bg-white rounded-lg shadow-md p-6">
        <!-- Title -->
        <div class="mb-4">
          <label for="title" class="block text-gray-700 font-semibold mb-2">Title *</label>
          <input
            type="text"
            id="title"
            v-model="form.title"
            class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
          >
        </div>

        <!-- Description -->
        <div class="mb-4">
          <label for="description" class="block text-gray-700 font-semibold mb-2">Description</label>
          <textarea
            id="description"
            v-model="form.description"
            rows="5"
            class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
          ></textarea>
        </div>

        <!-- Status -->
        <div class="mb-4">
          <label for="status" class="block text-gray-700 font-semibold mb-2">Status *</label>
          <select
            id="status"
            v-model="form.status"
            class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
          >
            <option value="to-do">To Do</option>
            <option value="in-progress">In Progress</option>
            <option value="done">Done</option>
            <option value="canceled">Canceled</option>
          </select>
        </div>

        <!-- Visibility -->
        <div class="mb-4">
          <label for="visibility" class="block text-gray-700 font-semibold mb-2">Visibility *</label>
          <select
            id="visibility"
            v-model="form.visibility"
            class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
          >
            <option value="public">Public</option>
            <option value="private">Private</option>
          </select>
        </div>

        <!-- Tags -->
        <div class="mb-6">
          <label class="block text-gray-700 font-semibold mb-2">Tags</label>
          <div class="flex flex-wrap gap-2 mb-2">
            <div
              v-for="(tag, index) in form.tags"
              :key="index"
              class="bg-blue-100 text-blue-800 px-3 py-1 rounded flex items-center"
            >
              {{ tag }}
              <button
                type="button"
                @click="removeTag(index)"
                class="ml-2 text-blue-600 hover:text-blue-800"
              >
                &times;
              </button>
            </div>
          </div>
          <div class="flex">
            <input
              type="text"
              v-model="newTag"
              @keydown.enter.prevent="addTag"
              placeholder="Add a tag and press Enter"
              class="flex-grow px-3 py-2 border rounded-l focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
            <button
              type="button"
              @click="addTag"
              class="bg-blue-500 text-white px-4 py-2 rounded-r hover:bg-blue-600"
            >
              Add
            </button>
          </div>
        </div>

        <!-- Form buttons -->
        <div class="flex justify-between">
          <button
            type="button"
            @click="goBack"
            class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
            :disabled="submitting"
          >
            {{ submitting ? 'Saving...' : (isEditMode ? 'Update Task' : 'Create Task') }}
          </button>
        </div>
      </form>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useAuthStore, useTasksStore } from '../stores';
import type { Task } from '../api/models/task';
import type { StoreTaskRequest } from '../api/models/storeTaskRequest';
import type { UpdateTaskRequest } from '../api/models/updateTaskRequest';
import BaseLayout from "../layouts/BaseLayout.vue";
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
