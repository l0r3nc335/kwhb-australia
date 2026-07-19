import {defineStore} from "pinia";

export const useAuthUserStore = defineStore('resource', {
    state: () => {
        return {
            authUser: null,
            employeeData: null
        }
    },

    persist: true,

    actions: {
        setAuthUser(data) {
            this.authUser = data
        },

        setEmployeeData(data) {
            this.employeeData = data
        }
    }
})
