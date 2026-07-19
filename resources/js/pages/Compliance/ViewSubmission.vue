<script setup>
import complianceApi from "@/api/compliance/compliance";
import { useComplianceStore } from "@/store/complianceStore";
import { VDataTableServer } from 'vuetify/labs/VDataTable';
import ReviewSubmission from './ReviewSubmission.vue';
import Submit from './Submit.vue';
import Main from './index.vue';
import moment from "moment/moment"
import EditMySubmission from "@/pages/Compliance/EditMySubmission.vue"
import usersApi from "@/api/user/user"

const complianceStore = useComplianceStore()

const items = ref([])
const complianceTotal = ref(0)
const complianceData = ref({})
const loadItems = ref(false)
const isAddDialogVisible = ref(false)
// const isAddDrawerVisible = ref(false)
const isSubmitDialogVisible = ref(false)
const isViewSubmissionVisible = ref(true)
const searchQuery = ref('')
const itemsPerPage = ref(50)
// const page = ref(1)
var sortBy = ref(null)
const orderBy = ref()
const isEditMySubmission = ref(false)
const editMySubmissionData = ref([])
const assignees = ref([])

//pagination page length dropdown indicator
const itemsPerPageOptions = ref([
    { value: 50, title: '50' },
    { value: 100, title: '100' },
    { value: 200, title: '200' },
    { value: 500, title: 'All' },
])

var sortDesc = ref(false)
const query = reactive({
    page: 1,
    type: [],
    status: [],
    assignee: [],
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

      let aValue = getNestedValue(a, sortBy.value)
      let bValue = getNestedValue(b, sortBy.value)

    if (aValue === undefined || aValue === null || aValue === '') {
      return sortDesc.value ? 1 : -1
    }
    if (bValue === undefined || bValue === null || bValue === '') {
      return sortDesc.value ? -1 : 1
    }

    if (typeof aValue === 'string') aValue = aValue.toLowerCase()
    if (typeof bValue === 'string') bValue = bValue.toLowerCase()

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

const getNestedValue = (obj, path) => {
    return path.split('.').reduce((acc, part) => {
        const match = part.match(/(\w+)\[(\d+)\]/)

        if (match) {
            const arrayPart = match[1]
            const index = parseInt(match[2], 10)

            return acc && acc[arrayPart] && acc[arrayPart][index] !== undefined
                ? acc[arrayPart][index]
                : undefined
        } else {
            return acc && acc[part] !== undefined ? acc[part] : undefined
        }
    }, obj)
}


const headers = [
  {

    title: 'Employee Name',
    key: 'user.employee.full_name',
    align: 'left',
    sortable: true,
  },
  {
    title: 'Type',
    key: 'compliance_model.name',
    align: 'left',
    sortable: true,
    class: 'wrap-cell',
  },
  {
    title: 'Expiry',
    key: 'expiry_date',
    align: 'left',
    sortable: true,
  },
  {
    title: 'Uploaded',
    key: 'status',
    align: 'left',
    sortable: true,
  },
  {
    title: 'Status',
    key: 'file_status',
    class: 'font-weight-bold',
    align: 'center',
    sortable: true,
  },
  {
    title: 'Comment',
    key: 'comment',
    class: 'font-weight-bold',
    align: 'left',
    sortable: true,
  },
  {
    title: 'Assigned To',
    key: 'assignee.username',
    class: 'font-weight-bold',
    align: 'left',
    sortable: true,
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
            return '#FBD2FF'
        default:
            return '#FBDFE1'
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
            return '#947C96'
        default:
            return '#E63946'
    }
}

onMounted( async () => {
  await complianceStore.setCompliances();
})

const compliance = async () => {
  try {
    loadItems.value = true
    const response = await complianceApi.fetch_employee_compliance_list({ ...query,  itemsPerPage: itemsPerPage.value })
      items.value = response.data || []
      console.log("======================COMPLIANCE========================")
      console.log(items.value)
    complianceTotal.value = response.total
    loadItems.value = false
  } catch (error) {
    console.error(error)
  }
}
compliance()

watch(itemsPerPage, () => {
    query.page = 1
    compliance()
})


watch(
  () => query.type,
  (first, second) => {
    compliance()
  },
)

watch(
  () => query.status,
  (first, second) => {
    compliance()
  },
)

watch(
  () => query.assignee,
  (first, second) => {
    compliance()
  },
)
watch(
  () => query.page,
  (first, second) => {
    compliance()
  },
)

const rowClick = (e, row)=>{
  if(e.target.nodeName != "svg"){
    complianceData.value = row.item
      console.log(complianceData.value);
    isAddDialogVisible.value = true
  }
}

const dateFormat = (date) => {
    var dt = moment(new Date(date))
    return dt.format('DD-MM-YYYY');
}

const refetch = () => {
  compliance()
}

const passEditData = (valCompData) => {
    isEditMySubmission.value = true
    editMySubmissionData.value = valCompData
}

const usersAssignee = async () => {
    try {
        loadItems.value = true
        const role_id = 5
        const response = await usersApi.get_users()
        assignees.value = response.map(user => user.username)
    } catch (error) {
        console.error(error)
    } finally {
        loadItems.value = false
    }
}

onMounted(() => {
    usersAssignee()
})

/*watch(() => props.complianceData, newVal => {
    localCompliance.value = { ...newVal }
})*/
</script>

<template>
  <div v-if="isViewSubmissionVisible">
    <p class="text-h2 font-weight-bold mb-5">Compliance</p>
    <v-card>
      <VCardText class="d-flex flex-wrap gap-4">
        <VRow>
            <VSpacer/>
            <VCol cols="6">
                <div class="d-flex align-center flex-wrap gap-4">
                    <VSpacer/>
                    <v-btn
                        style="background-color: #E35205"
                        color="white"
                        variant="outlined"
                        class="text-none"
                        @click="$emit('close')"
                    >
                    View Compliance List
                    </v-btn>

                     <v-btn
                        
                        color="primary"
                        class="text-none"
                        @click="isSubmitDialogVisible = true"
                    >
                    Submit Compliance
                    </v-btn> 
                </div>
            </VCol>
            <Vcol cols="12" class="ml-3 mr-3">
                <div class="text-subtitle-1 text-medium-emphasis">Search Filter</div>
                <div class="d-flex align-center flex-wrap gap-4">
                <AppSelect
                        placeholder="By Type"
                        density="compact"
                        class="search-dropdown"
                        v-model="query.type"
                        :items="complianceStore.data.compliances"
                        item-title="name"
                        item-value="name"
                        clearable
                    />
                    <AppSelect
                        placeholder="By Status"
                        density="compact"
                        class="search-dropdown"
                        v-model="query.status"
                        :items="['Current','Expired','Pending review','Required','Applied']"
                        clearable
                    />
                    <AppSelect
                        placeholder="By Assignee"
                        density="compact"
                        class="search-dropdown"
                        v-model="query.assignee"
                        :items="assignees"
                        clearable
                    />
                </div>

            </Vcol>

        </VRow>

          <VRow>
              <VCol>
                  <div class="app-select me-3 d-flex gap-3">
                      <AppSelect
                          :model-value="itemsPerPage"
                          :items="itemsPerPageOptions"
                          style="inline-size: 6.25rem;"
                          @update:model-value="itemsPerPage = parseInt($event)"
                      />
                  </div>
              </VCol>
          </VRow>
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
          @click:row="rowClick"
          @update:options="updateOptions"
        >
        <template #headers="{ headers }">
            <tr>
              <template
                v-for="column in headers"
                :key="column.id"
              >
                <th
                    style="padding: 0px 10px"
                    v-for="item in column"
                    :key="item.title"
                    :class="`custom-header align-${item.align}`"
                >
                  <span
                    v-if="item.sortable"
                    style="display: inline-flex; align-items: center;"
                    @click="toggleSort(item)"
                  >
                    <span style="white-space: nowrap; " :style="{ marginLeft: item.key === 'employee' ? '0px' : '0px' }">{{ item.title }}</span>
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

        <template #item.user.employee.full_name="{ item }">
          <span class="font-weight-medium ml-1">
            {{ item.user.employee.full_name }}
          </span>
        </template>

        <template #item.compliance_model.name="{ item }">
          <span
              style="
                display: inline-block;
                max-width: 380px;
                white-space: normal;
                word-break: break-word;
          ">
              {{ item.compliance_model?.name || '' }}
          </span>
        </template>

        <template #item.expiry_date="{ item }">
          <span
              class="font-weight-regular"
              style="display: inline-block; min-width: 90px;"
          >
              {{ item.expiry_date ? dateFormat(item.expiry_date) : '' }}</span>
        </template>

        <template #item.status="{ item }">
          <span class="font-weight-regular">
            {{ item.status === '1' ? 'Yes' : 'No' }}
          </span>
        </template>

        <template #item.file_status="{ item }">
          <VChip
            label
            size="default"
            :style="{
              background: getBackgroundColor(item),
              color: getTextColor(item),
            }"
          >
            <span class="font-weight-bold">{{ item.file_status || '' }}</span>
          </VChip>
        </template>

        <template #item.comment="{ item }">
          <span style="color:#CFCFCF">{{ item.comments || '' }}</span>
        </template>

        <template #item.assignee.username="{ item }">
            <span v-if="item.assignee_user_id === -1">HR</span>
            <span v-else class="font-weight-regular">{{ item.assignee?.username}}</span>
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
      </VDataTableServer>
    </v-card>

    <!-- <Submit
      v-model:isDrawerOpen="isAddDrawerVisible"
    /> -->
    <Submit
      v-model:isDrawerOpen="isSubmitDialogVisible"
      @refetch="refetch"
    />

    <ReviewSubmission
      v-model:isDialogOpen="isAddDialogVisible"
      :complianceData="complianceData"
      @pass-edit-data="passEditData"
      @refetch="refetch"
    />

      <EditMySubmission
          v-model:isDrawerOpen="isEditMySubmission"
          :complianceData="editMySubmissionData"
          @refetch="refetch"
      />
  </div>
  <div v-else>
    <Main/>
  </div>
</template>
<style scoped>
.search-dropdown {
    flex: 1;
    width: 27.3rem;
}
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

:deep(.wrap-cell) {
    white-space: normal !important;
    word-break: break-word !important;
    max-width: 280px;
}
</style>
