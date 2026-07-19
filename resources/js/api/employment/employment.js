import axios from '@axios';


export const fetchEmployments = () => {
  return axios.get('/api/employment').then((reponse) => reponse.data)
}
export const fetchActiveEmployments = () => {
  return axios.get('/api/employments/active').then((reponse) => reponse.data)
}

export const updateRow = (data) => {
  return 0;
 // return axios.get('/api/employment').then((reponse) => reponse.data)
}

const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL
});

export default {
  async add_employment(param){
    return await apiClient.post('/api/employments/create', param).then(response => response.data)
  },
  async update_employment(param){
    return await apiClient.post('/api/employments/update', param).then(response => response.data)
  },
  async fetch_employment(param){
    return await apiClient.post('api/employments/list', param).then(response => response.data)
  },
  async delete_employment(param){
    return await apiClient.delete(`api/employments/delete/${param.id}`).then(response => response.data)
  },
}


