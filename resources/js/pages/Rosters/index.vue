<template>
    <div class="tw-flex tw-flex-col tw-py-4">
        <div class="tw-py-4">
            <div class="tw-text-[2rem] tw-font-bold"><span class="tw-text-gray-400">Roster /</span> {{filters.dateRangeType}}</div>
        </div>
        <RosterFilter class="tw-mb-6"
                      :filters="filters"
                      :date-range-types="dateRangeTypes"
                      :locations="locations"
                      :shift-types="shiftTypes"
                      :role-types="roleTypes"
                      :staffs="rosterUsers"
                      @date-range-index-change="dri => dateRangeIndex = dri"
                      @filter-change="updateFilters"
                      @add-shift="openShiftForm"
        >
        </RosterFilter>
        <RosterTable v-if="rosters.length > 0 && rosterUsers.length > 0"
                     :location="filters.location"
                     :data="rosterUsers"
                     :shifts="formattedShifts"
                     :no-shifts="noShifts"
                     :date-range-index="dateRangeIndex"
                     :date-range-type="filters?.dateRangeType"
                     :date-group="filters.dateRangeType"
                     @remove-employee="removeEmployee"
                     @edit-shift="editShift"
        >
        </RosterTable>
        <div v-else-if="!filters.location" class="tw-py-2 tw-shadow tw-p-4" style="min-height: 400px">Please select a location.</div>
        <div v-else class="tw-py-2 tw-shadow tw-p-4" style="min-height: 400px">
            No staff added yet.
        </div>
        <div class="tw-flex tw-my-4 tw-py-6 tw-px-4 tw-bg-gray-100">
            <div>
                <button class="tw-px-4 tw-bg-gray-500 hover:tw-bg-gray-700 tw-rounded-lg tw-mr-3 tw-text-white tw-text-[.9rem]"
                        :class="[!filters.location ? 'tw-cursor-not-allowed' : '']"
                        style="height: 2.5rem"
                        :disabled="!filters.location"
                        @click="showAddEmployeeDialog = true">
                    <VIcon class="tw-text-[1rem] tw-mr-2" icon="tabler-plus"></VIcon>
                    <template v-if="!filters.location">Please select a location</template>
                    <template v-else>Add Employee</template>
                </button>
            </div>
            <div class="tw-ml-10 tw-flex" v-if="shiftTypes.length > 0">
                <div class="tw-grid tw-grid-cols-2 tw-gap-2 tw-px-2">
                    <div v-for="type in shiftTypes" class="tw-text-[.65rem] tw-flex">
                        <div class="tw-flex tw-flex-col tw-justify-center tw-mr-2">
                            <div :class="`tw-bg-${getTypeColor(type.id)}`" style="width: 6px; height:6px;border-radius: 100%"></div>
                        </div>
                        <div class="tw-flex tw-flex-col tw-justify-center">
                            {{type.name}} : {{getTypeCount(type.id)}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="hasPermissions('operational.roster_templates')" class="tw-flex tw-flex-col tw-mt-10">
            <div class=" tw-flex tw-mb-4">
                <div>
                    <VBtn variant="outlined" color="secondary" class="tw-mr-4">
                        <VIcon icon="tabler-files"/>
                        <span class="tw-ml-2">Copy Last Week</span>
                    </VBtn>
                    <RosterTemplateForm default-name="Template Name" @save="name => saveTemplate(name)"></RosterTemplateForm>
                </div>
            </div>
            <RosterTemplateTable :templates="templates" @reload-templates="loadTemplates(filters.location?.id)"></RosterTemplateTable>
        </div>
        <RosterForm @add-shift="addShift"
                    @update-shift="updateShift"
                    :form="shiftForm"
                    :drawer-open="openAddShiftForm"
                    @close="selectedShift=null;openAddShiftForm = false"
                    :staffs="rosterUsers"
                    :shift-types="shiftTypes"
                    :divisions="divisions" :selected-shift="selectedShift">
        </RosterForm>
        <VDialog v-model="showAddEmployeeDialog" width="400">
            <div class="tw-flex tw-flex-col tw-bg-white tw-p-4 tw-rounded tw-shadow-lg">
                <div class="tw-mb-4">
                    <AppCombobox v-model="selectedEmployee"  :items="employeesOptions" item-title="name" item-value="id" placeholder="Select employee" clearable></AppCombobox>
                </div>
                <div class="tw-flex tw-justify-end">
                    <VBtn @click="cancelEmployeeSelect" color="secondary" class="tw-mr-2">Cancel</VBtn>
                    <VBtn @click="addEmployee" prepend-icon="tabler-plus" :disabled="!selectedEmployee">Add</VBtn>
                </div>
            </div>
        </VDialog>
    </div>
</template>

<script>
    // Plugins
    import {mapState, mapActions} from "pinia";
    // Components
    import RosterFilter from "@/pages/Rosters/components/RosterFilter.vue";
    import RosterTable from "@/pages/Rosters/components/RosterTable.vue";
    import RosterForm from "@/pages/Rosters/components/RosterForm.vue";
    import RosterTemplateForm from "@/pages/Rosters/components/RosterTemplateForm.vue";
    import RosterTemplateTable from "@/pages/Rosters/components/RosterTemplateTable.vue";
    // Apis
    import locationApi from "@/api/location/locationApi";
    import rosterApi from "@/api/roster/rosterApi";
    import employeeApi from "@/api/employee/employeeApi";
    import divisionApi from "@/api/division/divisionApi";
    import roleApi from "@/api/role/roleApi";
    // Stores
    import {useRosterStore} from "@/store/rosterStore";

    export default {
        components: {
            RosterFilter,
            RosterTable,
            RosterForm,
            RosterTemplateForm,
            RosterTemplateTable
        },

        data() {
            return {
                filters: {
                    location: null,
                    staff: null,
                    shiftType: null,
                    roleType: null,
                    dateRangeType: 'Daily',
                    dateRangeStart: null,
                    dateRangeEnd: null
                },
                rosters: [],
                templates: [],
                employees: [],
                divisions: [],
                openAddShiftForm: false,
                selectedShift: null,
                selectedEmployee: null,
                shiftForm: {
                    shiftType: null,
                    startTime: null,
                    finishTime: null,
                    shiftFinishNextDay: false,
                    isRepeat: false,
                    startDate: null,
                    endDate: null,
                    recurrence: 'weekly',
                    occursOn: [],
                    assignedTo: null
                },
                dateRangeTypes: [
                    'Daily',
                    'Weekly',
                    'Fortnightly',
                    'Monthly'
                ],
                locations: [],
                staffs: [],
                shiftTypes: [],
                roleTypes: [],
                showAddEmployeeDialog: false,
                dateRangeIndex: 0
            }
        },

        computed: {
            rosterUsers() {
                if (this.rosters.length > 0) {
                    return this.rosters[0].users
                }

                return []
            },

            noShifts() {
                if (this.rosterUsers.length > 0) {
                    let addedUserIds = this.rosterUsers.map(ru => ru.id)

                    return this.employeesOptions.filter(e => !addedUserIds.includes(e.user_id))
                }

                return this.employeesOptions
            },

            employeesOptions() {
                if (this.rosterUsers.length > 0) {
                    let addedUserIds = this.rosterUsers.map(ru => ru.id)

                    return this.employees.filter(e => !addedUserIds.includes(e.user_id))
                }

                return this.employees
            },

            formattedShifts() {
                let data = []
                let shifts = this.rosters[0]?.shifts || []

                for (let i in shifts) {
                    let shift = shifts[i]
                    if (data[shift.type]) {
                        data[shift.type].push(shift)
                    } else {
                        data[shift.type] = [shift]
                    }
                }

                let formattedShifts = []

                for(let i in data) {
                    if (data[i]) {
                        formattedShifts.push({
                          type: parseInt(i),
                          data: data[i]
                      })
                    }
                }

                if (this.filters?.shiftType) {
                    formattedShifts = formattedShifts.filter(fs => fs.type == this.filters.shiftType.id)
                }

                if (this.filters?.staff) {
                    formattedShifts = formattedShifts.map(fs => {
                        return {...fs, data: fs.data.filter(d => d.user_id == this.filters.staff.id)}
                    })
                }

                if (this.filters?.roleType) {
                    formattedShifts = formattedShifts.map(fs => {
                        return {...fs, data: fs.data.filter(d => d.user.role_id == this.filters.roleType.id)}
                    })
                }

                return formattedShifts
            },

            ...mapState(useRosterStore, ['filters'])
        },

        mounted() {
            this.loadResources()

            if (this.filters.location) {
                this.loadRosterEmployees(this.filters.location?.id)
            }
        },

        methods: {
            async loadTemplates() {
                this.loading = true
                this.templates = await rosterApi.loadTemplates(this.filters.location?.id)
                this.loading = false
            },

            async saveTemplate(name) {
                this.loading = true
                if (this.rosters.length > 0) {
                    // let shifts = []
                    // for(let i in this.rosters[0].shifts) {
                    //     let s = this.rosters[0].shifts[i]
                    //     s['user'] = null
                    //     shifts.push(s)
                    // }

                    await rosterApi.saveTemplate(name, this.filters.location?.id, this.rosters[0].shifts)
                    await this.loadTemplates()
                    this.$toast.success('Template Saved!')
                } else {
                    this.$toast.error('No roster shifts found!')
                }
                this.loading = false
            },

            getTypeCount(type) {
                let count = 0

                for (let shift in this.rosters[0]?.shifts) {
                    if (this.rosters[0]?.shifts[shift].type === type) {
                        count++
                    }
                }

                return count
            },

            getTypeColor(type) {
                let color = 'gray'

                switch (type) {
                    case 1: // Agency RAN
                        color = 'amber'
                        break;
                    case 2: // Permanent RAN
                        color = 'yellow'
                        break;
                    case 3: // First Responder
                        color = 'red'
                        break;
                    case 4: // Second Responder
                        color = 'orange'
                        break;
                    case 5: // GP
                        color = 'blue'
                        break;
                    case 6: // Program Staff
                        color = 'stone'
                        break;
                    case 7: // PHSCC
                        color = 'pink'
                        break;
                    case 8: // On Call
                        color = 'green'
                        break;
                    case 9: // Leave
                        color = 'fuchsia'
                        break;
                }

                return `${color}-600`
            },

            openShiftForm() {
                this.resetShiftForm()
                this.openAddShiftForm = !this.openAddShiftForm
            },

            updateFilters(filters) {
                this.filters = filters
            },

            loadResources() {
                this.loadRosters()
                this.loadLocations()
                this.loadShiftTypes()
                this.loadDivisions()
                this.loadRoleTypes()
            },

            async updateShift() {
                let form = {}
                form.location_id = this.filters.location?.id
                form.user_id = this.selectedShift?.user_id
                form.recurrence = this.shiftForm?.recurrence
                form.start_time = this.shiftForm?.startTime
                form.finish_time = this.shiftForm?.finishTime
                form.start_date = this.shiftForm?.startDate
                form.end_date = this.shiftForm?.endDate
                form.is_next_day_finish = this.shiftForm?.isNextDayFinish
                form.is_repeat = this.shiftForm?.isRepeat
                form.occurs_on = this.shiftForm?.occursOn
                form.type = this.shiftForm?.shiftType

                await rosterApi.updateShift(this.selectedShift?.id, form)
                this.$toast.success('Shift Updated!')
                await this.loadRosters()
            },

            async addShift() {
                let form = {}

                form.location_id = this.filters.location?.id
                form.user_id = this.shiftForm?.assignedTo?.employee?.user_id
                form.recurrence = this.shiftForm?.recurrence
                form.start_time = this.shiftForm?.startTime
                form.finish_time = this.shiftForm?.finishTime
                form.start_date = this.shiftForm?.startDate
                form.end_date = this.shiftForm?.endDate
                form.is_next_day_finish = this.shiftForm?.isNextDayFinish
                form.is_repeat = this.shiftForm?.isRepeat
                form.occurs_on = this.shiftForm?.occursOn
                form.type = this.shiftForm?.shiftType?.id


                await rosterApi.addShift(form)
                this.resetShiftForm()
                this.loadRosters()
                this.openAddShiftForm = false
                this.$toast.success('New Shift Added!')
            },

            resetShiftForm() {
              this.shiftForm = {
                  shiftType: null,
                  startTime: null,
                  finishTime: null,
                  shiftFinishNextDay: false,
                  isRepeat: false,
                  startDate: null,
                  endDate: null,
                  recurrence: null,
                  occursOn: [],
                  assignedTo: null
              }
            },

            async loadDivisions() {
                this.divisions = await divisionApi.fetchDivisions()
            },

            cancelEmployeeSelect() {
                this.showAddEmployeeDialog = false
                this.selectedEmployee = null
            },

            async addEmployee() {
                await rosterApi.addRosterEmployee(this.selectedEmployee.location_id, this.selectedEmployee.user_id)
                this.showAddEmployeeDialog = false
                this.$toast.success("Employee added to roster!")
                this.selectedEmployee = null
                await this.loadRosterEmployees(this.filters.location?.id)
                await this.loadRosters()
            },

            editShift(shift) {
                this.selectedShift = shift
                this.shiftForm.shiftType = this.selectedShift.type
                this.shiftForm.startTime = this.selectedShift.start
                this.shiftForm.finishTime = this.selectedShift.end
                this.shiftForm.shiftFinishNextDay = this.selectedShift.is_next_day_finish
                this.shiftForm.isRepeat = this.selectedShift.is_repeat
                this.shiftForm.startDate = this.selectedShift.start_date
                this.shiftForm.endDate = this.selectedShift.end_date
                this.shiftForm.recurrence = this.selectedShift.recurrence
                this.shiftForm.occursOn = JSON.parse(this.selectedShift.occurs_on)
                this.shiftForm.assignedTo = this.employees.find(e => e.user_id == this.selectedShift.user_id)
                this.openAddShiftForm = true
            },

            async removeEmployee(employee) {
                await rosterApi.removeRosterEmployee(employee.location_id, employee.user_id)
                this.$toast.success("Employee removed from the roster.")
                this.selectedEmployee = null
                await this.loadRosterEmployees(this.filters.location?.id)
                await this.loadRosters()
            },

            async loadRosters() {
                let params = {
                    location_id: this.filters.location?.id,
                    shift_type: this.filters.shiftType?.id,
                    role_id: this.filters.roleType?.id,
                    user_id: this.filters.staff?.id,
                    start_date: this.filters.dateRangeStart,
                    end_date: this.filters.dateRangeEnd
                }

                this.rosters = await rosterApi.fetchRosters(params)
            },

            async loadLocations() {
                this.locations = await locationApi.fetchLocations()
            },

            async loadRoleTypes() {
                this.roleTypes = await roleApi.fetchAllRoles()
            },

            async loadShiftTypes() {
                this.shiftTypes = await rosterApi.fetchShiftTypes()
            },

            async loadRosterEmployees(locationId) {
                this.employees = await employeeApi.fetchAllEmployees({
                    location_id: locationId
                })
            },
        },

        watch: {
            filters: {
                handler() {
                    if (this.filters.location) {
                        this.loadRosterEmployees(this.filters.location?.id)
                        this.loadTemplates(this.filters.location?.id)

                    } else {
                        this.employees = []
                        this.rosters = []
                    }

                    this.loadRosters()
                },
                deep: true
            },

            openAddShiftForm() {
                if (!this.openAddShiftForm) {
                    this.resetShiftForm()
                }
            },
        }
    }
</script>
