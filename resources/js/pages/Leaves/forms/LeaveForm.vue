<template>
    <div ref="leaveForm" class="tw-flex tw-flex-col tw-bg-white tw-rounded-lg tw-py-10 tw-px-12">
        <div class="tw-flex tw-font-bold tw-text-[1.9rem] tw-justify-center tw-w-full">
            <div>{{title}}</div>
        </div>
        <div class="tw-flex-col tw-w-full tw-mt-14">
            <div class="tw-flex tw-justify-between tw-mb-8">
                <div class="tw-py-4 tw-mr-6">
                    <template v-if="userId">
                        <div class="tw-flex tw-flex-col">
                            <div class="tw-text-[.9rem]">Full Name</div>
                            <div class="tw-px-6 tw-pt-4 tw-text-gray-500">
                                {{selectedEmployee?.name}}
                            </div>
                        </div>
                    </template>
                    <AppCombobox v-else
                                 label="Full Name"
                                 :disabled="viewMode"
                                 v-model="employee"
                                 :items="employees"
                                 item-title="name"
                                 item-value="user_id"
                                 placeholder="Skye Muniz"
                                 prepend-inner-icon="tabler-search"
                                 style="width: 300px"></AppCombobox>
                </div>
                <div class="tw-flex tw-w-full tw-px-4" v-if="showCreditBalance">
                    <div class="tw-flex tw-justify-center tw-flex-col tw-p-4 tw-w-1/2  tw-bg-gray-100 tw-mr-4 tw-rounded-lg tw-font-bold">
                        <div class="tw-flex tw-w-full tw-justify-center tw-mb-3">
                            <div class="tw-text-[.8rem]">Current Balance</div>
                        </div>
                        <div class="tw-flex tw-w-full tw-justify-center">
                            <div class="tw-text-[1.1rem] tw-text-blue-900 tw-text-center">{{currentBalanceHours}} Hours ({{leaveBalance}} days)</div>
                        </div>
                    </div>
                    <div class="tw-flex tw-justify-center tw-flex-col tw-p-4 tw-w-1/2  tw-bg-gray-100 tw-mr-4 tw-rounded-lg tw-font-bold">
                        <div class="tw-flex tw-w-full tw-justify-center tw-mb-3">
                            <div class="tw-text-[.8rem] tw-text-center">Projected Leave Balance</div>
                        </div>
                        <div class="tw-flex tw-w-full tw-justify-center">
                            <div class="tw-text-[1.1rem] tw-text-blue-900">{{projectedBalanceHours}} Hours ({{leaveDaysCount}} days)</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tw-grid tw-grid-cols-2 tw-gap-8 tw-mb-6">
                <AppCombobox label="Leave Type"
                             placeholder="Select"
                             v-model="leaveType"
                             :items="leaveTypes"
                             item-title="name"
                             item-value="id"
                             :disabled="viewMode"
                             clearable>
                </AppCombobox>
                <AppCombobox label="Approvers"
                             v-model="leaveApprovers"
                             placeholder="Select"
                             :items="approvers"
                             item-title="name"
                             item-value="user_id"
                             chips
                             clearable
                             :disabled="viewMode">
                </AppCombobox>
            </div>
            <div class="tw-grid tw-grid-cols-2 tw-gap-8">
                <div class="tw-flex-col">
                    <div class="tw-text-[.85rem]" style="margin-bottom: 6px">Start</div>
                    <VueDatePicker v-model="dateStart" :auto-apply="true" format="dd/MM/yyyy" placeholder="1 January 2024" :disabled="viewMode"></VueDatePicker>
                </div>
                <div class="tw-flex-col">
                    <div class="tw-text-[.85rem]" style="margin-bottom: 6px">End</div>
                    <VueDatePicker v-model="dateEnd"  :auto-apply="true" format="dd/MM/yyyy" :min-date="minDate" placeholder="1 January 2026" :disabled="viewMode || !dateStart"></VueDatePicker>
                </div>
            </div>
            <div v-if="!isSuperUser && !selectedLeave && insufficientBalance" class="tw-flex tw-py-4 tw-text-red-500 tw-text-[.7rem]">
                <VIcon icon="tabler-alert-triangle" class="tw-mr-2"></VIcon>
                <template v-if="!showExceptionButton">
                    Oops! You don't have enough accrued leave for this leave request.
                </template>
                <template v-else>
                    This Leave Request requires an endorsement from your Line Manager and final approval from the CEO. Request exception via the button below.
                </template>
            </div>
            <div class="tw-flex-col tw-mt-8">
                <AppTextarea v-model="reason" label="Reason for Leave" placeholder="Type reason here" :disabled="viewMode"></AppTextarea>
                <div class="tw-mt-6 tw-flex-col">
                    <div class="tw-text-[.85rem]">Attach Supporting Document</div>
                    <div v-if="!attachmentName && !selectedLeave?.attachment" @click="$refs.leaveAttachment.click()" class="tw-relative tw-flex tw-flex-col tw-border tw-border-dashed tw-mt-2 tw-w-1/2 tw-h-40 tw-justify-center tw-rounded tw-cursor-pointer hover:tw-bg-gray-50">
                        <input ref="leaveAttachment" type="file" class="tw-w-px tw-h-px tw-opacity-0 tw-overflow-hidden tw-absolute" @change="setAttachment">
                        <div  class="tw-flex tw-w-full tw-justify-center">
                            <div class="tw-flex tw-flex-col">
                                <div class="tw-flex tw-justify-center tw-mb-6">
                                    <div class="tw-rounded tw-bg-gray-200 tw-w-fit tw-px-2 tw-py-1">
                                        <VIcon icon="tabler-upload"></VIcon>
                                    </div>
                                </div>
                                <div>
                                    Drop files here or click to upload.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="tw-text-blue-500 tw-mt-1 hover:tw-text-red-500 tw-cursor-pointer">
                        <template v-if="selectedLeave?.attachment">
                            <a :href="`/` + selectedLeave.attachment">
                                <u>{{selectedLeave.attachment_name}}</u>
                            </a>
                        </template>
                        <template v-else>
                            <div @click="attachment=null;attachmentName=null">
                                <u>{{attachmentName}}</u>
                            </div>
                        </template>
                    </div>
                </div>
                <LeaveDetails :start="dateStart" :end="dateEnd" :view-mode="viewMode" class="tw-mt-10" :selected-leave="selectedLeave" @updated-leave-details="details => leaveDetails = details"></LeaveDetails>
            </div>
            <template v-if="hasPermissions(['people.leave_management_leave_request.update', 'people.employee_leave.update'], selectedEmployee, true)">
                <div v-if="(!selectedLeave || selectedLeave.status_name === 'Pending Approval')" class="tw-flex tw-mt-16 tw-justify-center">
                    <template v-if="!isSuperUser && projectedBalanceHours < 0 && !showExceptionButton">
                        <div class="tw-text-[.8rem] tw-text-center tw-text-red-500">Oops! You don't have enough accrued leave for this leave request. You can apply for Leave Without Pay, and this will require an endorsement from your Line Manager and final approval from the CEO.</div>
                    </template>
                    <template v-else>
                        <div v-if="viewMode === false && editMode === false" class="tw flex">
                            <VBtn color="secondary" class="tw-mr-5" @click="$emit('close')">Cancel</VBtn>
                            <VBtn v-if="showExceptionButton" :disabled="!formValidated" color="secondary" variant="outlined" :class="[!formValidated ? 'tw-cursor-not-allowed' : '']" class="tw-mr-5"  @click="submitLeave(true)">Request Exception</VBtn>
                            <VBtn :disabled="!formValidated || showExceptionButton" :class="[!formValidated ? 'tw-cursor-not-allowed' : '']" @click="submitLeave">Submit</VBtn>
                        </div>
                        <div v-if="viewMode === false && editMode === true" class="tw flex">
                            <VBtn color="secondary" class="tw-mr-5" @click="$emit('close')">Cancel</VBtn>
                            <VBtn v-if="showExceptionButton && !selectedLeave?.request_exemption" :disabled="!formValidated" color="secondary" variant="outlined" :class="[!formValidated ? 'tw-cursor-not-allowed' : '']" class="tw-mr-5"  @click="submitLeave(true)">Request Exception</VBtn>
                            <VBtn :disabled="!formValidated" class="tw-mr-4"  @click="updateLeave">Update</VBtn>
                            <VBtn v-if="selectedLeave && employee && (hasPermissions('people.leave_management_leave_request.approve', employee) || hasPermissions('people.employee_leave.approve', employee)) && ((!inRoles(['CEO', 'General Manager']) && !selectedLeave.is_endorsed)|| (inRoles(['CEO', 'General Manager']) && !selectedLeave.is_approved))" color="success" @click="approveLeave">
                                 <span v-if="inRoles(['CEO', 'General Manager'])">
                                    Approve
                                </span>
                                <span v-else>
                                    Endorse
                                </span>
                            </VBtn>
                        </div>
                    </template>
                    <div v-if="viewMode === true && editMode === false" class="tw flex">
                        <VBtn color="secondary" class="tw-mr-2" @click="$emit('close')">Close </VBtn>
                        <VBtn v-if="isAuthUserApprover" color="default" class="tw-mr-2"  variant="outlined" @click="cancelLeave">Cancel</VBtn>
                        <VBtn v-if="isAuthUserApprover" color="default" class="tw-mr-2"  variant="outlined" @click="declineLeave">Decline</VBtn>
                        <VBtn v-if="selectedLeave && employee && (hasPermissions('people.leave_management_leave_request.approve', employee) || hasPermissions('people.employee_leave.approve', employee)) && ((!inRoles(['CEO', 'General Manager']) && !selectedLeave.is_endorsed)|| (inRoles(['CEO', 'General Manager']) && !selectedLeave.is_approved))" color="success" @click="approveLeave">
                             <span v-if="inRoles(['CEO', 'General Manager'])">
                                    Approve
                                </span>
                            <span v-else>
                                    Endorse
                                </span>
                        </VBtn>
                    </div>
                </div>
            </template>
        </div>
        <VDialog v-model="showRequestExemptionSuccessDialog" width="550">
            <div class="tw-flex tw-flex-col tw-bg-white tw-p-6 tw-rounded-lg">
                <div class="tw-flex tw-justify-end">
                    <div class="tw-py-2 tw-px-3 tw-rounded-lg tw-bg-gray-200" @click="showRequestExemptionSuccessDialog = false">
                        <VIcon icon="tabler-x"></VIcon>
                    </div>
                </div>
                <div class="tw-flex tw-justify-center">
                    <div class="tw-p-4 tw-rounded-full tw-my-8" style="background-color: #b0e1bb">
                        <VIcon icon="tabler-check" class="tw-text-[4rem] tw-text-emerald-500"></VIcon>
                    </div>
                </div>
                <div class="tw-flex tw-justify-center tw-font-bold tw-text-[2rem]">
                    Request Submitted
                </div>
                <div class="tw-flex tw-text-center tw-py-6 tw-px-16">
                    This leave request requires an endorsement from your Line Manager and approval from the CEO.
                </div>
                <div class="tw-flex tw-justify-center tw-my-5">
                    <VBtn @click="showRequestExemptionSuccessDialog = false; $emit('reload-leaves');$emit('close')" class="tw-px-4">OK</VBtn>
                </div>
            </div>
        </VDialog>
    </div>
</template>

<script>
    import authMixin from "@/mixins/authMixin";
    import leaveApi from "@/api/leave/leaveApi";
    import employeeApi from "@/api/employee/employeeApi";
    import moment from "moment";
    import LeaveDetails from "@/pages/Leaves/forms/LeaveDetails.vue";

    export default {
        components: {
            LeaveDetails
        },

        emits: ['close', 'reload-leaves'],

        mixins: [authMixin],

        props: {
            requestPermission: {
                type: String,
                default: 'requestPermission'
            },

            userId: {
                type: Number,
                default: null
            },

            title: {
                type: String,
                default: 'Apply For Leave'
            },

            selectedLeave: {
                type: Object,
                default: null
            },

            editMode: {
                type: Boolean,
                default: false
            },

            viewMode: {
                type: Boolean,
                default: false
            }
        },

        data() {
            return {
                employee: null,
                leaveCredits: [],
                leaveType: null,
                leaveApprovers: [],
                dateStart: null,
                dateEnd: null,
                reason: null,
                employees: [],
                leaveTypes: [],
                leaveDetails: [],
                approvers: [],
                attachment: null,
                attachmentName: null,
                showRequestExemptionSuccessDialog: false,
            }
        },

        mounted() {
            if (this.userId) {
                this.loadLeaveTypes(this.userId)
            }

            this.loadResources()
        },

        computed: {
            showExceptionButton() {
                // LWOP, Parental Leave and Long Service
                return [3, 8, 11].includes(this.leaveType?.id)
            },

            selectedEmployee() {
                if (this.userId) {
                    return this.employees.find(es => es.user_id == this.userId)
                }

                return this.employee
            },

            minDate() {
                if (this.dateStart) {
                    return new Date(this.dateStart)
                }

                return null
            },

            isAuthUserApprover() {
                return this.selectedLeave.approvers.find(u => u.user_id === this.user.id) || this.inRoles(['CEO'])
            },

            insufficientBalance() {
                return this.projectedBalanceHours < 0 && this.leaveType?.id == 3
            },

            leaveBalance() {
                if (this.leaveType && this.leaveCredits.length > 0) {
                    let balance = this.leaveCredits.find(lc => lc.id === this.leaveType.id);

                    if (balance) {
                        return parseFloat(balance.credits)
                    }
                }

                return 0;
            },

            leaveDaysCount() {
                if (this.dateStart && this.dateEnd) {
                    return - (Math.abs(moment(this.dateStart).diff(moment(this.dateEnd), 'days')) + 1)
                }

                return 0;
            },

            currentBalanceHours() {
                return this.leaveBalance * 8;
            },

            projectedBalanceHours() {
                let subTotal = 0
                if (this.leaveDetails.length > 0) {
                    for(let i in this.leaveDetails) {
                        subTotal+= parseFloat(this.leaveDetails[i].total)
                    }
                }

                return this.currentBalanceHours - subTotal;
            },

            showCreditBalance() {
                return (this.employee || this.selectedEmployee) && this.leaveType
            },

            formValidated() {
                return (this.employee || this.selectedEmployee) && this.leaveType && this.leaveApprovers && this.dateStart && this.reason &&this.dateEnd && this.approvers.length > 0
            }
        },

        methods: {
            setAttachment(e) {
                let files = e.target.files || e.dataTransfer.files;
                if (files.length > 0) {
                    this.attachment = files[0]
                    this.attachmentName = files[0].name
                }
            },

            async approveLeave() {
                let loader  = this.$loading.show({
                    container: this.$refs.leaveForm
                })
                let form = {
                    batch: this.selectedLeave.batch,
                    user_id: this.employee.user_id,
                    type: this.selectedLeave.type
                }

                await leaveApi.leaveApprove(form).then(res => {
                    loader.hide()
                    this.$emit('reload-leaves')
                    this.$emit('close')
                }).catch(err => {
                    loader.hide()
                    this.$toast.error('Something went wrong while approving the leave request.')
                    console.log(err.data)
                    this.$emit('close')
                })
            },

            async declineLeave() {
                let loader  = this.$loading.show({
                    container: this.$refs.leaveForm
                })
                let form = {
                    batch: this.selectedLeave.batch,
                    user_id: this.employee.user_id,
                }

                await leaveApi.declineLeave(form).then(res => {
                    loader.hide()
                    this.$emit('reload-leaves')
                    this.$emit('close')
                }).catch(err => {
                    loader.hide()
                    this.$toast.error('Something went wrong while declining the leave request.')
                    console.log(err.data)
                    this.$emit('close')
                })
            },

            async cancelLeave() {
                let loader  = this.$loading.show({
                    container: this.$refs.leaveForm
                })
                let form = {
                    batch: this.selectedLeave.batch,
                    user_id: this.employee.user_id,
                }
                await leaveApi.cancelLeave(form).then(res => {
                    loader.hide()
                    this.$emit('reload-leaves')
                    this.$emit('close')
                }).catch(err => {
                    loader.hide()
                    this.$toast.error('Something went wrong while canceling the leave request.')
                    console.log(err.data)
                    this.$emit('close')
                })
            },

            loadSelectedLeave() {
                if (this.selectedLeave) {
                    this.employee = this.employees.find(e => e.user_id === this.selectedLeave.user_id)
                    this.leaveType = this.leaveTypes.find(l => l.id === this.selectedLeave.type)
                    this.dateStart = this.selectedLeave.start_date
                    this.dateEnd = this.selectedLeave.end_date
                    this.reason = this.selectedLeave.description
                    this.leaveApprovers = this.selectedLeave.approvers
                }
            },

            async updateLeave() {
                let loader  = this.$loading.show({
                    container: this.$refs.leaveForm
                })
                let form = {
                    batch: this.selectedLeave.batch,
                    user_id: this.employee.user_id,
                    type: this.leaveType.id,
                    approvers: this.leaveApprovers,
                    start: moment(this.dateStart).format('YYYY-MM-DD'),
                    end: moment(this.dateEnd).format('YYYY-MM-DD'),
                    reason: this.reason,
                    details: JSON.stringify(this.leaveDetails)
                }

                await leaveApi.updateLeave(form).then(res => {
                    loader.hide()
                    this.$emit('reload-leaves')
                    this.$emit('close')
                }).catch(err => {
                    loader.hide()
                    this.$toast.error('Something went wrong while updating the leave request.')
                    console.log(err.data)
                    this.$emit('close')
                })
            },

            async submitLeave(exemption = false) {
                let loader  = this.$loading.show({
                    container: this.$refs.leaveForm
                })

                let userId = this.employee?.user_id || this.selectedEmployee?.user_id

                let formData = new FormData()
                formData.append('user_id', userId)
                formData.append('type', this.leaveType.id)
                formData.append('approvers', JSON.stringify(this.leaveApprovers))
                formData.append('start', moment(this.dateStart).format('YYYY-MM-DD'))
                formData.append('end', moment(this.dateEnd).format('YYYY-MM-DD'))
                formData.append('reason', this.reason)
                formData.append('details', JSON.stringify(this.leaveDetails))
                formData.append('details', JSON.stringify(this.leaveDetails))
                formData.append('attachment', this.attachment)
                formData.append('exemption', exemption)
                formData.append('request_permission', this.requestPermission)

                await leaveApi.submitLeave(formData).then(res => {
                    loader.hide()
                    if (exemption) {
                        this.showRequestExemptionSuccessDialog = true
                    } else {
                        this.$emit('reload-leaves')
                        this.$emit('close')
                    }
                }).catch(err => {
                    loader.hide()
                    this.$toast.error('Something went wrong while submitting the leave request.')
                    console.log(err.data)
                    this.$emit('close')
                })
            },

            loadResources() {
                this.loadEmployees()
            },

            async loadCredits() {
                if (this.employee?.user_id) {
                    this.leaveCredits = await leaveApi.fetchCredits(this.employee.user_id)
                }
            },

            async loadLeaveTypes(userId) {
                this.leaveTypes = await leaveApi.fetchLeaveTypes(userId || this.employee?.user_id)
                this.loadSelectedLeave()
            },

            async loadEmployees() {
                this.employees = await employeeApi.fetchAllEmployees()
                this.loadSelectedLeave()
            },

            async loadApprovers() {
                let employee = this.employee || this.selectedEmployee
                if (employee?.user_id) {
                    let approvers = await leaveApi.fetchApprovers(employee?.user_id)
                    this.approvers = approvers.map(a => {
                        return {
                            user_id: a.user_id,
                            name: a.first_name + ' ' + a.last_name
                        }
                    })

                    this.loadSelectedLeave()
                }
            }
        },

        watch: {
            dateStart() {
                this.dateEnd = null
            },

            employee() {
                this.leaveCredits = []
                if (this.employee) {
                    this.loadCredits()
                    this.loadLeaveTypes()
                    this.loadApprovers()
                }
            },

            employees() {
                if (this.employees.length > 0) {
                    this.loadApprovers()
                }
            }
        }
    }
</script>
<style>
button.v-btn.v-btn--elevated.v-btn--icon.v-theme--light.bg-default.v-btn--density-comfortable.v-btn--size-default.v-btn--variant-elevated.v-dialog-close-btn {
    top: 20px;
    right: 15px;
    @apply !tw-bg-gray-200
}
</style>
