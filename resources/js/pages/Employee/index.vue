


<template>
  <div class="page-content-container">
    <section>
      <div class="mb-6 font-weight-bold text-h2">
        <span style="color: #808494;">Employee / </span>
        <span style="color: #000829;">List</span>
      </div>
      <div class="d-flex mb-6">
        <VRow>
          <template
            v-for="(data, id) in employeeStore.data.widget"
            :key="id"
          >
            <VCol
              cols="12"
              md="3"
              sm="6"
            >
              <VCard style="height: 100%">
                <VCardText >
                  <div class="d-flex justify-space-between pa-1">
                    <div class="d-flex flex-column gap-y-1">
                      <span class="text-subtitle-1 text-medium-emphasis">{{ data.title }}</span>
                      <div>
                        <h4 class="text-h1" style="font-family: Arial, Helvetica, sans-serif !important; font-weight: bold;">
                          {{ data.value }}
                          <span
                            class="text-base "
                            style="font-family: Arial, Helvetica, sans-serif !important; font-weight: normal;"
                            :class="data.change > 0 ? 'text-success' : 'text-error'"
                          >({{ data.change > 0 ? '+' + data.change : (data.change == 0 ? data.change : '-' + data.change) }}%)</span>
                        </h4>
                      </div>
                      <!-- <span class="text-subtitle-1">{{ data.desc }}</span> -->
                    </div>
                    <VAvatar
                      :color="data.iconColor"
                      variant="tonal"
                      rounded
                      size="48"

                    >
                      <VIcon
                        :icon="data.icon"
                        size="48"
                        class="pa-2"
                      />
                    </VAvatar>
                  </div>
                </VCardText>
              </VCard>
            </VCol>
          </template>
        </VRow>
      </div>
      <VCard
        title="Search Filter"
        class="mb-6"
      >
        <VCardText>
          <VRow>
            <VCol
              cols="12"
              sm="4"
            >
              <div class="app-select flex-grow-1">
                <AppSelect
                  v-model="filter.role"
                  placeholder="Select Role"
                  item-title="name"
                  item-value="id"
                  :items=" ['All', ...roleStore.data.roles]"
                  @update:modelValue="filter_change"
                  clearable
                />
              </div>
            </VCol>
            <VCol
              cols="12"
              sm="4"
            >
              <div class="app-select flex-grow-1">
                <AppSelect
                  v-model="filter.division"
                  placeholder="Select Division"
                  item-title="name"
                  item-value="id"
                  :items="['All', ...divisionStore.data.divisions]"
                  @update:modelValue="filter_change"
                  clearable
                />
              </div>
            </VCol>
            <VCol
              cols="12"
              sm="4"
            >
              <div class="app-select flex-grow-1">
                <AppSelect
                  v-model="filter.status"
                  placeholder="Select Status"
                  :items="['All', ...employeeStore.data.statuses]"
                  @update:modelValue="filter_change"
                  clearable
                />
              </div>
            </VCol>
          </VRow>
        </VCardText>
        <hr>
        <br>


        <VCardText class="d-flex flex-wrap py-4 gap-4">
          <div class="app-select me-3 d-flex gap-3">
            <AppSelect
              :model-value="itemsPerPage"
              :items="[
                { value: 25, title: '25' },
                { value: 50, title: '50' },
                { value: 100, title: '100' },
                { value: 200, title: '200' },
                { value: 500, title: 'All' },
              ]"
              style="inline-size: 6.25rem;"
              @update:model-value="itemsPerPage = parseInt($event)"
            />
          </div>
          <VSpacer />

          <div class="app-user-search-filter d-flex align-center flex-wrap gap-4">
            <!-- 👉 Search  -->
            <div
              class="app-select"
              style="inline-size: 15rem;"
            >
              <AppTextField
                v-model="searchQuery"
                placeholder="Search"
                density="compact"
                clearable
              />
            </div>

            <!-- 👉 Export button -->
            <VBtn
              variant="tonal"
              :style="{color: '#CC4A04 !important'}"
              prepend-icon="tabler-screen-share"
              append-icon="tabler-chevron-down"
            >
              <JsonCSV
                id="expdata"
                class="btn btn-default"
                :data="dataToExport"
                name="filename.csv"
              >
                Export
              </JsonCSV>
            </VBtn>

            <!-- 👉 Add user button -->
            <VBtn
              prepend-icon="tabler-plus"
              @click="isAddNewUserDrawerVisible = true"
            >
              Add new user
            </VBtn>
          </div>
        </VCardText>

        <VDivider />

        <!-- //EmployeeDetails-index -->
        <VDataTableServer
          :items-per-page="itemsPerPage"
          :page="page"
          :items="items"
          :items-length="totalEmployees"
          :headers="headers"
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
                  v-for="item in column"
                  :key="item.title"
                  :class="`custom-header align-${item.align}`"
                >
                  <span
                    v-if="item.title != 'Employee' && item.sortable"
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
          <template #item.employee="{ item }">
            <div style="padding: 7px;">
              <div
                style="display: flex;
                  width: 45px;
                  align-items: center;
                  justify-content: center;
                  border-radius: 50px;
                  aspect-ratio: 1;
                  font-weight: 800;"
                :style="{
                  background: getBackgroundColor(item, 'employee'),
                  color: getTextColor(item, 'employee')
                }"
              >
                {{ item.first_name.charAt(0).toUpperCase() + item.last_name.charAt(0).toUpperCase() }}
              </div>
            </div>
          </template>
          <template #item.first_name="{ item }">
            <div class="font-weight-bold">
              {{ capitalizeFirst(item.first_name) }}
            </div>
          </template>
          <template #item.last_name="{ item }">
            <div class="font-weight-bold">
              {{ capitalizeFirst(item.last_name) }}
            </div>
          </template>
          <template #item.job_title="{ item }">
            {{ capitalizeFirst(item.job_title) }}
          </template>
          <template #item.access="{ item }">
            <div v-if="item.user.role">
              {{ item.user.role?.name }}
            </div>
          </template>
          <template #item.status="{ item }">
              <VChip
                  label
                  size="default"
                  :style="{
                      background: getBackgroundColor(item, 'status'),
                      color: getTextColor(item, 'status'),
                      borderRadius: '5px',
                  }"
              >
                  <span class="font-weight-bold">{{ capitalizeFirst(item.status) }}</span>
              </VChip>
          </template>
          <template #item.action="{ item }">
            <IconBtn>
              <VIcon icon="tabler-dots-vertical" />

              <VMenu
                activator="parent"
                location="left"
              >
                <VList>
                  <VListItem>
                    <Btn
                      style="margin-right: 50px; color: #818493; cursor: pointer;"
                      @click="viewClick(item)"
                    >
                      View
                    </Btn>
                  </VListItem>
                  <VListItem>
                    <Btn
                      href="#"
                      style="margin-right: 50px; color: #818493;  cursor: pointer;"
                      @click="editClick(item)"
                    >
                      Edit
                    </Btn>
                  </VListItem>
                </VList>
              </VMenu>
            </IconBtn>
          </template>
          <template #bottom>
            <VDivider />
            <div class="d-flex align-center justify-sm-space-between justify-center flex-wrap gap-3 pa-5 pt-3">
              <p
                class="text-sm text-disabled mb-0"
                style="color: #999 !important;"
              >
                {{ generateRangeText(page, totalEmployees, itemsPerPage) }}
              </p>

              <VPagination
                v-if="totalEmployees > itemsPerPage"
                v-model="page"
                :total-visible="6"
                :length="Math.ceil(totalEmployees / itemsPerPage)"
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
      </VCard>
      <AddNewUserDrawer
        v-model:isDrawerOpen="isAddNewUserDrawerVisible"
        @user-data="addNewUser"
      />
      <EditUserDrawer
        v-model:isDrawerOpen="isEditUserDrawerVisible"
        :employee="editEmployee"
        @update-data="UpdateEmployee"
      />
    </section>
  </div>
</template>


<script setup>
import AddNewUserDrawer from '@/pages/user/list/AddNewUserDrawer.vue'
import EditUserDrawer from '@/pages/user/list/EditUserDrawer.vue'
// import { ProfilePlaceHolder } from '@/plugins/profilePlaceHolder'
import { useDivisionStore } from "@/store/divisionStore"
import { useEmployeeStore } from "@/store/employeeStore"
import { useRoleStore } from "@/store/roleStore"

//import { paginationMeta } from '@api-utils/paginationMeta';
import _, { debounce } from 'lodash'
import JsonCSV from 'vue-json-csv'
import { useRouter } from 'vue-router'
import { VDataTableServer } from 'vuetify/labs/VDataTable'

import toast from '@/composable/toast.js'


const router = useRouter()
const employeeStore = useEmployeeStore()
const divisionStore = useDivisionStore()
const roleStore = useRoleStore()
var dataToExport = ref([])
var exportItems = ref([])
var sortDesc = ref(false)
var sortBy = ref(null)
var sortIcon = 'tabler-chevron-up'
let items = ref([])
const triggerWatch = ref(true)
const itemsData= ref({})
const searchQuery = ref('')
const editEmployee = ref({})

// Data table options
const itemsPerPage = ref(25)
const totalEmployees = ref(0)
const page = ref(1)
const orderBy = ref()

const updateOptions = async options =>  {
  itemsPerPage.value = options.itemsPerPage
  page.value = options.page
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order

  //items.value  = await employeeStore.setEmployees({page:newValue})
}

const filter = ref({
  role: 'Select Role',
  division: 'Select Division',
  status: 'Select Status',

})

onMounted( async() => {
  reloadTable()
})


const headers = [
  {
    id: 0,
    title: 'Employee',
    key: 'employee',
    align: 'center',
    sortable: true,
  },
  {
    id: 1,
    title: 'First Name',
    key: 'first_name',
    align: 'left',
    sortable: true,
    width: '150px',
  },
  {
    id: 2,
    title: 'Last Name',
    key: 'last_name',
    align: 'left',
    sortable: true,
    width: '150px',
  },
  {
    id: 3,
    title: 'Location',
    key: 'location.name',
    class: 'font-weight-bold',
    align: 'left',
    sortable: true,
  },
  {
    id: 4,
    title: 'Division',
    key: 'division.name',
    align: 'left',
    sortable: true,
  },
  {
    id: 5,
    title: 'Job Title',
    key: 'position.name',
    align: 'left',
    sortable: true,
  },
  {
    id: 6,
    title: 'Employment',
    key: 'employment.name',
    align: 'left',
    sortable: true,
  },
  {
    id: 7,
    title: 'Access',
    key: 'access',
    align: 'left',
    sortable: true,
  },
  {
    id: 8,
    title: 'Status',
    key: 'status',
    align: 'center',
    sortable: true,
    width: '170px'
  },
  {
    id: 9,
    title: '',
    key: 'action',
    sortable: false,
  },
]

const isAddNewUserDrawerVisible = ref(false)
const isEditUserDrawerVisible = ref(false)

// const reloadTable = async() => {
//   itemsData.value = await employeeStore.setEmployees({page:page.value, itemsPerPage: newValue, search: searchQuery.value})
//   items.value = itemsData.value.data;
// }

async function reloadTable() {
    const payload = {
        filter: {
            role: true,
            division: true,
            status: true,
        },
        data: {
            role_id: filter.value.role,
            division_id: filter.value.division,
            status: filter.value.status,
        },
        page: page.value,
        per_page: itemsPerPage.value,
        search: searchQuery.value
    }

    itemsData.value = await employeeStore.multipleFilter(payload)
    items.value = itemsData.value.data

    console.log("####", items.value)
    if(itemsPerPage.value !== -1) {
      itemsPerPage.value = itemsData.value.per_page
      totalEmployees.value = itemsData.value.total
    }else {
      totalEmployees.value = items.value.length
    }
}
watch(page, async newValue => {
  if(newValue != null){
    await reloadTable()
  }
}, { immediate: true })

watch(itemsPerPage, async newValue => {
  if (newValue !== null && triggerWatch.value) {
    triggerWatch.value = false
    page.value = 1
    await reloadTable()

    triggerWatch.value = true
  }
}, { immediate: true })

watch(items, async newValue => {
  if(newValue != null){
    exportItems = newValue
  } else exportItems = items.value

  exportData()
})

watch(searchQuery, async () => {
  // if(newValue){
    page.value = 1
    await reloadTable()
  // }
})

// const search_change = debounce(async() => {
//   page.value = 1
//   await reloadTable()
//   items.value = await leaveStore.multipleFilter(payload)
// }, 800)

const addNewUser = async userData => {
  // const initials = userData.first_name.charAt(0).toUpperCase() + userData.last_name.charAt(0).toUpperCase()
  // userData.profile = ProfilePlaceHolder(initials)
  // console.log(userData)
  const response = await employeeStore.addEmployee(userData)
  if(!response)  toast('error',"Error please try again!")
  else  {
    toast('success',"Employee Added!")
    isAddNewUserDrawerVisible.value = false
    await reloadTable()
  }
}

const UpdateEmployee = async userData => {
  const response = await employeeStore.updateEmployee(userData)
  if(!response)  toast('error',"Error please try again!")
  else  {
    toast('success',"Employee Updated!")
    isEditUserDrawerVisible.value = false
    await reloadTable()
  }
}

const rowClick = (e, row)=>{
  if(e.target.nodeName != "svg"){
    employeeStore.setEmployeeSelected(row.item)
    employeeStore.setActiveTab('EmployeeInfo')

    //console.log("employee_selected :", employeeStore.data.employee_selected )
    router.push({ path: 'employee/employeedetails' , params: { tab: 'EmployeeInfo' } })
  }
}

const viewClick = item => {
  employeeStore.setEmployeeSelected(item)
  employeeStore.setActiveTab('EmployeeInfo')
  router.push({ path: 'employee/employeedetails' , params: { tab: 'EmployeeInfo' } })
}

const editClick = item => {

  const itemCopy = _.cloneDeep(item)

  if(itemCopy.user.role)
    itemCopy.access_id = itemCopy.user.role.id
  else
    itemCopy.access_id = null
  editEmployee.value = itemCopy
  isEditUserDrawerVisible.value = true
}

const exportData = () => {
  dataToExport = exportItems.map(item => ({
    first_name: item.first_name,
    last_name: item.last_name,
    location: item.location?.name,
    division: item.division?.name,
    job_title: item.job_title,
    employment: item.employment?.name,
    access: item.user && item.user.role && item.user.role ? item.user.role.name : '',
    status: item.status,
  }))

  // console.log('data to export', dataToExport)

}

const getBackgroundColor = (item, value) => {
  if (item.user.role && value == 'employee') {
      switch (capitalizeFirst(item.status)) {
          case 'Active':
              return '#e6f5e9'
          case 'Offswing':
              return '#fae3d7'
          case 'Pending':
              return '#e5eaff'
          case 'Extended leave':
              return '#FDF3DE'
          case 'Terminated':
              return '#FBDFE1'
          default:
              return '#cccccc'
      }
  } else if (item.status && value == 'status') {
    switch (capitalizeFirst(item.status)) {
    case 'Active':
      return '#e6f5e9'
    case 'Offswing':
      return '#fae3d7'
    case 'Pending':
      return '#e5eaff'
    case 'Extended Leave':
    case 'Extended leave':
      return '#FDF3DE'
    case 'Terminated':
      return '#FBDFE1'
    default:
      return '#cccccc'
    }
  }

  return '#cccccc'
}

const getTextColor = (item, value) => {

  if (item.user.role && value == 'employee') {
    switch (item.user.role?.name) {
    case 'Administrator':
      return '#e35205'
    case 'Applicant':
      return '#f15bb5'
    case 'Board':
      return '#001871'
    case 'CEO':
      return '#f1b434'
    case 'Finance':
      return '#e63946'
    case 'General Manager':
      return '#9b5de5'
    case 'HR':
      return '#f1b434'
    case 'Line Manager':
      return '#074625'
    case 'User':
      return '#0a7069'
    case 'Active':
      return '#62c379'
    default:
      return '#363636'
    }
  } else if (item.status && value == 'status') {
    switch (capitalizeFirst(item.status)) {
    case 'Active':
      return '#61c378'
    case 'Offswing':
      return '#e35205'
    case 'Pending':
      return '#5c7fff'
    case 'Extended Leave':
    case 'Extended leave':
      return '#f1b434'
    case 'Terminated':
      return '#E7404C'
    default:
      return '#363636'
    }
  }

  return '#363636'
}

const filter_change = debounce(async() => { //dari
  triggerWatch.value = false

  const payload = {
    filter: {
      role: true,
      division: true,
      status: true,
    },
    data: {
      role_id: filter.value.role,
      division_id: filter.value.division,
      status: filter.value.status,
    },
  }

  itemsData.value = await employeeStore.multipleFilter(payload)
  items.value = itemsData.value.data
  itemsPerPage.value = itemsData.value.per_page
  totalEmployees.value = itemsData.value.total
  setTimeout(() => {
    triggerWatch.value = true
  }, 500)
}, 800)

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
  } else if (rowsPerPage === -1) {
    rangeText = `Showing all of ${totalNumberOfRows} entries`;
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

const toggleSort = column => {
  console.log('sortBy', sortBy)
  if (sortBy === column.key) {
    sortDesc = !sortDesc
    console.log('toggle sort sortDesc', sortDesc)
  } else {
    console.log('toggle sort sortBy', column.key)
    sortBy = column.key
    sortDesc = false
  }

  sortItems()
}

const isSorted = column => {
  return sortBy === column.key
}

const sortItems = () => {
  if (!sortBy) {
    return
  }

  let sortKey = sortBy
  if (sortBy == 'access') {
    sortKey = 'user.role.name'
  }

  items.value.sort((a, b) => {
    let result = 0
    let aValue = getNestedValue(a, sortKey)
    let bValue = getNestedValue(b, sortKey)

    if (aValue === undefined || aValue === null || aValue === '') {
      return sortDesc ? 1 : -1
    }
    if (bValue === undefined || bValue === null || bValue === '') {
      return sortDesc ? -1 : 1
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

    return sortDesc ? -result : result
  })
}

const getNestedValue = (obj, path) => {
  return path.split('.').reduce((acc, part) => {
    const match = part.match(/(\w+)\[(\d+)\]/)
    if (match) {
      const arrayPart = match[1]
      const index = parseInt(match[2], 10)

      return acc && acc[arrayPart] && acc[arrayPart][index] !== undefined ? acc[arrayPart][index] : undefined
    } else {
      return acc && acc[part] !== undefined ? acc[part] : undefined
    }
  }, obj)
}

const capitalizeFirst = (str) => {
    if (str) {
        return str.split(' ').map(word => {
            return word.charAt(0).toUpperCase() + word.slice(1);
        }).join(' ');
    }

    return str
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

:deep(.text-primary) {
  color: #001871 !important;
}
.custom-width {
  width: 50px !important; /* Use !important to ensure it overrides other styles */
}
:deep(.v-card-title) {
  font-weight: 900 !important;
}
</style>
