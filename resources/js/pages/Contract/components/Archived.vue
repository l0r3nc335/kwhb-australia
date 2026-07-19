<script setup>
import { defineAsyncComponent, watch } from 'vue'
import PublishedTable from "@/pages/Contract/components/PublishedTable.vue"
import Templates from "@/pages/Contract/components/Templates.vue"
import { useAuthStore } from "@/store/authStore"

const ContractsTable = defineAsyncComponent(() => import('./ContractsTable.vue'));
const LetterOfOfferTable = defineAsyncComponent(() => import('@/pages/Contract/components/LetterOfOfferTable.vue'))

const emit = defineEmits();
const type = 'Published'
const currentTab = ref('letter_of_offer');
const currentLetterSubTab = ref('table'); //table or template

const openCreateNewTemplate = (templateData) => {
    emit('openCreateNewTemplate', templateData);
};

const getCurrentUserId = () => {
    return useAuthStore().userData.id
}

const handleCreateNewCancel = (create) => {
    emit('handleCreateNewCancel', create);
    console.log('handleCreateNewCancel', create);
};
</script>

<template>
    <VCard>
        <VTabs  v-model="currentTab" class="__tab_wrapper">
            <!-- TABS SECTIONS -->
            <VTab :key="'letters'" class="tab __tab_item_wrapper" :value="'letters'">
                <span class="__tab_item_text">Letters of Offer</span>
            </VTab>
            <VTab :key="'contracts'" class="tab __tab_item_wrapper" :value="'contracts'">
                <span class="__tab_item_text">Contracts</span>
            </VTab>
        </VTabs>

        <VCardText style="border-left: 1px solid #ebeff5!important;">
            <VWindow v-model="currentTab">
                <VWindowItem value="letters">
                    <div>
                        <!-- Nested Tabs for Letters -->
                        <VTabs v-model="currentLetterSubTab" class="__sub_tab_wrapper" density="compact">
                            <VTab value="table">
                                <span class="__tab_item_text">Table</span>
                            </VTab>
                            <VTab value="template">
                                <span class="__tab_item_text">Templates</span>
                            </VTab>
                        </VTabs>
                        <VWindow v-model="currentLetterSubTab">
                            <VWindowItem value="table">
                                <VCard
                                    style="box-shadow: 0 0 4px 2px rgba(0, 0, 0, 0.1);
                                        padding: 10px;
                                        margin: 10px;
                                        min-height: 400px;"
                                >
                                    <LetterOfOfferTable :status="'archive'"/>
                                </VCard>
                            </VWindowItem>
                            <VWindowItem value="template">
                                <Templates
                                    @openCreateNewTemplate="openCreateNewTemplate"
                                    @handleCreateNewCancel="handleCreateNewCancel"
                                    :type="'letter_of_offer'"
                                    :ownerEmployeeId="useAuthStore().userData.employee.id"
                                    @open-overlay="payload => emit('open-overlay', payload)"
                                />
                            </VWindowItem>
                        </VWindow>
                    </div>
                </VWindowItem>

                <VWindowItem value="contracts">
                    <div>
                        <!-- Nested Tabs for Letters -->
                        <VTabs v-model="currentLetterSubTab" class="__sub_tab_wrapper" density="compact">
                            <VTab value="table">
                                <span class="__tab_item_text">Table</span>
                            </VTab>
                            <VTab value="template">
                                <span class="__tab_item_text">Templates</span>
                            </VTab>
                        </VTabs>
                        <VWindow v-model="currentLetterSubTab">
                            <VWindowItem value="table">
                                <VCard
                                    style="box-shadow: 0 0 4px 2px rgba(0, 0, 0, 0.1);
                                        padding: 10px;
                                        margin: 10px;
                                        min-height: 400px;"
                                >
                                    <ContractsTable :status="'archive'"/>
                                </VCard>
                            </VWindowItem>
                            <VWindowItem value="template">
                                <Templates
                                    @openCreateNewTemplate="openCreateNewTemplate"
                                    @handleCreateNewCancel="handleCreateNewCancel"
                                    :type="'contract'"
                                    :ownerEmployeeId="useAuthStore().userData.employee.id"
                                    @open-overlay="payload => emit('open-overlay', payload)"
                                />
                            </VWindowItem>
                        </VWindow>
                    </div>
                </VWindowItem>
            </VWindow>
        </VCardText>
    </VCard>
</template>

<style scoped>
    .__tab_item_text {
        font-family: Arial, Helvetica, sans-serif!important;
        color: black!important;
        font-size: 16px!important;
    }
    .__tab_item_wrapper {
        height: 80px!important;
    }
</style>
