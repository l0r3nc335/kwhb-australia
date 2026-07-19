<script setup>
import { useDivisionStore } from "@/store/divisionStore";
import { useEmployeeStore } from "@/store/employeeStore";
import { useEmploymentStorev2 } from "@/store/employmentStorev2";
import { useLocationStorev2 } from "@/store/locationStorev2";
import { usePositionStoreV2 } from "@/store/positionStorev2";
import { useRoleStore } from "@/store/roleStore";
import { requiredValidator } from '@core/utils/validators';
import { PerfectScrollbar } from 'vue3-perfect-scrollbar';


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

const positionStore = usePositionStoreV2()
const divisionStore = useDivisionStore()
const roleStore = useRoleStore();
const locationStore = useLocationStorev2()
const employmentStore = useEmploymentStorev2()
const employeeStore = useEmployeeStore()

const isFormValid = ref(false)
const refForm = ref()
const firstName = ref('')
const lastName = ref('')
const location = ref()
const division = ref()
const manager = ref()
const managers = ref([])
const jobTitle = ref()
const employment = ref()
const access = ref()
const status = ref()
const dateHired = ref(new Date)


// 👉 drawer close
const closeNavigationDrawer = () => {
  emit('update:isDrawerOpen', false)
  nextTick(() => {
    refForm.value?.reset()
    refForm.value?.resetValidation()
  })
}

const fetchLineManager = async() => {
  managers.value = await employeeStore.fetch_line_managers({division_id: division.value })

  managers.value = managers.value.map(approver => ({
        ...approver,
        title: `${approver.first_name} ${approver.last_name}`
    }));
    manager.value = null;
}

const dateFormatted = date => {
    if (!date) return '';

    const jsDate = new Date(date);
    if (isNaN(jsDate)) return '';

    return jsDate.toLocaleDateString('en-US'); // or "en-GB" for DD/MM/YYYY
}

const onSubmit = () => {
  refForm.value?.validate().then(({ valid }) => {
    if (valid) {
      fetchLineManager()
      emit('userData', {
        first_name: firstName.value,
        last_name: lastName.value,
        location_id: location.value,
        division_id: division.value,
        manager_id: manager.value,
        job_title: typeof jobTitle.value == 'object' ? jobTitle.value?.name : jobTitle.value,
        position_id: jobTitle.value.id,
        employment_id: employment.value,
        role_id: access.value,
        status: status.value,
        date_hired: dateFormatted(dateHired.value),
      })
      emit('update:isDrawerOpen', false)
      nextTick(() => {
        refForm.value?.reset()
        refForm.value?.resetValidation()
      })
    }
  })
}

const handleDrawerModelValueUpdate = val => {
  emit('update:isDrawerOpen', val)
}

onMounted( async () => {
  await employeeStore.setEmployees();
  await employeeStore.fetchWidgetData();
  await divisionStore.setDivisions();
  await roleStore.setRoles();
  await locationStore.setLocations();
  await employmentStore.setEmployments();
  await positionStore.setPositions();
})
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
      title="Add User"
      @cancel="closeNavigationDrawer"
    />

    <PerfectScrollbar :options="{ wheelPropagation: false }">
      <VCard flat>
        <VCardText>
          <VForm
            ref="refForm"
            v-model="isFormValid"
            @submit.prevent="onSubmit"
          >
            <VRow>
              <VCol cols="12">
                <AppTextField
                  v-model="firstName"
                  :rules="[requiredValidator]"
                  label="First Name"
                  placeholder="Belinda"
                />
              </VCol>
              <VCol cols="12">
                <AppTextField
                  v-model="lastName"
                  :rules="[requiredValidator]"
                  label="Last Name"
                  placeholder="Katherine"
                />
              </VCol>

              <VCol cols="12">
                <AppSelect
                  v-model="location"
                  label="Location"
                  placeholder="Katherine"
                  item-title="name"
                  item-value="id"
                  :rules="[requiredValidator]"
                  :items="locationStore.data.locations"
                  clearable
                />
              </VCol>

              <VCol cols="12">
                <AppSelect
                  v-model="division"
                  label="Division"
                  placeholder="Division"
                  item-title="name"
                  item-value="id"
                  :rules="[requiredValidator]"
                  :items="divisionStore.data.divisions"
                  clearable
                />
              </VCol>

              <VCol cols="12">
                <AppCombobox
                  v-model="jobTitle"
                  :rules="[requiredValidator]"
                  label="Job Title"
                  placeholder="Chief Executive Officer (CEO)"
                  item-title="name"
                  item-value="name"
                  :items="positionStore.data.positions"
                  clearable
                />
              </VCol>

              <VCol cols="12">
                <AppSelect
                  v-model="employment"
                  label="Employment"
                  placeholder="Volunteer"
                  item-title="name"
                  item-value="id"
                  :rules="[requiredValidator]"
                  :items="employmentStore.data.employments"
                  clearable
                />
              </VCol>

              <VCol cols="12">
                <AppSelect
                  v-model="access"
                  label="Access"
                  placeholder="User"
                  item-title="name"
                  item-value="id"
                  :rules="[requiredValidator]"
                  :items="roleStore.data.roles"
                  clearable
                />
              </VCol>

              <VCol cols="12">
                <AppSelect
                  v-model="status"
                  label="Status"
                  placeholder="Active"
                  :rules="[requiredValidator]"
                  :items="['Active','Offswing','Pending','Extended Leave']"
                  clearable
                />
              </VCol>

              <VCol cols="12">
                <AppDateTimePicker
                  v-model="dateHired"
                  label="Join Date"
                  placeholder="28/02/2024"
                  :config="{dateFormat: 'd/m/Y' }"
                  clearable
                />
              </VCol>

              <VCol cols="12">
                <VBtn
                  type="submit"
                  class="me-3"
                >
                  Add
                </VBtn>
                <VBtn
                  type="reset"
                  variant="tonal"
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
