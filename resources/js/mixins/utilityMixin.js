import moment from "moment/moment";
import {ProfilePlaceHolder} from "@/plugins/profilePlaceHolder";

export default {
    computed: {
        formattedDates() {
            return this.weekPeriodDates.map(date => date.format('YYYY-MM-DD'))
        },

        weekPeriodDates() {
            let period = this.period * 7
            let dates = [];
            for (let i = 0; i < 7; i++) {
                dates.push(moment().startOf('week').add((3 + period + i), 'days'))
            }

            console.log(dates)

            return dates
        },
    },

    methods: {
        getEmployeeInitials(employee) {
            if (employee?.first_name && employee?.last_name) {
                return employee.first_name.charAt(0).toUpperCase() + employee.last_name.charAt(0).toUpperCase()
            }

            return '[ERROR]'
        },

        getEmployeeProfileImage(employee) {
            if (employee?.profile_image) {
                return employee.profile_image
            }

            return ProfilePlaceHolder(this.getEmployeeInitials(employee))
        },

        getHumanReadableTime(datetime) {
            let diffDays = moment().diff(moment(datetime), 'days')

            if (diffDays > 3) {
                return moment(datetime).format('DD MMM, YYYY')
            }

            return moment(datetime).fromNow()
        },

        formatDate(date, format) {
            return moment(date).format(format)
        }
    }
}
