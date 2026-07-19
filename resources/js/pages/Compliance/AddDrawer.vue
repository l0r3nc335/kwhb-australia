<script setup>
import complianceApi from "@/api/compliance/compliance"
import toast from '@/composable/toast.js'
import { requiredValidator } from '@core/utils/validators'
import moment from "moment"
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import DivisionApi from "@/api/division/divisionApi"
import { ref, onMounted } from 'vue'
import jobTitleApi from "@/api/jobTitle/jobTitleApi"

const props = defineProps({
  isDrawerOpen: {
    type: Boolean,
    required: true,
  },
})

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

const jobTitles = ref([])

const search = ref('')

const emit = defineEmits([
  'update:isDrawerOpen',
  'refetch',
])

const refForm = ref()

const roles = ref([
  'Assets Coordinator',
  'General Maintenance Officer',
  'Chief Executive Officer',
  'Executive Assistant to the CEO',
  'General Manager Health Services',
  'Director Community Engagement',
])

const divisions = ref([])

const loading = ref(false)
const minDate = new Date().toISOString().split('T')[0]



const form = ref({
  name: '',
  validity: [],
  division: [],
  required: 0,
  role: [],
  custom_expiry: null,
})


// 👉 drawer close
const closeNavigationDrawer = () => {
  emit('update:isDrawerOpen', false)
}

const handleDrawerModelValueUpdate = val => {
  emit('update:isDrawerOpen', val)
}

const createCompliance = async () => {
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

    const response = await complianceApi.add_compliance(formData)

    toast('success', response.message)
    loading.value = false
    form.value.name = ''
    form.value.validity = []
    form.value.custom_expiry = ''
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

const selectAllRole = () => {
  if (form.value.role.length === roles.value.length) {
    form.value.role = []
  } else {
    form.value.role = [...roles.value]
  }
}

const selectAllDivision = () => {
    if (form.value.division.length === divisions.value.length) {
        form.value.division = [];
    } else {
        form.value.division = [...divisions.value];
    }
}

const loadDivisions = async () => {
    try {
        loading.value = true
        const res = await DivisionApi.fetchDivisions();
        // this.divisions = res.data || [];
        divisions.value = Array.isArray(res.data)
            ? res.data.map(item => item.name)
            : Array.isArray(res)
                ? res.map(item => item.name)
                : []

        console.log("===========DIVISIONS==============")
        console.log(divisions.value)
    }catch (error) {
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
    } finally {
        loading.value = false
    }
}

// ✅ Call the function before UI renders
onMounted(() => {
    loadDivisions()
})

watch(() => form.value.division, (newVal, oldVal) => {
    if(form.value.division.length > 0){
        loadRolesJobTitle(form.value.division)
    }else{
        jobTitles.value = []
    }
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
      title="Create New Compliance Category"
      @cancel="closeNavigationDrawer"
    />

    <PerfectScrollbar :options="{ wheelPropagation: false }">
      <VCard flat>
        <VCardText>
          <VForm ref="refForm">
            <VRow>
              <VCol cols="12">
                <div class="text-subtitle-1 text-medium-emphasis">
                  Certification Name
                </div>
                <AppTextField
                  v-model="form.name"
                  placeholder="Enter Name"
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
                />
              </VCol>
              <VCol
                v-if="form.validity == 'Custom'"
                cols="12"
              >
                <VueDatePicker
                  v-model="form.custom_expiry"
                  :min-date="minDate"
                  :enable-time-picker="false"
                  :auto-apply="true"
                  format="dd-MM-yyyy"
                  placeholder="Expiry Date"
                />
              </VCol>
              <VCol cols="12">
                <v-combobox
                  placeholder="Clinical"
                  v-model="form.division"
                  label="Set Division"
                  :items="divisions"
                  clearable
                  multiple
                  small-chips
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
                    <template v-slot:no-data>
                        <v-list-item>
                            <v-list-item-title v-if="search">
                                No results matching "<strong>{{ search }}</strong>"
                            </v-list-item-title>
                        </v-list-item>
                    </template>
                </v-combobox>
              </VCol>

              <VCol cols="12">
                <div class="text-subtitle-1 text-medium-emphasis">
                  Select Requirement
                </div>
                <VRadioGroup
                  v-model="form.required"
                  inline
                >
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
                <div class="text-subtitle-1 text-medium-emphasis">
                  Select Roles
                </div>
                <VCombobox
                  v-model="form.role"
                  v-model:search-input="search"
                  placeholder="Search"
                  prepend-inner-icon="tabler-search"
                  clearable
                  :items="jobTitles"
                  multiple
                  :hide-no-data="false"
                  small-chips
                  :menu-props="{ maxWidth: '0' }"
                  color="white"
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
                </VCombobox>
              </VCol>

              <VCol
                cols="12"
                class="mt-3"
              >
                <VBtn
                  class="me-3"
                  :loading="loading"
                  @click="createCompliance"
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
  color: darkgray !important;
}
</style>
