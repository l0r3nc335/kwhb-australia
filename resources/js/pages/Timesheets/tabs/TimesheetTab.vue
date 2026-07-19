<template>
    <div class="tw-flex tw-flex-col">
        <div class="tw-flex tw-py-1 tw-justify-between">
            <div v-if="computePeriodDates.length > 0" class="tw-font-bold tw-text-[2rem]" style="font-family: sans-serif">
                <span class="tw-text-gray-400">Timesheet / </span>
                <span><span v-if="period===0">This week </span>{{formatDate(computePeriodDates[0], 'DD')}} - {{formatDate(computePeriodDates[6], 'DD MMM YYYY')}}</span>
                <template v-if="isReturned">
                    <span class="tw-cursor-pointer tw-ml-6 tw-text-[1.2rem] tw-px-4 tw-py-2 tw-bg-orange-500 tw-rounded-full tw-text-white">Returned</span>
                </template>
                <template v-if="isApproved">
                    <span class="tw-cursor-pointer tw-ml-6 tw-text-[1.2rem] tw-px-4 tw-py-2 tw-bg-green-100 tw-text-green-500 tw-rounded-full">Approved</span>
                </template>
                <div v-if="isReturned" class="tw-text-[.9rem] tw-font-bold tw-pt-4">
                    [Return Message] - <span class="tw-text-red-500">{{returnMessage}}</span>
                </div>
            </div>
            <div class="tw-flex">
                <div class="tw-w-[9rem] mr-2" v-if="teammates.length > 0">
                    <AppSelect :items="teammates" placeholder="Teammates" item-title="name" item-value="id"></AppSelect>
                </div>
                <div class="tw-flex  tw-rounded-lg tw-border tw-text-gray-600 tw-h-fit">
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
        <div class="tw-mt-1 tw-py-2 tw-flex tw-flex-col tw-w-full">
            <TimesheetTable :timesheets="timesheets" :categories="categories" @validated="v => validated = v" @delete="entry => deleteEntry(entry)" @timesheet-validated="v => timesheetsValidated = v"></TimesheetTable>
            <div class="tw-mt-10 tw-flex">
                <template v-if="hasPermissions('operational.timesheets.create')">
                    <TimesheetAddRow v-if="!currentWeekTimesheetSubmitted" :dates="availableDates" @selected="addTimesheetRow"></TimesheetAddRow>
                </template>
                <template v-if="hasPermissions(['operational.timesheet_templates.create', 'operational.timesheet_templates.update'])">
                    <VBtn v-if="!currentWeekTimesheetSubmitted"  color="primary" class="tw-mr-4" @click="copyLastWeekPreview">
                        <VIcon icon="tabler-files"/>
                        <span class="tw-ml-2">Copy Last Week</span>
                    </VBtn>
                    <TimesheetSaveTemplate :default-name="templateName" @save="name => saveTemplate(name)"></TimesheetSaveTemplate>
                </template>
            </div>
        </div>
        <TimesheetTemplates :templates="templates"
                            :categories="categories"
                            :timesheet-submitted="currentWeekTimesheetSubmitted"
                            class="tw-mt-4"
                            @reload-templates="loadTemplates"
                            @apply-template="data => applyTemplate(data)"
                            @update-template="data => updateTemplate(data)">
        </TimesheetTemplates>
        <div class="tw-mt-10 tw-flex tw-justify-end" v-if="!isApproved">
            <template v-if="!currentWeekTimesheetSubmitted || isReturned">
                <VDialog width="400" v-model="showSubmitDialog">
                    <template #activator="{props}">
                        <VBtn v-bind="props" color="success"  class="tw-mr-4" :disabled="!timesheetsValidated">
                            <template v-if="!isReturned" >Submit For Approval</template>
                            <template v-else>Resubmit for Approval</template>
                        </VBtn>
                    </template>
                    <DialogCloseBtn @click="showSubmitDialog=false" />
                    <div class="tw-p-4 tw-rounded-lg tw-bg-white tw-flex tw-flex-col">
                        <div class="tw-flex">
                            <VIcon icon="tabler-calendar-time" size="40" class="tw-mt-4 tw-mx-6 tw-text-blue-700"></VIcon>
                            <div class="tw-py-2 tw-mb-4 tw-text-[1.2rem]">Do you want to submit selected week's timesheet?</div>
                        </div>
                        <VBtn color="success" @click="submitTimesheets">
                            <VIcon icon="tabler-check" class="tw-mr-3"></VIcon>
                            Confirm
                        </VBtn>
                    </div>
                </VDialog>
                <template v-if="hasPermissions(['operational.timesheets.create', 'operational.timesheets.update'])">
                    <VBtn color="primary" @click="saveTimesheet" :disabled="!timesheetsValidated"><VIcon icon="tabler-check" class="tw-mr-2"></VIcon>Save</VBtn>
                </template>
            </template>
            <div v-else>
                <VBtn disabled color="warning" v-if="!isReturned">Pending</VBtn>
            </div>
        </div>
        <VDialog width="1460" v-model="showCopyLastWeekTemplate">
            <DialogCloseBtn @click="showCopyLastWeekTemplate=false" />
            <div class="tw-flex tw-flex-col">
                <TemplatePreview @apply-template="data => applyTemplate(data)"
                                 @close="showCopyLastWeekTemplate=false"
                                 :template="copyTemplate"
                                 :categories="categories"
                                 :view-mode="false"
                                 :apply-mode="true"
                                 :edit-mode="false">
                </TemplatePreview>
            </div>
        </VDialog>

    </div>
</template>
<script>
    import timesheetApi from '@/api/timesheet/timesheetApi.js';
import TemplatePreview from "@/pages/Timesheets/components/timesheets/TemplatePreview.vue";
import TimesheetAddRow from "@/pages/Timesheets/components/timesheets/TimesheetAddRow.vue";
import TimesheetSaveTemplate from "@/pages/Timesheets/components/timesheets/TimesheetSaveTemplate.vue";
import TimesheetTable from "@/pages/Timesheets/components/timesheets/TimesheetTable.vue";
import TimesheetTemplates from "@/pages/Timesheets/components/timesheets/TimesheetTemplates.vue";
import { useTimesheetStore } from "@/store/timesheetStore";
import moment from 'moment';
import { mapActions, mapState } from 'pinia';

    export default {
        components: {
            TimesheetTable,
            TimesheetAddRow,
            TimesheetTemplates,
            TimesheetSaveTemplate,
            TemplatePreview
        },

        data() {
            return {
                loading: false,
                timesheetsValidated: false,
                teammates: [],
                // Ordinary Hours
                default_category: null,
                // Current Period = 0
                period: 0,
                occupied_dates: [],
                // Copy Last Week
                showCopyLastWeekTemplate: false,
                copyTemplate: {
                    id: null,
                    name: null,
                    data: []
                },
                // Submit for Approval
                showSubmitDialog: false,
                validated: false
            }
        },

        computed: {
            currentWeekTimesheetSubmitted() {
                let submitted = false;
                let timesheets = this.timesheets

                for (let i in timesheets) {
                    if (timesheets[i].is_submitted == true && timesheets[i].is_returned != true) {
                        submitted = true
                    }
                }

                return submitted
            },

            formattedDates() {
                return this.computePeriodDates.map(date => date.format('YYYY-MM-DD'))
            },

            availableDates() {
                return this.formattedDates
            },

            computePeriodDates() {
                let period = this.period * 7
                let dates = [];
                for (let i = 0; i < 7; i++) {
                    dates.push(moment().startOf('week').add((3 + period + i), 'days'))
                }

                return dates
            },

            startDate() {
                return this.formatDate(this.computePeriodDates[0], 'YYYY-MM-DD')
            },

            filterPeriodString() {
                let string = 'Current Week'
                if (this.period !== 0) {
                    string = this.formatDate(this.computePeriodDates[0], 'MMM DD') + ' - ' + this.formatDate(this.computePeriodDates[6], 'MMMM DD')
                }
                return string
            },

            templateName() {
                return this.formatDate(this.computePeriodDates[0], 'MMM DD') + ' - ' + this.formatDate(this.computePeriodDates[6], 'MMMM DD')
            },

            isReturned() {
                return this.timesheets.length > 0 ? this.timesheets[0].is_returned : false
            },

            isApproved() {
                return this.timesheets.length > 0 ? this.timesheets[0].is_approved : false
            },

            returnMessage() {
                return this.timesheets.length > 0 ? this.timesheets[0].return_comment : null
            },

            ...mapState(useTimesheetStore, ['categories', 'timesheets', 'templates'])
        },

        mounted() {
            // this.loadResources()
            this.getOccupiedDates()
        },

        methods: {
           async getDateCategories(date) {
               return await timesheetApi.fetchDateCategories(date, this.authUser.id)
            },

            async addTimesheetRow(date) {
                let tsRow = {}
                tsRow.date = date
                tsRow.category = this.categories.find(c => c.id == this.default_category)
                tsRow.start = null
                tsRow.end = null
                tsRow.break = 60
                tsRow.comment = null
                tsRow.is_submitted = 0
                tsRow.is_returned = 0
                tsRow.categories = await this.getDateCategories(date)
                let timesheets = this.timesheets
                timesheets.push(tsRow)
                this.setTimesheets(timesheets.sort(function(a, b) {
                    let aDate = new Date(a.date)
                    let bDate = new Date(b.date)
                    if (aDate < bDate)
                        return -1
                    if (aDate > bDate)
                        return 1

                        return 0
                }))
            },

            async submitTimesheets() {
                this.loading = true
                await timesheetApi.submitTimesheets({start_date: this.timesheets[0]?.date})
                this.loading = false
                this.showSubmitDialog = false
                await this.loadTimesheets()
                this.$emit('reload')
            },

            async copyLastWeekPreview() {
                this.loading = true
                let period = this.period * 7

                let start_date = moment().startOf('week').add((2 + period - 7), 'days').format('YYYY-MM-DD')
                let timesheets = await timesheetApi.fetchTimesheets({
                    start_date: start_date
                })

                if (timesheets.length > 0) {
                    this.copyTemplate.data = timesheets
                    this.showCopyLastWeekTemplate = true
                } else {
                    this.$toast.error("No timesheet entries from last week!")
                }

                this.loading = false
            },

            async applyTemplate(data) {
                let startDate = this.computePeriodDates[0]
                this.loading = true
                await timesheetApi.applyTemplate(startDate, data.timesheets, this.authUser.id)
                this.loading = false
                await this.loadTimesheets()
                this.$emit('reload')
            },
            async updateTemplate(data) {
                this.loading = true
                await timesheetApi.updateTemplate(data.id, data.name, data.timesheets)
                this.loading = false
                await this.loadTemplates()
            },

            async deleteEntry(entry) {
                this.timesheets.splice(entry, 1)
                this.setTimesheets(this.timesheets)
            },

            loadResources() {
                this.loadTimesheets(true)
                this.loadTemplates()
                this.loadCategories()
            },

            async saveTemplate(name) {
                this.loading = true
                if (this.timesheets.length > 0) {
                    await timesheetApi.saveTemplate({
                        name: name,
                        timesheets: this.timesheets
                    })
                    await this.loadTemplates()
                } else {
                    this.$toast.error('No timesheet entries!')
                }
                this.loading = false
            },

            async saveTimesheet() {
                this.loading = true
                let startDate = this.formattedDates[0]
                await timesheetApi.deleteTimesheets(startDate)

                for (let i in this.timesheets) {
                    let entry = this.timesheets[i]
                    let cat = typeof entry.category === 'object' ? entry.category.id : entry.category

                    await timesheetApi.addTimesheet({
                        date: entry.date,
                        category: cat,
                        start: entry.start,
                        end: entry.end,
                        break: entry.break,
                        comment: entry.comment
                    })
                }

                this.$toast.success('Timesheet saved!')
                await this.loadResources()
            },

            async addTimesheet(data) {
                this.loading = true
                await timesheetApi.addTimesheet({
                    date: data?.date,
                    category: data?.category,
                    start: data?.start,
                    end: data?.end,
                    break: data?.break,
                    comment: data?.comment
                })
                this.$toast.success('Entry added!')
                this.loadTimesheets()
            },

            async loadTemplates() {
                this.loading = true
                let templates = await timesheetApi.fetchTemplates()
                this.setTemplates(templates)
                this.loading = false
            },

            async loadTimesheets(firstLoad = false) {
                this.loading = true
                let timesheets = await timesheetApi.fetchTimesheets({
                    start_date: this.startDate
                })
                this.setTimesheets(timesheets)
                if (firstLoad && timesheets.length === 0) {
                    for(let i in this.formattedDates) {
                        this.addTimesheetRow(this.formattedDates[i])
                    }
                }
                this.loading = false
            },

            async loadCategories() {
                this.loading = true
                let categories = await timesheetApi.fetchCategories()
                this.setCategories(categories)
                this.loading = false
            },

            formatDate(date, format) {
                return moment(date).format(format)
            },

            getOccupiedDates() {
                let occupied_dates = []

                for (let entry in this.timesheets) {
                    occupied_dates.push(this.timesheets[entry].date)
                }

                this.occupied_dates = occupied_dates
            },

            ...mapActions(useTimesheetStore, ['setCategories', 'setTimesheets', 'setTemplates'])
        },

        watch: {
            period() {
                this.loadTimesheets(true)
            },

            timesheets: {
                handler() {
                    this.getOccupiedDates()
                },
            },

            timesheetsValidated(val) {
                console.log(val)
            }
        }
    }
</script>
<style >
    button:disabled, button[disabled] {
        cursor: not-allowed;
    }
</style>
