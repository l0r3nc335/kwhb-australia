import axios from '@axios'

export default {
  async useCSRF(){
    return axios.get('/sanctum/csrf-cookie')
  },
  async useLogin(param){
    return axios.post('/api/auth/login', param).then(reponse => reponse.data)
  },
  async useLogOut(param){
    return axios.post('/api/auth/logout', param).then(reponse => reponse.data)
  },
  async useUserData() {
    return axios.get('/api/auth/user').then(reponse => reponse.data)
  },
  async useForgotPassword(param) {
    return axios.post('/api/auth/forgot-password', param).then(reponse => reponse.data)
  },
  async useResetPassword(param) {
    return axios.post('/api/auth/reset-password', param).then(reponse => reponse.data)
  },
}
