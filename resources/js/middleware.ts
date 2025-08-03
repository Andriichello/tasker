import {useAuthStore} from '@/stores';
import type {
  NavigationGuardWithThis,
  RouteLocationNormalized,
  RouteLocationNormalizedLoaded,
  NavigationGuardNext,
  _Awaitable, NavigationGuardReturn
} from 'vue-router';

export const beforeGuard: NavigationGuardWithThis<undefined> = function(
  to: RouteLocationNormalized,
  from: RouteLocationNormalizedLoaded,
  next: NavigationGuardNext
): _Awaitable<NavigationGuardReturn> {
  const isOpen = to.matched.some(record => !record.meta.requiresAuth);
  const isLogin = to.matched.some(record => record.name === 'Login');

  const authStore = useAuthStore();
  const hasToken = !!authStore.token;

  if (hasToken && isLogin) {
    return next('/');
  }

  if (!isOpen && !hasToken) {
    return next('/login');
  }

  return next();
}
