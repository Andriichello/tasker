import { defineStore } from 'pinia';
import { indexUsers, showUser } from '../api/services/users';
import type { User } from '../api/models/user';

export const useUsersStore = defineStore('users', {
  state: () => ({
    users: [] as User[],
    user: null as User | null,
    loading: false,
    error: null as string | null,
  }),

  getters: {
    getUsers: (state) => state.users,
    getUser: (state) => state.user,
    isLoading: (state) => state.loading,
    hasError: (state) => !!state.error,
    getError: (state) => state.error,
  },

  actions: {
    async fetchUsers() {
      this.loading = true;
      this.error = null;

      try {
        const response = await indexUsers();
        this.users = response.data.data || [];
        return this.users;
      } catch (error) {
        this.error = 'Failed to fetch users';
        return [];
      } finally {
        this.loading = false;
      }
    },

    async fetchUser(id: number) {
      this.loading = true;
      this.error = null;

      try {
        const response = await showUser(id);
        this.user = response.data.data || null;
        return this.user;
      } catch (error) {
        this.error = `Failed to fetch user with ID ${id}`;
        return null;
      } finally {
        this.loading = false;
      }
    },

    clearUser() {
      this.user = null;
    },

    clearError() {
      this.error = null;
    }
  },
});
