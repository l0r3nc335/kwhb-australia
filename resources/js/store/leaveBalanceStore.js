/* 

space
 */
import api from '@/api/leaveBalance/leaveBalance';
import { defineStore } from 'pinia';

export const useLeaveBalanceStore = defineStore('leave_balance', {
    state: () => ({
      data:{
        leave_balance:{}
      }
    }),
    actions: {
        async fetchLeaveBalance(param) {
          try {
              const response = await api.fetchLeaveBalance(param);
              this.data.leave_balance = response.data;
              return response.data;
          } catch (error) {
              console.error("Error fetching leave_balance:", error);
          }
        }
    }
})
