import toast from '@/composable/toast.js'
import axios from 'axios'

const axiosIns = axios.create({
  // You can add your headers here
  // ================================
  // baseURL: 'https://some-domain.com/api/',
  // timeout: 1000,
  // headers: {'X-Custom-Header': 'foobar'}
})

// ℹ️ Add request interceptor to send the authorization header on each subsequent request after login
axiosIns.interceptors.request.use((config) => {
  // Retrieve token from localStorage
  const token = localStorage.getItem('accessToken')

  // If token is found
  if (token) {
    // Get request headers and if headers is undefined assign blank object
    config.headers = config.headers || {}

    // Set authorization header
    // ℹ️ JSON.parse will convert token to string
    config.headers.Authorization = token ? `Bearer ${JSON.parse(token)}` : ''
  }

  // Return modified config
  return config
})

// ℹ️ Add response interceptor to handle 401 response
axiosIns.interceptors.response.use(
  (response) => {
    return response
  },
  (error) => {
    // Handle error

    switch (error.response.status) {
      case 500:
        toast('error', 'Server Error')
        break

      case 400:
        toast('error', error.response.data.message)
        break

      case 401:
        localStorage.setItem('is_logged_in', false)
        toast('error', error.response.data.message)
        break

      case 422:
        const errors = error.response.data.errors
        Object.keys(errors).forEach((key, index) => {
          toast('error', errors[key][0])
        })
        break

      case 405:
        toast('error', 'Method not allowed')
        break

      case 429:
        localStorage.setItem('is_logged_in', false)
        toast('error', 'Too many attempts')
        break

      default:
        return Promise.reject(error)
        break
    }
  }
)
export default axiosIns
