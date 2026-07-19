<script setup>
    import contractApi from "@/api/contract/contractApi";
    import { ref, onMounted } from 'vue';
    import { VDataTable } from 'vuetify/labs/VDataTable';
    import { useContractStore } from "@/store/contractStore";
    import moment from 'moment';

    const sortDesc = ref(false);
    const sortBy = ref(null);
    const sortIcon = 'tabler-chevron-up';
    const items = ref([]);

    const contractStore = useContractStore(); 

    const fetchData = async () => {
        try {
            const response = await contractApi.fetchAllContracts();
            console.log("LETTER OF OFFER")
            console.log(response)
            items.value = response;
            contractStore.setContract(response);
        } catch (error) {
            console.error('Error fetching data:', error);
        }
    };

    onMounted(() => {
        fetchData();
    });

    const headers = [
        {
            id: 0,
            title: '',
            key: 'id',
            align: 'center',
            sortable: false,
        },
        {
            id: 1,
            title: 'Contract Title',
            key: 'contractTitle',
            align: 'center',
            sortable: true
        },
        {
            id: 2,
            title: 'First Name',
            key: 'firstName',
            align: 'center',
            sortable: true
        },
        {
            id: 3,
            title: 'Last Name',
            key: 'lastName',
            align: 'center',
            sortable: true
        },
        {
            id: 4,
            title: 'Job Title',
            key: 'jobTitle',
            align: 'center',
            sortable: true
        },
        {
            id: 5,
            title: 'Location',
            key: 'location',
            align: 'center',
            sortable: true
        },
        {
            id: 6,
            title: 'Assigned To',
            key: 'assignedTo',
            align: 'center',
            sortable: true
        },
        {
            id: 7,
            title: 'Last Updated',
            key: 'lastUpdated',
            align: 'center',
            sortable: true
        },
        {
            id: 8,
            title: 'Status',
            key: 'status',
            align: 'center',
            sortable: true
        },
    ]

    const data = [
        {
            contractTitle: items.title,
            firstName: items.owner_employee_id,
            lastName: items.owner_employee_id,
            jobTitle: items.position_title,
            location: items.location_id,
            assignedTo: items.assigned_employee_id,
            lastUpdated: items.updated_at,
            status: items.status
        },
    ];

    const toggleSort = (column) => {
    if (sortBy.value === column.key) {
        sortDesc.value = !sortDesc.value;
    } else {
        sortBy.value = column.key;
        sortDesc.value = false;
    }

    sortItems();
};

const isSorted = (column) => {
    return sortBy.value === column.key;
};

const sortItems = () => {
    if (!sortBy.value) {
        return;
    }

    items.value.sort((a, b) => {
        let aValue = a[sortBy.value];
        let bValue = b[sortBy.value];

        if (aValue === undefined || aValue === null || aValue === '') {
            return sortDesc.value ? 1 : -1;
        }
        if (bValue === undefined || bValue === null || bValue === '') {
            return sortDesc.value ? -1 : 1;
        }

        if (typeof aValue === 'string') {
            aValue = aValue.toLowerCase();
        }
        if (typeof bValue === 'string') {
            bValue = bValue.toLowerCase();
        }

        let result = 0;
        if (typeof aValue === 'number' && typeof bValue === 'number') {
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
</script>

<template>
    <div class="page-content-container">
        <section>
            <div class="mb-6 font-weight-bold text-h2">
                <span style="color: #808494;">Letter of Offer </span>
            </div>

            <VCard
                title="Search Filter"
                class="mb-6"
            >
                <VCardText>
                    <VRow>
                        <VCol
                        cols="6"
                        sm="2"
                        >
                        <div class="app-select flex-grow-1">
                            <AppSelect
                                placeholder="Date"
                                item-title="name"
                                item-value="id"
                                clearable
                            />
                        </div>
                        </VCol>
                        <VCol
                        cols="6"
                        sm="2"
                        >
                        <div class="app-select flex-grow-1">
                            <AppSelect
                            placeholder="Role"
                            item-title="name"
                            item-value="id"
                            clearable
                            />
                        </div>
                        </VCol>
                        <VCol
                        cols="6"
                        sm="2"
                        >
                        <div class="app-select flex-grow-1">
                            <AppSelect
                            placeholder="Location"
                            clearable
                            />
                        </div>
                        </VCol>
                        <VCol
                        cols="6"
                        sm="2"
                        >
                        <div class="app-select flex-grow-1">
                            <AppSelect
                            placeholder="Expiry"
                            clearable
                            />
                        </div>
                        </VCol>
                          <VCol
                        cols="6"
                        sm="2"
                        >
                        <div class="app-select flex-grow-1">
                            <AppSelect
                            placeholder="Status"
                            clearable
                            />
                        </div>
                        </VCol>
                    </VRow>
                </VCardText>
            </VCard>
        </section>

        <VDataTable
            :items="items"
            :headers="headers"
            :items-per-page="5"
          style="color: #333954 !important; font-family: Arial, Helvetica, sans-serif; font-size: medium;"

        >
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
                    v-if="item.title != 'contractTitle' && item.sortable"
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
         
        </VDataTable>
    </div>
</template>

