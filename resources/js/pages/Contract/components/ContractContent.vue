<script setup>
import { defineAsyncComponent, defineEmits, ref, watch } from 'vue';

const currentTab = ref('contract'); 
const emit = defineEmits(['open-overlay']);

const tabs = [
  { title: 'Contract', key: 'contract' },
  { title: 'Templates', key: 'templates' }
];

const props = defineProps({
    activeTab: {
        type: null,
        required: false,
    },
})

onMounted(() => {
    if(props.activeTab !== null){
        currentTab.value = props.activeTab
    }
});

const Contracts = defineAsyncComponent(() => import('./Contracts.vue'));
const AllContracts = defineAsyncComponent(() => import('./ContractsTable.vue'));
const Templates = defineAsyncComponent(() => import('./Templates.vue'));

watch(currentTab, (newTab) => {
  const tabName = tabs.find(tab => tab.key === newTab)?.title;
  emit('update:tabOpen', tabName);
});

const handleCreateNewCancel = (create) => {
  emit('handleCreateNewCancel', create);
  console.log('handleCreateNewCancel', create);
};
const openCreateNewTemplate = (templateData) => {
    emit('openCreateNewTemplate', templateData); 
};

const type = 'contract';
</script>

<template>
  <VCard>
    <VTabs v-model="currentTab" class="__tab_wrapper">
      <VTab
          v-for="item in tabs"
          :key="item.key"
          :value="item.key"
          class="tab __tab_item_wrapper"
          @click="emit('update-contract-active-tab', item.key)"
      >
          <span class="__tab_item_text">{{ item.title }}</span>
      </VTab>
    </VTabs>

    <VCardText style="border-left: 1px solid #ebeff5!important;">
      <VWindow v-model="currentTab">
        <VWindowItem value="contract">
            <!--div class="mb-6 font-weight-bold text-h3">
                <span style="color: #808494;">All</span>
            </div-->
          <VCard>
              <VCard
                  style="box-shadow: 0 0 4px 2px rgba(0, 0, 0, 0.1);
                    padding: 10px;
                    margin: 10px;
                    min-height: 400px;
                ">
                  <AllContracts />
              </VCard>
          </VCard>
        </VWindowItem>

        <VWindowItem value="templates">
          <Templates
              @openCreateNewTemplate="openCreateNewTemplate"
              @handleCreateNewCancel="handleCreateNewCancel"
              :type="type"
              @open-overlay="payload => emit('open-overlay', payload)"
          />

        </VWindowItem>
      </VWindow>
    </VCardText>
  </VCard>
</template>


<style scoped>
.v-slide-group {
    /* background-color: #6b7280!important; */
    height: 50px;
}
.tab {
    height: 50px!important;
    @apply tw-mr-8 tw-py-4 tw-text-gray-400 tw-text-[1.1rem] tw-cursor-pointer hover:tw-text-black;
}
.tab.active {
    position: relative;
    color: green!important;
    font-weight: 1600!important;
}

.active {
    position: relative;
    font-weight: 1600!important;

}

.__tab_wrapper {
    margin-left: -21px;
    border-bottom: 1px solid #ebeff5!important;
    width: 100%;
    height: 80px!important;
    display: flex!important;
    align-items: center!important;
}

.v-slide-group-item--active {
    font-weight: bold!important;       /* or 600-700 */
    /* color: #2F2F48!important;          /* Dark indigo or navy-like */
}

.__tab_item_text {
    font-family: Arial, Helvetica, sans-serif!important;
    color: black!important;
    font-size: 16px!important;
}
.__tab_item_wrapper {
    height: 80px!important;
}
</style>
