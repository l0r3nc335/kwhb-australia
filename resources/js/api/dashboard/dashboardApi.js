import axios from '@axios'
import {useToast} from "vue-toast-notification";
const $toast = useToast({
    position: 'top-right'
})

export default {
    async getSummaries(params = null) {
        let data = []

        await axios.get('/api/v1/dashboard', {
            params: params
        })
            .then(res => {
                data = res.data || []
            })
            .catch(err => {
                $toast.error(err.response.data.message || 'Something went wrong while fetching dashboard summaries.')
                console.log(error.response)
            })

        return data
    }
}
