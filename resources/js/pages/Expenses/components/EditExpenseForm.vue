<template>
    <VNavigationDrawer
      temporary
      :width="500"
      location="end"
      class="scrollable-content px-3"
      v-model="drawerOpen"
      @after-leave="$emit('close')"
    >
      <AppDrawerHeaderSection
        title="Edit Expense"
        @cancel="$emit('close')"
        class="tw-mb-6"
      />
      <div class="tw-flex tw-flex-col tw-px-6" style="overflow: auto">
        <!-- Same fields as Add form -->
        <div class="tw-flex tw-flex-col tw-mb-8 tw-text-gray-500">
          <label class="tw-mb-2 tw-font-bold">Employee Name</label>
          <VAutocomplete
            v-model="selectedEmployee"
            :items="employees"
            :item-title="e => `${e.first_name} ${e.last_name}`"
            item-value="id"
            label="Select Employee"
            return-object
            clearable
            density="comfortable"
            :disabled="isDropdownDisabled"
            :readonly="isDropdownDisabled"
          />
        </div>
  
        <div class="tw-flex tw-flex-col tw-mb-8 tw-text-gray-500">
          <label class="tw-mb-2 tw-font-bold">Expense Date</label>
          <VueDatePicker v-model="form.expense_date" format="dd/MM/yyyy" :enable-time-picker="false" />
        </div>
  
        <div class="tw-flex tw-flex-col tw-mb-8 tw-text-gray-500">
          <label class="tw-mb-2 tw-font-bold">Reason for expense</label>
          <AppTextarea v-model="form.reason" placeholder="Reason" />
        </div>
  
        <div class="tw-flex tw-flex-col tw-mb-8 tw-text-gray-500">
          <label class="tw-mb-2 tw-font-bold">Cost Code</label>
          <AppCombobox v-model="form.costCode" :items="cost_codes" item-title="classId" item-value="classId" />
        </div>
  
        <div class="tw-flex tw-flex-col tw-mb-8 tw-text-gray-500">
          <label class="tw-mb-2 tw-font-bold">Amount</label>
          <AppTextField v-model="form.amount" prepend-inner-icon="tabler-currency-dollar" type="number" min="0" />
        </div>
  
        <div class="tw-flex tw-flex-col tw-mb-8 tw-text-gray-500">
          <div class="tw-flex">
            <VBtn color="primary" class="tw-mr-2" @click="submitUpdate" :disabled="!formValidated">Update</VBtn>
            <VBtn color="secondary" @click="cancelForm">Cancel</VBtn>
          </div>
        </div>
      </div>
    </VNavigationDrawer>
  </template>
  
  <script>
  import expenseApi from "@/api/expense/expenseApi";
import { useEmployeeStore } from "@/store/employeeStore";
  
  export default {
    props: {
      open: Boolean,
      expense: Object,
      cost_codes: Array,
      employees: Array
    },
    data() {
      return {
        drawerOpen: false,
        selectedEmployee: null,
        form: {
          expense_date: null,
          reason: null,
          costCode: null,
          amount: 0,
          confirm_expense: true,
        },
        employeeStore: useEmployeeStore(),
      };
    },
    computed: {
      formValidated() {
        return this.form.costCode && this.form.expense_date && this.form.amount && this.form.reason;
      },
      isDropdownDisabled() {
        return this.authUser?.role_id === 2 || this.authUser?.role_id === 5;
      }
    },
    watch: {
      open(val) {
        this.drawerOpen = val;
        if (val && this.expense) {
          this.prepopulateForm();
        }
      },
      expense: {
        immediate: true,
        handler() {
          if (this.open && this.expense) {
            this.prepopulateForm();
          }
        }
      }
    },
    methods: {
      prepopulateForm() {
        this.form.expense_date = new Date(this.expense.expense_date);
        this.form.reason = this.expense.reason;
        this.form.costCode = this.cost_codes.find(c => c.classId === this.expense.cost_code) || null;
        this.form.amount = this.expense.amount;
        this.selectedEmployee = this.employees.find(e => e.id === this.expense.user_id);
      },
      async submitUpdate() {
        await expenseApi.updateExpense(this.expense.id, {
          expense_date: this.form.expense_date,
          reason: this.form.reason,
          cost_code: this.form.costCode?.classId,
          amount: this.form.amount,
          user_id: this.selectedEmployee?.id,
        });
        this.$emit('reload');
        this.cancelForm();
      },
      cancelForm() {
        this.drawerOpen = false;
        this.$emit('close');
      }
    }
  };
  </script>
  