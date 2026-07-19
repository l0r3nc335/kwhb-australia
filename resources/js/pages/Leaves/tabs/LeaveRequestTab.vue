<template>
    <div class="tw-flex tw-flex-col py-2">
        <div class="tw-flex tw-flex-col lg:tw-flex-row tw-justify-between">
            <LeaveManagementFilters @query="filterName"
                                    @filter-type="filterType"
                                    @filter-division="filterDivision"
                                    @filter-status="filterStatus"
                                    @filter-location="filterLocation"
                                    @filter-date="filterDate">
            </LeaveManagementFilters>
            <div class="tw-flex tw-justify-end">
                <CreateLeaveButton request-permission="leave_management_leave_request"
                                   @reload-leaves="loadLeaves"
                                   v-if="hasPermissions('people.leave_management_apply_wizard.create')">
                </CreateLeaveButton>
            </div>
        </div>
        <VDataTableServer :items="leaves"
                          :headers="headers"
                          :items-per-page="pagination.per_page"
                          :items-length="total"
                          :page="pagination.page"
                          @click:row="viewTableItem"
                          class="tw-mt-12" style="color: #333954 !important; font-family: Arial, Helvetica, sans-serif; font-size: medium;">
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
                      v-if="item.title != '' && item.sortable"
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
            <template #item.total="{item}">
                {{item.total}} Hours
            </template>
            <template #item.approvers="{item}">
                <div v-for="i in item.approvers" class="tw-text-sm tw-px-2">
                    {{i.name}}
                </div>
            </template>
            <template #item.start_date="{item}">
                {{formatDate(item.start_date, 'DD/MM/YYYY')}}
            </template>
            <template #item.end_date="{item}">
                {{formatDate(item.end_date, 'DD/MM/YYYY')}}
            </template>
            <template #item.updated_at="{item}">
                {{formatDate(item.updated_at, 'DD/MM/YYYY')}}
            </template>
            <template #item.status_name="{item}">
                <template v-if="item.status_name === 'Pending Approval'">
                    <div v-if="item.is_endorsed" class="tw-flex tw-flex-col tw-text-[.8rem]">
                        <div class="tw-flex tw-justify-center tw-mb-1">
                            50%
                        </div>
                        <div class="tw-flex tw-px-2">
                            <div class="tw-flex-1 tw-bg-green-500 tw-rounded-l-lg" style="height: 4px"></div>
                            <div class="tw-flex-1 tw-bg-gray-300 tw-rounded-r-lg" style="height: 4px"></div>
                        </div>
                        <div class="tw-flex tw-justify-center tw-mb-2 tw-mt-1">
                            Endorsed
                        </div>
                    </div>
                    <div v-else class="tw-relative tw-bg-amber-100 tw-text-amber-500 tw-font-bold tw-py-1 tw-px-4 tw-rounded-lg">
                        {{item.status_name}}
                        <span v-if="item?.request_exemption"
                              class="tw-bg-white tw-text-[.6rem] tw-rounded-lg tw-shadow tw-text-black tw-px-1"
                              style="position: absolute;top: -10px;right: 0; font-family: system-ui" >
                            Exemption Request
                        </span>
                    </div>
                </template>

                <div v-else-if="item.status_name === 'Approved'" class="tw-bg-green-100 tw-text-green-500 tw-font-bold tw-p-1 tw-rounded-lg">
                    {{item.status_name}}
                </div>
                <div v-else-if="item.status_name === 'Declined'" class="tw-bg-red-100 tw-text-red-500 tw-font-bold tw-p-1 tw-rounded-lg">
                    {{item.status_name}}
                </div>
                <div v-else-if="item.status_name === 'Cancelled'" style="color: #081971; background-color: #08197136" class="tw-text-blue-700 tw-font-bold tw-p-1 tw-rounded-lg">
                    {{item.status_name}}
                </div>
                <div v-else>
                    {{item.status_name}}
                </div>
            </template>
            <template #item.action="{item}">
                <IconBtn v-if="hasPermissions([
                    'people.leave_management_leave_request.view',
                    'people.leave_management_leave_request.update',
                    'people.leave_management_leave_request.delete',
                ], null, true)">
                    <VIcon icon="tabler-dots-vertical" />
                    <VMenu
                        activator="parent"
                    >
                        <VList>
                            <VListItem v-if="hasPermissions('people.leave_management_leave_request.approve', item.employee) && ((!inRoles(['CEO', 'General Manager']) && !item.is_endorsed)|| (inRoles(['CEO', 'General Manager']) && item.status != 1))" class="tw-cursor-pointer hover:tw-bg-green-100" @click="approveLeave(item)">
                                <span v-if="inRoles(['CEO', 'General Manager'])">
                                    Approve {{item.is_approved}}
                                </span>
                                <span v-else>
                                    Endorse
                                </span>
                            </VListItem>
                            <VListItem v-if="hasPermissions('people.leave_management_leave_request.view', item.employee) &&  item.status != 1" class="tw-cursor-pointer hover:tw-bg-green-100" @click="viewLeave(item)">View</VListItem>
                            <VListItem v-if="hasPermissions('people.leave_management_leave_request.update', item.employee) && item.status != 1" class="tw-cursor-pointer hover:tw-bg-blue-100" @click="editLeave(item)">Edit</VListItem>
                            <VListItem v-if="hasPermissions('people.leave_management_leave_request.delete', item.employee) && item.status != 1" class="tw-cursor-pointer hover:tw-bg-red-100" @click="showDeleteLeaveConfirmation(item)">Delete</VListItem>
                            <VListItem v-if="item.status == 1" class="tw-cursor-pointer hover:tw-bg-red-100" @click="showCancelConfirmation(item)">Cancel</VListItem>
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
    <VDialog v-model="showLeaveForm" width="900" class="leave-dialog-wrapper">
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
    </VDialog>
</template>

<script>
    import leaveApi from "@/api/leave/leaveApi";
import CreateLeaveButton from "@/pages/Leaves/components/CreateLeaveButton.vue";
import LeaveManagementFilters from "@/pages/Leaves/components/LeaveManagementFilters.vue";
import LeaveForm from "@/pages/Leaves/forms/LeaveForm.vue";
import moment from "moment";
import { VDataTableServer } from 'vuetify/labs/VDataTable';

    export default {
        components: {
            LeaveManagementFilters,
            CreateLeaveButton,
            VDataTableServer,
            LeaveForm
        },

        data() {
            return {
                showLeaveForm: false,
                showDeleteLeaveDialog: false,
                showCancelConfirmationDialog: false,
                leaveFormViewMode: false,
                leaveFormEditMode: false,
                selectedLeave: null,
                leaves: [],
                total: 0,
                pagination: {
                    page: 1,
                    per_page: 10,
                    sortBy: [''],
                    sortDesc: [false],
                },
                headers: [
                    {
                        id: 0,
                        title: 'START DATE',
                        key: 'start_date',
                        align: 'center',
                        sortable: true
                    },
                    {
                        id: 1,
                        title: 'END DATE',
                        key: 'end_date',
                        align: 'center',
                        sortable: true
                    },
                    {
                        id: 2,
                        title: 'FULL NAME',
                        key: 'name',
                        align: 'center',
                        sortable: true
                    },
                    {
                        id: 3,
                        title: 'Leave Type',
                        key: 'leave_type_name',
                        align: 'center',
                        sortable: true
                    },
                    {
                        id: 4,
                        title: 'Duration',
                        key: 'total',
                        align: 'center',
                        sortable: true
                    },
                    {
                        id: 5,
                        title: 'Assigned To',
                        key: 'approvers',
                        align: 'center',
                        sortable: true
                    },
                    {
                        id: 6,
                        title: 'Last Updated',
                        key: 'updated_at',
                        align: 'center',
                        sortable: true
                    },
                    {
                        id: 7,
                        title: 'Status',
                        key: 'status_name',
                        align: 'center',
                        sortable: true
                    },
                    {
                        id: 7,
                        title: '',
                        key: 'action',
                        align: 'center',
                        sortable: true
                    },
                ],
                filters: {
                    query: null,
                    type: null,
                    division: null,
                    location: null,
                    status: null,
                    date_start: null,
                    date_end: null
                },
            }
        },

        mounted() {
            this.loadLeaves()
        },

        methods: {
            filterDate(dates) {
                this.filters.date_start = dates.start
                this.filters.date_end = dates.end
                this.loadLeaves()
            },

            filterStatus(status) {
                this.filters.status = status?.id
                this.loadLeaves()
            },

            filterLocation(location) {
                this.filters.location = location?.id
                this.loadLeaves()
            },

            filterDivision(division) {
                this.filters.division = division?.id
                this.loadLeaves()
            },

            filterType(type) {
                this.filters.type = type?.id
                this.loadLeaves()
            },

            filterName(name) {
                this.filters.query = name
                this.loadLeaves()
            },

            async approveLeave(leave) {
                let loader  = this.$loading.show({
                    container: this.$refs.leaveForm
                })

                let form = {
                    batch: leave.batch,
                    user_id: leave.user_id,
                    type: leave.type
                }

                await leaveApi.leaveApprove(form).then(res => {
                    loader.hide()
                    this.$toast.success('Success!')
                    this.loadLeaves()
                }).catch(err => {
                    loader.hide()
                    this.$toast.error('Something went wrong while approving the leave request.')
                    console.log(err.data)
                    this.$emit('close')
                })
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

            async deleteLeave() {
                await leaveApi.deleteLeave(this.selectedLeave.user_id, this.selectedLeave.batch)
                this.loadLeaves()
                this.showDeleteLeaveDialog = false
            },

            async cancelLeave() {
                await leaveApi.cancelLeave(this.selectedLeave.user_id, this.selectedLeave.batch)
                this.loadLeaves()
                this.showCancelConfirmationDialog = false
            },

            editLeave(leave) {
                this.selectedLeave = leave
                this.leaveFormEditMode = true
                this.leaveFormViewMode = false
                this.showLeaveForm = true
            },

            showCancelConfirmation(leave) {
                this.selectedLeave = leave
                this.showCancelConfirmationDialog = true
            },

            showDeleteLeaveConfirmation(leave) {
                this.selectedLeave = leave
                this.showDeleteLeaveDialog = true
            },

            async loadLeaves() {
                let response = await leaveApi.fetchLeaves({
                page: this.pagination.page,
                per_page: this.pagination.per_page,
                query: this.filters.query,
                type: this.filters.type,
                division: this.filters.division,
                location: this.filters.location,
                status: this.filters.status,
                date_start: this.filters.date_start,
                date_end: this.filters.date_end
                })
                this.leaves = response.leaves.data
                this.total = response.total
            },

            isSorted(item) {

            },

            formatDate(date, format) {
                return moment(date).format(format)
            }
        },

        watch: {
            pagination: {
                handler() {
                    this.loadLeaves()
                },
                deep: true
            }
        }
    }
</script>

<style >
    th.custom-header.align-center {
        color: #333954 !important;
        font-family: Oswald !important;
        font-size: large;
        font-weight: 400 !important;
        padding: 20px 10px !important;
        font-size: 1.2rem;
    }

    .align-center {
        text-align: center !important;
        @apply tw-text-[1.3rem]
    }

    td.v-data-table__td.v-data-table-column--align-center {
        padding: 24px 10px !important;
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
</style>
