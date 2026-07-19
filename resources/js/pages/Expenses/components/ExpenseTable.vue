<template>
    <div class="tw-bg-white tw-w-full">
        <!-- Filters: Now at the top of the table -->
        <div class="tw-flex tw-justify-end roster-filters-wrapper mb-5">
            <AddExpenseButton @reload-expenses="loadExpenses" :cost_codes="cost_codes"></AddExpenseButton>      
        </div>   
        <div class="tw-flex tw-items-end tw-justify-between mb-5 roster-filters-wrapper">
            <!-- Left: Per page select -->
            <div class="app-select me-3 d-flex gap-3">
                <AppSelect
                    :model-value="itemsPerPage"
                    :items="itemsPerPageOptions"
                    style="inline-size: 6.25rem;"
                    @update:model-value="onItemsPerPageChange"
                />
            </div>

            <!-- Right: Filters -->
            <div class="tw-flex tw-items-end tw-gap-4">
                <AppTextField
                    v-model="query"
                    @input="onSearch"
                    class="!tw-grow-0"
                    prepend-inner-icon="tabler-search"
                    placeholder="Search By Name"
                    style="width: 200px"
                />

                <VueDatePicker
                    v-model="internalFilters.dateRange"
                    class="!tw-grow-0"
                    placeholder="Select Date"
                    format="dd/MM/yyyy"
                    :enable-time-picker="false"
                    style="width: 250px;"
                />

                <AppCombobox
                    v-model="internalFilters.status"
                    :items="statuses"
                    clearable
                    placeholder="Status"
                    item-title="name"
                    item-value="id"
                    class="!tw-grow-0"
                    style="width: 150px"
                />

                <AppCombobox
                    v-model="internalFilters.division"
                    :items="divisions"
                    clearable
                    placeholder="Division"
                    item-title="name"
                    item-value="id"
                    class="!tw-grow-0"
                    style="width: 150px"
                />

                <AppCombobox
                    v-model="internalFilters.costCode"
                    :items="cost_codes"
                    clearable
                    placeholder="Cost Codes"
                    item-title="classId"
                    item-value="id"
                    class="!tw-grow-0"
                    style="width: 150px"
                />
            </div>
        </div>

        
        <VDataTableServer
            :items-per-page="pagination.per_page"
            :page="pagination.page"
            :items="expenses"
            :items-length="pagination.total"
            :headers="headers"
            v-model:sort-by="sortBy"
            v-model:sort-desc="sortDesc"
            style="color: #333954 !important; font-family: Arial, Helvetica, sans-serif; font-size: medium;"
        >
            <template #headers="{ headers }">
                <tr>
                    <template v-for="column in headers" :key="column.id">
                        <th
                            v-for="item in column"
                            :key="item.title"
                            :class="`custom-header align-${item.align}`"
                        >
                            <span
                                v-if="item.title != 'Employee' && item.sortable"
                                style="display: inline-flex; align-items: center; cursor: pointer;"
                                @click="toggleSort(item)"
                            >
                                <span style="white-space: nowrap;"><b>{{ item.title }}</b></span>
                                <span v-if="isSorted(item)">
                                    <VIcon v-if="sortDesc" style="font-size: medium;">
                                        tabler-chevron-down
                                    </VIcon>
                                    <VIcon v-else style="font-size: medium;">
                                        tabler-chevron-up
                                    </VIcon>
                                </span>
                                <VIcon v-else style="font-size: medium;">
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

            <template #item.expense_date="{ item }">
                {{ formatDateDateOnly(item.expense_date, 'DD-MM-YYYY') }}
            </template>

            <template #item.user="{ item }">
                <div style="cursor: pointer" @click="viewExpense(item)">
                    {{ item.user.name }}
                </div>
            </template>

            <template #item.amount="{ item }">
                ${{ item.amount }}
            </template>
            <template #item.cost_code="{ item }">
                <div v-if="cost_codes.length > 0">
                    {{ getCostCode(item) }}
                </div>
            </template>
            <template #item.status="{ item }">
                <div>
                    <div
                        v-if="item.status === 1"
                        class="tw-inline-block tw-bg-orange-100 tw-text-orange-600 tw-font-bold tw-py-1 tw-px-4 tw-rounded-lg"
                    >
                        Pending Approval
                    </div>
                    <div
                        v-else-if="item.status === 2"
                        class="tw-inline-block tw-bg-green-100 tw-text-green-600 tw-font-bold tw-py-1 tw-px-4 tw-rounded-lg"
                    >
                        Approved
                    </div>
                    <div
                        v-else-if="item.status === 3"
                        class="tw-inline-block tw-bg-blue-100 tw-text-blue-900 tw-font-bold tw-py-1 tw-px-4 tw-rounded-lg"
                    >
                        Cancelled
                    </div>
                    <div
                        v-else-if="item.status === 4"
                        class="tw-inline-block tw-bg-red-100 tw-text-red-600 tw-font-bold tw-py-1 tw-px-4 tw-rounded-lg"
                    >
                        Rejected
                    </div>
                </div>
            </template>

            <template #item.action="{ item }">
                <IconBtn>
                    <VIcon icon="tabler-dots-vertical" />
                    <VMenu activator="parent">
                        <VList>
                            <VListItem class="tw-cursor-pointer hover:tw-bg-red-100" @click="viewExpense(item)">View</VListItem>
                            <VListItem v-if="canTakeActionOnExpense(item) && item.status !== 2" class="tw-cursor-pointer hover:tw-bg-red-100" @click="approveExpense(item)">Approve</VListItem>
                            <VListItem v-if="canTakeActionOnExpense(item) && item.status !== 3" @click="cancelExpense(item)">Cancel</VListItem>
                            <VListItem v-if="canTakeActionOnExpense(item)  && item.status !== 4" @click="rejectExpense(item)">Reject</VListItem>
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
            </template>
        </VDataTableServer>
        <ExpenseViewForm
            :open="showExpenseViewer"
            :expense="selectedExpense"
            :auth-user="authUser"
            @close="showExpenseViewer = false"
            @submit-review="handleReview"
        />

        <EditExpenseForm :open="showResourceForm" @close="showResourceForm = false" :cost_codes="cost_codes" @reload="$emit('reload-expenses')"></EditExpenseForm>
    </div>
</template>

<script>
import adminSettingApi from "@/api/adminSetting/adminSettingApi";
import expenseApi from "@/api/expense/expenseApi";
import AddExpenseButton from "@/pages/Expenses/components/AddExpenseButton.vue";
import EditExpenseForm from "@/pages/Expenses/components/EditExpenseForm.vue";
import ExpenseViewForm from "@/pages/Expenses/components/ExpenseViewForm.vue";
import dayjs from 'dayjs';
import timezone from 'dayjs/plugin/timezone';
import utc from 'dayjs/plugin/utc';
import _ from "lodash";
import { VDataTableServer } from "vuetify/labs/VDataTable";

dayjs.extend(utc)
dayjs.extend(timezone)

export default {
    components: {
        VDataTableServer,
        ExpenseViewForm,
        EditExpenseForm,
        AddExpenseButton
    },
    props: {
        expenses: { required: true },
        perPage: { required: false, default: 10 },
        pagination: { type: Object, required: true },
        cost_codes: { type: Array, default: () => [] },
        search: { type: String, default: '' },
        itemsPerPageOptions: { type: Array, default: () => [10, 25, 50, 100, 200, "All"] }
    },
    data() {
        return {
            query: null,
            internalFilters: {
                status: null,
                dateRange: null,
                division: null,
                costCode: null
            },
            statuses: [
                { id: 1, name: "Pending Approval" },
                { id: 2, name: "Approved" },
                { id: 3, name: "Cancelled" },
                { id: 4, name: "Rejected" }
            ],
            divisions: [],
            headers: [
                { id: 0, title: "Submitted By ", key: "user", sortable: true },
                { id: 1, title: "Expense Date", key: "expense_date", sortable: true },
                { id: 2, title: "Reason", key: "reason", sortable: false },
                { id: 4, title: "Amount", key: "amount", sortable: true },
                { id: 5, title: "Cost Code", key: "cost_code", sortable: true },
                { id: 6, title: "Status", key: "status", sortable: true },
                { id: 7, title: "", key: "action", sortable: false }
            ],
            sortDesc: false,
            sortBy: null,
            showExpenseViewer: false,
            selectedExpense: null,
            showResourceForm: false,
            itemsPerPage: this.pagination.per_page
        };
    },
    async mounted() {
        this.divisions = await adminSettingApi.getDivisions();
        this.loadResources();
    },
    watch: {
        internalFilters: {
            handler: _.debounce(function () {
                this.loadResources();
            }, 500),
            deep: true
        },
        query: {
            handler: _.debounce(function () {
                this.loadResources();
            }, 500),
            immediate: false
        },
        pagination: {
            handler() {
                this.loadResources();
            },
            deep: true
        },
        sortBy() {
            this.loadResources();
        },
        sortDesc() {
            this.loadResources();
        }
    },
    methods: {
        formatDateDateOnly(dateStr) {
            if (!dateStr) return '-'
            return dayjs(dateStr).tz('Australia/Sydney').format('DD-MM-YYYY')
        },        
        onSearch: _.debounce(function () {
            this.loadResources();
        }, 500),
        async handleReview({ action, comment, expenseId }) {
            const status = action === "approve" ? 2 : 4;
            await expenseApi.updateExpense(expenseId, status, comment);
            await this.loadResources();
        },
        getCostCode(item) {
            const index = this.cost_codes.findIndex(code => code.classId === item.cost_code);
            if (index !== -1) {
                return this.cost_codes[index].classId;
            } else {
                return "—";
            }
        },
        canTakeActionOnExpense(item, action = "approve") {
            return true;
        },
        async onItemsPerPageChange(newVal) {
            this.itemsPerPage = newVal;
            this.$emit('update:pagination', { ...this.pagination, per_page: newVal, page: 1 });
            this.pagination.page = 1;
            this.loadResources();
        },
        async loadResources() {
            if (!this.pagination.page) {
                this.pagination.page = 1;
            }
            const data = await expenseApi.fetchExpensesWithPagination(
                this.query,
                this.internalFilters.dateRange,
                this.internalFilters.status?.id,
                this.internalFilters.division?.id,
                this.internalFilters.costCode?.classId,
                this.pagination.page,
                this.sortBy,
                this.sortDesc,
                this.itemsPerPage
            );
            this.pagination.total = data.total ?? 0;
            this.pagination.per_page = data.per_page ?? 10;
            this.$emit("update:expenses", data.data);
        },
        generateRangeText() {
            let pageNumber = this.pagination.page;
            let totalNumberOfRows = this.pagination.total;
            let rowsPerPage = this.pagination.per_page;
            const startIndex = (this.pagination.page - 1) * this.pagination.per_page + 1;
            let endIndex = this.pagination.page * this.pagination.per_page;
            if (endIndex > this.pagination.total) {
                endIndex = this.pagination.total;
            }
            const rowsOnLastPage = totalNumberOfRows % rowsPerPage || rowsPerPage;
            let rangeText;
            if (totalNumberOfRows === 0) {
                rangeText = "No entries";
            } else if (rowsPerPage === -1) {
                rangeText = `Showing all of ${totalNumberOfRows} entries`;
            } else if (pageNumber === 1 && endIndex === totalNumberOfRows) {
                rangeText = `Showing all of ${totalNumberOfRows} entries`;
            } else if (pageNumber === 1) {
                rangeText = `Showing ${startIndex} to ${endIndex} of ${totalNumberOfRows} entries`;
            } else if (endIndex === totalNumberOfRows) {
                if (rowsOnLastPage === 1) {
                    rangeText = `Showing last entry of ${totalNumberOfRows} entries`;
                } else {
                    rangeText = `Showing ${startIndex} to ${endIndex} of ${totalNumberOfRows} entries`;
                }
            } else {
                rangeText = `Showing ${startIndex} to ${endIndex} of ${totalNumberOfRows} entries`;
            }
            return rangeText;
        },
        toggleSort(column) {
            if (this.sortBy === column.key) {
                this.sortDesc = !this.sortDesc;
            } else {
                this.sortBy = column.key;
                this.sortDesc = false;
            }
        },
        isSorted(column) {
            return this.sortBy === column.key;
        },
        formatDate(date, format) {
            if (!date) return "";
            const d = new Date(date);
            const pad = n => (n < 10 ? "0" + n : n);
            return `${pad(d.getDate())}-${pad(d.getMonth() + 1)}-${d.getFullYear()}`;
        },
        async approveExpense(item) {
            await expenseApi.updateExpense(item.id, 2);
            await this.loadResources();
        },
        async cancelExpense(item) {
            await expenseApi.updateExpense(item.id, 3);
            await this.loadResources();
        },
        async rejectExpense(item) {
            await expenseApi.updateExpense(item.id, 4);
            await this.loadResources();
        },
        async archiveExpense(item) {
            await expenseApi.updateExpense(item.id, 5);
            await this.loadResources();
        },
        async viewExpense(item) {
            this.selectedExpense = null;
            this.showExpenseViewer = false;
            this.$nextTick(() => {
                this.selectedExpense = { ...item };
                this.showExpenseViewer = true;
            });
        }
    }
};
</script>

<style>
th b{
    font-size: medium !important;
    font-family: Oswald;
    color: #333954 !important;
    font-weight: 400;        
}
</style>
