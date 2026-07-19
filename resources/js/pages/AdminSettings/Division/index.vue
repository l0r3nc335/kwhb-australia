<template>
    <div>
      <div class="tw-flex tw-justify-between">
        <p class="font-weight-bold text-h2 mb-3">Divisions</p>
        <VBtn prepend-icon="tabler-circle-plus-filled" @click="showAddDrawer = true">Add</VBtn>
      </div>
  
      <VDialog
        v-model="showAddDrawer"
        max-width="500"
        persistent
        scrollable
      >
        <VCard>
          <!-- Title Section -->
          <VCardTitle class="tw-bg-orange-500 tw-text-white tw-font-bold tw-py-2 tw-px-4">
            Add Division
          </VCardTitle>

          <div>
            <div class="tw-flex tw-flex-col tw-px-4 tw-rounded">
              <div class="tw-flex tw-py-1 tw-mb-2">
                <AppTextField
                  v-model="form.name"
                  :rules="[requiredValidator]"
                  label="Division Name"
                  placeholder="Division Name"
                />
              </div>
              <!-- If you need more fields, copy this row and add them here in same format -->
              <div class="tw-flex tw-justify-end tw-py-3">
                <VBtn class="tw-mr-2" color="secondary" @click="showAddDrawer = false">Cancel</VBtn>
                <VBtn color="success" :disabled="!form.name" @click="addRecord">Save</VBtn>
              </div>
            </div>
          </div>
        </VCard>
      </VDialog>


  
      <VRow>
        <VCol cols="12">
          <DivisionTable ref="divisionTableRef" />
        </VCol>
      </VRow>
    </div>
  </template>
  
  <script>
  import DivisionApi from "@/api/division/divisionApi";
import EmployeeApi from "@/api/employee/employeeApi";
import DivisionCard from "@/pages/AdminSettings/Division/DivisionCard.vue";
import DivisionTable from "@/pages/AdminSettings/Division/DivisionTable.vue";
import { requiredValidator } from '@core/utils/validators';
  
  export default {
    components: {
      DivisionCard,
      DivisionTable
    },
  
    data() {
      return {
        showAddDrawer: false,
        divisions: [],
        statuses: [],
        selectedStatus: null,
        selectedItem: null,
        search: null,
        perPage: 10,
        form: {
          name: null
        }
      };
    },
  
    mounted() {
      this.loadStatuses();
    },
  
    methods: {
      async addRecord() {
        await DivisionApi.addDivision(this.form.name);
        this.showAddDrawer = false;
        this.form.name = null;
        this.$toast.success("Division added successfully");
  
        // Refresh the DivisionTable
        this.$refs.divisionTableRef.loadItems();
      },
  
      async loadStatuses() {
        this.statuses = await EmployeeApi.fetchStatuses();
      }
    }
  };
  </script>
  