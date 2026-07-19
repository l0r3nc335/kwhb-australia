/*

space
 */
import api from '@/api/leave/leave';
import { defineStore } from 'pinia';

export const useLeaveStore = defineStore('leave', {
    state: () => ({
      data:{
        leave:[]
      }
    }),
    actions: {
        async saveLeave(param) {
          const response = await api.saveLeave(param);
           if(!response.error){
                return response.data
            }
            else
                return false
        },
        async updateRow(param) {
          const response = await api.updateRow(param);
          return response;
        },
        async fetchLeave(emp_id) {
          const response = await api.fetchLeave(emp_id);
          return response.data;
        },
        async fetchLeaveByApprover() {
          const emp = JSON.parse(localStorage.getItem("user"));

          const response = await api.fetchLeaveByApprover(emp.user.id);
          return response.data;
        },
        async multipleFilter(payload) {
          const response = await api.multipleFilter(payload);
          return response.data;
        }
    }
})
