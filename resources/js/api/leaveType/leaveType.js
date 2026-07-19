import axios from '@axios'

export default {
  async fetchLeaveTypes(){
    return await axios.get('/api/leave_type').then((reponse) => reponse.data)
  },
}
