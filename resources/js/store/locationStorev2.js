/* 

space
 */
import { fetchActiveLocations } from '@/api/location/location';
import { defineStore } from 'pinia';

export const useLocationStorev2 = defineStore('locations', {
    state: () => ({
      data:{
        locations:[]
      }
    }),
    actions: {
        addLocation(location) {
            this.data.locations.push(location)
        },
        async setLocations() {
          try {
              const response = await fetchActiveLocations();
              this.data.locations = response;
          } catch (error) {
              console.error("Error fetching locations:", error);
          }
        }
    }
})
