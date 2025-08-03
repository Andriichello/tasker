import { defineStore } from 'pinia';
import { login, me } from '../api/services/auth';
import type { Me } from '../api/models/me';
import type { LoginRequest } from '../api/models/loginRequest';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('token') || null as string | null,
    me: null as Me | null,
    isLoadingMe: false,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token && state.me,
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
          return true;
        }
        return false;
      } catch (error) {
        this.token = null;
        this.me = null;
        localStorage.removeItem('token');
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
    },
  },
});
