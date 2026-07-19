<script setup>
import vaccinationApi from "@/api/vaccination/vaccination";
import toast from '@/composable/toast.js';
import { useEmployeeStore } from "@/store/employeeStore";
import { useVaccinationStore } from "@/store/vaccinationStore";
import { requiredValidator } from '@core/utils/validators';

const props = defineProps({
  isDialogOpen: {
    type: Boolean,
    required: true,
  },
})

const vaccinationStore = useVaccinationStore()
const employeeStore = useEmployeeStore();

const upload_file = ref(null)
const loading = ref(false)
const form = ref({
  file_data: '',
  file_url: '',
  vaccination: [],
  comments: '',
  reason: [],
  exempt_reason: [],
})

const emit = defineEmits([
  'update:isDialogOpen',
  'refetch'
])

const closeNavigationDialog = () => {
  emit('update:isDialogOpen', false)
}

onMounted( async () => {
  await vaccinationStore.setVaccinations();
})

const onSelectFile = () => {
  const input = upload_file
  const files = input.value.files
  if (files && files[0]) {
    const size = (files[0].size / 1024 / 1024).toFixed(2)
    if (size > 5) {
      toast(
        'error',
        'File size too large. Please select file not exceeding to 5mb'
      )
      return
    }
    const reader = new FileReader()
    reader.onload = (e) => {
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

const submitExemption = async () => {
    try {
    loading.value = true
    const formData = new FormData()
    formData.append('user_id', employeeStore.data.employee_selected.user_id)
    formData.append('vaccination', form.value.vaccination)
    formData.append('status', 1)
    formData.append('file', form.value.file_data)
    formData.append('file_status', 'Pending review')
    formData.append('comments', form.value.comments)
    formData.append('exempt_reason', form.value.exempt_reason)

    const response = await vaccinationApi.add_employee_vaccination(formData)
    toast('success', response.message)
    loading.value = false
    emit('update:isDialogOpen', false)
    emit('refetch')
  } catch (error) {
    console.error(error)
    loading.value = false
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
 <div class="text-center pa-4">
      <VDialog
        v-model="props.isDialogOpen"
        persistent
        width="800"
        class="exemption-dialog-wrapper"
      >
        <VCard
          class="d-flex align-center justify-center exemption-card"
        >
        <VCardText class="ma-0 pa-0 mb-5 mt-3">
            <p class="text-h2 font-weight-bold text-center mt-8 mb-4">Exemption Request</p>
            <div>
                <p>Not every health professional can grant a medical exemption.</p>
                <p>Health professionals who can grant a medical exemption are:</p>
                <ul class="ml-6">
                    <li>- general practice registrars on an approved 3GA training placement</li>
                    <li>- pediatricians</li>
                    <li>- public health physicians</li>
                    <li>- infectious diseases physicians</li>
                    <li>- clinical immunologists</li>
                    <li>- GPs who meet certain criteria</li>
                </ul>
            </div>
            <div class="mt-2">
                <p>GPs need to be one of the following:</p>
                <ul class="ml-6">
                    <li>- vocationally registered</li>
                    <li>- a fellow of the Royal Australian College of General Practitioners (RACGP)</li>
                    <li>- a fellow of the Australian College of Rural and Remote Medicine (ACRRM)</li>
                </ul>
            </div>
        </VCardText>
        <VCardText style="inline-size: 25em;" class="ma-0 pa-0">
            <VRow>
                <VCol cols="12">
                    <AppSelect
                        label="Select Type"
                        placeholder="Vaccination Type"
                        v-model="form.vaccination"
                        item-title="name"
                        item-value="name"
                        :items="vaccinationStore.data.vaccinations"
                        clearable
                        :rules="[requiredValidator]"
                    />
                </VCol>

                <VCol cols="12">
                    <AppSelect
                        label="Exemption Reason"
                        placeholder="Select Reason"
                        v-model="form.exempt_reason"
                        :items="['Anaphylaxis to a vaccine or one of its components.', 'Significant immunocompromise, only for live vaccines like MMR or varicella.']"
                        :rules="[requiredValidator]"
                    />
                </VCol>

                <VCol cols="12">
                    <div class="text-subtitle-1 text-medium-emphasis">Upload Exemption</div>
                    <u v-if="form.file_url" style="color: #5C7FFF;">{{ form.file_data?.name }} <VIcon icon="tabler-x" size="small" color="#E5E4E6" @click="form.file_url = ''" class="ml-1"/></u>
                    <div v-else class="file_upload_wrapper justify-center mb-3 cursor-pointer">
                        <VCol class="d-flex align-center justify-center no_image_wrapper background" @click="triggerSelectFile">
                        <input
                            ref="upload_file"
                            type="file"
                            name="upload_file"
                            class="upload_file"
                            accept=".pdf"
                            @input="onSelectFile"
                            style="display: none;"
                        />
                            <div class="text-center">
                                <v-icon
                                    icon="tabler-upload"
                                    class="mb-3"
                                    size="x-large"
                                    style="background-color: #F1F0F2; padding: 5px; border-radius: 5px; color: #7D7A89;"
                                ></v-icon>
                                <p class="font-weight-medium">Drop files here or click to upload</p>
                            </div>
                        </VCol>
                    </div>
                </VCol>

                <VCol cols="12">
                    <div class="text-subtitle-1 text-medium-emphasis">Comments</div>
                    <AppTextarea
                        placeholder="Comments"
                        v-model="form.comments"
                        :rules="[requiredValidator]"
                    />
                </VCol>

                <VCol cols="12" class="text-center mb-8">
                    <VBtn
                        type="submit"
                        class="me-3"
                        :loading="loading"
                        @click="submitExemption"
                        :disabled="
                            !form.comments ||
                            !form.file_url ||
                            form.exempt_reason.length === 0 ||
                            form.vaccination.length === 0"
                    >
                    Submit
                    </VBtn>
                    <VBtn
                        type="reset"
                        variant="tonal"
                        color="secondary"
                        @click="closeNavigationDialog"
                    >
                    Cancel
                    </VBtn>
                </VCol>
            </VRow>
        </VCardText>
        </VCard>
      </VDialog>
    </div> 
</template>
<style scoped>
.background {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 1;
}

.file_upload_wrapper {
  position: relative;
  display: flex;
  border: 1px dashed #c9c8cd;
  border-radius: 5px;
  block-size: 80%;
  block-size: 150px;
}

.v-theme--dark .file_upload_wrapper {
  border: 1px dashed #595d74 !important;
}

.no_image_wrapper {
  block-size: 100%;
}
</style>
<style>
.exemption-dialog-wrapper.v-dialog > .v-overlay__content {
  max-height: max-content !important;
  position: absolute;
  top: 30px;
  padding-bottom: 50px;
}

.exemption-dialog-wrapper.v-dialog {
    overflow: auto !important;
}

.exemption-dialog-wrapper .v-overlay-scroll-blocked:not(html) {
    overflow-y: hidden !important;
}

.exemption-dialog-wrapper .v-overlay {
    pointer-events: all !important;
}
.exemption-card {
  overflow: hidden !important;
}
</style>
