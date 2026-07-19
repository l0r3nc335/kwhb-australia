<template>
        <AppCombobox :loading="loading"
                    :label="label"
                     v-model="selected"
                     :items="employees"
                     :multiple="multiple"
                     item-title="name"
                     item-value="user_id"
                     placeholder="Select Employee"
                     prepend-inner-icon="tabler-users"
                     class="tw-w-full"
                     clearable>
        </AppCombobox>
</template>

<script>
    import employeeApi from "@/api/employee/employeeApi";

    export default {
        emits: ['selected'],

        props: {
            label: {
                default: 'Select Employee'
            },

            multiple: {
                type: Boolean,
                default: false
            }
        },

        data() {
            return {
                loading: false,
                employees: [],
                selected: null
            }
        },

        mounted() {
            this.loadEmployees()
        },

        methods: {
            async loadEmployees() {
                this.loading = true
                this.employees = await employeeApi.fetchAllEmployees()
                this.loading = false
            },
        },

        watch: {
            selected() {
                this.$emit('selected', this.selected)
            }
        }
    }
</script>
