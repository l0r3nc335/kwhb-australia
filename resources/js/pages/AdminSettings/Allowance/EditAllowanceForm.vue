<template>
  <VCard flat>
    <VCardTitle class="tw-bg-orange-500 tw-text-white tw-font-bold tw-py-2 tw-px-4">
      {{ isEdit ? 'Edit Allowance' : 'Add Allowance' }}
    </VCardTitle>

    <div class="tw-flex tw-flex-col tw-px-4">
      <!-- Name -->
      <div class="tw-py-1 tw-mb-2">
        <AppTextField
          v-model="localForm.name"
          label="Allowance Name"
          placeholder="Enter name"
          :rules="[requiredValidator]"
        />
      </div>

      <!-- Description -->
      <div class="tw-py-1 tw-mb-2">
        <AppTextField
          v-model="localForm.description"
          label="Description"
          placeholder="Enter description"
          clearable
          type="textarea"
          rows="3"
        />
      </div>

      <!-- Position -->
      <div class="tw-py-1 tw-mb-2">
        <AppSelect
          v-model="localForm.position_id"
          :items="positions"
          item-title="name"
          item-value="id"
          label="Position"
          placeholder="Select Position"
          clearable
          :rules="[requiredValidator]"
        />
      </div>

      <!-- Value -->
      <div class="tw-py-1 tw-mb-2">
        <AppTextField
          v-model="localForm.value"
          label="Value"
          placeholder="e.g. 2000"
          :rules="[requiredValidator]"
          type="text"
          @keypress="onlyNumberInput"
          @paste="onPasteNumber"
        />
      </div>

      <!-- Frequency -->
      <div class="tw-py-1 tw-mb-2">
        <AppSelect
          v-model="localForm.frequency"
          :items="frequencies"
          label="Frequency"
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
          v-model="localForm.start_date"
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
          v-model="localForm.end_date"
          auto-apply
          format="dd/MM/yyyy"
          :enable-time-picker="false"
          teleport="body"
        />
      </div>

      <!-- Buttons -->
      <div class="tw-flex tw-justify-end tw-py-3">
        <VBtn class="tw-mr-2" color="secondary" @click="$emit('cancel')">Cancel</VBtn>
        <VBtn color="success" :disabled="!canSave" @click="submitForm">{{ isEdit ? 'Update' : 'Save' }}</VBtn>
      </div>
    </div>
  </VCard>
</template>

<script>
import VueDatePicker from '@vuepic/vue-datepicker';

export default {
  name: 'EditAllowanceForm',
  components: { VueDatePicker },
  props: {
    value: { type: Object, default: () => ({}) }, // For v-model or prop drilling
    positions: { type: Array, default: () => [] },
    frequencies: { type: Array, default: () => [] },
    isEdit: { type: Boolean, default: false }
  },
  data() {
    return {
      localForm: {
        name: null,
        description: null,
        position_id: null,
        value: null,
        frequency: null,
        start_date: null,
        end_date: null
      }
    };
  },
  computed: {
    canSave() {
      const f = this.localForm;
      return f.name && f.position_id && f.value && f.frequency;
    }
  },
  watch: {
    value: {
      immediate: true,
      deep: true,
      handler(val) {
        // Pre-populate fields on edit
        if (val) {
          this.localForm = {
            name: val.name ?? null,
            description: val.description ?? null,
            position_id: val.position_id ?? null,
            value: val.value ?? null,
            frequency: val.frequency ?? null,
            start_date: val.start_date ?? null,
            end_date: val.end_date ?? null
          };
        }
      }
    }
  },
  methods: {
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
    submitForm() {
      // Pass filled data to parent
      this.$emit('submit', { ...this.localForm });
    },
    requiredValidator(v) {
      return !!v || 'Required';
    }
  }
};
</script>
