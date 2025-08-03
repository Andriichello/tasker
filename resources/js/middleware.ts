import { useAuthStore } from './stores';

/**
 * Authentication middleware
 * Only redirects authenticated users from login page to home page
 * @param requireAuth - Whether authentication is required for the route (not used for redirects)
 * @returns - Returns true if the user can access the route, false otherwise
 */
export const authMiddleware = (requireAuth = false): Promise<boolean> => {
  const authStore = useAuthStore();

  const me = authStore.getMe();
  const isAuthenticated = authStore.isAuthenticated;

  // If user is authenticated but tries to access login page, redirect to home
  if (isAuthenticated && window.location.pathname === '/login') {
    window.location.href = '/';
    return Promise.resolve(false);
  }

  if (requireAuth && !isAuthenticated) {
    window.location.href = '/login';
    return Promise.resolve(false);
  }

  return Promise.resolve(true);
};
