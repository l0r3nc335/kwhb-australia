<script setup>
import toast from '@/composable/toast.js';
import { cross_icon } from '@/plugins/svg_handlers.js';
import { useEmployeeStore } from "@/store/employeeStore";
import { emailValidator, requiredValidator } from '@core/utils/validators';

const props = defineProps({
  data: {
    type: null,
    required: true,
  },
})


const employeeStore = useEmployeeStore()


if(props.data.emergency_contact == null)
  props.data.emergency_contact = {}
if(props.data.medical == null)
  props.data.medical = {}

let edit_fields = ref(false)
let edit_fields_med = ref(false)

const updateRow = async (row, data)=>{

  var update = await employeeStore.updateRow({
    id: props.data.id,
    data,
    row,
  })
  toast('success',"Updated!")
}

const isFormValid = ref(false)
const refForm = ref()
let address = ref('')
let loadingEmergency = ref(false)
let loadingMedical = ref(false)

const onSubmit = () => {
  loadingEmergency.value = true
  refForm.value?.validate().then( async({ valid }) => {
    if (valid) {
      var result = await employeeStore.updateEmergencyContact(
        {
          employee_id: props.data.id,
          name: props.data.emergency_contact.name,
          address: props.data.emergency_contact.address,
          phone: props.data.emergency_contact.phone,
          email: props.data.emergency_contact.email,
          relationship: props.data.emergency_contact.relationship,
        },
      )
      if(!result)  toast('error',"Error please try again!")
      else  {
        toast('success',"Updated!")
        loadingEmergency.value = false
        edit_fields.value = false
      }
      nextTick(() => {
        refForm.value?.reset()
        refForm.value?.resetValidation()
      })
    }
  })
}


const isFormValidMed = ref(false)
const refFormMed = ref()

const onSubmitMed = () => {
  loadingMedical.value =true
  refFormMed.value?.validate().then( async({ valid }) => {
    if (valid) {
      var result = await employeeStore.updateMedical(
        {
          employee_id: props.data.id,
          allergies: props.data.medical.allergies,
          medication: props.data.medical.medication,
          condition: props.data.medical.condition,
          diagnosis_date: "2020-01-01"//props.data.medical.diagnosis_date,
        },
      )
      if(!result)  toast('error',"Error please try again!")
      else  {
        toast('success',"Updated!")
        employeeStore.setEmployeeSelected(result)
        loadingMedical.value =false
        edit_fields_med.value = false
      }
      nextTick(() => {
        refForm.value?.reset()
        refForm.value?.resetValidation()
      })
    }
  })
}
</script>

<template>
  <VCard class="mb-4 py-1">
    <VCardText>
      <p class="text-lg d-flex justify-space-between mb-5">
        <span>
          <VIcon
          icon="tabler-phone"
          size="small"
          class="me-2"
          />
          <b class="ml-3">Emergency Contact</b>
        </span>


        <span
          class="cursor-pointer"
          v-if="hasPermissions('people.employee_info_emergency_contact.update', props.data)"
          @click="edit_fields = !edit_fields"
        >
          <VIcon
            icon="tabler-pencil"
            size="x-small"
            class="me-2"
            color="#808494"
          />
        </span>
      </p>
      <VForm
        ref="refForm"
        v-model="isFormValid"
        @submit.prevent="onSubmit"
      >
        <VList class="card-list text-medium-emphasis employee-card">
          <VListItem>
            <template #prepend>
              <VIcon
                size="20"
                class="me-2"
              />
            </template>
            <VListItemTitle>
              <span class="text-label me-1">Full Name:</span>
              <span v-if="!edit_fields" style="margin-left :2.8em"> {{ props.data.emergency_contact.name }}</span>
              <span v-else>
                <AppTextField
                  v-model="props.data.emergency_contact.name"
                  :rules="[requiredValidator]"
                  placeholder="John Doe"
                />
              </span>
            </VListItemTitle>
          </VListItem>
          <VListItem>
            <template #prepend>
              <VIcon
                size="20"
                class="me-2"
              />
            </template>
            <VListItemTitle>
              <div v-if="!edit_fields">
                <span class="text-label me-1">Address:</span>
                <span class="address-emergency"  style="margin-left :3.7em">{{ props.data.emergency_contact.address }}</span>
              </div>
              <div v-else>
                <span class="me-1 text-label">Address:</span>
                <span>
                  <!-- <AppTextField
                    v-model="props.data.emergency_contact.address"
                    :rules="[requiredValidator]"

                  /> -->
                  <Autocomplete
                    v-model="props.data.emergency_contact.address"
                    :rules="[requiredValidator]"
                    placeholder="Emergency contact address"
                  />
                </span>
              </div>
            </VListItemTitle>
          </VListItem>
          <VListItem>
            <template #prepend>
              <VIcon
                size="20"
                class="me-2"
              />
            </template>
            <VListItemTitle>
              <span class="text-label me-1">Phone:</span>
              <span v-if="!edit_fields" style="margin-left :4.7em"> {{ props.data.emergency_contact.phone }}</span>
              <span v-else>
              <AppTextField
                v-model="props.data.emergency_contact.phone"
                :rules="[requiredValidator]"
                placeholder="Emergency contact phone number"
                type="tel"
                @input="props.data.emergency_contact.phone = $event.target.value.replace(/[^0-9]/g, '')"
              />
              </span>
            </VListItemTitle>
          </VListItem>
          <VListItem>
            <template #prepend>
              <VIcon
                size="20"
                class="me-2"
              />
            </template>
            <VListItemTitle>
              <span class="text-label me-1">Email:</span>
              <span v-if="!edit_fields" style="margin-left :5.1em"> {{ props.data.emergency_contact.email }} </span>
              <span v-else>
                <AppTextField
                  v-model="props.data.emergency_contact.email"
                  :rules="[requiredValidator, emailValidator]"
                  placeholder="Emergency contact Email address"
                />
              </span>
            </VListItemTitle>
          </VListItem>
          <VListItem class="mb-6">
            <template #prepend>
              <VIcon
                size="20"
                class="me-2"
              />
            </template>
            <VListItemTitle>
              <span class="text-label me-1">Relationship:</span>
              <span v-if="!edit_fields" style="margin-left :1.6em"> {{ props.data.emergency_contact.relationship }}</span>
              <span v-else>
                <AppTextField
                  v-model="props.data.emergency_contact.relationship"
                  :rules="[requiredValidator]"
                  placeholder="Relationship"
                />
              </span>
            </VListItemTitle>
          </VListItem>
          <VBtn
            style="margin-inline-start: 25px;"
            v-if="edit_fields"
            type="submit"
            class="me-3"
            :loading="loadingEmergency"
          >
            Save
          </VBtn>
        </VList>
      </VForm>
    </VCardText>
  </VCard>


  <VCard class="mb-4 py-1" v-if="hasPermissions('people.employee_info_medical.view', props.data)">
    <VCardText>
      <p class="text-lg d-flex justify-space-between mb-5">
        <p class="d-flex">
          <span class="custom-svg-icon" v-html="cross_icon"></span>
          <b class="ml-6">Medical</b>
        </p>

        <span
          class="cursor-pointer"
          v-if="hasPermissions('people.employee_info_medical.update', props.data)"
          @click="edit_fields_med = !edit_fields_med"
        >
          <VIcon
            icon="tabler-pencil"
            size="x-small"
            class="me-2"
            color="#808494"
          />
        </span>
      </p>

      <VForm
        ref="refFormMed"
        v-model="isFormValidMed"
        @submit.prevent="onSubmitMed"
      >
        <VList class="card-list text-medium-emphasis employee-card">
          <VListItem>
            <template #prepend>
              <VIcon
                size="20"
                class="me-2"
              />
            </template>
            <VListItemTitle>
              <span class="text-label me-1">Allergies:</span>
              <span v-if="!edit_fields_med" style="margin-left :3.4em">{{ props.data.medical.allergies }}</span>
              <span v-else>
                <AppTextField
                  v-model="props.data.medical.allergies"
                  :rules="[requiredValidator]"
                  placeholder="Peanuts"
                />
              </span>
            </VListItemTitle>
          </VListItem>
          <VListItem>
            <template #prepend>
              <VIcon
                size="20"
                class="me-2"
              />
            </template>
            <VListItemTitle>
              <span class="text-label me-1">Medications:</span>
              <span v-if="!edit_fields_med" style="margin-left :1.7em">{{ props.data.medical.medication }}</span>
              <span v-else>
                <AppTextField
                  v-model="props.data.medical.medication"
                  :rules="[requiredValidator]"
                  placeholder="Epi-pen"
                />
              </span>
            </VListItemTitle>
          </VListItem>
          <VListItem>
            <template #prepend>
              <VIcon
                size="20"
                class="me-2"
              />
            </template>
            <VListItemTitle>
              <span class="text-label me-1">Condition:</span>
              <span v-if="!edit_fields_med" style="margin-left :2.9em">{{ props.data.medical.condition }}</span>
              <span v-else>
                <AppTextField
                  v-model="props.data.medical.condition"
                  :rules="[requiredValidator]"
                  placeholder="Anaphylaxis"
                />
              </span>
            </VListItemTitle>
          </VListItem>
          <!--
            <VListItem>
            <template #prepend>
            <VIcon
            size="20"
            class="me-2"
            />
            </template>
            <VListItemTitle>
            <span class="text-label me-1">Diagnosis Date:</span>
            <span v-if="!edit_fields_med"> {{ props.data.medical.diagnosis_date }}</span>
            <span v-else>
            <AppDateTimePicker
            v-model="props.data.medical.diagnosis_date"
            :rules="[requiredValidator]"
            placeholder="Jan 1 2024"
            />
            </span>
            </VListItemTitle>
            </VListItem>
          -->
          <VBtn
            style="margin-inline-start: 25px;"
            v-if="edit_fields_med"
            type="submit"
            class="me-3"
            :loading="loadingMedical"
          >
            Save
          </VBtn>
        </VList>
      </VForm>
    </VCardText>
  </VCard>
</template>

<style scoped>
.card-list {
  --v-card-list-gap: 16px;
}
.address-emergency {
  /* margin-left: 0em;
  white-space: normal; */
}
</style>
