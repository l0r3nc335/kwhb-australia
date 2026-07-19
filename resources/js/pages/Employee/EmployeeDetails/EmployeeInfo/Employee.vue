<script setup>
import JobTitleApi from '@/api/jobTitle/jobTitleApi'
import toast from '@/composable/toast.js'
import { division_icon } from '@/plugins/svg_handlers.js'
import { useDivisionStore } from "@/store/divisionStore"
import { useEmployeeStore } from "@/store/employeeStore"
import { useLocationStore } from "@/store/locationStore"
import { useRoleStore } from "@/store/roleStore"
import { emailValidator, requiredValidator } from '@core/utils/validators'

const emit = defineEmits(['refresh-header'])

const props = defineProps({
  data: {
    type: null,
    required: true,
  },
})

const employeeStore = useEmployeeStore()
const locationStore = useLocationStore()
const roleStore = useRoleStore()
const divisionStore = useDivisionStore()
const isPasswordVisible = ref(false)
const isPasswordVisible2 = ref(false)
const loading = ref(false)
const loadingNew = ref(false)

let edit_fields = ref(false)
let edit_fields_new = ref(false)
let edit_password = ref(false)
const isFormValid = ref(false)
const managers = ref([])
const detail = ref()
detail.value = employeeStore.data.employee_selected

// new: list of all job titles
const jobTitles = ref([]);

const selectedJobTitle = computed(() => {
  const jt = jobTitles.value.find(j => j.id === props.data.position_id)
  return jt ? jt.name : '-'
})

const activeLocations = computed(() =>
  locationStore.data.locations.filter(loc => {
    // only active
    if (Number(loc.is_active) !== 1) return false

    // include if no valid_from, or valid_from is today/past
    if (!loc.valid_from) return true
    const validDate = new Date(loc.valid_from)
    const today     = new Date()
    // zero out times so you’re comparing just dates
    validDate.setHours(0,0,0,0)
    today.setHours(0,0,0,0)

    return validDate <= today
  })
)


const fetchJobTitles = async () => {
  const res = await JobTitleApi.fetchActiveJobTitles({ page: 1, per_page: -1 })
  jobTitles.value = res.data || []
}

const updateRow = async (row, data) => {
  fetchLineManager()
  loading.value =true
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
    detail.value = employeeStore.data.employee_selected
    loading.value = false
    edit_fields.value = false
    emit('refresh-header')
  }
}

const updateRowNew = async (row, data) => {
  loadingNew.value =true
    console.log(props.data.user)
  data.role_id = props.data.user.role?.id || props.data.user?.role_id
  data.position_id = props.data.position_id
  var update = await employeeStore.updateRow({
    id: props.data.id,
    data,
    row,
  })
    fetchLineManager()

    if(!update )
    toast('error',"Error please try again!")
  else  {
    toast('success',"Updated!")
    employeeStore.setEmployeeSelected(update)
    detail.value = employeeStore.data.employee_selected
    loadingNew.value = false
    edit_fields_new.value = false
    emit('refresh-header')
  }
}


const fetchLineManager = async() => {
    let division_id = props.data?.division?.id
    if (division_id) {
        const response = await employeeStore.fetch_line_managers({division_id: division_id })
        managers.value = response
    }

}

const managersName = computed(() =>
  managers.value.map(manager => ({
    id: manager.id,
    fullName: `${manager.first_name} ${manager.last_name}`
  }))
);

onMounted(async () => {
  fetchLineManager()
  await divisionStore.setDivisions();
  await locationStore.setLocations();
  await roleStore.setRoles();
  await fetchJobTitles();
})

</script>

<template>
  <VCard class="mb-4 pa-1">
    <VCardText>
      <template v-if="hasPermissions('people.employee_dashboard_employee_section.view', props.data)">
          <p class="text-lg mb-5 d-flex justify-space-between">
              <b>Employee</b>
              <span
                  class="cursor-pointer"
                  v-if="hasPermissions('people.employee_dashboard_employee_section.update', props.data)"
                  @click="edit_fields_new = !edit_fields_new"
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
              @submit.prevent="updateRowNew('employee', props.data)"
          >
              <VList class="card-list text-medium-emphasis">
                  <VListItem>
                      <template #prepend>
                          <VIcon
                              icon="tabler-user"
                              size="20"
                              class="me-2"
                          />
                      </template>
                      <VListItemTitle>
                          <span class="text-label me-1">Full Name:</span>
                          <span v-if="!edit_fields_new">{{ detail.first_name }} {{ detail.last_name }}</span>
                          <span v-else>
                          <AppTextField
                              v-model="props.data.first_name"
                              :rules="[requiredValidator]"
                              class="mb-3"
                          />
                          <AppTextField
                              v-model="props.data.last_name"
                              :rules="[requiredValidator]"
                          />
                        </span>
                      </VListItemTitle>

                  </VListItem>
                    <VListItem>
                    <template #prepend>
                        <VIcon icon="tabler-briefcase" size="20" class="me-2" />
                    </template>
                    <VListItemTitle>
                        <span class="text-label me-1">Job Title:</span>
                        <!-- show the saved name when not editing -->
                        <span v-if="!edit_fields_new">{{ selectedJobTitle }}</span>
                        <!-- replace with a dropdown when editing -->
                        <AppSelect
                        v-else
                        v-model="props.data.position_id"
                        :items="jobTitles"
                        item-title="name"
                        item-value="id"
                        placeholder="Select Job Title"
                        :rules="[requiredValidator]"
                        class="mb-3"
                        />
                    </VListItemTitle>
                    </VListItem>
                  <VListItem>
                      <template #prepend>
                          <VIcon
                              icon="tabler-crown"
                              size="20"
                              class="me-2"
                          />
                      </template>
                      <VListItemTitle>
                          <span class="text-label me-1">Role:</span>
                          <span v-if="!edit_fields_new"> {{ employeeStore.data.employee_selected?.user?.role?.name }} </span>
                          <AppSelect
                              v-else
                              :disabled="inRoles(['User'], true)"
                              v-model="props.data.user.role_id"
                              placeholder="Role"
                              item-title="name"
                              item-value="id"
                              :rules="[requiredValidator]"
                              :items="roleStore.data.roles"
                          />
                      </VListItemTitle>
                  </VListItem>
                  <VListItem>
                      <template #prepend>
                          <VIcon
                              icon="tabler-user"
                              size="20"
                              class="me-2"
                          />
                      </template>
                      <VListItemTitle>
                          <span class="text-label me-1">Manager:</span>
                          <span v-if="!edit_fields_new"> {{ detail.manager ? detail.manager.first_name + " " + detail.manager.last_name : '' }}</span>
                          <AppSelect
                              v-else
                              v-model="props.data.manager_id"
                              placeholder="Manager"
                              :item-value="(obj) => obj['id']"
                              :item-title="(obj) => obj['fullName']"
                              :rules="[requiredValidator]"
                              :items="managersName"
                          />
                      </VListItemTitle>
                  </VListItem>
                  <VListItem>
                      <template #prepend>
                          <div
                              class="custom-svg-icon"
                              v-html="division_icon"
                          />
                      </template>
                      <VListItemTitle>
                          <span class="text-label me-1 ml-4">Division:</span>
                          <span v-if="!edit_fields_new">{{ detail.division?.name }} </span>
                          <AppSelect
                              v-else
                              class="me-1 ml-4"
                              v-model="props.data.division_id"
                              placeholder="Division"
                              item-title="name"
                              item-value="id"
                              :rules="[requiredValidator]"
                              :items="divisionStore.data.divisions"
                              @update:modelValue="fetchLineManager"
                          />
                      </VListItemTitle>
                  </VListItem>
                  <VBtn
                      style="margin-inline-start: 25px;"
                      v-if="edit_fields_new"
                      :loading="loadingNew"
                      type="submit"
                      class="me-3"
                  >
                      Save
                  </VBtn>
                  <!--
                    <VListItem>
                    <template #prepend>
                    <VIcon
                    icon="tabler-color-filter"
                    size="20"
                    class="me-2"
                    />
                    </template>

                    <VListItemTitle>
                    <span class="text-label me-1">Group:</span>
                    <span>(group name)</span>
                    </VListItemTitle>

                    </VListItem>
                  -->
              </VList>
          </VForm>
      </template>
      <br>
      <template v-if="hasPermissions('people.employee_profile_contact_section.view', props.data)">
          <p class="text-lg d-flex justify-space-between mb-5">
        <span>
          <b>Contact</b>
        </span>
              <span
                  class="cursor-pointer"
                  v-if="hasPermissions('people.employee_profile_contact_section.update', props.data)"
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
              @submit.prevent="updateRow('contact', props.data)"
          >
              <VList class="card-list text-medium-emphasis">
                  <VListItem>
                      <template #prepend>
                          <VIcon
                              icon="tabler-phone-call"
                              size="20"
                              class="me-2"
                          />
                      </template>
                      <VListItemTitle>
                          <span class="text-label me-1">Work Phone:</span>
                          <span v-if="!edit_fields">{{ detail.work_phone }}</span>
                          <span v-else>
                        <AppTextField
                            v-model="props.data.work_phone"
                            placeholder="Work Phone"
                            type="tel"
                            @input="props.data.work_phone = $event.target.value.replace(/[^0-9]/g, '')"
                        />
              </span>
                      </VListItemTitle>
                  </VListItem>
                  <VListItem>
                      <template #prepend>
                          <VIcon
                              icon="tabler-mail"
                              size="20"
                              class="me-2"
                          />
                      </template>
                      <VListItemTitle>
                          <span class="text-label me-1">Work Email:</span>
                          <span v-if="!edit_fields">{{ detail.work_email }}</span>
                          <span v-else>
                <AppTextField
                    v-model="props.data.work_email"
                    placeholder="Work Email"
                    :rules="[emailValidator]"
                />
              </span>
                      </VListItemTitle>
                  </VListItem>
                  <VListItem>
                      <template #prepend>
                          <VIcon
                              icon="tabler-map-pin"
                              size="20"
                              class="me-2"
                          />
                      </template>
                      <VListItemTitle v-if="detail.location">
                          <!-- <span class="text-label me-1">Location:</span> -->
                          <span class="text-label" v-if="!edit_fields">{{ detail.location?.name }}</span>
                          <AppSelect
                              v-else
                              v-model="props.data.location_id"
                              placeholder="Select Location"
                              item-title="name"
                              item-value="id"
                              :rules="[requiredValidator]"
                              :items="activeLocations"
                          />
                      </VListItemTitle>
                  </VListItem>
                  <VBtn
                      style="margin-inline-start: 25px;"
                      v-if="edit_fields"
                      :loading="loading"
                      :disabled="!isFormValid"
                      type="submit"
                      class="me-3"
                      @click="handleSave"
                  >
                      Save
                  </VBtn>
              </VList>
          </VForm>
      </template>
      <br>
      <br>
      <template v-if="hasPermissions('people.employee_profile_contact_section.view', props.data)">
          <VList class="card-list text-medium-emphasis mb-2">
              <VListItem>
                  <template #prepend>
                      <VIcon
                          icon="tabler-lock"
                          size="20"
                          class="me-2"
                      />
                  </template>
                  <VListItemTitle>
            <span
                v-if="!edit_password"
                class="text-label me-1"
            >Password:</span>
                      <span
                          v-if="edit_password"
                          class="text-label me-1"
                      >New Password:</span>
                      <span v-if="!edit_password">********</span>
                      <AppTextField
                          v-else
                          v-model="props.data.password1"
                          :type="isPasswordVisible2 ? 'text' : 'password'"
                          :append-inner-icon="isPasswordVisible2 ? 'tabler-eye-off' : 'tabler-eye'"
                          @click:append-inner="isPasswordVisible2 = !isPasswordVisible2"
                      />
                  </VListItemTitle>
              </VListItem>
              <VListItem v-if="edit_password">
                  <template #prepend>
                      <VIcon
                          v-if="edit_password"
                          icon="tabler-lock"
                          size="20"
                          class="me-2"
                      />
                  </template>
                  <VListItemTitle>
            <span
                v-if="edit_password"
                class="text-label me-1"
            >Re-enter Password:</span>
                      <AppTextField
                          v-if="edit_password"
                          v-model="props.data.password"
                          :type="isPasswordVisible ? 'text' : 'password'"
                          :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                          @click:append-inner="isPasswordVisible = !isPasswordVisible"
                          @keyup.enter="updateRow('password', props.data.password)"
                      />
                  </VListItemTitle>
              </VListItem>
          </VList>
          <div
              v-if="props.data.password1 != props.data.password"
              style="color: red; font-size: small;"
          >
              Passwords do not match!
          </div>
          <VBtn
              v-if="!edit_password"
              color="secondary"
              variant="outlined"
              class="mt-1 mr-3"
              @click="edit_password = !edit_password"
          >
              Reset Password
          </VBtn>
          <VBtn
              v-if="edit_password"
              color="secondary"
              variant="outlined"
              class="mt-4 mr-3"
              @click="edit_password = !edit_password"
          >
              Cancel
          </VBtn>
          <VBtn
              v-if="edit_password"
              color="primary"
              variant="tonal"
              class="mt-4"
              @click="updateRow('password', props.data.password); edit_password = !edit_password"
          >
              Save
          </VBtn>
      </template>
    </VCardText>
  </VCard>
</template>

<style lang="scss" scoped>
.card-list {
  --v-card-list-gap: 16px;
}
</style>
