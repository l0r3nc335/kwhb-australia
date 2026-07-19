<script setup>
import { ref, computed, onMounted } from 'vue';
import contractApi from "@/api/contract/contractApi";

const props = defineProps({
  isDialogVisible: {
    type: Boolean,
    required: true,
  },
});

const emit = defineEmits(['update:isDialogVisible', 'employeeSelected']);

const dialogVisibleUpdate = (val) => {
  emit('update:isDialogVisible', val);
};

const searchTerm = ref('');
const formData = ref({
  email: '',
  username: '',
  firstName: '',
  lastName: '',
  jobTitle: '',
  profileImage: null,
});

const employees = ref([]);

const fetchData = async () => {
  try {
    const response = await contractApi.fetchAllEmployees();
    employees.value = response.data.map((emp) => ({
      employeeId: emp.id,
      firstName: emp.first_name,
      lastName: emp.last_name,
      jobTitle: emp.job_title,
      email: emp.work_email,
      profileImage: emp.profile_image,
    }));
  } catch (error) {
    employees.value = [];
    $toast.error(
      error.response?.data?.message || 'Something went wrong while fetching employees.'
    );
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
    (emp) =>
      emp.firstName.toLowerCase().startsWith(searchTermLower) ||
      emp.lastName.toLowerCase().startsWith(searchTermLower)
  );
});

const selectEmployee = (employee) => {
  formData.value.firstName = employee.firstName || '';
  formData.value.lastName = employee.lastName || '';
  formData.value.email = employee.email || '';
  formData.value.jobTitle = employee.jobTitle || '';
  formData.value.profileImage = employee.profileImage || null;
  emit('employeeSelected', employee);
};
</script>

<template>
  <VDialog
    :model-value="isDialogVisible"
    max-width="740"
    @update:model-value="dialogVisibleUpdate"
  >
    <!-- Dialog Header -->
    <!-- <VCardTitle>
      <div style="display: flex; justify-content: space-between;">
        <span>Select Employee</span>
        <VBtn icon @click="dialogVisibleUpdate(false)">
          <VIcon>mdi-close</VIcon>
        </VBtn>
      </div>
    </VCardTitle> -->
    <DialogCloseBtn @click="$emit('update:isDialogVisible', false)" />

    <VCard class="refer-and-earn-dialog">
      <VCardText class="px-5 px-sm-16 pt-16 pb-10">
        <div style="padding: 50px; width: 100%;">
          <h2 class="text-h2 font-weight-medium">Select Employee</h2>
          <p class="mb-0 mt-4">Use the search bar to search for the employee by first or last name.</p>

          <!-- Search Bar -->
          <div class="search-bar-container mt-2 mb-2">
            <input
              type="text"
              class="search-bar"
              placeholder="Search employees"
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
          <div v-else-if="searchTerm">
            <p>No employees found matching the search term.</p>
          </div>

          <!-- Selected Employee Form -->
          <div
            v-if="formData.firstName"
            class="d-flex"
            style="border: 1px solid gray; border-left: 5px solid orange; padding: 50px;"
          >
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
      </VCardText>
    </VCard>
  </VDialog>
</template>


<style lang="scss">
.refer-link-input {
  .v-field--appended {
    padding-inline-end: 0;
  }

  .v-field__append-inner {
    padding-block-start: 0.125rem;
  }
}
</style>
