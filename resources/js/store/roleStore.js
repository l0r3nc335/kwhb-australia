/*

space
 */
import { fetchRoles } from '@/api/role/role';
import { defineStore } from 'pinia';

export const useRoleStore = defineStore('roles', {
    state: () => ({
      data:{
        roles:[],
        routeTab: 'Roles',
      }
    }),
    persist: true,
    getters: {
      activeTab: state => state.data.routeTab,
    },
    actions: {
        async setActiveTab(tab) {
          this.data.routeTab = tab
        },
        addRoles(role) {
            this.data.roles.push(role)
        },
        async setRoles() {
          try {
              const response = await fetchRoles();
              this.data.roles = response;
          } catch (error) {
              console.error("Error fetching roles:", error);
          }
        }
    }
})
