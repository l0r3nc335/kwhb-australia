<template>
    <div v-if="leaveDetails.length > 0" class="tw-flex tw-flex-col tw-rounded-lg tw-border">
        <div class="tw-flex tw-justify-center tw-py-2 tw-border-b">
            <div class="tw-font-bold">Leave Details</div>
        </div>
        <div class="tw-flex tw-flex-col tw-px-10 ">
            <div class="tw-flex tw-justify-center tw-py-3">
                Total Hours Requested: {{totalHoursRequested}}
            </div>
            <div v-for="leaveDetail in leaveDetails" class="tw-flex tw-flex-col tw-border-b">
                <div class="tw-flex tw-justify-between tw-py-3">
                    <div>{{formatDate(leaveDetail[0].date, 'MMM')}} {{formatDate(leaveDetail[0].date, 'YYYY')}}</div>
                    <div>Total Hours: {{getTotalHours(leaveDetail)}}</div>
                    <div class="tw-text-orange-500 hover:tw-text-orange-700 tw-text-[.9rem] tw-cursor-pointer">
                        <template v-if="expands.includes(formatDate(leaveDetail[0].date, 'MM-YYYY'))">
                            <div class="tw-flex" @click="expands.splice(expands.indexOf(formatDate(leaveDetail[0].date, 'MM-YYYY')), 1)">
                                <div>Collapse</div>
                                <VIcon icon="tabler-chevron-up"></VIcon>
                            </div>
                        </template>
                        <template v-else>
                            <div class="tw-flex" @click="expands.push(formatDate(leaveDetail[0].date, 'MM-YYYY'))">
                                <div>Expand</div>
                                <VIcon icon="tabler-chevron-down"></VIcon>
                            </div>
                        </template>
                    </div>
                </div>
                <table class="tw-w-full leave-detail-table" v-if="expands.includes(formatDate(leaveDetail[0].date, 'MM-YYYY'))">
                    <thead>
                        <tr class="tw-bg-gray-200">
                            <th style="width: 150px">Date</th>
                            <th style="width: 150px">Day</th>
                            <th style="width: 150px">Hours</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="lv in leaveDetail">
                            <td>{{formatDate(lv.date, 'DD/MM/YYYY')}}</td>
                            <td>{{formatDate(lv.date, 'dddd')}}</td>
                            <td>
                                <VTextField v-model="lv.total" style="width: 80px" type="number" :disabled="viewMode"></VTextField>
                            </td>
                            <td>
                                <VTextField v-model="lv.notes"  :disabled="viewMode"></VTextField>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</template>
<script>
    import moment from "moment";
    import leaveApi from "@/api/leave/leaveApi";

    export default {
        emits: ['updated-leave-details'],

        props: {
            viewMode: {
                type: Boolean,
                default: false
            },

            selectedLeave: {
                required: true,
                type: Object
            },

            start: {
                required: true,
            },
            end: {
                required: true,
            }
        },

        data() {
            return {
                leaveDetails: [],
                leaves: [],
                expands: []
            }
        },

        computed: {
            formattedLeaveDetails() {
                let details = []

                for (let i in this.leaveDetails) {
                    for (let j in this.leaveDetails[i]) {
                        details.push(this.leaveDetails[i][j])
                    }
                }

                return details;
            },

            totalHoursRequested() {
                let total = 0;

                for (let i in this.leaveDetails) {
                    for (let j in this.leaveDetails[i]) {
                        total += parseFloat(this.leaveDetails[i][j].total)
                    }
                }

                return total;
            }
        },

        mounted() {
            this.loadLeaveDetails()
            this.getLeaveDates()
        },

        methods: {
            async loadLeaveDetails() {
                if (this.selectedLeave) {
                    this.leaves = await leaveApi.fetchLeaveDetails(this.selectedLeave.user_id, this.selectedLeave.batch)
                }
            },

            getTotalHours(details) {
                let total = 0

                for (let i in details) {
                    total += parseFloat(details[i].total)
                }
                return total
            },

            getLeaveDates() {
                this.leaveDetails = []
                let dates = []
                let details = []

                if (this.start && this.end) {
                    let s = moment(this.start)
                    let e = moment(this.end)

                    if (s.isSameOrBefore(e)) {
                        for(let i = 0; i <= Math.abs(s.diff(e,'days')); i++) {
                            dates.push(s.clone().add(i, 'days').format('YYYY-MM-DD'))
                        }

                        for (let i in dates) {
                            let month = moment(dates[i]).format('M')
                            let leaveDetail = this.leaves.find(l => l.date == dates[i])
                            let detail = {
                                date: dates[i],
                                day_name: moment(dates[i]).format('dddd'),
                                total: leaveDetail ? leaveDetail.total : 7.5,
                                notes: leaveDetail ? leaveDetail.notes : null
                            }
                            if (details[month]) {
                                details[month].push(detail)
                            } else {
                                details[month] = [detail]
                            }
                        }

                        for (let i in details) {
                            this.leaveDetails.push(details[i])
                        }
                    }
                }

                this.$emit('updated-leave-details', this.formattedLeaveDetails)
            }
        },

        watch: {
            start() {
                this.getLeaveDates()
            },

            end() {
                this.getLeaveDates()
            }
        }
    }
</script>
<style>
    .leave-detail-table th, .leave-detail-table td {
        @apply tw-font-normal tw-text-left tw-py-2 tw-px-3 tw-text-[.8rem]
    }
</style>
