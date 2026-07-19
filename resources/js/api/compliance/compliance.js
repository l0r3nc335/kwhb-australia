import axios from '@axios';

export const fetchCompliances = () => {
  return axios.get('/api/v1/compliance').then((reponse) => reponse.data)
}

const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL
});

export default {
  async add_compliance(param){
    return await apiClient.post('/api/v1/compliance/create', param).then(response => response.data)
  },
  async update_compliance(param){
    return await apiClient.post('/api/v1/compliance/update', param).then(response => response.data)
  },
  async fetch_compliance(param){
    return await apiClient.post('/api/v1/compliance/list', param).then(response => response.data)
  },
  async fetch_compliance_by_role(param){
    return await apiClient.post('/api/v1/compliance/role', param).then(response => response.data)
  },
  async delete_compliance(param){
    return await apiClient.delete(`/api/v1/compliance/delete/${param.id}`).then(response => response.data)
  },
  async add_employee_compliance(param){
    return await apiClient.post('/api/v1/compliance/employee/create', param).then(response => response.data)
  },
  async update_employee_compliance(param){
    return await apiClient.post('/api/v1/compliance/employee/update', param).then(response => response.data)
  },
  async fetch_employee_compliance(param){
    return await apiClient.post('/api/v1/compliance/employee', param).then(response => response.data)
  },
  async fetch_employee_compliance_list(param){
    return await apiClient.post('/api/v1/compliance/employee/list', param).then(response => response.data)
  },
  async delete_employee_compliance(param){
    return await apiClient.delete(`/api/v1/compliance/employee/delete/${param.id}`).then(response => response.data)
  },
  async employee_compliance_approval(param){
    return await apiClient.post('/api/v1/compliance/employee/approval', param).then(response => response.data)
  },
}
