/*

space
 */
import auth from "@/api/auth"
import router from '@/router'
import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    roles: [],
    loading: false,
    is_logged_in: false,
  }),
  getters: {
    userData(state) {
      return state.user
    },
  },
  persist: true,
  actions: {
    async login(payload) {
      this.loading = true
      try {
        await auth.useCSRF()
        await auth.useLogin(payload).then(async r => {
          this.user = r
          localStorage.setItem('user', JSON.stringify(r))
          localStorage.setItem('is_logged_in', 'true')
          await this.getAuth()
          router.push('/')
          this.loading = false
        })
        this.is_logged_in = true
      } catch (error) {
        this.is_logged_in = false
        this.loading = false
      }
    },
    async getAuth() {
      await auth.useUserData()
        .then(response => {
          this.user = response.data
          this.is_logged_in = true
          localStorage.setItem('user', JSON.stringify(this.user))
          localStorage.setItem('is_logged_in', true)
        })
        .catch(e => {
          localStorage.setItem('is_logged_in', false)

          // router.push('/login')
        })
    },
    async logout() {
      this.loading = true
      try {
        await auth.useLogOut({})
        this.is_logged_in = false
        router.push('/login')
        localStorage.removeItem('is_logged_in')
        localStorage.removeItem('user')

      } catch (error) {
        this.is_logged_in = true
        this.loading = false
      }
      this.loading = false
    },
    async forgot(payload) {
      return await auth.useForgotPassword(payload).then(async r => {
        return r
      })
    },
    async reset(payload) {
      return await auth.useResetPassword(payload).then(async r => {
        return r
      })
    },
  },
})
