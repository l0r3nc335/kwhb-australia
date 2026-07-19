<script setup>
import { useEmployeeStore } from "@/store/employeeStore"
import { computed, watch } from 'vue'
import { useRouter } from 'vue-router'

import ActivityLog from '@/pages/Employee/EmployeeDetails/ActivityLog/index.vue'
import Certifications from '@/pages/Employee/EmployeeDetails/Certifications/index.vue'
import EmployeeInfo from '@/pages/Employee/EmployeeDetails/EmployeeInfo/index.vue'
import LeaveInfo from '@/pages/Employee/EmployeeDetails/Leave/index.vue'
import Notes from '@/pages/Employee/EmployeeDetails/Notes/index.vue'
import PayInfo from '@/pages/Employee/EmployeeDetails/Pay/index.vue'
import UserProfileHeader from '@/pages/Employee/EmployeeDetails/UserProfileHeader.vue'
import { leave_tab_icon, pay_tab_icon } from '@/plugins/svg_handlers.js'

let c_leave_tab_icon = leave_tab_icon
let c_pay_tab_icon = pay_tab_icon
const router = useRouter()
const employeeStore = useEmployeeStore()

const refreshHeaderData = async () => {
  console.log("Highest")
}

const activeTab = computed({
  get: () => employeeStore.data.routeTab,
  set: value => { employeeStore.setActiveTab(value) },
})

watch(activeTab, (newValue, oldValue) => {
  //console.log('activeTab changed from', oldValue, 'to', newValue)
  c_leave_tab_icon = c_leave_tab_icon.replace(/fill:#051C73/g, "fill:#808494");
  c_pay_tab_icon = c_pay_tab_icon.replace(/fill:#051C73/g, "fill:#808494");

  if(newValue == "Leave")
    c_leave_tab_icon = c_leave_tab_icon.replace(/fill:#808494/g, "fill:#051C73")
  if(newValue == "Pay")
    c_pay_tab_icon = c_pay_tab_icon.replace(/fill:#808494/g, "fill:#051C73")
})

const tabs = [
  {
    title: 'Employee Info',
    icon: 'tabler-user-check',
    tab: 'EmployeeInfo',
    permissions: [
        'people.employee_dashboard_employee_section.view',
        'people.employee_profile_contact_section.view',
        'people.employee_profile_personal_detail.view',
        'people.employee_info_emergency_contact.view'
    ]
  },
  {
    title: 'Certifications',
    icon: 'tabler-certificate',
    tab: 'Certifications',
    permissions: [
        'people.employee_certification_vaccinations.view',
        'people.employee_certification_compliance.view'
    ]
  },
  {
    title: 'Pay',
    icon: 'customPay',
    tab: 'Pay',
    permissions: [
        'people.employee_pay_bank_information.view',
        'people.employee_pay_tfn.view',
        'people.employee_super_info.view'
    ]
  },
  {
    title: 'Leave',
    icon: 'customLeave',
    tab: 'Leave',
    permissions: [
        'people.employee_leave.view'
    ]
  },
  {
    title: 'Notes',
    icon: 'tabler-file-text',
    tab: 'Notes',
      permissions: [
          'people.employee_notes.view'
      ]
  },
  {
    title: 'Activity Log',
    icon: 'tabler-layout-grid',
    tab: 'ActivityLog',
      permissions: [
          'people.employee_activity_logs.view'
      ]
  },
]

// if(!profileTabData.value)
// {
//   console.log("No employee selected");
//   router.push("employee")
// }
</script>

<template>
  <div>
    <UserProfileHeader class="mb-5" />
    <VTabs
      v-model="activeTab"
      class="v-tabs custom-tabs"
      color="#051c73"
    >
    <template  v-for="item in tabs"
               :key="item.icon">
        <VTab
            v-if="hasPermissions(item.permissions, employeeStore.data.employee_selected, true)"
            :value="item.tab"
            class="font-weight-bold text-none"
        >
            <template v-if="item.icon === 'customPay'">
          <span class="custom-icon mr-2 ml-n1"  v-html="c_pay_tab_icon">
          </span>
            </template>
            <template v-else-if="item.icon === 'customLeave'">
          <span class="custom-icon mr-2 ml-n1" v-html="c_leave_tab_icon">
          </span>
            </template>
            <template v-else>
                <VIcon
                    size="20"
                    start
                    :icon="item.icon"
                />
            </template>
            {{ item.title }}
        </VTab>
    </template>

    </VTabs>

    <VWindow
      v-model="activeTab"
      class="mt-5 disable-tab-transition"
    >
      <VWindowItem value="EmployeeInfo">
        <EmployeeInfo @refresh-header="refreshHeaderData" /> 
      </VWindowItem>

      <VWindowItem value="Certifications">
        <Certifications />
      </VWindowItem>

      <VWindowItem value="Pay">
        <PayInfo @reload-employees=""/>
      </VWindowItem>

      <VWindowItem value="Leave">
        <LeaveInfo />
      </VWindowItem>

      <VWindowItem value="ActivityLog" >
        <ActivityLog/>
      </VWindowItem>

      <VWindowItem value="Notes" >
        <Notes/>
      </VWindowItem>
    </VWindow>
  </div>
</template>

<style lang="scss">
.custom-active-tab {
  background-color: #007bff !important; /* Set your desired background color */
  color: #fff !important; /* Set your desired text color */
}
.custom-tabs .v-btn__content:not(.v-tab--selected) {
  color: #808494;
  font-family: Arial, Helvetica, sans-serif;
}

.custom-tabs .v-tab--selected .v-btn__content  {
  color: #051C73;
  font-family: Arial, Helvetica, sans-serif;
}
</style>
