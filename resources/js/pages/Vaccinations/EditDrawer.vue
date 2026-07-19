<script setup>
import vaccinationApi from "@/api/vaccination/vaccination";
import toast from '@/composable/toast.js';
import { requiredValidator } from '@core/utils/validators';
import { PerfectScrollbar } from 'vue3-perfect-scrollbar';
import moment from "moment";

const props = defineProps({
  isDrawerOpen: {
    type: Boolean,
    required: true,
  },
  vaccinationData: {
    type: Object,
    required: true,
  },
})

const search = ref('')
const refForm = ref()

const roles = ref([
  'Assets Coordinator',
  'General Maintenance Officer',
  'Chief Executive Officer',
  'Executive Assistant to the CEO',
  'General Manager Health Services',
  'Director Community Engagement'
])
const loading = ref(false)
const minDate = new Date().toISOString().split('T')[0];
const emit = defineEmits([
  'update:isDrawerOpen',
  'refetch',
  'update:vaccinationData'
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
      return props.vaccinationData.division
        ? props.vaccinationData.division.split(",")
        : [];
    },
    set(newValue) {
      emit("update:vaccinationData", {
        ...props.vaccinationData,
        division: newValue.join(","),
      });
    },
  });

const roleArray = computed({
    get() {
      return props.vaccinationData.role
        ? props.vaccinationData.role.split(",")
        : [];
    },
    set(newValue) {
      emit("update:vaccinationData", {
        ...props.vaccinationData,
        role: newValue.join(","),
      });
    },
  });

const updateVaccination = async () => {
  try {
    loading.value = true

    let custom_expiry = null

    if (props.vaccinationData.validity == 'Custom') {
        custom_expiry = moment(props.vaccinationData.custom_expiry).format('YYYY-MM-DD')
    }


    const formData = new FormData()
    formData.append('id', props.vaccinationData.id)
    formData.append('name', props.vaccinationData.name)
    formData.append('validity', props.vaccinationData.validity)
    formData.append('custom_expiry', custom_expiry)
    formData.append('division', props.vaccinationData.division)
    formData.append('required', props.vaccinationData.required)
    formData.append('role', props.vaccinationData.role)
    const response = await vaccinationApi.update_vaccination(formData)
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
      title="Edit Vaccination"
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
                  placeholder="Vaccination name"
                  v-model="vaccinationData.name"
                  :rules="[requiredValidator]"
                />
              </VCol>

              <VCol cols="12">
                <AppSelect
                  label="Set Validity"
                  placeholder="Annually"
                  v-model="vaccinationData.validity"
                  :items="['Quarterly','Annual','Lifetime','Custom']"
                  clearable
                  :rules="[requiredValidator]"
                />
              </VCol>
                <VCol cols="12" v-if="vaccinationData.validity == 'Custom'">
                    <VueDatePicker v-model="vaccinationData.custom_expiry" :min-date="minDate" :auto-apply="true" format="dd/MM/yyyy" placeholder="Expiry Date"></VueDatePicker>
                </VCol>

              <VCol cols="12">
                <div class="text-subtitle-1 text-medium-emphasis">Select Division</div>
                <v-combobox
                  placeholder="Program"
                  v-model="divisionArray"
                  :items="['Clinical','Program','Corporate','Community']"
                  clearable
                  multiple
                  small-chips
                  :menu-props="{ maxWidth: '0' }"
                  color="#000"
                />
              </VCol>

              <VCol cols="12">
                <div class="text-subtitle-1 text-medium-emphasis">Select Requirement</div>
                <VRadioGroup inline v-model="vaccinationData.required">
                  <VRadio
                    label="Mandatory"
                    :value="'1'"
                    color="#CC4A04"
                  />
                  <VRadio
                    label="Optional"
                    :value="'0'"
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
                  :items="roles"
                  multiple
                  :hide-no-data="false"
                  small-chips
                  :menu-props="{ maxWidth: '0' }"
                  color="#000"
                >
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
                  @click="updateVaccination"
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
:deep(.v-field__prepend-inner)  {
  color:darkgray !important;
}
</style>
