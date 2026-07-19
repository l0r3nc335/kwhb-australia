/* 

space
 */
import { fetchLocations } from '@/api/location/location';
import { defineStore } from 'pinia';

export const useLocationStore = defineStore('locations', {
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
              const response = await fetchLocations();
              this.data.locations = response;
          } catch (error) {
              console.error("Error fetching locations:", error);
          }
        }
    }
})
