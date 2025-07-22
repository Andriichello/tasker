<template>
  <footer class="bg-gray-100 py-4 mt-8">
    <div class="container mx-auto px-4">
      <div class="flex justify-between items-center">
        <div class="text-gray-600 text-sm">
          &copy; {{ currentYear }} Tasker. All rights reserved.
        </div>
        <div class="text-gray-600 text-sm">
          A simple task management application
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup lang="ts">
import { computed } from 'vue';

// Get the current year for copyright
const currentYear  = computed(() => new Date().getFullYear());
</script>
