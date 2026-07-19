<template>
    <div class="tw-flex tw-flex-col">
        <div class="tw-text-[2rem] tw-font-bold tw-text-gray-500 tw-mb-4">
            Leave Management
        </div>
        <div class="leave-tabs">
            <div v-if="hasPermissions('people.leave_management_leave_request.view')" class="tab" :class="[currentTab === 'leave_requests' ? 'active' : '']" @click="currentTab = 'leave_requests'">Leave Requests</div>
            <div v-if="hasPermissions('people.leave_management_leave_calendar.view')" class="tab" :class="[currentTab === 'leave_calendar' ? 'active' : '']" @click="currentTab = 'leave_calendar'">Leave Calendar</div>
        </div>
        <div class="tw-flex tw-flex-col tw-mt-2 tw-py-4">
            <LeaveRequestTab v-if="currentTab === 'leave_requests' && hasPermissions('people.leave_management_leave_request.view')"></LeaveRequestTab>
            <LeaveCalendarTab v-if="currentTab === 'leave_calendar' && hasPermissions('people.leave_management_leave_calendar.view')"></LeaveCalendarTab>
        </div>
    </div>
</template>

<script>
    import LeaveCalendarTab from "@/pages/Leaves/tabs/LeaveCalendarTab.vue";
import LeaveRequestTab from "@/pages/Leaves/tabs/LeaveRequestTab.vue";

    export default {
        components: {
            LeaveRequestTab,
            LeaveCalendarTab
        },

        data() {
            return {
                loading: false,
                currentTab: 'leave_requests',
            }
        },
    }
</script>

<style>
    .leave-tabs {
        @apply tw-flex tw-border-b-[1px]
    }

    .leave-tabs .tab {
        @apply tw-mr-8 tw-py-4 tw-text-gray-400 tw-text-[1.1rem] tw-cursor-pointer hover:tw-text-blue-900
    }

    .leave-tabs .tab.active {
        position: relative;
        @apply tw-text-gray-600 tw-font-bold
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
</style>
