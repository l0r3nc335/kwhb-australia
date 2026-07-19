<script setup>
import ComplianceTbl from './ComplianceTbl.vue';
import VaccinationTbl from './VaccinationTbl.vue';
import {useEmployeeStore} from "@/store/employeeStore";

const employeeStore = useEmployeeStore();
const employee = employeeStore.data.employee_selected;
</script>
<template>
    <div>
        <div class="mt-10 mb-16" v-if="hasPermissions('people.employee_certification_vaccinations.view', employee)">
            <p class="text-h4 font-weight-bold mb-3"><VIcon icon="tabler-vaccine" size="22" color="#848898" class="mb-1 mr-2"/>Vaccinations</p>
            <VaccinationTbl />
        </div>

        <div class="mt-10" v-if="hasPermissions('people.employee_certification_compliance.view', employee)">
            <p class="text-h4 font-weight-bold mb-3"><VIcon icon="tabler-certificate" color="#848898" size="22" class="mb-2 mr-2"/>Compliance</p>
            <ComplianceTbl />
        </div>
    </div>
</template>
<style scoped>

</style>
