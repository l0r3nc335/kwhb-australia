<script setup>
import contractApi from "@/api/contract/contractApi";
import { ref, onMounted, computed } from "vue";
import { VDataTable } from "vuetify/labs/VDataTable";
import { useContractStore } from "@/store/contractStore";
import moment from "moment";

const date = ref("");
const expiryDate = ref("");
const sortDesc = ref(false);
const sortBy = ref(null);
const items = ref([]);
const statusFilter = ref(null);

const status = ["Pending", "Expired", "Rejected", "Completed"];

const contractStore = useContractStore();

const fetchData = async () => {
  try {
    const response = await contractApi.fetchAllContracts();
    items.value = response;
    contractStore.setContract(response);
  } catch (error) {
    console.error("Error fetching data:", error);
  }
};

onMounted(() => {
  fetchData();
});

const headers = [
  { id: 0, title: "", key: "id", align: "center", sortable: false },
  { id: 1, title: "Contract Title", key: "contractTitle", align: "center", sortable: true },
  { id: 2, title: "First Name", key: "firstName", align: "center", sortable: true },
  { id: 3, title: "Last Name", key: "lastName", align: "center", sortable: true },
  { id: 4, title: "Job Title", key: "jobTitle", align: "center", sortable: true },
  { id: 5, title: "Location", key: "location", align: "center", sortable: true },
  { id: 6, title: "Assigned To", key: "assignedTo", align: "center", sortable: true },
  { id: 7, title: "Last Updated", key: "lastUpdated", align: "center", sortable: true },
  { id: 8, title: "Status", key: "status", align: "center", sortable: true },
];

const getProgress = (status) => {
  if (status === "Pending_0") return "0";
  if (status === "Pending_50") return "50";
};

const toggleSort = (column) => {
  if (sortBy.value === column.key) {
    sortDesc.value = !sortDesc.value;
  } else {
    sortBy.value = column.key;
    sortDesc.value = false;
  }
  sortItems();
};

const isSorted = (column) => sortBy.value === column.key;

const sortItems = () => {
  if (!sortBy.value) {
    return;
  }

  items.value.sort((a, b) => {
    let aValue = a[sortBy.value];
    let bValue = b[sortBy.value];

    if (aValue === undefined || aValue === null || aValue === "") {
      return sortDesc.value ? 1 : -1;
    }
    if (bValue === undefined || bValue === null || bValue === "") {
      return sortDesc.value ? -1 : 1;
    }

    if (typeof aValue === "string") {
      aValue = aValue.toLowerCase();
    }
    if (typeof bValue === "string") {
      bValue = bValue.toLowerCase();
    }

    let result = 0;
    if (typeof aValue === "number" && typeof bValue === "number") {
      result = aValue - bValue;
    } else {
      if (aValue < bValue) {
        result = -1;
      } else if (aValue > bValue) {
        result = 1;
      }
    }

    return sortDesc.value ? -result : result;
  });
};

const filteredItems = computed(() => {
  let filtered = items.value;

  if (statusFilter.value) {
    if (statusFilter.value === "Pending") {
      filtered = filtered.filter(item => item.status === "Pending_0" || item.status === "Pending_50");
    } else {
      filtered = filtered.filter(item => item.status === statusFilter.value);
    }
  }

  if (date.value) {
    const selectedDate = moment(date.value).startOf('day');
    filtered = filtered.filter(item => {
      const lastUpdatedDate = moment(item.lastUpdated);
      return lastUpdatedDate.isSame(selectedDate, 'day');
    });
  }

  return filtered;
});
</script>

<template>
  <div class="page-content-container">
    <section>
      <VCard title="Search Filter" class="mb-6">
        <VCardText>
          <VRow>
            <!-- Filters -->
            <VCol cols="6" sm="4">
              <AppDateTimePicker v-model="date" placeholder="Date" clearable />
            </VCol>
            <!-- <VCol cols="6" sm="2">
              <AppSelect placeholder="Role" clearable />
            </VCol>
            <VCol cols="6" sm="2">
              <AppSelect placeholder="Location" clearable />
            </VCol>
            <VCol cols="6" sm="2">
              <AppDateTimePicker v-model="expiryDate" placeholder="Expiry" clearable />
            </VCol> -->
            <VCol cols="6" sm="4">
              <AppSelect v-model="statusFilter" :items="status" placeholder="Status" clearable />
            </VCol>
          </VRow>
        </VCardText>
      </VCard>
    </section>

    <VDataTable
      :items="filteredItems"
      :headers="headers"
      :items-per-page="5"
      style="color: #333954; font-family: Arial, Helvetica, sans-serif; font-size: medium;"
    >
      <template #item.status="{ item }">
        <div>
          <div style="font-size: 10px;" v-if="item.status === 'Pending_0' || item.status === 'Pending_50'">
            {{ getProgress(item.status) !== null ? getProgress(item.status) + '%' : 'N/A' }}
          </div>

          <VProgressLinear
            v-if="item.status === 'Pending_0' || item.status === 'Pending_50'"
            :height="6"
            :model-value="getProgress(item.status)"
            color="success"
            rounded
          />
          <p style="font-size: 10px;" v-if="item.status === 'Pending_0' || item.status === 'Pending_50'">Need to sign</p>

          <VChip
            label
            :color="item.status === 'Completed' ? 'success' : 'error'"
            v-if="item.status !== 'Pending_0' && item.status !== 'Pending_50'"
          >
            {{ item.status }}
          </VChip>
        </div>
      </template>
    </VDataTable>
  </div>
</template>

<style>
.v-table {
  --v-table-row-height: 5px;
}
</style>
