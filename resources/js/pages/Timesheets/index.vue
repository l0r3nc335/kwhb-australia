<template>
    <div class="tw-flex tw-flex-col">
        <div class="timesheet-tabs">
            <div v-if="hasPermissions('operational.timesheets_submit_tab.view')" class="tab" :class="[currentTab === 'timesheet' ? 'active' : '']" @click="currentTab = 'timesheet'">Timesheet</div>
            <div v-if="hasPermissions('operational.timesheets_pending_tab.view')" class="tab" :class="[currentTab === 'pending' ? 'active' : '']" @click="currentTab = 'pending'"><span class="item-count-indicator" v-if="pending.length > 0">{{pending.length}}</span>Pending Approval</div>
            <div v-if="hasPermissions('operational.timesheets_unsubmitted_tab.view')" class="tab" :class="[currentTab === 'unsubmitted' ? 'active' : '']" @click="currentTab = 'unsubmitted'"><span class="item-count-indicator" v-if="unsubmitted.length > 0">{{unsubmitted.length}}</span>Unsubmitted</div>
            <div v-if="hasPermissions('operational.timessheets_approved_tab.view')" class="tab" :class="[currentTab === 'approved' ? 'active' : '']" @click="currentTab = 'approved'">Approved</div>
        </div>
        <div class="tw-flex tw-flex-col tw-mt-2 tw-py-4">
            <TimesheetTab v-if="currentTab === 'timesheet' && hasPermissions('operational.timesheets_submit_tab.view')" @reload="loadResources(true)"></TimesheetTab>
            <PendingTab v-if="currentTab === 'pending' && hasPermissions('operational.timesheets_pending_tab.view')" :periods="pending" @reload="loadResources(true)"></PendingTab>
            <UnsubmittedTab v-if="currentTab === 'unsubmitted' && hasPermissions('operational.timesheets_unsubmitted_tab.view')" :periods="unsubmitted"></UnsubmittedTab>
            <ApprovedTab v-if="currentTab === 'approved' && hasPermissions('operational.timessheets_approved_tab.view')" :periods="approved"></ApprovedTab>
        </div>
    </div>
</template>
<script>
    // Libraries
    import {mapState, mapActions} from "pinia";
    // Apis
    import timesheetApi from "@/api/timesheet/timesheetApi";
    import employeeApi from "@/api/employee/employeeApi";
    import roleApi from "@/api/role/roleApi";
    import divisionApi from "@/api/division/divisionApi";
    // Components
    import TimesheetTab from "@/pages/Timesheets/tabs/TimesheetTab.vue";
    import PendingTab from "@/pages/Timesheets/tabs/PendingTab.vue";
    import UnsubmittedTab from "@/pages/Timesheets/tabs/UnsubmittedTab.vue";
    import ApprovedTab from "@/pages/Timesheets/tabs/ApprovedTab.vue";
    // Stores
    import {useTimesheetStore} from "@/store/timesheetStore";
    import {useResourceStore} from "@/store/resourceStore";
    import {useAuthStore} from "@/store/authStore";
    // Mixins
    import AuthMixin from "@/mixins/authMixin";


    export default {
        components: {
            TimesheetTab,
            PendingTab,
            UnsubmittedTab,
            ApprovedTab
        },

        mixins: [AuthMixin],

        data() {
            return {
                loading: false,
                currentTab: 'timesheet',
                filters: {
                    teammates: [],
                    job_title: null,
                    date: {
                        start: null,
                        end: null
                    }
                }
            }
        },

        mounted() {
            this.loadResources()
        },

        computed: {
            canActionPending() {
                return this.inRoles([
                    'Line Manager',
                    'General Manager',
                    'HR',
                    'Finance',
                    'CEO',
                    'Administrator'
                ])
            },

            ...mapState(useTimesheetStore, ['pending', 'unsubmitted', 'approved']),
            ...mapState(useResourceStore, ['roles', 'employees', 'divisions']),
            ...mapState(useAuthStore, ['user'])
        },

        methods: {
            async loadResources(force = true) {
                await this.loadPendingTimesheets()
                await this.loadUnsubmittedTimesheets()
                await this.loadApprovedTimesheets()
                await this.loadEmployees()
                await this.loadRoles()
                await this.loadDivisions()
                await this.loadJobTitles()
            },

            async loadDivisions() {
                this.loading = true
                let divisions = await divisionApi.fetchDivisions()
                this.setDivisions(divisions)
                this.loading = false
            },

            async loadEmployees() {
                this.loading = true
                let employees = await employeeApi.fetchAllEmployees()
                this.setEmployees(employees)
                this.loading = false
            },

            async loadJobTitles() {
                this.loading = true
                let employees = await employeeApi.fetchJobTitles()
                this.setJobTitles(employees)
                this.loading = false
            },

            async loadRoles() {
                this.loading = true
                let roles = await roleApi.fetchRoles()
                this.setRoles(roles.data)
                this.loading = false
            },

            async loadPendingTimesheets() {
                this.loading = true
                let pending = await timesheetApi.fetchPendingTimesheets()
                this.setPending(pending)
                this.loading = false
            },

            async loadUnsubmittedTimesheets() {
                this.loading = true
                let unsubmitted = await timesheetApi.fetchUnSumibttedTimesheets()
                this.setUnsubmitted(unsubmitted)
                this.loading = false
            },

            async loadApprovedTimesheets() {
                this.loading = true
                let approved = await timesheetApi.fetchApprovedTimesheets()
                this.setApproved(approved)
                this.loading = false
            },

            ...mapActions(useTimesheetStore, ['setPending', 'setUnsubmitted', 'setApproved']),
            ...mapActions(useResourceStore, ['setRoles', 'setEmployees', 'setDivisions', 'setJobTitles'])
        }
    }
</script>

<style>
    .timesheet-tabs {
        @apply tw-flex tw-border-b-[1px]
    }

    .timesheet-tabs .tab {
        @apply tw-relative tw-mr-8 tw-py-4 tw-font-bold tw-text-gray-400 tw-text-[.9rem] tw-cursor-pointer hover:tw-text-blue-900 hover:tw-border-b-blue-900 hover:tw-border-b-[2px]
    }

    .timesheet-tabs .tab .item-count-indicator {
        top: 8px;
        right: -12px;
        @apply tw-absolute tw-bg-red-500 tw-text-[.7rem] tw-text-white tw-px-1 tw-rounded-full
    }

    .timesheet-tabs .tab.active {
        @apply tw-text-blue-900 tw-border-b-blue-900 tw-border-b-[2px]
    }
</style>
