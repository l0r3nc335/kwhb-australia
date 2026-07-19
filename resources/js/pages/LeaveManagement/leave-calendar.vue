


<template>
  <div class="page-content-container">
    <section>
      <VCard
        title=""
        class="mb-6"
      >
        <VCardText class="d-flex flex-wrap py-6 gap-6">
          <div
            class="me-12 d-flex justify-space-evenly gap-2"
            width="200"
          >
            <AppTextField
              v-model="filter.search_name"
              class="filter-field"
              density="compact"
              prepend-inner-icon="tabler-search"
              placeholder="Search by Name"
              persistent-placeholder
              @input="filter_change"
            />
            <AppSelect
              v-model="filter.division"
              class="filter-field"
              density="compact"
              placeholder="Division"
              item-title="name"
              item-value="id"
              :items="['all', ...divisionStore.data.divisions]"
              @update:modelValue="filter_change"
            />
            <AppSelect
              v-model="filter.location"
              class="filter-field"
              density="compact"
              placeholder="Location"
              item-title="name"
              item-value="id"
              :items="['all', ...locationStore.data.locations]"
              @update:modelValue="filter_change"
            />
            <AppSelect
              v-model="filter.status"
              class="filter-field"
              density="compact"
              placeholder="Status"
              :items="['all','Pending','Declined','Approved', 'Cancelled']"
              @update:modelValue="filter_change"
            />
            <!-- <AppSelect
              v-model="filter.timeFrame "
              class="filter-field"
              density="compact"
              placeholder="Time Frame "
              :items="[ 'Monthly', 'Weekly', 'Fortnightly' ]"
            /> -->
            <!--
              <IconBtn>
              <VIcon icon="tabler-arrow-left" />
              </IconBtn>
              <IconBtn>
              <VIcon icon="tabler-arrow-right" />
              </IconBtn>
            -->

            <!-- <VBtn
              :rounded="true"
              icon="tabler-arrow-left"
              variant="outlined"
              color="secondary"
            />

            <VBtn
              rounded
              icon="tabler-arrow-right"
              variant="outlined"
              color="secondary"
            />
            <p>{{ filter.date_label }}</p> -->
          </div>
          <VSpacer />
        </VCardText>


        <VDivider />

        <div style="padding: 20px;">
          <div id="calendar" />
          <div style="width: 100%; padding: 20px; padding-left: 31%; background-color: #ebecee; font-size: small;">
            <div style="display: flex;">
              <ul style="line-height: 120%;">
                <li style="color: #e35205;">
                  <span style="color: #212121;">Annual Leave</span>
                </li>
                <li style="color: #9b5de5;">
                  <span style="color: #212121;">Personal Leave</span>
                </li>
                <li style="color: #f15bb5;">
                  <span style="color: #212121;">Leave Without Pay (LWOP)</span>
                </li>
                <li style="color: #f1b434;">
                  <span style="color: #212121;">Compassionate Leave</span>
                </li>
              </ul>
              <ul style=" margin-left: 5%;line-height: 120%;">
                <li style="color: #5c7fff;">
                  <span style="color: #212121;">Special/Sorry Leave</span>
                </li>
                <li style="color: #808494;">
                  <span style="color: #212121;">FOIL</span>
                </li>
                <li style="color: #e63946;">
                  <span style="color: #212121;">Parental Leave</span>
                </li>
                <li style="color: #61c378;">
                  <span style="color: #212121;">Jury Service Leave</span>
                </li>
              </ul>
              <ul style=" margin-left: 5%;line-height: 120%;">
                <li style="color: #4ca6c3;">
                  <span style="color: #212121;">Defense Reserve Leave</span>
                </li>
                <li style="color: #808cb8;">
                  <span style="color: #212121;">Long Leave</span>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <!-- <Calendar id="calendar"></Calendar> -->

        <!-- //EmployeeDetails-index -->
        <!-- <VDataTableServer
          :headers="tableHeaders"
          :items="items"
          height="300"
        >
          <template
            v-for="header in tableHeaders"
            :key="header.key"
            #[`item.${header.key}`]="{ item }"
          >
            <p v-if="header.key == 'full_name'">
              {{ item.employee.first_name }} {{ item.employee ? item.employee.last_name : '' }}
            </p>
            <span
              v-else
              :id="'headspan' + header.key +item.id"
              :class="{ 'highlight': header.date == item.date_from }"
            >
              aaaa
            </span>

              <span v-else :class="{ 'highlight': header.date == item.date_from }">

              </span>

          </template>
        </VDataTableServer> -->
      </VCard>
    </section>

    <DialogLeaveDetails
      v-model:isDialogVisible="leaveDetailsVisible"
      v-model:leaveDetail="item_selected"
    />
  </div>
</template>


<script setup>
import { useEmployeeStore } from "@/store/employeeStore"
import { useRouter } from 'vue-router'
//import { VDataTableServer } from 'vuetify/labs/VDataTable'

import { useDivisionStore } from "@/store/divisionStore"
import { useEmploymentStore } from "@/store/employmentStore"
import { useLeaveBalanceStore } from "@/store/leaveBalanceStore"
import { useLeaveStore } from "@/store/leaveStore"
import { useLocationStore } from "@/store/locationStore"
import { debounce } from 'lodash'

import { Calendar } from '@fullcalendar/core'
import resourceTimelinePlugin from '@fullcalendar/resource-timeline'

const router = useRouter()
const leaveBalanceStore = useLeaveBalanceStore()
const employeeStore = useEmployeeStore()
const leaveStore = useLeaveStore()
const divisionStore = useDivisionStore()
const locationStore = useLocationStore()
const employmentStore = useEmploymentStore()
let items = ref([])
let item_selected = ref({})
let timeFrame = ref({})
const leaveDetailsVisible = ref(false)
const startDate = new Date()
const endDate = new Date()
let calendar = ref(null)
var resources
onMounted( async() => {
  items.value = await leaveStore.fetchLeaveByApprover()
  fullCalendar()

  /* resources = items.value.map(resource => ({
    id: resource.id,
    title: resource.employee.first_name + " " + resource.employee.last_name,
    eventColor: resource.leave_type.name.includes('Annual') ? '#e3520580' :
      resource.leave_type.name.includes('Personal') ? '#9b5de580' :
        resource.leave_type.name.includes('Leave Without Pay') ? '#f15bb580' :
          resource.leave_type.name.includes('Compassionate') ? '#f1b43480' :
            resource.leave_type.name.includes('Special/Sorry') ? '#5c7fff80' :
              resource.leave_type.name.includes('FOIL') ? '#80849480' :
                resource.leave_type.name.includes('Parental') ? '#e6394680' :
                  resource.leave_type.name.includes('Jury Service') ? '#61c37880' :
                    resource.leave_type.name.includes('Defense Reserve') ? '#4ca6c380' :
                      resource.leave_type.name.includes('Long Service') ? '#808cb880' : '#00000080',
  }))
  var events = items.value.map(events => ({
    resourceId: events.id,
    title: events.leave_type.name,
    start: events.date_from,
    end: events.date_to,
  }))


  let calendarEl = document.getElementById('calendar')
  calendar.value = new Calendar(calendarEl, {
    schedulerLicenseKey: 'CC-Attribution-NonCommercial-NoDerivatives',
    plugins: [resourceTimelinePlugin],
    height: 600,
    initialView: 'resourceTimelineWeek',
    slotDuration: { days: 1 },
    views: {
      resourceTimelineFortnight: {
        type: 'resourceTimelineWeek',
        duration: { weeks: 2 }, // Display 2 weeks in a row
        buttonText: 'fortnight', // Text for the button to switch to this view
      },
    },
    slotLabelFormat: [
      { day: 'numeric', weekday: 'short' }, // top level of text
      // {  }, // lower level of text
    ],

    // headerToolbar: false,
    headerToolbar: {
      left: 'prev,next',
      center: 'title',
      right: 'resourceTimelineWeek,resourceTimelineFortnight,resourceTimelineMonth',
    },
    resourceAreaHeaderContent: '',
    resources: resources,
    events: events,
    columnHeader: true,
    weekday: 'long',
  })
  calendar.value.render() */

  //fetchLeaveByApprover
  //await divisionStore.setDivisions();
  //await locationStore.setLocations();
  //await employmentStore.setEmployments();

  //items.value = employeeStore.data.employees;
})

const headers1 = [
  {
    title: 'ID',
    key: 'id',
  },
  {
    title: 'Full Name',
    key: 'full_name',
  },
  {
    title: 'Job Title',
    key: 'employee.job_title',
  },
  {
    title: 'Date Filed',
    key: 'date_filed',
  },
  {
    title: 'Date From',
    key: 'date_from',
  },
  {
    title: 'Date To',
    key: 'date_to',
  },
  {
    title: 'Leave Type',
    key: 'leave_type.name',
  },
  {
    title: 'total_hours',
    key: 'total_hours',
  },
  {
    title: 'Action',
    key: 'action',
  },
]

const months = [
  {
    zoomTo: 100, // we want to display this format for all zoom levels until 100
    period: "month",
    format({ timeStart }) {
      return timeStart.format("MMM") // full list of formats: https://day.js.org/docs/en/display/format
    },
  },
]

const days = [
  {
    zoomTo: 100, // we want to display this format for all zoom levels until 100
    period: "day",
    main: true, // we want grid to be divided by this period = day
    format({ timeStart }) {
      return timeStart.format("DD") // full list of formats: https://day.js.org/docs/en/display/format
    },
  },
]

const config = {
  licenseKey: "",
  height: 300,

  list: {
    toggle: {
      display: false, // toggle icon for show hide project names
    },
    rows: {
      1: {
        id: "1",
        style: { background: "red" },
        label: "Intranet",
        height: 30,
      },
      2: {
        id: "2",
        label: " Dashboard",
        height: 30,
      },
      3: {
        id: "3",
        label: "Purchase Verification",
        height: 30,
      },
      4: {
        id: "4",
        label: "App Migration",
        height: 30,
      },
      5: {
        id: "5",
        label: "Central Management",
        height: 30,
      },
    },
    columns: {
      data: {
        id: {
          id: "id",
          data: "id",
          width: 50,

          header: {
            content: "ID",
          },
        },
        label: {
          id: "label",
          data: "label",
          width: 200,
          header: {
            content: "Projects",
          },
        },
      },
    },
  },
  chart: {
    calendarLevels: [
      [
        {
          zoomTo: 100, // we want to display this format for all zoom levels until 100
          period: "month",
          periodIncrement: 1,
          format({ timeStart }) {
            return timeStart.format("MMMM") // full list of formats: https://day.js.org/docs/en/display/format
          },
        },
      ],
    ],
    items: {
      1: {
        id: "1",
        rowId: "1",
        style: { background: "purple" },
        label: "Intranet",
        time: {
          start: new Date("01-02-2020").getTime(),
          end: new Date("06-10-2020").getTime(),
        },
      },
      2: {
        id: "2",
        rowId: "2",
        style: { background: "blue" },
        label: "Item 2",
        time: {
          start: new Date("01-05-2020").getTime(),
          end: new Date("01-15-2020").getTime(),
        },
      },
      3: {
        id: "3",
        rowId: "3",
        style: { background: "orange" },
        label: "Item 3",
        time: {
          start: new Date("01-10-2020").getTime(),
          end: new Date("01-20-2020").getTime(),
        },
      },
      4: {
        id: "4",
        rowId: "4",
        style: { background: "green" },
        label: "Item 4",
        time: {
          start: new Date("01-13-2020").getTime(),
          end: new Date("01-18-2020").getTime(),
        },
      },
      5: {
        id: "5",
        rowId: "5",
        style: { background: "red" },
        label: "Item 5",
        time: {
          start: new Date("01-15-2020").getTime(),
          end: new Date("01-20-2020").getTime(),
        },
      },
    },
  },
}

const filter = ref({
  search_name: '',
  division: 'all',
  location: 'all',
  status: 'all',
  timeFrame: 'Weekly',
  date_label: "May 2024",

})

const filter_change = debounce(async() => {
  const payload = {
    filter: {
      search_name: filter.value.search_name ? true : false,
      leave_type: false,
      division: true,
      location: true,
      status: true,
      filter_date: filter.value.filter_date ? true : false,
    },
    data: {
      search_name: filter.value.search_name,
      division_id: filter.value.division,
      location_id: filter.value.location,
      status: filter.value.status,
      filter_date: {
        from: filter.value.filter_date ? filter.value.filter_date[0] : null,
        to: filter.value.filter_date ? filter.value.filter_date[1] : null,
      },
    },
  }

  items.value = await leaveStore.multipleFilter(payload)
  fullCalendar()
  //refreshCalendarData()
}, 800)

const refreshCalendarData = () => {


  if (calendar.value) {

    var resources = items.value.map(resource => ({
      id: resource.id,
      title: resource.employee.first_name + " " + resource.employee.last_name,
    }))

    // Map the updated items to create a new array of events
    var newEvents = items.value.map(event => ({
      resourceId: event.id,
      title: event.leave_type.name,
      start: event.date_from,
      end: event.date_to,
    }))

    // Add the new events to the calendar
    calendar.value.removeAllEvents() // Remove all existing events
    calendar.value.addEventSource(newEvents)

    // Set the updated resources in the calendar
    calendar.value.setOption('resources', resources)
  }
  else
  {
    console.log("calendar not initialized")
  }
}

const fullCalendar = () => {

  // eslint-disable-next-line sonarjs/cognitive-complexity
  resources = items.value.map(resource => ({
    id: resource.id,
    title: resource.employee.first_name + " " + resource.employee.last_name,
    eventColor: resource.leave_type.name.includes('Annual') ? '#e3520580' :
      resource.leave_type.name.includes('Personal') ? '#9b5de580' :
        resource.leave_type.name.includes('Leave Without Pay') ? '#f15bb580' :
          resource.leave_type.name.includes('Compassionate') ? '#f1b43480' :
            resource.leave_type.name.includes('Special/Sorry') ? '#5c7fff80' :
              resource.leave_type.name.includes('FOIL') ? '#80849480' :
                resource.leave_type.name.includes('Parental') ? '#e6394680' :
                  resource.leave_type.name.includes('Jury Service') ? '#61c37880' :
                    resource.leave_type.name.includes('Defense Reserve') ? '#4ca6c380' :
                      resource.leave_type.name.includes('Long Service') ? '#808cb880' : '#00000080',
  }))
  var events = items.value.map(events => ({
    resourceId: events.id,
    title: events.leave_type.name,
    start: events.date_from,
    end: events.date_to,
  }))

  let calendarEl = document.getElementById('calendar')
  calendar.value = new Calendar(calendarEl, {
    schedulerLicenseKey: 'CC-Attribution-NonCommercial-NoDerivatives',
    plugins: [resourceTimelinePlugin],
    height: 600,
    initialView: 'resourceTimelineWeek',
    slotDuration: { days: 1 },
    views: {
      resourceTimelineFortnight: {
        type: 'resourceTimelineWeek',
        duration: { weeks: 2 }, // Display 2 weeks in a row
        buttonText: 'fortnight', // Text for the button to switch to this view
      },
    },
    slotLabelFormat: [
      { day: 'numeric', weekday: 'short' }, // top level of text
      // {  }, // lower level of text
    ],

    // headerToolbar: false,
    headerToolbar: {
      left: 'prev,next',
      center: 'title',
      right: 'resourceTimelineWeek,resourceTimelineFortnight,resourceTimelineMonth',
    },
    resourceAreaHeaderContent: '',
    resources: resources,
    events: events,
    columnHeader: true,
    weekday: 'long',
  })
  calendar.value.render()
}


const formatDate = date => {
  return `${date.toLocaleString('default', { month: 'long' })} ${date.getDate()}, ${date.getFullYear()}`
}

const getStartDateOfWeek = () => {
  const today = new Date()
  const day = today.getDay()
  const diff = today.getDate() - day + (day === 0 ? -6 : 1) // Adjust to get the start of the week

  return new Date(today.setDate(diff))
}

const tableHeaders = computed(() => {
  const headers = [{ title: 'Full Name', key: 'full_name' }]

  if (filter.value.timeFrame === 'Weekly') {
    const startOfWeek = getStartDateOfWeek()
    for (let i = 0; i < 7; i++) {
      const date = new Date(startOfWeek)

      date.setDate(date.getDate() + i)
      headers.push({
        title: formatDate(date),
        key: `date${i}`,
        date: date.toISOString().split('T')[0],
      })
    }
  } else if (filter.value.timeFrame === 'Monthly') {
    const daysInMonth = new Date(startDate.getFullYear(), startDate.getMonth() + 1, 0).getDate()
    for (let i = 1; i <= daysInMonth; i++) {
      const date = new Date(startDate.getFullYear(), startDate.getMonth(), i)

      headers.push({
        title: formatDate(date),
        ket: `date${i}`,
        date: date.toISOString().split('T')[0],
      })
    }
  }

  return headers

})
</script>

<style lang="scss" scoped>
.filter-field {
  max-inline-size: 170px;
  min-inline-size: 170px;
}

.highlight {
  display: block;
  background-color: yellow; /* Set the highlight color */
  block-size: 100% !important;
  inline-size: 100% !important;
}

td {
  border: 1px;
}

.v-table > .v-table__wrapper > table > tbody > tr > td {
  padding: 0 !important;
}

#calendar .fc-datagrid-cell-main {
  color: red !important;
}
</style>


