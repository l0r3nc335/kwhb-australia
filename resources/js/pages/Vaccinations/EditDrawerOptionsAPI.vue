<template>
    <VNavigationDrawer
        temporary
        :width="500"
        location="end"
        class="scrollable-content px-3"
        :model-value="isDrawerOpen"
        @update:model-value="handleDrawerModelValueUpdate"
    >
        <AppDrawerHeaderSection
            title="Edit Vaccination"
            @cancel="closeNavigationDrawer"
        />

        <PerfectScrollbar :options="{ wheelPropagation: false }">
            <VCard flat>
                <VCardText>
                    <VForm ref="refForm">
                        <VRow>
                            <VCol cols="12">
                                <div class="text-subtitle-1 text-medium-emphasis">Vaccination Name</div>
                                <AppTextField
                                    placeholder="Vaccination name"
                                    v-model="localVaccinationName"
                                    :rules="[requiredValidator]"
                                />
                            </VCol>

                            <VCol cols="12">
                                <AppSelect
                                    label="Set Validity"
                                    placeholder="Annually"
                                    v-model="localVaccinationValidity"
                                    :items="['Quarterly','Annual','Lifetime','Custom']"
                                    clearable
                                    :rules="[requiredValidator]"
                                />
                            </VCol>

                            <VCol cols="12" v-if="localVaccinationValidity === 'Custom'">
                                <VueDatePicker
                                    v-model="localVaccinationCustomExpiry"
                                    :min-date="minDate"
                                    :auto-apply="true"
                                    format="dd-MM-yyyy"
                                    placeholder="Expiry Date"
                                />
                            </VCol>

                            <VCol cols="12">
                                <div class="text-subtitle-1 text-medium-emphasis">Select Division</div>
                                <v-combobox
                                    placeholder="Division"
                                    v-model="localVaccinationDivision"
                                    :items="divisionList"
                                    clearable
                                    multiple
                                    small-chips
                                    :menu-props="{ maxWidth: '0' }"
                                    color="white"
                                >
                                    <template v-slot:prepend-item>
                                        <v-list-item
                                            @click="selectAllDivision"
                                            :key="'select-all'"
                                        >
                                            <v-list-item-content>
                                                <v-list-item-title>Select All</v-list-item-title>
                                            </v-list-item-content>
                                        </v-list-item>
                                        <v-divider />
                                    </template>
                                </v-combobox>
                            </VCol>

                            <VCol cols="12">
                                <div class="text-subtitle-1 text-medium-emphasis">Select Requirement</div>
                                <VRadioGroup inline v-model="localVaccinationRequired">
                                    <VRadio label="Mandatory" :value="'1'" color="#CC4A04" />
                                    <VRadio label="Optional" :value="'0'" color="#CC4A04" />
                                </VRadioGroup>
                            </VCol>

                            <VCol cols="12">
                                <div class="text-subtitle-1 text-medium-emphasis">Select Roles</div>
                                <v-combobox
                                    placeholder="Search"
                                    prepend-inner-icon="tabler-search"
                                    clearable
                                    v-model="localVaccinationRoles"
                                    :search-input.sync="search"
                                    :items="jobTitles"
                                    multiple
                                    :hide-no-data="false"
                                    small-chips
                                    :menu-props="{ maxWidth: '0' }"
                                    color="white"
                                    @update:modelValue="handleRoleChange"
                                >
                                    <template v-slot:prepend-item>
                                        <v-list-item
                                            @click="selectAllRoles"
                                            :key="'select-all'"
                                        >
                                            <v-list-item-content>
                                                <v-list-item-title>Select All</v-list-item-title>
                                            </v-list-item-content>
                                        </v-list-item>
                                        <v-divider />
                                    </template>
                                    <template #no-data>
                                        <v-list-item>
                                            <v-list-item-title v-if="search">
                                                No results matching "<strong>{{ search }}</strong>"
                                            </v-list-item-title>
                                        </v-list-item>
                                    </template>
                                </v-combobox>
                            </VCol>

                            <VCol cols="12" class="mt-3">
                                <VBtn class="me-3" :loading="loading" @click="updateVaccination">
                                    Update
                                </VBtn>
                                <VBtn variant="tonal" color="secondary" @click="closeNavigationDrawer">
                                    Cancel
                                </VBtn>
                            </VCol>
                        </VRow>
                    </VForm>
                </VCardText>
            </VCard>
        </PerfectScrollbar>
    </VNavigationDrawer>
</template>

<script>
import vaccinationApi from "@/api/vaccination/vaccination";
import toast from '@/composable/toast.js';
import { requiredValidator } from '@core/utils/validators';
//import { requiredValidator } from '@core/utils/validators';
import { PerfectScrollbar } from 'vue3-perfect-scrollbar';
import moment from "moment";
import divisionApi from "@/api/division/divisionApi"
import jobTitleApi from "@/api/jobTitle/jobTitleApi"

export default {
    name: "VaccinationDrawer",
    components: {
        PerfectScrollbar,
    },
    props: {
        isDrawerOpen: {
            type: Boolean,
            required: true,
        },
        vaccinationData: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'update:isDrawerOpen',
        'refetch',
        'update:vaccinationData'
    ],
    data() {
        return {
            search: '',
            refForm: null,
            roles: [],
            jobTitles: [],
            loading: false,
            minDate: new Date().toISOString().split('T')[0],
            localRoleArray: [],
            localDivisionArray: [],
            unmatchedRoleSearch: '', // this was changed
            lastRoleArraySnapshot: [], // this was changed
            divisionList: [],
            localVaccination: {}
        };
    },
    created() {
        this.localVaccination = { ...this.vaccinationData };
    },
    computed: {
        localVaccinationName: {
            get() {
                return this.localVaccination.name;
            },
            set(newValue) {
                this.localVaccination.name = newValue;
            },
        },
        localVaccinationValidity: {
            get() {
                return this.localVaccination.validity;
            },
            set(newValue) {
                this.localVaccination.validity = newValue;
            },
        },
        localVaccinationDivision: {
            get() {
                return this.localVaccination.division
                    ? this.localVaccination.division.split(',')
                    : [];
            },
            set(newValue) {
                this.localVaccination.division = newValue.join(',');
            }
        },
        localVaccinationRequired: {
            get() {
                return this.localVaccination.required;
            },
            set(newValue) {
                this.localVaccination.required = newValue;
            },
        },
        localVaccinationCustomExpiry: {
            get() {
                return this.localVaccination.custom_expiry;
            },
            set(newValue) {
                this.localVaccination.custom_expiry = newValue;
            },
        },
        localVaccinationRoles: {
            get() {
                return this.localVaccination.role
                    ? this.localVaccination.role.split(',')
                    : [];
            },
            set(newValue) {
                this.localVaccination.role = newValue.join(',');
            }
        }
    },

    watch: {
        localVaccinationDivision(newVal) {
            if(this.localVaccinationDivision.length > 0){
                this.loadRolesJobTitle(this.localVaccinationDivision)
            }else{
                jobTitles.value = []
            }
        },
        isDrawerOpen(newVal) {
            if (newVal) {
                this.localVaccination = { ...this.vaccinationData };
            }
        },
        'vaccinationData.role': {
            immediate: true,
            handler(newVal) {
                this.localRoleArray = newVal ? newVal.split(',') : [];
            }
        },
        'vaccinationData.division': {
            immediate: true,
            handler(newVal) {
                this.localDivisionArray = newVal ? newVal.split(',') : [];
            }
        }
    },
    methods: {
        selectAllRoles() {
            if (this.localVaccinationRoles.length === this.jobTitles.length) {
                this.localVaccinationRoles = [];
            } else {
                this.localVaccinationRoles = [...this.jobTitles];
            }
        },
        selectAllDivision() {
            if (this.localVaccinationDivision.length === this.divisionList.length) {
                this.localVaccinationDivision = [];
            } else {
                this.localVaccinationDivision = [...this.divisionList];
            }
        },
        async loadDivisions() {
            try {
                this.loading = true;
                const res = await divisionApi.fetchDivisionsActive();

                this.divisionList = Array.isArray(res.data)
                    ? res.data.map(item => item.name)
                    : Array.isArray(res)
                        ? res.map(item => item.name)
                        : [];

                //console.log("===========DIVISIONS==============");
                //console.log(this.divisionList);
            } catch (error) {
                console.error(error);
                this.loading = false;

                if (error.response && error.response.status === 422) {
                    const validationErrors = error.response.data.errors;

                    Object.values(validationErrors).forEach(errorMessages => {
                        errorMessages.forEach(message => {
                            toast('error', message);
                        });
                    });
                } else {
                    toast('error', 'An unexpected error occurred. Please try again.');
                }
            } finally {
                this.loading = false;
            }
        },
        handleRoleChange(newVal) { // this was changed
                                   // Detect the newly added value
            const added = newVal.find(item => !this.lastRoleArraySnapshot.includes(item)); // this was changed
            if (added && !this.roles.includes(added)) { // this was changed
                this.unmatchedRoleSearch = added; // this was changed
                // Remove from selected array
                this.localRoleArray = newVal.filter(role => role !== added); // this was changed

                // Clear search
                this.search = ''; // this was changed
            }

            this.lastRoleArraySnapshot = [...this.localRoleArray]; // this was changed
        },
        requiredValidator,
        closeNavigationDrawer() {
            this.$emit('update:isDrawerOpen', false);
        },
        handleDrawerModelValueUpdate(val) {
            this.$emit('update:isDrawerOpen', val);
        },
        async updateVaccination() {
            try {
                this.loading = true;
                let custom_expiry = null;

                if (this.vaccinationData.validity === 'Custom') {
                    custom_expiry = moment(this.localVaccination.custom_expiry).format('YYYY-MM-DD');
                }
                const formData = new FormData();
                formData.append('id', this.vaccinationData.id);
                formData.append('name', this.localVaccination.name);
                formData.append('validity', this.localVaccination.validity);
                formData.append('custom_expiry', custom_expiry);
                formData.append('division', this.localVaccination.division);
                formData.append('required', this.localVaccination.required);
                formData.append('role', this.localVaccination.role);

                const response = await vaccinationApi.update_vaccination(formData);
                toast('success', response.message);

                this.loading = false;
                this.$emit('update:isDrawerOpen', false);
                this.$emit('refetch');
            } catch (error) {
                console.error(error);
                this.loading = false;
                if (error.response && error.response.status === 422) {
                    const validationErrors = error.response.data.errors;
                    Object.values(validationErrors).forEach(errorMessages => {
                        errorMessages.forEach(message => toast('error', message));
                    });
                } else {
                    toast('error', 'An unexpected error occurred. Please try again.');
                }
            }
        },
        async loadRolesJobTitle() {
            try {
                const result  = await jobTitleApi.fetchActiveJobTitlesForDivision(this.localVaccinationDivision)
                this.jobTitles = result.data.map(d => d.name)
                console.log("jobTitles.value")
                console.log(this.localVaccinationRoles)
                console.log(this.jobTitles)
                this.jobTitles = Array.from(new Set([...this.localVaccinationRoles, ...this.jobTitles]))



            } catch (error) {
                console.error('Failed to load jobTitles:', error)
            }
        }
    },
    mounted() {
        this.lastRoleArraySnapshot = [...this.localRoleArray];
        this.loadDivisions()
        this.loadRolesJobTitle()
    },
};
</script>

<style scoped>
:deep(.v-field__prepend-inner) {
    color: darkgray !important;
}
</style>
