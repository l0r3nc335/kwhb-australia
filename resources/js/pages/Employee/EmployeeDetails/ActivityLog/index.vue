<script setup>
import employeeApi from "@/api/employee/employeeApi";
import { useEmployeeStore } from "@/store/employeeStore";
import { PerfectScrollbar } from 'vue3-perfect-scrollbar';
import Timeline from './Timeline.vue';
const timelineEvents = ref([])
const loading = ref(false)
const empData = ref(null)
const employeeStore = useEmployeeStore();
empData.value = employeeStore.data.employee_selected

const events = async () => {
  try {
    loading.value = true
    const response = await employeeApi.fetch_activity_logs({ employee_id: empData.value.user_id })
    timelineEvents.value = response
    loading.value = false
  } catch (error) {
    console.error(error)
  }
}
events()
</script>

<template>
    <VRow>
        <VCol
        lg="8"
        sm="6"
    >
        <VCard
        outlined
        class="mt-12 mt-sm-0"
        elevation="5"
        >
        <VCardText>
            <p class="mb-6 text-h4 font-weight-bold text-left">
                <VIcon
                    icon="tabler-layout-grid"
                    class="ml-4 mr-4 mb-1"
                    size="small"
                />
                Activity Timeline
            </p>
            <template v-if="loading">
                <LoadingData></LoadingData>
            </template>
            <PerfectScrollbar
                :options="{ wheelPropagation: false }"
                style="block-size: 20rem"
            >
                <Timeline :events="timelineEvents"/>
            </PerfectScrollbar>
        </VCardText>
        </VCard>
    </VCol>
    </VRow>
</template>

<style lang="scss" scoped>
</style>
