<template>
  <div class="dashboard-wrapper">
    <h2 class="dashboard-title">Dashboard</h2>
    <div class="dashboard-grid">
      <router-link
        v-for="item in dashboardItems"
        :key="item"
        :to="dashboardRoutes[item]"
        class="dashboard-card-link"
      >
        <div class="dashboard-card">
          <div class="icon-container">
            <img
              :src="`/assets/images/icons/${item.toLowerCase()}.png`"
              :alt="item"
              class="dashboard-img"
            />
          </div>
          <h3 class="dashboard-card-title">{{ formatTitle(item) }}</h3>
          <p class="dashboard-desc">
            {{ dashboardDescriptions[item] || 'No description available.' }}
          </p>
        </div>
      </router-link>
    </div>
  </div>
</template>

<script setup>
const dashboardItems = [
  'vaccinations',
  'employees',
  'contracts',
  'compliance',
  'reports',
  'settings',
];

const dashboardDescriptions = {
  vaccinations: 'Easily view and manage all vaccination types, track assigned employees, and verify who has submitted proof of vaccination',
  employees: 'View and manage all employee profiles in one place  -  including roles, contact details, contracts and leaving notes',
  contracts: 'Create, send, and manage employment contracts and letters of offer. Track status, view signed documents, and ensure every hire is formally documented.',
  compliance: 'Access a complete overview of compliance requirements, see which employees are assigned to each and track submitted evidence to ensure full regulatory adherence',
  reports: 'Generate and access detailed reports across HR activities - from compliance, employee status, offswing and leave requests all in one central location',
  settings: 'Configure system preferences, manage user permissions, customise settings over your platform’s core settings.',
};

const dashboardRoutes = {
  vaccinations: '/vaccinations',
  employees: '/employee',
  contracts: '/contract',
  compliance: '/compliance',
  reports: '/report',
  settings: '/adminsettings',
};

function formatTitle(str) {
  return str.charAt(0).toUpperCase() + str.slice(1);
}
</script>

<style scoped>
h2{
    font-size: 32px;
}
.dashboard-wrapper {
  padding: 2rem 2.5rem;
}
.dashboard-title {
  font-size: 1.3rem;
  font-weight: 700;
  margin-bottom: 1.5rem;
}
.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 2rem 1.5rem;
}
.dashboard-card-link {
  text-decoration: none;
  color: inherit;
  display: block;
  height: 100%;
}
.dashboard-card {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 4px 20px 0 rgba(33, 40, 54, 0.08);
  padding: 16px;
  text-align: left;
  display: flex;
  flex-direction: column;
  align-items: center;
  min-height: 210px;
  transition: box-shadow 0.2s;
  height: 100%;
}
.dashboard-card:hover {
  box-shadow: 0 6px 26px 0 rgba(33, 40, 54, 0.13);
}
.icon-container {
  width: 100%;
  background: #f4f5f7;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  margin-bottom: 15px;
}
.dashboard-img {
  width: 155px;
  height: 155px;
  object-fit: contain;
}
.dashboard-card-title {
  font-size: 22px;
  font-weight: 700;
  color: #232323;
  align-self: flex-start;
  margin: 0 0 0.5rem 0;
}
.dashboard-desc {
  color: #6c757d;
  font-size: 0.97rem;
  line-height: 1.45;
  align-self: flex-start;
}
</style>
