<template>
    <div class="d-flex" style="justify-content: space-between; margin-top: 20px;">
        <div class="__left_menu">

        </div>

        <div class="__right_menu">
            <div class="__content_wrapper tw-mt-16" style="max-width: 800px">
                <h2 class="text-h3 font-weight-bold">Select Employee</h2>
                <p class="mb-6 __global_text_font">Use the search bar to search for the employee by first or last name.</p>

                <div class="search-bar-container mt-2 mb-10 relative w-full">
                    <!-- Magnifying Glass Icon -->
                    <v-icon class="__input_search_magnifier tw-absolute">mdi-magnify</v-icon>

                    <!-- Input -->
                    <input
                        type="text"
                        class="search-bar pl-10 w-full py-2 px-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Search applicants"
                        v-model="searchTerm"
                        :disabled="this.selectedEmployee !== null"
                    />
                </div>

                <!-- Employee List -->
                <div v-if="searchTerm && filteredEmployees.length > 0" class="employee-list mb-6" style="border: 1px solid #9ca3af; border-radius: 2px; padding: 1px 10px; max-height: 300px!important; overflow-y: scroll">
                    <ul style="padding: 10px 0!important;">
                        <li
                            v-for="employee in filteredEmployees"
                            :key="employee.employeeId"
                            @click="selectEmployee(employee)"
                            class="__global_text_font __space_between"
                            style="padding: 10px 15px!important;"
                        >
                            <span>{{ employee.firstName }} {{ employee.lastName }}</span>
                            <span>({{ employee.jobTitle }})</span>
                        </li>
                    </ul>
                </div>

                <!-- No Employees Found Message -->
                <div v-if="searchTerm && filteredEmployees.length === 0">
                    <p class="__global_text_font">No employees found matching the search term.</p>
                </div>

                <!-- Employee Info Form -->
                <div v-if="isEmployeeSelected" class="d-flex __employee_info_form __global_text_font">
                    <AppTextField
                        v-model="formData.email"
                        placeholder="Email"
                        label="Email"
                        class="mr-4"
                        disabled
                    />
                    <AppTextField
                        v-model="formData.firstName"
                        placeholder="First Name"
                        label="First Name"
                        class="mr-4"
                        disabled
                    />
                    <AppTextField
                        v-model="formData.lastName"
                        placeholder="Last Name"
                        label="Last Name"
                        class="mr-4"
                        disabled
                    />
                    <v-icon
                        class="__input_trash"
                        @click="handleUnselectEmployee"
                        style="cursor: pointer;"
                    >
                        mdi-trash
                    </v-icon>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import contractApi from "@/api/contract/contractApi";
import { defineComponent } from "vue";
import toast from '@/composable/toast.js';

export default defineComponent({
    name: 'SelectEmployee',
    components: { },
    props: {
        selectedEmployee: {
            type: null,
            required: false,
            default: null
        },
    },
    data() {
        return {
            searchTerm: '',
            formData: {
                email: '',
                username: '',
                firstName: '',
                lastName: '',
                jobTitle: '',
                employeeData: []
            },
            employees: [],
            isEmployeeSelected: false,
        };
    },
    methods: {
        async fetchData() {
            try {
                const response = await contractApi.fetchAllEmployees();
                this.employees = response.data.map(emp => ({
                    employeeId: emp.id,
                    firstName: emp.first_name,
                    lastName: emp.last_name,
                    jobTitle: emp.job_title,
                    email: emp.work_email,
                    locationId: emp.location_id,
                    completeEmployeeData: emp,
                }));
            } catch (error) {
                this.employees = [];
                this.$toast?.error?.(error.response?.data?.message || 'Something went wrong while fetching employees.');
                console.error('Error fetching data:', error);
            }
        },
        autoSelectEmployee(employee) {
            this.formData.firstName = employee.first_name || '';
            this.formData.lastName = employee.last_name || '';
            this.formData.email = employee.personal_email || '';
            this.formData.jobTitle = employee.job_title || '';
            this.formData.employeeData = employee
            this.isEmployeeSelected = true;
            this.searchTerm = '';

            this.$emit('employeeSelected', this.formData);
        },
        selectEmployee(employee) {
            this.formData.firstName = employee.firstName || '';
            this.formData.lastName = employee.lastName || '';
            this.formData.email = employee.email || '';
            this.formData.jobTitle = employee.jobTitle || '';
            this.formData.employeeData = employee.completeEmployeeData
            this.isEmployeeSelected = true;
            this.searchTerm = '';
            if(employee.email === null){
                this.$toast?.info?.('The selected employee does not have email in the employee profile.', {duration: 5000});
            }
            this.$emit('employeeSelected', this.formData);
        },
        handleUnselectEmployee(){
            if(this.selectedEmployee === null) {
                this.formData.firstName = '';
                this.formData.lastName = '';
                this.formData.email = '';
                this.formData.jobTitle = '';

                this.isEmployeeSelected = false;
                this.searchTerm = '';
                this.$emit('employeeSelected', null);
            }
        }
    },
    computed: {
        filteredEmployees() {
            if (!this.searchTerm.trim()) return [];

            const searchTermLower = this.searchTerm.trim().toLowerCase();

            return this.employees.filter(emp =>
                emp.firstName.toLowerCase().startsWith(searchTermLower) ||
                emp.lastName.toLowerCase().startsWith(searchTermLower)
            );
        }
    },
    mounted() {
        this.fetchData();
        if(this.selectedEmployee !== null){
            this.autoSelectEmployee(this.selectedEmployee)
        }
    }
})
</script>

<style>
.v-toast {
    z-index: 99999 !important;
}
.__space_between {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.__left_menu {
    /* border: 1px solid red; */
    width: 20%;
}

.__right_menu {
    width: 80%;
    min-height: 500px!important;
}

.__employee_info_form {
    border: 1px solid #D3DDED;
    border-left:5px solid orangered;
    padding: 25px;
    border-radius: 5px;
}

.__input_search_magnifier {
    color: #D0D3D9;
    font-size: 28px;
    display: inline-block;
    margin: 8px 8px;
}

.__input_trash {
    color: #D0D3D9;
    font-size: 28px;
    display: inline-block;
    margin: 30px 8px;
}

.__global_text_font {
    font: 16px Arial, Helvetica, sans-serif;
}

.v-label {
    color: black!important;
}

.employee-list li {
    cursor: pointer; /* so mouse looks clickable */
}

.employee-list li:hover {
    background-color: #f3f4f6; /* light gray (Tailwind’s gray-100) */
    border-radius: 5px!important;
}
</style>
