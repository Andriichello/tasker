<template>
  <component :is="layout">
    <router-view/>
  </component>
</template>

<script setup lang="ts">
import {computed} from 'vue';
import AuthLayout from './layouts/AuthLayout.vue';
import BaseLayout from './layouts/BaseLayout.vue';
import {useRouter} from 'vue-router';

const router = useRouter();
const layout = computed(
  () => router.currentRoute.value.fullPath === '/login'
    ? AuthLayout : BaseLayout
);
</script>
