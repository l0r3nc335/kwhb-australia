<template>
    <div class="tw-flex tw-flex-col tw-rounded-lg tw-shadow-lg">
        <div class="tw-flex tw-w-full tw-px-5 tw-py-1 tw-bg-gray-300 ">
            <span class="tw-text-[1.5rem] tw-bold">
                <template v-if="viewMode">Template</template><template v-else>Template</template> Preview
            </span>
        </div>
        <div class="tw-flex tw-px-6 tw-py-3 tw-bg-white">
            <div class="tw-text-[1.4rem]" v-if="!editMode">{{newTemplate?.name}}</div>
            <div v-else class="tw-flex tw-w-full">
                <VTextField v-model="newTemplate.name" class="tw-w-full"></VTextField>
            </div>
        </div>
        <div class="tw-py-3 tw-px-2 tw-bg-white">
            <VTable class="tw-w-full tw-mb-3" ref="timesheet_table">
                <thead class="tw-bg-gray-100">
                <tr>
                    <th style="text-transform: capitalize;font-family: sans-serif"><b>Timesheet</b></th>
                    <th class="tw-px-2" style="text-transform: capitalize; font-family: sans-serif">Category</th>
                    <th style="text-transform: capitalize;font-family: sans-serif">Start Time</th>
                    <th style="text-transform: capitalize;font-family: sans-serif">Finish Time</th>
                    <th style="text-transform: capitalize;font-family: sans-serif">Break</th>
                    <th style="text-transform: capitalize;font-family: sans-serif">Comment</th>
                    <th style="text-transform: capitalize;font-family: sans-serif">Total</th>
                    <th v-if="editMode"></th>
                </tr>
                </thead>
                <tbody >
                <tr v-for="timesheet in entries">
                    <td class="tw-border-r-[1px] tw-text-blue-900" style="width: 10rem"><VIcon icon="tabler-calendar-month" class="tw-mr-4"></VIcon>{{formatDate(timesheet.date, 'dddd')}}</td>
                    <td class="py-6">
                        <AppCombobox :disabled="!editMode" :items="categories" v-model="timesheet.category" style="width: 14rem" placeholder="Categories" item-title="name" item-value="id"></AppCombobox>
                    </td>
                    <td>
                        <AppTextField :disabled="!editMode" placeholder="0:00" type="time" v-model="timesheet.start"></AppTextField>
                    </td>
                    <td>
                        <AppTextField :disabled="!editMode" placeholder="0:00" type="time" v-model="timesheet.end" :label="getNextDayLabel(timesheet)"></AppTextField>
                    </td>
                    <td class="break-time">
                        <AppTextField :disabled="!editMode" placeholder="0:00"  type="number" v-model="timesheet.break"  style="width: 5rem"></AppTextField>
                    </td>
                    <td>
                        <AppTextField :disabled="!editMode" placeholder="Enter Comment Here"  v-model="timesheet.comment" style="width: 20rem"></AppTextField>
                    </td>
                    <td>
                        <div class="tw-font-bold tw-w-[6rem]">{{convertToReadableTime(timesheetLineTime(timesheet))}}</div>
                    </td>
                    <td v-if="editMode">
                        <VBtn variant="outlined" :size="30" @click="deleteEntry(timesheet)">
                            <VIcon icon="tabler-x"></VIcon>
                        </VBtn>
                    </td>
                </tr>
                </tbody>
            </VTable>
        </div>
        <div class="tw-flex tw-justify-end tw-px-4 tw-py-3 tw-bg-gray-100">
            <VBtn v-if="applyMode" color="warning" @click="applyTemplate"><VIcon icon="tabler-copy" class="tw-mr-3"></VIcon>Apply</VBtn>
            <template v-if="editMode" >
                <VBtn color="info" class="tw-mr-3" @click="reset"><VIcon icon="tabler-refresh" class="tw-mr-3" ></VIcon>Reset</VBtn>
                <VBtn color="success" @click="updateTemplate"><VIcon icon="tabler-edit" class="tw-mr-3"></VIcon>Save</VBtn>
            </template>
        </div>
    </div>
</template>
<script>
    import moment from "moment";

    export default {
        emits: ['update-template', 'apply-template', 'close'],
        props: {
            template: {
                required: true,
                type: Array
            },

            categories: {
                required: true,
                type: Array
            },

            editMode: {
                type: Boolean,
                default: false
            },

            viewMode: {
                type: Boolean,
                default: false
            },

            applyMode: {
                type: Boolean,
                default: false
            },
        },

        data() {
            return {
                newTemplate: {
                    name: null
                },
                entries: []
            }
        },

        mounted() {
            this.newTemplate = this.template
            this.entries = this.parseTemplateTimesheets(this.newTemplate)
        },

        methods: {
            applyTemplate() {
                this.$emit('apply-template', {timesheets: this.entries})
                this.newTemplate = null
                this.entries = []
                this.$emit('close')
            },

            updateTemplate() {
                this.$emit('update-template', {id: this.template.id, name: this.newTemplate.name, timesheets: this.entries})
                this.newTemplate = null
                this.entries = []
                this.$emit('close')
            },

            reset() {
                this.entries = [];
                this.newTemplate = this.template
                this.entries = this.parseTemplateTimesheets(this.template)
            },

            deleteEntry(timesheet) {
                this.entries = this.entries.filter(entry => entry.id !== timesheet.id)
            },

            getNextDayLabel(entry) {
                if (entry.end < entry.start) {
                    return moment(entry.date).add(1, 'days').format('ddd, DD MMMM')
                }

                return '';
            },

            parseTemplateTimesheets(template) {
                if (typeof template.data === 'string') {
                    return JSON.parse(JSON.parse(template.data)) ?? []
                }

                return template.data
            }
        },
    }
</script>
