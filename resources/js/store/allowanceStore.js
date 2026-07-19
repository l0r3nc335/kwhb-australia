import  allowanceApi  from '@/api/allowance/allowanceApi';
import { defineStore } from 'pinia';

export const useAllowanceStore = defineStore('allowances', {
    state: () => ({
      data:{
        allowances:[]
      }
    }),
    actions: {
        addAllowances(allowance) {
            this.data.allowances.push(allowance)
        },
        async setAllowances() {
          try {
              const response = await allowanceApi.fetchAllowancesActive();
              this.data.allowances = response;
          } catch (error) {
              console.error("Error fetching allowances:", error);
          }
        }
    }
})
