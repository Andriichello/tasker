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
    /**
     * Fetch tags from the API
     * @param forceReload - Force reload from API even if tags are already loaded
     *                      This is used on page refresh or initial load
     * @returns Array of tags
     */
    async fetchTags(forceReload: boolean = false) {
      // If we already have tags and don't need to force reload, return them
      if (this.tags.length > 0 && !forceReload) {
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
