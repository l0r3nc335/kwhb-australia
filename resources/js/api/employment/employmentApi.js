import axios from '@axios'
import { useToast } from "vue-toast-notification"
const $toast = useToast({
    position: 'top-right'
})

export default {
    async fetchEmploymentTypes() {
        let employmentTypes = []

        await axios.get('/api/v1/employments')
            .then(res => {
                employmentTypes = res.data || []
            })
            .catch(error => {
                $toast.error(error?.response?.data?.message || 'Something went wrong while fetching employment types.')
                console.log(error?.response)
            })

        return employmentTypes
    },

    async fetchActiveEmploymentTypes() {
        let employmentTypes = []

        await axios.get('/api/v1/employments/active')
            .then(res => {
                employmentTypes = res.data || []
            })
            .catch(error => {
                $toast.error(error?.response?.data?.message || 'Something went wrong while fetching employment types.')
                console.log(error?.response)
            })

        return employmentTypes
    },    

    async fetchPaginatedEmploymentTypes(params = {}) {
        try {
            const response = await axios.get('/api/v1/employments/get_paginated_employment_type', {
                params
            })

            return response.data
        } catch (error) {
            $toast.error(error?.response?.data?.message || 'Something went wrong while fetching employment types.')
            console.error(error?.response)
            return {
                data: [],
                total: 0
            }
        }
    },

    async addEmploymentType({ name, description = null, valid_from = null, hours_per_week = null }) {
        try {
            const data = {
                name,
                description,
                valid_from,
                hours_per_week, 
            };
            const response = await axios.post('/api/v1/employments', data);

            if (response.data && response.data.success === false) {
                $toast.error(response.data.message || 'Employment type already exists.');
                return null;
            }

            $toast.success('Employment type created.');
            return response.data;
        } catch (error) {
            $toast.error(error?.response?.data?.message || 'Failed to create employment type.');
            console.error(error?.response);
            return null;
        }
    },


    async updateEmploymentTypeStatus(id, is_active) {
        try {
            const response = await axios.post(`/api/v1/employments/${id}/status`, {
                is_active: is_active
            })

            $toast.success('Employment type status updated.')
            return response.data
        } catch (error) {
            $toast.error(error?.response?.data?.message || 'Failed to update employment type status.')
            console.error(error?.response)
            return null
        }
    },

    async getEmploymentTypeHistory(id) {
        try {
            const response = await axios.get(`/api/v1/employments/${id}/history`)
            return response.data
        } catch (error) {
            $toast.error(error?.response?.data?.message || 'Failed to fetch employment type history.')
            console.error(error?.response)
            return []
        }
    },
}
