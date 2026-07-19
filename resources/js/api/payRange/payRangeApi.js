import axios from '@axios';
import { useToast } from 'vue-toast-notification';

const $toast = useToast({
  position: 'top-right'
});

export default {
  /**
   * Fetch all pay ranges
   */
  async fetchPayRanges() {
    try {
      const response = await axios.get('/api/v1/pay-ranges');
      return response.data || [];
    } catch (error) {
      $toast.error(error?.response?.data?.message || 'Something went wrong while fetching pay ranges.');
      console.error(error?.response);
      return [];
    }
  },

  async addPayRangeCSV(data) {
    try {
      const response = await axios.post('/api/v1/pay-ranges/csv', data);
      return response.data;
    } catch (error) {
      $toast.error(error?.response?.data?.message || 'Failed to add pay classification.');
      console.error(error?.response);
      return null;
    }
  },    
  

  /**
   * Fetch all active pay ranges
   */
  async fetchActivePayRanges() {
    try {
      const response = await axios.get('/api/v1/pay-ranges/active');
      return response.data || [];
    } catch (error) {
      $toast.error(error?.response?.data?.message || 'Something went wrong while fetching active pay ranges.');
      console.error(error?.response);
      return [];
    }
  },

  /**
   * Fetch pay range by id active pay ranges
   */
  async fetchPayRangeById(id) {
    try {
      const response = await axios.get(`/api/v1/pay-ranges/${id}`);
      return response.data || [];
    } catch (error) {
      $toast.error(error?.response?.data?.message || 'Something went wrong while fetching pay ranges.');
      console.error(error?.response);
      return [];
    }
  },

  /**
   * Duplicate pay range
   */
  async duplicatePayRange(id, data = {}) {
    try {
      const response = await axios.post(`/api/v1/pay-ranges/${id}/duplicate`, data);
      // $toast.success('Pay range duplicated successfully.');
      return response.data;
    } catch (error) {
      $toast.error(error?.response?.data?.message || 'Failed to duplicate pay range.');
      console.error(error?.response);
      return null;
    }
  },

  async fetchPayRangeYears() {
    try {
      const response = await axios.get('/api/v1/pay-ranges/years');
      return response.data.years || [];
    } catch (error) {
      $toast.error(error?.response?.data?.message || 'Failed to fetch available years.');
      console.error(error?.response);
      return [];
    }
  },    

  /**
   * Fetch paginated & filtered pay ranges
   * params: { page, per_page, classification_id, status, sort_by, sort_direction }
   */
  async fetchPaginatedPayRanges(params = {}) {
    try {
      const response = await axios.get('/api/v1/pay-ranges/get_paginated_ranges', { params });
      return response.data;
    } catch (error) {
      $toast.error(error?.response?.data?.message || 'Something went wrong while fetching pay ranges.');
      console.error(error?.response);
      return { data: [], total: 0 };
    }
  },

  /**
   * Add a new pay range
   * data: { classification_id, start_range, end_range, start_date, end_date, status }
   */
  async addPayRange(data) {
    try {
      const response = await axios.post('/api/v1/pay-ranges', data);
      return response.data;
    } catch (error) {
      $toast.error(error?.response?.data?.message || 'Failed to add pay range.');
      console.error(error?.response);
      return null;
    }
  },

  /**
   * Update an existing pay range
   */
  async updatePayRange(id, data) {
    try {
      const response = await axios.put(`/api/v1/pay-ranges/${id}`, data);
      // $toast.success('Pay range updated successfully.');
      return response.data;
    } catch (error) {
      $toast.error(error?.response?.data?.message || 'Failed to update pay range.');
      console.error(error?.response);
      return null;
    }
  },

  /**
   * Change status of a pay range
   */
  async updatePayRangeStatus(id, status, extras = {}) {

        console.log("Payload", status, { status, extras })

    try {
      const response = await axios.post(`/api/v1/pay-ranges/${id}/status`, status);
      // $toast.success('Pay range status updated.');
      return response.data;
    } catch (error) {
      // $toast.error(error?.response?.data?.message || 'Failed to update pay range status.');
      console.error(error?.response);
      return null;
    }
  },

  /**
   * Fetch history log for a pay range
   */
  async getPayRangeHistory(id) {
    try {
      const response = await axios.get(`/api/v1/pay-ranges/${id}/history`);
      return response.data;
    } catch (error) {
      $toast.error(error?.response?.data?.message || 'Failed to fetch pay range history.');
      console.error(error?.response);
      return [];
    }
  }
};
