<script setup>
import toast from '@/composable/toast.js'
import { ProfilePlaceHolder } from '@/plugins/profilePlaceHolder'
import { brief_case_icon } from '@/plugins/svg_handlers.js'
import { useEmployeeStore } from "@/store/employeeStore"
import { useRoleStore } from "@/store/roleStore"
import '@vuepic/vue-datepicker/dist/main.css'
import { getCurrentInstance, ref } from 'vue'
import { useRouter } from 'vue-router'

const app = getCurrentInstance().appContext.app
const router = useRouter()
const employeeStore = useEmployeeStore()
const roleStore = useRoleStore()

// const vDatePicker = VDatepicker()
// const profileHeaderData = ref()
const upload_image = ref(null)
const profile_upload_image = ref(null)
const loading = ref(false)
const attachment_path = app.config.globalProperties.$attachment_path

// const selectedDate = ref('2024-05-19')
const employeeStatus = ref()
const selectedDate = ref(new Date())
const statusSelected = ref()
const roleSelected = ref()
const datePickerRef = ref(null)
const isMenuOpen = ref(false)
const loadingSave = ref(false)


const profileHeaderData = computed(() => employeeStore.data.employee_selected)

// if(!profileHeaderData.value.first_name)
// {
//   router.push("employee")
// }

const initials = computed(() => {
  return (
    profileHeaderData.value.first_name.charAt(0).toUpperCase() +
    profileHeaderData.value.last_name.charAt(0).toUpperCase()
  )
})

const form = ref({
  image_url: profileHeaderData.value.cover_image ? profileHeaderData.value.cover : null,
  image_data: null,
  //profile_image_url: profileHeaderData.value.profile_image ? attachment_path + "profile_image/" +  profileHeaderData.value.profile_image.file_name
  profile_image_url: profileHeaderData.value.profile_image ? profileHeaderData.value.image
    : ProfilePlaceHolder(initials.value),
  profile_image_data: null,
  job_title: profileHeaderData.value.job_title ?? '',
  phone: profileHeaderData.value.work_phone ?? '',
  personal_email: profileHeaderData.value.personal_email ?? '',
  work_email: profileHeaderData.value.work_email ?? '',
})

const triggerSelectImage = type => {
  if (type == 'cover') {
    upload_image.value.click()
  } else {
    profile_upload_image.value.click()
  }
}

const onSelectFile = type => {
  let input = null
  if (type == 'cover') {
    input = upload_image
  } else {
    input = profile_upload_image
  }
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
    let reader = new FileReader()
    reader.onload = e => {
      if (type == 'cover') {
        form.value.image_data = files[0]
        form.value.image_url = e.target.result
      } else {
        form.value.profile_image_data = files[0]
        form.value.profile_image_url = e.target.result
      }
    }
    reader.readAsDataURL(files[0])
  }
}

const cancelBtn = type => {
  if (type == 'cover') {
    form.value.image_data = ''
    form.value.image_url = profileHeaderData.value.cover_photo
      ? profileHeaderData.value.cover_image
      : null

  } else {
    form.value.profile_image_data = ''
    form.value.profile_image_url = profileHeaderData.value.picture
      ? profileHeaderData.value.profile_image
      : ProfilePlaceHolder(initials.value)
  }
}

const saveBtn = async type => {
  var attachment_id = null
  var image_url = null
  if (type == 'cover') {
    attachment_id = profileHeaderData.value.cover_image != null ? profileHeaderData.value.cover_image.id : null
    image_url = form.value.image_url
  } else if (type == 'profile') {
    attachment_id = profileHeaderData.value.profile_image != null ? profileHeaderData.value.profile_image.id : null
    image_url = form.value.profile_image_url
  }

  // pag save dapat set ang form image url
  const formData = {
    image_url: image_url,
    image: type == 'cover' ? 'cover_image' : 'profile_image',
    attachment_id: attachment_id,
    emp_id: profileHeaderData.value.id,
  }

  try {
    loading.value = true
    const response = await employeeStore.updateAttachment(formData)
    if(type == 'cover'){
      toast('success', 'Cover Image Updated!')
    }else{
      toast('success', 'Profile Image Updated!')
    }
    loading.value = false

    // form.value.image_url = pro
    form.value.image_data = ''
    form.value.profile_image_data = ''
  } catch (error) {
    loading.value = false
  }
}

const updateRow = async (row, data)=>{

  loadingSave.value = true
  let updatedEmployee = await employeeStore.updateUser({
    id: profileHeaderData.value.id,
    data: data && data.length ? data : profileHeaderData.value.status,
    dateSelected: selectedDate.value ? selectedDate.value : null,
    selectedRole: roleSelected.value ? roleSelected.value : null,
    row,
  })
    employeeStore.setEmployeeSelected(updatedEmployee)
  toast('success',"Status Updated!")
  loadingSave.value = false
  statusSelected.value = [updatedEmployee.status]
    employeeStatus.value = updatedEmployee.status
  roleSelected.value = []
  isMenuOpen.value=false
}

const formatEmployeeNumber = num => {
  return String(num).padStart(5, '0')
}

const formatDateToMonthYear = (dateString) => {
let [day, month, year] = dateString.split('/').map(Number);
let date = new Date(year, month - 1, day);

let options = { year: 'numeric', month: 'long' };
return new Intl.DateTimeFormat('en-US', options).format(date);
}

const onMenuOpen = (value) => {
 /*  if (value) {
    setTimeout(async () => {
      await nextTick()
      console.log(datePickerRef.value)

      const datePickerElement = datePickerRef.value.$el.querySelector('.dp__flex_display')

      console.log(datePickerElement)
      if (datePickerElement) {
        datePickerElement.classList.remove('dp__flex_display')
      }
    }, 100) // Adjust the delay as needed
  } */
}

const formatDate = dateStr => {
  let date

  if (dateStr.includes('/')) {
    const [month, day, year] = dateStr.split('/').map(Number)

    date = new Date(year, month - 1, day)
  }
  else if (dateStr.includes('-')) {
    const [year, month, day] = dateStr.split('-').map(Number)

    date = new Date(year, month - 1, day)
  }
  else {
    throw new Error('Invalid date format. Please use mm/dd/yyyy or yyyy-mm-dd.')
  }

  // return `${date.toLocaleString('default', { month: 'long' })} ${date.getDate()}, ${date.getFullYear()}`
  return `${date.toLocaleString('default', { month: 'long' })} ${date.getFullYear()}`
}


const dropdownVisible = ref(false);

const toggleDropdown = () => {
  // console.log(dropdownVisible.value);
  dropdownVisible.value = !dropdownVisible.value;
};

const handleClickOutside = (event) => {
 /*  const dropdownWrapper = ref('dropdownWrapper');
  if (dropdownWrapper.value && !dropdownWrapper.value.contains(event.target)) {
    dropdownVisible.value = false;
  } */
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
  employeeStatus.value = profileHeaderData.value?.status
  roleSelected.value = employeeStore.data.employee_selected?.user?.role
});

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside);
});

const capitalizeFirst = (str) => {
    return str.split(' ').map(word => {
        return word.charAt(0).toUpperCase() + word.slice(1);
    }).join(' ');
}

const getTextColor = (status) => {
  switch (status && capitalizeFirst(status)) {
  case 'Active':
    return '#61c378 !important'
  case 'Offswing':
    return '#e35205 !important'
  case 'Pending':
    return '#5c7fff !important'
  case 'Extended Leave':
  case 'Extended leave':
    return '#f1b434 !important'
  case 'Terminated':
    return '#E7404C !important'
  default:
    return '#363636 !important'
  }
}
</script>

<template>
  <div>
    <div class="mb-6 font-weight-bold text-h2">
      <span style="color: #808494;cursor:pointer" @click="$router.push('/employee')">Employee / </span>
      <span style="color: #000829;">{{ profileHeaderData?.first_name }} {{ profileHeaderData?.last_name }}</span>
    </div>
    <VRow>
      <VCol cols="12">
        <VCard v-if="profileHeaderData">
          <div
            :class="form.image_url ? 'cover-photo' : 'cover-photo-wrapper'"
            style=" position: relative;block-size: 250px;"
            :style="
              form.image_url
                ? { backgroundImage: 'url(' + form.image_url + ')'}
                : {
                  background: 'rgb(250, 160, 255)',
                  background:
                    'linear-gradient(0deg, rgba(250, 160, 255, 100%) 0%, rgba(248, 115, 255, 100%) 40%, rgba(207, 30, 255, 100%) 100%)',
                }
            "
          >
            <span
              class="top-right px-5 py-1"
              style="position: absolute; top: 0; right: 0; border-radius: 0 0 0 5px; background-color: #F2EBF7; color: #8D899A; font-weight: 500;"
            >Employee # {{ formatEmployeeNumber(profileHeaderData?.id) }}</span>
            <span
              class="bottom-right-icon mr-3 mb-3"
            >
              <VIcon
                icon="tabler-pencil"
                style="color: rgb(122 122 122);"
                size="x-small"
                class="mb-2"
                @click="triggerSelectImage('cover')"
              /></span>

            <input
              ref="upload_image"
              type="file"
              name="upload_image"
              class="upload_image"
              accept="image/png, image/jpeg"
              style="display: none;"
              @input="onSelectFile('cover')"
            >
          </div>

          <VCardText class="d-flex align-bottom flex-sm-row flex-column justify-center gap-x-5">
            <div class="d-flex h-0 position-relative">
              <VAvatar
                rounded
                size="110"
                :image="form.profile_image_url"
                class="user-profile-avatar mx-auto"
                style="border: 12px solid #F15BB5; background-color: #CC4A04 !important;"
              />
              <VIcon
                size="small"
                icon="tabler-pencil"
                color="#FFF"
                class="top-right-icon mt-n11 mr-1"
                @click="triggerSelectImage('profile')"
              />
            </div>
            <input
              ref="profile_upload_image"
              type="file"
              name="profile_upload_image"
              class="upload_image"
              accept="image/png, image/jpeg"
              style="display: none;"
              @input="onSelectFile('profile')"
            >

            <div class="user-profile-info w-100 mt-16 pt-6 pt-sm-0 mt-sm-0">
              <div class="d-flex flex-wrap gap-4">
                <div class="mb-3 me-auto">
                  <span class="text-h4 text-center text-sm-start font-weight-bold mr-2">{{ profileHeaderData?.first_name }} {{ profileHeaderData?.last_name }}</span>
                    <template v-if="profileHeaderData.pronouns">
                        {{profileHeaderData.pronouns}}
                    </template>
                    <template v-else>
                        <span class="text-h4 font-weight-medium" v-if="profileHeaderData?.gender === 'Male' || profileHeaderData?.gender === 'male'">{{ 'he/him' }}</span>
                        <span class="text-h4 font-weight-medium" v-else>{{ 'she/her' }}</span>
                    </template>
                </div>
                <div
                  v-if="form.image_data || form.profile_image_data"
                  class="top-button"
                >
                  <VBtn @click="form.image_data ? saveBtn('cover') : saveBtn('profile')" :loading="loading">
                    Save Changes
                  </VBtn>
                  <VBtn
                    class="ml-2"
                    color="secondary"
                    @click="form.image_data ? cancelBtn('cover') : cancelBtn('profile')"
                  >
                    Cancel
                  </VBtn>
                </div>
                <VMenu
                  v-model="isMenuOpen"
                  location="bottom"
                  offset-y
                  transition="scale-transition"
                  :close-on-content-click="false"
                  @update:modelValue="onMenuOpen"
                >
                  <template #activator="{ props }">
                    <VBtn
                      append-icon="tabler-chevron-down"
                      :style="{
                        background: getTextColor(employeeStatus),
                      }"
                      class="date-picker-btn"
                      v-bind="props"
                    >
                        {{employeeStatus}}
                    </VBtn>
                  </template>
                 <VCard>
                    <!-- <VRow class="pa-1  ma-2">
                      <VCol cols="12">
                        <VueDatePicker
                          ref="datePickerRef"
                          v-model="selectedDate"
                          inline
                          auto-apply
                        />
                      </VCol>
                    </VRow> -->
                    <div class="app-date-picker-wrapper">
                      <AppDateTimePicker
                        v-model="selectedDate"
                        :config="{ inline: true }"
                      />
                    </div>


                    <VDivider />
                    <div ref="dropdownWrapper" class="dropdown-wrapper" @click.stop>
                      <AppSelect
                        v-model="statusSelected"
                        class="pa-3 ma-2"
                        placeholder="Select Status"
                        :items="employeeStore.data.statuses"
                        @click="toggleDropdown"
                        :visible="dropdownVisible"
                        :close-on-content-click="false"
                      />
                      <AppSelect
                        v-model="roleSelected"
                        class="pa-3 ma-2"
                        placeholder="Select Role"
                        :items="roleStore.data.roles"
                        item-title="name"
                        item-value="id"
                        @click="toggleDropdown"
                        :visible="dropdownVisible"
                        :close-on-content-click="false"
                      />
                    </div>
                    <VRow class="pa-3  ma-2">
                      <VCol cols="9">
                        <div v-if="profileHeaderData.scheduled_date && profileHeaderData.scheduled_status" class="tw-text-[.8rem]">
                            Scheduled Status Update: <b>{{profileHeaderData.scheduled_status}}</b> <br>
                            Effective Date: <b>{{profileHeaderData.scheduled_date}}</b>
                        </div>
                          <template v-else>
                              <span v-if="statusSelected == 'active'">from</span>
                              <span v-else>from</span>: {{ $formatDateDMY(selectedDate) }}
                          </template>
                      </VCol>
                      <VCol cols="2">
                        <VBtn
                          variant="tonal"
                          color="success"
                          :loading="loadingSave"
                          @click="updateRow('status',statusSelected)"
                        >
                          Save
                        </VBtn>
                      </VCol>
                    </VRow>
                  </VCard>
                </VMenu>
              </div>
              <div class="d-flex align-center justify-center justify-sm-space-between flex-wrap gap-4">
                <div class="d-flex flex-wrap justify-center justify-sm-start flex-grow-1 gap-4">
                  <span class="d-flex" v-if="profileHeaderData?.position?.name">
                    <VIcon
                      size="20"
                      icon="tabler-color-swatch"
                      class="me-1"
                    />
                    <span class="text-body-1">
                      {{ profileHeaderData?.position.name }}
                    </span>
                  </span>
                  <span class="d-flex" v-if="profileHeaderData?.location?.name">
                    <VIcon
                      size="20"
                      icon="tabler-map-pin"
                      class="me-1"
                    />
                    <span class="text-body-1">
                      {{ profileHeaderData?.location?.name }}
                    </span>
                  </span>
                  <span class="d-flex" v-if="profileHeaderData?.division?.name">
                    <span class="custom-svg-icon" v-html="brief_case_icon"></span>
                    <span class="text-body-1 ml-2">
                      {{ profileHeaderData?.division?.name }}
                    </span>
                  </span>
                  <span class="d-flex" v-if="profileHeaderData?.date_hired">
                    <VIcon
                      size="20"
                      icon="tabler-calendar"
                      class="me-1"
                    />
                    <span class="text-body-1">
                      Joined {{ formatDateToMonthYear(profileHeaderData.date_hired) }}
                    </span>
                  </span>
                </div>

              </div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>

<style lang="scss" >
.app-date-picker-wrapper .v-input {
    display: none;
}
.app-date-picker-wrapper  {
    .flatpickr-calendar, .flatpickr-rContainer, .flatpickr-days {
        width: 100%;
    }

    .dayContainer {
        min-inline-size: 23.625rem !important;
    }

    .flatpickr-day {
        max-inline-size: 3.125rem !important;
    }
}
.cover-photo {
  position: relative;
  z-index: -1;
  width: 100%;
  height: 300px;
  background-size: cover;
  background-position: center;
}
.cover-photo-wrapper {
  position: relative;
  z-index: -1;
}
.top-right-icon {
  position: absolute;
  top:0;
  right: 0;
  background-color: rgb(214 203 203 / 42%);
   -moz-border-radius: 60px;
   -webkit-border-radius: 60px;
   border-radius: 60px;
   opacity: 80%;
   height: 18px;
   padding: 2px;
}
.bottom-right-icon {
  position: absolute;
  bottom: 0;
  right: 0;
  background-color: rgba(0, 0, 0, 0.144);
   -moz-border-radius: 60px;
   -webkit-border-radius: 60px;
   border-radius: 60px;
   height: 21px;
   padding: 3px;
}
.user-profile-avatar {
  border: 5px solid rgb(var(--v-theme-surface));
  background-color: rgb(var(--v-theme-surface)) !important;
  inset-block-start: -3rem;

  .v-img__img {
    border-radius: 0.125rem;
  }

  .v-picker-title .v-date-picker-header {
    display: none !important;
  }
}
.hide-input .v-input {
  display: none !important;
}
.hide-input .flatpickr-calendar {
  width: auto;
}
.hide-input .flatpickr-calendar .flatpickr-innerContainer {
  width: auto;
}
.hide-input .flatpickr-calendar .flatpickr-innerContainer .flatpickr-rContainer{
  width: auto;
}
.hide-input .flatpickr-calendar .flatpickr-innerContainer .flatpickr-rContainer .flatpickr-days{
  width: auto;
}
.hide-input .flatpickr-calendar .flatpickr-innerContainer .flatpickr-rContainer .flatpickr-days .dayContainer{
  width: auto;
}
</style>
