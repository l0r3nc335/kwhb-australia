import axios from '@axios'
import { useToast } from "vue-toast-notification"
const $toast = useToast({
    position: 'top-right'
})

export default {
    async fetchLocations() {
        let locations = []

        await axios.get('/api/v1/locations')
            .then(res => {
                locations = res.data || []
            })
            .catch(error => {
                $toast.error(error?.response?.data?.message || 'Something went wrong while fetching locations.')
                console.log(error?.response)
            })

        return locations
    },

    async fetchActiveLocations() {
        let locations = []

        await axios.get('/api/v1/locations_active')
            .then(res => {
                locations = res.data || []
            })
            .catch(error => {
                $toast.error(error?.response?.data?.message || 'Something went wrong while fetching locations.')
                console.log(error?.response)
            })

        return locations
    },

    async fetchPaginatedLocations(params = {}) {
        try {
            const response = await axios.get('/api/v1/locations/get_paginated_location', {
                params
            })

            return response.data
        } catch (error) {
            $toast.error(error?.response?.data?.message || 'Something went wrong while fetching locations.')
            console.error(error?.response)
            return {
                data: [],
                total: 0
            }
        }
    },

    async addLocation(payload) {
        // log again to ensure this version is invoked
        console.log("API.addLocation received:", payload);
        return axios.post('/api/v1/locations', payload);
    },

    async updateLocationStatus(id, is_active) {
        try {
            const response = await axios.post(`/api/v1/locations/${id}/status`, {
                is_active: is_active
            })

            $toast.success('Location status updated.')
            return response.data
        } catch (error) {
            $toast.error(error?.response?.data?.message || 'Failed to update location status.')
            console.error(error?.response)
            return null
        }
    },

    async getLocationHistory(id) {
        try {
            const response = await axios.get(`/api/v1/locations/${id}/history`)
            return response.data
        } catch (error) {
            $toast.error(error?.response?.data?.message || 'Failed to fetch location history.')
            console.error(error?.response)
            return []
        }
    }
}
