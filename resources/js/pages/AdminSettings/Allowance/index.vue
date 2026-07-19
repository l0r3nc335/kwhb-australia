<template>
  <div>
    <div class="tw-flex tw-justify-between">
      <p class="font-weight-bold text-h2 mb-3">Allowances</p>
      <VBtn prepend-icon="tabler-circle-plus-filled" @click="showAddDrawer = true">Add Allowance</VBtn>
    </div>

    <!-- Drawer -->
    <VNavigationDrawer
      v-model="showAddDrawer"
      location="right"
      temporary
      width="450"
      class="tw-rounded-l-2xl tw-overflow-visible"
    >
      <VCard flat>
        <VCardTitle class="tw-bg-orange-500 tw-text-white tw-font-bold tw-py-2 tw-px-4">
          Add Allowance
        </VCardTitle>

        <div class="tw-flex tw-flex-col tw-px-4">
          <!-- Name -->
          <div class="tw-py-1 tw-mb-2">
            <label class="tw-font-medium tw-mb-1 tw-text-[13px]">Allowance Name</label>
            <AppTextField
              v-model="form.name"
              placeholder="Enter name"
              :rules="[requiredValidator]"
            />
          </div>

          <!-- Description -->
          <div class="tw-py-1 tw-mb-2">
            <label class="tw-font-medium tw-mb-1 tw-text-[13px]">Description</label>
            <AppTextField
              v-model="form.description"
              placeholder="Enter description"
              clearable
              type="textarea"
              rows="3"
            />
          </div>

          <!-- Position -->
          <div class="tw-py-1 tw-mb-2">
            <label class="tw-font-medium tw-mb-1 tw-text-[13px]">Position</label>
            <!--AppSelect
              v-model="form.position_id"
              :items="positions"
              item-title="name"
              item-value="id"
              placeholder="Select Position"
              clearable
              :rules="[requiredValidator]"
            /-->
              <v-combobox
                  placeholder="Select Positionsss"
                  v-model="form.position_id"
                  :items="positions"
                  item-title="name"
                  item-value="id"
                  clearable
                  multiple
                  small-chips
                  :hide-no-data="false"
                  :menu-props="{ maxWidth: '0' }"
                  color="white"
              >
                  <!--template v-slot:prepend-item>
                      <v-list-item
                          @click="selectPositions"
                          :key="'select-all'"
                      >
                          <v-list-item-content>
                              <v-list-item-title>Select All</v-list-item-title>
                          </v-list-item-content>
                      </v-list-item>
                      <v-divider />
                  </template-->
              </v-combobox>
          </div>

          <!-- Value -->
          <div class="tw-py-1 tw-mb-2">
            <label class="tw-font-medium tw-mb-1 tw-text-[13px]">Value</label>
            <AppTextField
              v-model="form.value"
              placeholder="e.g. 2000"
              :rules="[requiredValidator]"
              type="number"
              min="0"
              step="any"
            >
              <template #prepend-inner>
                <span class="tw-text-[13px] tw-text-gray-500">$</span>
              </template>
            </AppTextField>
          </div>

          <!-- Frequency -->
          <div class="tw-py-1 tw-mb-2">
            <label class="tw-font-medium tw-mb-1 tw-text-[13px]">Frequency</label>
            <AppSelect
              v-model="form.frequency"
              :items="frequencies"
              placeholder="e.g. Monthly"
              clearable
              :rules="[requiredValidator]"
            />
          </div>

          <!-- Start Date -->
          <div class="tw-py-1 tw-mb-2 tw-flex-col">
            <label class="tw-font-medium tw-mb-1 tw-text-[13px]">Start Date</label>
            <VueDatePicker
              placeholder="Start Date"
              v-model="form.start_date"
              auto-apply
              format="dd/MM/yyyy"
              :enable-time-picker="false"
              teleport="body"
            />
          </div>

          <!-- End Date -->
          <div class="tw-py-1 tw-mb-2 tw-flex-col">
            <label class="tw-font-medium tw-mb-1 tw-text-[13px]">End Date</label>
            <VueDatePicker
              placeholder="End Date"
              v-model="form.end_date"
              auto-apply
              format="dd/MM/yyyy"
              :enable-time-picker="false"
              teleport="body"
            />
          </div>

          <!-- Buttons -->
          <div class="tw-flex tw-justify-end tw-py-3">
            <VBtn class="tw-mr-2" color="secondary" @click="resetForm">Cancel</VBtn>
            <VBtn color="success" :disabled="!canSave" @click="addRecord">Save</VBtn>
          </div>
        </div>
      </VCard>
    </VNavigationDrawer>

    <VRow>
      <VCol cols="12">
        <AllowanceTable ref="allowanceTableRef"/>
      </VCol>
    </VRow>
  </div>
</template>

<script>
import AllowanceApi from "@/api/allowance/allowanceApi";
import JobTitleApi from '@/api/jobTitle/jobTitleApi';
import AllowanceTable from "@/pages/AdminSettings/Allowance/AllowanceTable.vue";
import VueDatePicker from '@vuepic/vue-datepicker';

export default {
  components: {
    AllowanceTable,
    VueDatePicker
  },

  data() {
    return {
      showAddDrawer: false,
      positions: [],
      frequencies: ['Daily', 'Weekly', 'Monthly', 'Yearly'],
      form: {
        name: null,
        description: null,
        position_id: [],
        value: null,
        frequency: null,
        start_date: null,
        end_date: null
      }
    };
  },

  computed: {
    canSave() {
      return this.form.name && this.form.position_id && this.form.value && this.form.frequency;
    }
  },

  mounted() {
    this.loadPositions();
    this.updateStatus();
  },

  methods: {
      selectPositions() {

          //form.position_id
          if (this.form.position_id.length === this.positions.length) {
              this.form.position_id = [];
          } else {
              this.form.position_id = [...this.positions];
          }
      },
      async updateStatus() {
          try {
              const res = await AllowanceApi.updateAllowancesStatus();
              console.log('Allowance updated:', res);
          } catch (err) {
              console.error('Failed to update allowance:', err);
          }
      },
        onlyNumberInput(e) {
          if (!/[0-9]/.test(e.key)) {
            e.preventDefault();
          }
        },
        onPasteNumber(e) {
          const pasted = (e.clipboardData || window.clipboardData).getData('text');
          if (!/^\d+$/.test(pasted)) {
            e.preventDefault();
          }
        },
    async loadPositions() {
      const res = await JobTitleApi.fetchJobTitles()
      //const res = await JobTitleApi.fetchAllowanceJobTitles()
      this.positions = res || []
      
      console.log("@@@@1 position", res)
    },

    async addRecord() {
      const formatDate = date => {
        if (!date) return null;
        const d = new Date(date);
        const pad = n => String(n).padStart(2, '0');
        return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
      };

      const response = await AllowanceApi.addAllowance({
        name: this.form.name,
        description: this.form.description,
        position_id: this.form.position_id,
        value: this.form.value,
        frequency: this.form.frequency,
        start_date: formatDate(this.form.start_date),
        end_date: formatDate(this.form.end_date)
      });

      if (response) {
        this.$toast.success("Allowance added successfully");
        this.resetForm();
        this.$refs.allowanceTableRef.loadItems();
      }
    },

    resetForm() {
      this.showAddDrawer = false;
      this.form = {
        name: null,
        description: null,
        position_id: null,
        value: null,
        frequency: null,
        start_date: null,
        end_date: null
      };
    }
  }
};
</script>
<style scoped>
:deep(.v-label) {
  font-size: 13px !important;
}
</style>

