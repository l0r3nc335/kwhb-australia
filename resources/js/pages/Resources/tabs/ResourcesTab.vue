<template>
    <div class="tw-flex tw-flex-col">
        <div class="tw-flex tw-justify-between">
            <div>
                <template v-if="category">
                    <v-combobox
                    class="tw-w-[320px]"
                    v-model="filter.role"
                    :items="rolesOptions"
                    item-title="label"
                    item-value="value"
                    label="Select Role"
                    clearable
                    prepend-inner-icon="tabler-search"
                    :hide-no-data="false"
                    @update:model-value="val => filterFieldChanged('role', val)"
                    color="white"
                    />                        
                </template>             
            </div>
            <div>
                <AddResourceButton v-if="category" @reload-resources="loadResources" :category="category"></AddResourceButton>
                <AddCategoryButton v-else @reload-categories="loadCategories"></AddCategoryButton>
            </div>
        </div>
        <div v-if="category" class="tw-flex tw-items-center tw-cursor-pointer hover:tw-underline group"
            @click="category = null; currentTab = null">
            <svg xmlns="http://www.w3.org/2000/svg"
                class="tw-w-5 tw-h-5 tw-mr-2 tw-text-gray-400 group-hover:tw-text-orange-500"
                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 19l-7-7 7-7"/>
            </svg>
            <span class="tw-text-base tw-font-medium tw-text-gray-700 group-hover:tw-text-orange-500">
                Back to Resource Center
            </span>
        </div>
        <div class="tw-mt-10">
            <template v-if="!category && currentTab !== 'acknowledgement'">
                <div class="tw-grid tw-grid-cols-4 tw-gap-4">
                    <div v-for="cat in filteredCategories" class="tw-relative tw-flex tw-flex-col tw-justify-end tw-w-full "
                         style="overflow:hidden"
                         @click="onCategorySelect(cat)">
                        <div class="resource-category tw-relative" style="z-index: 1; overflow:hidden">
                            <div class="tw-text-[1.5rem] tw-mb-4 tw-ml-4 " style="z-index: 2;
                             position:absolute;bottom: 5px; left: 10px; text-shadow: black;">
                                {{cat.name}}
                            </div>
                            <div style="position: absolute;top:0;left:0;z-index: 1;width: 100%;height: 100%;
                            background: linear-gradient(183deg, rgba(250,131,51,0.8855917366946778) 0%, rgba(255,136,32,0.742734593837535) 35%, rgba(233,111,21,0.5690651260504201) 100%);
                            "></div>
                            <div :style="{background:`url(/${cat.background})`}" style="position: absolute;
                           width: 100%; top: 0; left:0;
                            height: 100%;
                            z-index: 0;    background-size: cover;
                            background-repeat: no-repeat;
                            background-position: top center;"></div>
                        </div>
                    </div>
                </div>
            </template>
            <template v-else>
                <div class="leave-tabs tw-mb-6 tw-flex tw-gap-2">
                    <div class="tab" :class="{ active: currentTab === 'resources' }" @click="switchTab('resources')">Resources</div>
                    <div class="tab" :class="{ active: currentTab === 'acknowledgement' }" @click="switchTab('acknowledgement')">Acknowledgements</div>
                </div>

                <!-- Filter Bar: Only show on table view (category selected or all resources shown) -->
                <div class="tw-flex tw-gap-3 tw-mb-5" v-if="category || true">
                    <VTextField
                    v-model="search"
                    prepend-inner-icon="tabler-search"
                    placeholder="Search"
                    class="tw-max-w-[200px] tw-w-full"
                    />
                    <template v-if="currentTab === 'acknowledgement' || currentTab === 'resources'">
                        <VueDatePicker
                        :model-value="filter.due_date"
                        @update:model-value="val => filterFieldChanged('due_date', val)"                    
                        auto-apply
                        format="dd/MM/yyyy"
                        :enable-time-picker="false"
                        teleport="body"
                        placeholder="Due Date"
                        class="tw-max-w-[150px] tw-min-h-[45px]"
                        hide-input-icon
                        />          
                    </template>
                    <template v-if="currentTab === 'acknowledgement' || currentTab === 'resources'">
                        <AppSelect
                            placeholder="Division"
                            class="tw-min-w-[130px]"
                            :model-value="filter.division"
                            @update:model-value="val => filterFieldChanged('division', val)"
                            :items="divisionOptions"
                            item-title="label"
                            item-value="value"
                            clearable
                        />                        
                    </template>
                    <template v-if="currentTab === 'acknowledgement' || currentTab === 'resources'">
                        <AppSelect
                            placeholder="Job Title"
                            class="tw-min-w-[130px]"
                            :model-value="filter.job_title"
                            @update:model-value="val => filterFieldChanged('job_title', val)"                        
                            :items="jobTitleOptions"
                            item-title="label"
                            item-value="value"
                            clearable
                        />                        
                    </template>
                    <template v-if="currentTab === 'acknowledgement'">
                        <AppSelect
                            placeholder="Status"
                            class="tw-min-w-[110px]"
                            :model-value="filter.status"
                            @update:model-value="val => filterFieldChanged('status', val)"                                     
                            :items="statusOptions"
                            item-title="label"
                            item-value="value"
                            clearable
                        />                        
                    </template>
                    <template v-if="currentTab === 'resources'">
                    <AppSelect
                        placeholder="Privacy"
                        class="tw-min-w-[120px]"
                        :model-value="filter.privacy_status"
                        @update:model-value="val => filterFieldChanged('privacy_status', val)"
                        :items="privacyStatusOptions"
                        item-title="label"
                        item-value="value"
                        clearable
                    />
                    </template>                    

                </div>

                <template v-if="currentTab === 'resources'">
                    <ResourceTable :resources="resources" :filter="filter" :category="category" :pagination="pagination" :search="search" @update:resources="resources = $event"></ResourceTable>
                </template>
                <template v-else-if="currentTab === 'acknowledgement'">
                    <AcknowledgementTable :resources="resources" :filter="filter" :category="category" :pagination="pagination" :search="search" @update:resources="resources = $event" />
                </template>
            </template>
        </div>
    </div>
</template>

<script>
// APIs
import resourceApi from "@/api/resources/resourceApi";
// Components
import divisionApi from '@/api/division/divisionApi';
import JobTitleApi from '@/api/jobTitle/jobTitleApi';
import AcknowledgementTable from "@/pages/Resources/components/AcknowledgementTable.vue";
import AddCategoryButton from "@/pages/Resources/components/AddCategoryButton.vue";
import AddResourceButton from "@/pages/Resources/components/AddResourceButton.vue";
import ResourceTable from "@/pages/Resources/components/ResourceTable.vue";
import dayjs from 'dayjs';
import timezone from 'dayjs/plugin/timezone';
import utc from 'dayjs/plugin/utc';

dayjs.extend(utc)
dayjs.extend(timezone)

export default {
    components: {
        ResourceTable,
        AddResourceButton,
        AddCategoryButton,
        AcknowledgementTable
    },

    props: {
        selectedCategory: {
            default: null
        }
    },

    data() {
        return {
            loading: false,
            search: null,
            resources: [],
            categories: [],
            category: null,
            selectedResource: null,
            jobTitleOptions: [],
            form: {},
            pagination: {
                page: 1,
                per_page: 10,
                total: 0
            },
            currentTab: "resources",
            rolesOptions: [],

            filter: {
                due_date: null,
                division: null,
                job_title: null,
                resource_name: null,
                status: null,
                role: null,
                privacy_status: null,
            },
            privacyStatusOptions: [
            { label: 'With Everyone', value: 'everyone' },
            { label: 'With Role', value: 'role' },
            { label: 'With User', value: 'user' },
            ],            
            dueDateOptions: [
                { label: 'Overdue', value: 'overdue' },
                { label: 'Due Today', value: 'today' },
                { label: 'Due This Week', value: 'this_week' },
                { label: 'No Due Date', value: 'none' },
            ],
            divisionOptions: [],
            jobTitleOptions: [
                // Example: { label: 'Manager', value: 'manager' }
            ],
            resourceNameOptions: [
                // Example: { label: 'Handbook', value: 'handbook' }
            ],
            statusOptions: [
                { label: 'Not Viewed', value: 'not_viewed' },
                { label: 'Acknowledged', value: 'acknowledged' },
            ],
        }
    },

    mounted() {
        this.loadAll();
        this.loadDivisionOptions();
        this.loadJobTitlesForDivision();
        this.loadRolesOptions();
    },

    computed: {
        filteredCategories() {
            if (this.search) {
                return this.categories.filter(c => !c.name.toLowerCase().search(this.search.toLowerCase()))
            }
            return this.categories
        },
        filteredResources() {
            if (this.search) {
                return this.resources.filter(r =>
                    r.name.toLowerCase().includes(this.search.toLowerCase())
                );
            }
            return this.resources;
        }
    },

    methods: {   
        selectCategoryById(id) {
            const cat = this.categories.find(c => String(c.id) === String(id));
            if (cat) {
            this.category = cat;
            this.currentTab = 'resources';
            }
        },        
        onCategorySelect(cat) {
            this.category = cat;
            this.currentTab = 'resources';

            // Set ?category_id=cat.id in URL
            this.$router.replace({
            query: {
                ...this.$route.query,
                category_id: cat.id
            }
            });
        },      
        async loadRolesOptions() {
            try {
                // Change this if your API structure is different!
                const res = await import('@/api/role/roleApi').then(api => api.default.fetchAllRoles());
                const rolesArr = Array.isArray(res) ? res : (res.data || []);
                this.rolesOptions = rolesArr.map(role => ({
                label: role.name,
                value: role.id,
                }));
            } catch (e) {
                this.rolesOptions = [];
            }
        },  
        filterFieldChanged(field, value) {
            console.log("FFF", field, value)

            // if(field === "due_date"){
            //     value =dayjs(value).tz('Australia/Sydney').format('DD-MM-YYYY')
            // }
            this.filter = { ...this.filter, [field]: value };
        },          
        async loadJobTitlesForDivision() {
            try {
                const res = await JobTitleApi.fetchActiveJobTitlesForDivision(this.filter.division);
                console.log("!!!!", res)
                this.jobTitleOptions = (res.data || []).map(jt => ({
                    label: jt.name,
                    value: jt.id
                }));
            } catch (e) {
                this.jobTitleOptions = [];
                this.filter.job_title = null;
            }
        },             
        switchTab(tab) {
            this.currentTab = tab;
        },
        async loadDivisionOptions() {
            // Adjust based on your API
            // If you have a fetchAllDivisions, use that:
            // const res = await divisionApi.fetchAllDivisions();
            // Otherwise, use paginated:
            const res = await divisionApi.fetchPaginatedDivisions({ per_page: 1000 });
            this.divisionOptions = (res.data || []).map(d => ({
                label: d.name,
                value: d.id
            }));
        },        
        loadAll() {
            this.loadCategories()
        },
        async loadCategories() {
            this.categories = await resourceApi.loadCategories()
        },
        async loadResources() {
            // let data = await resourceApi.loadResources(this.category?.id, this.search) // Pass search term to API
            // this.resources = data.data
            // this.pagination.per_page = data.per_page
            // this.pagination.page = data.current_page
            // this.pagination.total = data.total
        },
        async save() {
            await resourceApi.save(this.form)
        },
        async delete() {
            await resourceApi.delete(this.selectedResource?.id)
        }
    },

    watch: {
        categories(newCats) {
            const categoryId = this.$route.query.category_id;
            if (categoryId) {
            this.selectCategoryById(categoryId);
            }
        },        
        category() {
            this.loadResources()
            this.$emit('selected-category', this.category)
        },

        selectedCategory() {
            if (!this.selectedCategory) {
                this.category = null
            }
        },

        // Watch for search term changes and load resources accordingly
        search() {
            console.log("Searched!", this.search);
            // this.loadResources()
        }
    }
}
</script>

<style scoped>
.resource-category {
    height: 280px;
    text-transform: capitalize;
    word-wrap: break-word;
    border-radius: 40px;
    @apply tw-cursor-pointer tw-shadow-lg tw-p-4 tw-text-[2rem] tw-text-white tw-font-bold tw-mr-8 hover:tw-bg-orange-500
}
.leave-tabs {
    @apply tw-flex tw-border-b-[1px];
}
.leave-tabs .tab {
    @apply tw-mr-8 tw-py-4 tw-text-gray-400 tw-text-[1.1rem] tw-cursor-pointer hover:tw-text-blue-900;
}
.leave-tabs .tab.active {
    position: relative;
    @apply tw-text-gray-600 tw-font-bold;
}
.leave-tabs .tab.active::after {
    content: "";
    display: block;
    width: 100%;
    height: 5px;
    position: absolute;
    bottom: -2px;
    background-color: #e35205;
}
:deep(.v-field__input),
:deep(.dp__input),
:deep(input),
:deep(select) {
  height: 45px !important;
  min-height: 45px !important;
  line-height: 45px !important;
  border-color: #999 !important;
  box-sizing: border-box;
}

.leave-tabs .tab.active[data-v-4acae305]::after{
    background-color: #00156e !important;
}

.tab.active {
    color: #00156e !important;
}
</style>
