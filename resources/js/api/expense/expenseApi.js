import axios from '@axios';
import { useToast } from "vue-toast-notification";
const $toast = useToast({
    position: 'top-right'
})

export default {

    async fetchCodes() {
        let expenses = []

        const params = {};

        await axios.get(`/api/v1/expenses/get_codes`, {
            params: params
        })
        .then(res => {
            expenses = res.data || []
        })
        .catch(err => {
            $toast.error(err.response.data.message || 'Something went wrong while fetching approvers')
            console.log(error.response)
        })

        return expenses
    },    
    async fetchExpenses(search, date, status, division, costCode, page = 1) {
        let expenses = []

        const params = {
            search,
            date,
            status,
            division,
            costCode,
            page
        };

        await axios.get(`/api/v1/expenses`, {
            params: params
        })
        .then(res => {
            expenses = res.data || []
        })
        .catch(err => {
            $toast.error(err.response.data.message || 'Something went wrong while fetching approvers')
            console.log(error.response)
        })

        return expenses
    },

    async fetchExpensesWithPagination(search, date, status, division, costCode, page = 1, sortBy = null, sortDesc = false, per_page = 10) {
        let expenses = [];
    
        const params = {
            search,
            date,
            status,
            division,
            costCode,
            page,
            sort_by: sortBy,
            sort_desc: sortDesc,
            per_page: per_page
        };
    
        await axios.get(`/api/v1/expenses`, { params })
            .then(res => {
                expenses = res.data || [];
            })
            .catch(err => {
                $toast.error(err.response.data.message || 'Something went wrong while fetching expenses');
                console.log(err.response);
            });
    
        return expenses;
    },

    async addExpense(formData) {
        await axios.post('/api/v1/expenses', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        });
      },

    async updateExpense(id, status, reviewer_comment = null) {
        await axios.put(`/api/v1/expenses/${id}`, {
          status,
          ...(reviewer_comment ? { reviewer_comment } : {})
        })
        .then(() => {
          $toast.success('Expense status updated successfully');
        })
        .catch(err => {
          $toast.error(err.response?.data?.message || 'Failed to update expense status');
          console.error(err.response);
        });
    } 
}
