

import axios from '@axios';

export default {
  async fetchLeaveBalance(param){
    return await axios.post('/api/leave_balance/fetch_leave_balance', param).then((reponse) => reponse.data)
  }
};
