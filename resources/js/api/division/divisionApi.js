import axios from '@axios';
import { useToast } from "vue-toast-notification";
const $toast = useToast({
    position: 'top-right'
})

export default {
    async fetchDivisions() {
        let divisions = []

        await axios.get('/api/v1/divisions')
            .then(res => {
                divisions = res.data || []
            })
            .catch(err => {
                $toast.error(err.response.data.message || 'Something went wrong while fetching divisions.')
                console.log(error.response)
            })

        return divisions
    },

  async fetchDivisionsActive() {
    let divisions = []

    await axios.get('/api/v1/divisions/active')
      .then(res => {
        divisions = res.data || []
      })
      .catch(err => {
        $toast.error(err.response.data.message || 'Something went wrong while fetching divisions.')
        console.log(error.response)
      })

    return divisions
  },

    async fetchPaginatedDivisions(params = {}) {
        try {
          const response = await axios.get('/api/v1/divisions/get_paginated_division', {
            params
          });
      
          return response.data;
        } catch (error) {
          $toast.error(error?.response?.data?.message || 'Something went wrong while fetching divisions.');
          console.error(error?.response);
          return {
            data: [],
            total: 0
          };
        }
    },
      

    async addDivision(name) {
        await axios.post('/api/v1/divisions', {
            name: name
        })
    },

    async updateDivisionStatus(id, is_active) {
        try {
          const response = await axios.post(`/api/v1/divisions/${id}/status`, {
            is_active: is_active
          });
    
          $toast.success('Division status updated.');
          return response.data;
        } catch (error) {
          $toast.error(error?.response?.data?.message || 'Failed to update division status.');
          console.error(error?.response);
          return null;
        }
    },
    
    async getDivisionHistory(id) {
        try {
            const response = await axios.get(`/api/v1/divisions/${id}/history`);
            return response.data;
        } catch (error) {
            $toast.error(error?.response?.data?.message || 'Failed to fetch division history.');
            console.error(error?.response);
            return [];
        }
    }    
}
