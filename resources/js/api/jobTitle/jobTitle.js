import axios from '@axios';

export const fetchJobTitles = () => {
  return axios.get('/api/job_title').then((response) => response.data)
}

const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL
});

export default {
  async add_job_title(param) {
    return await apiClient.post('/api/job_titles/create', param).then(response => response.data)
  },
  async update_job_title(param) {
    return await apiClient.post('/api/job_titles/update', param).then(response => response.data)
  },
  async fetch_job_title(param) {
    return await apiClient.post('/api/job_titles/list', param).then(response => response.data)
  },
  async delete_job_title(param) {
    return await apiClient.delete(`api/job_titles/delete/${param.id}`).then(response => response.data)
  },
}
