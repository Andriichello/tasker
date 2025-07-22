<template>
  <div class="container mx-auto py-6">
    <!-- Loading state -->
    <div v-if="loading" class="flex justify-center items-center py-10">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-500"></div>
    </div>

    <!-- Error state -->
    <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
      {{ error }}
      <div class="mt-4">
        <button @click="goBack" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 flex items-center">
          <ArrowLeft class="mr-1 h-4 w-4" />
          Back to Tasks
        </button>
      </div>
    </div>

    <!-- Task navigation and info -->
    <div v-if="!loading && !error" class="flex justify-between items-center mb-4">
      <button @click="goBack" class="text-gray-600 hover:text-gray-800 flex items-center">
        <ArrowLeft class="mr-1 h-4 w-4" />
        Back to Tasks
      </button>
      <span
        :class="[
          'text-sm px-3 py-1 rounded-full',
          task.visibility === 'public' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800'
        ]"
      >
        {{ task.visibility }}
      </span>
    </div>

    <!-- Task details -->
    <div v-if="!loading && !error" class="bg-white rounded-lg shadow-md p-6">
      <div class="flex justify-between items-start mb-6">
        <div>
          <h1 class="text-3xl font-bold mb-2">{{ task.title }}</h1>
          <div class="flex items-center space-x-2 text-sm text-gray-500">
            <span>Created: {{ formatDate(task.created_at) }}</span>
            <span v-if="task.updated_at !== task.created_at">
              | Updated: {{ formatDate(task.updated_at) }}
            </span>
          </div>
        </div>
        <div class="flex space-x-2">
          <span
            :class="[
              'text-sm px-3 py-1 rounded-full',
              task.status === 'to-do' ? 'bg-yellow-100 text-yellow-800' :
              task.status === 'in-progress' ? 'bg-blue-100 text-blue-800' :
              task.status === 'done' ? 'bg-green-100 text-green-800' :
              'bg-red-100 text-red-800'
            ]"
          >
            {{ task.status }}
          </span>
        </div>
      </div>

      <!-- Task description -->
      <div class="mb-6">
        <p class="text-gray-700 whitespace-pre-line">{{ task.description || 'No description provided.' }}</p>
      </div>

      <!-- Task tags -->
      <div class="mb-6" v-if="task.tags && task.tags.length > 0">
        <div class="flex flex-wrap gap-2">
          <span
            v-for="tag in task.tags"
            :key="tag"
            class="bg-gray-100 text-gray-700 px-3 py-1 rounded"
          >
            {{ tag }}
          </span>
          <span v-if="!task.tags || task.tags.length === 0" class="text-gray-500">
            No tags
          </span>
        </div>
      </div>

      <!-- Action buttons -->
      <div class="flex space-x-4 mt-8">
        <!-- Edit and Delete buttons only shown if user is authenticated and owns the task -->
        <template v-if="isAuthenticated && currentUser && currentUser.id === task.user_id">
          <button @click="editTask" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            Edit Task
          </button>
          <button @click="confirmDelete" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
            Delete Task
          </button>
        </template>

        <!-- Status change buttons for task owner -->
        <div v-if="isAuthenticated && currentUser && currentUser.id === task.user_id" class="ml-auto">
          <select
            v-model="newStatus"
            @change="updateStatus"
            class="border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option disabled value="">Change Status</option>
            <option value="to-do">To Do</option>
            <option value="in-progress">In Progress</option>
            <option value="done">Done</option>
            <option value="canceled">Canceled</option>
          </select>
        </div>
      </div>

      <!-- Delete confirmation modal -->
      <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full">
          <h2 class="text-xl font-bold mb-4">Confirm Delete</h2>
          <p class="mb-6">Are you sure you want to delete this task? This action cannot be undone.</p>
          <div class="flex justify-end space-x-4">
            <button
              @click="showDeleteModal = false"
              class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400"
            >
              Cancel
            </button>
            <button
              @click="deleteTask"
              class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600"
            >
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { ArrowLeft } from 'lucide-vue-next';
import { useAuthStore, useTasksStore } from '../stores';
import type { Task } from '../api/models/task';
import type { Me } from '../api/models/me';

// Get stores
const authStore = useAuthStore();
const tasksStore = useTasksStore();

// State
const showDeleteModal = ref(false);
const newStatus = ref('');

// Get task ID from URL
const taskId = parseInt(window.location.pathname.split('/')[1], 10);

// Computed properties
const isAuthenticated = computed(() => authStore.isAuthenticated);
const currentUser = computed(() => authStore.user);
const task = computed(() => tasksStore.getTask || {} as Task);
const loading = computed(() => tasksStore.isLoading);
const error = computed(() => tasksStore.getError);

// Fetch task details
const fetchTask = async () => {
  // First check if user is authenticated
  await authStore.getMe();

  // Fetch task details
  const fetchedTask = await tasksStore.fetchTask(taskId);
  if (fetchedTask) {
    newStatus.value = fetchedTask.status;
  }
};

// Format date
const formatDate = (dateString: string | null): string => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
};

// Navigate back to tasks list
const goBack = (): void => {
  window.location.href = '/';
};

// Navigate to edit page
const editTask = (): void => {
  window.location.href = `/${taskId}/edit`;
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
      window.location.href = '/';
    }
  } catch (err) {
    console.error('Error deleting task:', err);
    showDeleteModal.value = false;
  }
};

// Update task status
const updateStatus = async (): Promise<void> => {
  try {
    const updatedTask = await tasksStore.updateTask(taskId, {
      status: newStatus.value
    });

    if (!updatedTask) {
      // Reset to original status if update failed
      newStatus.value = task.value.status;
    }
  } catch (err) {
    console.error('Error updating task status:', err);
    // Reset to original status
    newStatus.value = task.value.status;
  }
};

// Fetch task on component mount
onMounted(fetchTask);
</script>
