<script setup>
import { watch } from 'vue';
import PublishedTable from "@/pages/Contract/components/PublishedTable.vue"
import Templates from "@/pages/Contract/components/Templates.vue"

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
                    <Templates
                        @openCreateNewTemplate="openCreateNewTemplate"
                        @handleCreateNewCancel="handleCreateNewCancel"
                        :type="'letter_of_offer'"
                        :status="'published'"
                        @open-overlay="payload => emit('open-overlay', payload)"
                    />
                </VWindowItem>

                <VWindowItem value="contracts">
                    <Templates
                        @openCreateNewTemplate="openCreateNewTemplate"
                        @handleCreateNewCancel="handleCreateNewCancel"
                        :type="'contract'"
                        :status="'published'"
                    />
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
