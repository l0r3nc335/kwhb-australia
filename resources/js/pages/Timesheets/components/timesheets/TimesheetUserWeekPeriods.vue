<template>
    <div class="tw-flex tw-flex-col">
        <div class="tw-flex tw-py-2 tw-justify-between" v-if="!selectedTimesheet">
            <div class="tw-text-[2.3rem] tw-font-bold" >{{title}}</div>
            <TimesheetFilters :selected-role="filters.role"
                              :selected-people="filters.employee"
                              :show-division-selector="showDivisionSelector"
                              :show-role-selector="showRoleSelector"
                              :show-employee-selector="showEmployeeSelector"
                              :show-teammate-selector="showTeammateFilter"
                              :show-job-titles-selector="showJobTitleSelector"
                              :show-week-period-selector="false"
                              :filters="filters">
            </TimesheetFilters>
        </div>
        <div class="tw-flex tw-flex-col tw-mt-4 tw-shadow" v-if="filteredPeriods.length > 0 && !selectedTimesheet">
            <div v-for="period in formattedData">
                <VTable>
                    <thead>
                    <tr class="tw-py-2 tw-bg-gray-100">
                        <th style="padding: 15px"><span class="tw-text-gray-900 tw-font-bold tw-capitalize tw-text-[1.1rem]">{{period.name}}</span></th>
                        <th class="tw-w-[10rem] tw-text-center tw-capitalize">
                            <div  class="tw-capitalize tw-font-bold tw-text-center tw-text-[1.1rem]">Total Hours</div>
                        </th>
                        <th v-if="!approveViewMode">
                        </th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="data in period.data">
                        <td>
                            <span class="tw-text-gray-600 tw-cursor-pointer hover:tw-text-gray-800" @click="selectedTimesheet = data">{{formatDate(data.week_start, 'DD MMMM YYYY')}} - {{formatDate(data.week_end, 'DD MMMM YYYY')}}</span>
                        </td>
                        <td class="tw-text-center">
                            <b>{{convertToReadableTime(data.total)}}</b>
                        </td>
                        <td class="tw-w-[5rem] tw-text-center" v-if="!approveViewMode">
                            <div class="tw-text-center">
                                <div v-if="data.submitted && !data.returned" class="tw-py-1 tw-px-4 tw-rounded-full tw-bg-yellow-500 tw-font-bold">Submitted</div>
                                <div v-else-if="data.submitted && data.returned" class="tw-py-1 tw-px-4 tw-rounded-full tw-bg-orange-500 tw-text-white tw-font-bold">Returned</div>
                                <div v-else-if="!data.submitted && !data.returned" class="tw-py-1 tw-px-4 tw-rounded-full tw-bg-gray-200 tw-font-bold ">Unsubmitted</div>
                                <div v-else-if="!data.submitted && data.returned">Returned</div>
                            </div>
                        </td>
                        <td  class="tw-w-[5rem]">
                            <IconBtn>
                                <VIcon icon="tabler-dots-vertical" />
                                <VMenu
                                    activator="parent"
                                >
                                    <VList>
                                        <VListItem class="tw-cursor-pointer hover:tw-bg-gray-100" @click="selectedTimesheet = data">View</VListItem>
                                        <VListItem v-if="approveMode" class="tw-cursor-pointer hover:tw-bg-green-100" @click="openApproveDialog(data)">Approve</VListItem>
                                    </VList>
                                </VMenu>
                            </IconBtn>
                        </td>
                    </tr>
                    </tbody>
                </VTable>
            </div>
        </div>
        <div v-else>
            <TimesheetPreview v-if="selectedTimesheet"
                              :data="selectedTimesheet"
                              :status-string="statusString"
                              :user-id="selectedTimesheet.user_id"
                              :date="selectedTimesheet.week_start"
                              :show-actions="isManager"
                              :show-approve-button="showApproveButton"
                              @period-changed="p => filters.period = p"
                              @back="selectedTimesheet = null; $emit('reload')">
            </TimesheetPreview>
            <span v-else>No data found.</span>
        </div>
        <VDialog v-model="showApproveDialog" width="400">
            <DialogCloseBtn @click="showApproveDialog=false" />
            <div class="tw-flex tw-flex-col tw-bg-white tw-rounded-lg tw-relative tw-overflow-hidden">
                <div class="tw-flex tw-flex-col tw-w-full tw-p-3 tw-bg-gray-200 tw-text-[1.4rem]">
                    <div>Approve Timesheet</div>
                    <div class="tw-text-[.8rem]">{{formatDate(selectedApproveEntry.week_start, 'MMMM DD, YYYY')}} - {{formatDate(selectedApproveEntry.week_end, 'MMMM DD, YYYY')}}</div>
                </div>
                <div class="tw-p-4 tw-flex tw-w-full">
                    Confirming that you are approving this timesheet.
                </div>
                <div class="tw-px-4 tw-mb-2 tw-flex tw-w-full tw-justify-end">
                    <VBtn color="success" @click="approveTimesheetWeek">Confirm</VBtn>
                </div>
            </div>
        </VDialog>
        <VDialog v-model="showReturnDialog" width="400">
            <DialogCloseBtn @click="showReturnDialog=false" />
            <div class="tw-flex tw-flex-col tw-bg-white tw-rounded-lg tw-relative tw-overflow-hidden">
                <div class="tw-flex tw-flex-col tw-w-full tw-p-3 tw-bg-gray-200 tw-text-[1.4rem]">
                    <div>Return Timesheet</div>
                    <div class="tw-text-[.8rem]">{{formatDate(selectedReturnEntry.week_start, 'MMMM DD, YYYY')}} - {{formatDate(selectedReturnEntry.week_end, 'MMMM DD, YYYY')}}</div>
                </div>
                <div class="tw-p-4 tw-flex tw-w-full">
                    <VTextField placeholder="Enter Issue" v-model="returnRemark" required></VTextField>
                </div>
                <div class="tw-px-4 tw-mb-2 tw-flex tw-w-full tw-justify-end">
                    <VBtn :disabled="!returnRemark" @click="returnTimesheetWeek">Return</VBtn>
                </div>
            </div>
        </VDialog>
        <VDialog v-model="showTemplatePreview" width="1400">
            <DialogCloseBtn @click="showTemplatePreview=false" />
            <TemplatePreview :template="selectedTemplateData" :view-mode="true" :categories="categories"></TemplatePreview>
        </VDialog>
        <div class="tw-flex tw-mt-4 tw-justify-end" v-if="!selectedTimesheet && showBackButton">
            <VBtn variant="outlined" color="default" @click="$emit('back')">Back</VBtn>
        </div>
    </div>
</template>
<script>
    // libraries
    import moment from "moment";
    import {mapState} from "pinia";
    // stores
    import {useTimesheetStore} from "@/store/timesheetStore.js";
    // components
    import TemplatePreview from "@/pages/Timesheets/components/timesheets/TemplatePreview.vue";
    import TimesheetFilters from "@/pages/Timesheets/components/TimesheetFilters.vue";
    import TimesheetPreview from "@/pages/Timesheets/components/timesheets/TimesheetPreview.vue";

    export default {
        emits: ['return-timesheet', 'approve-timesheet', 'timesheet-selected', 'timesheet-not-selected', 'reload', 'back'],

        components: {
            TemplatePreview,
            TimesheetFilters,
            TimesheetPreview
        },

        props: {
            editTimesheetEntries: {
                type: Boolean,
                default: false
            },

            showTeammateFilter: {
                type: Boolean,
                default: false
            },

            showBackButton: {
                type: Boolean,
                default: false
            },

            periodSelected: {
                type: String,
                default: null
            },

            showDotMenuActions: {
                type: Boolean,
                default: true
            },

            statusString: {
                type: String,
                default: null
            },
            title: {
                required: true,
                type: String
            },
            periods: {
                required: true,
                type: Array
            },
            approveMode: {
                type: Boolean,
                default: false
            },
            approveViewMode: {
                type: Boolean,
                default: false
            },

            showJobTitleSelector: {
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

            showTimesheetPreviewActions: {
                type: Boolean,
                default: false
            },

            showApproveButton: {
                type: Boolean,
                default: false
            }
        },

        data() {
            return {
                showTemplatePreview: false,
                showReturnDialog: false,
                showApproveDialog: false,
                formattedData: [],
                selectedReturnEntry: null,
                selectedApproveEntry: null,
                returnRemark: null,
                selectedTemplateData: {
                    id: null,
                    name: null,
                    data: []
                },
                filters: {
                    division: null,
                    role: null,
                    employee: null,
                    period: null
                },
                selectedTimesheet: null,
            }
        },

        computed: {
            filteredPeriods() {
                let filteredPeriods = this.periods

                if (this.filters.employee) {
                    filteredPeriods = filteredPeriods.filter(fp => fp.employee_id == this.filters.employee.id)
                }

                if (this.filters.division) {
                    filteredPeriods = filteredPeriods.filter(fp => fp.division_id == this.filters.division.id)
                }

                if (this.filters.job_title) {
                    filteredPeriods = filteredPeriods.filter(fp => fp.job_title == this.filters.job_title)
                }

                if (this.filters.role) {
                    filteredPeriods = filteredPeriods.filter(fp => {
                        let roles = fp.roles.split(',').map(i => parseInt(i))
                        return roles.includes(this.filters.role.id)
                    })
                }

                if (this.filters.period) {
                    filteredPeriods = filteredPeriods.filter(fp => fp.week_start == this.filters.period)
                }

                return filteredPeriods
            },

            ...mapState(useTimesheetStore, ['categories'])
        },

        mounted() {
            console.log(this.periods)
            this.getFormattedPeriods()

            this.filters.period = this.periodSelected
        },

        methods: {
            openTemplatePreviewDialog(entry) {
                this.selectedTemplateData.data = JSON.parse(entry.entries)
                this.showTemplatePreview = true
            },

            openApproveDialog(entry) {
                this.selectedApproveEntry = entry
                this.showApproveDialog = true
            },

            openReturnDialog(entry) {
                this.selectedReturnEntry = entry
                this.showReturnDialog = true
            },

            returnTimesheetWeek() {
                this.$emit('return-timesheet', {
                    data: this.selectedReturnEntry,
                    remarks: this.returnRemark
                })
                this.selectedReturnEntry = false
                this.showReturnDialog = false
            },

            approveTimesheetWeek() {
                this.$emit('approve-timesheet', this.selectedApproveEntry)
                this.selectedReturnEntry = false
                this.showApproveDialog = false
            },

            getFormattedPeriods() {
                let formattedPeriods = []
                let data = [];

                for (let i in this.filteredPeriods) {
                    if (data[this.filteredPeriods[i]?.name]) {
                        data[this.filteredPeriods[i].name].push(this.filteredPeriods[i])
                    } else {
                        data[this.filteredPeriods[i].name] = [
                            this.filteredPeriods[i]
                        ];
                    }
                }

                for (let i in data) {
                    let fp = {}
                    fp.name = i
                    fp.data =  data[i];

                    formattedPeriods.push(fp)
                }

                this.formattedData = formattedPeriods
            },

            parseEntries(entries) {
                return JSON.parse(entries)
            },

            formatDate(date, format) {
                return moment(date).format(format)
            },

            convertToReadableTime(minutes) {
                let totalString = "";
                let hours = Math.floor(minutes / 60);
                minutes = minutes - (hours * 60)

                if (hours > 0) {
                    totalString += `${hours}h`
                }

                totalString += ` ${minutes}m`

                return totalString
            },
        },

        watch: {
            periods() {
                this.getFormattedPeriods()
            },

            filters: {
                handler() {
                    this.getFormattedPeriods()
                },
                deep: true
            },

            selectedTimesheet() {
                if (this.selectedTimesheet) {
                    this.$emit('timesheet-selected')
                } else {
                    this.$emit('timesheet-not-selected')
                }
            }
        }
    }
</script>
