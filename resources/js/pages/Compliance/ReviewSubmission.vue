<script setup>
import complianceApi from "@/api/compliance/compliance";
import toast from '@/composable/toast.js';
import { requiredValidator } from '@core/utils/validators';
import { useAuthStore } from "@/store/authStore"

const props = defineProps({
  isDialogOpen: {
    type: Boolean,
    required: true,
  },
  complianceData: {
    type: Object,
    required: true,
  },
})

const selectedApproval = ref(0)
const comment = ref('')

const form = ref({
  review_comments: '',
})

const loading = ref(false)
const emit = defineEmits([
    'update:isDialogOpen',
    'refetch',
    'pass-edit-data'
])

const closeNavigationDialog = () => {
  emit('update:isDialogOpen', false)
}

const formatDate = (dateStr) => {
    if (!dateStr) return ''
    const [y, m, d] = dateStr.split('-')
    return `${d}-${m}-${y}`
}

const approval = async (id) => {
  try {
    loading.value = true
    const formData = new FormData()
    formData.append('id', id)
    formData.append('approval_status', selectedApproval.value)
    formData.append('comments', comment.value)
    formData.append('review_comments', props.complianceData.review_comments)
    formData.append('user_id', JSON.parse(localStorage.getItem('user')).id)

    const response = await complianceApi.employee_compliance_approval(formData)
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

const dlFile = (id, name) => {
    const fileName = name;

    const downloadUrl =  `${import.meta.env.VITE_API_BASE_URL}/api/compliance/pdf/${id}`
    fetch(downloadUrl)
    .then(response => response.blob())
    .then(blob => {
      const blobWithFileName = new Blob([blob], { type: 'application/pdf' });

      const url = window.URL.createObjectURL(blobWithFileName);

      const anchor = document.createElement('a');
      anchor.href = url;
      anchor.download = fileName;
      anchor.style.display = 'none'; 

      document.body.appendChild(anchor);

      anchor.click();

      setTimeout(() => {
          window.URL.revokeObjectURL(url);
          document.body.removeChild(anchor);
      }, 2000);
    })
}

//prevent edit
const isDisableEditButtonInVaccination = (rowUserId) => {
    //TODO: return false if the owner or the superuser
    //TODO: return true, as default
    const currentUserRoleId = useAuthStore().userData.role.id //id and role.id
    const superUserRoleId = 1
    const userid = useAuthStore().userData.id

    return !(currentUserRoleId === superUserRoleId || rowUserId === userid);
}

const handleOpenEditDrawer = (valCompData) => {
    emit('pass-edit-data', { ...valCompData })
    emit('update:isDialogOpen', false)
}

const isApproverOfCompliances = () => {
    //Allow super admin and hr to approve/reject
    const isSuperApprover = useAuthStore().userData.role.id === 1
    const isHrApprover = useAuthStore().userData.role.id === 5

    return isSuperApprover || isHrApprover === true
}

//isStatusForApproval return false if status not for approval
const isStatusForApproval = (fileStatus) => {
    return !(fileStatus === "Required" || fileStatus === "Current" || fileStatus === "Applied");
}

const fullName = computed(() => {
    return props.complianceData.user.employee?.first_name + ' ' + props.complianceData.user.employee?.last_name
})
</script>
<template>
 <div class="text-center pa-4">
      <VDialog
        v-model="props.isDialogOpen"
        persistent
        width="800"
      >
        <VCard
          class="d-flex align-center justify-center" style="overflow-y: scroll"
        >
            <VCardText style="inline-size: 25em;max-height: 80vh;" class="ma-0 pa-0 mt-5">
                <p class="text-h3 font-weight-bold text-center mb-5" >{{ 'Review Compliance' }}</p>
                <VRow>
                    <VCol cols="12">
                        <AppSelect
                            label="Employee Name"
                            v-model="fullName"
                            item-title="name"
                            disabled
                        />
                    </VCol>
                    <VCol cols="12">
                        <div class="text-subtitle-1 text-medium-emphasis">Type</div>
                        <AppSelect
                            v-model="complianceData.compliance"
                            item-title="name"
                            disabled
                        />
                    </VCol>

                    <VCol cols="12">
                        <!--AppSelect
                            label="Expiry"
                            v-model="complianceData.expiry_date"
                            item-title="validity"
                            disabled
                        /-->
                        <div class="text-subtitle-1 text-medium-emphasis">Expiry</div>
                        <v-text-field
                            :model-value="formatDate(complianceData.expiry_date)"
                            disabled
                        />
                    </VCol>

                    <VCol cols="12" v-if="complianceData.file">
                        <div class="text-subtitle-1 text-medium-emphasis">Uploaded File</div>
                        <u style="color: #5C7FFF;" class="cursor-pointer" @click="dlFile(complianceData.file, complianceData.file_name)">{{ complianceData.file_name }}</u>
                    </VCol>

                    <VCol cols="12">
                        <div class="text-subtitle-1 text-medium-emphasis">User Comments</div>
                        <AppTextarea v-model="complianceData.comments" disabled/>
                    </VCol>

                    <VCol cols="12">
                        <VDivider/>
                    </VCol>

                    <div v-if="isApproverOfCompliances() === true && isStatusForApproval(complianceData.file_status) === true">
                        <VCol cols="12">
                            <div class="text-subtitle-1 text-medium-emphasis">Select Approval</div>
                            <VRadioGroup inline v-model="selectedApproval">
                                <VRadio
                                    label="Approved"
                                    :value="0"
                                    color="#CC4A04"
                                />
                                <VRadio
                                    label="Reject"
                                    :value="1"
                                    color="#CC4A04"
                                />
                            </VRadioGroup>
                        </VCol>

                        <VCol cols="12">
                            <div class="text-subtitle-1 text-medium-emphasis">Reviewer Comments</div>
                            <AppTextarea placeholder="Comments" v-model="complianceData.review_comments" :rules="[requiredValidator]" style="display: inline-block; width: 370px!important;"/>
                        </VCol>

                        <VCol cols="12" class="text-center mb-3">
                            <VBtn
                                type="submit"
                                class="me-3"
                                :loading="loading"
                                :disabled="!props.complianceData.review_comments"
                                @click="approval(complianceData.id)"
                            >
                                Save
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
                    </div>
                    <!-- HANDLE FEATURE CORRECTIONS -->
                    <div v-else>
                        <VCol cols="12">
                            <VBtn
                                type="button"
                                class="me-3"
                                :loading="loading"
                                :disabled="isDisableEditButtonInVaccination(complianceData.user_id)"
                                @click="handleOpenEditDrawer(complianceData)"
                            >
                                Edit
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
                        <VCol cols="12">
                            <VDivider class="mt-3"/>
                        </VCol>
                    </div>
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


/* SCOLL SIZE */
::-webkit-scrollbar {
    width: 12px!important; /* define scrollbar width */
}

::-webkit-scrollbar-thumb {
    background-color: rgba(0,0,0,0.3); /* custom color */
    border-radius: 4px;
}

::-webkit-scrollbar-track {
    background: transparent;
}
</style>
