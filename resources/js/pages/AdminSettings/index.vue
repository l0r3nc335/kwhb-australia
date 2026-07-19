<script setup>
import { useRoleStore } from "@/store/roleStore";
import { useRouter } from 'vue-router';

const roleStore = useRoleStore()
const router = useRouter()

const navigateTo = (page) => {
  if (page == 'Roles' || page == 'Permission') {
    roleStore.setActiveTab(page)
    router.push('adminsettings/rolespermission')
  } else if(page == 'Division') {
    router.push('adminsettings/division')
  } else if(page == 'Location') {
    router.push('adminsettings/location')
  } else if(page == 'PositionTitle') {
    router.push('adminsettings/positiontitle')
  } else if(page == 'Employment') {
    router.push('adminsettings/employment')
  } else if(page == 'PayClassification') {
    router.push('adminsettings/payclassification')
  } else if(page == 'PayRange') {
    router.push('adminsettings/payrange')
  }
  else if (page == 'Allowance') {
    router.push('adminsettings/allowance')
  }  
}
</script>

<template>
  <div>
    <div>
      <span class="font-weight-bold text-h2">Roles & Permissions</span>
      <VRow class="mt-1 mb-3">
        <VCol cols="4" v-if="hasPermissions('admin_settings.roles_and_permissions_roles_and_users.view')">
          <v-card class="rounded" variant="outlined" elevation="1">
            <v-card-text class="text-center d-flex align-center flex-column">
              <VIcon class="mt-4" icon="tabler-users" size="40"/>
              <span class="font-weight-bold mt-5 mb-6 text-h4">Roles & Users</span>
              <v-btn class="mt-5" color="primary" @click="navigateTo('Roles')">Show</v-btn>
            </v-card-text>
          </v-card>
        </VCol>
        <VCol cols="4" v-if="hasPermissions('admin_settings.roles_and_permissions_permissions.view')">
          <v-card class="rounded" variant="outlined" elevation="1">
            <v-card-text class="text-center d-flex align-center flex-column">
              <VIcon class="mt-4" icon="tabler-lock" size="40"/>
              <span class="font-weight-bold mt-5 mb-6 text-h4">Permissions</span>
              <v-btn class="mt-5" color="primary" @click="navigateTo('Permission')">Show</v-btn>
            </v-card-text>
          </v-card>
        </VCol>
      </VRow>
    </div>
    <div>
      <span class="font-weight-bold text-h2">Organisational Structure</span>
      <VRow class="mt-1 mb-3">
        <VCol cols="4" v-if="hasPermissions('admin_settings.operational_structure_divisions.view')">
          <v-card class="rounded" variant="outlined" elevation="1">
            <v-card-text class="text-center d-flex align-center flex-column">
              <VIcon class="mt-4" icon="tabler-tournament" size="40"/>
              <span class="font-weight-bold mt-5 mb-6 text-h4">Divisions</span>
              <v-btn class="mt-5" color="primary" @click="navigateTo('Division')">Show</v-btn>
            </v-card-text>
          </v-card>
        </VCol>
        <VCol cols="4" v-if="hasPermissions('admin_settings.operational_structure_locations.view')">
          <v-card class="rounded" variant="outlined" elevation="1">
            <v-card-text class="text-center d-flex align-center flex-column">
              <VIcon class="mt-4" icon="tabler-map-pin" size="40"/>
              <span class="font-weight-bold mt-5 mb-6 text-h4">Locations</span>
              <v-btn class="mt-5" color="primary" @click="navigateTo('Location')">Show</v-btn>
            </v-card-text>
          </v-card>
        </VCol>
      </VRow>
    </div>
    <div>
      <span class="font-weight-bold text-h2">Payroll Information</span>
      <VRow class="mt-1 mb-3">
        <VCol cols="4" v-if="hasPermissions('admin_settings.payroll_information_position_and_title.view')">
          <v-card class="rounded" variant="outlined" elevation="1">
            <v-card-text class="text-center d-flex align-center flex-column">
              <VIcon class="mt-4" icon="tabler-crown" size="40"/>
              <span class="font-weight-bold mt-5 mb-6 text-h4">Position & Title</span>
              <v-btn class="mt-5" color="primary" @click="navigateTo('PositionTitle')">Show</v-btn>
            </v-card-text>
          </v-card>
        </VCol>
        <VCol cols="4" v-if="hasPermissions('admin_settings.payroll_information_employment_basis.view')">
          <v-card class="rounded" variant="outlined" elevation="1">
            <v-card-text class="text-center d-flex align-center flex-column">
              <VIcon class="mt-4" icon="tabler-briefcase" size="40"/>
              <span class="font-weight-bold mt-5 mb-6 text-h4">Employment Basis</span>
              <v-btn class="mt-5" color="primary" @click="navigateTo('Employment')">Show</v-btn>
            </v-card-text>
          </v-card>
        </VCol>

        <VCol cols="4" v-if="hasPermissions('admin_settings.payroll_information_classifications_and_pay_ranges.view')">
          <v-card class="rounded pt-3" variant="outlined" elevation="1">
            <v-card-text class="text-center d-flex align-center flex-column">
              <img src="/assets/images/icons/dollar-userv2.svg" style="width: 40px !important">
              <span class="font-weight-bold mt-5 mb-6 text-h4">Pay Classifications</span>
              <v-btn class="mt-5" color="primary" @click="navigateTo('PayClassification')">Show</v-btn>
            </v-card-text>
          </v-card>
        </VCol>

        <!-- PAY RANGES CARD -->
        <VCol cols="4" v-if="hasPermissions('admin_settings.payroll_information_classifications_and_pay_ranges.view')">
          <v-card class="rounded" variant="outlined" elevation="1">
            <v-card-text class="text-center d-flex align-center flex-column">
              <img src="/assets/images/icons/pay_ranges.svg" style="width: 50px !important">
              <span class="font-weight-bold mt-5 mb-6 text-h4">Pay Ranges</span>
              <v-btn class="mt-5" color="primary" @click="navigateTo('PayRange')">Show</v-btn>
            </v-card-text>
          </v-card>
        </VCol> 
        <VCol cols="4" v-if="hasPermissions('admin_settings.payroll_information_allowances.view')">
          <v-card class="rounded" variant="outlined" elevation="1">
            <v-card-text class="text-center d-flex align-center flex-column">
              <img src="/assets/images/icons/carv2.svg" style="width: 50px !important" />
              <span class="font-weight-bold mt-5 mb-6 text-h4">Allowances</span>
              <v-btn class="mt-5" color="primary" @click="navigateTo('Allowance')">Show</v-btn>
            </v-card-text>
          </v-card>
        </VCol>               

      </VRow>
    </div>
  </div>
</template>
