<template>
  <div class="max-w-md mx-auto bg-white rounded-lg shadow-md overflow-hidden">
    <div class="bg-blue-600 text-white py-4 px-6">
      <h2 class="text-xl font-bold">Login to Tasker</h2>
    </div>

    <form @submit.prevent="handleLogin" class="p-6">
      <!-- Error Alert -->
      <div v-if="error" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
        {{ error }}
      </div>

      <!-- Email Field -->
      <div class="mb-4">
        <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
        <input
          id="email"
          v-model="form.email"
          type="email"
          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
          :class="{ 'border-red-500': validationErrors.email }"
          placeholder="Enter your email"
          required
        />
        <p v-if="validationErrors.email" class="text-red-500 text-xs mt-1">{{ validationErrors.email }}</p>
      </div>

      <!-- Password Field -->
      <div class="mb-6">
        <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
        <input
          id="password"
          v-model="form.password"
          type="password"
          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
          :class="{ 'border-red-500': validationErrors.password }"
          placeholder="Enter your password"
          required
        />
        <p v-if="validationErrors.password" class="text-red-500 text-xs mt-1">{{ validationErrors.password }}</p>
      </div>

      <!-- Submit Button -->
      <div class="flex items-center justify-between">
        <button
          type="submit"
          class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full"
          :disabled="loading"
        >
          <span v-if="loading" class="flex items-center justify-center">
            <span class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
            Logging in...
          </span>
          <span v-else>Login</span>
        </button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';
import { useAuthStore } from '../stores';

// Get auth store
const authStore = useAuthStore();

// Form state
const form = reactive({
  email: '',
  password: ''
});

// UI state
const loading = ref(false);
const error = ref('');
const validationErrors = reactive({
  email: '',
  password: ''
});

// Clear validation errors
const clearValidationErrors = () => {
  validationErrors.email = '';
  validationErrors.password = '';
};

// Validate form
const validateForm = () => {
  let isValid = true;
  clearValidationErrors();

  if (!form.email) {
    validationErrors.email = 'Email is required';
    isValid = false;
  } else if (!/^\S+@\S+\.\S+$/.test(form.email)) {
    validationErrors.email = 'Please enter a valid email address';
    isValid = false;
  }

  if (!form.password) {
    validationErrors.password = 'Password is required';
    isValid = false;
  } else if (form.password.length < 6) {
    validationErrors.password = 'Password must be at least 6 characters';
    isValid = false;
  }

  return isValid;
};

// Handle login form submission
const handleLogin = async () => {
  // Validate form
  if (!validateForm()) {
    return;
  }

  // Set loading state
  loading.value = true;
  error.value = '';

  try {
    // Call login action from auth store
    const success = await authStore.login({
      email: form.email,
      password: form.password
    });

    if (success) {
      // Redirect to home page on successful login
      window.location.href = '/';
    } else {
      error.value = 'Invalid email or password';
    }
  } catch (err) {
    console.error('Login error:', err);
    error.value = 'An error occurred during login. Please try again.';
  } finally {
    loading.value = false;
  }
};
</script>
