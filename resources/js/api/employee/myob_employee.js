import axios from '@axios'

export default {
  async fetchEmployees(){
    return await axios.get('/api/myob/contact/employee').then(response => response.data)
  },
  async fetchEmployeeByDisplayID(param){
    return await axios.get(`/api/myob/fetch_employee_by_display_id/${param}`).then(response => response.data)
  },
}
