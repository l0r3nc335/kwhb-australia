<template>
    <VDialog v-model="showDialog" width="300">
        <template #activator="{props}">
            <VBtn v-bind="props"  color="kwhb" class="tw-mr-4" @click="showDialog=true">
                <VIcon icon="tabler-circle-plus"/>
                <span class="tw-ml-1">Add New Row</span>
            </VBtn>
        </template>
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="showDialog=false" />

        <div class="tw-flex tw-flex-col tw-bg-white tw-p-4 tw-rounded-lg">
            <div class="tw-text-sm tw-mb-3">Select Timesheet Date</div>
            <hr>
            <div class="tw-pt-4">
                <div v-for="date in dates"
                     class="date-item tw-flex" @click="selectDate(date)">
                    <VIcon icon="tabler-calendar" class="tw-mr-3" ></VIcon>
                    <div class="tw-flex tw-justify-between tw-flex tw-w-full">
                        <div>{{changeDateFormat(date)}}</div>
                        <div>({{getDayName(date)}})</div>
                    </div>
                </div>
                <div v-if="dates.length === 0" class="tw-text-[.8rem] tw-px-4 tw-text-gray-500 tw-flex">
                    <VIcon icon="tabler-info-triangle" class="tw-text-blue-500 tw-text-[2rem] tw-mr-5" style="margin-top:2px"></VIcon>
                    All dates for this week has entries.
                </div>
            </div>
        </div>
    </VDialog>
</template>
<script>
    import moment from "moment";

    export default {
        emits: ['selected'],
        props: {
            dates: {
                type: Array,
                required: true
            }
        },
        data() {
            return {
                showDialog: false
            }
        },

        methods: {
            changeDateFormat(date) {
                let dateArr = date.split('-')

                return  `${dateArr[2]}-${dateArr[1]}-${dateArr[0]}`
            },

            selectDate(date) {
                this.$emit('selected', date)
                this.showDialog = false
            },

            getDayName(date) {
                let dateArr = date.split('-')

                return moment(date).format('dddd')
            }
        }
    }
</script>
<style>
    .date-item {
        @apply tw-cursor-pointer hover:tw-bg-indigo-500 tw-flex tw-mb-3 tw-py-1 tw-px-2 tw-border tw-border-gray-100 tw-rounded-lg tw-text-white tw-bg-indigo-900
    }
</style>
