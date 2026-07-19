import axios from '@axios'
import {useToast} from "vue-toast-notification";
const $toast = useToast({
    position: 'top-right'
})

export default {
    async fetchPositions() {
        let positions = []

        await axios.get('/api/v1/positions')
            .then(res => {
                positions = res.data || []
            })
            .catch(err => {
                $toast.error(err.response.data.message || 'Something went wrong while fetching positions.')
                console.log(error.response)
            })

        return positions
    },

    async addPosition(name) {
        await axios.post('/api/positions/create', {
            name: name
        })
        .catch(err => {
            console.log(error.response) // ❌ should be 'err.response'
        })
    }
}
