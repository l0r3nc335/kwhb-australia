<template>
    <div class="tw-flex tw-w-full">
        <VueDatePicker
            class="tw-mr-0 filter_fields"
            placeholder="Date"
            v-model="dateRange"
            format="dd/MM/yyyy"
            :enable-time-picker="false"
            range
            multi-calendars="1"
            style="width: 250px; padding: 0!important; margin: 0 5px!important;"
            position="left">
            clearable
        </VueDatePicker>
        <div style="margin: 0 5px!important;">
            <AppCombobox
                class="!tw-grow-0 tw-text-xs filter_fields "
                style="width: 150px; "
                clearable
                v-model="role"
                :items="roles"
                placeholder="Job Title"
                item-title="name"
                item-value="id">
            </AppCombobox>
        </div>
        <div style="margin: 0 5px!important;">
            <AppCombobox
                class="tw-mr-0 !tw-grow-0 tw-text-xs filter_fields"
                style="width: 150px;"
                clearable
                v-model="location"
                :items="locations"
                placeholder="Location"
                item-title="name"
                item-value="id">
            </AppCombobox>
        </div>
        <div style="margin: 0 5px!important;">
            <AppCombobox
                class="tw-mr-0 tw-text-xs filter_fields"
                style="width: 150px"
                clearable
                v-model="expiry"
                :items="expiries"
                placeholder="Expiry"
                item-title="formatted_expiry_date"
                item-value="id">
            </AppCombobox>
        </div>
        <div style="margin: 0 5px!important;">
            <AppCombobox
                class="tw-mr-0 tw-text-xs filter_fields"
                style="width: 150px"
                clearable
                v-model="status"
                :items="statuses"
                placeholder="Status"
                item-title="name"
                item-value="id">
            </AppCombobox>
        </div>
    </div>
</template>

<script>
import contractAPI from "@/api/contract/contractApi";
import locationApi from "@/api/location/locationApi";
import _ from 'lodash';
import moment from "moment";

export default {
    props: {
        filterMonth: {
            default: false
        }
    },

    data() {
        return {
            query: null,
            role: null,
            location: null,
            expiry: null,
            status: null, // approved
            dateRange: null,
            dateGroup: 'Monthly',
            selectedMonth: null,
            roles: [],
            locations: [],
            expiries: [],
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
                role: this.role,
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
            this.loadRoles()
            this.loadLocations()
            this.laodExpiries()
            this.loadContractStatuses()
        },

        async laodExpiries() {
            this.expiries = await contractAPI.fetchAllContractsExpiry()
        },

        async loadRoles() {
            //TODO: loaad the distinc positions of each contracts
            this.roles = await contractAPI.fetchRolesWithContract()
        },

        async loadLocations() {
            this.locations = await locationApi.fetchLocations()
        },

        async loadContractStatuses() {
            this.statuses = await contractAPI.fetchContractStatuses()
            console.log(this.statuses)
        },
    },

    watch: {
        role() {
            this.$emit('filter-role', this.role)
        },

        expiry() {
            this.$emit('filter-expiry', this.expiry)
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
            if (!this.dateRange || this.dateRange.length === 0) {
                this.$emit('filter-date', { start_date: null, end_date: null });
                return;
            }
            this.$emit('filter-date', {
                start_date: moment(this.dateRange[0]).format('YYYY-MM-DD'),
                end_date: moment(this.dateRange[1]).format('YYYY-MM-DD'),
            });
        },

        selectedMonth() {
            this.$emit('filter-month', this.selectedMonth)
        },

        filterMonth() {
            if (this.filterMonth) {
                this.selectedMonth = this.months.find(m => m.id == this.filterMonth)
            }
        }
    }
}
</script>

<style scoped>
.dp__input {
    height: 43px;
    border-radius: 8px;
    border-color: #999999db;
}

/* Scale down the VueDatePicker calendar */
.dp__main {
    /* transform: scale(0.75); /* Adjust scale factor for size */
    transform-origin: top left; /* Ensure proper positioning */
    z-index: 9999 !important;
}

.dp__calendar_header,
.dp__calendar_header_separator,
.dp__calendar {
    font-size: 12px; /* Reduce font size */
}

.dp__cell_inner {
    padding: 4px; /* Reduce padding between date cells */
}


.filter_fields {
    //border: 1px solid #6b7280;
    //border-radius: 10px;
    height: 44px;
}

</style>
