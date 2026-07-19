<template>
    <div class="tw-flex tw-flex-col">
        <div class="tw-flex tw-mb-4 tw-justify-between">
            <div class="tw-flex tw-py-1 tw-justify-between">
                <div v-if="periodDates.length > 0" class="tw-font-bold tw-text-[2rem] tw-flex" style="font-family: sans-serif">
                    <span class="tw-text-gray-400 tw-mr-2">Timesheet / </span>
                    <span>{{formatDate(periodDates[0], 'DD')}} - {{formatDate(periodDates[6], 'DD MMM YYYY')}}</span>

                    <div class="tw-flex tw-flex-col tw-justify-center" v-if="statusString">
                        <span v-if="isReturned" class="tw-cursor-pointer tw-ml-6 tw-text-[.9rem] tw-px-4 tw-py-1 tw-bg-orange-500 tw-text-white tw-rounded-full" style="text-transform: capitalize">Returned</span>
                        <span v-else class="tw-cursor-pointer tw-ml-6 tw-text-[.9rem] tw-px-4 tw-py-1 tw-bg-yellow-500 tw-rounded-full" style="text-transform: capitalize">{{statusString}}</span>
                    </div>
                </div>
            </div>
            <div class="tw-py-1">
                <div class="tw-flex tw-rounded-lg tw-border tw-text-gray-600 tw-h-fit">
                    <div class="tw-flex tw-px-8 tw-py-2">
                        <VIcon icon="tabler-calendar-month" class="mr-3"></VIcon>
                        <span>
                        {{filterPeriodString}}
                    </span>
                    </div>
                    <div @click="period--"
                         class="tw-px-4 tw-border-l tw-px-3 tw-py-2 tw-cursor-pointer hover:tw-bg-orange-500 hover:tw-text-white">
                        <VIcon icon="tabler-chevron-left"></VIcon>
                    </div>
                    <div @click="period++"
                         class="tw-px-4 tw-border-l tw-px-3 tw-py-2 tw-cursor-pointer hover:tw-bg-orange-500 hover:tw-text-white">
                        <VIcon icon="tabler-chevron-right"></VIcon>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="isReturned" class="tw-mb-4 tw-text-[1.2rem]">
            <b>Returned Note: {{data?.return_comment}}</b>
        </div>
        <table class="timesheet-preview-table tw-border tw-border-gray-100" v-if="loading === false">
            <thead>
                <tr>
                    <th v-if="timesheets.length > 0" style="width: 30%;text-align: left;padding: 10px 20px;text-transform: capitalize">
                        <div class="tw-text-[1.1rem]">
                            <b>{{timesheets[0].full_name}}</b>
                        </div>
                    </th>
                    <template v-if="periodDates.length > 0">
                        <th class="header-date" v-for="periodDate in periodDates">
                            <div class="tw-flex tw-flex-col tw-text-right">
                                <div>{{formatDate(periodDate, 'ddd')}}</div>
                                <div style="word-break: keep-all">{{formatDate(periodDate, 'D MMM')}}</div>
                            </div>
                        </th>
                    </template>
                    <th class="tw-text-center !tw-px-6 header-total">
                        <b>Total</b>
                    </th>
                    <th style="width: 200px" v-if="showActions && !isReturned"></th>
                </tr>
            </thead>
            <tbody>
                <template v-if="timesheets.length > 0">
                    <tr v-for="ac in activeCategories" class="tw-border-b tw-border-b-gray-100">
                        <td class="tw-px-4 tw-py-5 tw-text-[1rem]">
                            {{categories.find(c => c.id == ac)?.name}}
                        </td>
                        <td v-for="periodDate in periodDates" class="hours" :class="[getDateHours(ac, periodDate) != '0:00' ? 'tw-font-bold' : '']">
                            {{getDateHours(ac, periodDate)}}
                        </td>
                        <td class="tw-relative tw-text-center" >
                            <div class="tw-flex tw-w-full tw-h-full tw-bg-orange-500 tw-text-center" style="position: absolute;top: 0;opacity: .15">
                            </div>
                            <b>
                                {{getTotalByCategory(ac)}}
                            </b>
                        </td>
                        <td v-if="showActions && !isReturned">
                            <div class="tw-flex tw-w-full tw-justify-center tw-text-gray-500 tw-text-[.8rem]">
                                <div class="tw-flex tw-flex-col tw-justify-center">
                                    <VIcon icon="tabler-edit" class="mr-6 tw-cursor-pointer hover:tw-text-blue-500" @click="showEditTimesheetsDialog = true"></VIcon>
                                </div>
                                <div class="tw-flex tw-flex-col tw-justify-center">
                                    <VIcon icon="tabler-trash" class="mr-3 tw-cursor-pointer hover:tw-text-red-500" @click="selectedTimesheetCategory = ac;showDeleteTimesheetCategory = true"></VIcon>
                                </div>
                                <IconBtn>
                                    <VIcon icon="tabler-dots-vertical" />
                                    <VMenu
                                        activator="parent"
                                    >
                                        <VList>
                                            <VListItem class="tw-cursor-pointer hover:tw-bg-gray-100" @click="showEditTimesheetsDialog = true">Edit</VListItem>
                                            <VListItem class="tw-cursor-pointer hover:tw-bg-green-100" @click="selectedTimesheetCategory = ac;showDeleteTimesheetCategory = true">Delete</VListItem>
                                        </VList>
                                    </VMenu>
                                </IconBtn>
                            </div>
                        </td>
                    </tr>
                    <tr >
                        <td class="tw-px-4 tw-py-5 tw-text-[1rem]">
                            <b>Total</b>
                        </td>
                        <td v-for="periodDate in periodDates" class="tw-text-center hours">
                            <b>{{getTotalByDate(periodDate)}}</b>
                        </td>
                        <td class="tw-relative tw-text-center">
                            <div class="tw-flex tw-w-full tw-h-full tw-bg-orange-500 tw-text-center" style="position: absolute;top: 0;opacity: .15">
                            </div>
                            <b>{{grandTotal}}</b>
                        </td>
                    </tr>
                </template>
                <template v-else>
                    <tr>
                        <td class="tw-px-10 py-4" style="width: 35%">
                            <span class="tw-text-red-500">No Timesheet Entries for this period.</span>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
        <div class="tw-mt-4 tw-flex tw-justify-end" v-if="showActions">
            <VBtn  color="default" variant="outlined" @click="$emit('back')">Back</VBtn>
            <VBtn class="tw-ml-4" @click="showReturnDialog = true" v-if="!isReturned">Return</VBtn>
            <VBtn class="tw-ml-4" v-if="showApproveButton && !isReturned" color="success" prepend-icon="tabler-check" @click="showApproveDialog = true">Approve</VBtn>
        </div>
        <VDialog v-model="showApproveDialog" width="400">
            <DialogCloseBtn @click="showApproveDialog=false" />
            <div class="tw-flex tw-flex-col tw-bg-white tw-rounded-lg tw-relative tw-overflow-hidden">
                <div class="tw-flex tw-flex-col tw-w-full tw-p-3 tw-bg-gray-200 tw-text-[1.4rem]">
                    <div>Approve Timesheet</div>
                    <div class="tw-text-[.8rem]">{{formatDate(periodDates[0], 'DD MMMM, YYYY')}} - {{formatDate(periodDates[6], 'DD MMMM, YYYY')}}</div>
                </div>
                <div class="tw-p-4 tw-flex tw-w-full">
                    Confirming that you are approving this timesheet.
                </div>
                <div class="tw-px-4 tw-mb-2 tw-flex tw-w-full tw-justify-end">
                    <VBtn color="success" @click="approveTimesheetWeek">Confirm</VBtn>
                </div>
            </div>
        </VDialog>
        <VDialog v-model="showReturnDialog" width="500">
            <DialogCloseBtn @click="showReturnDialog=false" />
            <div class="tw-flex tw-flex-col tw-bg-white tw-rounded-lg tw-relative tw-overflow-hidden tw-py-4 tw-px-6">
                <div class="tw-mb-3">Return with comment</div>
                <div class=" tw-flex tw-w-full tw-mb-3">
                    <VTextarea placeholder="Comment" v-model="returnRemark" required></VTextarea>
                </div>
                <div class="tw-mb-2 tw-flex tw-w-full tw-justify-end">
                    <VBtn :disabled="!returnRemark" color="success" @click="returnTimesheetWeek">Send</VBtn>
                </div>
            </div>
        </VDialog>
        <VDialog v-model="showEditTimesheetsDialog" width="1400">
            <DialogCloseBtn @click="showEditTimesheetsDialog=false" />
            <div class="tw-py-4 tw-bg-white tw-flex tw-flex-col">
                <div class="tw-font-bold tw-text-[1rem] tw-mb-4 tw-px-4">
                    Edit Timesheet
                </div>
                <div style="max-height: 500px;overflow:auto">
                    <TimesheetTable :timesheets="timesheets" :categories="categories" :force-edit="true" @delete="entry => deleteEntry(entry)"></TimesheetTable>
                </div>
                <div class="tw-flex tw-justify-between tw-px-4 tw-h-full">
<!--                    <TimesheetAddRow :dates="formattedDates" @selected="addTimesheetRow"></TimesheetAddRow>-->
                    <VBtn @click="updateTimesheets">Update</VBtn>
                </div>
            </div>
        </VDialog>
        <VDialog v-model="showDeleteTimesheetCategory" width="400">
            <DialogCloseBtn @click="showDeleteTimesheetCategory=false" />
            <div class="tw-py-4 tw-bg-white tw-flex tw-flex-col">
                <div class="tw-flex tw-font-bold tw-text-[1rem] tw-mb-4 tw-px-10 tw-py-4">
                    <div class="tw-py-2 tw-mr-4">
                        <VIcon icon="tabler-trash" class="tw-text-red-500"></VIcon>
                    </div>
                    Are you sure you want to delete this timesheet entry? This cannot be undone.
                </div>
                <div class="tw-flex tw-justify-end tw-px-4 tw-h-full">
                    <VBtn @click="deleteTimesheetCategory">Confirm</VBtn>
                </div>
            </div>
        </VDialog>
    </div>
</template>
<script>
    import timesheetApi from "@/api/timesheet/timesheetApi";
import TimesheetAddRow from "@/pages/Timesheets/components/timesheets/TimesheetAddRow.vue";
import TimesheetTable from "@/pages/Timesheets/components/timesheets/TimesheetTable.vue";
import { useTimesheetStore } from "@/store/timesheetStore";
import _ from 'lodash';
import moment from "moment/moment";
import { mapState } from "pinia";

    export default {
        components: {
            TimesheetTable,
            TimesheetAddRow
        },

        props: {
            editTimesheetEntries: {
                type: Boolean,
                default: false
            },

            data: {
                required: true
            },

            userId: {
                required: true
            },

            date: {
                required: true
            },

            showApproveButton: {
                type: Boolean,
                default: false
            },

            showActions: {
                type: Boolean,
                default: false
            },

            statusString: {
                type: String,
                default: null
            }
        },

        data() {
            return {
                loading: false,
                showApproveDialog: false,
                showReturnDialog: false,
                showEditTimesheetsDialog: false,
                showDeleteTimesheetCategory: false,
                selectedTimesheetCategory: null,
                returnRemark: null,
                timesheets: [],
                periodDates: [],
                period: 0
            }
        },

        mounted() {
            this.loadTimesheets()
            this.loadPeriodDates()
        },

        computed: {
            filterPeriodString() {
                // let string = 'Current Week'
                // if (this.period !== 0) {
                //     string = this.formatDate(this.periodDates[0], 'MMM DD') + ' - ' + this.formatDate(this.periodDates[6], 'MMMM DD')
                // }
                return this.formatDate(this.periodDates[0], 'MMM DD') + ' - ' + this.formatDate(this.periodDates[6], 'MMMM DD')
            },

            grandTotal() {
                let total = 0
                this.timesheets.forEach(ts => total+=ts.total)

                return convertToReadableTime(total)
            },

            activeCategories() {
                let categories = []

                this.timesheets.forEach(ts => {
                    categories.push(ts.category)
                })

                return _.uniq(categories)
            },

            isReturned() {
                return (this.data.submitted && this.data.returned) || (!this.data.submitted && this.data.returned)
            },

            ...mapState(useTimesheetStore, ['categories'])
        },

        methods: {
            async deleteTimesheetCategory() {
                this.loading = true
                await timesheetApi.deleteTimesheetCategory(this.userId, this.selectedTimesheetCategory, this.periodDates[0])
                this.loading = false
                this.showDeleteTimesheetCategory = false
                await this.loadTimesheets()
            },

            async updateTimesheets() {
                this.loading = true
                for (let i in this.timesheets) {
                    let entry = this.timesheets[i]
                    let cat = typeof entry.category === 'object' ? entry.category.id : entry.category
                    await timesheetApi.updateTimsheet(entry.id, {
                        category: cat,
                        start: entry.start,
                        end: entry.end,
                        break: entry.break,
                        comment: entry.comment
                    })
                }
                await this.loadTimesheets()
                this.showEditTimesheetsDialog = false
                this.$toast.success('Timesheet saved!')
            },

            async deleteEntry(entry) {
                let timesheetEntry = this.timesheets[0]
                this.timesheets.splice(entry, 1)
                this.loading = true

                await timesheetApi.deleteTimesheet(timesheetEntry.id)
            },

            async returnTimesheetWeek() {
                this.loading = true;
                await timesheetApi.returnTimesheetWeekPeriod(this.periodDates[0], this.userId, this.returnRemark)
                this.returnRemark = null
                this.loading = false;
                this.$emit('back')
            },

            async approveTimesheetWeek() {
                this.loading = true;
                await timesheetApi.approveTimesheetWeekPeriod(this.periodDates[0], this.userId)
                this.showApproveDialog = false
                this.loading = false;
                this.$emit('back')
            },

            getTotalByDate(date) {
                let total = 0;
                this.timesheets.filter(ts => ts.date == date).forEach(ts => total += ts.total)
                let minutes = convertToReadableTime(total)

                return minutes == 0 ? '0:00' : minutes
            },

            getTotalByCategory(category) {
                let total = 0;
                this.timesheets.filter(ts => ts.category == category).forEach(ts => total += ts.total)

                return convertToReadableTime(total)
            },

            getDateHours(category, date) {
                let minutes = this.timesheets.filter(ts => ts.category == category).find(ts => ts.date == date)?.total

                if (minutes) {
                    return convertToReadableTime(minutes)
                }

                return "0m"
            },

            async loadTimesheets() {
                this.loading = true
                this.timesheets = await timesheetApi.fetchTimesheets({
                    user_id: this.userId,
                    start_date: this.periodDates[0],
                    period: this.period
                })
                this.loading = false
            },

            async loadPeriodDates() {
                this.loading = true
                this.periodDates = await timesheetApi.fetchPeriodDates(this.date, this.period)
                this.loading = false
            },

            formatDate(date, format) {
                return moment(date).format(format)
            },

        },

        watch: {
            periodDates() {
                this.loadTimesheets()
            },

            period() {
                this.loadPeriodDates()
            }
        }
    }
</script>

<style>
    .timesheet-preview-table {
        @apply tw-w-full
    }

    .timesheet-preview-table th {
        font-weight: normal;
        @apply tw-px-6 tw-py-3 tw-bg-gray-100
    }

    .timesheet-preview-table td.hours {
        @apply tw-px-6 tw-py-3 tw-text-right
    }

    .header-date {
        width: 110px;
    }

    .header-total {
        @apply tw-relative
    }

    .header-total::after {
        content: ' ';
        position: absolute;
        left:0;
        bottom: -2px;
        width: 100%;
        height: 3px;
        @apply tw-bg-orange-500
    }
</style>
