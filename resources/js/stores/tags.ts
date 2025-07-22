import { defineStore } from 'pinia';
import { indexTags } from '../api/services/tags';
import type { Tag } from '../api/models/tag';

// Helper function to load persisted state from localStorage
const loadPersistedState = (key: string) => {
  try {
    const storedState = localStorage.getItem(`pinia-${key}`);
    return storedState ? JSON.parse(storedState) : null;
  } catch (e) {
    console.error(`Error loading persisted state for ${key}:`, e);
    return null;
  }
};

// Get persisted tags data
const persistedState = loadPersistedState('tags');

export const useTagsStore = defineStore('tags', {
  state: () => ({
    tags: persistedState?.tags || [] as Tag[],
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
        this.persistState();
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
    },

    // Persist state to localStorage
    persistState() {
      try {
        localStorage.setItem('pinia-tags', JSON.stringify({
          tags: this.tags
        }));
      } catch (e) {
        console.error('Error persisting tags state:', e);
      }
    }
  },
});
