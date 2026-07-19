/*

space
 */
import { fetchEmployments, updateRow } from '@/api/employment/employment';
import { defineStore } from 'pinia';

export const useEmploymentStore = defineStore('employments', {
    state: () => ({
      data:{
        employments:[]
      }
    }),
    actions: {
        addEmployment(employment) {
            this.data.employments.push(employment)
        },
        async updateRow(data)
        {
          const response = await updateRow(data);
          return response;
        },
        async setEmployments() {
          try {
              const response = await fetchEmployments();
              this.data.employments = response;
          } catch (error) {
              console.error("Error fetching employments:", error);
          }
        }
    }
})
