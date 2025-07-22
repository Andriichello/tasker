<template>
  <nav class="bg-blue-600 text-white p-4">
    <div class="container mx-auto flex justify-between items-center">
      <!-- App Name -->
      <div class="text-xl font-bold">
        <a href="/" class="hover:text-blue-200">Tasker</a>
      </div>

      <!-- User Info and Dropdown -->
      <div class="relative">
        <div v-if="isAuthenticated" class="flex items-center cursor-pointer" @click="toggleDropdown">
          <span class="mr-2">{{ user.name }}</span>
          <ChevronDown class="h-5 w-5" />
        </div>
        <div v-else>
          <a href="/login" class="hover:text-blue-200" v-if="!isLogin">
            Login
          </a>
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
import { ChevronDown } from 'lucide-vue-next';
import { useAuthStore } from '../stores';
import type { Me } from '../api/models/me';

// Get auth store
const authStore = useAuthStore();

// State
const showDropdown = ref<boolean>(false);

// Computed properties
const isLogin = computed(() => window.location.pathname === '/login');
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

  // Redirect to home page
  window.location.href = '/';
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
