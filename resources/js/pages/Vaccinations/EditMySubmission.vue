<script setup>
import vaccinationApi from "@/api/vaccination/vaccination";
import toast from '@/composable/toast.js';
import { useVaccinationStore } from "@/store/vaccinationStore";
import { requiredValidator } from '@core/utils/validators';
import { PerfectScrollbar } from 'vue3-perfect-scrollbar';

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

const upload_file = ref(null)
const loading = ref(false)
const noExpiry = ref(false)

const refForm = ref()

const form = ref({
    file_data: '',
    file_url: '',
})

const emit = defineEmits([
    'update:isDrawerOpen',
    'refetch',
])

const vaccinationStore = useVaccinationStore()

// 👉 drawer close
const closeNavigationDrawer = () => {
    emit('update:isDrawerOpen', false)
}

const handleDrawerModelValueUpdate = val => {
    emit('update:isDrawerOpen', val)
}

onMounted(async () => {
    await vaccinationStore.setVaccinations()
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
        reader.onload = (e) => {
            form.value.file_data = files[0]
            form.value.file_url = e.target.result
        }
        reader.readAsDataURL(files[0])
    }
}

const triggerSelectFile = () => {
    upload_file.value.click()
}


const formattedExpiryDate = computed({
    get() {
        return props.vaccinationData.expiry_date ? formatDate(props.vaccinationData.expiry_date) : ''
    },
    set(value) {
        props.vaccinationData.expiry_date = parseDate(value)
    },
})

const formatDate = (date) => {
    const [year, month, day] = date.split('-')
    return `${day}/${month}/${year}`
}

const parseDate = (date) => {
    const [day, month, year] = date.split('/')
    return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`
}

const updateVaccination = async () => {
    try {
        loading.value = true
        const formData = new FormData()
        const appendIfValid = (key, value) => {
            if (value !== null && value !== undefined && value !== '') {
                formData.append(key, value)
            }
        }

        const vaccinationName = typeof props.vaccinationData?.vaccination === 'string' ?
            props.vaccinationData.vaccination :
            props.vaccinationData.vaccination?.name

        console.log("===========================VAC NAME================================")
        console.log(vaccinationName)

        appendIfValid('id', props.vaccinationData.id)
        appendIfValid('vaccination', vaccinationName)
        if(!noExpiry.value){
            appendIfValid('expiry_date', props.vaccinationData.expiry_date)
        }
        appendIfValid('status', props.vaccinationData.status)
        appendIfValid('file', form.value.file_data)
        appendIfValid('file_status', props.vaccinationData.status == 0 ? 'Applied' : props.vaccinationData.file_status == 'Exempt' ? 'Exempt' : '')
        appendIfValid('comments', props.vaccinationData.comments)


        const response = await vaccinationApi.update_employee_vaccination(formData)
        toast('success', response.message)
        loading.value = false
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
            title="Edit My Vaccination"
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
                                <AppSelect
                                    v-model="vaccinationData.vaccination"
                                    label="Select Type"
                                    placeholder="Vaccination Type"
                                    item-title="name"
                                    item-value="name"
                                    :items="vaccinationStore.data.vaccinations"
                                    clearable
                                    :rules="[requiredValidator]"
                                    @update:modelValue="val => vaccinationData.vaccination = val ?? []"
                                />
                            </VCol>

                            <VCol cols="12"
                                  v-if="vaccinationData.file_status != 'Exempt' && vaccinationData.status === '1'"
                            >
                                <AppDateTimePicker
                                    v-model="formattedExpiryDate"
                                    prepend-inner-icon="tabler-calendar-week"
                                    label="Expiry Date"
                                    placeholder="4 July 2025"
                                    :config="{ dateFormat: 'd-m-Y' }"
                                    clearable
                                    :rules="[requiredValidator]"
                                />
                                <VCheckbox label="Never" v-model="noExpiry" />
                            </VCol>

                            <VCol cols="12" v-if="vaccinationData.file_status == 'Exempt'">
                                <AppSelect
                                    v-model="vaccinationData.exempt_reason"
                                    label="Select Exemption Reason"
                                    placeholder="Exemption Reason"
                                    :items="['had anaphylaxis after previous dose of vaccine']"
                                    clearable
                                    :rules="[requiredValidator]"
                                />
                            </VCol>

                            <VCol cols="12">
                                <div class="text-subtitle-1 text-medium-emphasis">Select Status</div>
                                <VRadioGroup inline v-model="vaccinationData.status">
                                    <VRadio
                                        label="Applied"
                                        :value="'0'"
                                        color="#CC4A04"
                                    />
                                    <VRadio
                                        label="Uploaded"
                                        :value="'1'"
                                        color="#CC4A04"
                                    />
                                </VRadioGroup>
                            </VCol>

                            <VCol cols="12" v-if="vaccinationData.status == '1'">
                                <div class="text-subtitle-1 text-medium-emphasis mb-2">Vaccination Upload</div>
                                <u v-if="vaccinationData.file || form.file_url" style="color: #5C7FFF;"
                                >{{ form.file_data ? form.file_data?.name : vaccinationData.file_name }}
                                    <VIcon
                                        icon="tabler-x"
                                        size="small"
                                        color="#E5E4E6"
                                        @click="vaccinationData.file = ''; form.file_url =''; form.file_data = ''"
                                        class="ml-1"
                                    />
                                </u>
                                <div v-else class="file_upload_wrapper justify-center mb-3 cursor-pointer">
                                    <VCol class="d-flex align-center justify-center no_image_wrapper background"
                                          @click="triggerSelectFile"
                                    >
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
                                <AppTextarea placeholder="Comments" v-model="vaccinationData.comments"
                                             :rules="[requiredValidator]"
                                />
                            </VCol>

                            <VCol cols="12" class="mt-3">
                                <VBtn
                                    class="me-3"
                                    @click="updateVaccination()"
                                    :loading="loading"
                                    :disabled="
                    (vaccinationData.status === '1' && !form.file_data && !props.vaccinationData.file) ||
                    !vaccinationData.comments ||
                    vaccinationData.vaccination.length === 0"
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
    block-size: 100%;
    block-size: 150px;
}

.v-theme--dark .file_upload_wrapper {
    border: 1px dashed #595d74 !important;
}

.no_image_wrapper {
    block-size: 100%;
}
:deep(.v-field__prepend-inner ) {
    color:darkgray !important;
}
</style>
