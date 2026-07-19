<script setup>
import toast from '@/composable/toast.js';
import { useEmployeeStore } from "@/store/employeeStore";
import { useLeaveBalanceStore } from "@/store/leaveBalanceStore";
import { useLeaveStore } from "@/store/leaveStore";
import { useLeaveTypeStore } from "@/store/leaveTypeStore";
import { VDataTableServer } from 'vuetify/labs/VDataTable';

const leaveBalanceStore = useLeaveBalanceStore();
const employeeStore = useEmployeeStore();
const profileTabData = ref()
const leaveBalance = ref({})
const leaveDetails = ref([])
const approvers = ref([])
const reassign = ref(false);
const fullName = ref('');
const approver_selected = ref();
profileTabData.value = employeeStore.data.employee_selected

const leaveTypeStore = useLeaveTypeStore();
const leaveStore = useLeaveStore();

const props = defineProps({
  isDialogVisible: {
    type: Boolean,
    required: true,
  },
  leaveDetail: {
    type: Object,
    required: true
  },
  items:
  {
    type: Array,
    required: true
  },
})

const emit = defineEmits([
  'update:isDialogVisible', 'items'
])


const updateStatus = async (status, data)=>{

  var update = await leaveStore.updateRow({
    id: data.id,
    data: status,
    row: "status"
  })
  toast('success',"Updated!")
  let temp = await leaveStore.fetchLeaveByApprover();
  emit('update:items', temp);
  emit('update:isDialogVisible', false);

}
const updateApprover = async (data)=>{

  var update = await leaveStore.updateRow({
    id: data.id,
    data: approver_selected.value,
    row: "approver_id"
  })
  toast('success',"Updated!")
  let temp = await leaveStore.fetchLeaveByApprover();
  emit('update:items', temp);
  emit('update:isDialogVisible', false);

}


const modifiedApprovers = computed(() => {
  if (props.leaveDetail.approvers) {
    return props.leaveDetail.approvers.map(approver => ({
        ...approver,
        title: `${approver.first_name} ${approver.last_name}`
    }));
  } else
  return [];
});

watchEffect(() => {
    if (props.leaveDetail && props.leaveDetail.employee) {
        fullName.value = `${props.leaveDetail.employee.first_name ?? ''} ${props.leaveDetail.employee.last_name ?? ''}`;
    } else {
        fullName.value = '';
    }
});

onMounted( async() => {
})

const headers = [
  {
    title: 'Date',
    key: 'leave_date',
  },
  {
    title: 'Day',
    key: 'day_name',
  },
  {
    title: 'Hours',
    key: 'hour',
  },
]
</script>

<template>
 <VDialog
    persistent
    :width="$vuetify.display.smAndDown ? 'auto' : 1100 "
    :model-value="props.isDialogVisible"
    @update:model-value="val => $emit('update:isDialogVisible', val)"
  >
    <!-- 👉 Dialog close btn -->
    <DialogCloseBtn @click="$emit('update:isDialogVisible', false)" />

    <VCard
      class="pa-sm-8 pa-5"
    >
      <VCardItem>
        <VCardTitle class="text-h3 text-center">
         Leave Details
        </VCardTitle>
      </VCardItem>

      <VCardText>


        <VRow>
          <VCol cols="12" md="6">
            <AppTextField
              v-model="fullName"
              label="Name"
              disabled
            />
          </VCol>
          <VCol cols="12" md="6">
            <VRow>
              <VCol cols="12" md="6">
                <VCard>
                  <VCardText>
                    <div class="d-flex flex-column gap-y-1">
                      <span class="text-body-1 text-medium-emphasis">Current Balance</span>
                      <div>
                        <h4 class="text-h4">
                          {{ props.leaveDetail.leaveBalance.balance }} hours
                        </h4>
                      </div>
                    </div>
                  </VCardText>
                </VCard>
              </VCol>
              <VCol cols="12" md="6">
                <VCard>
                  <VCardText>
                    <div class="d-flex flex-column gap-y-1">
                      <span class="text-body-1 text-medium-emphasis">Projected Leave Balance</span>
                      <div>
                        <h4 class="text-h4">
                          {{ props.leaveDetail.leaveBalance.remaining_balance }} hours
                        </h4>
                      </div>
                    </div>
                  </VCardText>
                </VCard>
              </VCol>
            </VRow>
          </VCol>
        </VRow>
        <VRow>
          <VCol cols="12" md="6">
            <AppTextField
              v-model="props.leaveDetail.leave_type.name"
              label="Leave Type"
              disabled
            />
          </VCol>
          <VCol cols="12" md="6">
            <AppSelect
              v-model="props.leaveDetail.approver_id"
              label="Reassign to: "
              item-title="title"
              item-value="id"
              :items="modifiedApprovers"
              disabled
            />
          </VCol>
        </VRow>
        <VRow>
          <VCol cols="12" md="6">
            <AppDateTimePicker
              v-model="props.leaveDetail.date_from"
              label="Start Date"
              placeholder="Select date"
              :config="{dateFormat: 'd/m/Y' }"
              disabled
            />
          </VCol>
          <VCol cols="12" md="6">
            <AppDateTimePicker
              v-model="props.leaveDetail.date_to"
              label="End Date"
              placeholder="Select date"
              :config="{dateFormat: 'd/m/Y' }"
              disabled
            />
          </VCol>
        </VRow>

        <VRow>
          <VCol cols="12" md="12" >
            <AppTextarea
            v-model="props.leaveDetail.reason"
            label="Reason for Leave"
            placeholder="Type Reason here..."
            auto-grow
            disabled
          />
          </VCol>
        </VRow>
         <VRow>
           <VCol cols="12" md="12" >
            Attachments
            <span v-for="(atch, index) in props.leaveDetail.attachments" :key="index">
                <br>
               <a :href="$attachment_path + 'leave_attachments/' + atch.file_name" target="_blank">
                {{ atch.file_name }}
              </a>
            </span>

           </VCol>
        </VRow>
        <VRow>
          <VCol cols="12" md="12" >
            <VCard class="pa-sm-4 pa-5">
              <VCardItem style="padding: 5px">
                  <VCardTitle class="text-h5 text-center">
                  Leave Details
                  </VCardTitle>
                </VCardItem>
                <hr>
                <!-- <p class="text-center">Total Hours Requested: {{ leaveBalance == null ? 0 : leaveBalance.leave_requested }}</p> -->
                <VCardText>
                  <VDataTableServer
                    :headers="headers"
                    :items="props.leaveDetail.leave_details"
                  >
                    <template #bottom></template>
                  </VDataTableServer>

                </VCardText>
              </VCard>
          </VCol>
        </VRow>
         <div class="d-flex justify-center mt-6 " v-if="!reassign">
          <VBtn
            class="me-3 ma-3"
            type="button"
            color="secondary"
            variant="tonal"
            @click="$emit('update:isDialogVisible', false)"
          >
            Cancel
          </VBtn>
          <VBtn
            class="me-3 ma-3"
            type="button"
            variant="outlined"
            color="secondary"
            @click="reassign = !reassign"
          >
            Reassign
          </VBtn>
          <VBtn
            class="me-3 ma-3"
            type="button"
            variant="outlined"
            color="secondary"
            @click="updateStatus('Cancelled', props.leaveDetail)"
          >
            Cancel Request
          </VBtn>
          <VBtn
            class="me-3 ma-3"
            type="button"
            variant="outlined"
            color="secondary"
            @click="updateStatus('Declined', props.leaveDetail)"
          >
            Decline
          </VBtn>
          <VBtn
            class="me-3 ma-3"
            type="button"
            variant="outlined"
            color="success"
            @click="updateStatus('Approved', props.leaveDetail)"
          >
            Approve
          </VBtn>


        </div>
        <div class="d-flex justify-center mt-12 " v-else>

          <VCol cols="6" md="6">
            <AppSelect
              v-model="approver_selected"
              label="Reassign to: "
              item-title="title"
              item-value="id"
              :items="modifiedApprovers"
            />
          </VCol>
            <VBtn
              class="mt-9 ma-3"
              type="button"
              color="secondary"
              variant="tonal"
              @click="reassign = !reassign"
            >
              Cancel
            </VBtn>
            <VBtn
              class="mt-9 ma-3"
              type="button"
              color="primary"
              @click="updateApprover(props.leaveDetail)"
            >
              Submit
            </VBtn>
        </div>


      </VCardText>
    </VCard>
  </VDialog>
</template>
