import axios from '@axios'

export default {
  async fetchEmployees(params){
    let page = 1
    if(params)
      page = params.page

    return await axios.get(`/api/v1/employees?page=${page}`, {
      params: params
    }).then(response => response.data)
  },
  async addEmployee(param){
    return await axios.post('/api/employee', param).then(response => response.data)
  },
  async updateEmployee(param){
    return await axios.put('/api/employee/' + param.id, param).then(response => response.data)
  },
  async addBankInfo(param){
    return await axios.post('/api/bank_info', param).then(response => response.data)
  },
  async updateBankInfo(param){
    return await axios.put('/api/bank_info/' + param.id, param).then(response => response.data)
  },
  async deleteBankInfo(param){
    return await axios.delete('/api/bank_info/' + param.id).then(response => response.data)
  },
  async updateRow(param){
    return await axios.post('/api/employee/update_row', param).then(response => response.data)
  },
  async updateUser(param){
    return await axios.post('/api/employee/update_user', param).then(response => response.data)
  },
  async updateEmergencyContact(param){
    return await axios.post('/api/employee/update_emergency_contact', param).then(response => response.data)
  },
  async updateMedical(param){
    return await axios.post('/api/employee/update_medical', param).then(response => response.data)
  },
  async updateAddress(param){
    return await axios.post('/api/employee/update_address', param).then(response => response.data)
  },
  async fetch_approvers(param){
    return await axios.get(`/api/v1/employees/${param.employee_id}/approvers`, param).then(response => response.data)
  },
  async fetch_line_managers(param){
    return await axios.get(`/api/v1/divisions/${param.division_id}/line-managers`, param).then(response => response.data)
  },
  async fetch_employee_by_name(param){
    return await axios.post('/api/employee/fetch_employee_by_name', param).then(response => response.data)
  },
  async fetch_widget_data(){
    return await axios.post('api/employee/fetch_widget_data').then(response => response.data)
  },
  async multipleFilter(payload){
    return await axios.get('/api/v1/employees', {
      params: payload
    }).then(response => response.data)
  },
  async update_attachment(param){
    return await axios.post('/api/employee/update_attachment', param).then(response => response.data)
  },
}
