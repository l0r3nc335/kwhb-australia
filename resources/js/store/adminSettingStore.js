import {defineStore} from "pinia";

export const useAdminSettingStore = defineStore('admin_setting', {
    state: () => {
        return {
            rolePermissions: [],
            permissionNavigations: [],
            permissionNavigationIndexes: [],
            permissionNavigationBackLabel: 'Admin Settings',
            activePermissionNavigations: [],
            actions: [],
            scopes: []
        }
    },

    persist: true,

    actions: {
        setPermissionNavigationBackLabel(label) {
            this.permissionNavigationBackLabel = label
        },

        addPermissionNavigationIndex(index) {
            this.permissionNavigationIndexes.push(index)
        },

        removePermissionNavigationIndex(all) {
            if (all === true) {
                this.permissionNavigationIndexes = []
            } else {
                this.permissionNavigationIndexes.pop()
            }
        },

        setRolePermissions(data) {
            this.rolePermissions = data
        },

        setPermissionNavigations(data) {
            this.permissionNavigations = data
        },

        setActivePermissionNavigations(data) {
            this.activePermissionNavigations = data
        },

        setActions(data) {
            this.actions = data
        },

        setScopes(data) {
            this.scopes = data
        }
    }
})
