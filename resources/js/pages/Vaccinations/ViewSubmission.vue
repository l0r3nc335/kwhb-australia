<template>
    <div v-if="!isViewSubmissionVisible">
        <p class="text-h2 font-weight-bold mb-5">Employee Vaccinations</p>
        <v-card>
            <VCardText class="d-flex flex-wrap gap-4">
                <VRow>
                    <VCol cols="12" class="mr-5">
                        <div class="d-flex align-center flex-wrap gap-4">
                            <VSpacer/>
                            <v-btn
                                style="background-color: #001871"
                                color="#CFCFCF"
                                variant="outlined"
                                class="text-none"
                                @click="$emit('back')"
                            >
                                View all vaccination types
                            </v-btn>
                            <v-btn
                                variant="tonal"
                                color="#E35205"
                                class="text-none font-weight-bold"
                                @click="isExemptDialogVisible = true"
                            >
                                Create Exemption
                            </v-btn>
                            <v-btn
                                color="primary"
                                class="text-none"
                                @click="isSubmitDialogVisible = true"
                            >
                                Submit Vaccination
                            </v-btn>
                        </div>
                    </VCol>

                    <!-- </VRow>
                    <VRow> -->
                    <Vcol cols="12" class="ml-3 mr-3">
                        <div class="text-subtitle-1 text-medium-emphasis">Search Filter</div>
                        <div class="d-flex align-center flex-wrap gap-4">
                            <AppSelect
                                placeholder="By Type"
                                density="compact"
                                class="search-dropdown"
                                v-model="query.type"
                                :items="vaccinationStore.data.vaccinations"
                                item-title="name"
                                item-value="name"
                                clearable
                            />
                            <AppSelect
                                placeholder="By Status"
                                density="compact"
                                class="search-dropdown"
                                v-model="query.status"
                                :items="['Current','Expired','Pending review','Required','Applied','Exempt', 'Rejected']"
                                clearable
                            />
                            <AppSelect
                                placeholder="By Assignee"
                                density="compact"
                                class="search-dropdown"
                                v-model="query.assignee"
                                :items="assignees.values()"
                                clearable
                            />
                        </div>
                    </Vcol>

                </VRow>

            </VCardText>

            <VCardText>
                <div class="app-select me-3 d-flex gap-3">
                    <AppSelect
                        :model-value="itemsPerPage"
                        :items="itemsPerPageOptions"
                        style="inline-size: 6.25rem;"
                        @update:model-value="itemsPerPage = parseInt($event)"
                    />
                </div>
            </VCardText>


            <VDivider />

            <VDataTableServer
                :loading="loadItems"
                :items-per-page="itemsPerPage"
                :page="query.page"
                :items="items"
                :items-length="vaccinationTotal"
                :headers="headers"
                :search="searchQuery"
                @click:row="rowClick"
                style="color: #333954 !important; font-family: Arial, Helvetica, sans-serif; font-size: medium;"
                @update:options="updateOptions"
            >
                <template #headers="{ headers }">
                    <tr>
                        <template
                            v-for="column in headers"
                            :key="column.id"
                        >
                            <th
                                style="padding: 0 10px"
                                v-for="item in column"
                                :key="item.title"
                                :class="`custom-header align-${item.align}`"
                            >
                              <span
                                  v-if="item.sortable"
                                  style="display: inline-flex; align-items: center;"
                                  @click="toggleSort(item)"
                              >

                                <span style="white-space: nowrap; " :style="{ marginLeft: item.key === 'employee' ? '0px' : '0px' }">{{ item.title }}</span>
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

                <template #item.user.employee.first_name="{ item }">
                    <span class="font-weight-medium ml-1">{{ item.user.employee.first_name + ' ' + item.user.employee.last_name  }}</span>
                </template>

                <template #item.vaccination.name="{ item }">
                    <span
                        style="
                            display: inline-block;
                            max-width: 170px;
                            min-width: 50px;
                            white-space: normal;
                            word-break: break-word;
                        ">
                        {{ item.vaccination?.name }}
                      </span>
                </template>

                <template #item.expiry_date="{ item }">
                    <td style="min-width: 40px!important; max-width: 100px!important;">
                        <span class="font-weight-regular __no_padding" v-if="item.expiry_date">{{ moment(item.expiry_date).format('DD-MM-YYYY') }}</span>
                    </td>
                </template>

                <template #item.status="{ item }">
                        <span class="font-weight-regular">{{ item.status === '1' ? 'Yes' : 'No' }}</span>
                </template>

                <template #item.vaccination.required ="{ item }">
                    <span class="font-weight-regular">{{ item.vaccination?.required === '1' ? 'Mandatory' : 'Optional' }}</span>
                </template>

                <template #item.assignee.username="{ item }">
                    <span v-if="item.assignee_user_id === -1">HR</span>
                    <span v-else class="font-weight-regular">{{ item.assignee?.username }}</span>
                </template>

                <template #item.file_status="{ item }">
                    <VChip
                        label
                        size="default"
                        :style="{
                background: (
                    item.expiry_date && !hasDatePassed(item.expiry_date))
                    || item.file_status == 'Exempt'
                    || item.file_status == 'Required'
                    || item.file_status == 'Applied'
                    || item.file_status == 'Pending review'
                    || item.file_status == 'Current' ?
                        getBackgroundColor(item) :
                        getBackgroundColor('Expired'),

                color: (
                    item.expiry_date && !hasDatePassed(item.expiry_date))
                     || item.file_status == 'Exempt'
                     || item.file_status == 'Required'
                     || (item.file_status == 'Applied' && !hasDatePassed(item.expiry_date))
                     || item.file_status == 'Pending review'
                     || item.file_status == 'Current' ?
                        getTextColor(item) :
                        getTextColor('Expired'),
              }"
                    >
                <span class="font-weight-bold">
                    {{
                        (item.expiry_date == null && !hasDatePassed(item.expiry_date) && item.file_status == 'Applied' && 'Applied') ||
                        (item.expiry_date != null && hasDatePassed(item.expiry_date) && item.file_status == 'Applied' && 'Expired') ||
                        (item.file_status == 'Current' && 'Current') ||
                        (item.file_status && item.file_status)
                    }}
                </span>
                    </VChip>
                </template>

                <template #item.comments="{ item }">
                    <span
                        style="
                            display: inline-block;
                            max-width: 150px;
                            min-width: 100px;
                            white-space: normal;
                            word-break: break-word;
                            color:#CFCFCF">{{ item.comments ? item.comments : 'Comment here' }}</span>
                </template>

                <template #bottom>
                    <VDivider />
                    <div class="d-flex align-center justify-sm-space-between justify-center flex-wrap gap-3 pa-5 pt-3">
                        <p
                            class="text-sm text-disabled mb-0"
                            style="color: #999 !important;"
                        >
                            {{ generateRangeText(query.page, vaccinationTotal, itemsPerPage) }}
                        </p>

                        <VPagination
                            v-if="vaccinationTotal > itemsPerPage"
                            v-model="query.page"
                            :total-visible="6"
                            :length="Math.ceil(vaccinationTotal / itemsPerPage)"
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
        </v-card>

        <Submit
            v-model:isDrawerOpen="isSubmitDialogVisible"
            @refetch="refetch"
        />

        <EditMySubmission
            v-model:isDrawerOpen="isEditMySubmission"
            :vaccinationData="editMySubmissionData"
            @refetch="refetch"
        />

        <!--EditMySubmission
            v-model:isEditMySubmissionDrawerOpen="isEditMySubmission"
            :editMySubmissionProp="editMySubmissionData"
            @refetch="refetch"
        /-->

        <Exemption
            v-model:isDialogOpen="isExemptDialogVisible"
            @refetch="refetch"
        />
        <ReviewSubmission
            v-model:isDialogOpen="isReviewDialogVisible"
            :vaccinationData="vaccinationData"
            @pass-edit-data="passEditData"
            @refetch="refetch"
        />
    </div>
    <!--div v-else>
        <Main/>
    </div-->
</template>

<script>
import vaccinationApi from "@/api/vaccination/vaccination";
import usersApi from "@/api/user/user";
import { useVaccinationStore } from "@/store/vaccinationStore";
import { VDataTableServer } from 'vuetify/labs/VDataTable';
import { useAuthStore } from "@/store/authStore";
import Exemption from './Exemption.vue';
import ReviewSubmission from './ReviewSubmission.vue';
import Submit from './Submit.vue';
import EditMySubmission from './EditMySubmission.vue';
//import Main from './index.vue';

export default {
    components: {
        VDataTableServer,
        Exemption,
        ReviewSubmission,
        Submit,
        EditMySubmission,
        //Main,
    },
    data() {
        return {
            items: [],
            vaccinationTotal: 0,
            vaccinationData: {},
            loadItems: false,
            isReviewDialogVisible: false,
            isSubmitDialogVisible: false,
            isExemptDialogVisible: false,
            isViewSubmissionVisible: false,
            showRecordNotYoursExemptionDialog: false,
            currentRecordOwner: null,
            isEditMySubmission: false,
            editMySubmissionData: [],
            searchQuery: '',
            itemsPerPage: 50,
            sortBy: null,
            orderBy: undefined,
            assignees: [],
            itemsPerPageOptions: [
                { value: 50, title: '50' },
                { value: 100, title: '100' },
                { value: 200, title: '200' },
                { value: 500, title: 'All' },
            ],
            sortDesc: false,
            query: {
                page: 1,
                type: [],
                status: [],
                assignee: [],
            },
            headers: [
                {
                    title: 'Employee Name',
                    key: 'user.employee.first_name',
                    align: 'left',
                    sortable: true,
                },
                {
                    title: 'Type',
                    key: 'vaccination.name',
                    align: 'left',
                    sortable: true,
                },
                {
                    title: 'Expiry',
                    key: 'expiry_date',
                    align: 'left',
                    sortable: true,
                    class: '__no_padding'
                },
                {
                    title: 'Uploaded',
                    key: 'status',
                    align: 'left',
                    sortable: true,
                },
                {
                    title: 'Status',
                    key: 'file_status',
                    class: 'font-weight-bold',
                    align: 'center',
                    sortable: true,
                },
                {
                    title: 'Requirement',
                    key: 'vaccination.required',
                    class: 'font-weight-bold',
                    align: 'left',
                    sortable: true,
                },
                {
                    title: 'Comment',
                    key: 'comments',
                    class: 'font-weight-bold',
                    align: 'left',
                    sortable: true,
                },
                {
                    title: 'Assigned To',
                    key: 'assignee.username',
                    class: 'font-weight-bold',
                    align: 'left',
                    sortable: true,
                },
            ],
        };
    },
    computed: {
        vaccinationStore() {
            return useVaccinationStore();
        },
        userData() {
            return useAuthStore().userData; // No need for storeToRefs
        }
    },
    watch: {
        itemsPerPage() {
            this.query.page = 1;
            this.vaccination();
        },
        'query.type': {
            handler() {
                this.vaccination();
            },
            deep: true,
        },
        'query.status': {
            handler() {
                this.vaccination();
            },
            deep: true,
        },
        'query.assignee': {
            handler() {
                this.vaccination();
            },
            deep: true,
        },
        'query.page': {
            handler() {
                this.vaccination();
            },
        },
    },
    methods: {
        passEditData(valVacData) {
            this.isEditMySubmission = true
            this.editMySubmissionData = valVacData
        }
        ,
        updateOptions(options) {
            // page update commented out in original
            this.sortBy = options.sortBy[0]?.key || null;
            this.orderBy = options.sortBy[0]?.order;
        },
        toggleSort(column) {
            if (this.sortBy === column.key) {
                this.sortDesc = !this.sortDesc;
            } else {
                this.sortBy = column.key;
                this.sortDesc = false;
            }
            this.sortItems();
        },
        isSorted(column) {
            return this.sortBy === column.key;
        },
        getNestedValue(obj, path) { // this was changed where obj is the aray and path is the sortkey
            return path.split('.').reduce((acc, part) => {
                const match = part.match(/(\w+)\[(\d+)\]/)
                if (match) {
                    const arrayPart = match[1]
                    const index = parseInt(match[2], 10)

                    return acc && acc[arrayPart] && acc[arrayPart][index] !== undefined
                        ? acc[arrayPart][index]
                        : undefined
                } else {
                    return acc && acc[part] !== undefined ? acc[part] : undefined
                }
            }, obj)
        },
        sortItems() {
            if (!this.sortBy) return;

            if(this.sortBy === 'uploaded') {
                this.sortBy = 'status'
            }

            this.items.sort((a, b) => {
                let result = 0
                let aValue = this.getNestedValue(a, this.sortBy)
                let bValue = this.getNestedValue(b, this.sortBy)

                // Handle undefined/null/empty string
                if (aValue === undefined || aValue === null || aValue === '') {
                    return this.sortDesc ? 1 : -1;
                }
                if (bValue === undefined || bValue === null || bValue === '') {
                    return this.sortDesc ? -1 : 1;
                }

                // Normalize strings
                if (typeof aValue === 'string') aValue = aValue.toLowerCase();
                if (typeof bValue === 'string') bValue = bValue.toLowerCase();

                if (typeof aValue === 'number' && typeof bValue === 'number') {
                    result = aValue - bValue
                } else {
                    if (aValue < bValue) {
                        result = -1
                    } else if (aValue > bValue) {
                        result = 1
                    }
                }
                return this.sortDesc ? -result : result;
            });
        },


        generateRangeText(pageNumber, totalNumberOfRows, rowsPerPage) {
            const startIndex = (pageNumber - 1) * rowsPerPage + 1;
            let endIndex = pageNumber * rowsPerPage;
            if (endIndex > totalNumberOfRows) endIndex = totalNumberOfRows;

            const rowsOnLastPage = totalNumberOfRows % rowsPerPage || rowsPerPage;

            let rangeText;
            if (totalNumberOfRows === 0) {
                rangeText = "No entries";
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
        getBackgroundColor(item) {
            switch (item.file_status || 'Expired') {
            case 'Current':
                return '#E6F5E9'
            case 'Expired':
                return '#FBDFE1'
            case 'Pending review':
                return '#FAE3D7'
            case 'Required':
                return '#E5EAFF'
            case 'Applied':
                return '#FDF3DE' // #F8ECB5
            case 'Exempt':
                return '#FBD2FF'
            default:
                return '#FBDFE1'
            }
        },
        getTextColor(item) {
            switch (item.file_status || 'Expired') {
            case 'Current':
                return '#62C379'
            case 'Expired':
                return '#E63946'
            case 'Pending review':
                return '#E35205'
            case 'Required':
                return '#5C7FFF'
            case 'Applied':
                return '#F1B435'
            case 'Exempt':
                return '#947C96'
            default:
                return '#E63946'
            }
        },
        async vaccination() {
            try {
                this.loadItems = true;
                const response = await vaccinationApi.fetch_employee_vaccination_list({
                    ...this.query,
                    itemsPerPage: this.itemsPerPage,
                });
                this.items = response.data || [];
                console.log("================================ VACCINATION ============================")
                console.log(this.items)
                this.vaccinationTotal = response.total;
                this.loadItems = false;
            } catch (error) {
                console.error(error);
            }
        },
        async usersAssignee() {
            try {
                this.loadItems = true;
                const role_id = 5;
                const response = await usersApi.get_users();
                this.assignees = response.map(user => user.username);
            } catch (error) {
                console.error(error);
            }
        },
        hasDatePassed(givenDateStr) {
            if (givenDateStr === null) {
                return false
            }
            const today = new Date().setHours(0, 0, 0, 0);
            const [year, month, day] = givenDateStr.split('-').map(Number);
            const givenDate = new Date(year, month - 1, day).setHours(0, 0, 0, 0);
            return givenDate < today;
        },
        rowClick(e, row) {
            //if(this.userData.username === row.item.assignee.username || this.userData.role_id === 1) {
                if (e.target.nodeName !== "svg") {
                    this.vaccinationData = row.item;
                    this.isReviewDialogVisible = true;
                }
            //} else {
                //this.currentRecordOwner = row.item.assignee.username
            //    this.showRecordNotYoursExemptionDialog = true
            //}
        },
        refetch() {
            this.vaccination();
        },
    },
    async mounted() {
        await this.vaccinationStore.setVaccinations();
        this.vaccination();
        this.usersAssignee();
    }
};
</script>

<style scoped>
.search-dropdown {
    flex: 1;
    width: 27.3rem;
}

.custom-header {
    color: #333954 !important;
    font-family: Oswald !important;
    font-size: large;
    font-weight: 400 !important;
}

.align-left {
    text-align: left !important;
}

.align-center {
    text-align: center !important;
}

.align-right {
    text-align: right !important;
}

:deep(.text-primary) {
    color: #001871 !important;
}

.__status_pending_approval {
    background-color: #FDF3DE;
    color: #f1b434;
    padding: 5px 5px;
    min-width: 50px;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    font-size: 15px !important;
}

.__status_approved {
    background-color: #E6F5E9;
    color: #61C378;
    padding: 5px 5px;
    min-width: 50px;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    font-size: 15px !important;
}

.__status_applied {
    background-color: #FDF3DE;
    color: #F1B434;
    padding: 5px 5px;
    min-width: 50px;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    font-size: 15px !important;
}

.__status_rejected {
    background-color: #FCECE5;
    color: orangered;
    padding: 5px 5px;
    min-width: 50px;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    font-size: 15px !important;
}


</style>
