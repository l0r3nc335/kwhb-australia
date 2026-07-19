<script setup>
import toast from '@/composable/toast.js';
import { addOneDay, daysBetween, getDayName } from '@/plugins/helper';
import { useEmployeeStore } from "@/store/employeeStore";
import { useLeaveBalanceStore } from "@/store/leaveBalanceStore";
import { useLeaveStore } from "@/store/leaveStore";
import { useLeaveTypeStore } from "@/store/leaveTypeStore";
import { ref, watch } from 'vue';
import UploadImages from 'vue-upload-drop-images';
import { VDataTableServer } from 'vuetify/labs/VDataTable';

const leaveBalanceStore = useLeaveBalanceStore();
const employeeStore = useEmployeeStore();
const profileTabData = ref()
const leaveBalance = ref({})
const leaveDetails = ref([])
const approvers = ref([])
profileTabData.value = employeeStore.data.employee_selected

const leaveTypeStore = useLeaveTypeStore();
const leaveStore = useLeaveStore();
const employee_selected = ref('')
const queryLoading = ref(false)
const queryTermWatch = ref('f')
const employees = ref([])

const loadEmployees = async (e) => {
  if(e)
  if(e.length > 2) {
    queryLoading.value = true
    employees.value = await employeeStore.fetch_employee_by_name( {name:e })
    queryLoading.value = false
  }

}
const customFilter = (itemTitle, queryText, item) => {
  const textOne = item.raw.first_name.toLowerCase()
  const textTwo = item.raw.last_name.toLowerCase()
  const searchText = queryText.toLowerCase()

  return textOne.includes(searchText) || textTwo.includes(searchText)
}

const handleImagesUpdated = (images) => {
  // Handle the updated images here

  apply_data.value.attachments = [];
  images.forEach(item => {
    var fileReader = new FileReader();
    fileReader.readAsDataURL(item);

    fileReader.onload = item => {
      //this.ticket.attachments.push(item.target.result);
      apply_data.value.attachments.push(item.target.result)
      //console.log(this.ticket.attachments);
    };
  });
}

const fetch_approvers = async(employee_id, division_id) => {
      approvers.value = await employeeStore.fetch_approvers({
    "division_id": division_id,
    "employee_id": employee_id
  })
  approvers.value = approvers.value.map(approver => ({
        ...approver,
        title: `${approver.first_name} ${approver.last_name}`
    }));
};


watch(queryTermWatch, async (newValue) => {

  await loadEmployees();
}, { immediate: true });
watch(employee_selected, async (newValue) => {
  if(newValue.id != null)
  await fetch_approvers(newValue.id, newValue.division_id)
}, { immediate: true });

const props = defineProps({
  isDialogVisible: {
    type: Boolean,
    required: true,
  },
  items:
  {
    type: Array,
    required: true
  },
})

const emit = defineEmits([
  'update:isDialogVisible','items'
])

const btnSaveLeave = async () => {
  //const diff_date = daysBetween(apply_data.value.start_date, apply_data.value.end_date) + 1;


   const data = {
    employee_id: employee_selected.value.id,
    leave_type_id: apply_data.value.leave_type,
    date_from: apply_data.value.start_date,
    date_to: apply_data.value.end_date,
    total_hours: leaveBalance.value.leave_requested,
    reason: apply_data.value.reason,
    status: "Pending",
    attachments: apply_data.value.attachments,
    leave_details: leaveDetails.value,
    approver_id: apply_data.value.approver_id,
    remarks: "",
  };

  const res = await leaveStore.saveLeave(data);
  if(!res)  toast('error',"Error please try again!")
  else  {
    toast('success',"Leave Applied")
    let temp = await leaveStore.fetchLeaveByApprover();
    emit('update:items', temp);
    emit('update:isDialogVisible', false);
  }
}



onMounted( async() => {

})


const fetchLeaveBalance = async() => {
  if (employee_selected != null) {
     var temp = {
      employee_id: employee_selected.value.id,
      leave_type_id: apply_data.value.leave_type
    }
    leaveBalance.value = await leaveBalanceStore.fetchLeaveBalance(temp);
    leaveBalance.value.leave_requested = 0;
    leaveBalance.value.remaining_balance = 0;
    leaveBalance.value.pending_leave = 0;
    calculateLeave();
  }

}

const updateLeaveDetails = () => {
 if(apply_data.value.start_date != null && apply_data.value.end_date != null )
 {
  const diff_date = daysBetween(apply_data.value.start_date, apply_data.value.end_date) + 1;
  var date_starter = apply_data.value.start_date
  leaveDetails.value = [];
  for (let x = 0; x < diff_date; x++) {
    var temp = {
      date: date_starter,
      day: getDayName(date_starter),
      hours: 8,
    }
    leaveDetails.value.push(temp)

    date_starter = addOneDay(date_starter)
  }
  calculateLeave();
 }
}
const calculateLeave = () => {
  leaveBalance.value.leave_requested = 0;
  let leave_requested = 0
  if(leaveDetails.value.length > 0)
  {
    leaveDetails.value.forEach(item => {
      leave_requested += parseFloat(item.hours);
    });

    leaveBalance.value.remaining_balance = leaveBalance.value.balance - leave_requested;
    leaveBalance.value.leave_requested = leave_requested;
  }
}
leaveBalance.value.balance = 0;
leaveBalance.value.remaining_balance = -1;
const apply_data = ref()
apply_data.value = {
  leave_type: null,
  start_date: null,
  end_date: null,
  reason: null,
  attachment: null,
  approver_id: null,
  leave_details: [],
  attachments: null
}
const leave_types = ref()
leave_types.value = ['Annual Leave','Personal Leave','Leave without pay','Compassionate Leave']


const headers = [
  {
    title: '',
    key: 'data-table-expand',
  },
  {
    title: 'Date',
    key: 'date',
  },
  {
    title: 'Day',
    key: 'day',
  },
  {
    title: 'Hours',
    key: 'hours',
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
         Apply for Leave
        </VCardTitle>
      </VCardItem>

      <VCardText>


        <VRow>
          <VCol cols="12" md="6">
          <VRow>
            <VCol cols="12" md="12" >
              <!-- <v-autocomplete
                v-model="newTag"
                label="First Name/Last Name"
                prepend-inner-icon="tabler-search"
                :items="employees"
                :search-input.sync="searchEmployees"
                ></v-autocomplete> -->
                <v-autocomplete
                  v-model="employee_selected"
                  label="First Name/Last Name"
                  prepend-inner-icon="tabler-search"
                  :items="employees"
                  :loading="queryLoading"
                  :custom-filter="customFilter"
                  @update:search="loadEmployees"
                >
               <template #selection="{ props, item }">
               {{item?.raw?.first_name}}  {{item?.raw?.last_name}}

              </template>
               <template #item="{ props, item }">

                <VListItem
                  v-bind="props"
                  :title="item?.raw?.first_name + ' ' + item?.raw?.last_name"
                />
              </template>
              </v-autocomplete>
            </VCol>

          </VRow>
          <VRow>
            <VCol cols="12" md="12" >
              <AppSelect
                  v-model="apply_data.leave_type"
                  label="Leave Type"
                  item-title="name"
                  item-value="id"
                  :rules="[requiredValidator]"
                  :items="leaveTypeStore.data.leave_types"
                  @update:modelValue="fetchLeaveBalance()"
              />
            </VCol>

          </VRow>
          <VRow>
            <VCol cols="12" md="12" >
              <AppDateTimePicker
                  v-model="apply_data.start_date"
                  label="Start Date"
                  placeholder="Select date"
                  :config="{dateFormat: 'd/m/Y' }"
                   @change="updateLeaveDetails()"
                />
            </VCol>
          </VRow>
          <VRow>
            <VCol cols="12" md="12" >
              <AppDateTimePicker
                  v-model="apply_data.end_date"
                  label="End Date"
                  placeholder="Select date"
                  :config="{dateFormat: 'd/m/Y' }"
                   @change="updateLeaveDetails()"
                />
            </VCol>
          </VRow>
          <VRow>
            <VCol cols="12" md="12" >
              <AppSelect
                  v-model="apply_data.approver_id"
                  label="Approvers"
                  item-title="title"
                  item-value="id"
                  :rules="[requiredValidator]"
                  :items="approvers"
              />
            </VCol>

          </VRow>
          <VRow>
            <VCol cols="12" md="12"  style="height: 240px">
              <label>Attach Supporting Documents</label>
              <UploadImages :max="5"
              accept="*"
              @changed="handleImagesUpdated"/>
            </VCol>

          </VRow>
          </VCol>
          <VCol cols="12" md="6">
            <VCard class="pa-sm-4 pa-5">
               <VCardItem style="padding: 5px">
                  <VCardTitle class="text-h5 text-center">
                  Leave Balance
                  </VCardTitle>
                </VCardItem>
                <hr>
                <VCardText>
                  <p class=" d-flex justify-space-between"><span>Balance</span>
                    <span>{{ leaveBalance == null ? 0 : leaveBalance.balance }} Hours</span></p>
                  <p class=" d-flex justify-space-between"><span>Availed</span>
                    <span>{{ leaveBalance == null ? 0 : leaveBalance.availed }} Hours</span></p>
                  <p class=" d-flex justify-space-between"><span>Accrued</span>
                  <span>Not Available</span></p>
                  <p class=" d-flex justify-space-between"><span>This Leave Request</span>
                  <span>{{ leaveBalance == null ? 0 : leaveBalance.leave_requested }} Hours</span></p>
                  <hr>
                  <p class=" d-flex justify-space-between"><span>Remaining Balance</span>
                  <span>{{ leaveBalance == null ? 0 : leaveBalance.remaining_balance }} Hours</span></p>
                  <p class=" d-flex justify-space-between"><span>Pending Leave</span>
                  <span>{{ leaveBalance == null ? 0 : leaveBalance.pending_leave }} Hours</span></p>
                </VCardText>
            </VCard>
          </VCol>

        </VRow>

        <VRow>
          <VCol cols="12" md="12" >
            <AppTextarea
            v-model="apply_data.reason"
            label="Reason for Leave"
            placeholder="Type Reason here..."
            auto-grow
          />
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
                <p class="text-center">Total Hours Requested: {{ leaveBalance == null ? 0 : leaveBalance.leave_requested }}</p>
                <VCardText>
                  <VDataTableServer
                    :headers="headers"
                    :items="leaveDetails"
                    expand-on-click
                  >
                    <!-- Expanded Row Data -->
                    <template #expanded-row="slotProps">
                      <tr class="v-data-table__tr">
                        <td :colspan="headers.length">
                          <p class="my-1">
                            hours: {{slotProps.item.value.hours}}
                          </p>
                          <p class="my-1">
                            details 2
                          </p>
                          <p>details 3</p>
                        </td>
                      </tr>
                    </template>
                    <template #item.hours="{ item }">
                      <div class="d-flex align-center">

                       <AppTextField
                          v-model="item.value.hours"
                          placeholder="hours"
                          @input="calculateLeave"
                        />
                      </div>
                    </template>
                    <template #bottom></template>
                  </VDataTableServer>

                </VCardText>
              </VCard>
          </VCol>
        </VRow>
        <div class="d-flex justify-center mt-6 ">
          <VBtn
            class="me-3 ma-3"
            type="button"
            color="secondary"
            variant="tonal"
            @click="$emit('update:isDialogVisible', false)"
          >
            Cancel
          </VBtn>
          <VBtn type="button" class="me-3 ma-3" @click="btnSaveLeave" :disabled="leaveBalance.remaining_balance < 0">
            Submit
          </VBtn>
        </div>



      </VCardText>
    </VCard>
  </VDialog>
</template>
