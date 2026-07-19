import axios from '@axios';
import { useToast } from "vue-toast-notification";
const $toast = useToast({
    position: 'top-right'
})

const apiClient = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL
  });
export default {
    async fetchStatuses() {
        let statuses = []

        await axios.get('/api/v1/employees/statuses')
            .then(res => {
                statuses = res.data || []
            })
            .catch(err => {
                $toast.error(err.response.data.message || 'Something went wrong while fetching statuses.')
                console.log(error.response)
            })

        return statuses
    },

    async fetchJobTitles() {
        let jobTitles = []

        await axios.get('/api/v1/employees/job-titles')
            .then(res => {
                jobTitles = res.data || []
            })
            .catch(err => {
                $toast.error(err.response.data.message || 'Something went wrong while fetching job titles.')
                console.log(error.response)
            })

        return jobTitles
    },

    async fetchAllEmployees(params = {}) {
        let employees = []

        await axios.get('/api/v1/employees/all', {
            params: params
        })
            .then(res => {
                employees = res.data || []
            })
            .catch(err => {
                $toast.error(err.response.data.message || 'Something went wrong while fetching approved timesheets.')
                console.log(error.response)
            })

        return employees
    },

    async fetchEmployees(params){
        return await axios.get(`/api/v1/employees`, {
            params: params
        }).then(response => response.data)
    },

    async addEmployee(param){
        return await axios.post('/api/v1/employees', param).then(response => response.data)
    },

    async updateEmployee(param){
        return await axios.post('/api/v1/employees/' + param.id, param).then(response => response.data)
    },

    async addBankInfo(param){
        return await axios.post('/api/v1/bank_info', param).then(response => response.data)
    },

    async updateBankInfo(param){
        return await axios.put('/api/v1/bank_info/' + param.id, param).then(response => response.data)
    },

    async updateRow(param){
        return await axios.put(`/api/v1/employee/${param.id}`, param).then(response => response.data)
    },

    async updateEmergencyContact(param){
        return await axios.put(`/api/v1/employees/${param.employee_id}/emergency-contacts`, param).then(response => response.data)
    },

    async updateMedical(param){
        return await axios.put(`/api/v1/employee/${param.employee_id}/medical-hostories`, param).then(response => response.data)
    },

    async updateAddress(param){
        return await axios.put(`/api/v1/employee/${param.employee_id}/addresses`, param).then(response => response.data)
    },

    async fetch_approvers(param){
        return await axios.get(`/api/v1/employees/${param.employee_id}/approvers`, param).then(response => response.data)
    },

    async fetch_line_managers(param){
        return await axios.get(`/api/v1/divisions/${param.division_id}/line-managers`, param).then(response => response.data)
    },

    async fetch_employee_by_name(param){
        return await axios.get('/api/v1/employees', {params: param}).then(response => response.data)
    },

    async fetch_widget_data(){
        return await axios.get('api/v1/employees/reports').then(response => response.data)
    },

    async multipleFilter(payload){
        return await axios.get('/api/v1/employees', {
            params: payload
        }).then(response => response.data)
    },

    async update_attachment(param){
        return await axios.post(`/api/employee/${param.employee_id}/attachment`, param).then(response => response.data)
    },
    async fetch_activity_logs(param){
        return await apiClient.get(`/api/v1/users/${param.employee_id}/logs`).then(response => response.data)
    },
    async add_notes(param){
        return await apiClient.post('/api/v1/notes/create', param).then(response => response.data)
    },
    async update_notes(param){
        return await apiClient.post('/api/v1/notes/update', param).then(response => response.data)
    },
    async fetch_notes(param){
        return await apiClient.get('/api/v1/notes/user', {
            params: param
        }).then(response => response.data)
    },
    async delete_notes(param){
        return await apiClient.delete(`api/v1/notes/delete/${param.id}`).then(response => response.data)
    },
}
