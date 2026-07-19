<template>
    <div class="tw-flex tw-flex-col py-2">
        <!--div class="tw-flex tw-flex-col lg:tw-flex-row tw-justify-between">
            <ContractsFilters
                @filter-date="filterDate"
                @filter-expiry="filterExpiry"
                @filter-role="filterRole"
                @filter-location="filterLocation"
                @filter-status="filterStatus">
            </ContractsFilters>
        </div-->

        <div class="app-select me-3 mt-10 d-flex gap-3">
            <AppSelect
                :model-value="pagination.per_page"
                :items="itemsPerPageOptions"
                style="inline-size: 6.25rem;"
                @update:model-value="pagination.per_page = parseInt($event)"
            />
        </div>

        <VDataTableServer
            :loading="loadItems"
            :items-per-page="pagination.per_page"
            :page="pagination.page"
            :items="contracts"
            :items-length="total"
            :headers="headers"
            @click:row="viewTableItem"
            class="tw-mt-5" style="color: #333954 !important; font-family: Arial, Helvetica, sans-serif; font-size: medium;"
            @update:options="updateOptions"
            fixed-header
        >
            <template #headers="{ headers }">
                <tr>
                    <template
                        v-for="column in headers"
                        :key="column.id"
                    >
                        <th
                            style="padding: 0 0"
                            v-for="item in column"
                            :key="item.title"
                            :class="`custom-header align-${item.align}`">

                            <span v-if="item.title === 'check'">
                                <input type="checkbox" class="custom-checkbox" v-model="selectAll" @change="toggleAll">
                            </span>

                            <span v-if="item.sortable"
                                  style="display: inline-flex; align-items: center;"
                                  @click="toggleSort(item)">
                                <span style="white-space: nowrap;">{{ item.title }}</span>
                                <span v-if="isSorted(item)">
                                  <VIcon
                                      v-if="pagination.sortDesc"
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

                                <VIcon v-else style="font-size: medium;">
                                  tabler-selector
                                </VIcon>
                              </span>
                            <span v-else>
                                  <span v-if="item.title !== 'check'">{{ item.title }}</span>
                              </span>
                        </th>
                    </template>
                </tr>
            </template>

            <template #item.contract_id="{ item }">
                <input type="checkbox"  class="custom-checkbox" v-model="selectedItems" :value="item">
            </template>

            <template #item.contract_title="{item}">
                <span style="margin-left: 15px">{{item.contract_title}}</span>
            </template>


            <template #item.employee_first_name="{item}">
                <span  style="margin-left: 15px!important;">{{item.employee_first_name}}</span>
            </template>
            <template #item.employee_last_name="{item}">
                <span  style="margin-left: 15px!important;">{{item.employee_last_name}}</span>
            </template>
            <template #item.employee_job_title="{item}">
                <span  style="margin-left: 15px!important;">{{item.employee_job_title}}</span>
            </template>
            <template #item.location_name="{item}">
                <span  style="margin-left: 25px!important;">{{item.location_name}}</span>
            </template>
            <template #item.assignee_username="{item}">
                <span  style="margin-left: 15px!important;">{{item.assignee_username}}</span>
            </template>
            <template #item.created_at="{item}">
                <span  style="margin-left: 15px!important;">{{formatDate(item.created_at, 'DD/MM/YYYY')}}</span>
            </template>

            <!-- Status Name Button -->
            <template #item.stat="{item}">
                <template v-if="item.stat === 'pending_0'">
                    <span class="tw-block tw-text-[11px]">0%</span>
                    <span class="tw-block tw-flex tw-justify-center">
                      <div class="tw-w-14 tw-bg-gray-200 tw-rounded tw-h-1">
                        <div class="tw-bg-green-500 tw-h-1 tw-w-1/12 tw-rounded"></div>
                      </div>
                    </span>
                    <span class="tw-block tw-text-[11px]">Need to sign</span>
                </template>

                <template v-if="item.stat === 'pending_50'">
                    <span class="tw-block tw-text-[11px]">50%</span>
                    <span class="tw-block tw-flex tw-justify-center">
                      <div class="tw-w-14 tw-bg-gray-200 tw-rounded tw-h-1">
                        <div class="tw-bg-green-500 tw-h-1 tw-w-1/2 tw-rounded"></div>
                      </div>
                    </span>
                    <span class="tw-block tw-text-[11px]">Need to sign</span>
                </template>

                <div v-else-if="item.stat === 'completed'" class="tw-bg-green-100 tw-text-green-500 tw-font-bold tw-p-1 tw-rounded-lg">
                    {{item.stat}}
                </div>
                <div v-else-if="item.stat === 'rejected'" class="tw-bg-red-100 tw-text-red-500 tw-font-bold tw-p-1 tw-rounded-lg">
                    {{item.stat}}
                </div>
                <div v-else-if="item.stat === 'expired'" class="tw-bg-red-100 tw-text-red-500 tw-font-bold tw-p-1 tw-rounded-lg">
                    {{item.stat}}
                </div>
                <div v-else-if="item.stat === 'draft'" class="tw-bg-gray-100  tw-font-bold tw-p-1 tw-rounded-lg">
                    {{item.stat}}
                </div>
                <!--div v-else>
                    {{item.stat}}
                </div-->
            </template>

            <!-- 3 dot Action -->
            <template #item.action="{item}">
                <IconBtn v-if="hasPermissions([ /* kwhb.permissions.ui_key */], null, true)">

                    <VIcon icon="tabler-dots-vertical" />

                    <VMenu activator="parent">
                        <VList>
                            <VListItem @click="viewLeave(item)" class="action_vlist">Duplicate</VListItem>
                            <VListItem @click="viewLeave(item)" class="action_vlist">Save as Template</VListItem>
                            <VListItem @click="viewLeave(item)" class="action_vlist">History</VListItem>
                            <VListItem @click="viewLeave(item)" class="action_vlist">Transfer of Ownership</VListItem>
                            <VListItem @click="viewLeave(item)" class="action_vlist">Resend</VListItem>
                            <VListItem @click="viewLeave(item)" class="action_vlist">Cancelled</VListItem>
                            <VListItem @click="viewLeave(item)" class="action_vlist">Delete</VListItem>
                            <VListItem @click="viewLeave(item)" class="action_vlist">Edit</VListItem>
                            <!--VListItem v-if="hasPermissions('people.leave_management_leave_request.approve', item.employee) && ((!inRoles(['CEO', 'General Manager']) && !item.is_endorsed)|| (inRoles(['CEO', 'General Manager']) && item.status != 1))" class="tw-cursor-pointer hover:tw-bg-green-100" @click="approveLeave(item)">
                                <span v-if="inRoles(['CEO', 'General Manager'])">
                                    Approve {{item.is_approved}}
                                </span>
                                <span v-else>
                                    Endorse
                                </span>
                            </VListItem-->
                            <!--VListItem
                                v-if="hasPermissions('people.leave_management_leave_request.view', item) &&  item.status != 1"
                                class="tw-cursor-pointer hover:tw-bg-green-100" @click="viewLeave(item)">View
                            </VListItem>
                            <VListItem
                                v-if="hasPermissions('people.leave_management_leave_request.update', item.employee) && item.status != 1"
                                class="tw-cursor-pointer hover:tw-bg-blue-100" @click="editLeave(item)">Edit
                            </VListItem>
                            <VListItem
                                v-if="hasPermissions('people.leave_management_leave_request.delete', item.employee) && item.status != 1"
                                lass="tw-cursor-pointer hover:tw-bg-red-100" @click="showDeleteLeaveConfirmation(item)">Delete
                            </VListItem>
                            <VListItem
                                v-if="item.status == 1" class="tw-cursor-pointer hover:tw-bg-red-100"
                                @click="showCancelConfirmation(item)">Cancel
                            </VListItem-->
                        </VList>
                    </VMenu>
                </IconBtn>
            </template>

            <!-- PAGINATION BUTTONS BELOW -->
            <template #bottom>
                <VDivider />
                <div class="d-flex align-center justify-sm-space-between justify-center flex-wrap gap-3 pa-5 pt-3">
                    <p
                        class="text-sm text-disabled mb-0"
                        style="color: #999 !important;"
                    >
                    </p>

                    <VPagination
                        v-if="total > pagination.per_page"
                        v-model="pagination.page"
                        :total-visible="5"
                        :length="Math.ceil(total / pagination.per_page)"
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
    </div>
    <!--VDialog v-model="showLeaveForm" width="900" class="leave-dialog-wrapper">
        <DialogCloseBtn @click="showLeaveForm = false" />
        <LeaveForm :selected-leave="selectedLeave"
                   :edit-mode="leaveFormEditMode"
                   :view-mode="leaveFormViewMode"
                   :title="leaveFormEditMode ? 'Edit Leave Request' : 'Leave Request'"
                   @reload-leaves="loadLeaves"
                   @close="showLeaveForm = false">
        </LeaveForm>
    </VDialog>
    <VDialog v-model="showDeleteLeaveDialog" width="400">
        <DialogCloseBtn @click="showDeleteLeaveDialog = false" />
        <div class="tw-bg-white tw-rounded-lg  tw-flex tw-flex-col">
            <div class="tw-px-10 tw-py-6 tw-mt-4  tw-flex"><VIcon icon="tabler-trash" class=" tw-text-red-500 tw-mr-3"></VIcon>Are you sure you want to delete this leave request? This cannot be undone.</div>
            <div class="tw-flex tw-justify-end tw-p-4">
                <VBtn @click="deleteLeave">Confirm</VBtn>
            </div>
        </div>
    </VDialog>
    <VDialog v-model="showCancelConfirmationDialog" width="400">
        <DialogCloseBtn @click="showCancelConfirmationDialog = false" />
        <div class="tw-bg-white tw-rounded-lg  tw-flex tw-flex-col">
            <div class="tw-px-10 tw-py-6 tw-mt-4  tw-flex"><VIcon icon="tabler-warning" class=" tw-text-red-500 tw-mr-3"></VIcon>Are you sure you want to CANCEL this leave request? This cannot be undone.</div>
            <div class="tw-flex tw-justify-end tw-p-4">
                <VBtn @click="cancelLeave">Cancel</VBtn>
            </div>
        </div>
    </VDialog-->
</template>

<script>
import moment from "moment";
import { VDataTableServer } from 'vuetify/labs/VDataTable';
import leaveApi from "@/api/leave/leaveApi";
import contractApi from "@/api/contract/contractApi";
import LeaveManagementFilters from "@/pages/Leaves/components/LeaveManagementFilters.vue";
import CreateLeaveButton from "@/pages/Leaves/components/CreateLeaveButton.vue";
import LeaveForm from "@/pages/Leaves/forms/LeaveForm.vue";
import { ref } from "vue";
import ContractsFilters from "@/pages/Contract/components/ContractsFilters.vue"

export default {
    components: {
        ContractsFilters,
        LeaveManagementFilters,
        CreateLeaveButton,
        VDataTableServer,
        LeaveForm
    },

    data() {
        return {
            loadItems: false,
            selectAll: false,
            showLeaveForm: false,
            showDeleteLeaveDialog: false,
            showCancelConfirmationDialog: false,
            leaveFormViewMode: false,
            leaveFormEditMode: false,
            selectedLeave: null,
            selectedItems: [],
            leaves: [],
            contracts: [],
            total: 0,
            pagination: {
                page: 1,
                per_page: 10,
                sortBy: null,
                sortDesc: false,
            },
            orderBy: undefined,
            itemsPerPageOptions: [
                { value: 3, title: '3' },
                { value: 10, title: '10' },
                { value: 50, title: '50' },
                { value: 100, title: '100' },
                { value: 200, title: '200' },
                { value: 500, title: 'All' },
            ],
            headers: [
                {
                    id: 0,
                    title: 'check',
                    key: 'contract_id',
                    align: 'center',
                    sortable: false
                },
                {
                    id: 1,
                    title: 'CONTRACT TITLE',
                    key: 'contract_title',
                    align: 'center',
                    sortable: true
                },
                {
                    id: 2,
                    title: 'FIRST NAME',
                    key: 'employee_first_name',
                    align: 'center',
                    sortable: true
                },
                {
                    id: 3,
                    title: 'LAST NAME',
                    key: 'employee_last_name',
                    align: 'center',
                    sortable: true
                },
                {
                    id: 4,
                    title: 'JOB TITLE',
                    key: 'employee_job_title',
                    align: 'center',
                    sortable: true
                },
                {
                    id: 5,
                    title: 'LOCATION',
                    key: 'location_name',
                    align: 'center',
                    sortable: true
                },
                {
                    id: 6,
                    title: 'ASSIGNEE',
                    key: 'assignee_username',
                    align: 'center',
                    sortable: true
                },
                {
                    id: 7,
                    title: 'LAST UPDATED',
                    key: 'created_at',
                    align: 'center',
                    sortable: true
                },
                {
                    id: 8,
                    title: 'STATUS',
                    key: 'stat',
                    align: 'center',
                    sortable: true
                },
                {
                    id: 9,
                    title: '',
                    key: 'action',
                    align: 'center',
                    sortable: true
                },
            ],
            filters: {
                role: null,
                expiry: null,
                location: null,
                status: null,
                date_start: null,
                date_end: null
            },
        }
    },

    mounted() {
        //this.loadLeaves()
        this.loadContracts()
    },

    methods: {
        updateOptions(options) {
            // page update commented out in original
            this.pagination.sortBy = options.sortBy[0]?.key || null;
            this.pagination.orderBy = options.sortBy[0]?.order;
        },
        toggleAll() {
            if (this.selectAll) {
                this.selectedItems = [...this.contracts]; // Select all items
            } else {
                this.selectedItems = []; // Deselect all
            }
        },
        filterDate(dates) {
            this.filters.date_start = dates.start_date
            this.filters.date_end = dates.end_date
            this.loadContracts()
        },

        filterStatus(status) {
            this.filters.status = status?.name
            this.loadContracts()
        },

        filterLocation(location) {
            this.filters.location = location?.id
            this.loadContracts()
        },

        filterExpiry(expiry) {
            this.filters.expiry = expiry
            this.loadContracts()
        },

        filterRole(role) {
            this.filters.role = role
            this.loadContracts()
        },

        viewTableItem(e, data) {
            this.selectedLeave = data.item
            this.leaveFormEditMode = false
            this.leaveFormViewMode = true
            this.showLeaveForm = true
        },

        viewLeave(leave) {
            this.selectedLeave = leave
            this.leaveFormEditMode = false
            this.leaveFormViewMode = true
            this.showLeaveForm = true
        },

        showCancelConfirmation(leave) {
            this.selectedLeave = leave
            this.showCancelConfirmationDialog = true
        },

        async loadContracts() {
            this.loadItems = true;
            let response = await contractApi.fetchAllContracts({
                page: this.pagination.page,         //ok
                per_page: this.pagination.per_page, //ok
                role: this.filters.role,            //personal
                expiry: this.filters.expiry,        //ok
                location: this.filters.location,    //ok
                status: this.filters.status,        //ok
                date_start: this.filters.date_start,
                date_end: this.filters.date_end,
                type: 'letter_of_offer'
            })
            this.loadItems = false;
            this.contracts = response.contracts.data
            console.log("================================ CONTRACT ============================")
            console.log(this.contracts)
            this.total = response.total
        },

        toggleSort(column) {
            if (this.pagination.sortBy === column.key) {
                this.pagination.sortDesc = !this.pagination.sortDesc;
            } else {
                this.pagination.sortBy = column.key;
                this.pagination.sortDesc = false;
            }
            this.sortItems();
        },

        isSorted(column) {
            return this.pagination.sortBy === column.key;
        },

        sortItems() {
            if (!this.pagination.sortBy) {
                alert("wala")
                return
            }

            /*if(this.pagination.sortBy === 'uploaded') {
                this.pagination.sortBy = 'status'
            }*/

            this.contracts.sort((a, b) => {
                let result = 0
                let aValue = this.getNestedValue(a, this.pagination.sortBy)
                let bValue = this.getNestedValue(b, this.pagination.sortBy)

                // Handle undefined/null/empty string
                if (aValue === undefined || aValue === null || aValue === '') {
                    return this.pagination.sortDesc ? 1 : -1;
                }
                if (bValue === undefined || bValue === null || bValue === '') {
                    return this.pagination.sortDesc ? -1 : 1;
                }

                // Normalize strings
                if (typeof aValue === 'string') aValue = aValue.toLowerCase();
                if (typeof bValue === 'string') bValue = bValue.toLowerCase();

                if (typeof aValue === 'number' && typeof bValue === 'number') {
                    result = aValue - bValue
                } else {
                    if (aValue < bValue) {
                        result = -1
                    } else if (aValue > bValue) {
                        result = 1
                    }
                }
                return this.pagination.sortDesc ? -result : result;
            });
            console.log("SORT")
            console.log(this.contracts)
        },

        getNestedValue(obj, path) {
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
        },

        formatDate(date, format) {
            return moment(date).format(format)
        }
    },

    watch: {
        'pagination.per_page'(newVal, oldVal) {
            console.log('per_page changed:', oldVal, '→', newVal);
            this.loadContracts();
        },
        'pagination.page': {
            handler() {
                this.loadContracts();
            },
        },
    }
}
</script>

<style>
td:is(:nth-child(2), :nth-child(3), :nth-child(4), :nth-child(5), :nth-child(6),:nth-child(6), :nth-child(7), :nth-child(8)) {
    text-align: left !important;
}

.custom-checkbox {
    transform: scale(1.5); /* Increase size */
    cursor: pointer;
}

th.custom-header.align-center {
    color: #333954 !important;
    font-family: Oswald !important;
    font-size: large;
    font-weight: 450 !important;
    padding: 10px 5px !important;
    font-size: 0.95rem;
    font-weight: bold;
    letter-spacing: -0.3px;
}

.align-center {
    text-align: center !important;
    @apply tw-text-[1.3rem];
}

td.v-data-table__td.v-data-table-column--align-center {
    padding: 0px 0px !important;
    font-size: 16px !important;
}

tr.v-data-table__tr:hover {
    @apply tw-bg-gray-200 tw-cursor-pointer
}

.v-pagination__list .text-primary {
    color: #001871 !important;
}

.scrollable-dialog {
    overflow: visible !important;
}

.leave-dialog-wrapper.v-dialog > .v-overlay__content {
    max-height: max-content !important;
    position: absolute;
    top: 30px;
    padding-bottom: 50px;
}

.leave-dialog-wrapper.v-dialog {
    overflow: auto !important;
}

.leave-dialog-wrapper .v-overlay-scroll-blocked:not(html) {
    overflow-y: auto !important;
}

.leave-dialog-wrapper .v-overlay {
    pointer-events: all !important;
}

th, td {
    max-width: 110px !important; /* Adjust width */
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
}

.action_vlist {
    font-size: 12px;
    color: #808080;
    padding: 0px 0px !important;
    margin: 0px 10px !important;
}
</style>
