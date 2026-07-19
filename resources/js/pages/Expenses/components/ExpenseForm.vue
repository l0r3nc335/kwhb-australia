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
        title="Add Expense"
        @cancel="$emit('close')"
        class="tw-mb-6"
      />
      <div class="tw-flex tw-flex-col tw-px-6" style="overflow: auto">
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
          <VueDatePicker class="tw-mr-4 !tw-grow-0" :max-date="new Date()" placeholder="Select Date" v-model="form.expense_date" auto-apply format="dd/MM/yyyy" :enable-time-picker="false"  ></VueDatePicker>
        </div>
  
        <div class="tw-flex tw-flex-col tw-mb-8 tw-text-gray-500">
          <label class="tw-mb-2 tw-font-bold">Reason for expense</label>
          <AppTextarea v-model="form.reason" placeholder="Reason" />
        </div>
  
        <div class="tw-flex tw-flex-col tw-mb-8 tw-text-gray-500">
          <label class="tw-mb-2 tw-font-bold">Cost Code</label>
          <AppCombobox
            class="tw-mr-3 !tw-grow-0"
            clearable
            v-model="form.costCode"
            :items="cost_codes"
            placeholder="Cost Code"
            item-title="classId"
            item-value="classId"
          />
        </div>
  
        <div class="tw-flex tw-flex-col tw-mb-8 tw-text-gray-500">
          <label class="tw-mb-2 tw-font-bold">Amount</label>
          <AppTextField
            v-model="form.amount"
            prepend-inner-icon="tabler-currency-dollar"
            type="number"
            min="0"
            @focus="clearAmount"
            @blur="restoreAmount"
          />
        </div>
  
        <div class="tw-flex tw-flex-col tw-mb-8 tw-text-gray-500">
          <label class="tw-mb-2 tw-font-bold">Receipt Upload</label>
          <div
            @click="$refs.leaveAttachment.click()"
            @dragover.prevent
            @drop.prevent="handleDrop"
            v-if="!leaveAttachmentFile"
            class="tw-relative tw-flex tw-flex-col tw-border tw-border-dashed tw-mt-2 tw-h-40 tw-justify-center tw-items-center tw-rounded tw-cursor-pointer hover:tw-bg-gray-50"
          >
            <input
              ref="leaveAttachment"
              type="file"
              class="tw-w-px tw-h-px tw-opacity-0 tw-overflow-hidden tw-absolute"
              @change="setAttachment"
            />
            <div class="tw-flex tw-flex-col tw-items-center">
              <div class="tw-rounded tw-bg-gray-200 tw-w-fit tw-px-2 tw-py-1 tw-mb-4">
                <VIcon icon="tabler-upload" />
              </div>
              <div>Drop files here or click to upload.</div>
            </div>
          </div>

          <!-- ✅ Preview -->
          <div v-if="leaveAttachmentFile" class="tw-mt-2 tw-text-sm tw-text-gray-700 tw-flex tw-items-center tw-gap-2">
            <VIcon icon="tabler-paperclip" />
            <span>{{ leaveAttachmentFile.name }}</span>
          </div>

          <div v-if="!leaveAttachmentFile && triedSubmit" class="tw-text-red-500 tw-mt-1 tw-text-sm">
            Receipt is required.
          </div>
        </div>
  
        <div class="tw-flex tw-flex-col tw-mb-8 tw-text-gray-500">
          <div class="tw-flex">
            <VCheckbox v-model="form.confirm_expense" />
            <div class="tw-flex tw-flex-col tw-justify-center">
              This expense claim is work-related and accurate.
            </div>
          </div>
        </div>
  
        <div class="tw-flex tw-flex-col tw-mb-8 tw-text-gray-500">
          <div class="tw-flex">
            <VBtn
              type="button"
              color="primary"
              class="tw-mr-2"
              :disabled="!formValidated"
              @click.prevent="submitExpense"
            >
              Submit
            </VBtn>
            <VBtn color="secondary" class="tw-mr-2" @click="cancelForm">Cancel</VBtn>
          </div>
        </div>
      </div>
    </VNavigationDrawer>
  </template>
  
  <script>
  import expenseApi from "@/api/expense/expenseApi";
import { useEmployeeStore } from "@/store/employeeStore";
import _ from "lodash";
  
  export default {
    props: {
      open: Boolean,
      cost_codes: {
        type: Array,
        default: () => [],
      },
      employees: Array,
      // authUser: Object, // ✅ Now required
    },
  
    data() {
      return {
        drawerOpen: false,
        form: {
          expense_date: null,
          reason: null,
          costCode: null,
          amount: 0.0,
          confirm_expense: false,
        },
        employeeStore: useEmployeeStore(),
        selectedEmployee: null,
        leaveAttachmentFile: null,
        triedSubmit: false,        
      };
    },
  
    computed: {
      formValidated() {
        return (
          this.form.costCode &&
          this.form.expense_date &&
          this.form.amount &&
          this.form.reason &&
          this.form.confirm_expense &&
          this.leaveAttachmentFile // 👈 file must be selected
        );
      },
      isDropdownDisabled() {
        return [2, 5].includes(this.authUser?.role_id);
      },
      employees() {
        return this.employeeStore.data.employees;
      }      
    },
  
    mounted() {
      this.drawerOpen = this.open;
      this.loadEmployees();
      this.$nextTick(() => {
        this.$refs.datepicker?.openMenu?.(); // Optional if VueDatePicker supports it
      });
    },
  
    methods: {
      setAttachment(e) {
        const file = e.target.files[0];
        if (file) this.leaveAttachmentFile = file;
      },

      handleDrop(e) {
        const file = e.dataTransfer?.files?.[0];
        if (file) {
          this.leaveAttachmentFile = file;
        }
      },      
  
      clearAmount() {
        if (this.form.amount === 0 || this.form.amount === "0.00") {
          this.form.amount = "";
        }
      },
  
      restoreAmount() {
        if (this.form.amount === "" || this.form.amount === null) {
          this.form.amount = 0.0;
        }
      },
  
      loadFromSearch: _.debounce(function () {
        this.loadEmployees();
      }, 1000),
  
      async loadEmployees() {
        await this.employeeStore.setEmployees();
        this.selectedEmployee = this.authUser?.employee;
      },
  
      async submitExpense(event) {

        this.triedSubmit = true;
        
        if (!this.formValidated) return;

        if (event?.preventDefault) event.preventDefault();
  
        let employeeId = this.selectedEmployee?.id;
        if ([2, 5].includes(this.authUser?.role_id)) {
          employeeId = this.authUser?.employee?.id;
        }
        try {
          await expenseApi.addExpense({
            date: this.form.expense_date?.toISOString().split("T")[0],
            reason: this.form.reason,
            cost_code: this.form.costCode.classId,
            amount: this.form.amount,
            user_id: employeeId,
            receipt: this.leaveAttachmentFile ?? null,
          });
          this.$emit('reload'); 
          this.$toast.success("Expense submitted successfully");
          this.loadEmployees();
          this.cancelForm();          
        }
        catch (err) {
          console.error(err);
          this.$toast.error("Something went wrong while submitting.");
        }        

      },
  
      cancelForm() {
        this.form = {
          expense_date: null,
          reason: null,
          costCode: null,
          amount: 0.0,
          confirm_expense: false,
        };
        this.$emit("close");
      },
    },
  
    watch: {
      open(val) {
        this.drawerOpen = val;


        if (val) {
          this.leaveAttachmentFile = null; // ✅ Clear uploaded file on open
          this.triedSubmit = false;        // (optional) also reset validation flag
        }        
        this.loadEmployees()
      },
    },
  };
  </script>
  