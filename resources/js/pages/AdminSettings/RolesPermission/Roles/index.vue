<template>
  <p class="font-weight-bold text-h2 mb-3">Roles</p>
  <VRow>
    <VCol
      v-for="role in roles"
      :key="role.id"
      cols="12"
      sm="6"
      lg="4"
    >
        <RoleCard :role="role" :user_total_count="role.user_count" :users="role.sample_users" :selected-role="selectedRole" @select-role="roleId => this.selectedRole = roleId"></RoleCard>
    </VCol>
  </VRow>
  <VRow>
    <VCol cols="12">
        <div class="tw-flex tw-py-2 tw-justify-between tw-mb-4">
            <div>
                <AppSelect
                    v-model="perPage"
                    placeholder="Per Page"
                    :items="[
                                10,
                                25,
                                50,
                                100
                            ]"
                />
            </div>
            <div class="tw-flex">
                <div style="width: 200px" class="tw-mr-4">
                    <AppSelect
                        v-model="selectedStatus"
                        placeholder="Select Status"
                        :items="statuses"
                        clearable
                    />
                </div>
                <div style="width: 200px" class="tw-mr-4">
                    <AppSelect
                        v-model="selectedRole"
                        placeholder="Select Role"
                        item-title="name"
                        item-value="id"
                        :items="roles"
                        clearable
                    />
                </div>
                <div style="width: 260px">
                    <VTextField v-model="search" placeholder="Search employee" prepend-inner-icon="tabler-search" clearable=""></VTextField>
                </div>
            </div>
        </div>
        <EmployeeTable :selected-role="selectedRole" :selected-status="selectedStatus" :search="search" :per-page="perPage"></EmployeeTable>
    </VCol>
  </VRow>
</template>

<script>
    import AdminSettingApi from "@/api/adminSetting/adminSettingApi";
import EmployeeApi from "@/api/employee/employeeApi";
import RoleCard from "@/pages/AdminSettings/RolesPermission/Roles/RoleCard.vue";
import EmployeeTable from "@/pages/Employee/EmployeeTable.vue";

    export default {
        components: {
            RoleCard,
            EmployeeTable
        },

        data() {
            return {
                roles: [],
                statuses: [],
                selectedStatus: null,
                selectedRole: null,
                search: null,
                perPage: 10
            }
        },

        mounted() {
            this.loadRolesWithUsers()
            this.loadStatuses()
        },

        methods: {
            async loadStatuses() {
                this.statuses = await EmployeeApi.fetchStatuses()
            },

            async loadRolesWithUsers() {
                this.roles = await AdminSettingApi.getRolesWithUsers()
            }
        }
    }
</script>
