import { defineStore } from 'pinia';
import { indexTags } from '../api/services/tags';
import type { Tag } from '../api/models/tag';

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
