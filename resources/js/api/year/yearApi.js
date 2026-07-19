import axios from '@axios'
import { useToast } from "vue-toast-notification"
const $toast = useToast({ position: 'top-right' })

export default {
  // Fetch all years (descending)
  async fetchYears() {
    try {
      const res = await axios.get('/api/v1/year')
      return res.data || []
    } catch (error) {
      $toast.error(error?.response?.data?.message || 'Something went wrong while fetching years.')
      console.error(error?.response)
      return []
    }
  },

  // Add new year (auto: current year if empty, next year otherwise)
  async addYear() {
    try {
      const res = await axios.post('/api/v1/year')
      $toast.success('Year added.')
      return res.data
    } catch (error) {
      $toast.error(error?.response?.data?.message || 'Failed to add year.')
      console.error(error?.response)
      return null
    }
  },

  // If you ever need to delete a year (not in your controller, just for example)
  async deleteYear(id) {
    try {
      const res = await axios.delete(`/api/v1/year/${id}`)
      $toast.success('Year deleted.')
      return res.data
    } catch (error) {
      $toast.error(error?.response?.data?.message || 'Failed to delete year.')
      console.error(error?.response)
      return null
    }
  },

  // (Optional) Get the latest year
  async fetchLatestYear() {
    try {
      const years = await this.fetchYears()
      return years && years.length ? years[0] : null
    } catch {
      return null
    }
  }
}
