<template>
  
  <div>

    <div class="tw-flex tw-justify-between">
      <p class="font-weight-bold text-h2 mb-3">Employment Types</p>
      <VBtn prepend-icon="tabler-circle-plus-filled" @click="showAddDrawer = true">Add Employment Basis</VBtn>
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
          Add Employment Type
        </VCardTitle>

        <div>
          <div class="tw-flex tw-flex-col tw-px-4 tw-rounded">
            <!-- Employment Type Name -->
            <div class="tw-flex tw-py-1 tw-mb-2">
              <AppTextField
                v-model="form.name"
                :rules="[requiredValidator]"
                label="Employment Type Name"
                placeholder="Employment Type Name"
              />
            </div>
            <!-- Description -->
            <div class="tw-flex tw-py-1 tw-mb-2">
              <AppTextField
                v-model="form.description"
                :rules="[requiredValidator]"
                label="Description"
                placeholder="Description"
                clearable
              />
            </div>
            <!-- Hours Per Week Numeric -->
            <div class="tw-flex tw-py-1 tw-mb-2">
              <AppTextField
                v-model.number="form.hours_per_week"
                :rules="[requiredValidator]"
                type="number"
                min="0"
                label="Hours per Week"
                placeholder="Hours per Week"
                clearable
              />
            </div>
            <!-- Valid From Date Picker -->
            <div class="tw-flex tw-py-1 tw-mb-2 tw-flex-col">
              <label class="tw-font-medium tw-mb-1 tw-text-[13px] tw-text-[#000]">Valid From</label>
              <VueDatePicker
                :min-date="new Date()"
                placeholder="Select Date"
                v-model="form.valid_from"
                auto-apply
                format="dd/MM/yyyy"
                :enable-time-picker="false"
                teleport="body"
                :rules="[requiredValidator]"
              />
              <div v-if="!form.valid_from && showValidation" class="tw-text-red-500 tw-text-xs tw-mt-1">Required</div>
            </div>
            <!-- Footer Buttons -->
            <div class="tw-flex tw-justify-end tw-py-3">
              <VBtn class="tw-mr-2" color="secondary" @click="showAddDrawer = false">Cancel</VBtn>
              <VBtn
                color="success"
                :disabled="!isFormValid"
                @click="addRecord"
              >Save</VBtn>
            </div>
          </div>
        </div>
      </VCard>
    </VDialog>
    <VRow>
      <VCol cols="12">
        <EmploymentTable ref="employmentTypeTableRef" />
      </VCol>
    </VRow>
  </div>
</template>

<script>
import employmentApi from "@/api/employment/employmentApi";
import EmploymentTable from "@/pages/AdminSettings/Employment/EmploymentTable.vue";
import VueDatePicker from '@vuepic/vue-datepicker';

export default {
  components: {
    EmploymentTable,
    VueDatePicker
  },

  data() {
    return {
      showAddDrawer: false,
      showValidation: false,
      form: {
        name: null,
        description: null,
        hours_per_week: null,
        valid_from: null,
      }
    }
  },

  computed: {
    isFormValid() {
      // Validate all fields
      return (
        !!this.form.name &&
        !!this.form.description &&
        this.form.hours_per_week !== null &&
        this.form.hours_per_week !== '' &&
        !!this.form.valid_from
      );
    }
  },

  methods: {
    async addRecord() {
      // Show validation if form is invalid
      this.showValidation = true;
      if (!this.isFormValid) return;

      let payload = {
        name: this.form.name,
        description: this.form.description,
        valid_from: null,
        hours_per_week: this.form.hours_per_week
      };
      // Format valid_from as yyyy-mm-dd
      if (this.form.valid_from) {
        const d = new Date(this.form.valid_from);
        payload.valid_from = d.toISOString().split('T')[0];
      }

      await employmentApi.addEmploymentType(payload);
      this.showAddDrawer = false;
      this.form = { name: null, description: null, hours_per_week: null, valid_from: null };
      this.showValidation = false;

      // Refresh the EmploymentTypeTable
      this.$refs.employmentTypeTableRef.loadItems();
    },
  }
}
</script>
