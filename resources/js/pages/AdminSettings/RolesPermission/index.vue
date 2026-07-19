<script setup>
import { useRoleStore } from "@/store/roleStore";

import Roles from '@/pages/AdminSettings/RolesPermission/Roles/index.vue';

const roleStore = useRoleStore()

const activeTab = computed({
  get: () => {
    return roleStore.data.routeTab
  },
  set: (value) => {
    roleStore.setActiveTab(value)
  },
})

const tabs = [
  {
    title: 'Roles & Users',
    tab: 'Roles',
  },
  {
    title: 'Permission',
    tab: 'Permission',
  },
]
</script>

<template>
  <div>
    <!-- <VTabs
      v-model="activeTab"
      class="v-tabs custom-tabs"
      color="#051c73"
    >
      <VTab
        v-for="item in tabs"
        :key="item.tab"
        :value="item.tab"
        class="font-weight-bold text-none"
      >
        {{ item.title }}
      </VTab>
    </VTabs> -->

    <Roles v-if="hasPermissions('admin_settings.roles_and_permissions_roles_and_users.view')"/>
    
    <!-- <VWindow
      v-model="activeTab"
      class="mt-5 disable-tab-transition"
    >
      <VWindowItem value="Roles">
        <Roles v-if="hasPermissions('admin_settings.roles_and_permissions_roles_and_users.view')"/>
          <div v-else>You no access for this section.</div>
      </VWindowItem>

      <VWindowItem value="Permission" >
        <Permission  v-if="hasPermissions('admin_settings.roles_and_permissions_permissions.view')"/>
          <div v-else>You no access for this section.</div>
      </VWindowItem>
    </VWindow> -->
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
