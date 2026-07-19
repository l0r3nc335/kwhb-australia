<template>
    <div class="tw-flex tw-flex-col tw-w-full">
        <VTable class="tw-w-full tw-mb-3" ref="timesheet_table">
            <thead class="tw-bg-gray-100">
            <tr>
                <th style="text-transform: capitalize;font-family: sans-serif;font-weight:bold;font-size: 16px"><b>Timesheet</b></th>
                <th class="tw-px-2" style="text-transform: capitalize; font-family: sans-serif;font-weight:bold;font-size: 16px">Category</th>
                <th style="text-transform: capitalize;font-family: sans-serif;font-weight:bold;font-size: 16px">Start Time</th>
                <th style="text-transform: capitalize;font-family: sans-serif;font-weight:bold;font-size: 16px">Finish Time</th>
                <th style="text-transform: capitalize;font-family: sans-serif;font-weight:bold;font-size: 16px">Break</th>
                <th style="text-transform: capitalize;font-family: sans-serif;font-weight:bold;font-size: 16px">Comment</th>
                <th style="text-transform: capitalize;font-family: sans-serif; text-align: center;font-weight:bold;font-size: 16px">Total</th>
                <th></th>
            </tr>
            </thead>
            <tbody >
            <tr v-for="(timesheet, index) in timesheets" class="tw-40" :class="[(timesheet.is_submitted && !timesheet.is_returned) ? 'tw-bg-gray-50' : '']">
                <td class="tw-border-r-[1px] tw-text-blue-900" >
                    <div class="tw-flex">
                        <VIcon icon="tabler-calendar-month" class="tw-mr-4"></VIcon>{{formatDate(timesheet.date, 'dddd')}}
                    </div>
                    <div class="tw-flex tw-justify-center tw-mt-1">
                        {{formatDate(timesheet.date, 'D MMM')}}
                    </div>
                </td>
                <td class="!tw-py-6 tw-relative" :class="[getValidations(timesheet).length > 0 ? '!tw-pb-14' : '']">
                    <AppCombobox :disabled="!isTimesheetEditable(timesheet)" @update:modelValue="ts => entryCategoryUpdated(timesheet, ts)" :item-props="true" :items="filteredCategories(timesheet.categories, timesheet.date, timesheet, index)" v-model="timesheet.category" style="width: 14rem" placeholder="Select Category" item-title="name" item-value="id"></AppCombobox>
                    <div v-if="getValidations(timesheet).length > 0" style="position:absolute; bottom:13px" class="tw-text-[.8rem] tw-text-red-600 tw-bg-red-100 tw-rounded-lg tw-px-3 tw-py-1 tw-w-[64.5rem]">
                        {{getValidations(timesheet)[0]}}
                    </div>
                </td>
                <td class="!tw-py-6" :class="[getValidations(timesheet).length > 0 ? '!tw-pb-14' : '']">
                    <AppTextField :class="[timesheet.start ? '' : 'error tw-border tw-rounded-lg tw-border-red-400']" :disabled="!isTimesheetEditable(timesheet)" placeholder="0:00" type="time" min="08:00:00" v-model="timesheet.start" ></AppTextField>
                </td>
                <td class="!tw-py-6" :class="[getValidations(timesheet).length > 0 ? '!tw-pb-14' : '']">
                    <AppTextField  :class="[timesheet.end ? '' : 'error tw-border tw-rounded-lg tw-border-red-400']" :disabled="!isTimesheetEditable(timesheet)" placeholder="0:00" type="time" v-model="timesheet.end" :label="getNextDayLabel(timesheet)"></AppTextField>
                </td>
                <td class="break-time !tw-py-6" :class="[getValidations(timesheet).length > 0 ? '!tw-pb-14' : '']">
                    <AppTextField :disabled="!isTimesheetEditable(timesheet)" placeholder="0:00"  type="number" v-model="timesheet.break"  style="width: 5rem"></AppTextField>
                </td>
                <td class="!tw-py-6" :class="[getValidations(timesheet).length > 0 ? '!tw-pb-14' : '']">
                    <AppTextField :class="[getValidations(timesheet).length === 0 ? '' : 'error tw-border tw-rounded-lg tw-border-red-400']"  :disabled="!isTimesheetEditable(timesheet)" placeholder="Enter Comment Here"  v-model="timesheet.comment" style="width: 20rem"></AppTextField>
                </td>
                <td>
                    <div class="tw-font-bold tw-w-[6rem] tw-text-center">
                        <div>
                            {{convertToReadableTime(timesheetLineTime(timesheet))}}
                        </div>
                    </div>
                </td>
                <td>
                    <template v-if="isTimesheetEditable(timesheet)">
                        <VBtn variant="outlined" :size="30" @click="openDeleteDialog(index)">
                            <VIcon icon="tabler-x"></VIcon>
                        </VBtn>
                    </template>
                    <div v-else class="tw-flex tw-justify-center tw-p-2 tw-border tw-rounded-lg tw-bg-gray-100">
                        <VIcon icon="tabler-lock"></VIcon>
                    </div>
                </td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td class="tw-text-center">
                    <div class="tw-font-bold tw-w-[6rem] tw-text-center">
                        <div>
                            {{convertToReadableTime(totalHours)}}
                        </div>
                    </div>
                </td>
                <td></td>
            </tr>
            </tbody>
        </VTable>
        <div v-if="timesheets.length === 0" class="tw-text-[1.2rem] tw-text-center tw-mt-4">
            No timesheet entries yet.
        </div>
        <VDialog width="200" v-model="showDeleteDialog">
            <DialogCloseBtn @click="showDeleteDialog=false" />
            <div class="tw-p-4 tw-rounded-lg tw-bg-white tw-flex tw-flex-col ">
                <div class="tw-py-2">Are you sure?</div>
                <button @click="deleteEntry" class="tw-text-white tw-rounded-lg tw-bg-red-700 hover:tw-bg-red-600 tw-mt-5 tw-py-1"><VIcon icon="tabler-trash" size="18" class="tw-mr-2 tw-mb-1"></VIcon>Remove Entry</button>
            </div>
        </VDialog>
    </div>
</template>
<script>

import moment from "moment/moment";

    export default {
        emits: ['delete', 'timesheet-validated'],

        props: {
            forceEdit: {
                type: Boolean,
                default: false
            },

            timesheets: {
                type: Array,
                required: true
            },

            categories: {
                type: Array,
                required: true
            }
        },

        data() {
            return {
                showDeleteDialog: false,
                selectedTimesheet: null,
            }
        },

        computed: {
            totalHours() {
                return timesheetsTotalTime(this.timesheets)
            },

            timesheetValidated() {
                let validated = true

                for(let i in this.timesheets) {
                    let vArr = this.getValidations(this.timesheets[i])
                    if (vArr.length > 0) {
                        validated = false
                    }
                }

                return validated
            }
        },

        methods: {
            
            entryCategoryUpdated(timesheet, category) {
                if ([6,7,14].includes(category?.id)) {
                    timesheet.break = 60
                } else  {
                    timesheet.break = 0
                }
            },

            getValidations(timesheet) {
                let validations = []
                let totalMinutes = 0
                let sMoment = ''
                let eMoment = ''
                let hasCOPhone = false
                let hasCOSite = false

                for (let i in this.timesheets) {
                    totalMinutes += (computeMinutesDifference(this.timesheets[i]?.start, this.timesheets[i]?.end, parseInt(this.timesheets[i]?.break)))

                    if (this.timesheets[i]?.category?.id === 12) {
                        hasCOPhone = true;
                    }
                    if (this.timesheets[i]?.category?.id === 13) {
                        hasCOSite = true;
                    }
                }

                if (totalMinutes/60 > 40) {
                    validations.push('Hours exceeded the 40 hour week')
                }

                let tc = typeof timesheet?.category == 'object' ? timesheet?.category?.id : timesheet?.category

                if (!(tc && [11,12,13].includes(tc)) && timesheet?.start && timesheet?.end && !timesheet?.comment) {
                    let totalMinutes = computeMinutesDifference(timesheet.start, timesheet.end, timesheet.break)
                    if (totalMinutes/60 >= 10) {
                        validations.push('This shift was over 10 hours')
                    }
                }

                if ((tc && [11,12,13].includes(tc)) && (hasCOPhone || hasCOSite) && !this.timesheets.some(ts => ts?.category?.id === 11)) {
                    validations.push('Must have logged On Call first');
                }

                if (!(tc && [11,12,13,15,16].includes(tc)) && (timesheet?.break == 0) && !timesheet?.comment) {
                    validations.push('No lunch break')
                }

                // if (tc && [15,16].includes(tc)  && !timesheet?.comment) {
                //     validations.push('Overtime')
                // }

                switch (timesheet?.category?.id) {
                    case 1: // Annual
                    case 2: // Ceremonial
                    case 3: // Community Service
                    case 4: // Compassionate
                    case 5: // Defense Reserve
                    case 6: // Flexi
                    case 7: // Foil
                    case 8: // Jury Service
                    case 9: // LWOP
                    case 10: // Long Service
                    case 14: // Ordinary Hours
                    case 17: // Parental
                    case 24: // Personal
                    case 19: // Incident
                    case 20: // Special
                    case 21: // Sorry
                    case 22: // Study
                    case 23: // TOIL
                        sMoment = moment(moment().format('YYYY-MM-DD') + ' ' + timesheet.start)
                        eMoment = moment(moment().format('YYYY-MM-DD') + ' ' + timesheet.end)
                        if (
                            (
                                parseInt(sMoment.format('HH')) < 8
                            )
                            || // Earlier than 8 am
                            (
                                parseInt(eMoment.format('HH')) > 16 || // After 4:30 pm
                                (parseInt(eMoment.format('HH')) === 16 && eMoment.minutes() > 30) // After 4:30 pm
                            )
                        )
                            validations.push('Start time should not be earlier than 8 am and end time must not be later than 4:30 pm.')
                        break;
                    case 11: // On Call
                    case 12: // Call Out - Phone
                    case 13: // Call Out - On Site
                    case 15: // Overtime - Paid
                    case 16: // Overtime - Toil
                        sMoment = moment(moment().format('YYYY-MM-DD') + ' ' + timesheet.start)
                        eMoment = moment(moment().format('YYYY-MM-DD') + ' ' + timesheet.end)
                        if (
                            !(
                                (
                                    sMoment.isSameOrAfter(moment(sMoment.format('YYYY-MM-DD') + ' 16:30')) ||
                                    sMoment.isSameOrBefore(moment(sMoment.format('YYYY-MM-DD') + ' 08:00'))
                                ) &&
                                (
                                    eMoment.isSameOrBefore(moment(eMoment.format('YYYY-MM-DD') + ' 08:00')) ||
                                    eMoment.isSameOrAfter(moment(eMoment.format('YYYY-MM-DD') + ' 16:30'))
                                )
                            )
                        )
                            validations.push('Start time should not be earlier than 4:30 pm and end time must not be later than 8 am.')
                        break;
                }

                switch (timesheet?.category?.id) {
                    case 1: // Annual
                    case 2: // Ceremonial
                    case 3: // Community Service
                    case 4: // Compassionate
                    case 5: // Defense Reserve
                    case 6: // Flexi
                        totalMinutes = computeMinutesDifference(timesheet.start, timesheet.end, timesheet.break)
                        if (totalMinutes/60 > 7.5) {
                            validations.push('Only max of 7 and a half hours is allowed to log in this entry.')
                        }
                        break;
                    case 7: // Foil
                        totalMinutes = computeMinutesDifference(timesheet.start, timesheet.end, timesheet.break)
                        if (totalMinutes/60 > 7.5) {
                            validations.push('Only max of 7 and a half hours is allowed to log in this entry.')
                        }
                        break;
                    case 8: // Jury Service
                    case 9: // LWOP
                    case 10: // Long Service
                    case 17: // Parental
                    case 24: // Personal
                    case 19: // Incident
                    case 20: // Special
                    case 21: // Sorry
                    case 22: // Study
                    case 23: // TOIL
                        totalMinutes = computeMinutesDifference(timesheet.start, timesheet.end, timesheet.break)
                        if (totalMinutes/60 > 7.5) {
                            validations.push('Only max of 7 and a half hours is allowed to log in this entry.')
                        }
                        break;
                    case 11: // On Call
                        totalMinutes = computeMinutesDifference(timesheet.start, timesheet.end, timesheet.break)
                        if (totalMinutes/60 > 10) {
                            validations.push('Only max of 10 hours is allowed to log in this entry.')
                        }
                        break;
                    case 13: // Call Out - On Site
                        totalMinutes = computeMinutesDifference(timesheet.start, timesheet.end, timesheet.break)
                        if (totalMinutes/60 < 3) {
                            validations.push('Must log at least 3 hours for this entry.')
                        }
                        break;
                    case 12: // Call Out - Phone
                        totalMinutes = computeMinutesDifference(timesheet.start, timesheet.end, timesheet.break)
                        if (totalMinutes < 15) {
                            validations.push('Must log at least 15 minutes for this entry.')
                        } else if (totalMinutes % 15 !== 0) {
                            validations.push('Can only log in 15 minutes incriments.');
                        }
                        break;
                    case 14: // Ordinary Hours
                        totalMinutes = computeMinutesDifference(timesheet.start, timesheet.end, timesheet.break)
                        if (totalMinutes/60 > 7.5) {
                            validations.push('Only max of 7 and a half hours is allowed to log in this entry.')
                        }
                        break;
                }

                switch (timesheet?.category?.id) {
                    case 6: // Flexi
                        if (timesheet.break < 60) {
                            validations.push('Must have at least 1 hour of break.')
                        }
                        break;
                    case 7: // Foil
                        if (timesheet.break < 60) {
                            validations.push('Must have at least 1 hour of break.')
                        }
                        break;
                    case 14: // Ordinary Hours
                        if (timesheet.break < 60) {
                            validations.push('Must have at least 1 hour of break.')
                        }
                        break;
                    case 24: // Personal
                        if (timesheet.break < 60) {
                            validations.push('Must have at least 1 hour of break.')
                        }
                        break;
                }

                switch (timesheet?.category?.id) {
                    case 7: // Foil
                        if (!timesheet?.comment) {
                            validations.push('Must enter travel info.')
                        }
                        break;
                    case 12: // Call Out - Phone Call
                        if (!timesheet?.comment) {
                            validations.push('Must Enter callout info.')
                        }
                        break;
                    case 13: // Call Out - Onsite
                        if (!timesheet?.comment) {
                            validations.push('Must Enter callout info.')
                        }
                        break;
                    case 15: // Overtime - Paid
                        if (!timesheet?.comment) {
                            validations.push('Must Enter approval person.')
                        }
                        break;
                    case 16: // Overtime - Toil
                        if (!timesheet?.comment) {
                            validations.push('Must Enter approval person.')
                        }
                        break;
                    case 23: // Toil
                        if (!timesheet?.comment) {
                            validations.push('Please input entry info or details.')
                        }
                        break;
                }

                if (timesheet.category && validations.length == 0 && (!timesheet?.start || !timesheet?.end)) {
                    validations.push('Please enter start and finish time')
                }

                return validations
            },

            openDeleteDialog(entry) {
                this.selectedTimesheet = entry
                this.showDeleteDialog = true
            },

            deleteEntry() {
                this.$emit('delete', this.selectedTimesheet)
                this.showDeleteDialog = false
            },

            isTimesheetEditable(timesheet) {
                return timesheet.is_submitted === 0 || timesheet.is_returned === 1 || this.forceEdit
            },
            filteredCategories(data, date, timesheet, index) {
                const categoryDate = new Date(date);
                if (isNaN(categoryDate)) {
                    return data;
                }
                let excludedCategories = [15];

                const dayIndex = categoryDate.getDay();
                if (dayIndex === 0 || dayIndex === 6) {
                    excludedCategories.push(14);
                }

                let totalMinutes = 0

                for (let i in this.timesheets) {
                    if (index != i && timesheet?.category != 15) {
                        totalMinutes += computeMinutesDifference(
                            this.timesheets[i].start,
                            this.timesheets[i].end,
                            parseInt(this.timesheets[i].break)
                        );
                    }
                }

                if (totalMinutes/60 >= 7.5) {
                    excludedCategories = excludedCategories.filter(id => id !== 15);
                }

                return data.filter(category => {
                    return !excludedCategories.includes(category.id);
                });
            },
        },

        watch: {
            timesheets: {
                handler() {
                    this.$forceUpdate
                    this.$emit('timesheet-validated', this.timesheetValidated)
                },
                deep: true
            },
        }
    }
</script>
<style>
    .app-text-field {
        position: relative !important;
    }

    .app-text-field label {
        position: absolute;
        top: -18px;
        color:red !important;
        font-size: .68rem !important;
    }

    .error.tw-border.tw-rounded-lg.tw-border-red-400 {

    }
</style>
