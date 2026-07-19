/* 

space
 */
import { fetchPositions } from '@/api/position/position';
import { defineStore } from 'pinia';

export const usePositionStore = defineStore('positions', {
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
              const response = await fetchPositions();
              this.data.positions = response;
          } catch (error) {
              console.error("Error fetching positions:", error);
          }
        }
    }
})
