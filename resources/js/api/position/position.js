import axios from '@axios';

export const fetchPositions = () => {
  return axios.get('/api/positions').then((reponse) => reponse.data)
}

export const fetchActivePositions = () => {
  return axios.get('/api/positions/active').then((reponse) => reponse.data)
}

const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL
});

export default {
  async add_position(param){
    return await apiClient.post('/api/positions/create', param).then(response => response.data)
  },
  async update_position(param){
    return await apiClient.post('/api/positions/update', param).then(response => response.data)
  },
  async fetch_position(param){
    console.log(param)
    return await apiClient.get('/api/positions/list', {
      params: {
        page: param?.page,
        per_page: param?.per_page,
        search: param?.search
      }
    }).then(response => response.data)
  },
  async fetch_employees(param){
    return await apiClient.post('api/positions/employees', param).then(response => response.data)
  },
  async delete_position(param){
    return await apiClient.delete(`api/positions/delete/${param.id}`).then(response => response.data)
  },
}
