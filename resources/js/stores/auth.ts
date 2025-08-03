import {defineStore} from 'pinia';
import type {LoginRequest, Me} from '@/api';
import {login, me} from '@/api';

export interface AuthState {
  token: string | null;
  me: Me | null;
  isLoadingMe: boolean;
}

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('token') as string | null,
    me: null as Me | null,
    isLoadingMe: false,
  } as AuthState),

  getters: {
    isAuthenticated: (state: AuthState) => !!state.token && !!state.me,
    user: (state: AuthState) => state.me,
  },

  actions: {
    setToken(token: string | null) {
      this.token = token;

      if (token) {
        localStorage.setItem('token', token);
      } else {
        localStorage.removeItem('token');
      }
    },

    async login(credentials: LoginRequest) {
      try {
        const response = await login(credentials);
        const { token, user } = response.data.data || {};

        if (token) {
          this.setToken(token);
          this.me = user || null;
          return true;
        }
        return false;
      } catch (error) {
        this.setToken(null);
        this.me = null;
        return false;
      }
    },

    async loadMe() {
      if (this.isLoadingMe || !this.token) {
        return null;
      }

      try {
        this.isLoadingMe = true;
        const response = await me();
        this.isLoadingMe = false;

        if (response.status === 200) {
          this.me = response.data.data;
          return this.me;
        } else {
          this.logout();
        }
      } catch (error) {
        this.logout();
      }

      this.isLoadingMe = false;
      return null;
    },

    async getMe() {
      if (this.me) {
        return this.me;
      }

      return await this.loadMe();
    },

    logout() {
      this.setToken(null);
      this.me = null;
    },
  },
});
