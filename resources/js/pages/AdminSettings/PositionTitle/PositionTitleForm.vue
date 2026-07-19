<template>
    <VNavigationDrawer
      temporary
      :width="500"
      location="end"
      class="scrollable-content px-3"
      :model-value="isDrawerOpen"
      @update:model-value="handleDrawerModelValueUpdate"
    >
      <AppDrawerHeaderSection
        title="Edit User"
        @cancel="closeNavigationDrawer"
      />
  
      <PerfectScrollbar :options="{ wheelPropagation: false }">
        <VCard flat>
          <VCardText>
            <VForm
              v-if="employee"
              ref="refForm"
              v-model="isFormValid"
              @submit.prevent="onSubmit"
            >
              <VRow>
                <!-- First & Last Name -->
                <VCol cols="12">
                  <AppTextField
                    v-model="employee.first_name"
                    :rules="[requiredValidator]"
                    label="First Name"
                    placeholder="Belinda"
                  />
                </VCol>
                <VCol cols="12">
                  <AppTextField
                    v-model="employee.last_name"
                    :rules="[requiredValidator]"
                    label="Last Name"
                    placeholder="Katherine"
                  />
                </VCol>
  
                <!-- Location -->
                <VCol cols="12">
                  <AppSelect
                    v-model="employee.location_id"
                    label="Location"
                    item-title="name"
                    item-value="id"
                    :rules="[requiredValidator]"
                    :items="locationStore.data.locations"
                  />
                </VCol>
  
                <!-- Division & Manager -->
                <VCol cols="12">
                  <AppSelect
                    v-model="employee.division_id"
                    label="Division"
                    item-title="name"
                    item-value="id"
                    :rules="[requiredValidator]"
                    :items="divisionStore.data.divisions"
                    @update:modelValue="fetchLineManager"
                  />
                </VCol>
  
                <VCol cols="12" v-if="managers.length">
                  <AppSelect
                    v-model="employee.manager_id"
                    label="Manager"
                    item-title="title"
                    item-value="id"
                    :rules="[requiredValidator]"
                    :items="managers"
                  />
                </VCol>
  
                <!-- Job Title -->
                <VCol cols="12">
                  <AppSelect
                    v-model="employee.job_title"
                    :rules="[requiredValidator]"
                    label="Job Title"
                    item-title="name"
                    item-value="name"
                    :items="positionStore.data.positions"
                  />
                </VCol>
  
                <!-- Employment, Role, Status -->
                <VCol cols="12">
                  <AppSelect
                    v-model="employee.employment_id"
                    label="Employment"
                    item-title="name"
                    item-value="id"
                    :rules="[requiredValidator]"
                    :items="employmentStore.data.employments"
                  />
                </VCol>
  
                <VCol cols="12">
                  <AppSelect
                    v-model="employee.access_id"
                    label="Access"
                    item-title="name"
                    item-value="id"
                    :rules="[requiredValidator]"
                    :items="roleStore.data.roles"
                  />
                </VCol>
  
                <VCol cols="12">
                  <AppSelect
                    v-model="employee.status"
                    label="Status"
                    :rules="[requiredValidator]"
                    :items="employeeStore.data.statuses"
                  />
                </VCol>
  
                <!-- Date Hired -->
                <VCol cols="12">
                  <AppDateTimePicker
                    v-model="employee.date_hired"
                    label="Join Date"
                    placeholder="28/02/2024"
                    :config="{ dateFormat: 'd/m/Y' }"
                    clearable
                  />
                </VCol>
  
                <!-- Buttons -->
                <VCol cols="12">
                  <VBtn type="submit" class="me-3">Update</VBtn>
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
  
  <script>
  import { requiredValidator } from '@core/utils/validators'
import { nextTick } from 'vue'
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
  
  import { useAuthStore } from '@/store/authStore'
import { useDivisionStore } from '@/store/divisionStore'
import { useEmployeeStore } from '@/store/employeeStore'
import { useEmploymentStore } from '@/store/employmentStore'
import { useLocationStore } from '@/store/locationStore'
import { usePositionStore } from '@/store/positionStore'
import { useRoleStore } from '@/store/roleStore'
  
  export default {
    name: 'PositionTitleForm',
  
    components: {
      PerfectScrollbar,
    },
  
    props: {
      isDrawerOpen: {
        type: Boolean,
        required: true,
      },
      employee: {
        type: Object,
        required: true,
        default: () => ({}),
      },
    },
  
    emits: ['update:isDrawerOpen', 'updateData'],
  
    data() {
      return {
        isFormValid: false,
        refForm: null,
        managers: [],
        localEmployee: null,
        authStore: useAuthStore(),
        employeeStore: useEmployeeStore(),
        divisionStore: useDivisionStore(),
        locationStore: useLocationStore(),
        employmentStore: useEmploymentStore(),
        roleStore: useRoleStore(),
        positionStore: usePositionStore(),
      }
    },

    mounted() {
        this.initStores()
    },    
  
    methods: {
        async initStores() {
            await Promise.all([
                this.locationStore.setLocations(),
                this.divisionStore.setDivisions(),
                this.employmentStore.setEmployments(),
                this.roleStore.setRoles(),
                this.positionStore.setPositions(),
            ])
        },
      handleDrawerModelValueUpdate(val) {
        this.$emit('update:isDrawerOpen', val)
      },
  
      closeNavigationDrawer() {
        this.$emit('update:isDrawerOpen', false)
        this.$emit('updateData', this.employee)
        nextTick(() => {
          this.$refs.refForm?.reset()
          this.$refs.refForm?.resetValidation()
        })
      },
  
      async fetchLineManager() {
        console.log("@@@@@", this.employee);
        if(this.employee.division_id){
            const res = await this.employeeStore.fetch_line_managers({
            division_id: this.employee.division_id,
            })
    
            this.managers = res.map(m => ({
            ...m,
            title: `${m.first_name} ${m.last_name}`,
            }))
    
            this.employee.manager_id = null            
        }

      },
  
      dateFormatted(date) {
        return date
          ? new Date(date.split('/').reverse().join('-')).toLocaleDateString('en-US')
          : ''
      },
  
      onSubmit() {
        console.log("@@433",this.localEmployee);
        this.$refs.refForm?.validate().then(({ valid }) => {
          if (valid) {
            const employee_data = {
              id: this.localEmployee.id,
              user_id: this.localEmployee.user?.id,
              first_name: this.localEmployee.first_name,
              last_name: this.localEmployee.last_name,
              location_id: this.localEmployee.location_id,
              division_id: this.localEmployee.division_id,
              manager_id: this.localEmployee.manager_id,
              job_title: this.localEmployee.job_title,
              employment_id: this.localEmployee.employment_id,
              role_id: this.localEmployee.access_id,
              status: this.localEmployee.status,
              date_hired: this.dateFormatted(this.localEmployee.date_hired),
            };

            this.employeeStore.updateEmployee(this.localEmployee).then((res)=>{
                this.$emit('update:isDrawerOpen', false)
                nextTick(() => {
                this.$refs.refForm?.reset()
                this.$refs.refForm?.resetValidation()
                this.authStore.getAuth()
                })
                this.$emit('updateData', employee_data)

            })
  

          }
        })
      },
    },
    watch: {
        employee: {
            immediate: true,
            deep: true,
            handler(newVal) {
            this.localEmployee = { ...newVal }
            },
        },
    },    
  }
  </script>
  