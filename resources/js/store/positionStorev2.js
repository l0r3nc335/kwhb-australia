/* 

space
 */
import { fetchActivePositions } from '@/api/position/position';
import { defineStore } from 'pinia';

export const usePositionStoreV2 = defineStore('positions', {
    state: () => ({
      data:{
        positions:[]
      }
    }),
    actions: {
        addPosition(position) {
            this.data.positions.push(position)
        },
        async setPositions() {
          try {
              const response = await fetchActivePositions();
              this.data.positions = response;
          } catch (error) {
              console.error("Error fetching positions:", error);
          }
        }
    }
})
