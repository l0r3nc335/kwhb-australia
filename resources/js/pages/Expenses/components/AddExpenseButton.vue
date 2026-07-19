<template>
    <div>
        <VBtn prepend-icon="tabler-plus" @click="showResourceForm=!showResourceForm">Submit Expense</VBtn>
        <ExpenseForm :open="showResourceForm" @close="showResourceForm = false" :cost_codes="cost_codes" @reload="$emit('reload-expenses')"></ExpenseForm>
    </div>
</template>

<script>
import ExpenseForm from "@/pages/Expenses/components/ExpenseForm.vue";

export default {
    components: {
        ExpenseForm
    },

    props: {
        category: {
            default: null
        },
        cost_codes: {
            type: Array,
            default: () => []
        }        
    },
    
    mounted() {
    },    
    data() {
        return {
            showResourceForm: false
        }
    },
    watch: {
        cost_codes: {
            handler(newVal) {
            // console.log("cost_codes updated in AddExpenseButton:", newVal)
            },
            immediate: true
        }
    }

    
}
</script>
