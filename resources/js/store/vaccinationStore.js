import { fetchVaccinations } from '@/api/vaccination/vaccination';
import { defineStore } from 'pinia';

export const useVaccinationStore = defineStore('vaccinations', {
    state: () => ({
      data:{
        vaccinations:[]
      }
    }),
    actions: {
        addVaccination(vaccination) {
            this.data.vaccinations.push(vaccination)
        },
        async setVaccinations() {
          try {
              const response = await fetchVaccinations();
              this.data.vaccinations = response;
          } catch (error) {
              console.error("Error fetching vaccinations:", error);
          }
        }
    }
})
