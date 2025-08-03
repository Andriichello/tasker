import { defineStore } from 'pinia';
import { indexTags } from '@/api';
import type { Tag } from '@/api';

export const useTagsStore = defineStore('tags', {
  state: () => ({
    tags: [] as Tag[],
    loading: false,
    error: null as string | null,
  }),

  getters: {
    getTags: (state) => state.tags,
    isLoading: (state) => state.loading,
    hasError: (state) => !!state.error,
    getError: (state) => state.error,
  },

  actions: {
    async fetchTags() {
      // If we already have tags, return them
      if (this.tags.length > 0) {
        return this.tags;
      }

      this.loading = true;
      this.error = null;

      try {
        const response = await indexTags();
        this.tags = response.data.data || [];
        return this.tags;
      } catch (error) {
        this.error = 'Failed to fetch tags';
        return [];
      } finally {
        this.loading = false;
      }
    },

    clearError() {
      this.error = null;
    }
  },
});
