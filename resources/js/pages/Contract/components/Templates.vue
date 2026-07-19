<script setup>
import templateApi from "@/api/template/templateApi";
import toast from '@/composable/toast.js';
import { computed, inject, onMounted, ref } from 'vue';
import { VCard, VIcon, VList, VListItem, VListItemTitle, VMenu } from 'vuetify/components';

const items = ref([]);
const searchTerm = ref("");
const activeItem = ref(null);
const showModal = ref(false);
const editTemplate = ref(false);
const selectedItem = ref(null);
const versions = ref([]);
const showHistoryModal  = ref(false);
const selectedTemplate = ref(null);
const wysiwygTemplateCreator = inject('wysiwygTemplateCreator');
const templateContentEdit = inject('templateContentEdit');
const isTemplateSelected = ref(false)
const isHistorySelected= ref(false)
const selectedId = ref(null)
const passSelectedTemplate = ref(null)

const activeHistoryId = ref(null);

const props = defineProps({
    type: String,
    status: {
        type: null,
        default: null,
    },
    ownerEmployeeId: {
        type: null,
        default: null,
    },
    forCreating: {
        type: Number,
        default: 0,
    },
    outsideOverlay: {
        type: Boolean,
        default: true
    },
    selectedTemplateProp: {
        type: null,
        default: null
    }
});

const emit = defineEmits();

const fetchData = async () => {
    try {
        let typeToUse = props.type || 'contract';

        if(typeToUse === 'letterOfOffer'){
            typeToUse = 'letter_of_offer';
        }

        if(props.status !== null) {
            items.value = await templateApi.fetchAllTemplatesByTypeAndStatus(typeToUse, props.status);
        } else if(props.ownerEmployeeId !== null) {
            items.value = await templateApi.fetchAllTemplatesByOwnerEmployeeId(typeToUse, props.ownerEmployeeId);
        }else{
            items.value = await templateApi.fetchAllTemplatesByType(typeToUse);
        }

    } catch (error) {
        console.error('Error fetching data:', error);
    }
};

const fetchTemplateVersion = async (templateId) => {
    try {
        const response = await templateApi.fetchTemplateVersion(templateId);
        versions.value = response;
        console.log(versions.value);
    } catch (error) {
        console.error('Error fetching template version:', error);
    }
};

onMounted(() => {
    fetchData();
});

const filteredItems = computed(() => {
    if (!searchTerm.value) return items.value;
    return items.value.filter(item => {
        return item.templateName.toLowerCase().includes(searchTerm.value.toLowerCase());
    });
});

console.log(filteredItems)

const getStatusColor = (status) => {
    switch (status) {
    case 'Published':
        return { color: 'green' };
    case 'Archived':
        return { color: 'orangered' };
    case 'Draft':
        return { color: 'gray' };
    default:
        return { color: 'black' };
    }
};

const getDotColor = (activity) => {
    console.log("Activity received:", activity);
    if (!activity) return "red";
    if (activity.toLowerCase().includes("published")) return "success";
    if (activity.toLowerCase().includes("updated")) return "info";
    if (activity.toLowerCase().includes("created")) return "warning";
    return "red";
};

const formatDaysAgo = (daysAgo) => {
    if (daysAgo === 0) {
        return 'Today';
    }
    if (daysAgo === 1) {
        return '1 day ago';
    }
    return `${daysAgo} days ago`;
};

const handleEdit = (item) => {
    wysiwygTemplateCreator.value = true
    templateContentEdit.value = item
    //selectedItem.value = item;
    //emit('openCreateNewTemplate', { selectedTemplate: item });
};
const handleDuplicate = async (item) => {
    try {
        const formData = new FormData()
        formData.append('id', item.id)
        await templateApi.duplicateTemplate(formData);
        toast('success', `Template duplicated!`);

        await fetchData();
    } catch (error) {
        //toast('error', `Error updating template status. Please try again.`);
    }
};

const handleStatusChange = async (item, status) => {
    if (item && item.id && status !== 'History') {
        item.status = status;
        try {
            await templateApi.updateTemplateStatus(item.id, status);
            showModal.value = false;
            toast('success', `Template Status Updated to ${status}!`);
            await fetchData();
        } catch (error) {
            //toast('error', `Error updating template status. Please try again.`);
        }
    } else if(status === 'History') {
        //toast('error', 'No template selected or invalid ID.');
        isHistorySelected.value = true
    }
};

const openModal = (item) => {
    activeItem.value = item;
    showModal.value = true;
};

const openVersionModal = (item) => {

    activeItem.value = item;
    showHistoryModal.value = true;
    if (item && item.id) {
        fetchTemplateVersion(item.id);
    }
};

const displayText = computed(() => {
    return props.type === 'letter_of_offer' || props.type === 'letterOfOffer'
        ? 'Select Letter of Offer'
        : 'Select Contract';
});

const tester = [{ title: 'Option 1', value: 'Option 1' }, { title: 'Option 2', value: 'Option 2' }, { title: 'Option 3', value: 'Option 3' }];

const handleTemplateClick = (template) => {
    // Set the selected template and trigger the style change
    if (props.forCreating === 1) {
        selectedTemplate.value = template.id === selectedTemplate.value ? null : template.id;
        emit('templateSelected', template);
    }
};

// Style for selected template
const isSelected = (template) => {
    return selectedTemplate.value === template.id;
};
</script>


<template>
    <div class="page-content-container" v-if="!editTemplate">
        <section>
            <div class="mb-6 font-weight-bold text-h3">
                <span>Templates</span>
            </div>
            <div class="search-bar-container">
                <input
                    type="text"
                    class="search-bar"
                    placeholder="Search Templates..."
                    v-model="searchTerm"
                />
            </div>
        </section>

        <div class="font-weight-bold tw-mt-5">
            <span>{{ displayText }}</span>
        </div>

        <div class="templates-container">
            <div
                v-for="item in filteredItems"
                :key="item.id"
                @click="handleTemplateClick(item)"
                :class="['__card_wrapper', 'template-card', { 'selected-template': isSelected(item) }]"
            >
                <VCard
                    class="__template_card"
                    :class="{
                        '__template_card--active': selectedId === item.id || (selectedTemplateProp && selectedTemplateProp.id == item.id)
                    }"
                    @click="selectedTemplateProp !== null? '' :  (selectedId = item.id, passSelectedTemplate = item)"
                >
                    <!-- Template Screenshot -->

                    <div
                        class="__template_card_image"
                        :style="{
                            backgroundImage: `url('/storage/${item.screenshot}')`
                        }"
                        :class="{ '__disabled_image': selectedTemplateProp !== null && !(selectedTemplateProp && selectedTemplateProp.id === item.id) }"
                    ></div>

                    <!-- LOWER MENU -->
                    <div class="__lower_card_menu" style="display: flex; justify-content: space-between; align-items: center; padding: 5px; border-top: 1px solid #d1d1d1;" v-if="forCreating !== 1">

                        <!-- status menu -->
                        <VMenu transition="scale-transition">

                            <template #activator="{ props }">
                                <VIcon icon="tabler-clock" @click="openVersionModal(item)" v-bind="props" size="22"  />
                            </template>

                            <!-- Edit, Publish, Archive OVERLAY -->
                            <VList>
                                <VListItem @click="handleEdit(item)">
                                    <VListItemTitle>Edit</VListItemTitle>
                                </VListItem>
                                <VListItem @click="handleDuplicate(item)">
                                    <VListItemTitle>Duplicate</VListItemTitle>
                                </VListItem>
                                <VListItem v-if="item.status !== 'Published'" @click="() => handleStatusChange(item, 'Published')">
                                    <VListItemTitle>Publish</VListItemTitle>
                                </VListItem>
                                <VListItem v-if="item.status !== 'Archived'" @click="() => handleStatusChange(item, 'Archived')">
                                    <VListItemTitle>Archive</VListItemTitle>
                                </VListItem>
                                <VListItem @click="activeHistoryId = item.id">
                                    <VListItemTitle>History</VListItemTitle>
                                </VListItem>
                            </VList>
                        </VMenu>

                        <!-- history menu -->
                        <VMenu
                            :model-value="activeHistoryId === item.id"
                            @update:model-value="val => { if (!val) activeHistoryId = null }"
                            transition="scale-transition"
                        >
                            <!-- item.status -->
                            <template #activator="{ props }">
                                <p
                                    v-bind="props"
                                    :style="[getStatusColor(item.status), { fontWeight: '400', fontSize: '13px', cursor: 'pointer' }]"
                                >
                                    {{ item.status }}
                                </p>
                            </template>
                            <VList class="__history_list_panel">
                                <VListItem >
                                    <!-- <VCard v-for="version in versions" :key="version.id">
                                        {{ version.activity }}
                                    </VCard> -->
                                    <VCard>
                                        <!-- Top Section with Title and Close Button -->
                                        <div class="d-flex" style="justify-content: space-between;">
                                            <span class="app-card-title font-weight-bold">History</span>
                                            <VIcon
                                                size="24"
                                                icon="tabler-square-rounded-x"
                                                @click="activeHistoryId = null"
                                            />
                                        </div>

                                        <!-- Timeline -->
                                        <VTimeline align="start" side="end" density="compact">
                                            <VTimelineItem
                                                v-for="version in versions"
                                                :key="version.id"
                                                :dot-color="getDotColor(version.activity)"
                                                size="x-small"
                                            >
                                                <div class="d-flex justify-space-between align-center gap-20 flex-wrap">
                                                    <span class="app-timeline-title font-weight-bold">{{ version.activity }}</span>
                                                    <span class="app-timeline-meta text-grey-500">{{ formatDaysAgo(version.days_ago) }}</span>
                                                </div>
                                            </VTimelineItem>
                                        </VTimeline>
                                    </VCard>

                                </VListItem>
                            </VList>
                        </VMenu>

                    </div>
                </VCard>
                <span style="text-align: center; font-weight: bold; font-size: small; overflow: hidden; margin: 10px 0;">{{ item.templateName }}</span>
            </div>
        </div>

        <transition name="slide-up">
            <div v-if="selectedId !== null && outsideOverlay" class="bottom-bar">
                <VCardActions class="justify-end __wizard_actin_wrapper">
                    <VBtn
                        class="__wizard_action_button"
                        color="white"
                        @click="emit('open-overlay', passSelectedTemplate)"
                    >
                        Create
                    </VBtn>
                </VCardActions>
            </div>
        </transition>

    </div>

</template>

<style scoped>
.__disabled_image {
    //filter: grayscale(95%) brightness(95%);
    //opacity: 0.8;
}

/* slide-up transition */
.slide-up-enter-active,
.slide-up-leave-active {
    transition: all 0.3s ease;
}

.slide-up-enter-from {
    transform: translateY(100%);
    opacity: 0;
}

.slide-up-enter-to {
    transform: translateY(0);
    opacity: 1;
}

.slide-up-leave-from {
    transform: translateY(0);
    opacity: 1;
}

.slide-up-leave-to {
    transform: translateY(100%);
    opacity: 0;
}

.__wizard_actin_wrapper {
    padding: 0!important;
}
.__wizard_action_button {
    background-color: #E35205;
    padding: 0 20px;
    margin: 5px 30px 5px 0!important;

}
.bottom-bar {
    justify-content: end; /* horizontal alignment (center) - left and right*/
    display: flex;
    position: fixed;      /* stay fixed relative to viewport */
    bottom: 0;            /* stick to bottom */
    left: 0;              /* start from left edge */
    width: 100%;          /* full width */
    background-color: #EBECEF;
    color: #fff;
    text-align: center;
    padding: 10px;
    z-index: 1000;        /* keep it above other content */
}

.search-bar-container {
    margin-top: 15px;
}

.search-bar {
    width: 100%;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 16px;
}

.templates-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 16px;
    overflow-y: auto;
}

.template-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    overflow: hidden;
    height: 220px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.app-timeline-title {
    font-size: 14px;
}

.__template_card {
    margin-top: 20px;
    background-color: white;
    font-size: 10px;
    color: black;
    text-align: center;
    box-shadow: 0 0 5px 3px rgba(0, 0, 0, 0.1);
    width: 95%; /* Let grid control width */
    /*max-width: 220px;*/
    height: 150px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transition: all 0.3s ease;
}

.__history_list_panel{
    margin-left: -150px!important;
}

/* Responsive scaling */
@media (min-width: 768px) {
    .__template_card {
        height: 150px;
    }
    .templates-container {
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); /* cards can grow bigger */
    }
}

@media (min-width: 1024px) {
    .__template_card {
        height: 150px;
    }
    .templates-container {
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); /* cards can grow bigger */
    }
}

@media (min-width: 1440px) {
    .__template_card {
        height: 270px;
        max-width: 300px;
    }
    .templates-container {
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); /* cards can grow bigger */
    }
}

@media (min-width: 1920px) {
    .__template_card {
        height: 800px;
        max-width: 550px;
    }
    .templates-container {
        grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); /* cards can grow bigger */
    }
    .__lower_card_menu {
        height: 60px;
        padding: 0 20px!important;
    }
    .__history_list_panel{
        margin-left: -450px!important;
    }
}


.__template_card--active {
    box-shadow: 0 0 0 3px rgb(40, 199, 111);
    border-color: #0A002D;
}

.__card_wrapper {
    padding: 0;
    height: auto;
    display: flex;
    justify-content: center;
}

.__template_card_image {
    flex: 1;
    background-repeat: no-repeat;
    background-size: contain;
    background-position: top;
    padding-bottom: 5px;
}
</style>

