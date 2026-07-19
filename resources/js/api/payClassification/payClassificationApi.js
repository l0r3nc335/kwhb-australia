import axios from '@axios';
import { useToast } from 'vue-toast-notification';

const $toast = useToast({
  position: 'top-right'
});

export default {
  /**
   * Fetch all pay classifications
   */
  async fetchClassifications() {
    try {
      const response = await axios.get('/api/v1/pay-classifications');
      return response.data || [];
    } catch (error) {
      $toast.error(error?.response?.data?.message || 'Something went wrong while fetching pay classifications.');
      console.error(error?.response);
      return [];
    }
  },

  async fetchActiveClassifications() {
    try {
      const response = await axios.get('/api/v1/pay-classifications/active');
      return response.data || [];
    } catch (error) {
      $toast.error(error?.response?.data?.message || 'Something went wrong while fetching pay classifications.');
      console.error(error?.response);
      return [];
    }
  },  

  async duplicateClassification(id, data) {
    try {
      const response = await axios.post(`/api/v1/pay-classifications/${id}/duplicate`, data);
      $toast.success('Pay classification duplicated successfully.');
      return response.data;
    } catch (error) {
      $toast.error(error?.response?.data?.message || 'Failed to duplicate pay classification.');
      console.error(error?.response);
      return null;
    }
  },  

  /**
   * Fetch paginated & filtered pay classifications
   * params: { page, per_page, search, status, sort_by, sort_direction }
   */
  async fetchPaginatedClassifications(params = {}) {
    try {
      const response = await axios.get('/api/v1/pay-classifications/get_paginated_classifications', { params });
      return response.data;
    } catch (error) {
      $toast.error(error?.response?.data?.message || 'Something went wrong while fetching pay classifications.');
      console.error(error?.response);
      return { data: [], total: 0 };
    }
  },

  /**
   * Add a new pay classification
   * data: { classification, description, annual_salary, ot_weekday_sat_first_3,
   *         ot_weekday_sat_after_3, ot_sun_ph_all, ph_time_off_ordinary_hours,
   *         ordinary_hours, status }
   */
  async addClassification(data) {
    try {
      const response = await axios.post('/api/v1/pay-classifications', data);
      return response.data;
    } catch (error) {
      $toast.error(error?.response?.data?.message || 'Failed to add pay classification.');
      console.error(error?.response);
      return null;
    }
  },

  async addClassificationCSV(data) {
    try {
      const response = await axios.post('/api/v1/pay-classifications/csv', data);
      return response.data;
    } catch (error) {
      $toast.error(error?.response?.data?.message || 'Failed to add pay classification.');
      console.error(error?.response);
      return null;
    }
  },  

  /**
   * Update an existing pay classification
   */
  async updateClassification(id, data) {
    try {
      const response = await axios.put(`/api/v1/pay-classifications/${id}`, data);
      $toast.success('Pay classification updated successfully.');
      return response.data;
    } catch (error) {
      $toast.error(error?.response?.data?.message || 'Failed to update pay classification.');
      console.error(error?.response);
      return null;
    }
  },

  /**
   * Change status of a pay classification
   */
  async updateClassificationStatus(id, status, extras = {}) {
    try {
      const response = await axios.post(`/api/v1/pay-classifications/${id}/status`, { status, extras: extras });
      $toast.success('Pay classification status updated.');
      return response.data;
    } catch (error) {
      $toast.error(error?.response?.data?.message || 'Failed to update pay classification status.');
      console.error(error?.response);
      return null;
    }
  },

  /**
   * Fetch history log for a pay classification
   */
  async getClassificationHistory(id) {
    try {
      const response = await axios.get(`/api/v1/pay-classifications/${id}/history`);
      return response.data;
    } catch (error) {
      $toast.error(error?.response?.data?.message || 'Failed to fetch classification history.');
      console.error(error?.response);
      return [];
    }
  }
};
