<template>
    <div class="tw-bg-white tw-shadow">
        <VDataTableServer
            :items-per-page="pagination.per_page"
            :page="pagination.page"
            :items="employees"
            :items-length="pagination.total"
            :headers="headers"
            style="color: #333954 !important; font-family: Arial, Helvetica, sans-serif; font-size: medium;"
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
                    <span style="white-space: nowrap;"><b>{{ item.title }}</b></span>
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
                 <span v-else style="text-align: center">
                    <b>{{ item.title }}</b>
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
                    {{ 
                        (item.first_name?.charAt(0)?.toUpperCase() || '') + 
                        (item.last_name?.charAt(0)?.toUpperCase() || '') 
                    }}
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
                    {{ item.user.role.name }}
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

            <template #item.action="{item}">
                <IconBtn>
                    <VIcon icon="tabler-dots-vertical" />
                    <VMenu
                        activator="parent"
                    >
                        <VList>
                            <VListItem  class="tw-cursor-pointer hover:tw-bg-red-100" @click="openViewSlider(item)">View</VListItem>
                            <VListItem  class="tw-cursor-pointer hover:tw-bg-red-100" @click="openEditSlider(item)">Edit</VListItem>
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
                        {{ generateRangeText() }}
                    </p>

                    <VPagination
                        v-if="pagination.total > pagination.per_page"
                        v-model="pagination.page"
                        :total-visible="6"
                        :length="Math.ceil(pagination.total / pagination.per_page)"
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

                <PositionTitleForm
                    :isDrawerOpen="showPositionTitleViewer"
                    :employee="editEmployee"
                    @update:isDrawerOpen="val => showPositionTitleViewer = val"
                    @updateData="loadEmployees"            
                />

                <PositionTitleView
                    :isDrawerOpen="showPositionTitleViewerForm"
                    :employee="editEmployee"
                    @update:isDrawerOpen="val => showPositionTitleViewerForm = val"          
                />                

            </template>
        </VDataTableServer>


        
    </div>
</template>

<script>
    import employeeApi from "@/api/employee/employeeApi";
import PositionTitleForm from "@/pages/AdminSettings/PositionTitle/PositionTitleForm.vue";
import PositionTitleView from "@/pages/AdminSettings/PositionTitle/PositionTitleView.vue";
import _ from 'lodash';
import { VDataTableServer } from 'vuetify/labs/VDataTable';


    export default {
        components: {
            VDataTableServer,
            PositionTitleForm,
            PositionTitleView
        },

        props: {
            search: {
                required: false,
                default: null
            },
            perPage: {
              required: false,
              default: 10
            },
            selectedRole: {
                required: false,
                default: null
            },
            selectedDivision: {
                required: false,
                default: null
            },
            selectedLocation: {
                required: false,
                default: null
            },
            selectedJobTitle: {
                required: false,
                default: null
            },
            selectedEmployment: {
                required: false,
                default: null
            },
            selectedStatus: {
                required: false,
                default: null
            }
        },

        data() {
            return {
                employees: [],
                headers: [
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
                        key: 'job_title',
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
                        align: 'left',
                        sortable: true,
                        width: '170px'
                    },
                    {
                        id: 9,
                        title: '',
                        key: 'action',
                        sortable: false,
                    }                    
                ],
                pagination: {
                    page: 1,
                    per_page: 10,
                    total: 0
                },
                sortDesc: null,
                sortBy: null,
                showPositionTitleViewer: false,
                showPositionTitleViewerForm: false,
                editEmployee: null
            }
        },

        mounted() {
            this.pagination.per_page = this.perPage
            this.loadEmployees()
        },

        methods: {
            loadFromSearch: _.debounce(function() {
                this.loadEmployees()
            }, 1000),

            async  openEditSlider(item){
                console.log("item", item)
                this.showPositionTitleViewer = true;
                this.editEmployee = item;
            },

            async openViewSlider(item){
                console.log("item", item)
                this.showPositionTitleViewerForm = true;
                this.editEmployee = item;
            },
            async loadEmployees() {
                let data = await employeeApi.fetchEmployees({
                    role_id: this.selectedRole,
                    division_id: this.selectedDivision,
                    location_id: this.selectedLocation,
                    employment_id: this.selectedEmployment,
                    job_title: this.selectedJobTitle,
                    status: this.selectedStatus,
                    per_page: this.perPage,
                    page: this.pagination.page,
                    search: this.search
                })

                console.log("EEEEE", this.employees, this.pagination)

                this.employees = data.data || []
                this.pagination.total = data.total
            },

            generateRangeText() {
                let pageNumber = this.pagination.page
                let totalNumberOfRows = this.pagination.total
                let rowsPerPage = this.pagination.per_page

                const startIndex = (this.pagination.page - 1) * this.pagination.per_page + 1
                let endIndex = this.pagination.page * this.pagination.per_page

                if (endIndex > this.pagination.total) {
                    endIndex = this.pagination.total
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
            },

            sortItems() {
                if (!this.sortBy) {
                    return
                }

                let sortKey = this.sortBy
                if (sortBy == 'access') {
                    sortKey = 'user.role.name'
                }

                this.employees.value.sort((a, b) => {
                    let result = 0
                    let aValue = getNestedValue(a, sortKey)
                    let bValue = getNestedValue(b, sortKey)

                    if (aValue === undefined || aValue === null || aValue === '') {
                        return this.sortDesc ? 1 : -1
                    }
                    if (bValue === undefined || bValue === null || bValue === '') {
                        return this.sortDesc ? -1 : 1
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

                    return this.sortDesc ? -result : result
                })
            },

            toggleSort(column) {
                if (this.sortBy === column.key) {
                    this.sortDesc = !sortDesc
                } else {
                    this.sortBy = column.key
                    this.sortDesc = false
                }

                this.sortItems()
            },

            isSorted(column) {
                return this.sortBy === column.key
            },

            capitalizeFirst(str) {
                if (str) {
                    return str.split(' ').map(word => {
                        return word.charAt(0).toUpperCase() + word.slice(1);
                    }).join(' ');
                }

                return str
            },

            getTextColor(item, value) {
                if (item.user.role && value == 'employee') {
                    switch (item.user.role.name) {
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
                    switch (this.capitalizeFirst(item.status)) {
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
            },

            getBackgroundColor(item, value) {
                {
                    if (item.user.role && value == 'employee') {
                        switch (item.user.role.name) {
                            case 'Administrator':
                                return '#f9dccd'
                            case 'Applicant':
                                return '#fcdef0'
                            case 'Board':
                                return '#ccd1e3'
                            case 'CEO':
                                return '#fcf0d6'
                            case 'Finance':
                                return '#fad7da'
                            case 'General Manager':
                                return '#ebdffa'
                            case 'HR':
                                return '#9b5de5'
                            case 'Line Manager':
                                return '#b9f3c7'
                            case 'User':
                                return '#70f0e7'
                            default:
                                return '#cccccc'
                        }
                    } else if (item.status && value == 'status') {
                        switch (this.capitalizeFirst(item.status)) {
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
            }
        },

        watch: {
            pagination: {
                handler() {
                    this.loadEmployees()
                },
                deep: true
            },

            selectedRole() {
                this.loadEmployees()
            },

            selectedDivision() {
                this.loadEmployees()
            },

            selectedLocation() {
                this.loadEmployees()
            },

            selectedJobTitle() {
                this.loadEmployees()
            },

            selectedStatus() {
                this.loadEmployees()
            },

            selectedEmployment() {
                this.loadEmployees()
            },

            search() {
                this.loadFromSearch()
            },

            perPage() {
                console.log(this.perPage)
                this.loadEmployees()
            }
        }
    }
</script>
