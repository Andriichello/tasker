import axios, { AxiosRequestConfig } from 'axios';

// Declare axios on window object
declare global {
  interface Window {
    axios: typeof axios;
  }
}

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Add a request interceptor to include the token in all requests
axios.interceptors.request.use(
  (config: AxiosRequestConfig): AxiosRequestConfig => {
    // Get token from localStorage
    const token = localStorage.getItem('token');

    // If token exists, add it to the Authorization header
    if (token) {
      if (!config.headers) {
        config.headers = {};
      }
      config.headers.Authorization = `Bearer ${token}`;
    }

    return config;
  },
  (error: any): Promise<never> => {
    return Promise.reject(error);
  }
);
