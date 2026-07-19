<template>
    <div class="tw-flex tw-flex-col">
        <div class="tw-flex tw-py-4 tw-text-[2rem] tw-font-bold tw-mb-4">
            Reports
        </div>
        <div class="tw-py-3 tw-flex tw-justify-end">
            <div>
                <AppCombobox v-model="dateRangeType" :items="dateRangeOptions" style="width: 200px"></AppCombobox>
            </div>
            <div v-if="dateRangeType === 'Custom'" class="tw-flex tw-flex-col tw-justify-center tw-ml-3">
                <VueDatePicker  class="tw-mr-4 !tw-grow-0" placeholder="Select Date" v-model="dateRange" format="dd/MM/yyyy" :enable-time-picker="false"  range multi-calendars style="width: 250px"></VueDatePicker>
            </div>
        </div>
        <div class="tw-py-2 tw-mt-4">
            <div class="tabs">
                <div class="tab-item" v-for="tab in tabs" @click="activeTab=tab" :class="[activeTab === tab ? 'active' : '']">
                    {{tab}}
                </div>
            </div>
        </div>
        <template v-for="report in reports">
            <ResourceReport v-if="activeTab === report.label"  :report="report" :graph-report="graph"></ResourceReport>
        </template>
    </div>
</template>

<script>
    import reportApi from "@/api/reports/reportApi";
    import ResourceReport from "@/pages/Report/ResourceReport.vue";

    export default {
        components: {
            ResourceReport
        },

        data() {
            return {
                reports: [],
                graph: [],
                activeTab: 'People',
                dateRangeType: 'Weekly',
                dateRange: null,
                dateRangeOptions: [
                    'Weekly',
                    'Monthly',
                    'Quarterly',
                    'Annually',
                    'Custom'
                ]
            }
        },

        computed: {
            tabs() {
                let tabs = []
                for (let i in this.reports) {
                    tabs.push(this.reports[i].label)
                }

                return tabs
            }
        },

        mounted() {
            this.loadReports()
            this.loadReportGraphs()
        },

        methods: {
            async loadReportGraphs() {
                let start = this.dateRange?.length > 0 ? this.dateRange[0] : null;
                let end = this.dateRange?.length > 1 ? this.dateRange[1] : null;
                this.graph = await reportApi.fetchReportGraphs(this.dateRangeType, 0, start, end)
            },

            async loadReports() {
                this.reports = await reportApi.fetchReports(this.dateRangeType, this.dateRange)
            }
        },

        watch: {
            dateRangeType() {
                if (this.dateRangeType != 'Custom') {
                    this.loadReports()
                    this.loadReportGraphs()
                }
            },

            dateRange() {
                if (this.dateRange?.length > 0) {
                    this.loadReports()
                    this.loadReportGraphs()
                }
            }
        }
    }
</script>

<style>
    .tabs {
        @apply tw-flex tw-border-b-[1px]
    }

    .tabs .tab-item {
        @apply tw-mr-8 tw-py-4 tw-text-gray-400 tw-text-[1.1rem] tw-cursor-pointer hover:tw-text-blue-900
    }

    .tabs .tab-item.active {
        position: relative;
        @apply tw-text-gray-600 tw-font-bold
    }

    .tabs .tab-item.active::after {
        content: "";
        display: block;
        width: 100%;
        height: 5px;
        position: absolute;
        bottom: -2px;
        background-color: #e35205;
    }
</style>
