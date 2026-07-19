<template>
  <div class="page-content-container">

    <section>
      <VCard
        title=""
        class="mb-6"
      >

        <VCardText class="d-flex flex-wrap py-6 gap-6" style="">
          <div class="me-12 d-flex justify-space-evenly gap-2" width="200" style="">
            <AppTextField
              class="filter-field"
              density="compact"
              v-model="filter.search_name"
              prepend-inner-icon="tabler-search"
              placeholder="Search by Name"
              persistent-placeholder
              @input="filter_change"
            />
            <AppSelect
              class="filter-field"
              density="compact"
              v-model="filter.leave_type"
              placeholder="Leave Type"
              item-title="name"
              item-value="id"
              :items="['all', ...leaveTypeStore.data.leave_types]"
              @update:modelValue="filter_change"
            />
            <!-- <AppDateTimePicker
              class="filter-field"
              density="compact"
              style="min-width: 150px;"
              v-model="filter.filter_date"
              placeholder="Select date"
              :config="{dateFormat: 'd/m/Y' }"
              multiple
            /> -->
             <VueDatePicker v-model="filter.filter_date" range multi-calendars @update:model-value="filter_change()" />
            <AppSelect
              class="filter-field"
              density="compact"
              v-model="filter.division"
              placeholder="Division"
              item-title="name"
              item-value="id"
              :items="['all', ...divisionStore.data.divisions]"
              @update:modelValue="filter_change"
            />
            <AppSelect
              class="filter-field"
              density="compact"
              v-model="filter.location"
              placeholder="Location"
              item-title="name"
              item-value="id"
              :items="['all', ...locationStore.data.locations]"
              @update:modelValue="filter_change"
            />
            <AppSelect
              class="filter-field"
              density="compact"
              v-model="filter.status"
              placeholder="Status"
              item-title="name"
              item-value="id"
              :items="['all','Pending','Declined','Approved', 'Cancelled']"
              @update:modelValue="filter_change"
            />
          </div>
          <VSpacer />

          <div class="app-user-search-filter d-flex align-center flex-wrap gap-4">

            <!-- 👉 Add user button -->
            <VBtn
              prepend-icon="tabler-plus"
              @click="applyLeaveVisible = true"
            >
              Apply for Leave
            </VBtn>
          </div>
        </VCardText>

        <VDivider />

        <!-- //EmployeeDetails-index -->
        <VDataTableServer
            :headers="headers"
            :items="items"
            :items-per-page="10"
            height="300"
            @click:row="rowClick"
          >
             <template #item.access="{ item }">
              <div v-if="item.user.role">
                {{ item.user.role.name }}
              </div>
            </template>
            <template #item.full_name="{ item }">
              {{ item.employee.first_name }} {{ item.employee.last_name }}
            </template>
            <template #item.date_from="{ item }">
              {{ $formatDate(item.date_from) }}
            </template>
            <template #item.date_to="{ item }">
              {{ $formatDate(item.date_to) }}
            </template>
            <template #item.assignedTo="{ item }">
              {{ item.approver ? item.approver.last_name  : ''}}
            </template>


             <template #item.action="{ item }">
              <div class="me-n3">

                <IconBtn
                >
                  <VIcon icon="tabler-dots-vertical" />

                  <VMenu
                    v-if="moreList"
                    activator="parent"
                  >
                    <v-list>
                      <v-list-item>
                        <a @click="showLeaveDetails(item)" href="#">View</a>
                      </v-list-item>
                      <v-list-item>
                        <a @click="showLeaveDetails(item)" href="#">Edit</a>
                      </v-list-item>
                      <v-list-item>
                        <a @click="updateStatus('Deleted', item)" href="#">Delete</a>
                      </v-list-item>
                    </v-list>
                  </VMenu>
                </IconBtn>
              </div>
            </template>
        </VDataTableServer>
      </VCard>
    </section>

    <DialogLeaveDetails
      v-model:isDialogVisible="leaveDetailsVisible"
      v-model:leaveDetail="item_selected"
      v-model:items="items"
    />

    <applyLeaveDialogVue
      v-model:isDialogVisible="applyLeaveVisible"
      v-model:items="items"
    ></applyLeaveDialogVue>

  </div>

</template>


<script setup>
import toast from '@/composable/toast.js';
import { useEmployeeStore } from "@/store/employeeStore";
import { useRouter } from 'vue-router';

import DialogLeaveDetails from '@/pages/LeaveManagement/DialogLeaveDetails.vue';
import { useDivisionStore } from "@/store/divisionStore";
import { useEmploymentStore } from "@/store/employmentStore";
import { useLeaveBalanceStore } from "@/store/leaveBalanceStore";
import { useLeaveStore } from "@/store/leaveStore";
import { useLeaveTypeStore } from "@/store/leaveTypeStore";
import { useLocationStore } from "@/store/locationStore";
import { debounce } from 'lodash';
import { VDataTableServer } from 'vuetify/labs/VDataTable';
import applyLeaveDialogVue from "./applyLeaveDialog.vue";


const router = useRouter()
const leaveBalanceStore = useLeaveBalanceStore();
const employeeStore = useEmployeeStore();
const leaveStore = useLeaveStore();
const divisionStore = useDivisionStore();
const locationStore = useLocationStore();
const employmentStore = useEmploymentStore();
const leaveTypeStore = useLeaveTypeStore();
let items = ref([]);
let item_selected = ref({});
const leaveDetailsVisible = ref(false)
onMounted( async() => {
  items.value = await leaveStore.fetchLeaveByApprover();
  //fetchLeaveByApprover
  //await divisionStore.setDivisions();
  //await locationStore.setLocations();
  //await employmentStore.setEmployments();

  //items.value = employeeStore.data.employees;
})

const headers = [
  {
    title: 'ID',
    key: 'id',
  },
  {
    title: 'Start Date',
    key: 'date_from',
  },
    {
    title: 'End Date',
    key: 'date_to',
  },
  {
    title: 'Full Name',
    key: 'full_name',
  },
  {
    title: 'Leave Type',
    key: 'leave_type.name',
  },
  {
    title: 'Duration',
    key: 'total_hours',
  },
  {
    title: 'Assigned To',
    key: 'assignedTo',
  },
  {
    title: 'Status',
    key: 'status',
  },
  {
    title: '',
    key: 'action',
  }
]
const moreList = [
  {
    title: 'View',
    value: 'view',
  },
  {
    title: 'Edit',
    value: 'edit',
  },
  {
    title: 'Delete',
    value: 'delete',
  },
]
const applyLeaveVisible = ref(false)
const filter = ref({
  search_name: '',
  leave_type: 'all',
  division: 'all',
  location: 'all',
  status: 'all',
  filter_date: null

})

const rowClick = (e, row)=>{
  // employeeStore.setEmployeeSelected(row.item);
  // employeeStore.setActiveTab('EmployeeInfo')
  // // router.push("/EmployeeDetails");
  // router.push({ name: 'EmployeeDetails', params: { tab: 'EmployeeInfo' } })

}

const filter_change = debounce(async() => {
  const payload = {
    filter: {
      search_name: filter.value.search_name ? true : false,
      leave_type: true,
      division: true,
      location: true,
      status: true,
      filter_date: filter.value.filter_date ? true : false
    },
    data: {
      search_name: filter.value.search_name,
      leave_type_id: filter.value.leave_type,
      division_id: filter.value.division,
      location_id: filter.value.location,
      status: filter.value.status,
      filter_date: {
        from: filter.value.filter_date ? filter.value.filter_date[0] : null,
        to: filter.value.filter_date ? filter.value.filter_date[1] : null,
      }
    }
  }
  items.value = await leaveStore.multipleFilter(payload);
}, 800);

const showLeaveDetails = async(item)=>{

  var temp = {
    employee_id: item.employee_id,
    leave_type_id: item.leave_type_id
  }
  item.leaveBalance = await leaveBalanceStore.fetchLeaveBalance(temp);
  item.leaveBalance.leave_requested = 0;
  item.leaveBalance.remaining_balance = 0;
  item.leaveBalance.pending_leave = 0;
  item.approvers = await fetchApprovers({division_id: item.employee.division_id, employee_id: item.employee_id})

  item_selected =  calculateLeave(item)
  leaveDetailsVisible.value = true;
}

const fetchApprovers = async(item) => {
   let approvers = await employeeStore.fetch_approvers({
    "division_id": item.division_id,
    "employee_id": item.employee_id
  })
  return approvers;
}
const calculateLeave = (item) => {
  item.leaveBalance.leave_requested = 0;
  let leave_requested = 0
  if(item.leave_details.length > 0)
  {


    item.leave_details.forEach(temp => {
      leave_requested += parseFloat(temp.hour);
    });

    item.leaveBalance.remaining_balance = item.leaveBalance.balance - leave_requested;
    item.leaveBalance.leave_requested = leave_requested;
  }
  return item;
};

const updateStatus = async (status, data)=>{

  var update = await leaveStore.updateRow({
    id: data.id,
    data: status,
    row: "status"
  })
  items.value = await leaveStore.fetchLeaveByApprover();
  toast('success',"Updated!");
};

</script>
<style lang="scss" scoped>
.filter-field {
  min-width: 170px;
  max-width: 170px;
}
</style>

