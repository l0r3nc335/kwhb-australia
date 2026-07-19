<template>
    <div class="tw-flex tw-w-full">
        <AppTextField @keyup="searchName" v-model="query" class="tw-mr-4 !tw-grow-0" prepend-inner-icon="tabler-search" placeholder="Search By Name" style="width: 200px"></AppTextField>
        <AppCombobox v-if="showTypeFilter"  class="tw-mr-4 !tw-grow-0" style="width: 150px" clearable v-model="leaveType" :items="leaveTypes" placeholder="Leave Type" item-title="name" item-value="id"></AppCombobox>
        <VueDatePicker v-if="showDateFilter" class="tw-mr-4 !tw-grow-0" placeholder="Select Date" v-model="dateRange" format="dd/MM/yyyy" :enable-time-picker="false"  range multi-calendars style="width: 250px"></VueDatePicker>
        <AppCombobox v-if="showMonthFilter" class="tw-mr-4 !tw-grow-0" style="width: 150px" v-model="selectedMonth" :items="months" placeholder="Select Month" item-title="name" item-value="id"></AppCombobox>
        <AppCombobox class="tw-mr-4 !tw-grow-0" style="width: 150px" clearable v-model="division" :items="divisions" placeholder="Division" item-title="name" item-value="id"></AppCombobox>
        <AppCombobox class="tw-mr-4 !tw-grow-0" style="width: 150px" clearable v-model="location" :items="locations" placeholder="Location" item-title="name" item-value="id"></AppCombobox>
        <AppCombobox class="tw-mr-4 !tw-grow-0" style="width: 150px" clearable v-model="status" :items="statuses" placeholder="Status" item-title="name" item-value="id"></AppCombobox>
        <AppCombobox v-if="showDateGroupFilter"  class="tw-mr-4 !tw-grow-0" style="width: 150px"  v-model="dateGroup" :items="dateGroups" item-title="name" item-value="id"></AppCombobox>
        <div v-if="showPeriodNavigationFilter" class="tw-flex tw-cursor-pointer">
            <div class="hover:tw-bg-gray-100 tw-py-1 tw-p-2 tw-border tw-rounded tw-border-gray-400 tw-justify-center tw-mr-2" style="border-radius: 8px"
                 @click="$emit('filter-period-subtract')">
                <div class="tw-flex tw-flex-col tw-justify-center tw-h-full">
                    <VIcon icon="tabler-chevron-left"></VIcon>
                </div>
            </div>
            <div class="hover:tw-bg-gray-100 tw-py-1 tw-p-2 tw-border tw-rounded tw-border-gray-400 tw-justify-center" style="border-radius: 8px"
                 @click="$emit('filter-period-add')">
                <div class="tw-flex tw-flex-col tw-justify-center tw-h-full">
                    <VIcon icon="tabler-chevron-right"></VIcon>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import divisionApi from "@/api/division/divisionApi";
import leaveApi from "@/api/leave/leaveApi";
import locationApi from "@/api/location/locationApi";
import _ from 'lodash';
import moment from "moment";

    export default {
        props: {
            showPeriodNavigationFilter: {
                type: Boolean,
                default: false
            },
            showDateFilter: {
                type: Boolean,
                default: true
            },
            showTypeFilter: {
                type: Boolean,
                default: true
            },
            showMonthFilter: {
                type: Boolean,
                default: false
            },
            showDateGroupFilter: {
                type: Boolean,
                default: false
            },
            filterMonth: {
                default: false
            }
        },

        data() {
            return {
                query: null,
                leaveType: null,
                division: null,
                location: null,
                status: null, // approved
                dateRange: null,
                dateGroup: 'Monthly',
                selectedMonth: null,
                leaveTypes: [],
                divisions: [],
                locations: [],
                statuses: [],
                dateGroups: [
                    'Weekly',
                    'Fortnightly',
                    'Monthly'
                ],
                months: [
                    {
                        id: 1,
                        name: 'January'
                    },
                    {
                        id: 2,
                        name: 'February'
                    },
                    {
                        id: 3,
                        name: 'March'
                    },
                    {
                        id: 4,
                        name: 'April'
                    },
                    {
                        id: 5,
                        name: 'May'
                    },
                    {
                        id: 6,
                        name: 'June'
                    },
                    {
                        id: 7,
                        name: 'July'
                    },
                    {
                        id: 8,
                        name: 'August'
                    },
                    {
                        id: 9,
                        name: 'September'
                    },
                    {
                        id: 10,
                        name: 'October'
                    },
                    {
                        id: 11,
                        name: 'November'
                    },
                    {
                        id: 12,
                        name: 'December'
                    },
                ]
            }
        },

        mounted() {
            this.loadResources()
            this.getCurrentMonth()
        },

        computed: {
            filters() {
                return {
                    query: this.query,
                    leave_type: this.leaveType,
                    division: this.division,
                    location: this.location,
                    status: this.status,
                    date_start: this.dateStart,
                    date_end: this.dateEnd
                }
            },

            dateStart() {
                return moment(this.dateRange[0]).format('YYYY-MM-DD')
            },

            dateEnd() {
                return moment(this.dateRange[1]).format('YYYY-MM-DD')
            },
        },

        methods: {
            getDateFormat() {
                if (this.dateRange.length > 0) {

                }

                return 'asd'
            },

            getCurrentMonth() {
                this.selectedMonth = this.months.find(m => m.id == moment().format('M'))
                this.$emit('filter-month', this.selectedMonth)
            },

            searchName: _.debounce(function() {
                this.$emit('query', this.query)
            }, 500),

            loadResources() {
                this.loadLeaveTypes()
                this.loadDivisions()
                this.loadLocations()
                this.loadLeaveStatuses()

            },

            async loadLeaveStatuses() {
                this.statuses = await leaveApi.fetchLeaveStatuses()
            },

            async loadLeaveTypes() {
                this.leaveTypes = await leaveApi.fetchLeaveTypes()
            },

            async loadDivisions() {
                this.divisions = await divisionApi.fetchDivisions()
            },

            async loadLocations() {
                this.locations = await locationApi.fetchLocations()
            },
        },

        watch: {
            leaveType() {
                this.$emit('filter-type', this.leaveType)
            },

            division() {
                this.$emit('filter-division', this.division)
            },

            location() {
                this.$emit('filter-location', this.location)
            },

            status() {
                this.$emit('filter-status', this.status)
            },

            dateGroup() {
                this.$emit('filter-date-group', this.dateGroup)
            },

            dateRange() {
                if (this.dateRange && this.dateRange.length === 2) {
                    this.$emit('filter-date', {
                    start: moment(this.dateRange[0]).format('YYYY-MM-DD'),
                    end: moment(this.dateRange[1]).format('YYYY-MM-DD')
                    })
                } else {
                    this.$emit('filter-date', {
                    start: null,
                    end: null
                    })
                }
            },

            selectedMonth() {
                this.$emit('filter-month', this.selectedMonth)
            },

            filterMonth() {
                if (this.filterMonth) {
                    this.selectedMonth = this.months.find(m => m.id == this.filterMonth)
                    console.log('filterMonth', this.selectedMonth)
                }
            }
        }
    }
</script>

<style>
    .dp__input {
        height: 43px;
        border-radius: 8px;
        border-color: #999999db;
    }
</style>
