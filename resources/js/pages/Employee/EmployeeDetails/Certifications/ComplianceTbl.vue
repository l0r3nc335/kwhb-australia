<script setup>
import complianceApi from "@/api/compliance/compliance";
import toast from '@/composable/toast.js';
import { useEmployeeStore } from "@/store/employeeStore";
import moment from "moment";
import { VDataTableServer } from 'vuetify/labs/VDataTable';
// import Exemption from '../../../Vaccinations/Exemption.vue';
import EditCompliance from './EditCompliance.vue';
import Submit from './SubmitCompliance.vue';

const employeeStore = useEmployeeStore();

const itemselected = ref()
const loadingDelete = ref(false)
const isDialogDeleteVisible = ref(false)
const items = ref([])
const complianceTotal = ref(0)
const complianceData = ref({})
const loadItems = ref(false)
// const isAddDialogVisible = ref(false)
const isAddDrawerVisible = ref(false)
const isEditDrawerVisible = ref(false)
const searchQuery = ref('')
const itemsPerPage = ref(10)
// const page = ref(1)
var sortBy = ref(null)
const orderBy = ref()

var sortDesc = ref(false)
const query = reactive({
    page: 1
})

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
    let result = 0
    let aValue = a[sortBy.value]
    let bValue = b[sortBy.value]

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

const headers = [
{
    title: 'Type',
    key: 'compliance_model.name',
    align: 'left',
    sortable: true,
  },
  {
    title: 'Expiry Date',
    key: 'expiry_date',
    align: 'left',
    sortable: true,
  },
  {
    title: 'Upload',
    key: 'file',
    class: 'font-weight-bold',
    align: 'left',
    sortable: true,
    width: '400px'
  },
  {
    title: 'Status',
    key: 'status',
    class: 'font-weight-bold',
    align: 'center',
    sortable: true,
  },
  {
    title: 'Assigned To',
    key: 'assignee.username',
    class: 'font-weight-bold',
    align: 'center',
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

const getBackgroundColor = (item) => {
    switch (item.file_status) {
        case 'Current':
            return '#E6F5E9'
        case 'Expired':
            return '#FBDFE1'
        case 'Pending review':
            return '#FAE3D7'
        case 'Required':
            return '#E5EAFF'
        case 'Applied':
            return '#FDF3DE'
        case 'Exempt':
            return '#D6DAE8'
        default:
            return '#E6F5E9'
    }
}

const getTextColor = (item) => {
    switch (item.file_status) {
        case 'Current':
            return '#62C379'
        case 'Expired':
            return '#E63946'
        case 'Pending review':
            return '#E35205'
        case 'Required':
            return '#5C7FFF'
        case 'Applied':
            return '#F1B435'
        case 'Exempt':
            return '#001871'
        default:
            return '#62C379'
    }
}

const compliance = async () => {
  try {
    loadItems.value = true
    const response = await complianceApi.fetch_employee_compliance({ id: employeeStore.data.employee_selected.user_id, ...query })
    console.log("=================EMPLOYEE PROFILE COMPLIANCE=============")
    console.log(response.data)
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
  const response = await complianceApi.delete_employee_compliance({ id : itemselected.value })
  toast('success', response.message)
  loadingDelete.value = false
  itemselected.value = ''
  isDialogDeleteVisible.value = false
  compliance()
}

const dateFormat = (date) => {
    var dt = moment(new Date(date))
    return dt.format('DD-MM-YYYY');
  }

watch(
  () => query.page,
  (first, second) => {
    compliance()
  },
)
</script>

<template>
  <div>
    <v-card>
      <VDataTableServer
          :loading="loadItems"
          :items-per-page="itemsPerPage"
          :page="query.page"
          :items="items"
          :items-length="complianceTotal"
          :headers="headers"
          :search="searchQuery"
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
                    <span style="white-space: nowrap;">{{ item.title }}</span>
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

        <template #item.compliance_model.name="{ item }">
          <span class="font-weight-regular ml-1">{{ item.compliance_model?.name ?? item.compliance }}</span>
        </template>

        <template #item.expiry_date="{ item }">
          <span class="font-weight-regular">{{ item.expiry_date ? dateFormat(item.expiry_date) : '' }}</span>
        </template>

        <template #item.file="{ item }">
          <!-- <span class="font-weight-regular" style="color: #418E99;"><VIcon v-if="item.file" icon="tabler-square-check-filled" class="mb-1 mr-1" />{{ item.file ? 'Certificate' : '' }}</span> -->
         <!-- <span class="font-weight-regular" style="color: #418E99;">{{ item.file_name }}</span> -->
          <span v-if="item.file_name != null">
                <a :href="`/storage/attachments/compliance/` + item.file" target="_blank" class="tw-text-blue-400 hover:tw-text-blue-600">
                    <VIcon icon="tabler-paperclip" size="sm" class="tw-mr-1"></VIcon>
                    <span class="font-weight-regular" style="color: #6A8AFE;">{{ item.file_name}}</span>
                </a>
            </span>
        </template>

          <template #item.assignee.username="{ item }">
              <span v-if="item.assignee_user_id == '-1'" class="font-weight-regular">HR</span>
              <span v-else class="font-weight-regular">{{item.assignee.username}}</span>
              <!--span class="font-weight-regular">{{ item.file_status === 'Pending review' ? 'HR' : '' }}</span-->
          </template>

        <template #item.status="{ item }">
            <VChip
            label
            size="default"
            :style="{
                background: getBackgroundColor(item),
                color: getTextColor(item),
              }"
          >
            <span class="font-weight-bold">{{ item.file_status }}</span>
          </VChip>
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
                        <VIcon icon="tabler-edit"/>
                    </template>
                    <VListItemTitle> Edit Entry</VListItemTitle>
                </VListItem>
                <VListItem v-if="item.file_status != 'Required'" class="hand" style="color: red !important;"  @click="deleteDialog(item)">
                    <template #prepend>
                    <VIcon icon="tabler-archive"/>
                    </template>
                    <VListItemTitle> Archive Entry</VListItemTitle>
                </VListItem>
              </VList>
            </VMenu>
          </div>
        </template>
      </VDataTableServer>
    </v-card>

    <Submit
      v-model:isDrawerOpen="isAddDrawerVisible"
      @refetch="refetch"
    />

    <v-btn
        prepend-icon="tabler-plus"
        color="primary"
        @click="isAddDrawerVisible = true"
        class="float-right mt-5 ml-2"
    >
    Add New
    </v-btn>

    <!-- <v-btn
        color="#E35205"
        variant="tonal"
        @click="isAddDialogVisible = true"
        class="float-right mt-5 font-weight-bold"
    >
    Request Exemption
    </v-btn> -->

    <!-- <Exemption
      v-model:isDialogOpen="isAddDialogVisible"
    /> -->

    <EditCompliance
      v-model:isDrawerOpen="isEditDrawerVisible"
      :complianceData="complianceData"
      @refetch="refetch"
    />
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

:deep(.text-primary) {
  color: #001871 !important;
}
</style>
