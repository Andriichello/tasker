<template>
  <footer class="bg-white py-4">
    <div class="container mx-auto px-4">
      <div class="flex justify-between items-center gap-y-1">
        <div class="text-gray-600 text-sm">
          &copy; {{ currentYear }} Tasker
        </div>
        <div class="text-gray-600 text-sm">
          Simple task manager
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
