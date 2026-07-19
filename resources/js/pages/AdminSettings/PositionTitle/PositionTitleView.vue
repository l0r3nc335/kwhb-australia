<template>
    <VNavigationDrawer
      temporary
      :width="500"
      location="end"
      class="scrollable-content px-3"
      :model-value="isDrawerOpen"
      @update:model-value="$emit('update:isDrawerOpen', $event)"
    >
      <AppDrawerHeaderSection
        title="View Employee"
        @cancel="$emit('update:isDrawerOpen', false)"
      />
  
      <PerfectScrollbar :options="{ wheelPropagation: false }">
        <VCard flat>
          <VCardText>
            <VForm v-if="employee">
              <VRow>
                <!-- First & Last Name -->
                <VCol cols="12">
                  <AppTextField
                    v-model="employee.first_name"
                    label="First Name"
                    readonly
                  />
                </VCol>
                <VCol cols="12">
                  <AppTextField
                    v-model="employee.last_name"
                    label="Last Name"
                    readonly
                  />
                </VCol>
  
                <!-- Location -->
                <VCol cols="12">
                  <AppSelect
                    v-model="employee.location_id"
                    label="Location"
                    item-title="name"
                    item-value="id"
                    :items="locationStore.data.locations"
                    disabled
                  />
                </VCol>
  
                <!-- Division -->
                <VCol cols="12">
                  <AppSelect
                    v-model="employee.division_id"
                    label="Division"
                    item-title="name"
                    item-value="id"
                    :items="divisionStore.data.divisions"
                    disabled
                  />
                </VCol>
  
                <!-- Manager -->
                <VCol cols="12" v-if="managers.length">
                  <AppSelect
                    v-model="employee.manager_id"
                    label="Manager"
                    item-title="title"
                    item-value="id"
                    :items="managers"
                    disabled
                  />
                </VCol>
  
                <!-- Job Title -->
                <VCol cols="12">
                  <AppSelect
                    v-model="employee.job_title"
                    label="Job Title"
                    item-title="name"
                    item-value="name"
                    :items="positionStore.data.positions"
                    disabled
                  />
                </VCol>
  
                <!-- Employment -->
                <VCol cols="12">
                  <AppSelect
                    v-model="employee.employment_id"
                    label="Employment"
                    item-title="name"
                    item-value="id"
                    :items="employmentStore.data.employments"
                    disabled
                  />
                </VCol>
  
                <!-- Role -->
                <VCol cols="12">
                  <AppSelect
                    v-model="employee.access_id"
                    label="Access"
                    item-title="name"
                    item-value="id"
                    :items="roleStore.data.roles"
                    disabled
                  />
                </VCol>
  
                <!-- Status -->
                <VCol cols="12">
                  <AppSelect
                    v-model="employee.status"
                    label="Status"
                    :items="employeeStore.data.statuses"
                    disabled
                  />
                </VCol>
  
                <!-- Date Hired -->
                <VCol cols="12">
                  <AppDateTimePicker
                    v-model="employee.date_hired"
                    label="Join Date"
                    :config="{ dateFormat: 'd/m/Y' }"
                    readonly
                  />
                </VCol>
  
                <!-- Cancel Button Only -->
                <VCol cols="12">
                  <VBtn
                    variant="tonal"
                    color="secondary"
                    @click="$emit('update:isDrawerOpen', false)"
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
  import { useDivisionStore } from '@/store/divisionStore'
import { useEmployeeStore } from '@/store/employeeStore'
import { useEmploymentStore } from '@/store/employmentStore'
import { useLocationStore } from '@/store/locationStore'
import { usePositionStore } from '@/store/positionStore'
import { useRoleStore } from '@/store/roleStore'
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
  
  export default {
    name: 'PositionTitleViewer',
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
      },
    },
    emits: ['update:isDrawerOpen'],
    data() {
      return {
        managers: [],
        divisionStore: useDivisionStore(),
        locationStore: useLocationStore(),
        employmentStore: useEmploymentStore(),
        roleStore: useRoleStore(),
        positionStore: usePositionStore(),
        employeeStore: useEmployeeStore(),
      }
    },
    watch: {
      'employee.division_id': {
        immediate: true,
        handler(newVal) {
          if (newVal) {
            this.fetchLineManager(newVal)
          }
        },
      },
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
      async fetchLineManager(division_id) {
        const res = await this.employeeStore.fetch_line_managers({ division_id })
        this.managers = res.map(m => ({
          ...m,
          title: `${m.first_name} ${m.last_name}`,
        }))
      },
    },
  }
  </script>
  