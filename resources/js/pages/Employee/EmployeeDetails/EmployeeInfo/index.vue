<script setup>
import { useEmployeeStore } from "@/store/employeeStore";
import EmergencyContact from './EmergencyContact.vue';
import Employee from './Employee.vue';
import PersonalDetails from './PersonalDetails.vue';

const employeeStore = useEmployeeStore();
const profileTabData = ref()
profileTabData.value = employeeStore.data.employee_selected
const emit = defineEmits(['refresh-header'])

const refreshHeaderData = async () => {
  emit('refresh-header')
}

// import Connection from './Connection.vue'
// import ProjectList from './ProjectList.vue'
// import Teams from './Teams.vue'

// const route = useRoute('EmployeeDetails')

// console.log("router.params.tab", route.params.tab)
// const fetchAboutData = async () => {
//   console.log("router.params.tab",router.params.tab);
//   if (router.params.tab === 'EmployeeInfo') {
//     const data = await $api('/pages/profile', { query: { tab: router.params.tab } }).catch(err => console.log(err))

//     profileTabData.value = data
//   }
// }

// watch(router, fetchAboutData, { immediate: true })
</script>

<template>
  <VRow v-if="profileTabData">

    <VCol
      md="4"
      cols="12"
    >
      <Employee @refresh-header="refreshHeaderData" :data="profileTabData"/>
    </VCol>
    <VCol
      md="4"
      cols="12"
    >
      <PersonalDetails :data="profileTabData" v-if="hasPermissions('people.employee_profile_personal_detail.view', profileTabData.value)" />
    </VCol>
    <VCol
      md="4"
      cols="12"
    >
      <EmergencyContact :data="profileTabData" v-if="hasPermissions('people.employee_info_emergency_contact.view', profileTabData.value)"/>
    </VCol>
  </VRow>
</template>
