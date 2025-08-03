<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center p-6">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md">
      <div class="text-center mb-8">
        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <CheckSquareIcon class="h-8 w-8 text-blue-600" />
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Welcome to Tasker</h1>
        <p class="text-gray-600">Sign in to manage your tasks</p>
      </div>

      <!-- Login Form -->
      <form @submit.prevent="handleLogin" class="flex flex-col gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
          <input
            v-model="form.email"
            type="email"
            placeholder="Enter your email"
            :class="[
              'w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors',
              error || validationErrors.email ? 'border-red-300 bg-red-50' : 'border-gray-200'
            ]"
            required
          />
          <p v-if="validationErrors.email" class="text-red-500 text-xs mt-1">{{ validationErrors.email }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
          <div class="relative">
            <input
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              placeholder="Enter your password"
              :class="[
                'w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors pr-12',
                error || validationErrors.password ? 'border-red-300 bg-red-50' : 'border-gray-200'
              ]"
              required
            />
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
            >
              <component :is="showPassword ? EyeOffIcon : EyeIcon" class="h-5 w-5" />
            </button>
          </div>
          <p v-if="validationErrors.password" class="text-red-500 text-xs mt-1">{{ validationErrors.password }}</p>
        </div>

        <!-- Error Message -->
        <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4">
          <div class="flex items-center gap-2">
            <AlertCircleIcon class="h-5 w-5 text-red-500" />
            <p class="text-sm text-red-700">{{ error }}</p>
          </div>
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition-colors font-medium disabled:opacity-50 cursor-pointer disabled:cursor-not-allowed flex items-center justify-center gap-2"
        >
          <LoaderIcon v-if="loading" class="h-5 w-5 animate-spin" />
          {{ loading ? 'Signing in...' : 'Sign In' }}
        </button>

        <button @click.stop="router.push('/')" class="w-full text-md text-gray-500 hover:text-gray-700 transition-colors px-4 py-3 rounded-lg hover:bg-gray-100 cursor-pointer">
          Continue as Guest
        </button>
      </form>

      <!-- Demo Credentials -->
      <div class="mt-4 p-4 bg-blue-50 rounded-lg cursor-pointer"
           @click="form.email = 'first@example.com'; form.password = 'secret'">
        <p class="text-md text-blue-700 font-medium mb-2">Demo Credentials:</p>
        <div class="flex flex-wrap justify-start items-center gap-5 text-md text-blue-600">
          <p>first@example.com</p>
          <p>secret</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {reactive, ref} from 'vue';
import {useAuthStore} from '@/stores';
import {useRouter} from 'vue-router';
import {
  CheckSquareIcon,
  EyeIcon,
  EyeOffIcon,
  AlertCircleIcon,
  LoaderIcon
} from 'lucide-vue-next';

// Get auth store and router
const authStore = useAuthStore();
const router = useRouter();

// Form state
const form = reactive({
  email: '',
  password: ''
});

// UI state
const loading = ref(false);
const error = ref('');
const showPassword = ref(false);
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
      // Redirect to home page on successful login using router
      router.push('/');
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
