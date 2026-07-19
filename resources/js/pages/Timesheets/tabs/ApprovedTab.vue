<template>
    <div class="tw-flex tw-flex-col">
        <template v-if="!periodSelected">
            <div class="tw-flex tw-flex-col">
                <div class="tw-flex tw-py-2 tw-justify-between tw-mb-2">
                    <div class=" tw-text-[2.3rem]  tw-font-bold" >Approved Timesheets</div>
                    <TimesheetFilters :selected-role="filters.role"
                                      :selected-people="filters.employee"
                                      :show-search-query-input="true"
                                      :show-division-selector="true"
                                      :show-role-selector="true"
                                      :show-employee-selector="false"
                                      :show-teammate-selector="false"
                                      :show-job-titles-selector="false"
                                      :show-week-period-selector="!periodSelected"
                                      :filters="filters">
                    </TimesheetFilters>
                </div>
                <div class="tw-p-4 tw-bg-gray-200">
                    <b>Time Periods</b>
                </div>
                <div class="tw-flex tw-flex-col">
                    <div v-for="period in filteredTimePeriods" class="tw-flex tw-justify-between tw-p-1 tw-border-b ">
                        <div class="tw-cursor-pointer tw-text-blue-600 hover:tw-text-blue-400 tw-flex tw-flex-col tw-justify-center" @click="selectPeriod(period)">
                            <u>{{period.label}}</u>
                        </div>
                        <IconBtn>
                            <VIcon icon="tabler-dots-vertical" />
                            <VMenu
                                activator="parent"
                            >
                                <VList>
                                    <VListItem class="tw-cursor-pointer hover:tw-bg-gray-100" @click="periodSelected = period.start">View</VListItem>
                                </VList>
                            </VMenu>
                        </IconBtn>
                    </div>
                    <div v-if="filteredTimePeriods.length === 0" class="tw-p-4">
                        No approved timesheets in this period.
                    </div>
                </div>
            </div>
        </template>
        <TimesheetUserWeekPeriods v-else
                                  :show-teammate-filter="true"
                                  :period-selected="periodSelected"
                                  :periods="periods" title="Approved Timesheets"
                                  :show-back-button="true"
                                  :approve-view-mode="true"
                                  @back="periodSelected = null">
        </TimesheetUserWeekPeriods>
    </div>
</template>
<script>
import TimesheetUserWeekPeriods from "@/pages/Timesheets/components/timesheets/TimesheetUserWeekPeriods.vue"
import TimesheetFilters from "@/pages/Timesheets/components/TimesheetFilters.vue";
import moment from "moment/moment";
import _ from 'lodash'

export default {
    components: {
        TimesheetUserWeekPeriods,
        TimesheetFilters
    },

    props: {
        periods: {
            required: true,
            type: Array
        }
    },

    data() {
        return {
            periodSelected: null,
            filters: {},
        }
    },

    computed: {
        filteredTimePeriods() {
            let periods = this.timePeriods
            let _this = this

            if (this.filters.start && this.filters.end) {
                periods = periods.filter(function(p) {
                    return p.start == _this.filters.start
                })
            }

            if (this.filters.search_query) {
                periods = periods.filter(function(p) {
                    let employees = p.employees.filter(pe => pe.name.search(_this.filters.search_query))
                    return employees.length > 0 ? p : false
                })
            }

            if (this.filters.division) {
                periods = periods.filter(function(p) {
                    let employees = p.employees.filter(pe => pe.division_id == _this.filters.division.id)
                    return employees.length > 0 ? p : false
                })
            }

            if (this.filters.role) {
                periods = periods.filter(function(p) {
                    let employees = p.employees.filter(pe => pe.roles == _this.filters.role.id)
                    console.log(_this.filters.role)
                    return employees.length > 0 ? p : false
                })
            }

            return periods;
        },

        timePeriods() {
            let timePeriods = []
            let weekStarts = []
            let periods = []

            for (let i in this.periods) {
                weekStarts.push(this.periods[i].week_start)
                let dateRange = this.formatDate(this.periods[i].week_start, 'DD MMMM YYYY') + ' - ' + this.formatDate(this.periods[i].week_end, 'DD MMMM YYYY')
                timePeriods.push(dateRange)
            }

            timePeriods = _.uniq(timePeriods)
            weekStarts = _.uniq(weekStarts)

            for (let i in weekStarts) {
                let p = {}
                p.start = weekStarts[i]
                p.label = timePeriods[i]
                p.employees = this.periods.filter(p => p.week_start == weekStarts[i])
                periods.push(p)
            }

            return periods
        },
    },

    methods: {
        selectPeriod(period) {
            this.periodSelected = period.start
        },

        formatDate(date, format) {
            return moment(date).format(format)
        },
    }
}
</script>
