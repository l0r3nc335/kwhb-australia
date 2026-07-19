<template>
    <div>
        <table class="tw-w-full">
            <thead style="font-family: Oswald !important;">
                <tr>
                    <th class="tw-text-[1.1rem] tw-py-3 tw-text-gray-500 tw-text-left tw-px-4"
                           style="font-weight: normal;text-transform: uppercase;border-top: solid 1px #eee">
                        Feature
                    </th>
                    <template v-for="rp in rolePermissions">
                        <th v-if="rp.name != 'Super User'"
                            class="tw-text-[1.1rem] tw-py-3 tw-text-gray-500 tw-text-center tw-px-4"
                            style="font-weight: normal;text-transform: uppercase;border-top: solid 1px #eee"
                            :style="{width: '110px'}">
                            {{rp.name}}
                        </th>
                    </template>

                </tr>
            </thead>
            <tbody>
                <tr  class="tw-border-t">
                    <td class="tw-py-4 tw-text-[1rem] tw-px-4"  style="font-family: Arial, Helvetica, sans-serif"><b>{{setting?.name}}</b></td>
                    <template v-for="rp in rolePermissions" :key="rp.name">
                        <td v-if="rp.name != 'Super User'" :key="rp.name">
                            <PermissionSelector :role-permissions="rp" :setting="setting" @reload-roles-and-permissions="$emit('reload')"></PermissionSelector>
                        </td>
                    </template>
                </tr>
            </tbody>
        </table>
    </div>
</template>
<script>
    import {useAdminSettingStore} from "@/store/adminSettingStore";
    import {mapState} from "pinia";
    import PermissionSelector from "@/pages/AdminSettings/RolesPermission/Permission/PermissionSelector.vue";

    export default {
        emits: ['reload'],
        components: {
            PermissionSelector
        },

        props: {
            setting: {
                required: true
            }
        },

        data() {
            return {
                loading: false,
                settings: []
            }
        },

        computed: {
            ...mapState(useAdminSettingStore, ['scopes', 'actions', 'rolePermissions'])
        },
    }
</script>
