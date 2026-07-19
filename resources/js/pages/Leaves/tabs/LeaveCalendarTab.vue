<template>
    <div class="tw-flex tw-flex-col py-2">
        <div class="tw-flex tw-justify-between">
            <LeaveManagementFilters :show-date-filter="false"
                                    :show-type-filter="false"
                                    :show-month-filter="true"
                                    :show-date-group-filter="true"
                                    :show-period-navigation-filter="true"
                                    :filter-month="filters.month"
                                    @filter-period-add="dateIndex++"
                                    @filter-period-subtract="dateIndex--"
                                    @query="filterName"
                                    @filter-type="filterType"
                                    @filter-division="filterDivision"
                                    @filter-status="filterStatus"
                                    @filter-location="filterLocation"
                                    @filter-date-group="filterDateGroup"
                                    @filter-month="filterMonth">
            </LeaveManagementFilters>
            <CreateLeaveButton @reload-leaves="loadLeaveCalendar"></CreateLeaveButton>
        </div>
        <div class="tw-flex tw-justify-end tw-mt-6 tw-mb-2">
            {{currentDateRange}}
        </div>
        <div class="tw-flex ">
            <div class="leave-calendar-employees-wrapper">
                <table class="leave-calendar-employee">
                    <thead>
                        <tr>
                            <th>
                                <div class="header-pad"></div>
                                <div class="header"></div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="c in calendar">
                            <td class="employee-name tw-px-6" style="padding: 10px 20px">{{c.name}}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="leave-calendar-wrapper tw-flex-1">
                <table class="leave-calendar">
                    <thead>
                        <tr>
                            <th v-for="calendarDate in calendarDates">
                                <div class="tw-flex tw-flex-col">
                                    <div class="header-pad"></div>
                                    <div class="header" :class="[isWeekend(calendarDate) ? 'weekend-bg' : '']">
                                        <div class="day-name">
                                            {{getDayName(calendarDate)}}
                                        </div>
                                        <div class="tw-flex tw-justify-center">
                                            <div class="day-date" :class="[isCurrentDate(calendarDate) ? 'current' : '']">
                                                {{getDayDate(calendarDate)}}
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="employee in calendar">
                            <td v-for="date in calendarDates" class="tw-overflow-hidden hover:tw-overflow-visible" :class="[isWeekend(date) ? 'weekend-bg' : '']" >
                                <div v-for="leave in employee.leaves" class="tw-cursor-pointer" @click="loadLeave(leave)">
                                    <div v-if="isOnLeave(leave, date)" class="leave-plot" :class="[getPlotColor(leave.leave_type_name)]">
                                    </div>
                                    <div v-if="isFirstLeaveDate(leave, date)" class="first-leave-plot-bar" :class="[getPlotColor(leave.leave_type_name)]"></div>
                                    <div v-if="isLastLeaveDate(leave, date)" class="last-leave-plot-bar" :class="[getPlotColor(leave.leave_type_name)]"></div>
                                    <div v-if="isFirstLeaveDate(leave, date)" class=" tw-px-1 leave-plot-type-name tw-relative" style="overflow: hidden">
                                        {{leave.leave_type_name}}
                                    </div>
                                    <div v-if="isFirstLeaveDate(leave, date)" class="leave-plot-hours">
                                        {{getHourString(employee.leaves)}}
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="tw-flex tw-bg-gray-100 tw-py-4 tw-text-[.65rem]" style="height: 110px;padding-left:260px">
            <div class="tw-grid tw-grid-cols-3" style="width: 500px">
                <div v-for="activeCategory in uniqueActiveCategories" class="tw-flex" style="height: max-content">
                    <div class="color" :class="[getPlotColor(activeCategory)]"></div>
                    <div class="color-leave-name">{{activeCategory}}</div>
                </div>
            </div>
        </div>
        <VDialog v-model="showLeaveForm" width="900" class="leave-dialog-wrapper">
            <DialogCloseBtn @click="showLeaveForm = false" />
            <LeaveForm :selected-leave="selectedLeave"
                       :edit-mode="false"
                       :view-mode="true"
                       @close="showLeaveForm = false">
            </LeaveForm>
        </VDialog>
    </div>
</template>

<script>
    import moment from "moment";
    import leaveApi from "@/api/leave/leaveApi";
    import LeaveManagementFilters from "@/pages/Leaves/components/LeaveManagementFilters.vue";
    import CreateLeaveButton from "@/pages/Leaves/components/CreateLeaveButton.vue";
    import LeaveForm from "@/pages/Leaves/forms/LeaveForm.vue";
    import _ from 'lodash'

    export default {
        components: {
            CreateLeaveButton,
            LeaveManagementFilters,
            LeaveForm
        },

        data() {
            return {
                calendar: [],
                calendarDates: [],
                dateIndex: 0,
                dateGroup: 'Monthly',
                filters: {
                    query: null,
                    month: null,
                    division: null,
                    location: null,
                    status: null// Approved
                },
                selectedLeave: null,
                showLeaveForm: false
            }
        },

        computed: {
            currentDateRange() {
                if (this.calendarDates.length > 0) {
                    let firstDate = this.calendarDates[0]
                    let lastDate = this.calendarDates[this.calendarDates.length - 1]
                    let mfd = moment(firstDate)
                    let mld = moment(lastDate)
                    let dateRangeString = `${mfd.date()} - ${mld.date()} ${mfd.format('MMM')}, ${mfd.year()}`

                    if (mfd.format('MMM') != mld.format('MMM')) {
                        return `${mfd.date()} ${mfd.format('MMM')} - ${mld.date()} ${mld.format('MMM')}, ${mld.year()}`
                    }

                    return dateRangeString
                }

                return '[error]'
            },

            uniqueActiveCategories() {
                return _.uniq(this.activeCategories)
            },

            activeCategories() {
                let categories = []
                for (let i in this.calendar) {
                    let leaves = this.calendar[i].leaves
                    for (let j in leaves) {
                        categories.push(leaves[j].leave_type_name)
                    }
                }

                return [
                    'Personal Leave',
                    'Annual Leave',
                    'Leave Without Pay (LWOP)',
                    'Special/Sorry Leave'
                ]
            }
        },

        mounted() {
            this.loadLeaveCalendar()
            this.getCalendarDates()
        },

        methods: {
            async loadLeave(leave) {
                let response = await leaveApi.fetchLeaves({
                    user_id:  leave.user_id,
                    batch: leave.batch
                })

                let selectedLeave = response?.leaves?.data || []
                if (selectedLeave && selectedLeave.length > 0) {
                    this.selectedLeave = selectedLeave[0]
                    this.showLeaveForm = true
                }
            },

            getPlotColor(leave_type_name, shade = '600') {
                // this.activeCategories.push(leave_type_name)
                switch (leave_type_name) {
                    case 'Leave Without Pay (LWOP)':
                        return `tw-bg-pink-600`
                    case 'Annual Leave':
                        return `tw-bg-orange-600`
                    case 'Special/Sorry Leave':
                        return `tw-bg-blue-600`
                    case 'Defence Reserve Leave':
                        return `tw-bg-sky-600`
                    case 'Personal Leave':
                        return `tw-bg-purple-600`
                    case 'FOIL':
                        return `tw-bg-purple-600`
                    case 'Parental Leave':
                        return `tw-bg-red-600`
                    case 'Long Service Leave':
                        return `tw-bg-indigo-600`
                    case 'Jury Service Leave':
                        return `tw-bg-green-600`
                    case 'Compassionate Leave':
                        return `tw-bg-yellow-400`
                    case 'Ceremonial Leave':
                        return `tw-bg-emerald-400`
                    default:
                        return `tw-bg-gray-400`
                }
            },

            getHourString(leaves) {
                let total = 0;

                for(let i in leaves) {
                    total += leaves[i].total
                }

                return total + ' hours'
            },

            isFirstLeaveDate(employeeLeave, date) {
                return employeeLeave.start_date === date
            },

            isLastLeaveDate(employeeLeave, date) {
                return employeeLeave.end_date === date
            },

            isOnLeave(employeeLeave, date) {
                let start = moment(employeeLeave.start_date)
                let end = moment(employeeLeave.end_date)
                let current = moment(date);

                return current.isSameOrAfter(start) && current.isSameOrBefore(end)
            },

            isWeekend(date) {
                let d = moment(date).get('day')
                return d === 0 || d === 6
            },

            isCurrentDate(date) {
                return moment().format('YYYY-MM-DD') === date
            },

            getDayName(date) {
                return moment(date).format('ddd')
            },

            getDayDate(date) {
                return moment(date).format('DD')
            },

            getCalendarDates() {
                this.calendarDates = []
                if (this.dateGroup === 'Weekly') {
                    this.calculateWeeklyDates()
                } else if (this.dateGroup === 'Fortnightly') {
                    this.calculateFortnightlyDates()
                } else if (this.dateGroup === 'Monthly') {
                    this.calculateMonthlyDates()
                }
            },

            calculateWeeklyDates() {
                let dateIndex = this.dateIndex * 7 + 3;
                for(let i = 0; i < 7; i++) {
                    this.calendarDates.push(moment().startOf('week').add(dateIndex + i, 'days').format('YYYY-MM-DD'))
                }
            },

            calculateFortnightlyDates() {
                let dateIndex = this.dateIndex * 14 + 1;
                for(let i = 0; i < 14; i++) {
                    this.calendarDates.push(moment().startOf('month').add(dateIndex + i, 'days').format('YYYY-MM-DD'))
                }
            },

            calculateMonthlyDates() {
                let month = (Math.abs(this.dateIndex) > 12 ? this.dateIndex & 12 : this.dateIndex) + parseInt(moment().format('M'))

                let calendarMonth = moment().set('month', month - 1)
                let endDate = calendarMonth.clone().endOf('month').format('D')

                for (let i=0;i<endDate;i++) {
                    this.calendarDates.push(calendarMonth.clone().startOf('month').add('days', i).format('YYYY-MM-DD'))
                }
            },

            async loadLeaveCalendar() {
                this.calendar = await leaveApi.fetchCalendarData(this.filters)
            },

            filterName(name) {
                this.filters.query = name
                this.loadLeaveCalendar()
            },

            filterDivision(division) {
                this.filters.division = division?.id
                this.loadLeaveCalendar()
            },

            filterStatus(status) {
                this.filters.status = status?.id
                this.loadLeaveCalendar()
            },

            filterLocation(location) {
                this.filters.location = location?.id
                this.loadLeaveCalendar()
            },

            filterType() {

            },

            filterDateGroup(dateGroup) {
                this.dateGroup = dateGroup
            },

            filterMonth(month) {
                this.filters.month = month.id
                this.loadLeaveCalendar()
            },
        },

        watch: {
            dateIndex() {
                this.getCalendarDates()

                if (this.calendarDates.length > 0) {
                    this.filters.month = moment(this.calendarDates[0]).month() + 1
                }
            },

            dateGroup() {
                this.getCalendarDates()
            }
        }
    }
</script>

<style scoped>
    .leave-calendar {
        @apply tw-w-full
    }

    .leave-calendar th {
        vertical-align: top;
        font-weight: normal;
        text-transform: uppercase;
        font-family: system-ui;
        font-size: .8rem
    }

    td {
        padding: 20px 0;
        text-align: left;
        height: 64px;
        position: relative;
    }

    th, td {
        @apply tw-border tw-border-gray-100
    }

    .leave-calendar-employee {
        width: 250px;
    }



    .header {
        padding: 10px;
        text-align: center;
        height:68px;
    }

    .header .day-name {
        font-size: .9rem;
        font-weight: bold;
    }

    .header .day-date {
        padding: 4px 6px;
    }

    .day-date.current {
        width: fit-content;
        @apply tw-bg-orange-500 tw-text-white tw-rounded-full
    }

    .weekend-bg {
        @apply tw-bg-gray-50
    }

    .header-pad {
        height: 22px;
        background-color: #e7eaf4;
    }

    .employee-name {
        @apply tw-font-bold tw-text-blue-900
    }

    .leave-calendar-wrapper {
        overflow: auto;
    }

    .leave-plot {
        position: absolute;
        top: 4px;
        width: 100%;
        height: 55px;
        opacity: .03;
    }

    .first-leave-plot-bar {
        position: absolute;
        width: 3px;
        height: 55px;
        top: 4px;
        left: 0;
        opacity: .7;
    }

    .last-leave-plot-bar {
        position: absolute;
        width: 3px;
        height: 55px;
        top: 4px;
        right: 0;
        opacity: .7;
    }

    .leave-plot-type-name {
        font-size: .65rem;
        white-space: nowrap;
        top: 16px;
        left: 12px;
        color: black;
        z-index: 9;
        position: absolute;
    }

    .leave-plot-hours {
        font-size: .65rem;
        white-space: nowrap;
        top: 34px;
        left: 12px;
        color: black;
        z-index: 9;
        position: absolute;
    }

    .color {
        height:6px;
        width: 6px;
        @apply tw-rounded-full tw-mr-2 tw-mt-1
    }
</style>
