import axios, { AxiosHeaders } from 'axios';
import type { InternalAxiosRequestConfig } from 'axios';
import {TOKEN_STORAGE_KEY} from "@/stores/auth.ts";

// Declare axios on a window interface
declare global {
  interface Window {
    axios: typeof axios;
  }
}

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Add a request interceptor to include the token in all requests
axios.interceptors.request.use(
  (config: InternalAxiosRequestConfig): InternalAxiosRequestConfig => {
    const token = localStorage.getItem(TOKEN_STORAGE_KEY);

    // If token exists then add it to the Authorization header
    if (token) {
      if (!config.headers) {
        config.headers = new AxiosHeaders();
      }
      config.headers.set('Authorization', `Bearer ${token}`);
    }

    return config;
  },
  (error: any): Promise<never> => {
    return Promise.reject(error);
  }
);
