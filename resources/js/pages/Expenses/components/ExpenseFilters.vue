<template>
    <div class="tw-flex tw-justify-start roster-filters-wrapper">
      <AppTextField
        v-model="query"
        @input="onSearch"
        class="tw-mr-4 !tw-grow-0"
        prepend-inner-icon="tabler-search"
        placeholder="Search By Name"
        style="width: 200px"
      />
  
      <VueDatePicker
        v-model="internalFilters.dateRange"
        class="tw-mr-4 !tw-grow-0"
        placeholder="Select Date"
        format="dd/MM/yyyy"
        :enable-time-picker="false"
        style="width: 250px;"
      />
  
      <AppCombobox
        v-model="internalFilters.status"
        :items="statuses"
        clearable
        placeholder="Status"
        item-title="name"
        item-value="id"
        class="tw-mr-4 !tw-grow-0"
        style="width: 150px"
      />
  
      <AppCombobox
        v-model="internalFilters.division"
        :items="divisions"
        clearable
        placeholder="Division"
        item-title="name"
        item-value="id"
        class="tw-mr-4 !tw-grow-0"
        style="width: 150px"
      />
  
      <AppCombobox
        v-model="internalFilters.costCode"
        :items="cost_codes"
        clearable
        placeholder="Cost Codes"
        item-title="classId"
        item-value="id"
        class="tw-mr-4 !tw-grow-0"
        style="width: 150px"
      />
    </div>
  </template>
  
  <script>
  import adminSettingApi from "@/api/adminSetting/adminSettingApi";
import _ from "lodash";
  
  export default {
    props: {
      filters: {
        type: Object,
        required: true,
      },
      cost_codes: {
        type: Array,
        required: true,
      },
    },
  
    data() {
      return {
        query: null,
        internalFilters: { ...this.filters }, // Local reactive copy
        statuses: [
          { id: 1, name: "Pending Approval" },
          { id: 2, name: "Approved" },
          { id: 3, name: "Cancelled" },
          { id: 4, name: "Rejected" },
        ],
        divisions: [],
      };
    },
  
    mounted() {
      this.loadDivisions();
    },
  
    watch: {
      // Update internalFilters if parent changes (edge case)
      filters: {
        handler(newVal) {
          if (!_.isEqual(newVal, this.internalFilters)) {
            this.internalFilters = { ...newVal };
          }
        },
        deep: true
      },
      // Watch internal filters and emit (with debounce)
      internalFilters: {
        handler: _.debounce(function (val) {
          this.$emit("filter-change", { ...val });
        }, 500),
        deep: true,
      }
    },
  
    methods: {
      onSearch: _.debounce(function () {
        this.$emit("search", this.query);
      }, 500),
  
      async loadDivisions() {
        this.divisions = await adminSettingApi.getDivisions();
      }
    }
  };
  </script>