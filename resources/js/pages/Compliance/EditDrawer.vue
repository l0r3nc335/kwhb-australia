<script setup>
import complianceApi from "@/api/compliance/compliance";
import toast from '@/composable/toast.js';
import { requiredValidator } from '@core/utils/validators';
import { PerfectScrollbar } from 'vue3-perfect-scrollbar';
import moment from "moment/moment"
import DivisionApi from "@/api/division/divisionApi"
import { onMounted } from "vue"
import jobTitleApi from "@/api/jobTitle/jobTitleApi"

const props = defineProps({
  isDrawerOpen: {
    type: Boolean,
    required: true,
  },
  complianceData: {
    type: Object,
    required: true,
  },
})

const search = ref('')
const refForm = ref()
const minDate = new Date().toISOString().split('T')[0];

const jobTitles = ref([])
const roles = ref([
  'Assets Coordinator',
  'General Maintenance Officer',
  'Chief Executive Officer',
  'Executive Assistant to the CEO',
  'General Manager Health Services',
  'Director Community Engagement'
])
const loading = ref(false)
const divisionList = ref(['Clinical','Program','Corporate','Community'])

const loadDivisions = async () => {
    try {
        loading.value = true
        const res = await DivisionApi.fetchDivisions();
        // this.divisions = res.data || [];
        divisionList.value = Array.isArray(res.data)
            ? res.data.map(item => item.name)
            : Array.isArray(res)
                ? res.map(item => item.name)
                : []

        console.log("===========DIVISIONS==============")
        console.log(divisionList.value)
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

const emit = defineEmits([
  'update:isDrawerOpen',
  'refetch',
  'update:complianceData'
])

// 👉 drawer close
const closeNavigationDrawer = () => {
  emit('update:isDrawerOpen', false)
}

const handleDrawerModelValueUpdate = val => {
  emit('update:isDrawerOpen', val)
}

const divisionArray = computed({
    get() {
        return localCompliance.value.division
            ? localCompliance.value.division.split(",")
            : []
    },
    set(newValue) {
        localCompliance.value.division = newValue.join(",")
    },
  });

const localComplianceName = computed({
    get() {
        return localCompliance.value.name
    },
    set(newValue) {
        localCompliance.value.name = newValue
    },
});

const localComplianceCustomExpiry = computed({
    get() {
        return localCompliance.value.custom_expiry
    },
    set(newValue) {
        localCompliance.value.custom_expiry = newValue
    },
});

const localComplianceValidity= computed({
    get() {
        return localCompliance.value.validity
    },
    set(newValue) {
        localCompliance.value.validity = newValue
    },
});

const localComplianceRequired= computed({
    get() {
        return localCompliance.value.required
    },
    set(newValue) {
        localCompliance.value.required = newValue
    },
});

const roleArray = computed({
    get() {
        return localCompliance.value.role
            ? localCompliance.value.role.split(",")
            : []
    },
    set(newValue) {
        localCompliance.value.role = newValue.join(",")
    },
})

const selectAllDivision = () => {
    if (divisionArray.value.length === divisionList.value.length) {
        divisionArray.value = [];
    } else {
        divisionArray.value = [...divisionList.value];
    }
}

const selectAllRoles = () => {
    if (roleArray.value.length === jobTitles.value.length) {
        roleArray.value = [];
    } else {
        //roleArray.value = [...jobTitles.value];
        roleArray.value = Array.from(new Set([...roleArray.value, ...jobTitles.value]))
    }
}

const updateCompliance = async () => {
  try {
    loading.value = true
    let custom_expiry = null

    const formData = new FormData()
    formData.append('id', props.complianceData.id)
    formData.append('name', localCompliance.value.name)
    formData.append('validity', localCompliance.value.validity)
      if (localCompliance.value.validity === 'Custom') {
          custom_expiry = moment(localCompliance.value.custom_expiry).format('YYYY-MM-DD')
          formData.append('custom_expiry', custom_expiry)
      }
    formData.append('division', localCompliance.value.division)
    formData.append('required', localCompliance.value.required)
    formData.append('role', localCompliance.value.role)
    const response = await complianceApi.update_compliance(formData)
    toast('success', response.message)
    loading.value = false
    emit('update:isDrawerOpen', false)
    emit('refetch')
  } catch (error) {
    console.error(error)
    if (error.response && error.response.status === 422) {
      const validationErrors = error.response.data.errors;

      Object.values(validationErrors).forEach(errorMessages => {
        errorMessages.forEach(message => {
          toast('error', message);
        });
      });
    } else {
      toast('error', 'An unexpected error occurred. Please try again.');
      console.error(error);
    }
  }
}

const loadRolesJobTitle = async () => {
    try {
        const result  = await jobTitleApi.fetchActiveJobTitlesForDivision(divisionArray.value)
        jobTitles.value = result.data.map(d => d.name)
        jobTitles.value = Array.from(new Set([...roleArray.value, ...jobTitles.value]))

        console.log("jobTitles.value")
        console.log(jobTitles.value)
    } catch (error) {
        console.error('Failed to load roles:', error)
    }
}

const localCompliance = ref({ ...props.complianceData })

watch(() => props.complianceData, newVal => {
    localCompliance.value = { ...newVal }
})

//reset localCompliance data
watch(() => props.isDrawerOpen, (isOpen) => {
    if (isOpen) {
        localCompliance.value = { ...props.complianceData }
    }
})

watch(() => divisionArray.value, (newVal, oldVal) => {
    if(divisionArray.value.length > 0){
        loadRolesJobTitle(divisionArray.value)
    }else{
        jobTitles.value = []
    }
})

onMounted(() => {
    loadDivisions()
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
      title="Edit Compliance"
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
                <div class="text-subtitle-1 text-medium-emphasis">Certification Name</div>
                <AppTextField
                  placeholder="Certification name"
                  v-model="localComplianceName"
                  :rules="[requiredValidator]"
                />
              </VCol>

              <VCol cols="12">
                <AppSelect
                  label="Set Validity"
                  placeholder="Annually"
                  v-model="localComplianceValidity"
                  :items="['Quarterly','Annual','Lifetime','Custom']"
                  clearable
                  :rules="[requiredValidator]"
                />
              </VCol>
              <VCol cols="12" v-if="localComplianceValidity == 'Custom'">
                  <VueDatePicker
                      v-model="localComplianceCustomExpiry"
                      :min-date="minDate"
                      :auto-apply="true"
                      format="dd-MM-yyyy"
                      placeholder="Expiry Date">
                  </VueDatePicker>
              </VCol>
              <VCol cols="12">
                <div class="text-subtitle-1 text-medium-emphasis">Set Division</div>
                <v-combobox
                  placeholder="Clinical"
                  v-model="divisionArray"
                  :items="divisionList"
                  clearable
                  multiple
                  small-chips
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
                <VRadioGroup inline v-model="localComplianceRequired">
                  <VRadio
                    label="Mandatory"
                    :value="'0'"
                    color="#CC4A04"
                  />
                  <VRadio
                    label="Optional"
                    :value="'1'"
                    color="#CC4A04"
                  />
                </VRadioGroup>
              </VCol>

              <VCol cols="12">
                <div class="text-subtitle-1 text-medium-emphasis">Select Roles</div>
                <v-combobox
                  placeholder="Search"
                  prepend-inner-icon="tabler-search"
                  clearable
                  v-model="roleArray"
                  :search-input.sync="search"
                  :items="jobTitles"
                  multiple
                  :hide-no-data="false"
                  small-chips
                  :menu-props="{ maxWidth: '0' }"
                  color="white"
                >
                    <template v-slot:prepend-item>
                        <v-list-item
                            @click="selectAllRoles"
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
                  @click="updateCompliance"
                >
                  Update
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
