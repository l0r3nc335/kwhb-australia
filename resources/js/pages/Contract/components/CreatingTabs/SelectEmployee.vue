<script setup>
import { ref, computed, onMounted } from 'vue';
import contractApi from "@/api/contract/contractApi";

const searchTerm = ref('');
const formData = ref({
  email: '',
  username: '',
  firstName: '',
  lastName: '',
  jobTitle: '',
});

const employees = ref([]);
const isEmployeeSelected = ref(false);

const fetchData = async () => {
  try {
    const response = await contractApi.fetchAllEmployees();
    employees.value = response.data.map(emp => ({
      employeeId: emp.id,
      firstName: emp.first_name,
      lastName: emp.last_name,
      jobTitle: emp.job_title,
      email: emp.work_email,
      locationId: emp.location_id,
    }));
  } catch (error) {
    employees.value = [];
    $toast.error(error.response?.data?.message || 'Something went wrong while fetching employees.');
    console.error('Error fetching data:', error);
  }
};

onMounted(() => {
  fetchData();
});

const filteredEmployees = computed(() => {
  if (!searchTerm.value.trim()) return [];

  const searchTermLower = searchTerm.value.trim().toLowerCase();

  return employees.value.filter(
    emp =>
      emp.firstName.toLowerCase().startsWith(searchTermLower) ||
      emp.lastName.toLowerCase().startsWith(searchTermLower)
  );
});

function selectEmployee(employee) {
  formData.value.firstName = employee.firstName || '';
  formData.value.lastName = employee.lastName || '';
  formData.value.email = employee.email || '';
  formData.value.jobTitle = employee.jobTitle || '';

  isEmployeeSelected.value = true;
  searchTerm.value = ''; 
  emit('employeeSelected', employee);
}

const emit = defineEmits(['employeeSelected']);
</script>

<template>
  <div style="padding: 50px; width: 100%;">
    <h2 class="text-h2 font-weight-medium">Select Employee</h2>
    <p class="mb-0 mt-4">Use the search bar to search for the employee by first or last name.</p>

    <!-- Search Bar -->
    <div class="search-bar-container mt-2 mb-2">
      <input 
        type="text" 
        class="search-bar" 
        placeholder="Search applicants"
        v-model="searchTerm"
      />
    </div>

    <!-- Employee List -->
    <div v-if="searchTerm && filteredEmployees.length > 0" class="employee-list mb-6" style="border: 1px solid gray; border-radius: 2px;">
      <ul>
        <li
          v-for="employee in filteredEmployees"
          :key="employee.employeeId"
          @click="selectEmployee(employee)"
        >
          {{ employee.firstName }} {{ employee.lastName }} ({{ employee.jobTitle }})
        </li>
      </ul>
    </div>

    <!-- No Employees Found Message -->
    <div v-if="searchTerm && filteredEmployees.length === 0">
      <p>No employees found matching the search term.</p>
    </div>

    <!-- Employee Info Form -->
    <div v-if="isEmployeeSelected" class="d-flex" style="border: 1px solid gray; border-left:5px solid orange; padding: 50px;">
      <AppTextField
        v-model="formData.email"
        placeholder="Email"
        label="Email"
        class="mr-4"
      />
      <AppTextField
        v-model="formData.firstName"
        placeholder="First Name"
        label="First Name"
        class="mr-4"
      />
      <AppTextField
        v-model="formData.lastName"
        placeholder="Last Name"
        label="Last Name"
        class="mr-4"
      />
    </div>
  </div>
</template>

<style>
    .search-bar-container {
        margin-top: 15px;
    }

    .search-bar {
        width: 100%;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 16px;
    }
</style>
