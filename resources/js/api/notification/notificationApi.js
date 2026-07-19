import axios from '@axios';
import {useToast} from "vue-toast-notification";
const $toast = useToast({
    position: 'top-right'
})

export default {
    async toggleUnread(id) {
        await axios.post('/api/v1/notifications/' + id + '/toggle-read')
            .then(res => {

            })
            .catch(err => {
                $toast.error(err.response.data.message || 'Something went wrong while marking read/unread to notification')
                console.log(error.response)
            })
    },

    async fetchNotifications(page = 1, perPage = 5) {
        let data = []
        await axios.get('/api/v1/notifications', {
            params: {
                page: page,
                per_page: perPage
            }
        })
            .then(res => {
                data = res.data || []
            })
            .catch(err => {
                $toast.error(err.response.data.message || 'Something went wrong while fetching notifications')
                console.log(error.response)
            })

        return data;
    }
}
