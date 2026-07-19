import axios from '@axios';

export const fetchVaccinations = () => {
  return axios.get('/api/v1/vaccination').then((response) => response.data)
}

const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL
});

export default {
  async add_vaccination(param){
    return await apiClient.post('/api/v1/vaccination/create', param).then(response => response.data)
  },
  async update_vaccination(param){
    return await apiClient.post('/api/v1/vaccination/update', param).then(response => response.data)
  },
  async fetch_vaccination(param){
    return await apiClient.post('/api/v1/vaccination/list', param).then(response => response.data)
  },
  async fetch_vaccination_by_role(param){
    return await apiClient.post('/api/v1/vaccination/role', param).then(response => response.data)
  },
  async delete_vaccination(param){
    return await apiClient.delete(`/api/v1/vaccination/delete/${param.id}`).then(response => response.data)
  },
  async add_employee_vaccination(param){
    return await apiClient.post('/api/v1/vaccination/employee/create', param).then(response => response.data)
  },
  async update_employee_vaccination(param){
    return await apiClient.post('/api/v1/vaccination/employee/update', param).then(response => response.data)
  },
  async fetch_employee_vaccination(param){
    return await apiClient.post('/api/v1/vaccination/employee', param).then(response => response.data)
  },
  async fetch_employee_vaccination_list(param){
    return await apiClient.post('/api/v1/vaccination/employee/list', param).then(response => response.data)
  },
  async delete_employee_vaccination(param){
    return await apiClient.delete(`/api/v1/vaccination/employee/delete/${param.id}`).then(response => response.data)
  },
  async employee_vaccination_approval(param){
    return await apiClient.post('/api/v1/vaccination/employee/approval', param).then(response => response.data)
  },
}
