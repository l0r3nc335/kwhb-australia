<script setup>
import complianceApi from "@/api/compliance/compliance";
import toast from '@/composable/toast.js';
import { useComplianceStore } from "@/store/complianceStore";
import { VDataTableServer } from 'vuetify/labs/VDataTable';
import AddDrawer from './AddDrawer.vue';
import EditDrawer from './EditDrawer.vue';
import ViewSubmission from './ViewSubmission.vue';
import divisionApi from "@/api/division/divisionApi"
import DivisionApi from "@/api/division/divisionApi"

const complianceStore = useComplianceStore()

const itemselected = ref()
const loadingDelete = ref(false)
const isDialogDeleteVisible = ref(false)
const loadItems = ref(false)
const items = ref([])
const complianceTotal = ref(0)
const complianceData = ref({})
const isAddDrawerVisible = ref(false)
const isEditDrawerVisible = ref(false)
const isViewSubmissionVisible = ref(false)
const searchQuery = ref('')
const itemsPerPage = ref(50)
// const page = ref(1)
var sortBy = ref(null)
const orderBy = ref()

var sortDesc = ref(false)
const query = reactive({
    page: 1,
    type: [],
    division: [],
    role: [],
    requirement: [],
    validity: [],
})

//pagination page length dropdown indicator
const itemsPerPageOptions = ref([
    { value: 50, title: '50' },
    { value: 100, title: '100' },
    { value: 200, title: '200' },
    { value: 500, title: 'All' },
])

const divisions = ref([])

const loadDivisions = async () => {
    try {
        const res = await DivisionApi.fetchDivisions();
        const fetchedDivisions = Array.isArray(res.data)
            ? res.data.map(item => item.name)
            : Array.isArray(res)
                ? res.map(item => item.name)
                : [];

        // Prepend 'All' as the first item
        divisions.value = ['All', ...fetchedDivisions];

    } catch (error) {
        console.error('Failed to load divisions:', error)
    }
}

const updateOptions = options => {
  // page.value = options.page
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

const toggleSort = column => {
  if (sortBy.value === column.key) {
    sortDesc.value = !sortDesc.value
  } else {
    sortBy.value = column.key
    sortDesc.value = false
  }

  sortItems()
}


const isSorted = column => {
  return sortBy.value === column.key
}

const sortItems = () => {
  if (!sortBy.value) {
    return
  }
  items.value.sort((a, b) => {
      let key;

      switch (sortBy.value) {
          case 'type':
              key = 'name'
              break;
          case 'expiry':
              key = 'validity'
              break;
          case 'roles':
              key = 'role'
              break;
          case 'requirement':
              key = 'required'
              break;
          default:
              key = sortBy.value
      }


      let result = 0
      let aValue = a[key]
      let bValue = b[key]

    if (aValue === undefined || aValue === null || aValue === '') {
      return sortDesc.value ? 1 : -1
    }
    if (bValue === undefined || bValue === null || bValue === '') {
      return sortDesc.value ? -1 : 1
    }

    if (typeof aValue === 'string') {
      aValue = aValue.toLowerCase()
    }
    if (typeof bValue === 'string') {
      bValue = bValue.toLowerCase()
    }

    if (typeof aValue === 'number' && typeof bValue === 'number') {
      result = aValue - bValue
    } else {
      if (aValue < bValue) {
        result = -1
      } else if (aValue > bValue) {
        result = 1
      }
    }

    return sortDesc.value ? -result : result
  })
}

const roles = ref([
  'All',
  'Assets Coordinator',
  'General Maintenance Officer',
  'Chief Executive Officer',
  'Executive Assistant to the CEO',
  'General Manager Health Services',
  'Director Community Engagement'
])

const headers = [
{
    title: 'Type',
    key: 'type',
    align: 'left',
    sortable: true,
  },
  {
    title: 'Expiry',
    key: 'expiry',
    align: 'left',
    sortable: true,
  },
  {
    title: 'Roles',
    key: 'roles',
    class: 'font-weight-bold',
    align: 'left',
    sortable: true,
  },
  {
    title: 'Division',
    key: 'division',
    class: 'font-weight-bold',
    align: 'left',
    sortable: true,
  },
  {
    title: 'Requirement',
    key: 'requirement',
    class: 'font-weight-bold',
    align: 'left',
    sortable: true,
  },
  {
    title: 'Status',
    key: 'is_active',
    class: 'font-weight-bold',
    align: 'left',
    sortable: true,
  },
  {
    title: '',
    key: 'actions',
    align: 'center',
    sortable: false
  }
]

const generateRangeText = (pageNumber, totalNumberOfRows, rowsPerPage) => {
  const startIndex = (pageNumber - 1) * rowsPerPage + 1
  let endIndex = pageNumber * rowsPerPage
  if (endIndex > totalNumberOfRows) {
    endIndex = totalNumberOfRows
  }

  const rowsOnLastPage = totalNumberOfRows % rowsPerPage || rowsPerPage

  let rangeText
  if (totalNumberOfRows === 0) {
    rangeText = "No entries"
  } else if (pageNumber === 1 && endIndex === totalNumberOfRows) {
    rangeText = `Showing all of ${totalNumberOfRows} entries`
  } else if (pageNumber === 1) {
    rangeText = `Showing ${startIndex} to ${endIndex} of ${totalNumberOfRows} entries`
  } else if (endIndex === totalNumberOfRows) {
    if (rowsOnLastPage === 1) {
      rangeText = `Showing last entry of ${totalNumberOfRows} entries`
    } else {
      rangeText = `Showing ${startIndex} to ${endIndex} of ${totalNumberOfRows} entries`
    }
  } else {
    rangeText = `Showing ${startIndex} to ${endIndex} of ${totalNumberOfRows} entries`
  }

  return rangeText
}

watch(itemsPerPage, () => {
    query.page = 1
    compliance()
})

const compliance = async () => {
  try {
    loadItems.value = true
    const response = await complianceApi.fetch_compliance({ ...query, itemsPerPage: itemsPerPage.value  })
    items.value = response.data
    complianceTotal.value = response.total
    loadItems.value = false
  } catch (error) {
    console.error(error)
  }
}
compliance()

const refetch = () => {
  compliance()
}

onMounted( async () => {
  await complianceStore.setCompliances();
    loadDivisions()
})

watch(
  () => query.page,
  (first, second) => {
    compliance()
  },
)

watch(
  () => query.type,
  (first, second) => {
    compliance()
  },
)

watch(
  () => query.validity,
  (first, second) => {
    compliance()
  },
)

watch(
  () => query.division,
  (first, second) => {
    compliance()
  },
)

watch(
  () => query.requirement,
  (first, second) => {
    compliance()
  },
)

watch(
  () => query.role,
  (first, second) => {
    compliance()
  },
)

const editCompliance = (item) => {
  complianceData.value = item
  isEditDrawerVisible.value = true
}

const deleteDialog = (item) => {
  isDialogDeleteVisible.value = true
  itemselected.value = item.id
}

const deleteCompliance = async () => {
  loadingDelete.value = true
  const response = await complianceApi.delete_compliance({ id : itemselected.value })
  toast('success', response.message)
  loadingDelete.value = false
  itemselected.value = ''
  isDialogDeleteVisible.value = false
  await compliance()
}
</script>

<template>
  <div v-if="isViewSubmissionVisible">
    <p class="text-h2 font-weight-bold mb-5">Compliance</p>
    <v-card>
      <VCardText class="d-flex flex-wrap gap-4">
        <div class="d-flex align-center flex-wrap gap-4">
            <AppSelect
                placeholder="Type"
                density="compact"
                :items="complianceStore.data.compliances"
                item-title="name"
                item-value="name"
                style="inline-size: 8rem;"
                v-model="query.type"
                clearable
            />
            <AppSelect
                placeholder="Division"
                density="compact"
                :items="divisions"
                style="inline-size: 8rem;"
                v-model="query.division"
                clearable
            />
            <AppSelect
                placeholder="Role"
                density="compact"
                style="inline-size: 9rem;"
                :items="roles"
                v-model="query.role"
                clearable
            />
            <AppSelect
                placeholder="Requirement"
                density="compact"
                :items="['Mandatory','Optional']"
                style="inline-size: 9rem;"
                v-model="query.requirement"
                clearable
            />
            <AppSelect
                placeholder="Expiry"
                density="compact"
                :items="['Quarterly','Annual','Lifetime','Custom']"
                style="inline-size: 8rem;"
                v-model="query.validity"
                clearable
            />
        </div>
        <VSpacer />

        <div class="d-flex align-center flex-wrap gap-4">
          <v-btn
            style="background-color: #001871"
            color="#CFCFCF"
            variant="outlined"
            class="text-none"

            @click="isViewSubmissionVisible = false"
          >
          View Submissions
          </v-btn>

          <v-btn
            prepend-icon="tabler-plus"
            color="primary"
            @click="isAddDrawerVisible = true"
          >
          Create Compliance
          </v-btn>
        </div>
      </VCardText>

        <VCardText>
            <div class="app-select me-3 d-flex gap-3">
                <AppSelect
                    :model-value="itemsPerPage"
                    :items="itemsPerPageOptions"
                    style="inline-size: 6.25rem;"
                    @update:model-value="itemsPerPage = parseInt($event)"
                />
            </div>
        </VCardText>

      <VDivider />

      <VDataTableServer
          :loading="loadItems"
          :items-per-page="itemsPerPage"
          :page="query.page"
          :items="items"
          :items-length="complianceTotal"
          :headers="headers"
          :search="searchQuery"
          style="color: #333954 !important; font-family: Arial, Helvetica, sans-serif; font-size: medium;"
          @update:options="updateOptions"
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
                    <span style="white-space: nowrap;" :style="{ marginLeft: item.key === 'type' ? '5px' : '0px' }">{{ item.title }}</span>
                    <span v-if="isSorted(item)">
                      <VIcon
                        v-if="sortDesc"
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

        <template #item.type="{ item }">
          <span class="font-weight-regular ml-1">{{ item.name }}</span>
        </template>

        <template #item.expiry="{ item }">
          <span v-if="item.validity === 'Custom'" class="font-weight-regular">{{ moment(item.custom_expiry).format('DD-MM-YYYY') }}</span>
          <span v-else class="font-weight-regular">{{ item.validity }}</span>
        </template>

        <template #item.roles="{ item }">
            <span
                v-if="item.role?.split(',')?.length === (roles?.length -1 || 0)"
                class="font-weight-regular"
            >
                All Roles
            </span>
            <span v-else class="font-weight-regular">{{ (item.role ? item.role.split(',').join(', ') : '') }}</span>
        </template>

        <template #item.division="{ item }">
            <span
                v-if="item.division?.split(',')?.length === (divisions?.length -1 || 0)"
                class="font-weight-regular"
            >
                All Divisions
            </span>
            <span v-else class="font-weight-regular">{{ (item.division ? item.division.split(',').join(', ') : '') }}</span>
        </template>

        <template #item.requirement="{ item }">
          <span class="font-weight-regular">{{ item.required == '0' ? 'Mandatory' : 'Optional' }}</span>
        </template>

        <template #item.is_active="{ item }">
          <span class="font-weight-regular">{{ item.is_active == '1' ? 'Active' : 'Archive' }}</span>
        </template>

        <template #bottom>
            <VDivider />
            <div class="d-flex align-center justify-sm-space-between justify-center flex-wrap gap-3 pa-5 pt-3">
              <p
                class="text-sm text-disabled mb-0"
                style="color: #999 !important;"
              >
                {{ generateRangeText(query.page, complianceTotal, itemsPerPage) }}
              </p>

              <VPagination
                v-if="complianceTotal > itemsPerPage"
                v-model="query.page"
                :total-visible="6"
                :length="Math.ceil(complianceTotal / itemsPerPage)"
              >
                <template #prev="slotProps">
                  <VBtn
                    variant="tonal"
                    color="default"
                    v-bind="slotProps"
                    :icon="false"
                    class
                  >
                    <VIcon icon="tabler-chevron-left" />
                  </VBtn>
                </template>

                <template #next="slotProps">
                  <VBtn
                    variant="tonal"
                    color="default"
                    v-bind="slotProps"
                    :icon="false"
                  >
                  <VIcon icon="tabler-chevron-right" />
                  </VBtn>
                </template>
              </VPagination>
            </div>
        </template>

        <template #item.actions="{ item }">
          <div class="text-right">
            <v-dialog v-model="isDialogDeleteVisible" max-width="400">
              <v-card prepend-icon="mdi-warning" title="Confirm Archive" text="Are you sure you want to archive this item?">
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn :loading="loadingDelete" @click="deleteCompliance" variant="tonal" color="#CC4A04">Yes</v-btn>
                    <v-btn @click="isDialogDeleteVisible=false" color="#CC4A04">No</v-btn>
                </v-card-actions>
              </v-card>
            </v-dialog>
            <VMenu location="start">
              <template #activator="{ props }">
                  <IconBtn v-bind="props">
                      <VIcon
                          size="25"
                          color="#808494"
                          icon="mdi-dots-vertical"
                      />
                  </IconBtn>
              </template>
              <VList>
                <VListItem class="hand" style="color: #E35205" @click="editCompliance(item)">
                    <template #prepend>
                        <VIcon icon="tabler-edit" />
                    </template>
                    <VListItemTitle> Edit </VListItemTitle>
                </VListItem>
                <VListItem class="hand" style="color: red !important;"  @click="deleteDialog(item)">
                    <template #prepend>
                    <VIcon icon="tabler-archive"/>
                    </template>
                    <VListItemTitle> Archive </VListItemTitle>
                </VListItem>
              </VList>
            </VMenu>
          </div>
        </template>
      </VDataTableServer>
    </v-card>

    <AddDrawer
      v-model:isDrawerOpen="isAddDrawerVisible"
      @refetch="refetch"
    />

    <EditDrawer
      v-model:isDrawerOpen="isEditDrawerVisible"
      :complianceData="complianceData"
      @refetch="refetch"
    />
  </div>
  <div v-else>
    <ViewSubmission @close="isViewSubmissionVisible = true"/>
  </div>
</template>
<style scoped>
.custom-header {
  color: #333954 !important;
  font-family: Oswald !important;
  font-size:large;
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

:deep(.text-primary){
  color: #001871 !important;
}
</style>
