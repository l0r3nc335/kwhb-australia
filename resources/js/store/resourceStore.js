import {defineStore} from "pinia";

export const useResourceStore = defineStore('resource', {
    state: () => {
        return {
            employees: [],
            roles: [],
            divisions: [],
            locations: [],
            job_titles: []
        }
    },

    persist: true,

    actions: {
        setJobTitles(data) {
            this.job_titles = data
        },

        setEmployees(data) {
            this.employees = data
        },

        setRoles(data) {
            this.roles = data
        },

        setDivisions(data) {
            this.divisions = data
        },

        setLocations(data) {
            this.locations = data
        },
    }
})
