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

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

// State
const form = ref({
  title: '',
  description: '',
  status: 'to-do',
  visibility: 'public',
  tags: []
});
const newTag = ref('');
const loading = ref(false);
const submitting = ref(false);
const error = ref(null);
const isAuthenticated = ref(false);

// Determine if we're in edit mode based on the URL
const isEditMode = computed(() => {
  const path = window.location.pathname;
  return /^\/\d+\/edit$/.test(path);
});

// Get task ID from URL if in edit mode
const taskId = computed(() => {
  if (!isEditMode.value) return null;
  return window.location.pathname.split('/')[1];
});

// Check if user is authenticated
const checkAuth = async () => {
  try {
    const response = await axios.get('/api/me');
    isAuthenticated.value = true;
    return response.data.data;
  } catch (err) {
    isAuthenticated.value = false;
    window.location.href = '/'; // Redirect to home if not authenticated
    return null;
  }
};

// Fetch task data if in edit mode
const fetchTask = async () => {
  if (!isEditMode.value) return;

  loading.value = true;
  error.value = null;

  try {
    const response = await axios.get(`/api/tasks/${taskId.value}`);
    const task = response.data.data;

    // Populate form with task data
    form.value = {
      title: task.title,
      description: task.description || '',
      status: task.status,
      visibility: task.visibility,
      tags: task.tags || []
    };
  } catch (err) {
    console.error('Error fetching task:', err);
    if (err.response && err.response.status === 404) {
      error.value = 'Task not found.';
    } else if (err.response && err.response.status === 403) {
      error.value = 'You do not have permission to edit this task.';
    } else {
      error.value = 'Failed to load task. Please try again later.';
    }
  } finally {
    loading.value = false;
  }
};

// Add a tag to the list
const addTag = () => {
  if (newTag.value.trim() && !form.value.tags.includes(newTag.value.trim())) {
    form.value.tags.push(newTag.value.trim());
    newTag.value = '';
  }
};

// Remove a tag from the list
const removeTag = (index) => {
  form.value.tags.splice(index, 1);
};

// Submit the form
const submitForm = async () => {
  submitting.value = true;
  error.value = null;

  try {
    if (isEditMode.value) {
      // Update existing task
      await axios.patch(`/api/tasks/${taskId.value}`, form.value);
      window.location.href = `/${taskId.value}`;
    } else {
      // Create new task
      const response = await axios.post('/api/tasks', form.value);
      window.location.href = `/${response.data.data.id}`;
    }
  } catch (err) {
    console.error('Error saving task:', err);
    error.value = 'Failed to save task. Please try again later.';
    submitting.value = false;
  }
};

// Navigate back
const goBack = () => {
  if (isEditMode.value) {
    window.location.href = `/${taskId.value}`;
  } else {
    window.location.href = '/';
  }
};

// Initialize component
onMounted(async () => {
  // Check authentication first
  const user = await checkAuth();
  if (!user) return;

  // Fetch task data if in edit mode
  if (isEditMode.value) {
    await fetchTask();
  }
});
</script>
