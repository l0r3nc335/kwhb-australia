import axios from '@axios';

export const fetchDivisions = () => {
  return axios.get('/api/division').then((reponse) => reponse.data)
}

const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL
});

export default {
  async add_division(param){
    return await apiClient.post('/api/divisions/create', param).then(response => response.data)
  },
  async update_division(param){
    return await apiClient.post('/api/divisions/update', param).then(response => response.data)
  },
  async fetch_division(param){
    return await apiClient.post('api/divisions/list', param).then(response => response.data)
  },
  async delete_division(param){
    return await apiClient.delete(`api/divisions/delete/${param.id}`).then(response => response.data)
  },
}