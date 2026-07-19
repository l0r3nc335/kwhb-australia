<template>
    <VCard class="tw-p-6">
        <div class="tw-font-bold tw-text-[1.2rem] tw-mb-7">
            Leave History
        </div>
        <div class="tw-flex tw-grid tw-grid-cols-7 tw-text-center tw-text-[.9rem] tw-gap-y-5">
            <template v-for="leave in leaves">
                <div >
                    <VIcon v-if="leave.status == 1" icon="tabler-check" class="tw-text-green-600"></VIcon>
                    <VIcon v-else icon="tabler-x" class="tw-text-red-600"></VIcon>
                </div>
                <div>
                    {{formatDate(leave.start_date, 'D MMM YYYY')}}
                </div>
                <div class="tw-col-span-3">
                    {{leave.leave_type_name}}
                </div>
                <div >
                    {{leave.total}} hours
                </div>
                <div>
                    <div class="tw-text-blue-600 tw-cursor-pointer hover:tw-text-blue-400" @click="showLeaveDetails(leave)">Details</div>
                </div>
            </template>
        </div>
        <div v-if="total > leaves.length" @click="loadLeaves(true)" class="tw-flex tw-justify-end tw-mt-8 tw-text-[.9rem] tw-text-blue-900 tw-cursor-pointer hover:tw-text-blue-500">
            <div>All Leave History <VIcon icon="tabler-arrow-right"></VIcon></div>
        </div>
        <VDialog v-model="showLeaveDetailsDialog" width="800">
            <div class="tw-flex tw-flex-col tw-p-6 tw-bg-white tw-shadow-lg tw-rounded-lg">
                <div class="tw-text-font-bold tw-text-[1.2rem] tw-text-blue-900">Leave Details</div>
                <div class="tw-flex tw-py-2">
                    {{formatDate(selectedLeave?.start_date, 'DD MMM YYYY')}} -
                    {{formatDate(selectedLeave?.end_date, 'DD MMM YYYY')}}
                </div>
                <LeaveDetails :view-mode="true" :selected-leave="selectedLeave" :start="selectedLeave?.start_date" :end="selectedLeave?.end_date"></LeaveDetails>
                <div class="tw-flex tw-justify-end tw-mt-4">
                    <VBtn size="small" color="secondary" @click="showLeaveDetailsDialog=false">Close</VBtn>
                </div>
            </div>
        </VDialog>
    </VCard>
</template>

<script>
    import leaveApi from "@/api/leave/leaveApi";
    import LeaveDetails from "@/pages/Leaves/forms/LeaveDetails.vue";

    export default {
        components: {
            LeaveDetails
        },

        props: {
            data: {
                required: true
            }
        },

        data() {
            return {
                leaves: [],
                total: 0,
                selectedLeave: null,
                showLeaveDetailsDialog: false
            }
        },

        mounted() {
            this.loadLeaves()
        },

        methods: {
            showLeaveDetails(leave) {
                this.selectedLeave = leave
                this.showLeaveDetailsDialog = true
            },

            async loadLeaves(showAll = false) {
                let data = await leaveApi.fetchLeaves({
                    user_id: this.data.user_id,
                    history_mode: true,
                    show_all: showAll
                })

                this.total = data?.total || 0

                if (showAll)
                    this.leaves = data?.leaves || []
                else
                    this.leaves = data?.leaves?.data || []
            }
        }
    }
</script>
