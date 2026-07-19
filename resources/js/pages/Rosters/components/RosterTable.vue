<template>
    <template v-if="shifts.length == 0">
        <div class="tw-py-2 tw-shadow tw-p-4" style="min-height: 400px">
            No shifts added yet.
        </div>
    </template>
    <div v-else class="tw-flex tw-flex-col roster-calendar-wrapper"  style="overflow: auto">
        <div class="tw-flex">
            <div class="tw-flex tw-border-x">
                <table>
                    <thead></thead>
                    <tbody>
                        <tr class="tw-flex tw-flex-col tw-justify-start">
                            <td style="padding: 0px; height: 98px;">
                                <div class="header-pad tw-font-bold tw-px-2 tw-py-1"></div>
                            </td>
                        </tr>
                        <template v-for="shift in shifts">
                            <tr v-for="(data, i) in shift.data">
                                <td style="padding: 0">
                                    <div class="tw-flex tw-flex-col tw-h-full" :style="{minHeight: (i === 0 ? '110px' : '80px')}">
                                        <div v-if="i === 0" class="header-pad tw-py-1 tw-px-2 tw-font-bold">
                                            {{getShiftTypeName(shift.type)}}
                                        </div>
                                        <div class="tw-flex tw-flex-col justify-center tw-flex-1">
                                            <div class="tw-flex tw-justify-between ">
                                                <div class="tw-flex   tw-px-4 tw-flex-1">
                                                    <div class="tw-flex tw-mr-3 tw-flex-col tw-justify-center">
                                                        <VAvatar :image="getEmployeeProfileImage(data.user.employee)"></VAvatar>
                                                    </div>
                                                    <div class="tw-flex tw-flex-col tw-justify-center">
                                                        {{data.user.employee.first_name}} {{data.user.employee.last_name}}
                                                    </div>
                                                </div>
                                                <div>
                                                    <IconBtn>
                                                        <VIcon icon="tabler-dots-vertical" />
                                                        <VMenu
                                                            activator="parent"
                                                        >
                                                            <VList>
                                                                <VListItem class="tw-cursor-pointer hover:tw-bg-gray-100">Show Leave Booking</VListItem>
                                                                <VListItem class="tw-cursor-pointer hover:tw-bg-gray-100">Show Contract End</VListItem>
                                                                <VListItem class="tw-cursor-pointer hover:tw-bg-gray-100">Show Availability</VListItem>
                                                                <VListItem class="tw-cursor-pointer hover:tw-bg-gray-100" @click="$emit('edit-shift', data)">Edit shift</VListItem>
                                                                <VListItem class="tw-cursor-pointer hover:tw-bg-red-100" @click="selectedEmployee=data.user.employee;showRemoveRosterDialog = true">Remove From Roster</VListItem>
                                                            </VList>
                                                        </VMenu>
                                                    </IconBtn>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <div class="tw-flex tw-flex-1 tw-w-full" id="calendar-width-ref">
                <table class="roster-calendar" style="width: 100%">
                    <thead>
                        <tr>
                            <th v-for="calendarDate in calendarDates">
                                <div class="tw-flex tw-flex-col">
                                    <div class="header-pad"></div>
                                    <div class="header" :class="[isWeekend(calendarDate) ? 'weekend-bg' : '']">
                                        <template v-if="dateGroup != 'Daily'">
                                            <div class="day-name" :class="[isCurrentDate(calendarDate) && templatePreview ? 'current' : '']">
                                                {{getDayName(calendarDate)}}
                                            </div>
                                            <div class="tw-flex tw-justify-center" v-if="!templatePreview">
                                                <div class="day-date" :class="[isCurrentDate(calendarDate) ? 'current' : '']">
                                                    {{getDayDate(calendarDate)}}
                                                </div>
                                            </div>
                                        </template>
                                        <template v-else>
                                            <div class="day-name tw-flex tw-flex-col tw-justify-center tw-h-full">
                                                {{getDayTime(calendarDate)}}
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-for="shift in shifts">
                            <tr v-for="(data, i) in shift.data">
                                <td v-for="(calendarDate, j) in calendarDates" class="" style="padding:0">
                                    <div class="tw-flex tw-flex-col tw-h-full" :style="{minHeight: (i === 0 ? '110px' : '80px')}">
                                        <div v-if="i === 0" class="header-pad"></div>
                                        <div class="tw-flex tw-justify-between tw-flex-1 tw-relative " >
                                            <div v-if="shiftInCellDateTime(data, calendarDate)" @click="$emit('edit-shift', data)" class="cursor-pointer">
                                                <div class="shift-ribbon " :class="[`tw-bg-${getTypeColor(data.type)}`]" style="opacity: .10"></div>
                                                <div v-if="isFirstDataInCellDateTime(data,j)" class="shift-ribbon" :class="[`tw-bg-${getTypeColor(data.type)}`]" style="position: absolute;top:0;left:0; width: 3px"></div>
                                                <div v-if="isLastDataInCellDateTime(data,j)" class="shift-ribbon" :class="[`tw-bg-${getTypeColor(data.type)}`]" style="position: absolute;top:0;right:0; width: 3px"></div>
                                                <div class="shift-ribbon hover:tw-shadow-lg tw-px-3 tw-py-2" v-if="isFirstDataInCellDateTime(data, j)" style="font-size: .7rem;z-index:9;white-space:nowrap">
                                                    {{getShiftTimeRangeString(data)}}
                                                </div>
                                            </div>
                                            <div v-for="slice in cellSlice" class="tw-border-r tw-border-gray-100">
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </template>
<!--                        <tr v-for="(user, i) in noShifts">-->
<!--                            <td v-for="(calendarDate, j) in calendarDates" class="" style="padding:0">-->
<!--                                <div class="tw-flex tw-flex-col tw-h-full" :style="{minHeight: (i === 0 ? '110px' : '80px')}">-->
<!--                                    <div v-if="i === 0" class="header-pad"></div>-->
<!--                                    <div class="tw-flex tw-justify-between tw-flex-1 tw-relative">-->

<!--                                        <div v-for="slice in cellSlice" class="tw-border-r tw-border-gray-100">-->
<!--                                        </div>-->
<!--                                    </div>-->
<!--                                </div>-->
<!--                            </td>-->
<!--                        </tr>-->
                    </tbody>
                </table>
            </div>
        </div>
        <VDialog v-model="showRemoveRosterDialog" width="400px">
            <div class="tw-flex tw-flex-col tw-p-4 tw-bg-white tw-shadow-lg">
                <div class="tw-p-4 tw-text-[1.2rem] ">
                    Remove <b class="tw-text-red-600">{{selectedEmployee?.first_name}} {{selectedEmployee?.last_name}}</b> from <b>{{location?.name}}</b> roster?
                </div>
                <div class="tw-flex tw-justify-end">
                    <VBtn size="small" color="secondary" class="tw-mr-2" @click="selectedEmployee=null;showRemoveRosterDialog=false">Cancel</VBtn>
                    <VBtn size="small" @click="showRemoveRosterDialog=false;$emit('remove-employee', selectedEmployee)">Remove</VBtn>
                </div>
            </div>
        </VDialog>
    </div>
</template>

<script>
    import moment from "moment/moment";

    export default {
        props: {
            data: {
                required: false
            },

            dateGroup: {
                type: String,
                default: 'Weekly'
            },

            shifts: {
                required: true
            },

            noShifts: {
                required: false
            },

            dateRangeIndex: {
                type: Number,
                default: 0
            },

            location: {
                required: false
            },

            templatePreview: {
                type: Boolean,
                default: false
            }
        },

        data() {
            return {
                showRemoveRosterDialog: false,
                selectedEmployee: null,
                calendarDates: [],
                dateIndex: 0,
            }
        },

        computed: {
            cellSlice() {
                switch (this.dateGroup) {
                    case 'Daily':
                        return 4
                }

                return 1
            },

            employeeWithoutShifts() {
                let employeesWithShifts = []
                let shifts = this.shifts || []

                for (let i in shifts) {
                    let shift = shifts[i]
                    employeesWithShifts.push(shift.user_id)
                }

                return this.data.filter(s => !employeesWithShifts.includes(s.id))
            },
        },

        mounted() {
            this.getCalendarDates()
        },

        methods: {
            getShiftTimeRangeString(shift) {
                return moment(moment().format(`YYYY-MM-DD ${shift.start}`)).format('h:mma') + ' - ' + moment(moment().format(`YYYY-MM-DD ${shift.end}`)).format('h:mma')
            },

            isFirstDataInCellDateTime(shift, calendarIndex) {
                if (this.calendarDates[calendarIndex - 1]) {
                    return !this.shiftInCellDateTime(shift, this.calendarDates[calendarIndex - 1])
                }

                return false
            },

            isLastDataInCellDateTime(shift, calendarIndex) {
                if (this.calendarDates[calendarIndex + 1]) {
                    return !this.shiftInCellDateTime(shift, this.calendarDates[calendarIndex + 1])
                }

                return false
            },

            getTypeColor(type) {
                let color = 'gray'

                switch (type) {
                    case 1: // Agency RAN
                        color = 'amber'
                        break;
                    case 2: // Permanent RAN
                        color = 'yellow'
                        break;
                    case 3: // First Responder
                        color = 'red'
                        break;
                    case 4: // Second Responder
                        color = 'orange'
                        break;
                    case 5: // GP
                        color = 'blue'
                        break;
                    case 6: // Program Staff
                        color = 'stone'
                        break;
                    case 7: // PHSCC
                        color = 'pink'
                        break;
                    case 8: // On Call
                        color = 'green'
                        break;
                    case 9: // Leave
                        color = 'fuchsia'
                        break;
                }

                return `${color}-600`
            },

            shiftInCellDateTime(shift, calendarDate) {
                let startDateTime, endDateTime;
                let calendarDateTime = moment(moment().format(calendarDate))
                let occursOnDays = JSON.parse(shift.occurs_on)
                let repeatCondition = false;

                switch (this.dateGroup) {
                    case 'Daily':
                        startDateTime = moment(moment().format(`${calendarDateTime.format('YYYY-MM-DD')} ${shift.start}`)).startOf('hour')
                        endDateTime = moment(moment().format(`${calendarDateTime.format('YYYY-MM-DD')} ${shift.end}`)).endOf('hour')
                        break;
                    default:
                        startDateTime = moment(moment().format(shift.start_date))
                        endDateTime = moment(moment().format(shift.end_date))
                        repeatCondition = (shift.is_repeat && calendarDateTime.isSameOrAfter(startDateTime));
                        break;
                }

                return (
                        (
                            calendarDateTime.isSameOrAfter(startDateTime)
                            && calendarDateTime.isSameOrBefore(endDateTime)
                        ) || repeatCondition
                    )
                    && occursOnDays.includes(moment(calendarDate).format('dddd'))
            },

            getShiftTypeName(type) {
                switch (type) {
                    case 1:
                        return 'Agency RANs'
                    case 2:
                        return 'Permanent RANs'
                    case 3:
                        return 'First Responder'
                    case 4:
                        return 'Second Responder'
                    case 5:
                        return 'GP'
                    case 6:
                        return 'Program Staff'
                    case 7:
                        return 'PHSCC'
                    case 8:
                        return 'On Call'
                    case 9:
                        return 'Leave'
                    default:
                        return 'N/A'
                }
            },

            getDayTime(date) {
                return moment(date).format('ha')
            },

            getDayDate(date) {
                return moment(date).format('DD')
            },

            isCurrentDate(date) {
                return moment().format('YYYY-MM-DD') === date
            },

            getDayName(date) {
                return moment(date).format('ddd')
            },

            isWeekend(date) {
                let d = moment(date).get('day')
                return d === 0 || d === 6
            },

            getCalendarDates() {
                this.calendarDates = []
                if (this.dateGroup === 'Weekly') {
                    this.calculateWeeklyDates()
                } else if (this.dateGroup === 'Fortnightly') {
                    this.calculateFortnightlyDates()
                } else if (this.dateGroup === 'Monthly') {
                    this.calculateMonthlyDates()
                } else if (this.dateGroup === 'Daily') {
                    this.calculateDaily()
                }
            },

            calculateDaily() {
                for (let i = 0; i < 24; i++) {
                    this.calendarDates.push(moment().add(this.dateRangeIndex, 'days').startOf('day').add(i, 'hours').format('YYYY-MM-DD HH:mm:ss'))
                }
            },

            calculateWeeklyDates() {
                let dateIndex = this.dateRangeIndex * 7;
                for(let i = 0; i < 7; i++) {
                    this.calendarDates.push(moment().startOf('week').add(dateIndex + i, 'days').format('YYYY-MM-DD'))
                }
            },

            calculateFortnightlyDates() {
                let dateIndex = this.dateRangeIndex * 14 ;
                for(let i = 0; i < 14; i++) {
                    this.calendarDates.push(moment().startOf('month').add(dateIndex + i, 'days').format('YYYY-MM-DD'))
                }
            },

            calculateMonthlyDates() {
                let month = (Math.abs(this.dateRangeIndex) > 12 ? this.dateRangeIndex & 12 : this.dateRangeIndex) + parseInt(moment().format('M'))

                let calendarMonth = moment().set('month', month - 1)
                let endDate = calendarMonth.clone().endOf('month').format('D')

                for (let i=0;i<endDate;i++) {
                    this.calendarDates.push(calendarMonth.clone().startOf('month').add('days', i).format('YYYY-MM-DD'))
                }
            },
        },

        watch: {
            dateGroup() {
                this.getCalendarDates()
            },

            dateRangeIndex() {
                this.getCalendarDates()
            }
        }
    }
</script>

<style lang="scss">
    .shift-ribbon {
        position: absolute;
        margin: auto;
        height: 46px;
        top: 0;
        bottom: 0;

        @apply tw-flex tw-w-full
    }

    .roster-table {
        th {
            height: 20px;
            @apply tw-bg-gray-100 tw-w-full tw-flex
        }
    }

    .roster-calendar {
        @apply tw-w-full
    }

    .roster-calendar th {
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

    th .header {
        @apply tw-border tw-border-gray-100
    }

    td {
        @apply tw-border-y tw-border-gray-100
    }

    .roster-calendar-employee {
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

    .day-name.current {
        width: fit-content;
        @apply tw-bg-orange-500 tw-text-white tw-rounded-full tw-px-2 tw-flex tw-m-auto
    }

    .weekend-bg {
        @apply tw-bg-gray-50
    }

    .header-pad {
        height: 30px;
        background-color: #e7eaf4;
    }

    .employee-name {
        @apply tw-font-bold tw-text-blue-900
    }

    .roster-calendar-wrapper {
        overflow: auto;
    }

    .roster-plot {
        position: absolute;
        top: 4px;
        width: 100%;
        height: 55px;
        opacity: .03;
    }

    .first-roster-plot-bar {
        position: absolute;
        width: 3px;
        height: 55px;
        top: 4px;
        left: 0;
        opacity: .7;
    }

    .last-roster-plot-bar {
        position: absolute;
        width: 3px;
        height: 55px;
        top: 4px;
        right: 0;
        opacity: .7;
    }

    .roster-plot-type-name {
        font-size: .65rem;
        white-space: nowrap;
        top: 16px;
        left: 12px;
        color: black;
        z-index: 9;
        position: absolute;
    }

    .roster-plot-hours {
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
