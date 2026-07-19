<template>
  <div>
    <div class="tw-flex tw-justify-between">
      <p class="font-weight-bold text-h2 mb-3">Job Titles</p>
      <VBtn prepend-icon="tabler-circle-plus-filled" @click="showAddDrawer = true">Add Position</VBtn>
    </div>

    <!-- Drawer instead of Dialog -->
    <VNavigationDrawer
      v-model="showAddDrawer"
      location="right"
      temporary
      width="450"
      class="tw-rounded-l-2xl tw-overflow-visible"
    >
      <VCard flat>
        <!-- Title Section -->
        <VCardTitle class="tw-bg-orange-500 tw-text-white tw-font-bold tw-py-2 tw-px-4">
          Add Job Title
        </VCardTitle>

        <div>
          <div class="tw-flex tw-flex-col tw-px-4 tw-rounded">
            <!-- Name -->
            <div class="tw-flex tw-py-1 tw-mb-2">
              <AppTextField
                v-model="form.name"
                :rules="[requiredValidator]"
                label="Job Title Name"
                placeholder="Job Title Name"
              />
            </div>

            <!-- Division (Updated to Multi-Select) -->
            <div class="tw-flex tw-py-1 tw-mb-2">
              <AppSelect
                v-model="form.division_ids"
                :items="divisions"
                item-title="name"
                item-value="id"
                :rules="[requiredValidator]"
                label="Division"
                placeholder="Select Division"
                clearable
                multiple
                searchable
                color="white"
              />
            </div>

            <!-- Vaccinations multiselect (Optional) -->
            <div class="tw-flex tw-py-1 tw-mb-2">
              <v-combobox
                placeholder="Select Vaccination"
                prepend-inner-icon="tabler-search"
                clearable
                v-model="form.vaccinations"
                :search-input.sync="searchVaccinations"
                :items="vaccinations"
                item-title="name"
                item-value="id"
                multiple
                :hide-no-data="false"
                small-chips
                :menu-props="{ maxWidth: '0' }"
                color="#fff"
              >
                <template v-slot:prepend-item>
                </template>
                <template v-slot:no-data>
                  <v-list-item>
                    <v-list-item-title v-if="searchVaccinations">
                      No results matching "<strong>{{ searchVaccinations }}</strong>"
                    </v-list-item-title>
                  </v-list-item>
                </template>
              </v-combobox>
            </div>    

            <!-- Compliance multiselect (Optional) -->
            <div class="tw-flex tw-py-1 tw-mb-2">
              <v-combobox
                placeholder="Select Compliance"
                prepend-inner-icon="tabler-search"
                clearable
                v-model="form.compliances"
                :search-input.sync="searchCompliances"
                :items="compliances"
                item-title="name"
                item-value="id"
                multiple
                :hide-no-data="false"
                small-chips
                :menu-props="{ maxWidth: '0' }"
                color="#fff"
              >
                <template v-slot:prepend-item>
                </template>
                <template v-slot:no-data>
                  <v-list-item>
                    <v-list-item-title v-if="searchCompliances">
                      No results matching "<strong>{{ searchCompliances }}</strong>"
                    </v-list-item-title>
                  </v-list-item>
                </template>
              </v-combobox>

            </div>            

            <!-- Classification -->
            <div class="tw-flex tw-py-1 tw-mb-2">
              <AppSelect
                v-model="form.classification"
                :items="classifications"
                item-title="label"
                item-value="value"
                :rules="[requiredValidator]"
                label="Classification"
                placeholder="Select Classification"
                clearable
              />
            </div>

            <!-- Valid From (Start Date) -->
            <div class="tw-flex tw-py-1 tw-mb-2 tw-flex-col">
              <label class="tw-font-medium tw-mb-1 tw-text-[13px] tw-text-[#000]">Valid From</label>
              <VueDatePicker
                :min-date="new Date()"
                placeholder="Valid From"
                v-model="form.start_date"
                auto-apply
                format="dd/MM/yyyy"
                :enable-time-picker="false"
                teleport="body"
              />
            </div>

            <!-- End Date -->
            <div class="tw-flex tw-py-1 tw-mb-2 tw-flex-col">
              <label class="tw-font-medium tw-mb-1 tw-text-[13px] tw-text-[#000]">Valid Until</label>
              <VueDatePicker
                :min-date="new Date()"
                placeholder="Valid Until"
                v-model="form.end_date"
                auto-apply
                format="dd/MM/yyyy"
                :enable-time-picker="false"
                teleport="body"
              />
            </div>

            <div class="tw-flex tw-justify-end tw-py-3">
              <VBtn class="tw-mr-2" color="secondary" @click="resetForm">Cancel</VBtn>
              <VBtn color="success" :disabled="!canSave" @click="addRecord">Save</VBtn>
            </div>
          </div>
        </div>
      </VCard>
    </VNavigationDrawer>

    <VRow>
      <VCol cols="12">
        <JobTitleTable ref="jobTitleTableRef" />
      </VCol>
    </VRow>
  </div>
</template>

<script>
import { fetchCompliances } from "@/api/compliance/compliance";
import DivisionApi from "@/api/division/divisionApi";
import EmployeeApi from "@/api/employee/employeeApi";
import JobTitleApi from "@/api/jobTitle/jobTitleApi";
import payClassification from "@/api/payClassification/payClassificationApi";
import { fetchVaccinations } from "@/api/vaccination/vaccination";
import JobTitleTable from "@/pages/AdminSettings/PositionTitle/JobTitleTable.vue";
import VueDatePicker from '@vuepic/vue-datepicker';

export default {
  components: {
    JobTitleTable,
    VueDatePicker,
  },

  data() {
    return {
      showAddDrawer: false,
      jobTitles: [],
      divisions: [],
      statuses: [],
      vaccinations: [],
      searchVaccinations: "", 
      searchCompliances: "",
      compliances: [],
      selectedStatus: null,
      selectedItem: null,
      search: null,
      perPage: 10,
      classifications: [],
      form: {
        name: null,
        division_ids: [],  // Updated this to be an array for multiple divisions
        classification: null,
        start_date: null,
        end_date: null,
        vaccinations: [],  // Optional vaccinations
        compliances: []    // Optional compliances
      }
    };
  },

  computed: {
    canSave() {
      return (
        this.form.name &&
        this.form.classification &&
        this.form.start_date
      );
    }
  },

  mounted() {
    this.loadStatuses();
    this.loadDivisions();
    this.loadVaccinations();
    this.loadCompliances();
    this.loadClassifications();
  },

  methods: {
    async loadClassifications() {
      try {
        // Adjust this call according to your API
        const res = await payClassification.fetchActiveClassifications();
        const items = res.data ? res.data : res;
        this.classifications = items.map(item => ({
          label: item.classification, // This will display the text in dropdown
          value: item.id              // This will be the selected value
        }));        

        console.log("RRR", res)
      } catch (e) {
        console.error("Failed loading pay classifications:", e);
      }
    },    
    async loadVaccinations() {
      try {
        this.vaccinations = await fetchVaccinations();
      } catch (e) {
        console.error("Failed loading vaccinations:", e);
      }
    },

    async loadCompliances() {
      try {
        this.compliances = await fetchCompliances();
      } catch (e) {
        console.error('Failed loading compliances:', e);
      }
    },

    async addRecord() {
      const formatDate = (date) => {
        if (!date) return null;
        const d = new Date(date);
        const pad = n => String(n).padStart(2, '0');
        return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
      };

      // Ensure the division_ids is being passed correctly as an array
      console.log('Division IDs:', this.form.division_ids);  // Log to confirm the value

        const vaccinationIds = this.form.vaccinations.map(vaccination => vaccination.id);
        const complianceIds = this.form.compliances.map(compliance => compliance.id);



      const response = await JobTitleApi.addJobTitle({
        name: this.form.name,
        division_ids: this.form.division_ids,  // Pass the array of selected divisions
        classification: this.form.classification,
        start_date: formatDate(this.form.start_date),
        end_date: formatDate(this.form.end_date),
        vaccinations: vaccinationIds, // Optional
        compliances: complianceIds     // Optional
      });

      if(response.data.success){
        this.$toast.success("Job Title added successfully");
      }
      else{
        this.$toast.error("Job Title already exists");
      }

      this.resetForm();
      this.$refs.jobTitleTableRef.loadItems();
    },

    resetForm() {
      this.showAddDrawer = false;
      this.form.name = null;
      this.form.division_ids = [];  // Reset the divisions array
      this.form.classification = null;
      this.form.start_date = null;
      this.form.end_date = null;
      this.form.vaccinations = [];  // Reset vaccinations
      this.form.compliances = [];   // Reset compliances
    },

    async loadDivisions() {
      const res = await DivisionApi.fetchDivisions();
      this.divisions = res.data ? res.data : res;
    },

    async loadStatuses() {
      this.statuses = await EmployeeApi.fetchStatuses();
    }
  }
};
</script>
