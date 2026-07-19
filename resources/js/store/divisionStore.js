/* 

space
 */
import { fetchDivisions } from '@/api/division/division';
import { defineStore } from 'pinia';

export const useDivisionStore = defineStore('divisions', {
    state: () => ({
      data:{
        divisions:[]
      }
    }),
    actions: {
        addDivision(division) {
            this.data.divisions.push(division)
        },
        async setDivisions() {
          try {
              const response = await fetchDivisions();
              this.data.divisions = response;
          } catch (error) {
              console.error("Error fetching divisions:", error);
          }
        }
    }
})
