<template>
    <div class="tw-flex tw-flex-col py-2">
        <div class="tw-flex tw-flex-col lg:tw-flex-row tw-justify-between">
            <ContractsFilters
                @filter-date="filterDate"
                @filter-expiry="filterExpiry"
                @filter-role="filterRole"
                @filter-location="filterLocation"
                @filter-status="filterStatus">
            </ContractsFilters>
        </div>

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
                                <span style="white-space: nowrap!important; font-size: 18px!important;">{{ item.title }}</span>
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
            <template #item.employee_first_name="{item}">
                <span  style="display: block; margin: 0 auto; width: fit-content;">{{item.employee_first_name}}</span>
            </template>
            <template #item.employee_last_name="{item}">
                <span  style="display: block; margin: 0 auto; width: fit-content;">{{item.employee_last_name}}</span>
            </template>
            <template #item.employee_job_title="{item}">
                <span  style="display: block; margin: 0 auto; width: fit-content;">{{item.employee_job_title}}</span>
            </template>
            <template #item.location_name="{item}">
                <span  style="display: block; margin: 0 auto; width: fit-content;">{{item.location_name}}</span>
            </template>
            <template #item.assignee_username="{item}">
                <span  style="display: block; margin: 0 auto; width: fit-content;">{{item.assignee_username}}</span>
            </template>
            <template #item.created_at="{item}">
                <span v-if="item.updated_at === null" style="display: block; margin: 0 auto; width: fit-content;">{{formatDate(item.created_at, 'DD-MM-YYYY')}}</span>
                <span v-else style="display: block; margin: 0 auto; width: fit-content;">{{formatDate(item.updated_at, 'DD-MM-YYYY')}}</span>
            </template>

            <!-- Status Name Button -->
            <template #item.stat="{item}">
                <template v-if="item.stat === 'pending_0'">
                    <span class="tw-block tw-text-[11px] tw-flex tw-justify-center">0%</span>
                    <span class="tw-block tw-flex tw-justify-center">
                      <div class="tw-w-14 tw-bg-gray-200 tw-rounded tw-h-1">
                        <div class="tw-bg-green-500 tw-h-1 tw-w-1/12 tw-rounded"></div>
                      </div>
                    </span>
                    <span class="tw-block tw-text-[11px]  tw-flex tw-justify-center">Need to sign</span>
                </template>

                <template v-else-if="item.stat === 'pending_50'">
                    <span class="tw-block tw-text-[11px]  tw-flex tw-justify-center">50%</span>
                    <span class="tw-block tw-flex tw-justify-center">
                      <div class="tw-w-14 tw-bg-gray-200 tw-rounded tw-h-1">
                        <div class="tw-bg-green-500 tw-h-1 tw-w-1/2 tw-rounded"></div>
                      </div>
                    </span>
                    <span class="tw-block tw-text-[11px]  tw-flex tw-justify-center">Need to sign</span>
                </template>

                <template v-else>
                    <div
                        :class="getStatusClass(item.stat)"
                        style="max-width: 130px!important;margin: auto!important;"
                    >
                        {{ capitalize(item.stat) }}
                    </div>
                </template>
            </template>
            <!-- 3 dot Action -->
            <template #item.action="{item}">
                <IconBtn v-if="hasPermissions([ /* kwhb.permissions.ui_key */], null, true)">

                    <VIcon icon="tabler-dots-vertical" />

                    <VMenu activator="parent">
                        <VList>
                            <VListItem @click="handleContractDuplicate(item)" class="action_vlist">
                                <span  style="color: black!important;">
                                    Duplicate
                                </span>
                            </VListItem>
                            <VListItem @click="viewLeave(item)" class="action_vlist">
                                <span  style="color: black!important;">
                                    Save as Template
                                </span>
                            </VListItem>
                            <VListItem @click="viewHistory(item)" class="action_vlist">
                                <span  style="color: black!important;">
                                    History
                                </span>
                            </VListItem>
                            <VListItem @click="viewLeave(item)" class="action_vlist">
                                <span  style="color: black!important;">
                                    Transfer of Ownership
                                </span>
                            </VListItem>
                            <VListItem @click="viewLeave(item)" class="action_vlist">
                                 <span  style="color: black!important;">
                                     Resend
                                 </span>
                            </VListItem>
                            <VListItem @click="handleChangeStatus(item, 'cancelled')" class="action_vlist">
                                 <span  style="color: black!important;">
                                    Cancelled
                                 </span>
                            </VListItem>
                            <VListItem @click="handleChangeStatus(item, 'archive')" class="action_vlist">
                                 <span  style="color: black!important;">
                                    Archived
                                 </span>
                            </VListItem>
                            <VListItem @click="editRow(item)" class="action_vlist">
                                 <span  style="color: black!important;">
                                    Edit
                                 </span>
                            </VListItem>
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

    <ContractLetterOfOfferHistory v-model="showHistory" :contractId="localContractId"/>
</template>

<script>
import moment from "moment"
import { VDataTableServer } from 'vuetify/labs/VDataTable'
import contractApi from "@/api/contract/contractApi"
import LeaveManagementFilters from "@/pages/Leaves/components/LeaveManagementFilters.vue"
import CreateLeaveButton from "@/pages/Leaves/components/CreateLeaveButton.vue"
import LeaveForm from "@/pages/Leaves/forms/LeaveForm.vue"
import ContractsFilters from "@/pages/Contract/components/ContractsFilters.vue"
import toast from "@/composable/toast"
import { useAuthStore } from "@/store/authStore"
import ContractLetterOfOfferHistory from "@/pages/Contract/components/History/ContractLetterOfOfferHistory.vue"

export default {
    components: {
        ContractLetterOfOfferHistory,
        ContractsFilters,
        LeaveManagementFilters,
        CreateLeaveButton,
        VDataTableServer,
        LeaveForm
    },
    props: {
        userId: {
            type: null,
            required: false,
        },
        status: {
            type: null,
            required: false,
        },
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
                /*{
                    id: 1,
                    title: 'CONTRACT TITLE',
                    key: 'contract_title',
                    align: 'center',
                    sortable: true
                },*/
                {
                    id: 1,
                    title: 'FIRST NAME',
                    key: 'employee_first_name',
                    align: 'center',
                    sortable: true
                },
                {
                    id: 2,
                    title: 'LAST NAME',
                    key: 'employee_last_name',
                    align: 'center',
                    sortable: true
                },
                {
                    id: 3,
                    title: 'JOB TITLE',
                    key: 'employee_job_title',
                    align: 'center',
                    sortable: true
                },
                {
                    id: 4,
                    title: 'LOCATION',
                    key: 'location_name',
                    align: 'center',
                    sortable: true
                },
                {
                    id: 5,
                    title: 'ASSIGNEE',
                    key: 'assignee_username',
                    align: 'center',
                    sortable: true
                },
                {
                    id: 6,
                    title: 'LAST UPDATED',
                    key: 'created_at',
                    align: 'center',
                    sortable: true
                },
                {
                    id: 7,
                    title: 'STATUS',
                    key: 'stat',
                    align: 'center',
                    sortable: true
                },
                {
                    id: 8,
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
            showHistory: false,
            localContractId: null,
        }
    },

    mounted() {
        this.loadContracts()
    },
    inject: ['rowRecordOfTable'],
    methods: {
        getStatusClass(stat) {
            const base = 'tw-p-1 tw-rounded-lg text-center';
            const map = {
                completed: 'tw-bg-green-100 tw-text-green-500',
                rejected: 'tw-bg-red-100 tw-text-red-500',
                expired: 'tw-bg-red-100 tw-text-red-500',
                archive: 'tw-bg-red-100 tw-text-red-500',
                draft: 'tw-bg-gray-100',
                cancelled: 'tw-bg-red-100 tw-text-red-500',
            };
            return `${map[stat] || ''} ${base}`;
        },
        capitalize(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        },
        async viewTableItem(e, row) {
            this.$emit('editRow', false)
            this.rowRecordOfTable.value = await this.fetchFullRowById(row.item.contract_id)
        },
        async editRow(item) {
            this.rowRecordOfTable.value = await this.fetchFullRowById(item.contract_id)
            this.$emit('editRow', true)
        },
        async fetchFullRowById(id) {
            this.loadItems = true;
            let response = await contractApi.fetchContract({
                id: id,
            })
            //console.log("================================ CONTRACT BY ID ============================")
            //console.log(response)
            this.loadItems = false;
            return response
        },
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

        formatDateToMysqlReady(input) {
            const [dd, mm, yyyy] = input.split('-'); // dd-mm-yyyy

            // Pad month and day to ensure 2-digit format
            const paddedMonth = mm.padStart(2, '0');
            const paddedDay = dd.padStart(2, '0');

            // Return in MySQL format: yyyy-mm-dd
            return `${yyyy}-${paddedMonth}-${paddedDay}`;
        },

        filterExpiry(expiry) {
            this.filters.expiry = expiry !== null ? this.formatDateToMysqlReady(expiry.formatted_expiry_date) : null
            this.loadContracts()
        },

        filterRole(role) {
            this.filters.role = role
            this.loadContracts()
        },

        viewLeave(leave) {
            this.selectedLeave = leave
            this.leaveFormEditMode = false
            this.leaveFormViewMode = true
            this.showLeaveForm = true
        },

        viewHistory(item) {
            this.showHistory = true
            this.localContractId = item.contract_id
        },

        async handleChangeStatus(item, status) {
            try {
                const response = await contractApi.updateContractStatus(
                    item.contract_id,
                    status,
                    useAuthStore().userData.id,
                    useAuthStore().userData.name,
                    'Letter of Offer'
                );
                //await templateApi.updateTemplateStatus(item.contract_id, 'archive');
                toast('success', `A Letter of Offer was set to Archive Status!`);
                await this.loadContracts();
            } catch (error) {
                //toast('error', `Error updating template status. Please try again.`);
            }
        },

        async handleContractDuplicate(item) {
            try {
                const response = await contractApi.duplicateContract(item.contract_id);
                //await templateApi.updateTemplateStatus(item.contract_id, 'archive');
                toast('success', response.message);
                await this.loadContracts();
            } catch (error) {
                //toast('error', `Error updating template status. Please try again.`);
            }
        },

        showCancelConfirmation(leave) {
            this.selectedLeave = leave
            this.showCancelConfirmationDialog = true
        },

        async loadContracts() {
            this.loadItems = true;
            let response = await contractApi.fetchAllContracts({
                user_id: this.userId ?? null,
                page: this.pagination.page,         //ok
                per_page: this.pagination.per_page, //ok
                role: this.filters.role,            //personal
                expiry: this.filters.expiry,        //ok
                location: this.filters.location,    //ok
                status:  this.status ?? this.filters.status,        //ok
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
