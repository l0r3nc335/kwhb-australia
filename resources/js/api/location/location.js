import axios from '@axios';

export const fetchLocations = () => {
  return axios.get('/api/location').then((reponse) => reponse.data)
}

export const fetchActiveLocations = () => {
  return axios.get('/api/locations/active').then((reponse) => reponse.data)
}

const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL
});

export default {
  async add_location(param){
    return await apiClient.post('/api/locations/create', param).then(response => response.data)
  },
  async update_location(param){
    return await apiClient.post('/api/locations/update', param).then(response => response.data)
  },
  async fetch_location(param){
    return await apiClient.post('api/locations/list', param).then(response => response.data)
  },
  async delete_location(param){
    return await apiClient.delete(`api/locations/delete/${param.id}`).then(response => response.data)
  },
}
