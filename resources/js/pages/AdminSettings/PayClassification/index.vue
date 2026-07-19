<template>
  <div>
<VDialog v-model="showErrorModal" max-width="400" persistent>
  <VCard class="tw-px-8 tw-pt-10 tw-pb-7 tw-rounded-2xl tw-text-center tw-relative tw-shadow-lg kwbh-error-card">
    <!-- Close Button -->
    <button
      @click="showErrorModal = false"
      class="tw-absolute tw-top-5 tw-right-5 tw-h-8 tw-w-8 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-bg-white tw-border tw-border-[#111] tw-text-black hover:tw-bg-gray-100 tw-transition"
      aria-label="Close"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-5 tw-w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>
    <!-- Red Error Icon -->
    <div class="tw-flex tw-justify-center tw-mb-4 tw--mt-8">
      <div>
        <img
          src="/assets/images/icons/x.png"
          alt="Error"
          class="tw-h-[72px] tw-w-[72px] tw-object-contain"
        />
      </div>
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
        <p class="font-weight-bold text-h2 tw-mb-0">Pay Classifications</p>
        <div class="tw-flex tw-items-center tw-gap-3 tw-flex-1 tw-justify-center">
          <VBtn
            icon
            variant="text"
            size="large"
            @click="scrollYear(1)"
            class="tw-min-w-0"
            :style="{ visibility: yearIndex < yearList.length - 1 ? 'visible' : 'hidden' }"
          >
            <VIcon style="color: #001871">mdi-chevron-left</VIcon>
          </VBtn>
          <span class="tw-font-bold tw-text-xl tw-min-w-[60px] tw-text-center">{{ getSelectedYearLabel }}</span>
          <VBtn
            icon
            variant="text"
            size="large"
            @click="scrollYear(-1)"
            class="tw-min-w-0"
            :style="{ visibility: yearIndex === 0 ? 'hidden' : 'visible' }"
          >
            <VIcon style="color: #001871">mdi-chevron-right</VIcon>
          </VBtn>
        </div>
        <div class="tw-flex tw-items-end tw-space-x-2">
          <VBtn prepend-icon="tabler-circle-plus-filled" outlined @click="addNewYear">New Year</VBtn>
          <VBtn prepend-icon="tabler-circle-plus-filled" @click="showAddDrawer = true; drawerMode = 'add'">Add Classification</VBtn>
          <VBtn
            prepend-icon="tabler-upload"
            @click="showImportDrawer = true"
            :style="{ backgroundColor: '#001871 !important', color: '#ffffff' }"
          >
            Import Classification
          </VBtn>
        </div>
      </div>
    </div>

    <!-- Add Drawer (unchanged) -->
    <VNavigationDrawer
      v-model="showAddDrawer"
      location="right"
      width="520"
      temporary
      :scrim="true"
      class="tw-z-50"
    >
      <div class="tw-flex tw-flex-col tw-h-full">
        <AppDrawerHeaderSection :title="drawerMode === 'duplicate' ? 'Duplicate Classification' : 'Add New Classification'" @cancel="closeDrawer" class="tw-mb-6" />
        <div class="tw-overflow-y-auto tw-p-4">
          <div>
            <div>
              <label class="tw-font-medium tw-mb-1 tw-text-[13px] tw-text-[#000]">Start Date</label>
              <VueDatePicker
                :min-date="new Date()"
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
                :min-date="new Date()"
                placeholder="End Date"
                v-model="form.end_date"
                auto-apply
                format="dd/MM/yyyy"
                :enable-time-picker="false"
                teleport="body"
                class="tw-mb-3"
              />
            </div>            
            <AppTextField v-model="form.classification" :rules="[requiredValidator]" label="Classification Code" placeholder="Enter code" />
            <AppTextField v-model="form.description" label="Classification Description" placeholder="Classification description" />
            <AppTextField type="number" v-model="form.annual_salary" label="Annual Salary" placeholder="Annual Salary" />
            <hr class="my-4 border-gray-300" />
            <p class="tw-mb-2 tw-font-bold">Permanent</p>
            <AppTextField type="number" v-model="form.ordinary_hours" label="Ordinary Hours" placeholder="Ordinary Hours" />
            <AppTextField type="number" v-model="form.ot_weekday_sat_first_3" label="Mon–Fri & Sat: first 3 OT Rate" placeholder="Input Amount" />
            <AppTextField type="number" v-model="form.ot_weekday_sat_after_3" label="Mon–Fri & Sat: after 3 OT Rate" placeholder="Input Amount" />
            <AppTextField type="number" v-model="form.ot_sun_ph_all" label="Sun & PH: OT Rate" placeholder="Input Amount" />
            <AppTextField type="number" v-model="form.ph_time_off_ordinary_hours" label="PH Time-off Ordinary Hours" placeholder="Annual SalInput Amountary" />
            <hr class="my-4 border-gray-300" />            
            <p class="tw-mt-6 tw-mb-2 tw-font-bold">Casual</p>
            <AppTextField type="number" v-model="form.casual_ordinary_hours" label="Ordinary Hours" placeholder="Ordinary Hours" />
            <AppTextField type="number" v-model="form.casual_ot_weekday_sat_first_3" label="Mon–Fri & Sat: first 3 OT Rate" placeholder="Input Amount" />
            <AppTextField type="number" v-model="form.casual_ot_weekday_sat_after_3" label="Mon–Fri & Sat: after 3 OT Rate" placeholder="Input Amount" />
            <AppTextField type="number" v-model="form.casual_ot_sun_ph_all" label="Sun & PH: OT Rate" placeholder="Input Amount" />
          </div>
        </div>
        <div class="tw-flex tw-gap-2 tw-p-4 tw-border-t">
          <VBtn :disabled="!isFormValid" @click="addRecord">Add</VBtn>
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
        title="Import Classification"
        @cancel="closeImportDrawer"
        class="tw-mb-6"
      />
      <div class="tw-flex tw-flex-col tw-h-full">
        <div class="tw-overflow-y-auto tw-p-4">
          <div class="tw-flex tw-py-1 tw-mb-2 tw-flex-col">
            <label class="tw-font-medium tw-text-[13px] tw-text-[#000]">Start Date</label>
            <VueDatePicker
              :min-date="new Date()"
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
              :min-date="new Date()"
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
          <div class="tw-flex tw-justify-start tw-gap-3 tw-mt-6">
            <VBtn
              color="primary"
              :disabled="!importForm.file"
              @click="addMappedRows"
              style="min-width: 93px;"
            >Next</VBtn>
            <VBtn
              color="secondary"
              variant="tonal"
              @click="closeImportDrawer"
              style="min-width: 93px;"
            >Cancel</VBtn>
          </div>
          <!-- Preview & Add button only shown after mapping -->
          <!-- <div v-if="previewRows.length" class="tw-mt-5">
            <p class="tw-font-medium tw-mb-2">Preview</p>
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
              <VBtn color="success" :disabled="!previewRows.length" @click="addMappedRows">Add</VBtn>
            </div>
          </div> -->
        </div>
      </div>
    </VNavigationDrawer>

    <!-- Mapping Modal -->
<VDialog v-model="showMappingDialog" max-width="700" persistent>
  <VCard class="tw-relative">
    <!-- X Button (close modal) -->
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
      <VBtn style="background-color: #e25307; color: #fff !important" :disabled="!canSubmitMapping" @click="applyMapping">Import</VBtn>
    </VCardActions>
  </VCard>
</VDialog>


    <!-- Data Table -->
    <VRow>
      <VCol cols="12">
        <PayClassificationTable
          ref="tableRef"
          :selected-year="getSelectedYearLabel"
          :year-list="yearList"
          @duplicate-row="onDuplicateRow"
          @year-changed="onTableYearChanged"
        />
      </VCol>
    </VRow>
  </div>
</template>

<script>
import PayClassificationApi from "@/api/payClassification/payClassificationApi";
import YearApi from "@/api/year/yearApi";
import PayClassificationTable from "@/pages/AdminSettings/PayClassification/PayClassificationTable.vue";
import { requiredValidator } from '@core/utils/validators';
import * as XLSX from "xlsx";

export default {
  components: { PayClassificationTable },
  data() {
    return {
      drawerMode: 'add',      
      showErrorModal: false,
      errorModalTitle: 'Error',
      errorModalMessage: '',      
      yearList: [],
      selectedYear: new Date().getFullYear(),
      yearIndex: 0,
      showImportDrawer: false,
      importForm: {
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
        { key: "classification", label: "Classification Code" },
        { key: "month", label: "Month" },
        { key: "annual_salary", label: "Annual Salary" },
        { key: "ordinary_hours", label: "Ordinary Hours" },
        { key: "ot_weekday_sat_first_3", label: "Mon–Fri & Sat: first 3 OT Rate" },
        { key: "ot_weekday_sat_after_3", label: "Mon–Fri & Sat: after 3 OT Rate" },
        { key: "ot_sun_ph_all", label: "Sun & PH: OT Rate" },
        { key: "ph_time_off_ordinary_hours", label: "PH Time-off Ordinary Hours" },
        { key: "casual_ordinary_hours", label: "Casual Ordinary Hours" },
        { key: "casual_ot_weekday_sat_first_3", label: "Casual Mon–Fri & Sat: first 3 OT Rate" },
        { key: "casual_ot_weekday_sat_after_3", label: "Casual Mon–Fri & Sat: after 3 OT Rate" },
        { key: "casual_ot_sun_ph_all", label: "Casual Sun & PH: OT Rate" }
      ],
      statuses: [
        { text: 'Active', value: 'active' },
        { text: 'Inactive', value: 'inactive' }
      ],
      showYearMenu: false,
      form: {
        start_date: null,
        end_date: null,        
        classification: null,
        description: null,
        annual_salary: null,
        ot_weekday_sat_first_3: null,
        ot_weekday_sat_after_3: null,
        ot_sun_ph_all: null,
        ph_time_off_ordinary_hours: null,
        ordinary_hours: null,
        casual_ot_weekday_sat_first_3: null,
        casual_ot_weekday_sat_after_3: null,
        casual_ot_sun_ph_all: null,
        casual_ordinary_hours: null,
        status: 'active',
        year: new Date().getFullYear()
      },
      previewRows: []
    };
  },
  computed: {
    isFormValid() {
      return this.form.classification && this.form.annual_salary;
    },
    canSubmitMapping() {
      // All required fields must be mapped (used only once)
      const mappedKeys = Object.values(this.reverseFieldMappings).filter(Boolean);
      if (mappedKeys.length !== this.requiredFields.length) return false;
      const uniqueKeys = new Set(mappedKeys);
      return uniqueKeys.size === this.requiredFields.length;
    },
    getSelectedYearLabel() {
      if (!this.yearList.length) return this.selectedYear;
      return this.yearList[this.yearIndex]?.year ?? this.selectedYear;
    },
  },
  methods: {
    onTableYearChanged(newYear) {
      // Find the matching year index in the list
      const idx = this.yearList.findIndex(y => y.year === newYear);
      if (idx !== -1) {
        this.yearIndex = idx;
        this.selectedYear = this.yearList[idx];
      }
    },
    showError(message, title = 'Error') {
      this.errorModalMessage = message;
      this.errorModalTitle = title;
      this.showErrorModal = true;
    },    
    onDuplicateRow(row) {
      this.drawerMode = 'duplicate';
      // Populate all fields from row (edit as needed)
      this.form = {
        classification: row.classification,
        description: row.description,
        annual_salary: row.annual_salary,
        ot_weekday_sat_first_3: row.ot_weekday_sat_first_3,
        ot_weekday_sat_after_3: row.ot_weekday_sat_after_3,
        ot_sun_ph_all: row.ot_sun_ph_all,
        ph_time_off_ordinary_hours: row.ph_time_off_ordinary_hours,
        ordinary_hours: row.ordinary_hours,
        casual_ordinary_hours: row.casual_ordinary_hours,
        casual_ot_weekday_sat_first_3: row.casual_ot_weekday_sat_first_3,
        casual_ot_weekday_sat_after_3: row.casual_ot_weekday_sat_after_3,
        casual_ot_sun_ph_all: row.casual_ot_sun_ph_all,
        status: 'active', // Default to active or whatever you want
        year: this.getSelectedYearLabel,
        start_date: null,
        end_date: null,
        is_duplicate: true
      };
      this.showAddDrawer = true;
    },
    formatDateForSql(val) {
      if (!val) return null;
      // If value is already a Date object
      const d = typeof val === 'string' ? new Date(val) : val;
      const yyyy = d.getFullYear();
      const mm = String(d.getMonth() + 1).padStart(2, '0');
      const dd = String(d.getDate()).padStart(2, '0');
      return `${yyyy}-${mm}-${dd}`;
    },    
    resetImportState() {
      this.importForm = { start_date: null, end_date: null, file: null };
      this.csvHeaders = [];
      this.csvRows = [];
      this.reverseFieldMappings = {};
      this.previewRows = [];
      this.showMappingDialog = false;
      if (this.$refs.fileInput) {
        this.$refs.fileInput.value = '';
      }      
    },    
    async loadYearList() {
      try {
        const years = await YearApi.fetchYears();
        this.yearList = years;
        const idx = years.findIndex(
          y => y.year === (this.selectedYear.year ?? this.selectedYear)
        );
        this.yearIndex = idx !== -1 ? idx : (years.length ? years.length - 1 : 0);
        this.selectedYear = this.yearList[this.yearIndex];
      } catch (err) {
        this.$toast?.error('Failed to load years');
      }
    },
    scrollYear(dir) {
      const newIndex = this.yearIndex + dir;
      if (newIndex < 0 || newIndex >= this.yearList.length) return;
      this.yearIndex = newIndex;
      this.selectedYear = this.yearList[this.yearIndex];
      this.form.year = this.selectedYear;
      this.$refs.tableRef?.loadItems();
      console.log("SSS")
    },
    async addNewYear() {
      try {
        const res = await YearApi.addYear();
        await this.loadYearList();
        // if (this.yearList.length) {
        //   this.selectedYear = this.yearList[0];
        //   this.yearIndex = 0;
        // }
        this.$refs.tableRef?.loadItems();
      } catch (err) {
        this.$toast?.error('Failed to add year');
      }
    },
    closeImportDrawer() {
      this.showImportDrawer = false;
      this.importForm = { start_date: null, end_date: null, file: null };
      this.csvHeaders = [];
      this.csvRows = [];
      this.reverseFieldMappings = {};
      this.previewRows = [];
      this.resetImportState()
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
        const headerRow = rawRows[1] || [];
        const colNames = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        const headers = headerRow.map((cell, idx) => ({
          header: `Column ${colNames[idx] || ('Col' + (idx+1))}`,
          idx
        })).filter((col, idx) =>
          rawRows.some((row, i) => i > 1 && row[idx] !== undefined && row[idx] !== null && String(row[idx]).trim() !== '')
        );
        this.csvHeaders = headers;
        this.csvRows = rawRows.slice(2);
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
      // Allow each field to be selected only once per column
      const used = Object.values(this.reverseFieldMappings).filter(Boolean);
      return this.requiredFields.filter(field => !used.includes(field.key) || this.reverseFieldMappings[colIdx] === field.key);
    },
    isColMapped(idx) {
      return !!this.reverseFieldMappings[idx];
    },
    applyMapping() {
      if (!this.canSubmitMapping) return;
      // Build a mapping from required field to column index
      const fieldToCol = {};
      Object.keys(this.reverseFieldMappings).forEach(idx => {
        const key = this.reverseFieldMappings[idx];
        if (key) fieldToCol[key] = Number(idx);
      });
      // Generate preview rows
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

      // Format the start_date and end_date to YYYY-MM-DD
      const formattedStart = this.formatDateForSql(this.importForm.start_date);
      const formattedEnd = this.formatDateForSql(this.importForm.end_date);

      const response = await PayClassificationApi.addClassificationCSV({
        csv_data: this.previewRows,
        status: 1,
        year: this.getSelectedYearLabel,
        start_date: formattedStart,
        end_date: formattedEnd,
      });
      console.log("RESPPP", response)

      // Laravel: success=false means error (from your PHP)
      if (response?.success === false) {
        this.showError("Classification already exists for this year", "Import Error");
        return;
      }
      else{
        this.$toast.success('Import successful!');
      }
      

      this.closeImportDrawer();
      this.resetImportState();
      this.$refs.tableRef?.loadItems();      


    },
    closeDrawer() {
      this.showAddDrawer = false;
      this.showImportDrawer = false;
      this.resetForm();
    },
    resetForm() {
      this.form = {
        classification: null,
        description: null,
        annual_salary: null,
        ot_weekday_sat_first_3: null,
        ot_weekday_sat_after_3: null,
        ot_sun_ph_all: null,
        ph_time_off_ordinary_hours: null,
        ordinary_hours: null,
        casual_ot_weekday_sat_first_3: null,
        casual_ot_weekday_sat_after_3: null,
        casual_ot_sun_ph_all: null,
        casual_ordinary_hours: null,
        status: 'active',
        year: this.getSelectedYearLabel
      };
    },
    async addRecord() {
      // If you use VueDatePicker, the value might be a Date or ISO string.
      this.form.start_date = this.formatDateForSql(this.form.start_date);
      this.form.end_date = this.formatDateForSql(this.form.end_date);

      await PayClassificationApi.addClassification(this.form);
      this.$toast.success('Pay classification added successfully');
      this.closeDrawer();
      this.$refs.tableRef.loadItems();
    },
  },
  mounted() {
    this.loadYearList();
  }
};
</script>
<style scoped>
:deep(.v-input--focused .v-input__control .v-field--focused .v-field__outline) {
  border-color: #001871 !important;
}
.kwbh-error-card{
  padding: 90px 50px 20px 50px !important;
}

</style>