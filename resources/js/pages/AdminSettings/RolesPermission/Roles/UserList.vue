<script setup>
import avatar2 from '@images/avatars/avatar-2.png';
import avatar4 from '@images/avatars/avatar-4.png';
import avatar8 from '@images/avatars/avatar-8.png';
import { VDataTableServer } from 'vuetify/labs/VDataTable';

const searchQuery = ref('')
const selectedRole = ref()
const selectedStatus = ref()

const sortDesc = ref(false)

const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()

const isSorted = column => {
  return sortBy === column.key
}

const updateOptions = options => {
  page.value = options.page
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

const headers = [
  {
    title: 'User',
    align: 'center',
    sortable: false,
    key: 'user',
  },
  {
    title: 'First Name',
    key: 'first_name',
  },
  {
    title: 'Last Name',
    key: 'last_name',
  },
  {
    title: 'Location',
    key: 'location',
  },
  {
    title: 'Division',
    key: 'division',
  },
  {
    title: 'Role',
    align: 'left',
    key: 'role',
  },
  {
    title: 'Status',
    key: 'status',
  },
  {
    title: '',
    key: 'actions',
    align: 'center',
    sortable: false,
  },
]


const users = [
    {
        id:1,
        first_name:"John",
        last_name:"Doe",
        location:"Katherine",
        division:"Corporate",
        email:"jd_xx1@example.com",
        role:"CEO",
        status:"Active",
    },
    {
        id:2,
        first_name:"Taylor",
        last_name:"Swift",
        avatar: avatar2,
        location:"Darwin",
        division:"Corporate",
        email:"tlr_sw@example.com",
        role:"general manager",
        status:"Active",
    },
    {
        id:2,
        first_name:"Katy",
        last_name:"Perry",
        avatar: avatar4,
        location:"Darwin",
        division:"Corporate",
        email:"ktyp@example.com",
        role:"line manager",
        status:"Active",
    },
    {
        id:2,
        first_name:"Ariana",
        last_name:"Grande",
        avatar: avatar8,
        location:"Darwin",
        division:"Clinical",
        email:"a_grm@example.com",
        role:"HR",
        status:"Active",
    }
]
const totalUsers = 4

const roles = [
  {
    title: 'CEO',
    value: 'CEO',
  },
  {
    title: 'HR',
    value: 'HR',
  },
  {
    title: 'General Manager',
    value: 'general manager',
  },
  {
    title: 'Line Manager',
    value: 'line manager',
  },
]

const resolveUserRoleVariant = role => {
  const roleLowerCase = role.toLowerCase()
  if (roleLowerCase === 'ceo')
    return {
      color: 'success',
      icon: 'tabler-device-laptop',
    }
  else if (roleLowerCase === 'line manager')
    return {
      color: 'error',
      icon: 'tabler-settings',
    }
  else if (roleLowerCase === 'general manager')
    return {
        color: '#CC4A04',
        icon: 'tabler-settings',
    }
  else {
    return {
        color: 'info',
        icon: 'tabler-user',
    }
  }
}

const generateRangeText = (pageNumber, totalNumberOfRows, rowsPerPage) => {
  const startIndex = (pageNumber - 1) * rowsPerPage + 1
  let endIndex = pageNumber * rowsPerPage
  if (endIndex > totalNumberOfRows) {
    endIndex = totalNumberOfRows
  }

  const rowsOnLastPage = totalNumberOfRows % rowsPerPage || rowsPerPage

  let rangeText
  if (totalNumberOfRows === 0) {
    rangeText = "No entries"
  } else if (pageNumber === 1 && endIndex === totalNumberOfRows) {
    rangeText = `Showing all of ${totalNumberOfRows} entries`
  } else if (pageNumber === 1) {
    rangeText = `Showing ${startIndex} to ${endIndex} of ${totalNumberOfRows} entries`
  } else if (endIndex === totalNumberOfRows) {
    if (rowsOnLastPage === 1) {
      rangeText = `Showing last entry of ${totalNumberOfRows} entries`
    } else {
      rangeText = `Showing ${startIndex} to ${endIndex} of ${totalNumberOfRows} entries`
    }
  } else {
    rangeText = `Showing ${startIndex} to ${endIndex} of ${totalNumberOfRows} entries`
  }

  return rangeText
}

const getBackgroundColor = (item, value) => {
  if(item.role && value == 'user') {
    switch (item.role) {
    case 'Administrator':
      return '#f9dccd'
    case 'Applicant':
      return '#fcdef0'
    case 'Board':
      return '#ccd1e3'
    case 'CEO':
      return '#fcf0d6'
    case 'Finance':
      return '#fad7da'
    case 'General Manager':
      return '#ebdffa'
    case 'HR':
      return '#9b5de5'
    case 'Line Manager':
      return '#b9f3c7'
    case 'User':
      return '#70f0e7'
    default:
      return '#cccccc'
    }
  } else if (item.status && value == 'status') {
    switch (item.status) {
    case 'Active':
      return '#e6f5e9'
    case 'Offswing':
      return '#fae3d7'
    case 'Pending':
      return '#e5eaff'
    case 'Extended Leave':
    case 'Extended leave':
      return '#FDF3DE'
    case 'Terminated':
      return '#FBDFE1'
    default:
      return '#cccccc'
    }
  }

  return '#cccccc'
}

const getTextColor = (item, value) => {
  if(item.role && value == 'user') {
    switch (item.role) {
    case 'Administrator':
      return '#e35205'
    case 'Applicant':
      return '#f15bb5'
    case 'Board':
      return '#001871'
    case 'CEO':
      return '#f1b434'
    case 'Finance':
      return '#e63946'
    case 'General Manager':
      return '#9b5de5'
    case 'HR':
      return '#f1b434'
    case 'Line Manager':
      return '#074625'
    case 'User':
      return '#0a7069'
    case 'Active':
      return '#62c379'
    default:
      return '#363636'
    }
  }else if (item.status && value == 'status') {
      switch (item.status) {
      case 'Active':
        return '#61c378'
      case 'Offswing':
        return '#e35205'
      case 'Pending':
        return '#5c7fff'
      case 'Extended Leave':
      case 'Extended leave':
        return '#f1b434'
      case 'Terminated':
        return '#E7404C'
      default:
        return '#363636'
      }
    }

    return '#363636'
}
</script>

<template>
  <section>
    <p class="font-weight-bold text-h2 mb-3">Users</p>
    <VCard>
      <VCardText class="d-flex flex-wrap gap-4">
        <AppSelect
          :model-value="itemsPerPage"
          :items="[
            { value: 10, title: '10' },
            { value: 25, title: '25' },
            { value: 50, title: '50' },
            { value: 100, title: '100' },
            { value: 500, title: 'All' },
          ]"
          style="inline-size: 5rem;"
          @update:model-value="itemsPerPage = parseInt($event, 10)"
        />

        <VSpacer />

        <div class="d-flex align-center flex-wrap gap-4">
          <AppTextField
            v-model="searchQuery"
            placeholder="Search User"
            density="compact"
            style="inline-size: 12.5rem;"
          />

          <AppSelect
            v-model="selectedRole"
            placeholder="Select Role"
            :items="roles"
            density="compact"
            clearable
            clear-icon="tabler-x"
            style="inline-size: 10rem;"
          />
        </div>
      </VCardText>

      <VDivider />

      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        :items-per-page-options="[
          { value: 10, title: '10' },
          { value: 20, title: '20' },
          { value: 50, title: '50' },
          { value: 500, title: '$vuetify.dataFooter.itemsPerPageAll' },
        ]"
        :items="users"
        :items-length="totalUsers"
        :headers="headers"
        class="text-no-wrap"
        @update:options="updateOptions"
      >
        <template #headers="{ headers }">
            <tr>
              <template
                v-for="column in headers"
                :key="column.id"
              >
                <th
                  v-for="item in column"
                  :key="item.title"
                  :class="`custom-header align-${item.align}`"
                >
                  <span
                    v-if="item.sortable"
                    style="display: inline-flex; align-items: center;"
                    @click=""
                  >
                    <span style="white-space: nowrap;">{{ item.title }}</span>
                    <span v-if="isSorted(item)">
                      <VIcon
                        v-if="sortDesc"
                        style="font-size: medium;"
                      >
                        tabler-chevron-down
                      </VIcon>
                      <VIcon
                        v-else
                        style="font-size: medium;"
                      >
                        tabler-chevron-up
                      </VIcon>
                    </span>

                    <VIcon
                      v-else
                      style="font-size: medium;"
                    >
                      tabler-selector
                    </VIcon>
                  </span>
                  <span v-else>
                    {{ item.title }}
                  </span>
                </th>
            </template>
          </tr>
        </template>
        <template #item.user="{ item }">
          <div v-if="item.avatar">
            <VAvatar
              size="38"
              :variant="!item.avatar ? 'tonal' : undefined"
              :color="!item.avatar ? resolveUserRoleVariant(item.role).color : undefined"
            >
              <VImg
                :src="item.avatar"
              />
            </VAvatar>
          </div>
          <div v-else>
            <VAvatar
              size="38"
              :variant="!item.avatar ? 'tonal' : undefined"
              :style="{
                background: getBackgroundColor(item, 'user'),
                color: getTextColor(item, 'user'),
                fontWeight: 800
              }"
            >
              {{ item.first_name.charAt(0).toUpperCase() + item.last_name.charAt(0).toUpperCase() }}
            </VAvatar>
          </div>
        </template>

        <template #item.role="{ item }">
          <div class="d-flex align-center gap-4">
            <VAvatar
              size="30"
              variant="tonal"
              :color="resolveUserRoleVariant(item.role).color"
            >
              <VIcon
                size="20"
                :icon="resolveUserRoleVariant(item.role).icon"
              />
            </VAvatar>
            <span class="text-capitalize font-weight-medium">{{ item.role }}</span>
          </div>
        </template>

        <template #item.first_name="{ item }">
          <span class="font-weight-medium">{{ item.first_name }}</span>
        </template>

        <template #item.last_name="{ item }">
          <span class="font-weight-medium">{{ item.last_name }}</span>
        </template>

        <template #item.location="{ item }">
          <span class="font-weight-medium">{{ item.location }}</span>
        </template>

        <template #item.division="{ item }">
          <span class="font-weight-medium">{{ item.division }}</span>
        </template>

        <template #item.status="{ item }">
          <VChip
            label
            size="small"
            class="text-capitalize"
            :style="{
                background: getBackgroundColor(item, 'status'),
                color: getTextColor(item, 'status'),
              }"
          >
            {{ item.status }}
          </VChip>
        </template>

        <template #bottom>
            <VDivider />
            <div class="d-flex align-center justify-sm-space-between justify-center flex-wrap gap-3 pa-5 pt-3">
              <p
                class="text-sm text-disabled mb-0"
                style="color: #999 !important;"
              >
                {{ generateRangeText(page, totalUsers, itemsPerPage) }}
              </p>

              <VPagination
                v-if="totalUsers > itemsPerPage"
                v-model="page"
                :total-visible="6"
                :length="Math.ceil(totalUsers / itemsPerPage)"
              >
                <template #prev="slotProps">
                  <VBtn
                    variant="tonal"
                    color="default"
                    v-bind="slotProps"
                    :icon="false"
                    class
                  >
                    <VIcon icon="tabler-chevron-left" />
                  </VBtn>
                </template>

                <template #next="slotProps">
                  <VBtn
                    variant="tonal"
                    color="default"
                    v-bind="slotProps"
                    :icon="false"
                  >
                  <VIcon icon="tabler-chevron-right" />
                  </VBtn>
                </template>
              </VPagination>
            </div>
        </template>

        <template #item.actions="{ item }">
          <!-- <IconBtn>
            <VIcon icon="tabler-edit" />
          </IconBtn>
          <IconBtn @click="deleteUser(item.id)">
            <VIcon icon="tabler-trash" />
          </IconBtn> -->

          <VBtn
            icon
            color="medium-emphasis"
            density="comfortable"
            variant="text"
          >
            <VIcon
              size="24"
              icon="tabler-dots-vertical"
            />

            <!-- <VMenu activator="parent">
              <VList>
                <VListItem>
                  <VListItemTitle>View</VListItemTitle>
                </VListItem>
                <VListItem link>
                  <VListItemTitle>Suspend</VListItemTitle>
                </VListItem>
              </VList>
            </VMenu> -->
          </VBtn>
        </template>
      </VDataTableServer>
    </VCard>
  </section>
</template>

<style scoped>
.text-capitalize {
  text-transform: capitalize;
}

.user-list-name:not(:hover) {
  color: rgba(var(--v-theme-on-background), var(--v-medium-emphasis-opacity));
}
.custom-header {
  color: #333954 !important;
  font-family: Oswald !important;
  font-size:large;
  font-weight: 400 !important;
}
.align-left {
  text-align: left !important;
}

.align-center {
  text-align: center !important;
}

.align-right {
  text-align: right !important;
}

:deep(.text-primary) {
  color: #001871 !important;
}
</style>
