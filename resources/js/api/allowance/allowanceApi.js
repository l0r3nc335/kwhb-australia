import axios from '@axios'
import { useToast } from "vue-toast-notification"
const $toast = useToast({ position: 'top-right' })

export default {
  async fetchAllowances() {
    let allowances = []

    await axios.get('/api/v1/allowances')
      .then(res => {
        allowances = res.data || []
      })
      .catch(err => {
        $toast.error(err.response?.data?.message || 'Something went wrong while fetching allowances.')
        console.error(err.response)
      })

    return allowances
  },

  async fetchAllowancesActive() {
    let allowances = []

    await axios.get('/api/v1/allowances/active')
      .then(res => {
        allowances = res.data || []
      })
      .catch(err => {
        $toast.error(err.response?.data?.message || 'Something went wrong while fetching allowances.')
        console.error(err.response)
      })

    return allowances
  },

  async fetchAllowancesPackage(idList) {
    let allowances = []

    await axios.post('/api/v1/allowances/package', idList)
      .then(res => {
        allowances = res.data || []
      })
      .catch(err => {
        $toast.error(err.response?.data?.message || 'Something went wrong while fetching allowances.')
        console.error(err.response)
      })

    return allowances
  },

  async fetchPaginatedAllowances(params = {}) {
    try {
      const response = await axios.get('/api/v1/allowances/get_paginated_allowance', {
        params
      })

      return response.data
    } catch (error) {
      $toast.error(error?.response?.data?.message || 'Something went wrong while fetching allowances.')
      console.error(error?.response)
      return {
        data: [],
        total: 0
      }
    }
  },

  async addAllowance({ name, description, position_id, value, frequency, start_date, end_date }) {
    try {
      const response = await axios.post('/api/v1/allowances/create', {
        name,
        description,
        position_id,
        value,
        frequency,
        start_date,
        end_date
      })

      $toast.success('Allowance created successfully.')
      return response.data
    } catch (error) {
      $toast.error(error?.response?.data?.message || 'Failed to create allowance.')
      console.error(error?.response)
      return null
    }
  },

  async updateAllowancesStatus() {
    try {
      const response = await axios.post(`/api/v1/allowances/update_statuses`)

      $toast.success('Allowances status updated successfully.')
      return response.data
    } catch (error) {
      $toast.error(error?.response?.data?.message || 'Failed to update allowances status.')
      console.error(error?.response)
      return null
    }
  },

  async updateAllowance(id, payload) {
    try {
      const response = await axios.post(`/api/v1/allowances/update/${id}`, payload)

      $toast.success('Allowance updated successfully.')
      return response.data
    } catch (error) {
      $toast.error(error?.response?.data?.message || 'Failed to update allowance.')
      console.error(error?.response)
      return null
    }
  },

  async updateStatus(id, status) {
    try {
      const response = await axios.post(`/api/v1/allowances/${id}/status`, { status })
      $toast.success('Allowance status updated.')
      return response.data
    } catch (error) {
      $toast.error(error?.response?.data?.message || 'Failed to update allowance status.')
      console.error(error?.response)
      return null
    }
  },

  // async deleteAllowance(id) {
  //   try {
  //     const response = await axios.delete(`/api/v1/allowances/delete/${id}`)
  //     $toast.success('Allowance deleted successfully.')
  //     return response.data
  //   } catch (error) {
  //     $toast.error(error?.response?.data?.message || 'Failed to delete allowance.')
  //     console.error(error?.response)
  //     return null
  //   }
  // },

  async getAllowanceHistory(id) {
    try {
      const response = await axios.get(`/api/v1/allowances/${id}/history`)
      return response.data
    } catch (error) {
      $toast.error(error?.response?.data?.message || 'Failed to fetch allowance history.')
      console.error(error?.response)
      return []
    }
  }
}
