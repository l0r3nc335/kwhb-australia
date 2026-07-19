<template>
    <div  @click="isSuperUser ? showPermissionFormDialog=true : null" class="tw-py-4 tw-text-[1rem] tw-shadow tw-px-4 tw-text-center tw-cursor-pointer hover:tw-bg-gray-50" :class="[getPermissionBackground()]"  style="font-family: Arial, Helvetica, sans-serif">
        <div style="overflow:hidden; max-width: 100px;margin:auto" class="tw-py-2">
            {{getPermissionText().length > 0 ? getPermissionText() : 'No'}}
        </div>
        <VDialog v-model="showPermissionFormDialog" width="500">
            <div class="tw-flex tw-flex-col tw-bg-white tw-rounded">
                <div class="tw-px-4 tw-py-4 tw-text-[1rem] tw-flex">
                    <div class="tw-mr-2"><b>{{rolePermissions.name}}</b></div> <div class="tw-text-orange-600 tw-text-[.8rem] tw-py-1">{{setting.name}}</div>
                </div>
                <div class="tw-flex tw-flex-col tw-w-full tw-p-4">
                    <div v-for="formPermission in form.permissions" class="tw-grid tw-grid-cols-6 tw-gap-2 tw-mb-2">
                        <div class="tw-col-span-4">
                            <AppSelect :items="filteredActions" v-model="formPermission.action" item-title="label" item-value="id" disabled></AppSelect>
                        </div>
                        <div class="tw-col-span-2">
                            <AppSelect :items="scopes" v-model="formPermission.scope" item-title="label" item-value="id"></AppSelect>
                        </div>
                    </div>
                </div>
                <div class="tw-flex tw-px-4 tw-mb-4 tw-justify-end">
                    <VBtn size="small" color="secondary" class="tw-mr-2" @click="showPermissionFormDialog=false">Close</VBtn>
                    <VBtn size="small" color="success" @click="updateRolePermissions">Save</VBtn>
                </div>
            </div>
        </VDialog>
    </div>
</template>
<script>
    import _ from "lodash";
    import adminSettingApi from "@/api/adminSetting/adminSettingApi";

    export default {
        emits: ['reload-roles-and-permissions'],
        props: {
            setting: {
                required: true
            },

            rolePermissions: {
                required: true
            },
        },

        data() {
            return {
                loading: false,
                showPermissionFormDialog: false,
                form: {
                    roleId: null,
                    featureId: null,
                    subFeatureId: null,
                    permissions: []
                },
                actions: [
                    {
                        id: 1,
                        label: 'View'
                    },
                    {
                        id: 2,
                        label: 'Create',
                    },
                    {
                        id: 3,
                        label: 'Update'
                    },
                    {
                        id: 4,
                        label: 'Archive'
                    },
                    {
                        id: 5,
                        label: 'Delete'
                    },
                    {
                        id: 6,
                        label: 'Submit'
                    },
                    {
                        id: 7,
                        label: 'Approve'
                    }
                ],
                scopes: [
                    {
                        id: 0,
                        label: 'No'
                    },
                    {
                        id: 1,
                        label: 'Self'
                    },
                    {
                        id: 2,
                        label: 'Teammate'
                    },
                    {
                        id: 3,
                        label: 'Division'
                    },
                    {
                        id: 4,
                        label: 'All'
                    }
                ]
            }
        },

        computed: {
            filteredActions() {
                let actions = this.actions

                if (this.setting?.actions?.length > 0) {
                    actions = this.actions.filter(a => this.setting.actions.includes(a.id))
                }

                return actions
            }
        },

        mounted() {
            this.setFormData()
        },

        methods: {
            async updateRolePermissions() {
                await adminSettingApi.updateRolePermissions(this.form.roleId, this.form.featureId, this.form.subFeatureId, this.form.permissions)
                this.$emit('reload-roles-and-permissions')
            },

            setFormData() {
                this.form.roleId = this.rolePermissions.id
                this.form.featureId = this.setting?.feature_id
                this.form.subFeatureId = this.setting?.sub_feature_id
                this.form.permissions = this.getFormPermissions()
            },

            getFormPermissions() {
                let formPermissions = []

                for (let i in this.filteredActions) {
                    let activePermission = this.getActivePermissions().find(ap => ap.action == this.filteredActions[i].id)

                    formPermissions.push({
                        action: this.filteredActions[i].id,
                        feature_id: this.setting?.feature_id,
                        sub_feature_id: this.setting?.sub_feature_id,
                        scope: activePermission?.pivot?.scope || 0
                    })
                }

                return formPermissions
            },

            getActivePermissions() {
                let permissions = Array.isArray(this.rolePermissions?.permissions) ? this.rolePermissions?.permissions : []
                return permissions.filter(rp => rp?.feature === this.setting?.feature_id && rp?.sub_feature === this.setting?.sub_feature_id)
            },

            getPermissionText() {
                let actions = _.uniq(this.getActivePermissions().map(p => p.action))
                let actionTexts = []

                // Action - View
                if (actions.includes(1)) {
                    actionTexts.push('View')
                }
                // Action - Create
                if (actions.includes(2)) {
                    actionTexts.push('Create')
                }
                // Action - Update
                if (actions.includes(3)) {
                    actionTexts.push('Update')
                }
                // Action - Archive
                if (actions.includes(4)) {
                    actionTexts.push('Archive')
                }
                // Action - Delete
                if (actions.includes(5)) {
                    actionTexts.push('Delete')
                }
                // Action - Submit
                if (actions.includes(6)) {
                    actionTexts.push('Submit')
                }
                // Action - Approve
                if (actions.includes(7)) {
                    actionTexts.push('Approve')
                }

                return actionTexts.join(',')
            },

            getPermissionBackground() {
                let scopes = _.uniq(this.getActivePermissions().map(p => p.pivot.scope))

                // All roles has access to dashboard view
                if (this.setting?.feature_id === 1) {
                    return 'tw-bg-green-100'
                }

                if (Math.max(...scopes) === 1) {
                    return 'tw-bg-yellow-100'
                }

                if (Math.max(...scopes) === 2) {
                    return 'tw-bg-purple-100'
                }

                if (Math.max(...scopes) === 3) {
                    return 'tw-bg-purple-100'
                }

                if (Math.max(...scopes) === 4) {
                    return 'tw-bg-green-100'
                }

                return 'tw-bg-red-100'
            }
        }
    }
</script>
