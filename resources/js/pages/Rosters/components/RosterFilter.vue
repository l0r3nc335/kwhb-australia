<template>
    <div class="tw-flex tw-justify-start roster-filters-wrapper tw-items-end">
        <AppCombobox class="tw-mr-3 !tw-grow-0" density="compact" style="min-width: 120px; min-height: 2.5rem;"  v-model="filters.location" :items="locations" placeholder="Location" item-title="name" item-value="id"></AppCombobox>
        <AppCombobox class="tw-mr-3 !tw-grow-0" style="min-width: 120px" clearable v-model="filters.staff" :items="staffs" placeholder="Staffs" item-title="name" item-value="id"></AppCombobox>
        <AppCombobox class="tw-mr-3 !tw-grow-0" style="min-width: 120px" clearable v-model="filters.shiftType" :items="shiftTypes" placeholder="Shift Type" item-title="name" item-value="id"></AppCombobox>
        <AppCombobox class="tw-mr-3 !tw-grow-0" style="min-width: 120px" clearable v-model="filters.roleType" :items="roleTypes" placeholder="Role Type" item-title="name" item-value="id"></AppCombobox>
        <VBtn class="tw-mr-3" prepend-icon="tabler-bell" color="kwhb">Notify</VBtn>
        <button class="tw-px-3 tw-py-1 tw-bg-gray-200 tw-rounded-lg tw-mr-3 hover:tw-bg-gray-100" @click="goToday" style="height: 2.5rem">Today</button>
        <div class="tw-flex tw-relative tw-items-end">
            <div class="tw-flex tw-cursor-pointer tw-mr-3" style="height: 2.5rem">
                <div class="hover:tw-bg-gray-100 tw-py-1 tw-p-2 tw-border tw-rounded tw-border-gray-300 tw-text-gray-400 tw-justify-center tw-mr-2" style="border-radius: 8px"
                     @click="dateRangeIndex--">
                    <div class="tw-flex tw-flex-col tw-justify-center tw-h-full">
                        <VIcon icon="tabler-chevron-left"></VIcon>
                    </div>
                </div>
                <div class="hover:tw-bg-gray-100 tw-py-1 tw-p-2 tw-border tw-rounded tw-border-gray-300 tw-text-gray-400 tw-justify-center" style="border-radius: 8px"
                     @click="dateRangeIndex++">
                    <div class="tw-flex tw-flex-col tw-justify-center tw-h-full">
                        <VIcon icon="tabler-chevron-right"></VIcon>
                    </div>
                </div>
            </div>
            <div class="tw-px-3 tw-mr-3 tw-text-[.9rem] tw-flex tw-flex-col tw-justify-center tw-font-bold tw-absolute tw-top-[-30px] tw-left-[-10px] tw-w-max sm:tw-flex">
                {{dateRangeString}}
            </div>
        </div>
        <div  class="tw-mr-3 tw-flex tw-flex-1">
            <VTextField prepend-inner-icon="tabler-search" placeholder="Search" ></VTextField>
        </div>
        <AppCombobox class="tw-mr-3 !tw-grow-0" style="width: 120px"  v-model="filters.dateRangeType" :items="dateRangeTypes" placeholder="Date Period" item-title="name" item-value="id"></AppCombobox>
        <VBtn class="tw-mr-3" :disabled="!filters?.location || staffs.length == 0" prepend-icon="tabler-plus" color="primary" @click="$emit('add-shift')">Add Shift</VBtn>
    </div>
</template>
<script>
    import moment from "moment";

    export default {
        emits: ['filter-change', 'add-shift', 'date-range-index-change'],

        props: {
            filters: {
                type: Object,
                required: true
            },
            dateRangeTypes: {
                type: Array,
                default: []
            },
            locations: {
                type: Object,
                required: true
            },
            shiftTypes: {
                type: Object,
                required: true
            },
            roleTypes: {
                type: Object,
                required: true
            },
            staffs: {
                type: Array,
                default: [],
                required: true
            }
        },

        data() {
            return {
                dateRangeIndex: 0,
                dateRangeStart: null,
                dateRangeEnd: null
            }
        },

        computed: {
            dateRangeString() {
                let dateRangeString = ''
                let mDateStart = moment(this.dateRangeStart)
                let mDateEnd = moment(this.dateRangeEnd)

                if (this.filters.dateRangeType == 'Daily') {
                    dateRangeString =  mDateStart.format('MMM DD, YYYY')
                } else if (mDateStart.month() != mDateEnd.month() && mDateStart.year() == mDateEnd.year()) {
                    dateRangeString = mDateStart.format('MMM DD') + ' - ' + mDateEnd.format('MMM DD, YYYY')
                } else if (mDateStart.month() === mDateEnd.month() && mDateStart.year() == mDateEnd.year()) {
                    dateRangeString = mDateStart.format('MMM DD') + ' - ' + mDateEnd.format('DD, YYYY')
                }
                else {
                    dateRangeString = mDateStart.format('MMM DD, YYYY') + ' - ' + mDateEnd.format('MMM DD, YYYY')
                }

                return dateRangeString
            }
        },

        mounted() {
            this.calculateDateRanges()
        },

        methods: {
            goToday() {
                this.filters.dateRangeType = 'Daily'
                this.dateRangeIndex = 0
            },

            calculateDateRanges() {
                let dateStart = null
                let dateEnd = null

                switch (this.filters.dateRangeType) {
                    case 'Daily':
                        dateStart = moment().add(this.dateRangeIndex, 'days').format('YYYY-MM-DD')
                        dateEnd = moment().add(this.dateRangeIndex, 'days').format('YYYY-MM-DD')
                        break;
                    case 'Weekly':
                        dateStart = moment().startOf('week').add(this.dateRangeIndex, 'weeks').startOf('week').format('YYYY-MM-DD')
                        dateEnd = moment().startOf('week').add(this.dateRangeIndex, 'weeks').endOf('week').format('YYYY-MM-DD')
                        break;
                    case 'Fortnightly':
                        dateStart = moment().startOf('month').add(this.dateRangeIndex * 2, 'weeks').startOf('week').format('YYYY-MM-DD')
                        dateEnd = moment().startOf('month').add((this.dateRangeIndex * 2) + 1, 'weeks').endOf('week').format('YYYY-MM-DD')
                        break;
                    case 'Monthly':
                        dateStart = moment().startOf('month').add(this.dateRangeIndex, 'months').format('YYYY-MM-DD')
                        dateEnd = moment().endOf('month').add(this.dateRangeIndex, 'months').format('YYYY-MM-DD')
                        break
                }

                this.dateRangeStart = dateStart
                this.dateRangeEnd = dateEnd
            }
        },

        watch: {
            dateRangeIndex() {
                this.calculateDateRanges()
                this.$emit('date-range-index-change', this.dateRangeIndex)
                this.filters.dateRangeStart = this.dateRangeStart
                this.filters.dateRangeEnd = this.dateRangeEnd
                this.$emit('filter-change', this.filters)
            },

            filters: {
                handler() {
                    this.calculateDateRanges()
                    this.filters.dateRangeStart = this.dateRangeStart
                    this.filters.dateRangeEnd = this.dateRangeEnd
                    this.$emit('filter-change', this.filters)
                },
                deep: true
            }
        }
    }
</script>

<style>
    .roster-filters-wrapper .v-field__input {
        min-height: 2.5rem !important;
        padding: 0 15px !important;
    }
</style>
