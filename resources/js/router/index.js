import { useAuthStore } from '@/store/authStore'
import { setupLayouts } from 'virtual:generated-layouts'
import { createRouter, createWebHistory } from 'vue-router'

import routes from '~pages'

const router = createRouter({
  history: createWebHistory('/'),
  routes: [...setupLayouts(routes)],
})

// Global navigation guard
router.beforeEach(async (to, from, next) => {
  const store = useAuthStore()
  const { getAuth } = store
  const loggedIn = localStorage.getItem('is_logged_in') === 'true'

  // Allow public routes
  if (to.name === 'forgot-password' ||
     (to.name === 'reset-password-token' && to.params.token) ||
     (to.name === 'login' && !loggedIn)) {
    return next()
  }

  // Handle login state
  if (loggedIn) {
    if (!store.user) {
      await getAuth()
    }

    // 🚫 Role-based protection
    if (to.meta.requiresSuperAdminHRCEO && ![1, 5, 7, 10].includes(store.user.role.id)) {
      return next('/unauthorized')
    }

    return to.name === 'login' ? next('/') : next()
  }

  // Redirect to login if not authenticated
  next('/login')
})


// Docs: https://router.vuejs.org/guide/advanced/navigation-guards.html#global-before-guards
export default router
