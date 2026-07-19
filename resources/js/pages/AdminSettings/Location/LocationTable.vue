<template>
    <!-- History Modal -->
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
                v-for="(entry, index) in selectedLocation"
                :key="entry.id"
                :class="index % 2 === 0 ? 'tw-bg-gray-100' : ''"
                class="tw-text-sm tw-text-gray-900"
              >
                <td class="tw-py-3">
                  <div>{{ formatDateDateOnly(entry.created_at) }}</div>
                  <div class="tw-text-xs tw-text-gray-500">{{ formatTimeOnly(entry.created_at) }}</div>
                </td>
                <td class="tw-py-3">
                  {{ entry.user.employee.first_name }} {{ entry.user.employee.last_name }}
                </td>
                <td class="tw-py-3">{{ formatAction(entry.action) }}</td>
                <td class="tw-py-3">{{ entry.activity.replace(/\.$/, '') }}</td>
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
  
    <div class="tw-flex tw-py-2 tw-justify-between tw-mb-4">
      <div>
        <AppSelect
          v-model="localPerPage"
          placeholder="Per Page"
          :items="perPageOptions"
          item-title="label"
          item-value="value"
        />
      </div>
      <div class="tw-flex">
        <div style="width: 260px" class="tw-mr-4">
          <VTextField
            v-model="localSearch"
            placeholder="Search location"
            prepend-inner-icon="tabler-search"
            clearable
          />
        </div>
        <div style="width: 200px" class="tw-mr-4">
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
          <!-- Division filter -->
          <AppSelect
            v-model="localSelectedLocation"
            placeholder="Select Location"
            item-title="name"
            item-value="id"
            :items="locationsForFilter"
            clearable
          />          
        </div>
      </div>
    </div>
    <div class="tw-bg-white tw-shadow">
      <VDataTableServer
        :headers="headers"
        :items="locations"
        :items-length="pagination.total"
        :items-per-page="pagination.per_page"
        :page="pagination.page"
        :sort-by.sync="sortBy"
        @update:options="onOptionsUpdate"
        item-value="id"
        must-sort
        style="color: #333954 !important; font-family: Arial, Helvetica, sans-serif !important; font-size: medium !important;"
      >

        <template #headers="{ headers }">
          <tr>
              <template
                v-for="column in headers"
                :key="column.id"
              >
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
                      <VIcon
                        v-if="sortBy[0].order === 'desc'"
                        style="font-size: medium;"
                      >
                        tabler-chevron-down
                      </VIcon>
                      <VIcon
                        v-else
                        style="font-size: medium;"
                      >
                        tabler-chevron-up
                      </VIcon>
                    </span>

                    <VIcon
                      v-else
                      style="font-size: medium;"
                    >
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
        <template #item.name="{ item }">
          <div>{{ item.name }}</div>
        </template>
  
        <template #item.division.name="{ item }">
          <div>{{item.divisions && item.divisions.length ? formatDivisionNames(item.divisions) : "All"}}</div>
        </template>

        <template #item.valid_from="{ item }">
          <div>{{ formatDateDateOnly(item.valid_from) }}</div>
        </template>        
  
        <template #item.is_active="{ item }">
          <div class="tw-w-[80px]">
            <VChip
              label
              size="default"
              :style="{
                background: item.is_active ? '#e6f5e9' : '#FBDFE1',
                color: item.is_active ? '#61c378' : '#E7404C',
                borderRadius: '5px',
              }"
            >
              <span class="font-weight-bold">{{ item.is_active ? 'Active' : 'Inactive' }}</span>
            </VChip>
          </div>
        </template>
  
        <template #item.action="{ item }">
          <div class="tw-w-[80px]">
            <IconBtn>
              <VIcon icon="tabler-dots-vertical" />
              <VMenu activator="parent">
                <VList>
                  <VListItem
                    v-if="item.is_active"
                    class="tw-cursor-pointer hover:tw-bg-red-100"
                    @click="updateStatus(item.id, 0)"
                  >Archive</VListItem>
                  <VListItem
                    v-if="!item.is_active"
                    class="tw-cursor-pointer hover:tw-bg-red-100"
                    @click="updateStatus(item.id, 1)"
                  >Activate</VListItem>
                  <VListItem
                    class="tw-cursor-pointer hover:tw-bg-red-100"
                    @click="viewHistory(item.id)"
                  >View History</VListItem>
                </VList>
              </VMenu>
            </IconBtn>
          </div>
        </template>
  
        <template #bottom>
          <VDivider />
          <div class="d-flex align-center justify-sm-space-between justify-center flex-wrap gap-3 pa-5 pt-3">
            <p class="text-sm text-disabled mb-0" style="color: #999 !important;">
              {{ generateRangeText() }}
            </p>
  
            <VPagination
              v-if="pagination.per_page !== -1 && pagination.total > pagination.per_page"
              :model-value="pagination.page"
              @update:model-value="val => onOptionsUpdate({ ...pagination, page: val })"
              :total-visible="6"
              :length="Math.ceil(pagination.total / pagination.per_page)"
            >
              <template #prev="slotProps">
                <VBtn variant="tonal" color="default" v-bind="slotProps" :icon="false">
                  <VIcon icon="tabler-chevron-left" />
                </VBtn>
              </template>
  
              <template #next="slotProps">
                <VBtn variant="tonal" color="default" v-bind="slotProps" :icon="false">
                  <VIcon icon="tabler-chevron-right" />
                </VBtn>
              </template>
            </VPagination>
          </div>
        </template>
      </VDataTableServer>
    </div>
  </template>
  
<script>
import LocationApi from '@/api/location/locationApi'
import dayjs from 'dayjs'
import timezone from 'dayjs/plugin/timezone'
import utc from 'dayjs/plugin/utc'
import _ from 'lodash'
import { VDataTableServer } from 'vuetify/labs/VDataTable'

dayjs.extend(utc)
dayjs.extend(timezone)

export default {
  components: { VDataTableServer },
  props: {
    search: String,
    perPage: Number,
    selectedStatus: [String, Number],
    selectedLocation: [String, Number]
  },
  data() {
    return {
      historyDialog: false,
      selectedLocation: [],
      localSearch: this.search || '',
      localPerPage: this.perPage || 10,
      localSelectedStatus: this.selectedStatus || null,
      localSelectedLocation: this.selectedLocation || null,
      locations: [],
      locationsForFilter: [],
      headers: [
        { id: 1, title: 'Location', key: 'name', align: 'left', sortable: true },
        { id: 2, title: 'Division', key: 'division.name', align: 'left', sortable: false },
        { id: 3, title: 'Active From', key: 'valid_from', align: 'left', sortable: true },
        { id: 4, title: 'Status', key: 'is_active', align: 'left', sortable: true },
        { id: 5, title: '', align: 'left', key: 'action', sortable: false },
      ],
      pagination: {
        page: 1,
        per_page: this.perPage || 10,
        total: 0
      },
      sortBy: [{ key: 'name', order: 'asc' }],
      statusOptions: [
        { label: 'Active', value: 1 },
        { label: 'Inactive', value: 0 }
      ],
      perPageOptions: [
        { label: '10', value: 10 },
        { label: '25', value: 25 },
        { label: '50', value: 50 },
        { label: '100', value: 100 },
        { label: 'All', value: -1 }
      ]
    }
  },
  mounted() {
    this.loadLocationsForFilter()
    this.loadItems()
  },
  methods: {
    loadFromSearch: _.debounce(function () {
      this.loadItems()
    }, 600),

    isSorted(item) {
      // Check if this column is being sorted
      return this.sortBy[0]?.key === item.key
    },

    toggleSort(column) {
      // Toggle the sorting direction when the same column is clicked again
      if (this.sortBy[0]?.key === column.key) {
        // Toggle between ascending and descending
        this.sortBy[0].order = this.sortBy[0].order === 'asc' ? 'desc' : 'asc'
      } else {
        // If the column is different, set it as the new sort column, and default to ascending
        this.sortBy = [{ key: column.key, order: 'asc' }]
      }

      // Reload items with updated sorting
      this.loadItems()
    },

    formatDivisionNames(divisions) {
      if (!Array.isArray(divisions) || divisions.length === 0) {
        return ''
      }
      return divisions.map(d => d.name).join(', ')
    },

    async loadLocationsForFilter() {
      this.locationsForFilter = await LocationApi.fetchLocations()
    },

    formatTimeOnly(dateStr) {
      const d = new Date(dateStr)
      let hours = d.getHours()
      const minutes = String(d.getMinutes()).padStart(2, '0')
      const ampm = hours >= 12 ? 'pm' : 'am'
      hours = hours % 12
      hours = hours ? hours : 12
      return `${hours}:${minutes} ${ampm}`
    },
    formatDateDateOnly(dateStr) {
      if (!dateStr) return '-'
      return dayjs(dateStr).tz('Australia/Sydney').format('DD-MM-YYYY')
    },
    formatAction(action) {
      switch (action) {
        case 'create': return 'Created'
        case 'archive': return 'Archived'
        case 'activate': return 'Activated'
        case 'update': return 'Name Change'
        default: return action
      }
    },
    async onOptionsUpdate(options) {
      this.pagination.page = options.page
      this.pagination.per_page = options.itemsPerPage ?? this.pagination.per_page
      if (options.sortBy?.length > 0) {
        this.sortBy = options.sortBy
      }
      await this.loadItems()
    },
    async loadItems() {
      const response = await LocationApi.fetchPaginatedLocations({
        page: this.pagination.page,
        per_page: this.pagination.per_page,
        search: this.localSearch,
        is_active: this.localSelectedStatus,
        location_id: this.localSelectedLocation, // <- passed here
        sort_by: this.sortBy[0]?.key,
        sort_direction: this.sortBy[0]?.order
      })
      this.locations = response.data || []

      this.pagination.total = response.total || 0
    },
    async updateStatus(id, isActive) {
      await LocationApi.updateLocationStatus(id, isActive)
      this.loadItems()
    },
    async viewHistory(id) {
      try {
        const res = await LocationApi.getLocationHistory(id)
        this.selectedLocation = res.data || []
        this.historyDialog = true
      } catch (e) {
        this.$toast.error('Failed to load history.')
      }
    },
    generateRangeText() {
      const start = (this.pagination.page - 1) * this.pagination.per_page + 1
      let end = this.pagination.page * this.pagination.per_page
      if (end > this.pagination.total) end = this.pagination.total
      return this.pagination.total
        ? `Showing ${start} to ${end} of ${this.pagination.total} entries`
        : 'No entries'
    }
  },
  watch: {
    localSearch() {
      this.loadFromSearch()
    },
    localPerPage(val) {
      this.pagination.per_page = val
      this.loadItems()
    },
    localSelectedStatus() {
      this.loadItems()
    },
    localSelectedLocation() {
      this.loadItems()
    }
  }
}
</script>

<style scoped>
.custom-header {
  color: #333954 !important;
  font-family: Oswald !important;
  font-size: large;
  font-weight: 400 !important;
}

.app-select {
  color: #808494;
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
  width: 50px !important; /* Use !important to ensure it overrides other styles */
}
:deep(.v-card-title) {
  font-weight: 900 !important;
}
th.custom-header:last-child { width: 20px !important; }
:deep(.text-primary) {
  color: #001871 !important;
}
</style>
