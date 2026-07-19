<template>
    <div class="tw-flex tw-flex-col">
        <div class="tw-text-[2rem] tw-font-bold tw-mb-4">
        <span v-if="!selectedCategory" class="tw-cursor-pointer hover:tw-text-gray-400" @click="selectedCategory=null">
            <template v-if="currentTab === 'resources'">Resources</template>
            <template v-if="currentTab === 'acknowledgement'">Acknowledgements</template>
        </span>
        <span v-else class="tw-text-gray-400" v-if="selectedCategory">{{selectedCategory?.name}}</span>
        </div>
        <div class="tw-flex tw-flex-col tw-mt-4 tw-py-4">
            <ResourcesTab v-if="currentTab==='resources'" :selected-category="selectedCategory" @selected-category="category => selectedCategory = category"></ResourcesTab>
            <AcknowledgementTab v-if="currentTab==='acknowledgement'" :selected-category="selectedCategory" ></AcknowledgementTab>
        </div>
    </div>
</template>

<script>
    // Components
    import AcknowledgementTab from "@/pages/Resources/tabs/AcknowledgementTab.vue";
import ResourcesTab from "@/pages/Resources/tabs/ResourcesTab.vue";
import TagsTab from "@/pages/Resources/tabs/TagsTab.vue";

    export default {
        components: {
            ResourcesTab,
            AcknowledgementTab,
            TagsTab
        },

        data() {
            return {
                currentTab: 'resources',
                selectedCategory: null
            }
        },
    }
</script>

<style>
.leave-tabs {
    @apply tw-flex tw-border-b-[1px]
}

.leave-tabs .tab {
    @apply tw-mr-8 tw-py-4 tw-text-gray-400 tw-text-[1.1rem] tw-cursor-pointer hover:tw-text-blue-900
}

.leave-tabs .tab.active {
    position: relative;
    @apply tw-text-gray-600 tw-font-bold
}

.leave-tabs .tab.active::after {
    content: "";
    display: block;
    width: 100%;
    height: 5px;
    position: absolute;
    bottom: -2px;
    background-color: #e35205;
}
</style>
