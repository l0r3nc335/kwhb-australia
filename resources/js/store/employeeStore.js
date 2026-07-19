/*

space
 */
import api from '@/api/employee/employee'
import { defineStore } from 'pinia'
import { useAuthStore } from './authStore'

let selectedEmployee = localStorage.getItem('employee_selected')
selectedEmployee = selectedEmployee == 'undefined' ? null : selectedEmployee
try {
  selectedEmployee = JSON.parse(selectedEmployee)
} catch (e) {

}
export const useEmployeeStore = defineStore('employees', {
  persist: true,
  state: () => ({
    data: {
      employees: [],
      employee_selected: selectedEmployee || null,
      routeTab: localStorage.getItem('route_tab') || 'EmployeeInfo',
      widget: [
        {
          title: 'Active Employees',
          value: '0',
          change: 0,
          desc: 'Active Total Employees',
          icon: 'tabler-user',
          iconColor: '#62c379',
        },
        {
          title: 'Offswing',
          value: '0',
          change: 0,
          desc: 'Fixed Period Contractors',
          icon: 'tabler-user-plus',
          iconColor: '#9c60e5',
        },
        {
          title: ' Pending Employees',
          value: '0',
          change: 0,
          desc: 'Year to Date',
          icon: 'tabler-user-check',
          iconColor: '#e35306',
        },
        {
          title: 'Extended Leave',
          value: '0',
          change: 0,
          desc: 'Year to Date',
          icon: 'tabler-user-exclamation',
          iconColor: '#e63c49',
        },
      ],
      statuses: [
        { title: 'Active', value: 'active' },
        { title: 'Extended Leave', value: 'extended leave' },
        { title: 'Inactive', value: 'inactive' },
        { title: 'Offswing', value: 'offswing' },
        { title: 'Pending', value: 'pending' },
        { title: 'Terminated', value: 'terminated' },
      ],
    },
  }),
  getters: {
    activeTab: state => state.data.routeTab,
  },
  actions: {
    getSelectedEmployee() {
      return this.data.employee_selected
    },

    async setEmployeeSelected(employee) {
      this.data.employee_selected = employee
      localStorage.setItem('employee_selected', JSON.stringify(employee))
    },
    async setActiveTab(tab) {
      this.data.routeTab = tab
      localStorage.setItem('route_tab', tab)
    },
    async addEmployee(employee) {
      try {
        const response = await api.addEmployee(employee)

        if(!response.error){
          this.data.employee_selected = response.data

          return response.data
        }
        else
          return false

      } catch (error) {
        console.error("Error adding employee:", error)
      }
    },
    async updateEmployee(employee) {
      try {
        const response = await api.updateEmployee(employee)

        if(!response.error){
          this.data.employee_selected = response.data

          return response.data
        }
        else
          return false

      } catch (error) {
        console.error("Error updateEmployee employee:", error)
      }
    },



    async updateRow(data)
    {
      try {
        const response = await api.updateRow(data)

        if(!response.error){
          this.data.employee_selected = response.data

          return response.data
        }
        else
          return false

      } catch (error) {
        console.error("Error adding employee:", error)
      }
    },

    async updateUser(data)
    {
      try {
        const response = await api.updateUser(data)

        if(!response.error){
          this.data.employee_selected = response.data

          return response.data
        }
        else
          return false

      } catch (error) {
        console.error("Error adding employee:", error)
      }
    },

    async updateEmergencyContact(data)
    {
      try {
        const response = await api.updateEmergencyContact(data)

        if(!response.error){
          this.data.employee_selected = response.data

          return response.data
        }
        else
          return false

      } catch (error) {
        console.error("Error adding employee:", error)
      }
    },
    async updateMedical(data)
    {
      try {
        const response = await api.updateMedical(data)

        if(!response.error){
          this.data.employee_selected = response.data

          return response.data
        }
        else
          return false

      } catch (error) {
        console.error("Error updateMedical employee:", error)
      }
    },
    async updateAddress(data)
    {
      try {
        const response = await api.updateAddress(data)

        if(!response.error){
          this.data.employee_selected = response.data

          return response.data
        }
        else
          return false

      } catch (error) {
        console.error("Error updating Address employee:", error)
      }
    },
    async addBankInfo(param) {
      try {
        const response = await api.addBankInfo(param)

        this.data.employee_selected.bank_info = response.data

        if(!response.error)
          return response.data
        else
          return false
      } catch (error) {
        console.error("Error fetching employees:", error)
      }
    },
    async fetchWidgetData() {
      try {
        const response = await api.fetch_widget_data()
        if(!response.error){
          this.data.widget = response.data

          return response.data
        }
        else
          return false
      } catch (error) {
        console.log("Error fetch_widget_data:", error)
      }
    },
    async fetch_approvers(param) {
      try {
        const response = await api.fetch_approvers(param)

        return response.data
      } catch (error) {
        console.log("Error fetching approvers:", error)
      }
    },
    async fetch_line_managers(param) {
      try {
        const response = await api.fetch_line_managers(param)

        return response.data
      } catch (error) {
        console.log("Error fetch_line_managers:", error)
      }
    },
    async setEmployees(params) {
      const authStore = useAuthStore()
      try {
        const response = await api.fetchEmployees(params)

        this.data.employees = response.data

        return response
      } catch (error) {
        console.log("Error fetching employees:", error)
      }
    },
    async multipleFilter(payload) {
      return await api.multipleFilter(payload)
    },
    async updateBankInfo (param) {

      try {
        const response = await api.updateBankInfo(param)

        this.data.employee_selected.bank_info = response.data
        if(!response.error)
          return response.data
        else
          return false
      } catch (error) {
        console.error("Error fetching employees:", error)
      }
    },
    async deleteBankInfo (param) {
      try {
        const response = await api.deleteBankInfo(param)

        this.data.employee_selected.bank_info = response.data

        if(!response.error)
          return response.data
        else
          return false
      } catch (error) {
        console.error("Error fetching employees:", error)
      }
    },
    async fetch_employee_by_name(param) {
      try {
        const response = await api.fetch_employee_by_name(param)

        return response.data
      } catch (error) {
        console.log("Error fetch_employee_by_name:", error)
      }
    },
    async updateAttachment (param) {
      try {
        const response = await api.update_attachment(param)

        if(!response.error){
          this.data.employee_selected = response.data
          
          return response.data
        }
        else
          return false

      } catch (error) {
        console.error("Error adding attachment:", error)
      }
    },
  },
})
