/* 

space
 */
import api from '@/api/leaveType/leaveType';
import { defineStore } from 'pinia';

export const useLeaveTypeStore = defineStore('leave_types', {
    state: () => ({
      data:{
        leave_types:[]
      }
    }),
    actions: {
        addLeaves(leavetype) {
            this.data.leave_types.push(leavetype)
        },
        async setLeaveTypes() {
          try {
              const response = await api.fetchLeaveTypes();
              this.data.leave_types = response;
          } catch (error) {
              console.error("Error fetching leave_types:", error);
          }
        }
    }
})
