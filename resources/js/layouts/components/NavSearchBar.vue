<script setup>
import routeItems from '@/navigation/vertical/index'
import { useEmployeeStore } from "@/store/employeeStore"
import { useThemeConfig } from '@core/composable/useThemeConfig'
import Shepherd from 'shepherd.js'
import { useRouter } from 'vue-router'

const { appContentLayoutNav } = useThemeConfig()
const employeeStore = useEmployeeStore()

defineOptions({ inheritAttrs: false })

// 👉 Is App Search Bar Visible
const isAppSearchBarVisible = ref(false)
const employees = ref([])

let suggestionGroups = []

// 👉 Default suggestions
/* const suggestionGroups = [
  {
    title: 'Popular Searches',
    content: [
      {
        icon: 'tabler-chart-donut',
        title: 'Analytics',
        url: { name: 'dashboards-analytics' },
      },
      {
        icon: 'tabler-chart-bubble',
        title: 'CRM',
        url: { name: 'dashboards-crm' },
      },
      {
        icon: 'tabler-file',
        title: 'Invoice List',
        url: { name: 'apps-invoice-list' },
      },
      {
        icon: 'tabler-users',
        title: 'User List',
        url: { name: 'apps-user-list' },
      },
    ],
  },
  {
    title: 'Apps & Pages',
    content: [
      {
        icon: 'tabler-calendar',
        title: 'Calendar',
        url: { name: 'apps-calendar' },
      },
      {
        icon: 'tabler-file-plus',
        title: 'Invoice Add',
        url: { name: 'apps-invoice-add' },
      },
      {
        icon: 'tabler-currency-dollar',
        title: 'Pricing',
        url: { name: 'pages-pricing' },
      },
      {
        icon: 'tabler-user',
        title: 'Account Settings',
        url: {
          name: 'pages-account-settings-tab',
          params: { tab: 'account' },
        },
      },
    ],
  },
  {
    title: 'User Interface',
    content: [
      {
        icon: 'tabler-letter-a',
        title: 'Typography',
        url: { name: 'pages-typography' },
      },
      {
        icon: 'tabler-square',
        title: 'Tabs',
        url: { name: 'components-tabs' },
      },
      {
        icon: 'tabler-hand-click',
        title: 'Buttons',
        url: { name: 'components-button' },
      },
      {
        icon: 'tabler-keyboard',
        title: 'Statistics',
        url: { name: 'pages-cards-card-statistics' },
      },
    ],
  },
  {
    title: 'Popular Searches',
    content: [
      {
        icon: 'tabler-list',
        title: 'Select',
        url: { name: 'forms-select' },
      },
      {
        icon: 'tabler-space',
        title: 'Combobox',
        url: { name: 'forms-combobox' },
      },
      {
        icon: 'tabler-calendar',
        title: 'Date & Time Picker',
        url: { name: 'forms-date-time-picker' },
      },
      {
        icon: 'tabler-hexagon',
        title: 'Rating',
        url: { name: 'forms-rating' },
      },
    ],
  },
]

// 👉 No Data suggestion
const noDataSuggestions = [
  {
    title: 'Analytics Dashboard',
    icon: 'tabler-shopping-cart',
    url: { name: 'dashboards-analytics' },
  },
  {
    title: 'Account Settings',
    icon: 'tabler-user',
    url: {
      name: 'pages-account-settings-tab',
      params: { tab: 'account' },
    },
  },
  {
    title: 'Pricing Page',
    icon: 'tabler-cash',
    url: { name: 'pages-pricing' },
  },
] */

const searchQuery = ref('')
const searchResult = ref([])
const router = useRouter()
const currentRoute = useRoute()
const employeeResultCount = ref(0)
const pageResultCount = ref(0)

const handlerAppBarSearch = async searchQuery => {
  const queryLowered = (searchQuery ?? '').toString().toLowerCase()
  const filteredSearchData = []

  // routeItems.forEach(item => {
  //   // Check if the parent item matches the search query
  //   if (item.title.toLowerCase().includes(queryLowered)) {
  //     filteredSearchData.push({ ...item, children: undefined }) // Remove children property if present
  //   }
  //
  //   // Check if any of the children match the search query
  //   if (item.children) {
  //     const matchingChildren = item.children.filter(child => child.title.toLowerCase().includes(queryLowered))
  //
  //     matchingChildren.forEach(child => {
  //       filteredSearchData.push({ ...child, group: item.title }) // Add parent title as group if necessary
  //     })
  //   }
  // })

  const memberResults = await searchMember(queryLowered)

    if (memberResults.length > 0) {
        employeeResultCount.value = memberResults.length
        memberResults.forEach(item => {
            filteredSearchData.push({ ...item })
        })
    } else {
        employeeResultCount.value = 0
    }

    const pageResults = searchPage(queryLowered)

    if (pageResults.length > 0) {
        pageResultCount.value = pageResults.length
        pageResults.forEach(item => {
            filteredSearchData.push({ ...item })
        })
    } else {
        pageResultCount.value = 0
    }



  /* memberResults.then(function(result){
    console.log('hey', result)
    result.forEach(item => {
      filteredSearchData.push({ ...item })
    })
  }) */


  return filteredSearchData
}

const searchPage = searchQuery => {
    let filteredPages = []

    let availablePages = [
        {
            title: 'Dashboard',
            to: { path: '/'},
            category: 'Pages'
        },
        {
            title: 'Employee',
            to: { path: '/employee'},
            category: 'Pages'
        },
        {
            title: 'Leaves',
            to: { path: '/leaves'},
            category: 'Pages'
        },
        {
            title: 'Timesheets',
            to: { path: '/timesheets'},
            category: 'Pages'
        },
        {
            title: 'Vaccinations',
            to: { path: '/vaccinations'},
            category: 'Pages'
        },
        {
            title: 'Compliance',
            to: { path: '/compliance'},
            category: 'Pages'
        },
    ]

    if (searchQuery?.length > 0) {
        filteredPages = availablePages.filter(p => {
            return p.title.toLowerCase().includes(searchQuery)
        })
    }

    return filteredPages
}

const searchMember = async searchQuery => {
  const memberResults = []
  if(searchQuery.length > 0) {
    employees.value = await employeeStore.fetch_employee_by_name({ name: searchQuery })

    employees.value.forEach(member => {
      memberResults.push({
        title: member.first_name + " " + member.last_name,
        to: { path: '/employee/employeedetails', params: { tab: 'EmployeeInfo' } },
        category: 'Employees',
        item: member,
      })
    })
  }

  return memberResults
}

// 👉 fetch search result API
watchEffect(async () => {
  /*  axios.get('/app-bar/search', { params: { q: searchQuery.value } }).then(response => {
    searchResult.value = response.data
  }) */

  // const { data } = await useApi(withQuery('/app-bar/search', { q: searchQuery.value }))

  searchResult.value = await handlerAppBarSearch(searchQuery.value)

    suggestionGroups = []

    if (employeeResultCount.value > 0) {
        suggestionGroups.push({
            title: 'Employees',
            content: [
                {
                    icon: 'tabler-users',
                    title: 'Employee',
                    to: { name: 'Employee' },
                },
            ],
        })
    }

    if (pageResultCount.value > 0) {
        suggestionGroups.push({
            title: 'Pages',
            content: [
                {
                    icon: 'tabler-pages',
                    title: 'Pages',
                    to: { name: 'Pages' },
                },
            ],
        })
    }
})

const redirectToSuggestedOrSearchedPage = selected => {
  if (selected.category == 'Employees') {
    employeeStore.setEmployeeSelected(selected.item)
    if (router.currentRoute.value.name == 'Employee-EmployeeDetails') {
      router.push({ path: '/temporary-route' }).then(() => {
        router.push(selected.to)
      })
    } else {
        router.push(selected.to)
    }
  } else {
      router.push(selected.to)
  }

  isAppSearchBarVisible.value = false
  searchQuery.value = ''
}

const LazyAppBarSearch = defineAsyncComponent(() => import('@core/components/AppBarSearch.vue'))
</script>

<template>
  <div
    class="d-flex align-center cursor-pointer"
    v-bind="$attrs"
    style="user-select: none;"
    @click="isAppSearchBarVisible = !isAppSearchBarVisible"
  >
    <!-- 👉 Search Trigger button -->
    <!-- close active tour while opening search bar using icon -->
    <IconBtn
      class="me-1"
      @click="Shepherd.activeTour?.cancel()"
    >
      <VIcon
        size="26"
        icon="tabler-search"
      />
    </IconBtn>

    <span
      v-if="appContentLayoutNav === 'vertical'"
      class="d-none d-md-flex align-center text-disabled"
      @click="Shepherd.activeTour?.cancel()"
    >
      <span class="me-3">Search</span>
      <!-- <span class="meta-key">&#8984;K</span> -->
    </span>
  </div>

  <!-- 👉 App Bar Search -->
  <LazyAppBarSearch
    v-model:isDialogVisible="isAppSearchBarVisible"
    v-model:search-query="searchQuery"
    :search-results="searchResult"
    :suggestions="suggestionGroups"
    :no-data-suggestion="noDataSuggestions"
    @item-selected="redirectToSuggestedOrSearchedPage"
  >
    <!--
      <template #suggestions>
      use this slot if you want to override default suggestions
      </template>
    -->

    <!--
      <template #noData>
      use this slot to change the view of no data section
      </template>
    -->

    <!--
      <template #searchResult="{ item }">
      use this slot to change the search item
      </template>
    -->
  </LazyAppBarSearch>
</template>

<style lang="scss" scoped>
@use "@styles/variables/_vuetify.scss";

.meta-key {
  border: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-radius: 6px;
  block-size: 1.5625rem;
  line-height: 1.3125rem;
  padding-block: 0.125rem;
  padding-inline: 0.25rem;
}
</style>
