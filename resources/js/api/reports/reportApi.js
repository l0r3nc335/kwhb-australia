import axios from '@axios'
import {useToast} from "vue-toast-notification";
const $toast = useToast({
    position: 'top-right'
})

export default {
    async fetchReports(dateRangeType, dateRange = null) {
        let reports = []

        await axios.get('/api/v1/reports', {
            params: {
                date_range_type: dateRangeType,
                date_range: dateRange
            }
        })
            .then(res => {
                reports = res.data || []
            })
            .catch(err => {
                $toast.error(err.response.data.message || 'Something went wrong while fetching reports.')
                console.log(error.response)
            })

        return reports
    },

    async fetchReportGraphs(dateRangeType = 'weekly', dateRangeIndex= 0, start = null, end = null) {
        let reports = []

        await axios.get('/api/v1/reports/graph', {
            params: {
                date_range_type: dateRangeType,
                date_range_index: dateRangeIndex,
                start: start,
                end: end
            }
        })
            .then(res => {
                reports = res.data || []
            })
            .catch(err => {
                $toast.error(err.response.data.message || 'Something went wrong while fetching reports.')
                console.log(error.response)
            })

        return reports
    },
}
