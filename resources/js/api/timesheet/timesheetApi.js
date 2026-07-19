import axios from '@axios';
import { useToast } from "vue-toast-notification";
const $toast = useToast({
    position: 'top-right'
})

export default {
    async returnTimesheet(id, remarks) {
        await axios.post(`/api/v1/timesheets/${id}/return`)
            .then(res => {
                $toast.success("Timesheet entry returned!");
            })
            .catch(err => {
                $toast.error(err?.response?.data?.message || 'Something went wrong while returning timesheets.')
                console.log(error?.response)
            })
    },

    async returnTimesheetWeekPeriod(startDate, userId, remarks) {
        await axios.post('/api/v1/timesheets/return', {
            start_date: startDate,
            user_id: userId,
            remarks: remarks
        }).then(res => {
            $toast.success(res?.data?.message)
        }) .catch(err => {
            $toast.error(err?.response?.data?.message || 'Something went wrong while returning timesheets week period.')
            console.log(error?.response)
        })
    },

    async approveTimesheetWeekPeriod(startDate = null, userId = null) {
        await axios.post('/api/v1/timesheets/approve', {
            start_date: startDate,
            user_id: userId
        }).then(res => {
            $toast.success(res?.data?.message)
        }) .catch(err => {
            $toast.error(err?.response?.data?.message || 'Something went wrong while approving timesheets week period.')
            console.log(error?.response)
        })
    },

    async fetchApprovedTimesheets() {
        let timesheets = [];

        await axios.get('/api/v1/timesheets/approved')
            .then(res => {
                timesheets = res?.data || []
            })
            .catch(err => {
                $toast.error(err?.response?.data?.message || 'Something went wrong while fetching approved timesheets.')
                console.log(error?.response)
            })

        return timesheets
    },

    async fetchPendingTimesheets() {
        let timesheets = [];

        await axios.get('/api/v1/timesheets/submitted')
            .then(res => {
                timesheets = res?.data || []
            })
            .catch(err => {
                $toast.error(err?.response?.data?.message || 'Something went wrong while fetching submitted timesheets.')
                console.log(error)
            })

        return timesheets
    },

    async fetchUnSumibttedTimesheets() {
        let timesheets = [];

        await axios.get('/api/v1/timesheets/unsubmitted')
            .then(res => {
                timesheets = res?.data || []
            })
            .catch(err => {
                $toast.error(err?.response?.data?.message || 'Something went wrong while fetching un-submitted timesheets.')
                console.log(error?.response)
            })

        return timesheets
    },

    async deleteTimesheetCategory(userId, category, startDate) {
        await axios.delete(`/api/v1/timesheets/category`, {
            data: {
                user_id: userId,
                start_date: startDate,
                category: category
            }
        })
    },

    async updateTimsheet(id, params) {
        await axios.put(`/api/v1/timesheets/${id}`, {
            break: params.break,
            category: params.category,
            comment: params.comment,
            start: params.start,
            end: params.end
        })
    },

    async addTimesheet(params) {
        await axios.post('/api/v1/timesheets', {
            date: params.date,
            category: params.category,
            comment: params.comment,
            start: params.start,
            end: params.end,
            break: params.break,
        })
    },

    async fetchPeriodDates(startDate = null, period = 0) {
        let dates = []

        await axios.get('/api/v1/timesheets/period-dates', {
            params: {
                start_date: startDate,
                period: period
            }
        }).then (response => {
            dates = response?.data
        }).catch(err => {
            $toast.error('Failed to fetch current period dates. Something went wrong.')
            console.log(err?.response)
        })

        return dates
    },

    async fetchDateCategories(date, userId) {
        let categories = []

        await axios.get('/api/v1/timesheets/date-categories', {
            params: {
                date: date,
                user_id: userId
            }
        })
            .then(response => {
                categories = response.data
            })
            .catch(err => {
                $toast.error('Failed to fetch date categories. Something went wrong.')
                console.log(err?.response)
            })

        return categories
    },

    async fetchTimesheets(params = {}) {
        let timesheets = [];

        await axios.get('/api/v1/timesheets', {
            params: params
        })
            .then(response => {
                timesheets = response?.data
            })
            .catch(err => {
                $toast.error('Failed to fetch timesheets. Something went wrong.')
                console.log(err?.response)
            })

        return timesheets;
    },

    async fetchTemplates() {
        let templates = [];

        await axios.get('/api/v1/timesheets/templates')
            .then(response => {
                templates = response?.data
            })
            .catch(err => {
                $toast.error('Failed to fetch templates. Something went wrong.')
                console.log(err?.response)
            })

        return templates
    },

    async submitTimesheets(params) {
        await axios.post('/api/v1/timesheets/submit', {
            start_date: params.start_date
        }).then(res => {
            $toast.success('Timesheets submitted!')
        }).catch(err => {
            $toast.error(err?.response?.data?.message || 'Something went wrong')
            console.log('Submit Timesheets', error?.response)
        })
    },

    async saveTemplate(params) {
        await axios.post('/api/v1/timesheets/templates', {
            name: params.name,
            data: JSON.stringify(params.timesheets)
        })
    },

    async applyTemplate(startDate, timesheets, user_id) {
        await axios.put('/api/v1/timesheets/templates/', {
            start_date: startDate,
            timesheets: timesheets,
            user_id
        })
        $toast.success('Template updated!')
    },

    async updateTemplate(id, name, timesheets) {
        await axios.put('/api/v1/timesheets/templates/' + id, {
            name: name,
            data: JSON.stringify(timesheets)
        })
        $toast.success('Template updated!')
    },

    async fetchCategories() {
        let categories = [];

         await axios.get('/api/v1/timesheets/categories')
            .then(response => {
                categories = response?.data
            })
            .catch(err => {
                $toast.error('Failed to fetch timesheet categories. Something went wrong.')
                console.log(err)
            });

        return categories
    },

    async deleteTimesheet(id) {
        await axios.delete(`/api/v1/timesheets/${id}`)
            .then(response => {
                $toast.success('Timesheet entry deleted')
            })
            .catch(err => {
                $toast.error('Failed to delete timesheet entry. Something went wrong.')
                console.log(err)
            });
    },

    async deleteTimesheets(date, userId = null) {
        await axios.delete('/api/v1/timesheets', {
            data: {
                date: date,
                user_id: userId
            }
        })
            .then(response => {
            })
            .catch(err => {
                $toast.error('Failed to delete timesheet entry. Something went wrong.')
                console.log(err)
            });
    },

    async deleteTemplate(id) {
        await axios.delete('/api/v1/timesheets/templates/' + id)
            .then(response => {
            })
            .catch(err => {
                $toast.error('Failed to delete timesheet template. Something went wrong.')
                console.log(err)
            });
    }
}
