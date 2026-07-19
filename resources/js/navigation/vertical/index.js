import { useAuthStore } from "@/store/authStore"

const authStore = useAuthStore()

const permissions = authStore.user?.role?.permissions || []

const isSuperUser = permissions.length === 0

let mainNavigations = []
let subNavigations = []
let uiKeys = []

let contractGroup = [1,5,7,10]

if (permissions.length > 0) {
  for (let i in permissions) {
    let uiKey = permissions[i].ui_key
    let mainNav = uiKey.split('.')[0]
    let secondNav = uiKey.split('.')[1]
    mainNavigations.push(mainNav)
    subNavigations.push(secondNav)
    uiKeys.push(uiKey)

  }
}

let activeNavigations = []

if (uiKeys.includes('dashboard.view') || isSuperUser) {
  activeNavigations.push({
    title: 'Dashboard',
    to: { name: 'index' },
    icon: { icon: 'tabler-smart-home' },
    category: 'Pages',
  })
}

if (mainNavigations.includes('people') || isSuperUser) {
  let children = []

  if (uiKeys.includes('people.employee_dashboard.view') || isSuperUser) {
    children.push( {
      title: 'Employee',
      to: 'Employee',
      group: 'People',
      category: 'Pages',
    })
  }

  if (uiKeys.includes('people.leave_management.view') || isSuperUser) {
    children.push({
      title: 'Leave Management',
      to: 'Leaves',
      group: 'People',
      category: 'Pages',
      icon: {
        icon: 'tabler-calendar-week',
        size: '18px',
      },
    })
  }

  activeNavigations.push({
    title: 'People',
    icon: { icon: 'tabler-users', color: '#ffffff' },
    category: 'Pages',
    badgeClass: 'bg-global-primary',
    children: children,
  })
}


if (mainNavigations.includes('operational') || isSuperUser) {
  let children = []

  if (uiKeys.includes('operational.timesheets.view') || isSuperUser) {
    children.push({
      title: 'Timesheets',
      to: { name: 'Timesheets' },
      group: 'Operational',
      category: 'Pages',
      icon: {
        icon: 'tabler-chart-pie-2',
        size: '18px',
      },
    })
  }

  if (uiKeys.includes('operational.rosters.view') || isSuperUser) {
    children.push({
      title: 'Roster',
      to: { name: 'Rosters' },
      group: 'Operational',
      category: 'Pages',
    })
  }

  children.push({
    title: 'Expenses',
    to: { name: 'Expenses' },
    group: 'Operational',
    category: 'Pages',
  })


  if (uiKeys.includes('operational.certifications_vaccinations.view') || isSuperUser) {
    children.push({
      title: 'Vaccinations',
      to: { name: 'Vaccinations' },
      group: 'Operational',
      category: 'Pages',
    })
  }

  if (uiKeys.includes('operational.certifications_compliance.view') || isSuperUser) {
    children.push({
      title: 'Compliance',
      to: { name: 'Compliance' },
      group: 'Operational',
      category: 'Pages',
    })
  }

  activeNavigations.push({
    title: 'Operational',
    icon: { icon: 'tabler-tool' },
    category: 'Pages',
    children: children,
  })
}

if (mainNavigations.includes('recruitment') || isSuperUser || contractGroup.includes(authStore.user?.role.id)) {
  let children = []

  if (uiKeys.includes('recruitment.contract.view') || isSuperUser || contractGroup.includes(authStore.user?.role.id)) {
    children.push({
      title: 'Contracts',
      to: { name: 'Contract' },
      icon: { icon: 'tabler-settings' },
      group: 'Recruitment',
      category: 'Pages',
    })
  }

  activeNavigations.push({
    title: 'Recruitment',
    icon: { icon: 'tabler-briefcase', color: '#ffffff' },
    category: 'Pages',
    badgeClass: 'bg-global-primary',
    children: children,
  })
}

// if (mainNavigations.includes('resources') || isSuperUser) {
  activeNavigations.push({
    title: 'Resources',
    to: { name: 'Resources' },
    icon: { icon: 'tabler-files' },
    category: 'Pages',
  })
// }

if (mainNavigations.includes('reports') || isSuperUser) {
  activeNavigations.push({
    title: 'Reporting',
    to: { name: 'Report' },
    icon: { icon: 'tabler-graph' },
    category: 'Pages',
  })
}

if (mainNavigations.includes('admin_settings') || isSuperUser) {
  activeNavigations.push({
    title: 'Admin Settings',
    to: { name: 'AdminSettings' },
    icon: { icon: 'tabler-settings' },
    category: 'Pages',
  })
}


// [
//   {
//     title: 'People',
//     icon: { icon: 'tabler-users', color: '#ffffff' },
//     category: 'Pages',
//     children: [
//       {
//         title: 'Employee',
//         to: 'Employee',
//         group: 'People',
//         category: 'Pages',
//       },

//     ],
//     badgeClass: 'bg-global-primary',
//   },
//   {
//     title: 'Operational',
//     icon: {icon: 'tabler-tool'},
//     category: 'Pages',
//     children: [
//       {
//         title: 'Timesheets',
//         to: {name: 'Timesheets'},
//         group: 'Operational',
//         category: 'Pages'
//       },
//       {
//         title: 'Certifications',
//         category: 'Pages',
//         children: [
//           {
//             title: 'Vaccinations',
//             to: {name: 'Vaccinations'},
//             group: 'Certifications',
//             category: 'Pages',
//           },
//           {
//             title: 'Compliance',
//             to: {name: 'Compliance'},
//             group: 'Certifications',
//             category: 'Pages',
//           },
//         ],
//       },
//     ]
//   },
//   {
//     title: 'Admin Settings',
//     to: { name: 'AdminSettings' },
//     icon: { icon: 'tabler-settings' },
//     category: 'Pages',
//   },
// ]


export default activeNavigations
