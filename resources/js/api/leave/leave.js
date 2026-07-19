



import axios from '@axios';

export default {
  async saveLeave(param){
    return await axios.post('/api/leave', param).then((response) => response.data)
  },
  async fetchLeave(emp_id){
    return await axios.get('/api/leave/' + emp_id).then((response) => response.data)
  },
  async fetchLeaveByApprover(emp_id){
    return await axios.get('/api/leave/fetch_leave_by_approver/' + emp_id).then((response) => response.data)
  },
  async multipleFilter(payload){
    return await axios.post('/api/leave/multiple_filter', payload).then((response) => response.data)
  },
  async updateRow(param){
    return await axios.post('/api/leave/update_row', param).then((response) => response.data)
  },

};
