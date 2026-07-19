<template>
    <div class="tw-flex tw-flex-col">
        <TimesheetUserWeekPeriods :periods="periods" title="Unsubmitted" :show-approve-button="true" :show-job-title-selector="true" :show-role-selector="false"></TimesheetUserWeekPeriods>
    </div>
</template>
<script>
import TimesheetUserWeekPeriods from "@/pages/Timesheets/components/timesheets/TimesheetUserWeekPeriods.vue"

export default {
    components: {
        TimesheetUserWeekPeriods
    },

    props: {
        periods: {
            required: true,
            type: Array
        }
    },
}
</script>
