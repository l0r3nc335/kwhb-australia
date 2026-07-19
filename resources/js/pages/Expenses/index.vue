<template>
    <div class="tw-flex tw-flex-col">
        <div class="tw-text-[2rem] tw-font-bold tw-mb-4">
            Expenses
        </div>
        <div class="tw-flex tw-flex-col tw-p-4 tw-bg-white tw-shadow-lg">
            <div class="tw-flex tw-justify-between ">
                <!-- Filters moved to table, just show button here -->
                <!-- <AddExpenseButton @reload-expenses="loadExpenses" :cost_codes="cost_codes"></AddExpenseButton> -->
            </div>
            <div class="tw-flex ">
                <ExpenseTable 
                    :expenses="expenses" 
                    :pagination="pagination" 
                    :filters="filters" 
                    :cost_codes="cost_codes" 
                    @update:expenses="expenses = $event"
                    v-model:sortBy="sortBy"
                    v-model:sortDesc="sortDesc"
                    @reload-expenses="loadExpenses"
                ></ExpenseTable>
            </div>
        </div>
    </div>
</template>

<script>
import expenseApi from "@/api/expense/expenseApi";
// import AddExpenseButton from "@/pages/Expenses/components/AddExpenseButton.vue";
import ExpenseTable from "@/pages/Expenses/components/ExpenseTable.vue";

export default {
    components: {
        // AddExpenseButton,
        ExpenseTable
    },

    data() {
        return {
            loading: false,
            expenses: [],
            query: null,
            cost_codes: [],
            filters: {
                status: null,
                dateRange: null,
                division: null,
                costCode: null
            },
            pagination: {
                per_page: 10,
                page: 1,
                total: 10
            },
            sortBy: null,
            sortDesc: false,
        }
    },

    async mounted() {
        this.loadExpenses();
        const data = await expenseApi.fetchCodes();
        this.cost_codes = data.codes;
    },

    methods: {
        async loadExpenses() {
            // const data = await expenseApi.fetchExpenses(
            //     this.query, this.filters.dateRange, this.filters.status?.id, this.filters.division?.id, this.filters.costCode?.classId
            // )
            // this.expenses = data.data;
            // if(!this.pagination.page){
            //     this.pagination.page = 1;
            // }
            // this.pagination.total = data.total ?? 0;
            // this.pagination.per_page = data.per_page ?? 10;
            // console.log("Expenses", data)
        },
        onFilterChange(newFilters) {
            this.filters = newFilters
            console.log("$$$$", this.filters)
            this.loadExpenses()
        }
    },

    watch: {
        query() {
            this.loadExpenses()
        },
    }
}
</script>
