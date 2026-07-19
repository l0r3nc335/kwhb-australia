import axios from '@axios'
import { useToast } from "vue-toast-notification"

const $toast = useToast({
    position: 'top-right'
})

export default {
    async fetchJobTitles() {
        let jobTitles = []

        await axios.get('/api/v1/job_titles')
            .then(res => {
                jobTitles = res.data || []
            })
            .catch(err => {
                $toast.error(err.response.data.message || 'Something went wrong while fetching job titles.')
                console.log(err.response)
            })

        return jobTitles
    },

  async fetchJobTitlesByName(names) {
    let jobTitles = []
    await axios.post('/api/v1/job_titles/by_names', names)
      .then(res => {
        jobTitles = res.data || []
      })
      .catch(err => {
        $toast.error(err.response.data.message || 'Something went wrong while fetching job titles.')
        console.log(err.response)
      })

    return jobTitles
  },

    async fetchPaginatedJobTitles(params = {}) {
        try {
          const response = await axios.get('/api/v1/job_titles/get_paginated_job_title', {
            params
          });
      
          return response.data;
        } catch (error) {
          $toast.error(error?.response?.data?.message || 'Something went wrong while fetching job titles.');
          console.error(error?.response);
          return {
            data: [],
            total: 0
          };
        }
    },


    async fetchActiveJobTitles(params = {}) {
        try {
          const response = await axios.get('/api/v1/job_titles/get_active_jobs', {
            params
          });
      
          return response.data;
        } catch (error) {
          $toast.error(error?.response?.data?.message || 'Something went wrong while fetching job titles.');
          console.error(error?.response);
          return {
            data: [],
            total: 0
          };
        }
    },

    async fetchActiveJobTitlesForDivision(params = {}) {
      try {
        const response = await axios.get('/api/v1/job_titles/get_active_jobs_for_division', {
          params
        });

          return response.data;
        } catch (error) {
          $toast.error(error?.response?.data?.message || 'Something went wrong while fetching job titles.');
          console.error(error?.response);
          return {
            data: [],
            total: 0
        };
      }
    },

    async fetchActiveJobTitlesList(params = {}) {
      try {
        const response = await axios.get('/api/v1/job_titles/get_active_jobs_for_division', {
          params
        });

        return response.data;
      } catch (error) {
        $toast.error(error?.response?.data?.message || 'Something went wrong while fetching job titles.');
        console.error(error?.response);
        return {
          data: [],
          total: 0
        };
      }
    },

    async addJobTitle({ name, division_ids, classification, start_date, end_date, is_active = 1, vaccinations = [], compliances = [] }) {
        const response = await axios.post('/api/v1/job_titles', {
            name,
            division_ids,
            classification,
            start_date,
            end_date,
            is_active,
            vaccinations,
            compliances
        })

        return response;
    },

    async updateJobTitleStatus(id, is_active) {
        try {
          const response = await axios.post(`/api/v1/job_titles/${id}/status`, {
            is_active: is_active
          });
    
          $toast.success('Job title status updated.');
          return response.data;
        } catch (error) {
          $toast.error(error?.response?.data?.message || 'Failed to update job title status.');
          console.error(error?.response);
          return null;
        }
    },
    
    async getJobTitleHistory(id) {
        try {
            const response = await axios.get(`/api/v1/job_titles/${id}/history`);
            return response.data;
        } catch (error) {
            $toast.error(error?.response?.data?.message || 'Failed to fetch job title history.');
            console.error(error?.response);
            return [];
        }
    }    
}
