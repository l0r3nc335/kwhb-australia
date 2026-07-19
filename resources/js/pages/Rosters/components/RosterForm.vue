<template>
    <VNavigationDrawer
        :temporary="true"
        :scrim="true"
        :touchless="true"
        :width="500"
        location="end"
        class="scrollable-content px-3"
        @click:outside="$emit('close');open=false"
        v-model="open"
    >
        <AppDrawerHeaderSection
            :title="selectedShift ? 'Edit shift' : label"
            @cancel="$emit('close')"
        />
        <div class="tw-flex tw-flex-col tw-px-6">
            <div class="tw-flex tw-flex-col tw-mb-8">
                <label class="tw-mb-2 tw-font-bold">Select Shift Type</label>
                <AppCombobox class="tw-mr-3 !tw-grow-0" clearable v-model="form.shiftType" :items="shiftTypes" placeholder="Shift Type" item-title="name" item-value="id"></AppCombobox>
            </div>
            <div class="tw-flex tw-mb-2">
                <div class="tw-flex tw-flex-col tw-mr-3">
                    <label class="tw-mb-2 tw-font-bold">Start Time</label>
                    <AppTextField v-model="form.startTime"  placeholder="0:00" type="time" min="08:00:00"></AppTextField>
                </div>
                <div class="tw-flex tw-flex-col">
                    <label class="tw-mb-2 tw-font-bold">Finish Time</label>
                    <AppTextField  v-model="form.finishTime" placeholder="0:00" type="time" min="08:00:00"></AppTextField>
                </div>
            </div>
            <div class="tw-flex tw-mb-2">
                <VCheckbox color="error" v-model="form.isNextDayFinish" :true-value="1"></VCheckbox>
                <div class="tw-py-2">
                    <label class="tw-font-bold">Shift Finish Next Day</label>
                </div>
            </div>
            <div class="tw-flex">
                <VCheckbox color="error" v-model="form.isRepeat" :true-value="1"></VCheckbox>
                <div class="tw-py-2">
                    <label class="tw-font-bold">Repeat</label>
                </div>
            </div>
            <div class="tw-flex tw-mb-4">
                <div class="tw-flex tw-flex-col tw-mr-3">
                    <label class="tw-mb-2 tw-font-bold">Start Date</label>
                    <AppTextField v-model="form.startDate" prepend-inner-icon="tabler-calendar" placeholder="0:00" type="date"></AppTextField>
                </div>
                <div class="tw-flex tw-flex-col">
                    <label class="tw-mb-2 tw-font-bold">Finish Date</label>
                    <AppTextField v-model="form.endDate" prepend-inner-icon="tabler-calendar"  placeholder="0:00" type="date"></AppTextField>
                </div>
            </div>
            <div class="tw-flex tw-mb-4">
                <div class="tw-flex tw-flex-col ">
                    <label class="tw-mb-2 tw-font-bold">Recurrence</label>
                    <div class="tw-flex tw-justify-between">
                        <div class="tw-flex tw-mr-4">
                            <VCheckbox color="error" value="weekly" v-model="form.recurrence"></VCheckbox>
                            <div class="tw-py-2">
                                <label >Weekly</label>
                            </div>
                        </div>
                        <div class="tw-flex tw-mr-4">
                            <VCheckbox color="error" value="fortnightly" v-model="form.recurrence"></VCheckbox>
                            <div class="tw-py-2">
                                <label>Fortnightly</label>
                            </div>
                        </div>
                        <div class="tw-flex tw-mr-4">
                            <VCheckbox color="error" value="monthly" v-model="form.recurrence"></VCheckbox>
                            <div class="tw-py-2">
                                <label>Monthly</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tw-flex tw-flex-col tw-mb-4">
                <label class="tw-mb-2 tw-font-bold">Occurs On</label>
                <AppCombobox class="tw-mr-3 !tw-grow-0" clearable v-model="form.occursOn" :items="occursOnOptions" placeholder="Day" multiple item-title="name" item-value="id"></AppCombobox>
            </div>
            <div class="tw-flex tw-flex-col tw-mb-8">
                <label class="tw-mb-2 tw-font-bold">Assign To</label>
                <AppCombobox class="tw-mr-3 !tw-grow-0 tw-mb-3" :disabled="selectedShift" clearable v-model="selectedDivision" :items="divisions" placeholder="Department" item-title="name" item-value="id"></AppCombobox>
                <AppCombobox class="tw-mr-3 !tw-grow-0" :disabled="selectedShift" clearable v-model="form.assignedTo" :items="staffOptions" prepend-inner-icon="tabler-search" :placeholder="staffOptionsPlaceHolder" item-title="name" item-value="id"></AppCombobox>
            </div>
            <div class="tw-flex">
                <VBtn color="primary" class="tw-mr-3" @click="submitShift" :disabled="!formValidated">Save</VBtn>
                <VBtn color="secondary" @click="$emit('close')">cancel</VBtn>
            </div>
        </div>

    </VNavigationDrawer>
</template>
<script>
    import employeeApi from "@/api/employee/employeeApi";

    export default {
        emits: ['filter-change'],
        props: {
            drawerOpen: {
                type: Boolean,
                default: false
            },

            form: {
                type: Object,
                required: true,
            },

            selectedShift: {
                default: null
            },

            label: {
                default: 'Add New Shift'
            },

            shiftTypes: {
                type: Array,
                default: []
            },

            divisions: {
                type: Array,
                default: []
            },

            staffs: {
                type: Array,
                default: []
            }
        },

        data() {
            return {
                occursOnOptions: [
                    'Monday',
                    'Tuesday',
                    'Wednesday',
                    'Thursday',
                    'Friday',
                    'Saturday',
                    'Sunday'
                ],
                selectedDivision: null,
                staffOptions: [],
                recurrence: 'weekly',
                open: false
            }
        },

        mounted() {
            this.open = this.drawerOpen
        },

        computed: {
            formValidated() {
                return this.form.shiftType &&
                    this.form.startTime &&
                    this.form.finishTime &&
                    this.form.startDate &&
                    this.form.endDate &&
                    this.form.occursOn.length > 0 &&
                    this.form.assignedTo
            },

            staffOptions() {
                if (this.selectedDivision)
                    return this.staffs.filter(s => s.division_id == this.selectedDivision?.id)

                return this.staffs
            },

            staffOptionsPlaceHolder() {
                if (!this.selectedDivision) {
                    return 'Select a Department'
                }

                if (this.staffOptions.length === 0) {
                    return 'No staff currently added for this department'
                }

                return 'Select staff'
            },
        },

        methods: {
            async loadStaffs() {
                this.form.assignedTo = null
                this.staffOptions = await employeeApi.fetchAllEmployees({
                    division_id: this.selectedDivision.id
                });
            },

            submitShift() {
                if (this.selectedShift) {
                    this.$emit('update-shift')
                } else {
                    this.$emit('add-shift')
                }
            }
        },

        watch: {
            selectedDivision() {
                if (this.selectedDivision) {
                    this.loadStaffs()
                }
            },

            filters() {
                this.$emit('filter-change', this.filters)
            },

            drawerOpen() {
                this.open = this.drawerOpen
            }
        }
    }
</script>

<style>
input[type='date']::-webkit-calendar-picker-indicator {
    background: transparent;
    bottom: 0;
    color: transparent;
    cursor: pointer;
    height: auto;
    left: 0;
    position: absolute;
    right: 0;
    top: 0;
    width: auto;
}

.v-navigation-drawer__scrim {
    pointer-events: none !important;
}
</style>
