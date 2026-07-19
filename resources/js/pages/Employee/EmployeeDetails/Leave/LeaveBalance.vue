<template>
    <VCard class="tw-p-6 leave-calculator-wrapper" style="overflow: initial">
        <div class="tw-text-[1.5rem] tw-font-bold tw-mb-3">Leave Balance</div>
        <div class="tw-py-4">
            <AppCombobox style="width: 280px"
                         placeholder="Leave Type"
                         v-model="leaveType"
                         :items="leaveTypes"
                         item-title="name"
                         item-value="id"
                         clearable>
            </AppCombobox>
        </div>
        <div class="tw-flex tw-py-3">
            <div class="tw-flex tw-w-full">
                <div style="background-color: #f9f9f9" class="tw-flex tw-justify-center tw-flex-col tw-p-4 tw-px-9 tw-w-1/2  tw-bg-gray-100 tw-mr-4 tw-rounded-lg tw-font-bold">
                    <div class="tw-flex tw-w-full tw-mb-3">
                        <div class="tw-text-[1rem]">Current Balance</div>
                    </div>
                    <div class="tw-flex tw-w-full">
                        <div class="tw-text-[1.4rem] tw-text-blue-900 tw-text-center">{{currentBalanceHours}} Hours ({{leaveBalance}} days)</div>
                    </div>
                </div>
                <div style="background-color: #f9f9f9"  class="tw-flex  tw-flex-col tw-p-4  tw-w-1/2 tw-bg-gray-100 tw-mr-4 tw-rounded-lg tw-font-bold">
                    <div class="tw-flex tw-w-full tw-mb-2">
                        <div class="tw-text-[1rem] tw-text-center">Current Requested Leave</div>
                    </div>
                    <div class="tw-flex tw-w-full ">
                        <div class="tw-text-[1.4rem] tw-text-blue-900">{{currentRequestedLeaveBalance}} Hours ({{currentRequestedLeaveDays}} days)</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tw-mt-4">
            <div class="tw-text-[1.5rem] tw-font-bold tw-mb-3">Leave Calculator</div>
        </div>
        <div class="tw-flex tw-grid tw-grid-cols-3 tw-gap-4">
            <VueDatePicker v-model="dateStart" :auto-apply="true" format="dd/MM/yyyy" placeholder="Date From"></VueDatePicker>
            <VueDatePicker v-model="dateEnd" :min-date="minDate" :auto-apply="true" format="dd/MM/yyyy" placeholder="Date To" :disabled="!dateStart"></VueDatePicker>
            <VBtn color="kwhb" @click="calculateProjectedBalance" :disabled="!dateStart || !dateEnd">Calculate</VBtn>
        </div>
        <div class="tw-py-4">
            <div style="background-color: #f9f9f9"  class="tw-flex  tw-flex-col tw-p-4  tw-w-1/2 tw-bg-gray-100 tw-mr-4 tw-rounded-lg tw-font-bold">
                <div class="tw-flex tw-w-full tw-mb-2">
                    <div class="tw-text-[1rem] tw-text-center">Projected Balance</div>
                </div>
                <div class="tw-flex tw-w-full ">
                    <div class="tw-text-[1.4rem] tw-text-blue-900">{{projectedBalanceHours}} Hours ({{projectedBalanceDays}} days)</div>
                </div>
            </div>
        </div>
    </VCard>
</template>
<script>
import leaveApi from "@/api/leave/leaveApi";
import moment from "moment";

export default {
    props: {
        data: {
            required: true
        },

        requestedLeaves: {
            default: []
        }
    },

    data() {
        return {
            leaveType: null,
            leaveTypes: [],
            leaveCredits: [],
            dateStart: null,
            dateEnd: null,
            projectedBalanceHours: 0
        }
    },

    mounted() {
        this.loadLeaveTypes()
        this.loadCredits()
    },

    computed: {
        currentRequestedLeaveDays() {
            let total = 0
            if (this.requestedLeaves && this.requestedLeaves.length > 0) {
                for(let i in this.requestedLeaves) {
                    if(this.leaveType && this.requestedLeaves[i].type == this.leaveType.id) {
                        total += Math.abs(moment(this.requestedLeaves[i].start_date).diff(moment(this.requestedLeaves[i].end_date), 'days')) + 1
                    } else if (!this.leaveType) {
                        total += Math.abs(moment(this.requestedLeaves[i].start_date).diff(moment(this.requestedLeaves[i].end_date), 'days')) + 1
                    }
                }
            }

            return total
        },

        currentRequestedLeaveBalance() {
            let total = 0
            if (this.requestedLeaves && this.requestedLeaves.length > 0) {
                for(let i in this.requestedLeaves) {
                    if(this.leaveType && this.requestedLeaves[i].type == this.leaveType.id) {
                        total += this.requestedLeaves[i].total
                    } else if (!this.leaveType) {
                        total += this.requestedLeaves[i].total
                    }
                }
            }

            return total
        },

        currentBalanceHours() {
            if (this.leaveType && this.leaveCredits.length > 0) {
                let balance = this.leaveCredits.find(lc => lc.id === this.leaveType.id);

                if (balance) {
                    return balance.credits
                }
            }

            return 0;
        },

        leaveBalance() {
            return this.currentBalanceHours / 8
        },

        projectedBalanceDays() {
            return this.projectedBalanceHours / 8
        },

        leaveDaysCount() {
            return 0;
        },

        minDate() {
            if (this.dateStart) {
                return new Date(this.dateStart)
            }

            return null
        },
    },

    methods: {
        calculateProjectedBalance() {
            if (this.dateStart && this.dateEnd) {
                let total = Math.abs(moment(this.dateStart).diff(moment(this.dateEnd), 'days')) + 1
                this.projectedBalanceHours = this.currentBalanceHours - (total * 8)
            }
        },

        async loadLeaveTypes() {
            this.leaveTypes = await leaveApi.fetchLeaveTypes(this.data.user_id)
        },

        async loadCredits() {
            this.leaveCredits = await leaveApi.fetchCredits(this.data.user_id)
        },
    },

    watch: {
        dateStart() {
            this.dateEnd = null
        }
    }
}
</script>

<style>
.leave-calculator-wrapper input.dp__pointer.dp__input_readonly.dp__input.dp__input_icon_pad.dp__input_reg, .leave-calculator-wrapper input.dp__disabled.dp__input_readonly.dp__input.dp__input_icon_pad.dp__input_reg {
    border-radius: 7px !important;
    padding: 10px 40px;
}

.leave-calculator-wrapper button.v-btn.v-btn--elevated.v-theme--light.bg-kwhb.v-btn--density-default.v-btn--size-default.v-btn--variant-elevated {
    height: 45px;
}
</style>
