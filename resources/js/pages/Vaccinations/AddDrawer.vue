<script setup>
import vaccinationApi from "@/api/vaccination/vaccination";
import toast from '@/composable/toast.js';
import { requiredValidator } from '@core/utils/validators';
import { PerfectScrollbar } from 'vue3-perfect-scrollbar';
import moment from "moment";
import divisionApi from "@/api/division/divisionApi"
import roleApi from "@/api/role/roleApi"

import jobTitleApi from "@/api/jobTitle/jobTitleApi"

const props = defineProps({
  isDrawerOpen: {
    type: Boolean,
    required: true,
  },
})

const refForm = ref()

const divisions = ref([]);
const roles = ref([])
const searchRole = ref([])
const jobTitles = ref([])

const loadDivisions = async () => {
    try {
        //const result = await divisionApi.fetchDivisions();
        const result = await divisionApi.fetchDivisionsActive();
        divisions.value = result.map(d => d.name);
    } catch (error) {
        console.error('Failed to load divisions:', error)
    }
}
const loadRoles = async () => {
    try {
        const result  = await roleApi.fetchAllRoles()
        roles.value = result.map(d => d.name);
    } catch (error) {
        console.error('Failed to load roles:', error)
    }
}
const loadRolesJobTitle = async () => {
    try {
        const result  = await jobTitleApi.fetchActiveJobTitlesForDivision(form.value.division)
        jobTitles.value = result.data.map(d => d.name)

        console.log("jobTitles.value")
        console.log(jobTitles.value)
    } catch (error) {
        console.error('Failed to load roles:', error)
    }
}

onMounted(() => {
    loadDivisions()
    loadRoles()
})

const loading = ref(false)
const minDate = new Date().toISOString().split('T')[0];
const form = ref({
    name: '',
    validity: [],
    division: [],
    required: 0,
    role: [],
    custom_expiry: null
})

const emit = defineEmits([
  'update:isDrawerOpen',
  'refetch',
])

// 👉 drawer close
const closeNavigationDrawer = () => {
  emit('update:isDrawerOpen', false)
}

const handleDrawerModelValueUpdate = val => {
  emit('update:isDrawerOpen', val)
}

const createVaccination = async () => {
  try {
    loading.value = true
    let custom_expiry = null

    if (form.value.validity == 'Custom') {
      custom_expiry = moment(form.value.custom_expiry).format('YYYY-MM-DD')
    }

    const formData = new FormData()
    formData.append('name', form.value.name)
    formData.append('validity', form.value.validity)
    formData.append('custom_expiry', custom_expiry)
    formData.append('division', form.value.division)
    formData.append('required', form.value.required)
    formData.append('role', form.value.role)

    const response = await vaccinationApi.add_vaccination(formData)
    toast('success', response.message)
    loading.value = false
    form.value.name = ''
    form.value.validity = []
    form.value.division = []
    form.value.required = 0
    form.value.role = []
    emit('update:isDrawerOpen', false)
    emit('refetch')
  } catch (error) {
    console.error(error)
    loading.value = false
    if (error.response && error.response.status === 422) {
      const validationErrors = error.response.data.errors

      Object.values(validationErrors).forEach(errorMessages => {
        errorMessages.forEach(message => {
          toast('error', message)
        })
      })
    } else {
      toast('error', 'An unexpected error occurred. Please try again.')
      console.error(error)
    }
  }
}

const selectAllDivision = () => {
  if (form.value.division.length === divisions.value.length) {
    form.value.division = [];
  } else {
    form.value.division = [...divisions.value];
  }
}

const selectAllRole = () => {
    if (form.value.role.length === jobTitles.value.length) {
        form.value.role = [];
    } else {
        form.value.role = [...jobTitles.value];
    }
}

watch(() => form.value.division, (newVal, oldVal) => {
    if(form.value.division.length > 0){
        loadRolesJobTitle(form.value.division)
    }else{
        jobTitles.value = []
    }
})

const searchDivision = ref('');

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
      title="Create New"
      @cancel="closeNavigationDrawer"
    />

    <PerfectScrollbar :options="{ wheelPropagation: false }">
      <VCard flat>
        <VCardText>
          <VForm
            ref="refForm"
          >
            <VRow>
              <VCol cols="12">
                <div class="text-subtitle-1 text-medium-emphasis">Vaccination Name</div>
                <AppTextField
                  placeholder="Enter Vaccination name here"
                  v-model="form.name"
                  :rules="[requiredValidator]"
                />
              </VCol>

              <VCol cols="12">
                <AppSelect
                  v-model="form.validity"
                  label="Set Validity"
                  placeholder="Annually"
                  :items="['Quarterly','Annual','Lifetime','Custom']"
                  clearable
                  :rules="[requiredValidator]"
                />
              </VCol>

                <VCol cols="12"
                      v-if="form.validity == 'Custom'">
                    <VueDatePicker
                        v-model="form.custom_expiry"
                        :min-date="minDate"
                        :enable-time-picker="false"
                        :auto-apply="true"
                        format="dd-MM-yyyy"
                        placeholder="Expiry Date">
                    </VueDatePicker>
                </VCol>

              <VCol cols="12">
                <div class="text-subtitle-1 text-medium-emphasis">Select Division</div>
                  <v-combobox
                      placeholder="Program"
                      v-model="form.division"
                      :search-input.sync="searchDivision"
                      :items="divisions"
                      clearable
                      multiple
                      small-chips
                      :hide-no-data="false"
                      :menu-props="{ maxWidth: '0' }"
                      color="white"
                  >

                  <template v-slot:prepend-item>
                    <v-list-item
                      @click="selectAllDivision"
                      :key="'select-all'"
                    >
                      <v-list-item-content>
                        <v-list-item-title>Select All</v-list-item-title>
                      </v-list-item-content>
                    </v-list-item>
                    <v-divider />
                  </template>
                </v-combobox>
              </VCol>

              <VCol cols="12">
                <div class="text-subtitle-1 text-medium-emphasis">Select Requirement</div>
                <VRadioGroup inline v-model="form.required">
                  <VRadio
                    label="Mandatory"
                    color="#CC4A04"
                    :value="1"
                  />
                  <VRadio
                    label="Optional"
                    color="#CC4A04"
                    :value="0"
                  />
                </VRadioGroup>
              </VCol>

              <VCol cols="12">
                <div class="text-subtitle-1 text-medium-emphasis">Select Roles</div>
                <v-combobox
                  placeholder="Search"
                  prepend-inner-icon="tabler-search"
                  clearable
                  v-model="form.role"
                  :search-input.sync="searchRole"
                  :items="jobTitles"
                  multiple
                  :hide-no-data="false"
                  small-chips
                  :menu-props="{ maxWidth: '0' }"
                  color="#000"
                  :disabled="form.division.length < 1"
                >
                  <template v-slot:prepend-item>
                    <v-list-item
                      @click="selectAllRole"
                      :key="'select-all'"
                    >
                      <v-list-item-content>
                        <v-list-item-title>Select All</v-list-item-title>
                      </v-list-item-content>
                    </v-list-item>
                    <v-divider />
                  </template>
                  <template v-slot:no-data>
                    <v-list-item>
                      <v-list-item-title v-if="search">
                        No results matching "<strong>{{ search }}</strong>"
                      </v-list-item-title>
                    </v-list-item>
                  </template>
                </v-combobox>
              </VCol>

              <VCol cols="12" class="mt-3">
                <VBtn
                  class="me-3"
                  :loading="loading"
                  @click="createVaccination"
                >
                  Add
                </VBtn>
                <VBtn
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
<style scoped>
:deep(.v-field__prepend-inner) {
  color:darkgray !important;
}
</style>
