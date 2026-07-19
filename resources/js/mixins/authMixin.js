import {mapState} from "pinia";
import {useAuthStore} from "@/store/authStore";

export default {
    data() {
        return {
        }
    },

    computed: {
        authUser() {
            return this.user
        },

        isSuperUser() {
            return this.user?.role?.name == 'Super User'
        },

        userPermissionsUiKeys() {
            let permissions = this.user?.role?.permissions || []
            let uiKeys = []

            for (let i in permissions) {
                uiKeys.push(permissions[i].ui_key)
            }

            return uiKeys
        },

        roleNames() {
            let role = this.user?.role

            return role ? [role?.name] : []
        },

        ...mapState(useAuthStore, ['user']),
    },

    mounted() {
        // console.log('authUser', this.user)
    },

    methods: {
        hasPermissions(val, employee = null, some = false) {
            let featurePermissions = Array.isArray(val) ? val : [val]
            let allowed = false;
            let error = [];
            let success = [];

            if (!val) {
                return false
            }

            if (this.isSuperUser) {
                return true
            }

            for (let i in featurePermissions) {
                if (employee) {
                    let permissions = this.user?.role?.permissions || []
                    let targetPermission = permissions.find(p => p.ui_key == featurePermissions[i])

                    if (targetPermission) {
                        if (targetPermission?.pivot?.scope == 1 && this.user.id == employee?.user_id) {
                            allowed = true
                            success.push(featurePermissions[i])
                        }
                        if (targetPermission?.pivot?.scope == 2 && (this.employee?.manager_id == this.user.id || this.employee?.line_manager_id == this.user.id)) {
                            allowed = true
                            success.push(featurePermissions[i])
                        }
                        if (targetPermission?.pivot?.scope == 3 && (this.employee?.division_id == this.user?.employee?.division_id)) {
                            allowed = true
                            success.push(featurePermissions[i])
                        }
                        if (targetPermission?.pivot?.scope == 4) {
                            allowed = true
                            success.push(featurePermissions[i])
                        }
                    } else {
                        error.push(featurePermissions[i])
                    }
                } else if (this.userPermissionsUiKeys.includes(featurePermissions[i])) {
                    allowed = true
                }
            }


            if (some && error.length > 0 && success.length > 0) {
                allowed = true
            }

            return allowed
        },

        inRoles(roles, reverse = false) {
            let allowSuperUser = (this.user?.role?.name == 'Super User')
            if (reverse) {
                allowSuperUser = !allowSuperUser
            }
            return roles.includes(this.user?.role?.name) || allowSuperUser
        },

        hasRoles(roles) {
            if (Array.isArray(roles)) {
                let hasRole = false;
                for (let i in roles) {
                    if (this.roleNames.includes(roles[i])) {
                        hasRole = true
                        break
                    }
                }
                return hasRole
            } else if (typeof roles === 'string') {
                return this.roleNames.includes(roles)
            }

            return false
        },

        isManager() {
            return this.hasRoles([
                'line manager',
                'general manager',
                'hr',
                'finance',
                'ceo',
                'administrator'
            ])
        }
    },
}
