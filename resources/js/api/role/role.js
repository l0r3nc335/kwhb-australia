import axios from '@axios'

export const fetchRoles = () => {
  return axios.get('/api/role').then((reponse) => reponse.data)
}
