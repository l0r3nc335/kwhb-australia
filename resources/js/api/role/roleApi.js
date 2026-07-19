import axios from '@axios'
import {useToast} from "vue-toast-notification";
const $toast = useToast({
    position: 'top-right'
})

export default {
    async fetchAllRoles() {
        let roles = []

        await axios.get('/api/v1/roles/all')
            .then(res => {
                roles = res.data || []
            })
            .catch(err => {
                $toast.error(err.response.data.message || 'Something went wrong while fetching all roles.')
                console.log(error.response)
            })

        return roles
    },

    async fetchRoles() {
        let roles = []

        await axios.get('/api/v1/roles')
            .then(res => {
                roles = res.data || []
            })
            .catch(err => {
                $toast.error(err.response.data.message || 'Something went wrong while fetching roles.')
                console.log(error.response)
            })

        return roles
    }
}
