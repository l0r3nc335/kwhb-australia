<script setup>
import myob_emp from '@/api/employee/myob_employee';
import toast from '@/composable/toast.js';
import { useEmployeeStore } from "@/store/employeeStore";

const props = defineProps({
  data: {
    type: null,
    required: true,
  },
})

const employeeStore = useEmployeeStore()

let edit_tfn = ref(false)
let edit_abn = ref(false)
let edit_status = ref(false)
const Superannuation = {
  EmployeeMembershipNumber: "34534535345",
  SuperannuationCategories: [
    {
      Name: "Superannuation Guarantee",
      Type: "Superannuation",
      UID:  "aa4572d0-fbd8-4b76-af15-cceff605f105",
    }
  ],
  SuperannuationFund : {
    UID: "a32f4edf-9dd0-4fe4-af77-0f8be1ed89d8",
    Name: "C+BUS - Construction and Building Union Superannuation"
  }
}
const myob_data = ref({
  EmployeeMembershipNumber: "",
  SuperannuationCategories: [
    {
      Name: "",
      Type: "",
      UID:  "",
    }
  ],
  SuperannuationFund : {
    UID: "",
    Name: ""
  }
});

const updateRow = async (row, data)=>{

  var update = await employeeStore.updateRow({
    id: props.data.id,
    data,
    row,
  })
  toast('success',"Updated!")
  edit_tfn = false
  edit_abn = false
}
</script>

<template>
  <VCard class="mb-4">
    <VCardText>
      <p class="text-lg d-flex justify-space-between">
        <span>
          <b>Super Information</b>
        </span>
      </p>
      <VList class="card-list text-medium-emphasis">
        <VListItem>
          <VListItemTitle>
            <span class="font-weight-medium me-1" />
            <span v-if="!edit_tfn">{{ props.data.tax_number }}</span>
            <span v-else>
              <AppTextField
                v-model="props.data.tax_number"
                placeholder="123456"
                @keyup.enter="updateRow('tax_number', props.data.tax_number)"
              />
            </span>
            <div class="hesta-data">
              <p class="text-lg d-flex justify-space-between">
                <span class="hesta-title">
                  HESTA
                </span>
                <span>
                  <VBtn
                    class="me-2"
                    variant="tonal"
                    color="#092076"
                  >
                    <b>Edit</b>
                  </VBtn>
                  <VBtn
                    variant="tonal"
                    color="#f1b63a"
                  >
                    <b>Delete</b>
                  </VBtn>
                </span>
              </p>
              <p>{{ props.data.first_name + " " + props.data.last_name }}</p>
              <p><b>Account Number: </b>
                <span v-if="myob_data.Superannuation != null">
                  {{ myob_data.Superannuation.EmployeeMembershipNumber }}
                </span></p>
            </div>
          </VListItemTitle>
        </VListItem>
      </VList>
    </VCardText>
  </VCard>
</template>

<style lang="scss" scoped>
.card-list {
  --v-card-list-gap: 16px;
}

.hesta-data {
  padding: 20px;
  border-radius: 7px;
  background: #f8f8f9;
  margin-block-start: -15px;
}

.hesta-title {
  padding: 10px;
  border-inline-end: 5px solid #7226e0;
  border-inline-start: 5px solid #7226e0;
  color: #7226e0;
  font-size: x-large;
  font-weight: 800;
  margin-inline-end: auto;
}

.action-buttons {
  justify-content: flex-end;
}
</style>
