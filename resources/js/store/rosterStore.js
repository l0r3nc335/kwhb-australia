import {defineStore} from "pinia";

export const useRosterStore = defineStore('timesheet', {
    state: () => {
        return {
            rosters: [],
            shiftTypes: [],
            filters: {
                search: null,
                location: null,
                shiftType: null,
                roleType: null,
                staff: null,
                dateRangeType: null,
                dateRange: {
                    start: null,
                    end: null
                }
            }
        }
    },

    persist: true,

    actions: {
        setRosters(data) {
            this.rosters = data
        },

        setLocationFilter(data) {
            this.filters.location = data
        },

        setShiftTypes(data) {
            this.shiftTypes = data
        }
    }
})
