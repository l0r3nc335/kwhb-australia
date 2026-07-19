<template>
  <div>
    <!-- Error Modal -->
    <VDialog v-model="showErrorModal" max-width="400" persistent>
      <VCard class="tw-px-8 tw-pt-10 tw-pb-7 tw-rounded-2xl tw-text-center tw-relative tw-shadow-lg kwbh-error-card">
        <button
          @click="showErrorModal = false"
          class="tw-absolute tw-top-5 tw-right-5 tw-h-8 tw-w-8 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-bg-white tw-border tw-border-[#111] tw-text-black hover:tw-bg-gray-100 tw-transition"
          aria-label="Close"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-5 tw-w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
        <div class="tw-flex tw-justify-center tw-mb-4 tw--mt-8">
          <img src="/assets/images/icons/x.png" alt="Error" class="tw-h-[72px] tw-w-[72px] tw-object-contain" />
        </div>
        <div class="tw-font-bold tw-text-lg tw-text-[#182656] tw-mb-2">{{ errorModalMessage }}</div>
        <div class="tw-flex tw-justify-center tw-mt-3 tw-mb-1">
          <VBtn
            style="background-color: #F16821; color: #fff; font-weight: 700; width:93px;"
            class="tw-rounded tw-shadow-none tw-text-base tw-py-2"
            @click="showErrorModal = false"
          >OK</VBtn>
        </div>
      </VCard>
    </VDialog>

    <!-- Top bar and actions -->
    <div class="tw-flex tw-items-end tw-justify-between tw-mb-2">
      <div class="tw-flex-1 tw-flex tw-items-center tw-justify-between">
        <p class="font-weight-bold text-h2 tw-mb-0">Pay Ranges</p>
        <div class="tw-flex tw-items-end tw-space-x-2">
          <VBtn
            prepend-icon="tabler-upload"
            @click="showImportDrawer = true"
            :style="{ backgroundColor: '#001871 !important', color: '#ffffff' }"
          >
            Import Pay Range
          </VBtn>
          <VBtn prepend-icon="tabler-circle-plus-filled" @click="showAddDrawer = true; drawerMode = 'add'">Add Pay Range</VBtn>
        </div>
      </div>
    </div>

    <!-- Add Drawer -->
    <VNavigationDrawer
      v-model="showAddDrawer"
      location="right"
      width="520"
      temporary
      :scrim="true"
      class="tw-z-50"
    >
      <div class="tw-flex tw-flex-col tw-h-full">
        <AppDrawerHeaderSection :title="drawerMode === 'duplicate' ? 'Duplicate Pay Range' : 'Add New Pay Range'" @cancel="closeDrawer" class="tw-mb-6" />

        <div class="tw-overflow-y-auto tw-p-4">
          <AppTextField
            v-model="form.classification"
            :rules="[requiredValidator]"
            label="Classification"
            placeholder="Enter Classification"
            class="tw-mb-3"
          />
          <AppTextField type="number" v-model="form.start_range" :rules="[requiredValidator]" label="Start Range" placeholder="Start Range" class="tw-mb-3" />
          <AppTextField type="number" v-model="form.end_range" :rules="[requiredValidator]" label="End Range" placeholder="End Range" class="tw-mb-3" />
          <div>
            <label class="tw-font-medium tw-mb-1 tw-text-[13px] tw-text-[#000]">Start Date</label>
            <VueDatePicker
              :min-date="null"
              placeholder="Start Date"
              v-model="form.start_date"
              auto-apply
              format="dd/MM/yyyy"
              :enable-time-picker="false"
              teleport="body"
              class="tw-mb-3"
            />
          </div>
          <div>
            <label class="tw-font-medium tw-mb-1 tw-text-[13px] tw-text-[#000]">End Date</label>
            <VueDatePicker
              :min-date="null"
              placeholder="End Date"
              v-model="form.end_date"
              auto-apply
              format="dd/MM/yyyy"
              :enable-time-picker="false"
              teleport="body"
              class="tw-mb-3"
            />
          </div>
        </div>
        <div class="tw-flex tw-gap-2 tw-p-4 tw-border-t">
          <VBtn :disabled="!isFormValid" @click="addRecord">{{ drawerMode === 'edit' ? 'Save' : 'Add' }}</VBtn>
          <VBtn class="tw-mr-2" color="secondary" @click="closeDrawer">Cancel</VBtn>
        </div>
      </div>
    </VNavigationDrawer>

    <!-- Import Drawer -->
    <VNavigationDrawer
      v-model="showImportDrawer"
      location="right"
      width="520"
      temporary
      :scrim="true"
      class="tw-z-50"
    >
      <AppDrawerHeaderSection
        title="Import Pay Range"
        @cancel="closeImportDrawer"
        class="tw-mb-6"
      />
      <div class="tw-flex tw-flex-col tw-h-full">
        <div class="tw-overflow-y-auto tw-p-4">
          <!-- Use free text for Classification -->
          <div class="tw-flex tw-py-1 tw-mb-2 tw-flex-col">
            <label class="tw-font-medium tw-text-[13px] tw-text-[#000]">Start Date</label>
            <VueDatePicker
              :min-date="null"
              placeholder="Start Date"
              v-model="importForm.start_date"
              auto-apply
              format="dd/MM/yyyy"
              :enable-time-picker="false"
              teleport="body"
              class="tw-mb-3"
            />
          </div>
          <div class="tw-flex tw-py-1 tw-mb-2 tw-flex-col">
            <label class="tw-font-medium tw-text-[13px] tw-text-[#000]">End Date</label>
            <VueDatePicker
              :min-date="null"
              placeholder="End Date"
              v-model="importForm.end_date"
              auto-apply
              format="dd/MM/yyyy"
              :enable-time-picker="false"
              teleport="body"
              class="tw-mb-3"
            />
          </div>
          <div>
            <label class="tw-font-medium tw-mb-1 tw-text-[13px] tw-text-[#000]">Excel sheet import</label>
            <div
              class="tw-border tw-border-dashed tw-border-gray-300 tw-rounded-lg tw-py-6 tw-bg-gray-50 tw-flex tw-flex-col tw-items-center tw-justify-center tw-cursor-pointer hover:tw-bg-gray-100 tw-transition"
              @click="$refs.fileInput.click()"
              @dragover.prevent
              @drop.prevent="handleFileDrop"
            >
              <VIcon class="tw-text-gray-400" size="30">tabler-upload</VIcon>
              <span class="tw-mt-2 tw-text-gray-500 tw-font-semibold tw-text-base">Drop files here or click to upload</span>
              <input
                type="file"
                ref="fileInput"
                class="tw-hidden"
                accept=".xls,.xlsx,.csv"
                @change="onFileChange"
              />
              <div v-if="importForm.file" class="tw-mt-2 tw-text-xs tw-text-gray-600">
                {{ importForm.file.name }}
              </div>
            </div>
          </div>
          <div v-if="previewRows.length" class="tw-mt-5">
            <p class="tw-font-medium tw-mb-2">Preview (first 5 rows):</p>
            <table class="tw-table-auto tw-w-full tw-text-xs tw-border">
              <thead>
                <tr>
                  <th v-for="field in requiredFields" :key="field.key" class="tw-px-2 tw-py-1 tw-border-b">
                    {{ field.label }}
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(row, rIdx) in previewRows.slice(0,5)" :key="rIdx">
                  <td v-for="field in requiredFields" :key="field.key" class="tw-px-2 tw-py-1 tw-border-b">
                    {{ row[field.key] }}
                  </td>
                </tr>
              </tbody>
            </table>
            <div class="tw-flex tw-justify-end tw-mt-6">
              <VBtn color="primary" :disabled="!previewRows.length" @click="addMappedRows" style="min-width: 93px;">Next</VBtn>
            </div>
          </div>
        </div>
      </div>
    </VNavigationDrawer>

    <!-- Mapping Modal -->
    <VDialog v-model="showMappingDialog" max-width="700" persistent>
      <VCard class="tw-relative">
        <button
          type="button"
          @click="showMappingDialog = false"
          class="tw-absolute tw-top-4 tw-right-4 tw-h-8 tw-w-8 tw-flex tw-items-center tw-justify-center tw-rounded-full hover:tw-bg-gray-200 tw-transition"
          aria-label="Close"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-5 tw-w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
        <VCardTitle class="tw-font-bold tw-text-lg tw-p-4"><h2>Map Import Fields</h2></VCardTitle>
        <VCardText class="tw-px-6 tw-pt-2 tw-pb-2">
          <div class="tw-grid tw-gap-y-4">
            <div
              v-for="(col, idx) in csvHeaders"
              :key="col.idx"
              class="tw-flex tw-items-center tw-gap-4"
            >
              <div class="tw-flex-1">
                <label v-if="idx <= 0" class="tw-block tw-text-xs tw-font-semibold tw-text-gray-500 mb-1">Imported Column</label>
                <input
                  type="text"
                  :value="col.header"
                  disabled
                  :style="{ height: '42px', backgroundColor: '#fff', borderStyle: 'solid' }"
                  class="tw-w-full tw-border tw-rounded tw-px-2 tw-text-gray-700"
                />
              </div>
              <div class="tw-flex-1">
                <label v-if="idx <= 0" class="tw-block tw-text-xs tw-font-semibold tw-text-gray-500 mb-1">Column Name</label>
                <VSelect
                  :items="availableFields(col.idx)"
                  v-model="reverseFieldMappings[col.idx]"
                  dense
                  outlined
                  item-title="label"
                  item-value="key"
                  placeholder="Select Column Name"
                  :error="!isColMapped(col.idx)"
                  clearable
                  class="tw-w-full"
                />
              </div>
            </div>
          </div>
        </VCardText>
        <VCardActions class="tw-pt-4 tw-justify-end tw-px-6">
          <VBtn color="secondary" variant="tonal" @click="showMappingDialog = false">Cancel</VBtn>
          <VBtn style="background-color: #e25307; color: #fff !important"  :disabled="!canSubmitMapping" @click="applyMapping">Import</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Data Table -->
    <VRow>
      <VCol cols="12">
        <PayRangeTable
          ref="tableRef"
          :classification-options="classificationOptions"
          @duplicate-row="onDuplicateRow"
        />
      </VCol>
    </VRow>
  </div>
</template>

<script>
import payClassification from "@/api/payClassification/payClassificationApi";
import payRangeApi from "@/api/payRange/payRangeApi";
import PayRangeTable from "@/pages/AdminSettings/PayRange/PayRangeTable.vue";
import { requiredValidator } from '@core/utils/validators';
import * as XLSX from "xlsx";

export default {
  components: { PayRangeTable },
  data() {
    return {
      drawerMode: 'add',
      showErrorModal: false,
      errorModalTitle: 'Error',
      errorModalMessage: '',
      showImportDrawer: false,
      importForm: {
        classification: '', // changed from classification_id
        start_date: null,
        end_date: null,
        file: null
      },
      showAddDrawer: false,
      csvHeaders: [],
      csvRows: [],
      reverseFieldMappings: {},
      showMappingDialog: false,
      requiredFields: [
        { key: "classification", label: "Classification" },
        { key: "start_range", label: "Start Range" },
        { key: "end_range", label: "End Range" },
      ],
      statusOptions: [
        { label: "Active", value: "active" },
        { label: "Inactive", value: "inactive" },
        { label: "Pending", value: "pending" },
        { label: "Expired", value: "expired" }
      ],
      classificationOptions: [],
      form: {
        classification: '', // changed from classification_id
        start_range: '',
        end_range: '',
        start_date: null,
        end_date: null,
        status: 'active'
      },
      previewRows: []
    };
  },
  computed: {
    isFormValid() {
      return this.form.classification && this.form.start_range && this.form.end_range;
    },
    canSubmitMapping() {
      const mappedKeys = Object.values(this.reverseFieldMappings).filter(Boolean);
      if (mappedKeys.length !== this.requiredFields.length) return false;
      const uniqueKeys = new Set(mappedKeys);
      return (
        uniqueKeys.size === this.requiredFields.length &&
        this.requiredFields.every(f => uniqueKeys.has(f.key))
      );
    }
  },
  methods: {
    onDuplicateRow(row) {
      this.drawerMode = 'duplicate';
      this.form = {
        classification: row.classification,
        start_range: row.start_range,
        end_range: row.end_range,
        start_date: null,
        end_date: null,
        status: 'pending',
        is_duplicate: true
      };
      this.showAddDrawer = true;
    },
    showError(message, title = 'Error') {
      this.errorModalMessage = message;
      this.errorModalTitle = title;
      this.showErrorModal = true;
    },
    formatDateForSql(val) {
      if (!val) return null;
      const d = typeof val === 'string' ? new Date(val) : val;
      const yyyy = d.getFullYear();
      const mm = String(d.getMonth() + 1).padStart(2, '0');
      const dd = String(d.getDate()).padStart(2, '0');
      return `${yyyy}-${mm}-${dd}`;
    },
    resetImportState() {
      this.importForm = { classification: '', start_date: null, end_date: null, file: null };
      this.csvHeaders = [];
      this.csvRows = [];
      this.reverseFieldMappings = {};
      this.previewRows = [];
      this.showMappingDialog = false;
      if (this.$refs.fileInput) this.$refs.fileInput.value = '';
    },
    async loadClassificationOptions() {
      const res = await payClassification.fetchActiveClassifications();
      const items = res.data ? res.data : res;
      this.classificationOptions = items.map(item => ({
        label: item.classification,
        value: item.id
      }));
    },
    closeImportDrawer() {
      this.showImportDrawer = false;
      this.resetImportState();
    },
    onFileChange(e) {
      const file = e.target.files[0];
      if (file) {
        this.importForm.file = file;
        this.processExcel(file, true);
      }
    },
    handleFileDrop(e) {
      const file = e.dataTransfer.files[0];
      if (file) {
        this.importForm.file = file;
        this.processExcel(file, true);
      }
    },
    processExcel(file, openMapping = false) {
      const reader = new FileReader();
      reader.onload = (e) => {
        const data = new Uint8Array(e.target.result);
        const workbook = XLSX.read(data, { type: 'array' });
        const firstSheetName = workbook.SheetNames[0];
        const worksheet = workbook.Sheets[firstSheetName];
        const rawRows = XLSX.utils.sheet_to_json(worksheet, { header: 1, defval: '' });

        // --- Always use Excel-style letters for columns ---
        const colNames = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        // Use the max column count of any row (covers extra columns)
        const maxCols = Math.max(...rawRows.map(r => r.length));
        const headers = Array.from({length: maxCols}).map((_, idx) => ({
          header: colNames[idx] || `Col${idx+1}`, // 'A', 'B', ...
          idx
        }));

        this.csvHeaders = headers;
        this.csvRows = rawRows.slice(1); // All rows after header row
        this.reverseFieldMappings = {};

        if (openMapping) {
          this.openMappingModal();
        }
        if (this.$refs.fileInput) {
          this.$refs.fileInput.value = '';
        }
      };
      reader.readAsArrayBuffer(file);
    },


    openMappingModal() {
      this.reverseFieldMappings = {};
      this.showMappingDialog = true;
    },
    availableFields(colIdx) {
      const used = Object.values(this.reverseFieldMappings).filter(Boolean);
      return this.requiredFields.filter(field => !used.includes(field.key) || this.reverseFieldMappings[colIdx] === field.key);
    },
    isColMapped(idx) {
      return !!this.reverseFieldMappings[idx];
    },
    applyMapping() {
      if (!this.canSubmitMapping) return;
      const fieldToCol = {};
      Object.keys(this.reverseFieldMappings).forEach(idx => {
        const key = this.reverseFieldMappings[idx];
        if (key) fieldToCol[key] = Number(idx);
      });
      const rows = [];
      for (let i = 0; i < this.csvRows.length; ++i) {
        const row = this.csvRows[i];
        if (!row) continue;
        const out = {};
        let hasData = false;
        for (const field of this.requiredFields) {
          const colIdx = fieldToCol[field.key];
          out[field.key] = colIdx !== undefined ? row[colIdx] ?? '' : '';
          if (out[field.key] && String(out[field.key]).trim() !== '') hasData = true;
        }
        if (hasData) rows.push(out);
      }
      this.previewRows = rows;
      this.showMappingDialog = false;
    },
    async addMappedRows() {
      if (!this.previewRows.length) return;
      const formattedStart = this.formatDateForSql(this.importForm.start_date);
      const formattedEnd = this.formatDateForSql(this.importForm.end_date);

      const response = await payRangeApi.addPayRangeCSV({
        classification: this.importForm.classification, // send as string
        csv_data: this.previewRows,
        start_date: formattedStart,
        end_date: formattedEnd,
      });
      if (response?.success === false) {
        this.showError("Pay range already exists for this classification", "Import Error");
        return;
      } else {
        this.$toast.success('Import successful!');
      }
      this.closeImportDrawer();
      this.resetImportState();
      this.$refs.tableRef?.loadItems();
    },
    closeDrawer() {
      this.showAddDrawer = false;
      this.resetForm();
    },
    resetForm() {
      this.form = {
        classification: '',
        start_range: '',
        end_range: '',
        start_date: null,
        end_date: null,
        status: 'active'
      };
    },
    async addRecord() {
      this.form.start_date = this.formatDateForSql(this.form.start_date);
      this.form.end_date = this.formatDateForSql(this.form.end_date);
      if (this.drawerMode === 'edit') {
        await payRangeApi.updateRange(this.form.id, this.form);
        this.$toast.success('Pay range updated successfully');
      } else {
        await payRangeApi.addPayRange(this.form);
        this.$toast.success('Pay range added successfully');
      }
      this.closeDrawer();
      this.$refs.tableRef.loadItems();
    }
  },
  mounted() {
    this.loadClassificationOptions();
  }
};
</script>

<style scoped>
:deep(.v-input--focused .v-input__control .v-field--focused .v-field__outline) {
  border-color: #001871 !important;
}
.kwbh-error-card {
  padding: 90px 50px 20px 50px !important;
}
</style>
