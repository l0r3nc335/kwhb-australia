<template>
    <VRow v-if="profileTabData">
        <VCol md="6" cols="12">
            <VRow>
                <VCol md="12" cols="12" class="tw-flex tw-justify-center" >
                    <div class="tw-py-8 tw-flex tw-w-full tw-justify-center tw-rounded-lg" style="background-color: #f9f9f9">
                        <CreateLeaveButton color="kwhb" :employee="profileTabData"></CreateLeaveButton>
                    </div>
                </VCol>
            </VRow>
            <VRow>
                <VCol md="12" cols="12">
                    <LeaveBalance :data="profileTabData" :requested-leaves="requestedLeaves"/>
                </VCol>
            </VRow>
        </VCol>
        <VCol md="6" cols="12">
            <VRow>
                <VCol md="12" cols="12">
                    <CurrentRequestLeave :data="profileTabData" @loaded-leave-requests="leaves => requestedLeaves = leaves"/>
                </VCol>
            </VRow>
            <VRow>
                <VCol md="12" cols="12">
                    <LeaveHistory :data="profileTabData" />
                </VCol>
            </VRow>
        </VCol>
        <applyLeaveDialogVue
            v-model:isDialogVisible="applyLeaveVisible"
        ></applyLeaveDialogVue>
    </VRow>
</template>

<script>
import { useEmployeeStore } from "@/store/employeeStore";
import CurrentRequestLeave from './CurrentRequestLeave.vue';
import LeaveBalance from './LeaveBalance.vue';
import LeaveHistory from './LeaveHistory.vue';
import CreateLeaveButton from "@/pages/Leaves/components/CreateLeaveButton.vue";
import applyLeaveDialogVue from "./applyLeaveDialog.vue";

const employeeStore = useEmployeeStore();

export default {
    components: {
        CurrentRequestLeave,
        LeaveBalance,
        LeaveHistory,
        CreateLeaveButton,
        applyLeaveDialogVue
    },

    data() {
        return {
            profileTabData: null,
            applyLeaveVisible: false,
            requestedLeaves: []
        }
    },

    mounted() {
        this.profileTabData = employeeStore.data.employee_selected
    }
}
</script>

<style lang="scss" scoped>
.btn_apply {
    margin: 30px;
}
</style>
