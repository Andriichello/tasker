import { defineStore } from 'pinia';
import { login, me } from '../api/services/auth';
import type { Me } from '../api/models/me';
import type { LoginRequest } from '../api/models/loginRequest';

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

// Get persisted user data
const persistedState = loadPersistedState('auth');

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('token') || null as string | null,
    me: persistedState?.me || null as Me | null,
    isLoadingMe: false,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    user: (state) => state.me,
  },

  actions: {
    async login(credentials: LoginRequest) {
      try {
        const response = await login(credentials);
        const { token, user } = response.data.data || {};

        console.log('token:', token);

        if (token) {
          this.token = token;
          this.me = user || null;
          localStorage.setItem('token', token);
          // Persist user data
          this.persistState();
          return true;
        }
        return false;
      } catch (error) {
        this.token = null;
        this.me = null;
        localStorage.removeItem('token');
        localStorage.removeItem('pinia-auth');
        return false;
      }
    },

    async getMe() {
      // If we already have user data, return it
      if (this.me) {
        return this.me;
      }

      // If a request is already in progress, don't make another one
      if (this.isLoadingMe) {
        return null;
      }

      // If we have a token but no user data, try to fetch it
      if (this.token) {
        try {
          // Set loading flag to true before making the request
          this.isLoadingMe = true;

          const response = await me();

          // Set loading flag to false after receiving the response
          this.isLoadingMe = false;

          if (response.status === 200) {
            this.me = response.data.data;
            // Persist user data
            this.persistState();
            return this.me;
          } else {
            // If the request fails, the token is invalid
            this.logout();
            return null;
          }
        } catch (error) {
          // Set loading flag to false on error
          this.isLoadingMe = false;

          // If there's an error, the token is invalid
          this.logout();
          return null;
        }
      }

      return null;
    },

    logout() {
      this.token = null;
      this.me = null;
      localStorage.removeItem('token');
      localStorage.removeItem('pinia-auth');
    },

    // Persist state to localStorage
    persistState() {
      try {
        localStorage.setItem('pinia-auth', JSON.stringify({
          me: this.me
        }));
      } catch (e) {
        console.error('Error persisting auth state:', e);
      }
    },
  },
});
