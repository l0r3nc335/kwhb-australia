<script setup>
import vaccinationApi from "@/api/vaccination/vaccination";
import toast from '@/composable/toast.js';
import { VDataTableServer } from 'vuetify/labs/VDataTable';
import AddDrawer from './AddDrawer.vue';
import EditDrawer from './EditDrawerOptionsAPI.vue';
import ViewSubmission from './ViewSubmission.vue';
import divisionApi from "@/api/division/divisionApi"
import roleApi from "@/api/role/roleApi"

const itemselected = ref()
const loadingDelete = ref(false)
const isDialogDeleteVisible = ref(false)
const loadItems = ref(false)
const items = ref([])
const vaccinationTotal = ref(0)
const vaccinationData = ref({})
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
    page: 1
})

//pagination page length dropdown indicator
const itemsPerPageOptions = ref([
    { value: 50, title: '50' },
    { value: 100, title: '100' },
    { value: 200, title: '200' },
    { value: 500, title: 'All' },
])

const updateOptions = options => {
    // page.value = options.page
    sortBy.value = options.sortBy[0]?.key
    orderBy.value = options.sortBy[0]?.order
}

const toggleSort = column => {
    sortBy.value = column.key
    sortDesc.value = !sortDesc.value

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

        return sortDesc.value ? result : -result
    })
}

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
    vaccination()
})

const vaccination = async () => {
    try {
        loadItems.value = true
        const response = await vaccinationApi.fetch_vaccination({ ...query, itemsPerPage: itemsPerPage.value })
        items.value = response.data
        vaccinationTotal.value = response.total
        loadItems.value = false
    } catch (error) {
        console.error(error)
    }
}
vaccination()

const divisions = ref([])
const roles = ref([])
const loadDivisions = async () => {
    try {
        //divisions.value = await divisionApi.fetchDivisions()
        divisions.value = await divisionApi.fetchDivisionsActive()
    } catch (error) {
        console.error('Failed to load divisions:', error)
    }
}
const loadRoles = async () => {
    try {
        roles.value = await roleApi.fetchAllRoles()
    } catch (error) {
        console.error('Failed to load roles:', error)
    }
}

onMounted(() => {
    loadDivisions()
    loadRoles()
})

const refetch = () => {
    vaccination()
}

watch(
    () => query.page,
    (first, second) => {
        vaccination()
    },
)

const editVaccination = (item) => {
    vaccinationData.value = item
    isEditDrawerVisible.value = true
}

const deleteDialog = (item) => {
    isDialogDeleteVisible.value = true
    itemselected.value = item.id
}

const deleteVaccination = async () => {
    loadingDelete.value = true
    const response = await vaccinationApi.delete_vaccination({ id : itemselected.value })
    toast('success', response.message)
    loadingDelete.value = false
    itemselected.value = ''
    isDialogDeleteVisible.value = false
    vaccination()
}
</script>

<template>
    <div v-if="isViewSubmissionVisible">
        <p class="text-h2 font-weight-bold mb-5">Vaccinations</p>
        <v-card>
            <VCardText class="d-flex flex-wrap gap-4">
                <VSpacer />

                <div class="d-flex align-center flex-wrap gap-4">
                    <v-btn
                        style="background-color: #001871"
                        color="#CFCFCF"
                        variant="outlined"
                        @click="isViewSubmissionVisible = false"
                    >
                        View Submissions
                    </v-btn>

                    <v-btn
                        prepend-icon="tabler-plus"
                        color="primary"
                        @click="isAddDrawerVisible = true"
                    >
                        Add Required Vaccination
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
                :items-length="vaccinationTotal"
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
                    <div class="tw-flex tw-justify-start" style="text-align: left">
                        <span class="font-weight-regular ml-1">{{ item.name }}</span>
                    </div>
                </template>

                <template #item.expiry="{ item }">
                    <template v-if="item.validity == 'Custom'">
                        <span class="font-weight-regular">{{ moment(item.custom_expiry).format('DD-MM-YYYY') }}</span>
                    </template>
                    <template v-else>
                        <span class="font-weight-regular">{{ item.validity }}</span>
                    </template>
                </template>

                <template #item.roles="{ item }">
                    <span v-if="item.role && item.role.split(',').length === (roles?.length || 0)" class="font-weight-regular">
                        All Roles
                    </span>
                    <span v-else class="font-weight-regular">{{ (item.role ? item.role.split(',').join(', ') : '') }}</span>
                </template>

                <template #item.division="{ item }">
                    <span
                        v-if="item.division?.split(',')?.length === (divisions?.length || 0)"
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
                    <span class="font-weight-regular">{{ item.is_active == '0' ? 'Archive' : 'Active' }}</span>
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

                <template #item.actions="{ item }">
                    <div class="text-right">
                        <v-dialog v-model="isDialogDeleteVisible" max-width="400">
                            <v-card prepend-icon="mdi-warning" title="Confirm Archive" text="Are you sure you want to archive this item?">
                                <v-card-actions>
                                    <v-spacer></v-spacer>
                                    <v-btn :loading="loadingDelete" @click="deleteVaccination" variant="tonal" color="#CC4A04">Yes</v-btn>
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
                                <VListItem class="hand" style="color: #E35205" @click="editVaccination(item)">
                                    <template #prepend>
                                        <VIcon icon="tabler-edit"/>
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
            :vaccinationData="vaccinationData"
            @refetch="refetch"
        />
    </div>
    <div v-else>
        <ViewSubmission @back="isViewSubmissionVisible=true"/>
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
