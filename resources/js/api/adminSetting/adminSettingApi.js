import axios from '@axios'
import {useToast} from "vue-toast-notification";
const $toast = useToast({
    position: 'top-right'
})

export default {
    async getJobTitles() {
        let data = []

        await axios.get('/api/v1/settings/job-titles')
            .then(res => {
                data = res.data
            }).catch(err => {
                $toast.error(err?.response?.data?.message || 'Something went wrong while fetching job titles.')
                console.log(error?.response)
            })

        return data;
    },

    async getEmployments() {
        let data = []

        await axios.get('/api/v1/settings/employments')
            .then(res => {
                data = res.data
            }).catch(err => {
                $toast.error(err?.response?.data?.message || 'Something went wrong while fetching employments.')
                console.log(error?.response)
            })

        return data;
    },

    async getLocations() {
        let data = []

        await axios.get('/api/v1/settings/locations')
            .then(res => {
                data = res.data
            }).catch(err => {
                $toast.error(err?.response?.data?.message || 'Something went wrong while fetching locations.')
                console.log(error?.response)
            })

        return data;
    },

    async getDivisions() {
        let data = []

        await axios.get('/api/v1/settings/divisions')
            .then(res => {
                data = res.data
            }).catch(err => {
                $toast.error(err?.response?.data?.message || 'Something went wrong while fetching divisions.')
                console.log(error?.response)
            })

        return data;
    },

    async getRolesWithUsers() {
        let data = []

        await axios.get('/api/v1/settings/roles-with-users')
            .then(res => {
                data = res.data
            }).catch(err => {
                $toast.error(err?.response?.data?.message || 'Something went wrong while fetching roles with users.')
                console.log(error?.response)
            })

        return data;
    },

    async updateRolePermissions(roleId, featureId, subFeatureId, permissions) {
        await axios.put('/api/v1/settings/roles-and-permissions', {
            role_id: roleId,
            feature_id: featureId,
            sub_feature_id: subFeatureId,
            permissions: JSON.stringify(permissions)
        }).then(res => {
            $toast.success("Role permissions updated.")
        })
        .catch(err => {
            $toast.error(err?.response?.data?.message || 'Something went wrong while updating roles and permission settings.')
            console.log(error?.response)
        })
    },

    async fetchRolePermissionSettings() {
        let data = []

        await axios.get(`/api/v1/settings/roles-and-permissions`)
            .then(res => {
                data = res?.data || []
            })
            .catch(err => {
                $toast.error(err?.response?.data?.message || 'Something went wrong while fetching roles and permission settings.')
                console.log(error?.response)
            })

        return data
    },

    async fetchPermissionNavigations() {
        let data = []
        await axios.get(`/api/v1/settings/permission-navigations`)
            .then(res => {
                data = res?.data || []
            })
            .catch(err => {
                $toast.error(err?.response?.data?.message || 'Something went wrong while fetching roles and permission settings.')
                console.log(error?.response)
            })

        return data
    },
}
