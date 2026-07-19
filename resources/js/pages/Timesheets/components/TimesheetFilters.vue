<template>
    <div class="tw-flex">
        <AppTextField v-if="showSearchQueryInput"
                    v-model="filters.search_query"
                      prepend-inner-icon="tabler-search"
                      class="tw-mr-3"
                      style="width: 250px"
                    placeholder="Search for user">
        </AppTextField>
        <AppCombobox v-if="showEmployeeSelector"
                     :items="formattedEmployees"
                     v-model="filters.employee"
                     placeholder="People"
                     style="width: 10rem"
                     class="tw-mr-3"
                     item-title="name"
                     item-value="id"
                     clearable>
        </AppCombobox>
        <AppCombobox  v-if="showDivisionSelector"
                      :items="divisions"
                      v-model="filters.division"
                      style="width: 10rem"
                      class="tw-mr-3"
                      placeholder="Divisions"
                      item-title="name"
                      item-value="id"
                      clearable>
        </AppCombobox>
        <AppCombobox  v-if="showRoleSelector"
                      :items="roles"
                     v-model="filters.role"
                     style="width: 10rem"
                     class="tw-mr-3"
                     placeholder="Roles"
                     item-title="name"
                     item-value="id"
                     clearable>
        </AppCombobox>
        <AppCombobox  v-if="showTeammateSelector"
                      :items="teammates"
                      v-model="filters.teammates"
                      placeholder="Teammates"
                      style="width: 10rem"
                      class="tw-mr-3"
                      item-title="name"
                      item-value="id"
                      clearable>
        </AppCombobox>
        <AppCombobox  v-if="showJobTitlesSelector"
                      :items="job_titles"
                      v-model="filters.job_title"
                      placeholder="Job Title"
                      style="width: 10rem"
                      class="tw-mr-3"
                      clearable>
        </AppCombobox>
        <div v-if="showWeekPeriodSelector" class="tw-flex tw-rounded-lg tw-border tw-border-gray-400 tw-text-gray-600 tw-h-fit" style="height: 43px;">
            <div class="tw-flex tw-px-4 tw-py-3 tw-justify-center" :class="weekPeriodString" style="width:200px">
                <div>
                    <VIcon icon="tabler-calendar-month" class="mr-3"></VIcon>
                    {{filterPeriodString}}
                </div>
            </div>
            <div @click="period=period-1"
                 class="tw-px-4 tw-border-l tw-border-gray-400 tw-px-3 tw-py-2 tw-cursor-pointer hover:tw-bg-orange-500 hover:tw-text-white">
                <VIcon icon="tabler-chevron-left"></VIcon>
            </div>
            <div @click="period=period+1"
                 class="tw-px-4 tw-border-l tw-border-gray-400 tw-px-3 tw-py-2 tw-cursor-pointer hover:tw-bg-orange-500 hover:tw-text-white">
                <VIcon icon="tabler-chevron-right"></VIcon>
            </div>
        </div>
    </div>
</template>
<script>
    import {useResourceStore} from "@/store/resourceStore";
    import {mapState} from "pinia";
    import timesheetApi from "@/api/timesheet/timesheetApi";
    import moment from "moment";

    export default {
        props: {
            showWeekPeriodSelector: {
                type: Boolean,
                default: false
            },

            showSearchQueryInput: {
                type: Boolean,
                default: false
            },

            showJobTitlesSelector: {
                type: Boolean,
                default: false
            },

            showTeammateSelector: {
                type: Boolean,
                default: false
            },

            showRoleSelector: {
                type: Boolean,
                default: true
            },
            showEmployeeSelector: {
                type: Boolean,
                default: true
            },
            showDivisionSelector: {
                type: Boolean,
                default: true
            },
            filters: {
                required: true
            }
        },

        data() {
            return {
                selectedRole: null,
                selectedEmployee: null,
                selectedDivision: null,
                selectedJobTitle: null,
                teammates: [
                    {
                        id: 1,
                        name: 'Show: Everyone'
                    },
                    {
                        id: 2,
                        name: 'Show: Division'
                    },
                    {
                        id: 3,
                        name: 'Show: Teammate'
                    }
                ],
                period: 0
            }
        },

        computed: {
            computePeriodDates() {
                let period = this.period * 7
                let dates = [];
                for (let i = 0; i < 7; i++) {
                    dates.push(moment().startOf('week').add((3 + period + i), 'days'))
                }

                return dates
            },

            weekPeriodString() {
                if (this.filterPeriodString.length > 15) {
                    return 'tw-text-[.7rem]'
                }
                return 'tw-text-[.8rem]'
            },

            filterPeriodString() {
                // let string = 'Current Week'
                // if (this.period !== 0) {
                //     string = this.formatDate(this.computePeriodDates[0], 'MMM DD') + ' - ' + this.formatDate(this.computePeriodDates[6], 'MMMM DD')
                // }
                return this.formatDate(this.computePeriodDates[0], 'MMM DD') + ' - ' + this.formatDate(this.computePeriodDates[6], 'MMMM DD')
            },


            formattedEmployees() {
                let employees = []

                for(let i in this.employees) {
                    let employee = {}
                    employee.id = this.employees[i].user_id
                    employee.name = this.employees[i].name
                    employees.push(employee)
                }

                return employees
            },
            ...mapState(useResourceStore, ['employees', 'roles', 'divisions', 'job_titles'])
        },

        mounted() {
            this.loadPeriodDates()
        },

        methods: {
            formatDate(date, format) {
                return moment(date).format(format)
            },

            async loadPeriodDates() {
                this.loading = true
                this.periodDates = await timesheetApi.fetchPeriodDates(this.formatDate(this.computePeriodDates[0], 'YYYY-MM-DD'), this.period)
                this.filters.start = this.formatDate(this.computePeriodDates[0], 'YYYY-MM-DD')
                this.filters.end = this.formatDate(this.computePeriodDates[6], 'YYYY-MM-DD')
                this.loading = false
            },
        },

        watch: {
            period() {
                this.loadPeriodDates()
            }
        }
    }
</script>
