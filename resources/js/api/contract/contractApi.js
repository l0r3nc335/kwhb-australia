import axios from '@axios';
import { useToast } from "vue-toast-notification";
const $toast = useToast({
    position: 'top-right'
})

export default {

    async fetchAllContracts(params = null) {
        let contracts = []

        await axios.get('/api/v1/contracts', {
          params: params
        })
            .then(res => {
                contracts = res.data || []
            })
            .catch(err => {
                $toast.error(err.response.data.message || 'Something went wrong while fetching contracts.')
                console.log(error.response)
            })

        return contracts
    },

  async fetchContract(params = null) {
    let contracts = []

    await axios.get(`/api/v1/contracts/contract`, {
      params: params
    })
      .then(res => {
        contracts = res.data || []
      })
      .catch(err => {
        $toast.error(err.response.data.message || 'Something went wrong while fetching contracts.')
        console.log(error.response)
      })

    return contracts
  },

  async fetchContractHistory(params = null) {
    let contracts = []

    await axios.post(`/api/v1/contract-version-history/contract-history`, params)
      .then(res => {
        contracts = res.data || []
      })
      .catch(err => {
        $toast.error(err.response?.data?.message || 'Something went wrong while fetching contracts.')
        console.log(err.response)
      })

    return contracts
  },

  async fetchContractOfOffer(params = null) {
    let contracts = []

    await axios.get(`/api/v1/contracts/contract_offer`, {
      params: params
    })
      .then(res => {
        contracts = res.data || []
      })
      .catch(err => {
        $toast.error(err.response.data.message || 'Something went wrong while fetching contracts.')
        console.log(error.response)
      })

    return contracts
  },

    async createContract(params = null) {
      try {
        const res = await axios.post('/api/v1/contracts/create-contract', params);
        return res.data || [];
      } catch (err) {
        $toast.error(err.response?.data?.message || 'Something went wrong while creating contract.');
        console.log(err.response);
        return [];
      }
    },

    async fetchRolesWithContract(params = null) {
      let contracts = []

      await axios.get('/api/v1/contracts/roles', {
        params: params
      })
        .then(res => {
          contracts = res.data || []
        })
        .catch(err => {
          $toast.error(err.response.data.message || 'Something went wrong while fetching contracts.')
          console.log(error.response)
        })

      return contracts
    },

    async fetchAllContractsExpiry(params = null) {
      let contracts = []

      await axios.get('/api/v1/contracts/expiries', {
        params: params
      })
        .then(res => {
          contracts = res.data || []
        })
        .catch(err => {
          $toast.error(err.response.data.message || 'Something went wrong while fetching contracts.')
          console.log(error.response)
        })

      return contracts
    },

    async fetchContractStatuses() {
      let types = []
      await axios.get('/api/v1/contracts/statuses')
        .then(res => {
          types = res.data || []
        })
        .catch(err => {
          $toast.error(err.response.data.message || 'Something went wrong while fetching contract statuses.')
          console.log(error.response)
        })

      return types;
    },

    async fetchTemplateVersion() {
        let template = []

        await axios.get('/api/v1/template-version')
            .then(res => {
                contracts = res.data || []
            })
            .catch(err => {
                $toast.error(err.response.data.message || 'Something went wrong while fetching contracts.')
                console.log(error.response)
            })

        return contracts
    },

    async fetchAllEmployees() {
        let employees = []

        await axios.get('/api/v1/employees')
            .then(res => {
                employees = res.data || []
            })
            .catch(err => {
                $toast.error(err.response.data.message || 'Something went wrong while fetching approved timesheets.')
                console.log(error.response)
            })

        return employees
    },

    async fetchLocations() {
        let locations = []

        await axios.get('/api/location')
            .then((response) => response.data) 
            .catch(err => {
                $toast.error(err.response.data.message || 'Something went wrong while fetching locations.')
                console.log(err.response)  
            })

        return locations
    },

    async storeContract(param){
        return await axios.post('/api/v1/contracts/store', param).then(response => response.data)
    },

    async editContract(contractId) {
        try {
            const response = await axios.get(`/api/v1/contracts/edit/${contractId}`);
            return response.data;
        } catch (error) {
            console.error('Error updating template status:', error);
            throw error; 
        }
    },

    async updateContract(contractData) {

        try {
            const response = await axios.post(`/api/v1/contracts/update`, contractData).then(response => response.data);
            return response;
        } catch (error) {
            console.error('Error updating contract status:', error);
            throw error; 
        }
    },

    async updateContractStatus(id, status, user_id, user_name, type_label) {
      try {
        const response = await axios.post(`/api/v1/contracts/update-status`, {id, status, user_id, user_name, type_label}).then(response => response.data);
        return response;
      } catch (error) {
        console.error('Error updating contract status:', error);
        throw error;
      }
    },

    async duplicateContract(id) {
      try {
        const response = await axios.post(`/api/v1/contracts/duplicate`, {id}).then(response => response.data);
        return response;
      } catch (error) {
        console.error('Error updating contract status:', error);
        throw error;
      }
    },

    async rejectContract(contractData) {

      try {
        const response = await axios.post(`/api/v1/contracts/reject`, contractData).then(response => response.data);
        return response;
      } catch (error) {
        console.error('Error updating contract status:', error);
        throw error;
      }
    },
    
    async saveSignature(param){
        return await axios.post('/api/v1/contracts/save-signature', param).then(response => response.data)
    },

    // async fetchEmployments ()  {
    //     return await axios.get('/api/employment').then((reponse) => reponse.data)
    //   }

    async fetchEmployments() {
        let employments = []

        await axios.get('/api/employment')
            .then(res => {
                employments = res.data || []
            })
            .catch(err => {
                $toast.error(err.response.data.message || 'Something went wrong while fetching approved timesheets.')
                console.log(error.response)
            })

        return employments
    },

}
