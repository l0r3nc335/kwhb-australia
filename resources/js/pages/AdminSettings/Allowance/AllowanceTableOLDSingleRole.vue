<template>
  <!-- History Dialog -->
  <VDialog v-model="historyDialog" max-width="800" persistent scrollable>
    <VCard class="tw-rounded-xl tw-pb-2">
      <VCardTitle class="text-center tw-font-bold tw-text-2xl tw-pt-6 tw-pb-2">History</VCardTitle>
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
            <tr v-for="(entry, index) in selectedHistory" :key="entry.id" :class="index % 2 === 0 ? 'tw-bg-gray-100' : ''" class="tw-text-sm tw-text-gray-900">
              <td class="tw-py-3">
                <div>{{ formatDateDateOnly(entry.created_at) }}</div>
                <div class="tw-text-xs tw-text-gray-500">{{ formatTimeOnly(entry.created_at) }}</div>
              </td>
              <td class="tw-py-3">{{ entry.user.employee.first_name }} {{ entry.user.employee.last_name }}</td>
              <td class="tw-py-3">{{ formatAction(entry.action) }}</td>
              <td class="tw-py-3">{{ entry.activity }}</td>
            </tr>
          </tbody>
        </VTable>
      </VCardText>
      <VCardActions class="justify-center pt-4">
        <VBtn @click="historyDialog = false" class="tw-font-medium tw-px-6 tw-rounded" height="38" style="background-color: #e45205; color: #fff !important;">Exit</VBtn>
      </VCardActions>
    </VCard>
  </VDialog>

  <!-- Edit Allowance Drawer -->
  <VNavigationDrawer
    v-model="showEditDrawer"
    location="right"
    temporary
    width="450"
    class="tw-rounded-l-2xl tw-overflow-visible"
  >
    <VCard flat>
      <VCardTitle class="tw-bg-orange-500 tw-text-white tw-font-bold tw-py-2 tw-px-4">
        Edit Allowance
      </VCardTitle>

      <div class="tw-flex tw-flex-col tw-px-4">
        <!-- Name -->
        <div class="tw-py-1 tw-mb-2">
          <AppTextField
            v-model="editForm.name"
            label="Allowance Name"
            placeholder="Enter name"
            :rules="[requiredValidator]"
          />
        </div>
        <!-- Description -->
        <div class="tw-py-1 tw-mb-2">
          <AppTextField
            v-model="editForm.description"
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
            v-model="editForm.position_id"
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
            v-model="editForm.value"
            label="Value"
            placeholder="e.g. 2000"
            :rules="[requiredValidator]"
          />
        </div>
        <!-- Frequency -->
        <div class="tw-py-1 tw-mb-2">
          <AppSelect
            v-model="editForm.frequency"
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
            v-model="editForm.start_date"
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
            v-model="editForm.end_date"
            auto-apply
            format="dd/MM/yyyy"
            :enable-time-picker="false"
            teleport="body"
          />
        </div>
        <!-- Buttons -->
        <div class="tw-flex tw-justify-end tw-py-3">
          <VBtn class="tw-mr-2" color="secondary" @click="resetEditForm">Cancel</VBtn>
          <VBtn color="success" :disabled="!canSaveEdit" @click="updateRecord">Save</VBtn>
        </div>
      </div>
    </VCard>
  </VNavigationDrawer>

  <!-- Table Filters -->
  <div class="tw-flex tw-py-2 tw-justify-between tw-mb-4">
    <div>
      <AppSelect v-model="localPerPage" placeholder="Per Page" :items="perPageOptions" item-title="label" item-value="value" />
    </div>
    <div class="tw-flex">
      <div style="width: 260px" class="tw-mr-4">
        <VTextField v-model="localSearch" placeholder="Search allowance" prepend-inner-icon="tabler-search" clearable />
      </div>
      <div style="width: 200px" class="tw-mr-4">
        <!-- Status filter -->
        <AppSelect
          v-model="localSelectedStatus"
          placeholder="Select Status"
          :items="statusOptions"
          item-title="label"
          item-value="value"
          clearable
        />
      </div>
      <div style="width: 200px">
        <AppSelect v-model="localSelectedPosition" placeholder="Select Position" item-title="name" item-value="id" :items="positions" clearable />
      </div>
    </div>
  </div>

  <!-- Table Display -->
  <VDataTableServer
    :headers="headers"
    :items="allowances"
    :items-length="pagination.total"
    :items-per-page="pagination.per_page"
    :page="pagination.page"
    :sort-by.sync="sortBy"
    must-sort
    @update:options="onOptionsUpdate"
    item-value="id"
    class="tw-w-full"
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
                <span style="white-space: nowrap;">{{ item.title }}</span>
                <span v-if="isSorted(item)">
                  <VIcon v-if="sortBy[0].order === 'desc'" style="font-size: medium;">tabler-chevron-down</VIcon>
                  <VIcon v-else style="font-size: medium;">tabler-chevron-up</VIcon>
                </span>
                <VIcon v-else style="font-size: medium;">tabler-selector</VIcon>
              </span>
              <span v-else>
                {{ item.title }}
              </span>
            </th>
          </template>
      </tr>
    </template>
    <template #item.name="{ item }">
      <div>{{ item.name }}</div>
    </template>
    <template #item.position="{ item }">
      <div>{{ item.position?.name || '-' }}</div>
    </template>
      <template #item.positions="{ item }">
          <div>{{ item.positions }}</div>
      </template>
    <template #item.frequency="{ item }">
      <div>{{ item.frequency }}</div>
    </template>
    <template #item.value="{ item }">
      <div>${{ item.value }}</div>
    </template>
    <template #item.start_date="{ item }">
      <div>{{ formatDateDateOnly(item.start_date) }}</div>
    </template>
    <template #item.end_date="{ item }">
      <div>{{ formatDateDateOnly(item.end_date) }}</div>
    </template>
    <!-- STATUS CHIP COLUMN -->
    <template #item.status="{ item }">
      <div class="tw-w-[80px]">
        <VChip
          label
          size="default"
          :style="{
            background: item.status === 'active' ? '#e6f5e9' : '#FBDFE1',
            color: item.status === 'active' ? '#61c378' : '#E7404C',
            borderRadius: '5px',
          }"
        >
          <span class="font-weight-bold">{{ item.status === 'active' ? 'Active' : item.status === 'pending' ? 'Pending' : 'Inactive' }}</span>
        </VChip>
      </div>
    </template>
    <!-- ACTION MENU -->
    <template #item.action="{ item }">
      <div class="tw-w-[80px]">
        <IconBtn>
          <VIcon icon="tabler-dots-vertical" />
          <VMenu activator="parent">
            <VList>
              <VListItem
                v-if="item.status === 'active'"
                class="tw-cursor-pointer hover:tw-bg-red-100"
                @click="archiveAllowance(item.id)"
              >Archive</VListItem>
              <VListItem
                v-if="item.status === 'archive'"
                class="tw-cursor-pointer hover:tw-bg-green-100"
                @click="activateAllowance(item.id)"
              >Activate</VListItem>
              <VListItem
                class="tw-cursor-pointer hover:tw-bg-gray-100"
                @click="viewHistory(item.id)"
              >View History</VListItem>
              <VListItem
                class="tw-cursor-pointer hover:tw-bg-orange-100"
                @click="openEditDrawer(item)"
              >Edit</VListItem>
            </VList>
          </VMenu>
        </IconBtn>
      </div>
    </template>
    <template #bottom>
      <VDivider />
      <div class="d-flex align-center justify-sm-space-between justify-center flex-wrap gap-3 pa-5 pt-3">
        <p class="text-sm text-disabled mb-0">{{ generateRangeText() }}</p>
        <VPagination
          v-if="pagination.per_page !== -1 && pagination.total > pagination.per_page"
          :model-value="pagination.page"
          @update:model-value="val => onOptionsUpdate({ ...pagination, page: val })"
          :length="Math.ceil(pagination.total / pagination.per_page)"
        />
      </div>
    </template>
  </VDataTableServer>
</template>

<script>
import AllowanceApi from '@/api/allowance/allowanceApi'
import JobTitleApi from '@/api/jobTitle/jobTitleApi'
import VueDatePicker from '@vuepic/vue-datepicker'
import dayjs from 'dayjs'
import _ from 'lodash'
import { VDataTableServer } from 'vuetify/labs/VDataTable'

export default {
  components: { VDataTableServer, VueDatePicker },

  data() {
    return {
      historyDialog: false,
      showEditDrawer: false,
      editForm: {
        id: null,
        name: null,
        description: null,
        position_id: null,
        value: null,
        frequency: null,
        start_date: null,
        end_date: null
      },
      frequencies: ['Daily', 'Weekly', 'Monthly', 'Yearly'],
      selectedHistory: [],
      allowances: [],
      positions: [],
      localSearch: '',
      localPerPage: 10,
      localSelectedStatus: null,
      localSelectedPosition: null,
      pagination: { page: 1, per_page: 10, total: 0 },
      sortBy: [{ key: 'name', order: 'asc' }],
      statusOptions: [
        { label: 'Active', value: 'active' },
        { label: 'Archived', value: 'archive' }
      ],
      perPageOptions: [
        { label: '10', value: 10 },
        { label: '25', value: 25 },
        { label: '50', value: 50 },
        { label: '100', value: 100 },
        { label: 'All', value: -1 }
      ],
      headers: [
        { title: 'Allowance', key: 'name', sortable: true },
        { title: 'Position', key: 'position', sortable: false },
        { title: 'Positions', key: 'positions', sortable: false },
        { title: 'Frequency', key: 'frequency', sortable: true },
        { title: 'Value', key: 'value', sortable: true },
        { title: 'Start Date', key: 'start_date', sortable: true },
        { title: 'End Date', key: 'end_date', sortable: true },
        { title: 'Status', key: 'status', sortable: true },
        { title: '', key: 'action', sortable: false }
      ]
    }
  },

  computed: {
    canSaveEdit() {
      return (
        this.editForm.name &&
        this.editForm.position_id &&
        this.editForm.value &&
        this.editForm.frequency
      )
    }
  },

  mounted() {
    this.loadPositions()
    this.loadItems()
  },

  methods: {
    requiredValidator(v) { return !!v || 'Required'; },
    toggleSort(column) {
      if (this.sortBy[0]?.key === column.key) {
        this.sortBy[0].order = this.sortBy[0].order === 'asc' ? 'desc' : 'asc'
      } else {
        this.sortBy = [{ key: column.key, order: 'asc' }]
      }
      this.loadItems()
    },
    isSorted(item) {
      return this.sortBy[0]?.key === item.key
    },
    formatDateDateOnly(date) {
      if (!date) return '-'
      return dayjs(date).format('DD-MM-YYYY')
    },
    formatTimeOnly(dateStr) {
      const d = new Date(dateStr)
      const hours = d.getHours() % 12 || 12
      const minutes = String(d.getMinutes()).padStart(2, '0')
      const ampm = d.getHours() >= 12 ? 'pm' : 'am'
      return `${hours}:${minutes} ${ampm}`
    },
    formatAction(action) {
      return {
        create: 'Created',
        update: 'Updated',
        archive: 'Archived',
        activate: 'Activated'
      }[action] || action
    },
    async loadPositions() {
      const res = await JobTitleApi.fetchJobTitles()
      this.positions = res || []
    },
    async loadItems() {
      const response = await AllowanceApi.fetchPaginatedAllowances({
        page: this.pagination.page,
        per_page: this.pagination.per_page,
        search: this.localSearch,
        position_id: this.localSelectedPosition,
        status: this.localSelectedStatus,
        sort_by: this.sortBy[0]?.key,
        sort_direction: this.sortBy[0]?.order
      })
      this.allowances = response.data || []
      this.pagination.total = response.total || 0
    },
    async viewHistory(id) {
      const res = await AllowanceApi.getAllowanceHistory(id)
      this.selectedHistory = res.data || []
      this.historyDialog = true
    },
    // ARCHIVE
    async archiveAllowance(id) {
      try {
        await AllowanceApi.updateStatus(id, 'archive')
        this.loadItems()
        this.$toast?.success?.('Archived successfully!')
      } catch (e) {
        this.$toast?.error?.('Failed to archive.')
      }
    },
    // ACTIVATE
    async activateAllowance(id) {
      try {
        await AllowanceApi.updateStatus(id, 'active')
        this.loadItems()
        this.$toast?.success?.('Activated successfully!')
      } catch (e) {
        this.$toast?.error?.('Failed to activate.')
      }
    },
    // OPEN EDIT DRAWER (prefill)
    openEditDrawer(item) {
      this.editForm = {
        id: item.id,
        name: item.name,
        description: item.description,
        position_id: item.position_id || (item.position && item.position.id) || null,
        value: item.value,
        frequency: item.frequency,
        start_date: item.start_date ? new Date(item.start_date) : null,
        end_date: item.end_date ? new Date(item.end_date) : null,
      }
      this.showEditDrawer = true
    },
    // RESET EDIT FORM
    resetEditForm() {
      this.showEditDrawer = false
      this.editForm = {
        id: null,
        name: null,
        description: null,
        position_id: null,
        value: null,
        frequency: null,
        start_date: null,
        end_date: null
      }
    },
    // UPDATE RECORD
    async updateRecord() {
      const formatDate = date => {
        if (!date) return null
        const d = new Date(date)
        const pad = n => String(n).padStart(2, '0')
        return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`
      }
      const data = {
        name: this.editForm.name,
        description: this.editForm.description,
        position_id: this.editForm.position_id,
        value: this.editForm.value,
        frequency: this.editForm.frequency,
        start_date: formatDate(this.editForm.start_date),
        end_date: formatDate(this.editForm.end_date)
      }
      await AllowanceApi.updateAllowance(this.editForm.id, data)
      this.$toast?.success?.("Allowance updated successfully")
      this.resetEditForm()
      this.loadItems()
    },
    onOptionsUpdate(options) {
      this.pagination.page = options.page
      this.pagination.per_page = options.itemsPerPage ?? this.pagination.per_page
      this.sortBy = options.sortBy || this.sortBy
      this.loadItems()
    },
    generateRangeText() {
      const start = (this.pagination.page - 1) * this.pagination.per_page + 1
      let end = this.pagination.page * this.pagination.per_page
      if (end > this.pagination.total) end = this.pagination.total
      return this.pagination.total ? `Showing ${start} to ${end} of ${this.pagination.total} entries` : 'No entries'
    }
  },

  watch: {
    localSearch: _.debounce(function () {
      this.loadItems()
    }, 600),
    localPerPage(val) {
      this.pagination.per_page = val
      this.loadItems()
    },
    localSelectedStatus() {
      this.loadItems()
    },
    localSelectedPosition() {
      this.loadItems()
    }
  }
}
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
:deep(.custom-select-label label.v-label) {
  font-size: 13px !important;
}
</style>

