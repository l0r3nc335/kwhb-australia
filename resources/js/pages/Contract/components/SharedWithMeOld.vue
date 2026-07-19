<script setup>
import { watch } from 'vue';
import PublishedTable from "@/pages/Contract/components/PublishedTable.vue"
import Templates from "@/pages/Contract/components/Templates.vue"
import { useAuthStore } from "@/store/authStore"

const emit = defineEmits();
const type = 'Published'
const currentTab = ref('letter_of_offer');

const openCreateNewTemplate = (templateData) => {
    emit('openCreateNewTemplate', templateData);
};
const handleCreateNewCancel = (create) => {
    emit('handleCreateNewCancel', create);
    console.log('handleCreateNewCancel', create);
};

onMounted(() => {
    //useAuthStore().userData.role.id
    console.log(useAuthStore().userData.employee.id);
});
</script>

<template>
    <VCard>
        <VTabs  v-model="currentTab" class="__tab_wrapper">
            <!-- TABS SECTIONS -->
            <VTab :key="'letters'" class="tab" :value="'letters'">Letters of Offer</VTab>
            <VTab :key="'contracts'" class="tab" :value="'contracts'">Contracts</VTab>
        </VTabs>

        <VCardText>
            <VWindow v-model="currentTab">
                <VWindowItem value="letters">
                    <Templates
                        @openCreateNewTemplate="openCreateNewTemplate"
                        @handleCreateNewCancel="handleCreateNewCancel"
                        :type="'letter_of_offer'"
                        :ownerEmployeeId="useAuthStore().userData.employee.id"
                    />
                </VWindowItem>

                <VWindowItem value="contracts">
                    <Templates
                        @openCreateNewTemplate="openCreateNewTemplate"
                        @handleCreateNewCancel="handleCreateNewCancel"
                        :type="'contract'"
                        :ownerEmployeeId="useAuthStore().userData.employee.id"
                    />
                </VWindowItem>
            </VWindow>
        </VCardText>
    </VCard>
</template>
