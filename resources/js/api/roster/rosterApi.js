import axios from '@axios'
import {useToast} from "vue-toast-notification";
const $toast = useToast({
    position: 'top-right'
})

export default {
    async deleteTemplate(id) {
        await axios.delete('/api/v1/rosters/templates', {
            data: {
                template_id: id
            }
        })
    },

    async loadTemplates(locationId) {
        let templates = []

        await axios.get('/api/v1/rosters/templates', {
            params: {
                location_id: locationId
            }
        })
            .then(res => {
                templates = res.data || []
            })
            .catch(err => {
                $toast.error(err.response.data.message || 'Something went wrong while fetching roster templates.')
                console.log(error.response)
            })

        return templates
    },

    async saveTemplate(name, locationId, shifts) {
        await axios.post('/api/v1/rosters/templates', {
            name: name,
            location_id: locationId,
            shifts: shifts
        })
    },

    async addShift(params) {
        await axios.post('/api/v1/rosters/shifts', params)
    },

    async updateShift(id, params) {
        await axios.put(`/api/v1/rosters/shifts/${id}`, params)
    },

    async removeRosterEmployee(locationId, userId) {
        await axios.delete('/api/v1/rosters/employees', {
            data: {
                location_id: locationId,
                user_id: userId
            }
        })
    },

    async addRosterEmployee(locationId, userId) {
        await axios.post('/api/v1/rosters/employees', {
            location_id: locationId,
            user_id: userId
        })
    },

    async fetchRosters(params) {
        let rosters = []

        await axios.get('/api/v1/rosters', {
            params: params
        })
            .then(res => {
                rosters = res.data || []
            })
            .catch(err => {
                $toast.error(err.response.data.message || 'Something went wrong while fetching rosters.')
                console.log(error.response)
            })

        return rosters
    },

    async fetchShiftTypes() {
        let shiftTypes = []

        await axios.get('/api/v1/rosters/shift-types')
            .then(res => {
                shiftTypes = res.data || []
            })
            .catch(err => {
                $toast.error(err.response.data.message || 'Something went wrong while fetching shift types.')
                console.log(error.response)
            })

        return shiftTypes
    },

    async fetchShifts(rosterId) {
        let shifts = []

        await axios.get(`/api/v1/rosters/${rosterId}/shifts`, {
            params: {
                location_id: locationId
            }
        })
            .then(res => {
                shifts = res.data || []
            })
            .catch(err => {
                $toast.error(err.response.data.message || 'Something went wrong while fetching shifts.')
                console.log(error.response)
            })

        return shifts
    }
}
