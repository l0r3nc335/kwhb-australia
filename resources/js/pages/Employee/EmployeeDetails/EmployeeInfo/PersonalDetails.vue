<script setup>
import toast from '@/composable/toast.js';
import { useEmployeeStore } from "@/store/employeeStore";
import { emailValidator, requiredValidator } from '@core/utils/validators';
import moment from "moment";

const props = defineProps({
  data: {
    type: null,
    required: true,
  },
})

const employeeStore = useEmployeeStore()

let edit_fields = ref(false)
let address = ref('')
const isFormValid = ref(false)
const loading = ref(false)

const dateFormatted = date => {
    let df = moment(date).format('l')
    console.log(df)
  return date ? new Date(df.split("/").reverse().join("-")).toLocaleDateString("en-US") : ''
}

const updateRow = async (row, data) => {
  loading.value =true
  data.dob2 = dateFormatted(data.dob)
  data.dob3 = moment(data.dob).calendar()
  data.dob = moment(data.dob).format('YYYY-MM-DD')
  data.address2 = address.value;
  var update = await employeeStore.updateRow({
    id: props.data.id,
    data,
    row,
  })
  if(!update )
    toast('error',"Error please try again!")
  else  {
    toast('success',"Updated!")
    employeeStore.setEmployeeSelected(update)
    loading.value = false
    edit_fields.value = false
  }
}

const updateAddress = async ()=>{
  const param = {
    employee_id: props.data.id,
    name: "Employee details address",
    label: "home",
    address: address.value,
    is_default: true,
    is_active: true,
  }

  // var result = await employeeStore.updateAddress(param)

  // console.log(result)
  // if(!result)  toast("Error please try again!")
  // else  {
  //   toast("Updated!")
  //   edit_fields.value = false
  // }
}
const formatDisplayDate = date => {
  return date ? moment(date).format('DD-MM-YYYY') : ''
}

const parseDate = dateString => {
  // If the date is in the format 'YYYY-MM-DD'
  if (dateString.includes('-')) {
    return new Date(dateString)
  }

  // If the date is in the format 'd/m/Y'
  const [day, month, year] = dateString.split('/')

  return new Date(`${year}-${month}-${day}`)
}

onMounted(() => {
  //console.log(props.data);
  if (props.data.address.length > 0) {
    address.value = props.data.address[0].address
  }
})

const age = computed(() => {

  const dob = props.data.dob
  if (!dob) return '' // Handle case where DOB is not provided

  const dobDate = parseDate(dob)

  if (isNaN(dobDate)) return ''
  const currentDate = new Date()
  const ageDiff = currentDate.getFullYear() - dobDate.getFullYear()

  // Adjust age if birthday hasn't occurred yet this year
  if (
    currentDate.getMonth() < dobDate.getMonth() ||
    (currentDate.getMonth() === dobDate.getMonth() && currentDate.getDate() < dobDate.getDate())
  ) {
    return ageDiff - 1
  }

  return ageDiff
})
</script>

<template>
  <VCard class="mb-4 py-1">
    <VCardText>
      <p class="text-lg d-flex justify-space-between mb-5">
        <span>
          <VIcon
            icon="tabler-user"
            size="small"
            class="me-2"
          />
          <b class="ml-3">Personal Details</b>
        </span>
        <span
          class="cursor-pointer"
          v-if="hasPermissions('people.employee_profile_personal_detail.update', props.data)"
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
        @submit.prevent="updateRow('personal_details', props.data)"
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
              <span class="me-1 text-label">Full Name:</span>
              <span v-if="!edit_fields" style="margin-left :4.7em">{{ props.data.first_name }} {{ props.data.last_name }}</span>
              <span v-else>
                <AppTextField
                 class="mb-3"
                  v-model="props.data.first_name"
                  :rules="[requiredValidator]"
                  placeholder="John"
                />
                <AppTextField
                  v-model="props.data.last_name"
                  :rules="[requiredValidator]"
                  placeholder="Doe"
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
              <span class="me-1 text-label">Gender:</span>
              <span v-if="!edit_fields" style="margin-left :6.1em"> {{ props.data.gender }}  </span>
              <span v-else>
                <AppSelect
                  v-model="props.data.gender"
                  placeholder="Select Gender"
                  :items="['Male', 'Female', 'Non-Binary', 'Prefer not to say', 'Other']"
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
              <span class="me-1 text-label">Pronouns:</span>
              <span v-if="!edit_fields" style="margin-left :4.9em">  {{ props.data.pronouns }} </span>
              <span v-else>
                <AppSelect
                  v-model="props.data.pronouns"
                  placeholder="pronouns"
                  :items="['She/Her', 'He/Him', 'They/Them', 'Ze/Hir', 'Xe/Xem', 'Prefer not to say', 'Other']"
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
              <span class="me-1 text-label">Indigenous:</span>
              <span v-if="!edit_fields" style="margin-left :4.1em">{{ props.data.indigenous }} </span>
              <span v-else>
                <AppSelect
                  v-model="props.data.indigenous"
                  placeholder="indigenous"
                  :items="['Aboriginal', 'Torres Strait Islander', 'Both Aboriginal and Torres Strait Islander', 'Neither Aboriginal nor Torres Strait Islander', 'Prefer not to say',]"
                />
              </span>
            </VListItemTitle>
          </VListItem>
            <div class="tw-px-2 tw-justify-start tw-w-100 tw-mb-4" style="margin-left: 25px">
                <b class="tw-font-bolder text-label">DOB:</b>
                <span v-if="!edit_fields" style="margin-left :7.5em">{{ formatDisplayDate(props.data.dob) }} </span>
                <VueDatePicker v-else v-model="props.data.dob" text-input="input" format="dd/MM/yyyy" :enable-time-picker="false" style="z-index: 99999;" placeholder="Select Date of birth"/>
            </div>
          <VListItem v-if="!edit_fields">
            <template #prepend>
              <VIcon
                size="20"
                class="me-2"
              />
            </template>
            <VListItemTitle>
              <span class="me-1 text-label">Age:</span>
              <span style="margin-left :7.7em">{{ age }} </span>
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
              <div class="d-flex justify-space-between" v-if="!edit_fields" style="width: 330px;">
                <span class="me-1 text-label">Address:</span>
                <span class="address-personal">{{ props.data.user.employee.address }}</span>
              </div>
              <div v-else>
                <span class="me-1 text-label">Address:</span>
                <span class="align-center">

                  <!--Autocomplete
                    v-model="address"
                    @update:modelValue="updateAddress"
                  /-->

                  <AppTextField
                    v-model="props.data.address"
                    :rules="[requiredValidator]"
                    placeholder="address"
                    @keyup.enter="updateRow('address', props.data.address)"
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
              <span class="me-1 text-label">Personal Phone:</span>
              <span v-if="!edit_fields" style="margin-left :1.9em">{{ props.data.personal_phone }} </span>
              <span v-else>
                <AppTextField
                  type="number"
                  v-model="props.data.personal_phone"
                  :rules="[requiredValidator]"
                  placeholder="Personal Phone"
                />
              </span>
            </VListItemTitle>
          </VListItem>
          <VListItem class="mb-2">
            <template #prepend>
              <VIcon
                size="20"
                class="me-2"
              />
            </template>
            <VListItemTitle>
              <span class="me-1 text-label">Personal Email:</span>
              <span v-if="!edit_fields" style="margin-left :2.3em">{{ props.data.personal_email }} </span>
              <span v-else>
                <AppTextField
                  v-model="props.data.personal_email"
                  :rules="[requiredValidator, emailValidator]"
                  placeholder="Personal Email"
                />
              </span>
            </VListItemTitle>
          </VListItem>
            <VBtn
              style="margin-inline-start: 25px;"
              v-if="edit_fields"
              type="submit"
              class="me-3"
              :loading="loading"
              :disabled="!isFormValid"
            >
              Save
            </VBtn>

        </VList>
      </VForm>
    </VCardText>
  </VCard>
</template>

<style lang="scss">
.card-list {
  --v-card-list-gap: 16px;
}

.text-label {
  color: black !important;
  font-weight: 900;
}

.employee-card {
  border-radius: 0;
  border-inline-start: 1px solid rgb(204, 204, 204);
  margin-inline-start: 10px;
}

.employee-card > div {
  margin-inline-start: -5px;
}
.address-personal {
  margin-left: 5.6em;
  white-space: normal;
}
</style>
