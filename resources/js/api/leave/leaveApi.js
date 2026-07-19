import axios from '@axios';
import {useToast} from "vue-toast-notification";
const $toast = useToast({
    position: 'top-right'
})

export default {
    async fetchApprovers(id) {
        let approvers = []

        await axios.get(`/api/v1/employees/${id}/approvers`)
            .then(res => {
                approvers = res.data || []
            })
            .catch(err => {
                $toast.error(err.response.data.message || 'Something went wrong while fetching approvers')
                console.log(error.response)
            })

        return approvers
    },

    async deleteLeave(userId, batch) {
        await axios.delete('/api/v1/leaves', {
            data: {
                user_id: userId,
                batch: batch
            }
        })
            .then(res => {
                $toast.success('Leave request deleted')
            }).catch(err => {
                $toast.error(err.response.data.message || 'Something went wrong while while deleting leave.')
                console.log(error.response)
            })
    },

    async fetchCalendarData(params) {
        let calendar_data = []
        await axios.get('/api/v1/leaves/calendar', {
            params: params
        })
            .then(res => {
                calendar_data = res.data || []
            })
            .catch(err => {
                $toast.error(err.response.data.message || 'Something went wrong while fetching leave calendar data.')
                console.log(error.response)
            })

        return calendar_data;
    },

    async fetchCredits(userId) {
        let credits = []
        await axios.get('/api/v1/leaves/credits', {
            params: {
                user_id: userId
            }
        })
            .then(res => {
                credits = res.data || []
            })
            .catch(err => {
                $toast.error(err.response.data.message || 'Something went wrong while fetching leave credits.')
                console.log(error.response)
            })

        return credits;
    },

    async fetchLeaveDetails(userId, batch) {
        let leaves = []
        await axios.get('/api/v1/leaves/details', {
            params: {
                user_id: userId,
                batch: batch
            }
        })
            .then(res => {
                leaves = res.data || []
            })
            .catch(err => {
                $toast.error(err.response.data.message || 'Something went wrong while fetching leave details.')
                console.log(error.response)
            })

        return leaves;
    },

    async fetchLeaves(params = null) {
        let leaves = []
        await axios.get('/api/v1/leaves', {
            params: params
        })
            .then(res => {
                leaves = res.data || []
            })
            .catch(err => {
                $toast.error(err.response.data.message || 'Something went wrong while fetching leaves.')
                console.log(error.response)
            })

        return leaves;
    },

    async fetchLeaveTypes(userId) {
        let types = []
         await axios.get('/api/v1/leaves/types', {
             params: {
                 user_id: userId
             }
         })
            .then(res => {
                types = res.data || []
            })
            .catch(err => {
                $toast.error(err.response.data.message || 'Something went wrong while fetching leave types.')
                console.log(error.response)
            })

        return types;
    },

    async fetchLeaveStatuses() {
        let types = []
        await axios.get('/api/v1/leaves/statuses')
            .then(res => {
                types = res.data || []
            })
            .catch(err => {
                $toast.error(err.response.data.message || 'Something went wrong while fetching leave statuses.')
                console.log(error.response)
            })

        return types;
    },

    async submitLeave(data) {
        await axios.post('/api/v1/leaves', data)
            .then(res => {
                $toast.success("You have successfully submitted the leave request")
            })
            .catch(err => {
                $toast.error(err.response.data.message || 'Something went wrong while submitting leave request.')
                console.log(error.response)
            })
    },

    async updateLeave(data) {
        await axios.put('/api/v1/leaves', data)
            .then(res => {
                $toast.success("You have successfully updated the leave request")
            })
            .catch(err => {
                $toast.error(err.response.data.message || 'Something went wrong while updating leave request.')
                console.log(error.response)
            })
    },

    async leaveApprove(data) {
        await axios.post('/api/v1/leaves/approve', data)
            .then(res => {
                // $toast.success("Leave request approved!")
            })
            .catch(err => {
                $toast.error(err.response.data.message || 'Something went wrong while approving leave request.')
                console.log(error.response)
            })
    },

    async declineLeave(data) {
        await axios.post('/api/v1/leaves/decline', data)
            .then(res => {
                $toast.warning("Leave request declined")
            })
            .catch(err => {
                $toast.error(err.response.data.message || 'Something went wrong while declining leave request.')
                console.log(error.response)
            })
    },

    async cancelLeave(userId, batch) {
        await axios.post('/api/v1/leaves/cancel', {
            user_id: userId,
            batch: batch
        })
            .then(res => {
                $toast.success("Leave request is cancelled")
            })
            .catch(err => {
                $toast.error(err.response.data.message || 'Something went wrong while canceling the leave request.')
                console.log(error.response)
            })
    },
}
