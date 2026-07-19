<script setup>
import complianceApi from "@/api/compliance/compliance"
import toast from '@/composable/toast.js'
import { useComplianceStore } from "@/store/complianceStore"
import { useEmployeeStore } from "@/store/employeeStore"
import { requiredValidator } from '@core/utils/validators'
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'


const props = defineProps({
  isDrawerOpen: {
    type: Boolean,
    required: true,
  },
})

const emit = defineEmits([
  'update:isDrawerOpen',
  'refetch',
])

const upload_file = ref(null)
const loading = ref(false)
const noExpiry = ref(false)

const refForm = ref()
const minDate = new Date()

const complianceStore = useComplianceStore()
const employeeStore = useEmployeeStore()

const form = ref({
  file_data: '',
  file_url: '',
  compliance: null,
  expiry: '',
  custom_expiry: null,
  status: 0,
  comments: '',
})

// 👉 drawer close
const closeNavigationDrawer = () => {
  emit('update:isDrawerOpen', false)
}

const handleDrawerModelValueUpdate = val => {
  emit('update:isDrawerOpen', val)
}

onMounted( async () => {
  await complianceStore.setCompliances()
})

const onSelectFile = () => {
  const input = upload_file
  const files = input.value.files
  if (files && files[0]) {
    const size = (files[0].size / 1024 / 1024).toFixed(2)
    if (size > 5) {
      toast(
        'error',
        'File size too large. Please select file not exceeding to 5mb',
      )
      
      return
    }
    const reader = new FileReader()

    reader.onload = e => {
      form.value.file_data = files[0]
      console.log(files[0])
      form.value.file_url = e.target.result
    }
    reader.readAsDataURL(files[0])
  }
}

const triggerSelectFile = () => {
  upload_file.value.click()
}

const createCompliance = async () => {
  try {
    loading.value = true

    const formData = new FormData()

    formData.append('user_id', JSON.parse(localStorage.getItem('user')).id)
    formData.append('compliance', form.value.compliance)
    formData.append('expiry_date', form.value.custom_expiry ?? null)
    formData.append('no_expiry', noExpiry.value)
    formData.append('status', form.value.status)
    formData.append('file', form.value.file_data)
    formData.append('file_status', form.value.status == 0 ? 'Applied' : '')
    formData.append('comments', form.value.comments)

    const response = await complianceApi.add_employee_compliance(formData)

    toast('success', response.message)
    loading.value = false
    emit('update:isDrawerOpen', false)
    form.value.compliance = []
    form.value.custom_expiry = ''
    form.value.status = 0
    form.value.comments = ''
    form.value.file_data = ''
    form.value.file_url = ''
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
      title="Add New"
      @cancel="closeNavigationDrawer"
    />

    <PerfectScrollbar :options="{ wheelPropagation: false }">
      <VCard flat>
        <VCardText>
          <VForm ref="refForm">
            <VRow>
              <VCol cols="12">
                <AppSelect
                  v-model="form.compliance"
                  label="Select Type"
                  placeholder="Certificate Type"
                  item-title="name"
                  item-value="name"
                  :items="complianceStore.data.compliances"
                  clearable
                  :rules="[requiredValidator]"
                />
              </VCol>

              <VCol cols="12" v-if="form.status == 1">
                <!--VueDatePicker
                  v-if="form.status === 1"
                  v-model="form.custom_expiry"
                  :min-date="minDate"
                  :enable-time-picker="false"
                  :auto-apply="true"
                  format="dd/MM/yyyy"
                  placeholder="Expiry Date"
                  :rules="[requiredValidator]"
                /-->
                  <AppDateTimePicker
                      v-model="form.custom_expiry"
                      prepend-inner-icon="tabler-calendar-week"
                      label="Expiry Date"
                      placeholder="4 July 2025"
                      :config="{ dateFormat: 'd-m-Y', minDate: new Date() }"
                      :min-date="minDate"
                      :auto-apply="true"
                      :enable-time-picker="false"
                      clearable
                  />
                  <VCheckbox label="Never" v-model="noExpiry" />
              </VCol>

              <VCol cols="12">
                <div class="text-subtitle-1 text-medium-emphasis">
                  Select Status
                </div>
                <VRadioGroup
                  v-model="form.status"
                  inline
                >
                  <VRadio
                    label="Applied"
                    :value="0"
                    color="#CC4A04"
                  />
                  <VRadio
                    label="Uploaded"
                    :value="1"
                    color="#CC4A04"
                  />
                </VRadioGroup>
              </VCol>

              <VCol
                v-if="form.status == '1'"
                cols="12"
              >
                <div class="text-subtitle-1 text-medium-emphasis mb-2">
                  Certification Upload
                </div>
                <u
                  v-if="form.file_url"
                  style="color: #5c7fff;"
                >{{ form.file_data?.name }} <VIcon
                  icon="tabler-x"
                  size="small"
                  color="#E5E4E6"
                  class="ml-1"
                  @click="form.file_url = ''"
                /></u>
                <div
                  v-else
                  class="file_upload_wrapper justify-center mb-3 cursor-pointer"
                >
                  <VCol
                    class="d-flex align-center justify-center no_image_wrapper background"
                    @click="triggerSelectFile"
                  >
                    <input
                      ref="upload_file"
                      type="file"
                      name="upload_file"
                      class="upload_file"
                      accept=".*"
                      style="display: none;"
                      @input="onSelectFile"
                    >
                    <div class="text-center">
                      <VIcon
                        icon="tabler-upload"
                        class="mb-3"
                        size="x-large"
                        style=" padding: 5px; border-radius: 5px;background-color: #f1f0f2; color: #7d7a89;"
                      />
                      <p class="font-weight-medium">
                        Drop files here or click to upload
                      </p>
                    </div>
                  </VCol>
                </div>
              </VCol>

              <VCol cols="12">
                <div class="text-subtitle-1 text-medium-emphasis">
                  Comments
                </div>
                <AppTextarea
                  v-model="form.comments"
                  placeholder="Comments"
                />
              </VCol>

              <VCol
                cols="12"
                class="mt-3"
              >
                <VBtn
                  class="me-3"
                  :loading="loading"
                  :disabled="
                    form.compliance === null ||
                    (form.status && form.custom_expiry === null &&  noExpiry === false) ||
                    (!form.file_data && form.status == 1) ||
                    !form.comments"
                  @click="createCompliance"
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

<style scoped>
.background {
  position: absolute;
  z-index: 1;
  block-size: 100%;
  inline-size: 100%;
  inset-block-start: 0;
  inset-inline-start: 0;
}

.file_upload_wrapper {
  position: relative;
  display: flex;
  border: 1px dashed #c9c8cd;
  border-radius: 5px;
  block-size: 100%;
  block-size: 150px;
}

.v-theme--dark .file_upload_wrapper {
  border: 1px dashed #595d74 !important;
}

.no_image_wrapper {
  block-size: 100%;
}

:deep(.v-field__prepend-inner) {
  color: darkgray !important;
}
</style>
