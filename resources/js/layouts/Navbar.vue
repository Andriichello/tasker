<template>
  <nav class="bg-white shadow-sm z-[1]">
    <div class="container mx-auto px-6 py-4 flex items-center justify-between">
      <div class="flex items-center gap-3"
           @click="router.push('/')">
        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
          <CheckSquareIcon class="h-5 w-5 text-white" />
        </div>
        <h1 class="text-xl font-bold text-gray-900 cursor-pointer">
          Tasker
        </h1>
      </div>

      <!-- User Info and Dropdown -->
      <div class="relative">
        <div v-if="isAuthenticated" class="flex items-center cursor-pointer" @click="toggleDropdown">
          <span class="mr-2">{{ user.name }}</span>
          <ChevronDown class="h-5 w-5" />
        </div>
        <div v-else>
          <router-link to="/login" class="text-sm text-gray-500 hover:text-gray-700 transition-colors px-4 py-3 rounded-lg hover:bg-gray-100" v-if="!isLogin">
            Log In
          </router-link>
        </div>

        <!-- Dropdown Menu -->
        <div v-if="isAuthenticated && showDropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10">
          <div class="px-4 py-2 text-sm text-gray-700 border-b">
            <div class="font-medium">{{ user.name }}</div>
            <div class="text-gray-500">{{ user.email }}</div>
          </div>
          <a href="#" @click.prevent="logout" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
            Logout
          </a>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { ChevronDown, CheckSquareIcon } from 'lucide-vue-next';
import { useAuthStore } from '../stores';
import type { Me } from '../api/models/me';
import { useRouter } from 'vue-router';

// Get auth store and router
const authStore = useAuthStore();
const router = useRouter();

// State
const showDropdown = ref<boolean>(false);

// Computed properties
const isLogin = computed(() => router.currentRoute.value.path === '/login');
const isAuthenticated = computed(() => authStore.isAuthenticated);
const user = computed(() => authStore.user || {} as Me);


// Toggle dropdown menu
const toggleDropdown = () => {
  showDropdown.value = !showDropdown.value;
};

// Close dropdown when clicking outside
const handleClickOutside = (event: MouseEvent) => {
  const target = event.target as HTMLElement;
  if (showDropdown.value && !target.closest('.relative')) {
    showDropdown.value = false;
  }
};

// Logout function
const logout = () => {
  // Use auth store to logout
  authStore.logout();

  // Close dropdown
  showDropdown.value = false;

  // Redirect to home page using router
  router.push('/');
};

onMounted(() => {
  // Check authentication using the store
  authStore.getMe();

  // Add event listener for clicking outside
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>
