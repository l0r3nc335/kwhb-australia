import axios from '@axios';
import { useToast } from "vue-toast-notification";
const $toast = useToast({
    position: 'top-right'
})

export default {
    async storeTemplate(data) {
      await axios.post('/api/v1/templates/store', data, {
        headers: {
          'Content-Type': 'multipart/form-data',
        }
      })
        .then(res => {
          $toast.success("You have successfully submitted the Template.")
        })
        .catch(err => {
          $toast.error(err.response.data.message || 'Something went wrong while submitting Template request.')
          console.log(err.response)
        })
    },

  async duplicateTemplate(id) {
    await axios.post('/api/v1/templates/duplicate', id, {
      headers: {
        'Content-Type': 'multipart/form-data',
      }
    })
      .then(res => {
        $toast.success("You have successfully submitted the Template.")
      })
      .catch(err => {
        $toast.error(err.response.data.message || 'Something went wrong while submitting Template request.')
        console.log(err.response)
      })
  },

    async fetchTemplateVersion(templateId) {
        let templates = [];
    
        try {
            const response = await axios.get(`/api/v1/template-version-history/${templateId}`);
            templates = response.data || [];
        } catch (err) {
            toast.error(err.response?.data?.message || 'Something went wrong while fetching templates.');
            console.error(err.response);  
        }
    
        return templates;
    
    },

    async fetchAllTemplatesByType(type) {
        let templates = [];
    
        try {
            const response = await axios.get(`/api/v1/templates/${type}`);
            templates = response.data || [];
        } catch (err) {
            toast.error(err.response?.data?.message || 'Something went wrong while fetching templates.');
            console.error(err.response);  
        }
    
        return templates;
    
    },


  async fetchAllTemplatesByTypeAndStatus(type,status) {
    let templates = [];

    try {
      const response = await axios.get(`/api/v1/templates/${type}/${status}`);
      templates = response.data || [];
    } catch (err) {
      toast.error(err.response?.data?.message || 'Something went wrong while fetching templates.');
      console.error(err.response);
    }

    return templates;

  },

  async fetchAllTemplatesByOwnerEmployeeId(type,ownerEmployeeId) {
    let templates = [];

    try {
      const response = await axios.get(`/api/v1/templates/by-type-owner/${type}/${ownerEmployeeId}`);
      templates = response.data || [];
    } catch (err) {
      toast.error(err.response?.data?.message || 'Something went wrong while fetching templates.');
      console.error(err.response);
    }

    return templates;

  },
    
    /*async storeTemplate(param){
        return await axios.post('/api/v1/templates/store', param).then(response => response.data)
    },*/

    async updateTemplateStatus(templateId, status) {
        return await axios.put(`/api/v1/templates/update-status/${templateId}`, { status })
            .then(response => response.data)
            .catch(error => {
                console.error('Error updating template status:', error);
                throw error;
            });
    },

    /*async updateTemplate(templateId, templateData) {
        try {
            const response = await axios.put(`/api/v1/templates/update/${templateId}`, templateData);
            return response.data;
        } catch (error) {
            console.error('Error updating template status:', error);
            throw error; 
        }
    },*/

    async updateTemplatePatch(templateId, templateData) {
      try {
        const response = await axios.post(`/api/v1/templates/update/${templateId}`, templateData, {
          headers: {
            'Content-Type': 'multipart/form-data', // optional but okay
          }
        });
        return response.data;
      } catch (error) {
        console.error('Error updating template status:', error);
        throw error;
      }
    }
}
