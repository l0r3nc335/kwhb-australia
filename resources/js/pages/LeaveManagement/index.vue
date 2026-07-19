<script setup>
import LeaveCalendar from '@/pages/LeaveManagement/leave-calendar.vue';
import LeaveRequest from '@/pages/LeaveManagement/leave-request.vue';
import Pusher from 'pusher-js';

//const employeeStore = useEmployeeStore();

// console.log(employeeStore.data.employee_selected);
// import { useRoute } from 'vue-router';

// definePage({
//   meta: {
//     navActiveLink: 'EmployeeDetails',
//     key: 'tab',
//   },
// })

// const route = useRoute('EmployeeDetails')

// console.log("route.params.tab", route.params.tab)

const message = ref('message');
// const APP_KEY = import.meta.env.VUE_PUSHER_APP_KEY;
// const APP_CLUSTER = import.meta.env.VUE_PUSHER_APP_CLUSTER;
const APP_KEY = "5051424ca173d93a9c01";
const APP_CLUSTER = "ap1";
// console.log("APP_KEY", APP_KEY);
// console.log("APP_CLUSTER", APP_CLUSTER);
const pusher = new Pusher(APP_KEY, {
  cluster: APP_CLUSTER,
});
const handleNewMessage = (data) => {
  message.value = data;
};

const routeTab = ref('LeaveRequest')

const activeTab = computed({
  get: () => routeTab,
  set: () => routeTab,
})

// tabs
const tabs = [
  {
    title: 'Leave Request',
    tab: 'LeaveRequest',
  },
  {
    title: 'Leave Calendar',
    tab: 'LeaveCalendar',
  },
]

// const router = useRouter()
// const employeeStore = useEmployeeStore();
// console.log(employeeStore.data.employee_selected.first_name);
// if(employeeStore.data.employee_selected.first_name == null)
// {
//   router.push("employee")
// }

onMounted(() => {
  const channel = pusher.subscribe('my-channel');
  channel.bind('update-notification', handleNewMessage);
});

onBeforeUnmount(() => {
  pusher.unsubscribe('my-channel');
});
</script>

<template>
  <div>
    <h1>Leave Management</h1>
    <VTabs
      v-model="activeTab"
      class="v-tabs-pill"
    >
      <VTab
        v-for="item in tabs"
        :key="item.title"
        :value="item.tab"
      >
        {{ item.title }}
      </VTab>
    </VTabs>

    <VWindow
      v-model="routeTab"
      class="mt-5 disable-tab-transition"
    >
      <VWindowItem value="LeaveRequest">
        <LeaveRequest />
      </VWindowItem>

      <VWindowItem value="LeaveCalendar">
        <LeaveCalendar />
      </VWindowItem>
    </VWindow>
  </div>
</template>
