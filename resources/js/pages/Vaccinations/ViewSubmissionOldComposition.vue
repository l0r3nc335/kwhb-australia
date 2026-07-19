<script setup>
import vaccinationApi from "@/api/vaccination/vaccination";
import usersApi from "@/api/user/user";
import { useVaccinationStore } from "@/store/vaccinationStore";
import { VDataTableServer } from 'vuetify/labs/VDataTable';
import Exemption from './Exemption.vue';
import ReviewSubmission from './ReviewSubmission.vue';
import Submit from './Submit.vue';
import Main from './index.vue';

const vaccinationStore = useVaccinationStore()

const items = ref([])
const vaccinationTotal = ref(0)
const vaccinationData = ref({})
const loadItems = ref(false)
const isReviewDialogVisible = ref(false)
const isSubmitDialogVisible = ref(false)
const isExemptDialogVisible = ref(false)
const isViewSubmissionVisible = ref(false)
const searchQuery = ref('')
const itemsPerPage = ref(50)
// const page = ref(1)
var sortBy = ref(null)
const orderBy = ref()
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
        title: 'Employee Name',
        key: 'employee',
        align: 'left',
        sortable: true,
    },
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
        title: 'Uploaded',
        key: 'uploaded',
        align: 'left',
        sortable: true,
    },
    {
        title: 'Status',
        key: 'status',
        class: 'font-weight-bold',
        align: 'center',
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
        title: 'Comment',
        key: 'comment',
        class: 'font-weight-bold',
        align: 'left',
        sortable: true,
    },
    {
        title: 'Assigned To',
        key: 'assigned',
        class: 'font-weight-bold',
        align: 'left',
        sortable: true,
    },
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
    switch (item.file_status || 'Expired') {
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
    switch (item.file_status || 'Expired') {
    case 'Current':
        return '#62C379'
    case 'Expired':
        return '#E63946'
    case 'Pending review':
        return '#E35205'
    case 'Required':
        return '#5C7FFF'
    case 'Applied':
        return '#F1B434'
    case 'Exempt':
        return '#001871'
    default:
        return '#62C379'
    }
}

onMounted( async () => {
    await vaccinationStore.setVaccinations();
})

const vaccination = async () => {
    try {
        loadItems.value = true
        const response = await vaccinationApi.fetch_employee_vaccination_list({  ...query,  itemsPerPage: itemsPerPage.value})
        items.value = response.data || []
        vaccinationTotal.value = response.total
        loadItems.value = false
    } catch (error) {
        console.error(error)
    }
}
vaccination()

const usersAssignee = async () => {
    try {
        loadItems.value = true
        let role_id = 5
        const response = await usersApi.get_users({ role_id })
        assignees.value = response.map(user => user.username)
    } catch (error) {
        console.error(error)
    }
}
usersAssignee()

watch(itemsPerPage, () => {
    query.page = 1
    vaccination()
})


watch(
    () => query.type,
    (first, second) => {
        vaccination()
    },
)

watch(
    () => query.status,
    (first, second) => {
        vaccination()
    },
)

watch(
    () => query.assignee,
    (first, second) => {
        vaccination()
    },
)

watch(
    () => query.page,
    (first, second) => {
        vaccination()
    },

)
const hasDatePassed = (givenDateStr) => {
    const today = new Date().setHours(0, 0, 0, 0);
    const [year, month, day] = givenDateStr.split('-').map(Number);
    const givenDate = new Date(year, month - 1, day).setHours(0, 0, 0, 0);

    return givenDate < today;
}

const rowClick = (e, row)=>{
    if(e.target.nodeName != "svg"){
        vaccinationData.value = row.item
        isReviewDialogVisible.value = true
    }
}

const refetch = () => {
    vaccination()
}
</script>

<template>
    <div v-if="!isViewSubmissionVisible">
        <p class="text-h2 font-weight-bold mb-5">Employee Vaccinations</p>
        <v-card>
            <VCardText class="d-flex flex-wrap gap-4">
                <VRow>
                    <VCol cols="12" class="mr-5">
                        <div class="d-flex align-center flex-wrap gap-4">
                            <VSpacer/>
                            <v-btn
                                style="background-color: #001871"
                                color="#CFCFCF"
                                variant="outlined"
                                class="text-none"
                                @click="$emit('back')"
                            >
                                View all vaccination types
                            </v-btn>
                            <v-btn
                                variant="tonal"
                                color="#E35205"
                                class="text-none font-weight-bold"
                                @click="isExemptDialogVisible = true"
                            >
                                Create Exemption
                            </v-btn>
                            <v-btn
                                color="primary"
                                class="text-none"
                                @click="isSubmitDialogVisible = true"
                            >
                                Submit Vaccination
                            </v-btn>
                        </div>
                    </VCol>

                    <!-- </VRow>
                    <VRow> -->
                    <Vcol cols="12" class="ml-3 mr-3">
                        <div class="text-subtitle-1 text-medium-emphasis">Search Filter</div>
                        <div class="d-flex align-center flex-wrap gap-4">
                            <AppSelect
                                placeholder="By Type"
                                density="compact"
                                class="search-dropdown"
                                v-model="query.type"
                                :items="vaccinationStore.data.vaccinations"
                                item-title="name"
                                item-value="name"
                                clearable
                            />
                            <AppSelect
                                placeholder="By Status"
                                density="compact"
                                class="search-dropdown"
                                v-model="query.status"
                                :items="['Current','Expired','Pending review','Required','Applied','Exempt']"
                                clearable
                            />
                            <AppSelect
                                placeholder="By Assignee"
                                density="compact"
                                class="search-dropdown"
                                v-model="query.assignee"
                                :items="assignees.values()"
                                clearable
                            />
                        </div>
                    </Vcol>

                </VRow>

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
                :items-length="vaccinationTotal"
                :headers="headers"
                :search="searchQuery"
                @click:row="rowClick"
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
                    <span style="white-space: nowrap;" :style="{ marginLeft: item.key === 'employee' ? '5px' : '0px' }">{{ item.title }}</span>
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
                    <span class="font-weight-medium ml-1">{{ item.user.employee.first_name + ' ' + item.user.employee.last_name  }}</span>
                </template>

                <template #item.type="{ item }">
                    <span class="font-weight-regular">{{ item.vaccination?.name }}</span>
                </template>

                <template #item.expiry="{ item }">
                    <span class="font-weight-regular" v-if="item.expiry_date">{{ moment(item.expiry_date).format('DD/MM/YYYY') }}</span>
                </template>

                <template #item.uploaded="{ item }">
                    <span class="font-weight-regular">{{ item.status === '1' ? 'Yes' : 'No' }}</span>
                </template>

                <template #item.requirement="{ item }">
                    <span class="font-weight-regular">{{ item.vaccination?.required === '0' ? 'Mandatory' : 'Optional' }}</span>
                </template>

                <template #item.assigned="{ item }">
                    <span class="font-weight-regular">{{ item.file_status === 'Pending review' ? 'HR' : '' }}</span>
                </template>

                <template #item.status="{ item }">
                    <template v-if="item.status == 0">
                        <div class="tw-py-1 tw-rounded-lg tw-font-bold __status_pending_approval">
                            Pending Approval
                        </div>
                    </template>
                    <template v-else>
                        <VChip
                            label
                            size="default"
                            :style="{
                background: (item.expiry_date && !hasDatePassed(item.expiry_date)) || item.file_status == 'Exempt'  ? getBackgroundColor(item) :  getBackgroundColor('Expired'),
                color: (item.expiry_date && !hasDatePassed(item.expiry_date)) || item.file_status == 'Exempt' ? getTextColor(item): getTextColor('Expired'),
              }"
                        >
                            <span class="font-weight-bold">{{ (item.expiry_date && !hasDatePassed(item.expiry_date)) || item.file_status == 'Exempt' ? item.file_status : 'Expired' }}</span>
                        </VChip>
                    </template>
                </template>

                <template #item.comment="{ item }">
                    <span style="color:#CFCFCF">{{ item.comments ? item.comments : 'Comment here' }}</span>
                </template>

                <template #bottom>
                    <VDivider />
                    <div class="d-flex align-center justify-sm-space-between justify-center flex-wrap gap-3 pa-5 pt-3">
                        <p
                            class="text-sm text-disabled mb-0"
                            style="color: #999 !important;"
                        >
                            {{ generateRangeText(query.page, vaccinationTotal, itemsPerPage) }}
                        </p>

                        <VPagination
                            v-if="vaccinationTotal > itemsPerPage"
                            v-model="query.page"
                            :total-visible="6"
                            :length="Math.ceil(vaccinationTotal / itemsPerPage)"
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

        <Submit
            v-model:isDrawerOpen="isSubmitDialogVisible"
            @refetch="refetch"
        />

        <Exemption
            v-model:isDialogOpen="isExemptDialogVisible"
            @refetch="refetch"
        />
        <ReviewSubmission
            v-model:isDialogOpen="isReviewDialogVisible"
            :vaccinationData="vaccinationData"
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

.__status_pending_approval {
    background-color: #FDF3DE;
    color: #f1b434;
    padding: 5px 5px;
}

</style>
