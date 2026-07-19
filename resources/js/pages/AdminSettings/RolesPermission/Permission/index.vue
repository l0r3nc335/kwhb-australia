<template>
    <div class="tw-flex tw-flex-col tw-p-4">
        <div class="tw-flex tw-justify-between">
            <div class="tw-flex tw-cursor-pointer tw-py-1" @click="navigateBack">
                <VIcon icon="tabler-chevron-left" class="tw-mr-2"></VIcon> Back to {{permissionNavigationBackLabel}}
            </div>
            <div class="tw-flex">
                <AppTextField prepend-inner-icon="tabler-search" class="tw-mr-3" placeholder="Search" style="width: 250px"></AppTextField>
                <AppSelect
                    placeholder="Vaccination Type"
                    v-model="pagination.perPage"
                    :items="perPageOptions"
                    size="sm"
                />
            </div>
        </div>
        <div class="tw-py-3 tw-mt-5">
            <PermissionNavigationTable @reload="loadSettings"></PermissionNavigationTable>
        </div>
    </div>
</template>
<script>
    import adminSettingApi from "@/api/adminSetting/adminSettingApi";
import PermissionNavigationTable from "@/pages/AdminSettings/RolesPermission/Permission/PermissionNavigationTable.vue";
import { useAdminSettingStore } from "@/store/adminSettingStore";
import { mapActions, mapState } from "pinia";

    export default {
        components: {
            PermissionNavigationTable
        },

        data() {
            return {
                loading: false,
                pagination: {
                    perPage: 10
                },
                perPageOptions: [
                    10, 15, 20, 50, 100
                ]
            }
        },

        computed: {
            ...mapState(useAdminSettingStore, ['actions', 'scopes', 'rolePermissions', 'permissionNavigations', 'permissionNavigationBackLabel', 'permissionNavigationIndexes'])
        },

        created() {
            this.removePermissionNavigationIndex(true)
        },

        mounted() {
            this.loadSettings()
            this.loadPermissionNavigations()
        },

        methods: {
            navigateBack() {
                let navigatedSetting = this.permissionNavigations
                this.setPermissionNavigationBackLabel('Admin Settings')

                if (this.permissionNavigationIndexes.length === 0) {
                    this.$router.go(-1)
                } else {
                    this.removePermissionNavigationIndex()
                    for(let i in this.permissionNavigationIndexes) {
                        this.setPermissionNavigationBackLabel(navigatedSetting.title)
                        navigatedSetting = navigatedSetting.items[this.permissionNavigationIndexes[i]].settings
                    }
                    this.setActivePermissionNavigations(navigatedSetting)
                }
            },

            async loadSettings() {
                let data = await adminSettingApi.fetchRolePermissionSettings()
                this.setRolePermissions(data?.role_permissions || [])
                this.setActions(data?.actions || [])
                this.setScopes(data?.scopes || [])
            },

            async loadPermissionNavigations() {
                let data = await adminSettingApi.fetchPermissionNavigations()
                this.setPermissionNavigations(data || [])
                this.setActivePermissionNavigations(data || [])
            },

            ...mapActions(useAdminSettingStore, [
                'setActions',
                'setScopes',
                'setRolePermissions',
                'setPermissionNavigations',
                'setActivePermissionNavigations',
                'removePermissionNavigationIndex',
                'setPermissionNavigationBackLabel'
            ])
        }
    }
</script>
