<template>
    <div class="tw-flex tw-flex-col">
        <template v-if="activePermissionNavigations?.items?.length > 0" >
            <table class="tw-border-b">
                <thead style="font-family: Oswald !important;">
                    <tr>
                        <th v-for="header in navigationHeaders"
                            class="tw-text-[1.1rem] tw-py-3 tw-text-gray-600"
                            :class="[header == 'Actions' ? 'tw-text-center' : 'tw-text-left']"
                            style="font-weight: normal;text-transform: uppercase;border-top: solid 1px #eee"
                            :style="{width: (['Created Date','Last Updated', 'Actions'].includes(header)) ? '140px' : 'initial'}">{{header}}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(activeNav, navIndex) in activeNavigationItems" class="tw-border-t">
                        <template v-for="(v, i) in activeNav">
                            <td v-if="allowedKeys.includes(i)" class="tw-py-4 tw-text-[1rem]" style="font-family: Arial, Helvetica, sans-serif">
                                <template v-if="i === 'name'">
                                    <b>{{v}}</b>
                                </template>
                                <template v-else>
                                    {{v}}
                                </template>
                            </td>
                        </template>
                        <td class="tw-text-center tw-cursor-pointer" @click="changeActivePermissionNavigations(activeNav.settings, navIndex)">
                            <u>View</u>
                        </td>
                    </tr>
                </tbody>
            </table>
        </template>
        <template v-else>
            <PermissionSettingTable :setting="activeSetting" @reload="$emit('reload')"></PermissionSettingTable>
        </template>
    </div>

</template>
<script>
    import {mapState, mapActions} from "pinia";
    import {useAdminSettingStore} from "@/store/adminSettingStore";
    import PermissionSettingTable from "@/pages/AdminSettings/RolesPermission/Permission/PermissionSettingTable.vue";

    export default {
        emits: ['reload'],
        components: {
            PermissionSettingTable
        },

        data() {
            return {
                loading: false,
                allowedKeys: [
                    'name', 'created_at', 'updated_at', 'description'
                ]
            }
        },

        computed: {
            activeSetting() {
                if (this.activeNavigationItems.length === 0 && this.permissionNavigationIndexes.length > 0) {
                    let setting = this.permissionNavigations
                    let all = []

                    for(let i in this.permissionNavigationIndexes) {
                        this.setPermissionNavigationBackLabel(setting.title)
                        all.push(setting.items[this.permissionNavigationIndexes[i]])
                        setting = setting.items[this.permissionNavigationIndexes[i]].settings
                    }

                    return all.length > 0 ? all[all.length - 1] : null
                }

                return null
            },

            activeNavigationItems() {
                return this.activePermissionNavigations?.items || []
            },

            navigationHeaders() {
                let headers = []

                if (this.activePermissionNavigations?.items.length > 0) {
                    let keyItem = this.activePermissionNavigations?.items[0]
                    for (let i in keyItem) {
                        switch (i) {
                            case 'name':
                                headers.push(this.activePermissionNavigations.title)
                                break
                            case 'description':
                                headers.push('Description')
                                break
                            case 'created_at':
                                headers.push('Created Date')
                                break
                            case 'updated_at':
                                headers.push('Last Updated')
                                break
                        }
                    }
                }

                headers.push('Actions')

                return headers
            },

            ...mapState(useAdminSettingStore, ['permissionNavigations', 'activePermissionNavigations', 'permissionNavigationIndexes'])
        },

        methods: {
            changeActivePermissionNavigations(settings, navIndex) {
                this.setPermissionNavigationBackLabel(this.activePermissionNavigations.title)
                this.addPermissionNavigationIndex(navIndex)
                this.setActivePermissionNavigations(settings)
            },

            ...mapActions(useAdminSettingStore, [
                'setActivePermissionNavigations',
                'addPermissionNavigationIndex',
                'removePermissionNavigationIndex',
                'setPermissionNavigationBackLabel'
            ])
        },

        watch: {
            activeNavigations() {

            }
        }
    }
</script>
