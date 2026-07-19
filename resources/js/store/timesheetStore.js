import {defineStore} from "pinia";

export const useTimesheetStore = defineStore('timesheet', {
    state: () => {
        return {
            timesheets: [],
            templates: [],
            categories: [],
            pending: [],
            unsubmitted: [],
            approved: [],
        }
    },

    persist: true,

    actions: {
        setCategories(data) {
            this.categories = data
        },

        setTemplates(data) {
            this.templates = data
        },

        setTimesheets(data) {
            this.timesheets = data
        },

        setPending(data) {
            this.pending = data
        },

        setUnsubmitted(data) {
            this.unsubmitted = data
        },

        setApproved(data) {
            this.approved = data
        },
    }
})

