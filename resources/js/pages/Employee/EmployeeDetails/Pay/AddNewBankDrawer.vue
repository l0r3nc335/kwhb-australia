<script setup>
import { useEmployeeStore } from "@/store/employeeStore";
import { requiredValidator } from '@core/utils/validators';
const employeeStore = useEmployeeStore();

const props = defineProps({
  isDrawerOpen: {
    type: Boolean,
    required: true,
  },
})

const emit = defineEmits([
  'update:isDrawerOpen',
  'userData',
])

const refForm = ref()
const bsb = ref('')
const account_name = ref('')
const account = ref('')
const primary = ref(false)
const reimbursement = ref(false)

// 👉 drawer close
const closeNavigationDrawer = () => {
  emit('update:isDrawerOpen', false)
}

const onSubmit = async () => {
  let validForm = false

  const response = await refForm.value.validate()
  validForm = response.valid

  if (validForm) {
    emit('userData', {
      bsb:bsb.value,
      account_name:account_name.value,
      account:account.value,
      primary:primary.value,
      reimbursement:reimbursement.value,
      employee_id:employeeStore.data.employee_selected.id,
    })
    emit('update:isDrawerOpen', false)
    bsb.value = ''
    account.value = ''
    account_name.value = ''
    reimbursement.value = ''
    primary.value = ''
  }
}

const handleDrawerModelValueUpdate = val => {
  emit('update:isDrawerOpen', val)
}
</script>

<template>
  <VNavigationDrawer
    temporary
    :width="500"
    location="end"
    class="scrollable-content px-3"
    :model-value="props.isDrawerOpen"
    @update:model-value="handleDrawerModelValueUpdate"
  >
    <AppDrawerHeaderSection
      title="Add Bank Information"
      @cancel="closeNavigationDrawer"
    />

    <PerfectScrollbar :options="{ wheelPropagation: false }">
      <VCard flat>
        <VCardText>
          <VForm
            ref="refForm"
            @submit.prevent="onSubmit"
          >
            <VRow>
             <VCol cols="12">
              <AppTextField
                v-model="account_name"
                :rules="[requiredValidator]"
                label="Account Name"
                placeholder="John Doe"
                @input="account_name = $event.target.value.replace(/[^a-zA-Z\s]/g, '')"
              />
              </VCol>
              <VCol cols="12">
                <AppTextField
                  v-model="bsb"
                  :rules="[requiredValidator]"
                  label="BSB"
                  placeholder="123-234"
                  @input="bsb = $event.target.value.replace(/[^0-9]/g, '')"
                />
              </VCol>

              <VCol cols="12">
                <AppTextField
                  v-model="account"
                  :rules="[requiredValidator]"
                  label="Account"
                  placeholder="0864 54343"
                  @input="account = $event.target.value.replace(/[^0-9]/g, '')"
                />
              </VCol>
              <VCol cols="12">
                <VCheckbox
                  v-model="primary"
                  label="Primary"
                />
              </VCol>
              <VCol cols="12">
                <VCheckbox
                  v-model="reimbursement"
                  label="Reimbursement Account"
                />
              </VCol>


              <VCol cols="12">
                <VBtn
                  type="submit"
                  class="me-3"
                >
                  Submit
                </VBtn>
                <VBtn
                  type="reset"
                  variant="outlined"
                  color="secondary"
                  @click="closeNavigationDrawer"
                >
                  Cancel
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </PerfectScrollbar>
  </VNavigationDrawer>
</template>
