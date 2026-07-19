<template>
    <div class="tw-flex tw-flex-col">
        <TimesheetUserWeekPeriods title="Pending Approval"
                                  status-string="Submitted"
                                  :periods="periods"
                                  :approve-mode="true"
                                  :show-timesheet-preview-actions="true"
                                  :show-approve-button="true"
                                  :show-job-title-selector="true"
                                  :show-role-selector="false"
                                  @reload="$emit('reload')"
                                  @timesheet-selected="showApproveAllButton = false"
                                  @timesheet-not-selected="showApproveAllButton = true"
                                  @return-timesheet="returnTimesheet"
                                  @approve-timesheet="approveTimesheet">
        </TimesheetUserWeekPeriods>
        <div class="tw-mt-6">
            <VBtn color="success" @click="showSubmitAllTimesheetDialog=true" v-if="showApproveAllButton">Approve All Timesheets</VBtn>
        </div>
        <VDialog v-model="showSubmitAllTimesheetDialog" width="400" >
            <DialogCloseBtn @click="showSubmitAllTimesheetDialog=false" />
            <div class="tw-bg-white tw-rounded-lg tw-px-8 tw-py-4 tw-flex tw-flex-col" >
                <div class="tw-text-[.95rem]">
                    Confirming that you are approving all submitted timesheets.
                </div>
                <div class="tw-mt-4 tw-flex tw-justify-end">
                    <VBtn size="small" color="success" @click="approveAllTimesheet">Approve All</VBtn>
                </div>
            </div>
        </VDialog>
    </div>
</template>
<script>
    import TimesheetUserWeekPeriods from "@/pages/Timesheets/components/timesheets/TimesheetUserWeekPeriods.vue"
    import timesheetApi from "@/api/timesheet/timesheetApi";

    export default {
        emits: ['reload'],

        components: {
            TimesheetUserWeekPeriods
        },

        props: {
            periods: {
                required: true,
                type: Array
            }
        },

        data() {
            return {
                loading: false,
                showSubmitAllTimesheetDialog: false,
                showApproveAllButton: true
            }
        },

        methods: {
            async approveAllTimesheet() {
                this.loading = true;
                await timesheetApi.approveTimesheetWeekPeriod()
                this.loading = true;
                this.showSubmitAllTimesheetDialog = false
                this.$emit('reload')
            },

            async returnTimesheet(data) {
                this.loading = true;
                await timesheetApi.returnTimesheetWeekPeriod(data.data.week_start, data.remarks)
                this.loading = true;
                this.$emit('reload')
            },

            async approveTimesheet(data) {
                this.loading = true;
                await timesheetApi.approveTimesheetWeekPeriod(data.week_start, data.user_id)
                this.loading = true;
                this.$emit('reload')
            }
        }
    }
</script>
