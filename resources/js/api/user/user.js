import axios from '@axios';

const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL
});

export default {
  async get_users(param){
    return await apiClient.get('/api/v1/users', { params: param }).then(response => response.data)
  }
}
