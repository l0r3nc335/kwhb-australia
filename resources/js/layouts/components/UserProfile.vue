<script setup>
import { ProfilePlaceHolder } from '@/plugins/profilePlaceHolder';
import { useAuthStore } from "@/store/authStore";
// import avatar1 from '@images/avatars/avatar-1.png';
import { useEmployeeStore } from "@/store/employeeStore";
import { storeToRefs } from 'pinia';
import { useRouter } from 'vue-router';
const { userData } = storeToRefs(useAuthStore())
const employeeStore = useEmployeeStore()

const router = useRouter()
const route = useRoute()
const activeItem = ref('')
const logout = async () => {
  await authStore.logout()
  router.push("login")
}

const authStore = useAuthStore()

const initials = computed(() => {
  return userData.value.employee.first_name.charAt(0).toUpperCase() + userData.value.employee.last_name.charAt(0).toUpperCase()

})
let profile_image = userData.value.employee.profile_image ?? ProfilePlaceHolder(initials.value)

const capitalFirst = (e) => {
  return e.charAt(0).toUpperCase() + e.slice(1)
}

const fullName = computed(() => {
  return capitalFirst(userData.value.employee.first_name) + ' ' + capitalFirst(userData.value.employee.last_name)
})

const navTo = (item) => {
  activeItem.value = item;
  let employeeSelected = employeeStore.data.employees.find(e => e.user_id == userData.value.employee.user_id)
  employeeStore.setEmployeeSelected(employeeSelected)
  employeeStore.setActiveTab('EmployeeInfo')
  router.push({ path: '/employee/employeedetails', params: { tab: 'EmployeeInfo' } })
}
</script>

<template>
  <span style="color: #33468d;font-family: Arial, Helvetica, sans-serif; font-weight: 600;  margin: auto 10px!important;">{{ 'Hello ' + userData.employee.first_name }}</span>
  <VBadge
    dot
    location="bottom right"
    offset-x="3"
    offset-y="3"
    bordered
    color="success"
    style="margin: auto 10px!important;"
  >
    <VAvatar
      class="cursor-pointer"
      color="primary"
      variant="tonal"
    >
      <VImg :src="profile_image" />

      <!-- SECTION Menu -->
      <VMenu
        activator="parent"
        width="230"
        location="bottom end"
        offset="14px"
      >
        <VList>
          <!-- 👉 User Avatar & Name -->
          <VListItem>
            <template #prepend>
              <VListItemAction start>
                <VBadge
                  dot
                  location="bottom right"
                  offset-x="3"
                  offset-y="3"
                  color="success"
                >
                  <VAvatar
                    color="primary"
                    variant="tonal"
                  >
                    <VImg :src="profile_image" />
                  </VAvatar>
                </VBadge>
              </VListItemAction>
            </template>

            <VListItemTitle class="font-weight-semibold">
              {{ fullName }}
            </VListItemTitle>
            <VListItemSubtitle>{{ capitalFirst(userData.employee.job_title)  }}</VListItemSubtitle>
          </VListItem>

          <VDivider class="my-2" />

          <!-- 👉 Profile -->
          <VListItem @click="navTo('profile')" :value="'profile'" :active="activeItem === 'profile' && route.name === 'EmployeeDetails'">
            <template #prepend>
              <VIcon
                class="me-2"
                icon="tabler-user"
                size="22"
              />
            </template>

            <VListItemTitle>Profile</VListItemTitle>
          </VListItem>

          <!-- 👉 Settings -->
          <!-- <VListItem link>
            <template #prepend>
              <VIcon
                class="me-2"
                icon="tabler-settings"
                size="22"
              />
            </template>

            <VListItemTitle>Settings</VListItemTitle>
          </VListItem> -->

          <!-- 👉 Pricing -->
          <!-- <VListItem link>
            <template #prepend>
              <VIcon
                class="me-2"
                icon="tabler-currency-dollar"
                size="22"
              />
            </template>

            <VListItemTitle>Pricing</VListItemTitle>
          </VListItem> -->

          <!-- 👉 FAQ -->
          <!-- <VListItem link>
            <template #prepend>
              <VIcon
                class="me-2"
                icon="tabler-help"
                size="22"
              />
            </template>

            <VListItemTitle>FAQ</VListItemTitle>
          </VListItem> -->

          <!-- Divider -->
          <VDivider class="my-2" />

          <VListItem @click="logout">
            <template #prepend>
              <VIcon
                class="me-2"
                icon="tabler-logout"
                size="22"
              />
            </template>

            <VListItemTitle>Logout</VListItemTitle>
          </VListItem>
        </VList>
      </VMenu>
      <!-- !SECTION -->
    </VAvatar>
  </VBadge>
</template>
<style scoped>
.active {
  background-color: rgb(var(--v-theme-primary));
}
</style>
