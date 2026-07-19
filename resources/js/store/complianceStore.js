import { fetchCompliances } from '@/api/compliance/compliance';
import { defineStore } from 'pinia';

export const useComplianceStore = defineStore('compliances', {
    state: () => ({
      data:{
        compliances:[]
      }
    }),
    actions: {
        addCompliance(compliance) {
            this.data.compliances.push(compliance)
        },
        async setCompliances() {
          try {
              const response = await fetchCompliances();
              this.data.compliances = response;
          } catch (error) {
              console.error("Error fetching compliances:", error);
          }
        }
    }
})