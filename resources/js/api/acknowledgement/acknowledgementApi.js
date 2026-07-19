import axios from '@axios'
import { useToast } from "vue-toast-notification"

const $toast = useToast({
    position: 'top-right'
})

export default {
    async list(resourceId = null) {
        let acks = []
        await axios.get('/api/v1/acknowledgements', {
            params: resourceId ? { resource_id: resourceId } : {}
        })
        .then(res => {
            acks = res.data || []
        })
        .catch(err => {
            $toast.error(err.response?.data?.message || 'Something went wrong while fetching acknowledgements.')
            console.error(err.response)
        })
        return acks
    },

    async get(id) {
        let ack = null
        await axios.get(`/api/v1/acknowledgements/${id}`)
            .then(res => {
                ack = res.data
            })
            .catch(err => {
                $toast.error(err.response?.data?.message || 'Failed to fetch acknowledgement.')
                console.error(err.response)
            })
        return ack
    },

    async save({ resource_id, is_acknowledged = false }) {
        let ack = null
        await axios.post('/api/v1/acknowledgements', {
            resource_id,
            is_acknowledged
        })
        .then(res => {
            ack = res.data
        })
        .catch(err => {
            $toast.error(err.response?.data?.message || 'Failed to save acknowledgement.')
            console.error(err.response)
        })
        return ack
    },

    async update(id, { is_acknowledged }) {
        let ack = null
        await axios.post(`/api/v1/acknowledgements/update/${id}`, {
            is_acknowledged
        })
        .then(res => {
            ack = res.data
        })
        .catch(err => {
            $toast.error(err.response?.data?.message || 'Failed to update acknowledgement.')
            console.error(err.response)
        })
        return ack
    },

    async delete(id) {
        await axios.delete(`/api/v1/acknowledgements/${id}`)
            .catch(err => {
                $toast.error(err.response?.data?.message || 'Failed to delete acknowledgement.')
                console.error(err.response)
            })
    }
}
