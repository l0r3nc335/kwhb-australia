<template>
  <div>
    <!-- HISTORY DIALOG -->
    <VDialog v-model="historyDialog" max-width="800" persistent scrollable>
      <VCard class="tw-rounded-xl tw-pb-2">
        <VCardTitle class="text-center tw-font-bold tw-text-2xl tw-pt-6 tw-pb-2">
          History
        </VCardTitle>
        <VCardText class="px-6 pb-0" style="max-height: 400px; overflow-y: auto;">
          <VTable class="tw-w-full" density="comfortable" hide-default-header>
            <thead class="tw-border-b tw-border-gray-200">
              <tr class="tw-text-sm tw-text-gray-700">
                <th class="tw-font-normal tw-text-left tw-py-2">Date</th>
                <th class="tw-font-normal tw-text-left tw-py-2">User</th>
                <th class="tw-font-normal tw-text-left tw-py-2">Action</th>
                <th class="tw-font-normal tw-text-left tw-py-2">Activity</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(entry, idx) in selectedHistory"
                :key="entry.id"
                :class="idx % 2 === 0 ? 'tw-bg-gray-100' : ''"
                class="tw-text-sm tw-text-gray-900"
              >
                <td class="tw-py-3">
                  <div>{{ formatDateDateOnly(entry.created_at) }}</div>
                  <div class="tw-text-xs tw-text-gray-500">{{ formatTimeOnly(entry.created_at) }}</div>
                </td>
                <td class="tw-py-3">
                  {{ entry.user?.employee?.first_name }} {{ entry.user?.employee?.last_name }}
                </td>
                <td class="tw-py-3">{{ formatAction(entry.action) }}</td>
                <td class="tw-py-3">{{ entry.activity }}</td>
              </tr>
            </tbody>
          </VTable>
        </VCardText>
        <VCardActions class="justify-center pt-4">
          <VBtn
            @click="historyDialog = false"
            class="tw-font-medium tw-px-6 tw-rounded"
            height="38"
            style="background-color: #e45205; color: #fff !important;"
          >
            Exit
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- ACTION DRAWER/SLIDER (Deactivate/Reactivate) -->
    <VNavigationDrawer
      v-model="actionDialog.open"
      location="right"
      width="400"
      temporary
      :scrim="true"
      class="tw-z-50"
    >
      <div class="tw-flex tw-flex-col tw-h-full">
        <div class="tw-flex-shrink-0 tw-p-4 tw-border-b">
          <div class="text-lg font-bold">
            {{ actionDialog.title }}
          </div>
        </div>
        <div class="tw-flex-1 tw-overflow-y-auto tw-p-4">
          <div v-if="actionDialog.type === 'setExpiryDate'">
            <div class="tw-mb-2">Please select an expiry date:</div>
            <VueDatePicker
              :min-date="new Date()"
              v-model="actionDialog.form.date"
              auto-apply
              format="dd/MM/yyyy"
              :enable-time-picker="false"
              teleport="body"
              class="tw-mt-2"
            />
          </div>
          <div v-else-if="actionDialog.type === 'setStartDate'">
            <div class="tw-mb-2">Please select a start date:</div>
            <VueDatePicker
              :min-date="new Date()"
              v-model="actionDialog.form.date"
              auto-apply
              format="dd/MM/yyyy"
              :enable-time-picker="false"
              teleport="body"
              class="tw-mt-2"
            />
          </div>
        </div>
        <div class="tw-flex tw-gap-2 tw-p-4 tw-border-t tw-justify-end">
          <VBtn variant="text" @click="closeActionDialog">Cancel</VBtn>
          <VBtn color="primary" @click="submitActionDialog" :disabled="!actionDialogReady">OK</VBtn>
        </div>
      </div>
    </VNavigationDrawer>

    <!-- FILTER BAR -->
    <div class="tw-flex tw-items-center tw-justify-between tw-py-2 tw-mb-4">
      <div class="tw-flex-shrink-0" style="min-width:90px;max-width:100px;">
        <AppSelect
          v-model="localPerPage"
          :items="perPageOptions"
          item-title="label"
          item-value="value"
          placeholder="Per Page"
          class="tw-w-full"
        />
      </div>
      <div class="tw-flex tw-items-center tw-space-x-2">
        <AppTextField
          v-model="localSearch"
          placeholder="Search range"
          clearable
          prepend-inner-icon="tabler-search"
          class="tw-h-10 tw-min-w-[180px]"
          input-class="tw-h-10"
        />
        <!-- <AppSelect
          v-model="localSelectedClassification"
          :items="classificationOptions"
          item-title="label"
          item-value="value"
          placeholder="Classification"
          clearable
          class="tw-h-10 tw-min-w-[150px]"
        /> -->
        <AppTextField
          v-model="localPayRangeMin"
          placeholder="Pay Range Min"
          type="number"
          clearable
          class="tw-h-10 tw-min-w-[110px]"
          input-class="tw-h-10"
        />
        <AppTextField
          v-model="localPayRangeMax"
          placeholder="Pay Range Max"
          type="number"
          clearable
          class="tw-h-10 tw-min-w-[110px]"
          input-class="tw-h-10"
        />
        <AppSelect
          v-model="localSelectedYear"
          :items="yearOptions"
          item-title="label"
          item-value="value"
          placeholder="Year"
          class="tw-h-10 tw-min-w-[110px]"
          clearable
        />             
        <AppSelect
          v-model="localSelectedStatus"
          :items="statusOptions"
          item-title="label"
          item-value="value"
          placeholder="Status"
          clearable
          class="tw-h-10 tw-min-w-[130px]"
        />   
      </div>
    </div>

    <!-- MAIN TABLE -->
    <div class="tw-bg-white tw-shadow">
      <VDataTableServer
        :headers="headers"
        :items="payRanges"
        :items-length="pagination.total"
        :items-per-page="pagination.per_page"
        :page="pagination.page"
        :sort-by.sync="sortBy"
        @update:options="onOptionsUpdate"
        item-value="id"
        class="tw-w-full payrange-table"
      >
        <template #headers="{ headers }">
          <tr>
            <template v-for="column in headers" :key="column.id">
              <th
                v-for="item in column"
                :key="item.title"
                :class="`custom-header align-${item.align}`"
              >
                <span
                  v-if="item.sortable"
                  style="display: inline-flex; align-items: center;"
                  @click="toggleSort(item)"
                >
                  <span>{{ item.title }}</span>
                  <span v-if="isSorted(item)">
                    <VIcon v-if="sortBy[0].order === 'desc'" style="font-size: medium;">
                      tabler-chevron-down
                    </VIcon>
                    <VIcon v-else style="font-size: medium;">
                      tabler-chevron-up
                    </VIcon>
                  </span>
                  <VIcon v-else style="font-size: medium;">
                    tabler-selector
                  </VIcon>
                </span>
                <span v-else>
                  {{ item.title }}
                </span>
              </th>
            </template>
          </tr>
        </template>

        <!-- Columns -->
        <template #item.classification="{ item }">
          <span>{{ item.classification ?? '' }}</span>
        </template>
        <template #item.start_range="{ item }">
          <span>${{ formatFloat(item.start_range) }}</span>
        </template>
        <template #item.end_range="{ item }">
          <span>${{ formatFloat(item.end_range) }}</span>
        </template>
        <template #item.start_date="{ item }">
          <span>{{ formatDateDateOnly(item.start_date) }}</span>
        </template>
        <template #item.end_date="{ item }">
          <span>{{ formatDateDateOnly(item.end_date) }}</span>
        </template>
        <template #item.status="{ item }">
          <VChip label size="default" :style="chipStyle(item.status)">
            {{ capitalizeStatus(item.status) }}
          </VChip>
        </template>

        <!-- KEBAB MENU -->
        <template #item.action="{ item }">
          <IconBtn>
            <VIcon icon="tabler-dots-vertical" />
            <VMenu activator="parent">
              <VList>
                <VListItem v-if="item.status === 'active'" @click="onDeactivate(item)">
                  Deactivate
                </VListItem>
                <VListItem v-if="item.status === 'inactive' || item.status === 'expired'" @click="onReactivate(item)">
                  Reactivate
                </VListItem>
                <VListItem @click="onDuplicate(item)">Duplicate</VListItem>
                <VListItem @click="viewHistory(item.id)">View History</VListItem>
              </VList>
            </VMenu>
          </IconBtn>
        </template>

        <template #bottom>
          <VDivider />
          <div class="d-flex align-center justify-sm-space-between pa-5 pt-3">
            <p class="text-sm text-disabled mb-0">{{ rangeText }}</p>
            <VPagination
              v-if="showPagination"
              :model-value="pagination.page"
              @update:model-value="val => onOptionsUpdate({ ...pagination, page: val })"
              :length="pageCount"
              :total-visible="6"
            />
          </div>
        </template>
      </VDataTableServer>
    </div>
  </div>
</template>

<script>
import payRangeApi from '@/api/payRange/payRangeApi';
import dayjs from 'dayjs';
import timezone from 'dayjs/plugin/timezone';
import utc from 'dayjs/plugin/utc';
import _ from 'lodash';
import { VDataTableServer } from 'vuetify/labs/VDataTable';

dayjs.extend(utc)
dayjs.extend(timezone)

export default {
  name: 'PayRangeTable',
  components: { VDataTableServer },
  props: {
    classificationOptions: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      yearOptions: [],
      localSelectedYear: null,      
      historyDialog: false,
      selectedHistory: [],
      actionDialog: {
        open: false,
        type: null,
        title: '',
        item: null,
        form: {}
      },
      localSearch: '',
      localPerPage: 10,
      localPayRangeMin: null,
      localPayRangeMax: null,
      localSelectedClassification: null,
      localSelectedStatus: null,
      payRanges: [],
      headers: [
        { title: 'Custom Pay', key: 'classification', sortable: true },
        { title: 'Start Range', key: 'start_range', sortable: true },
        { title: 'End Range', key: 'end_range', sortable: true },
        { title: 'Start Date', key: 'start_date', sortable: true },
        { title: 'End Date', key: 'end_date', sortable: true },
        { title: 'Status', key: 'status', sortable: true },
        { title: '', key: 'action', sortable: false }
      ],
      pagination: {
        page: 1,
        per_page: 10,
        total: 0
      },
      sortBy: [
        { key: 'start_range', order: 'asc' }
      ],
      statusOptions: [
        { label: 'Active', value: 'active' },
        { label: 'Inactive', value: 'inactive' },
        { label: 'Pending', value: 'pending' },
        { label: 'Expired', value: 'expired' }
      ],
      perPageOptions: [
        { label: '10', value: 10 },
        { label: '25', value: 25 },
        { label: '50', value: 50 },
        { label: '100', value: 100 },
      ]
    };
  },
  computed: {
    showPagination() {
      return (
        this.pagination.per_page !== -1 &&
        this.pagination.total > this.pagination.per_page
      );
    },
    pageCount() {
      return Math.ceil(
        this.pagination.total / this.pagination.per_page
      );
    },
    rangeText() {
      const start =
        (this.pagination.page - 1) * this.pagination.per_page + 1;
      let end = this.pagination.page * this.pagination.per_page;
      if (end > this.pagination.total) end = this.pagination.total;
      return this.pagination.total
        ? `Showing ${start} to ${end} of ${this.pagination.total}`
        : 'No entries';
    },
    actionDialogReady() {
      if (
        this.actionDialog.type === 'setExpiryDate' ||
        this.actionDialog.type === 'setStartDate'
      ) {
        return !!this.actionDialog.form.date;
      }
      return true;
    }
  },
  watch: {
    localSelectedYear() {
      this.pagination.page = 1;
      this.loadItems();
    },    
    localPayRangeMin() {
      this.pagination.page = 1;
      this.loadItems();
    },
    localPayRangeMax() {
      this.pagination.page = 1;
      this.loadItems();
    },    
    localSearch() {
      console.log("SSS")
      this.pagination.page = 1;
      this.loadItems();
    },
    localPerPage(val) {
      this.pagination.page = 1;
      this.pagination.per_page = val;
      this.loadItems();
    },
    localSelectedStatus() {
      this.pagination.page = 1;
      this.loadItems();
    },
    localSelectedClassification() {
      this.pagination.page = 1;
      this.loadItems();
    }
  },
  methods: {
    formatDateDateOnly(dateStr) {
        if (!dateStr) return '-'
        return dayjs(dateStr).tz('Australia/Sydney').format('DD-MM-YYYY')
    },     
    formatFloat(val) {
      if (val === null || val === undefined || val === '') return '';
      // Always show at least 2, but up to 10 if significant digits exist
      const num = parseFloat(val);
      // If it's an integer or only has up to 2 decimals, show 2 decimals. If more, show up to 10.
      return num.toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 10
      });
    }, 
    onDuplicate(item) {
      this.$emit('duplicate-row', item);
    },    
    formatDateForSql(val) {
      if (!val) return null;
      const d = typeof val === 'string' ? new Date(val) : val;
      const yyyy = d.getFullYear();
      const mm = String(d.getMonth() + 1).padStart(2, '0');
      const dd = String(d.getDate()).padStart(2, '0');
      return `${yyyy}-${mm}-${dd}`;
    },
    capitalizeStatus(status) {
      if (!status) return '';
      return status.charAt(0).toUpperCase() + status.slice(1);
    },
    chipStyle(status) {
      return {
        background:
          status === 'active' ? '#e6f5e9'
            : status === 'pending' ? '#e8e7f7'
            : status === 'expired' ? '#ffe4c0'
            : '#FBDFE1',
        color:
          status === 'active' ? '#61c378'
            : status === 'pending' ? '#4537de'
            : status === 'expired' ? '#d58716'
            : '#E7404C',
        borderRadius: '5px'
      };
    },
    formatTimeOnly(dateStr) {
      const d = new Date(dateStr);
      let h = d.getHours() % 12 || 12;
      const m = String(d.getMinutes()).padStart(2, '0');
      const ampm = d.getHours() >= 12 ? 'pm' : 'am';
      return `${h}:${m} ${ampm}`;
    },
    // formatDateDateOnly(dateStr) {
    //   if (!dateStr) return '';
    //   const d = new Date(dateStr);
    //   const pad = (n) => String(n).padStart(2, '0');
    //   return `${pad(d.getDate())}/${pad(d.getMonth() + 1)}/${d.getFullYear()}`;
    // },
    formatAction(action) {
      switch (action) {
        case 'create':
          return 'Created';
        case 'archive':
          return 'Archived';
        case 'activate':
          return 'Activated';
        case 'update':
          return 'Updated';
        default:
          return action;
      }
    },
    isSorted(column) {
      return this.sortBy[0]?.key === column.key;
    },
    toggleSort(column) {
      if (this.sortBy[0]?.key === column.key) {
        this.sortBy[0].order = this.sortBy[0].order === 'asc' ? 'desc' : 'asc';
      } else {
        this.sortBy = [{ key: column.key, order: 'asc' }];
      }
      this.loadItems();
    },
    onOptionsUpdate(options) {
      this.pagination.page = options.page;
      this.pagination.per_page =
        options.itemsPerPage || this.pagination.per_page;
      if (options.sortBy?.length) {
        this.sortBy = options.sortBy;
      }
      this.loadItems();
    },
    loadItems: _.debounce(async function () {
      const years = await payRangeApi.fetchPayRangeYears();
      this.yearOptions = years.map(y => ({ label: y, value: y }));    
        
      const params = {
        page: this.pagination.page,
        per_page: this.pagination.per_page,
        search: this.localSearch,
        status: this.localSelectedStatus,
        classification_id: this.localSelectedClassification,
        sort_by: this.sortBy[0]?.key,
        sort_direction: this.sortBy[0]?.order,
        min_range: this.localPayRangeMin,
        max_range: this.localPayRangeMax,       
        year: this.localSelectedYear,
      };

      const res = await payRangeApi.fetchPaginatedPayRanges(params);
      this.payRanges = res.data || [];
      this.pagination.total = res.total || 0;
    }, 300),
    async updateStatus(id, payload = {}) {

      // payload must have status as string, NOT object
      await payRangeApi.updatePayRangeStatus(id, payload);
      this.loadItems();
    },
    async viewHistory(id) {
      try {
        const response = await payRangeApi.getPayRangeHistory(id);
        this.selectedHistory = response.data || [];
        this.historyDialog = true;
      } catch (e) {
        this.$toast.error('Failed to load history.');
      }
    },

    // --- KEBAB MENU METHODS ---
    onDeactivate(item) {
      this.actionDialog = {
        open: true,
        type: 'setExpiryDate',
        title: 'Deactivate Pay Range',
        item,
        form: { date: null }
      };
    },
    onReactivate(item) {
      this.actionDialog = {
        open: true,
        type: 'setStartDate',
        title: 'Reactivate Pay Range',
        item,
        form: { date: null }
      };
    },
    closeActionDialog() {
      this.actionDialog.open = false;
      this.actionDialog.type = null;
      this.actionDialog.item = null;
      this.actionDialog.form = {};
    },
    async submitActionDialog() {
      const { type, item, form } = this.actionDialog;

      if (type === 'setExpiryDate') {
        const expiryDate = this.formatDateForSql(form.date);
        await this.updateStatus(item.id, { status: 'inactive', end_date: expiryDate });
        this.$toast.success('Pay range deactivated.');
        this.loadItems();
      }
      if (type === 'setStartDate') {
        const startDate = this.formatDateForSql(form.date);
        let newStatus = 'active';
        if (['expired', 'inactive'].includes(item.status)) {
          newStatus = 'active';
        }
        await this.updateStatus(item.id, { status: newStatus, start_date: startDate });
        this.$toast.success('Pay range reactivated.');
        this.loadItems();
      }
      this.closeActionDialog();
    }
    // --- END KEBAB MENU ---
  },
  async mounted() {
    // this.localSelectedYear = years.length ? years[years.length - 1] : null;    
    this.loadItems();
  }
};
</script>

<style scoped>
.payrange-table thead th:last-child {
  width: 60px !important;
}
.payrange-table thead th:nth-last-child(2) {
  width: 120px !important;
}
th b {
  font-size: medium !important;
  font-family: Oswald;
  color: #333954 !important;
  font-weight: 400;
}
.custom-header {
  color: #333954 !important;
  font-family: Oswald !important;
  font-size: large;
  font-weight: 400 !important;
}
.align-left {
  text-align: left !important;
}
.align-center {
  text-align: center !important;
}
.align-right {
  text-align: right !important;
}
.custom-width {
  width: 50px !important;
}
:deep(.v-card-title) {
  font-weight: 900 !important;
}
th.custom-header:last-child { width: 20px !important; }
:deep(.text-primary) {
  color: #001871 !important;
}
</style>
