<route lang="yaml">
meta:
layout: default
requiresSuperAdminHRCEO: true
</route>

<template>
    <div  style="position: relative;">
        <div
            style="
                position: absolute;
                top: 0;
                right: 0;
                z-index: 999;
                padding: 10px;
                width: 200px;
                height: 80px;
                display: flex;
            "
        >
            <!--Notifications class="tw-mr-2"></Notifications-->
            <UserProfile />
        </div>
        <div
            style="
            z-index: 1;
            position: relative;
            padding: 0;
            margin: 0;
            "
        >
            <section>

                <div
                    class="tw-flex tw-w-full tw-gap-0"
                    v-if="!wysiwygTemplateCreator && !contractLetterPreview && isContractOnPageContent === false"
                >
                    <VCard
                        class="__left_panel tw-transition-all tw-duration-300 tw-overflow-hidden px-5"
                        :class="isLeftPanelCollapsed ? 'tw-w-[20px] tw-px-0' : 'tw-w-[20%] tw-px-5'"
                    >
                        <div class="__left_panel_title_wrapper">
                            <div class="font-weight-bold text-h3 tw-ms-[20px]"  :class="{ 'tw-invisible': isLeftPanelCollapsed }">
                                <p style="color: #808494;">{{ selectedViewTitle }}</p>
                            </div>
                        </div>

                        <!-- Button - Create new [New Template, From My Template] -->
                        <div
                            class="tw-flex tw-w-full tw-p-0 tw-m-0 tw-mt-10"
                            :class="{ 'tw-invisible': isLeftPanelCollapsed }"
                        >
                            <v-menu offset-y>
                                <template #activator="{ props }">
                                    <v-btn
                                        class="tw-w-full tw-h-full tw-p-0 tw-border-0 tw-flex tw-items-stretch tw-overflow-hidden"
                                        v-bind="props"
                                        size="large"
                                        style="position: relative;"
                                    >
                                        <div class="tw-w-3/4 tw-h-full tw-absolute tw-left-0 tw-flex tw-items-center tw-justify-center" style="border-right: 2px solid white">
                                            <VIcon icon="tabler-plus" size="26" class="tw-mr-2" />
                                            Create New
                                        </div>

                                        <div class="tw-w-1/4 tw-h-full tw-absolute tw-right-0 tw-flex tw-justify-center tw-items-center">
                                            <div class="tw-w-0 tw-h-0 tw-border-l-[6px] tw-border-l-transparent
                                                tw-border-r-[6px] tw-border-r-transparent
                                                tw-border-t-[7px] tw-border-t-white">
                                            </div>
                                        </div>
                                    </v-btn>
                                </template>

                                <v-list>
                                    <v-list-item v-for="option in createOptions" :key="option.id" @click="handleCreateFunction(option.id)">
                                        {{ option.name }}
                                    </v-list-item>
                                </v-list>
                            </v-menu>
                        </div>

                        <!-- Side Menu - Contracts Page -->
                        <LeftSideMenu @updateSelectedView="updateSelectedView" v-if="!wysiwygTemplateCreator" :selectedView="selectedView" />

                        <!-- Toggle Button at the bottom -->
                        <v-btn
                            icon
                            variant="text"
                            class="tw-absolute tw-top-50 tw-right-2 tw-translate-y-[-50%] tw-bg-gray-50 tw-mt-10"
                            @click="isLeftPanelCollapsed = !isLeftPanelCollapsed"
                        >
                            <VIcon :icon="isLeftPanelCollapsed ? 'tabler-chevron-right' : 'tabler-chevron-left'" />
                        </v-btn>
                    </VCard>

                    <!-- Content Right - Main Content for [Contract, Templates]  -->
                    <VCard
                        class="__right_panel tw-min-h-[600px] tw-flex-grow tw-transition-all tw-duration-300"
                    >
                        <SharedWithMe
                            v-if="selectedView === 'sharedWithMe'"
                            @open-overlay="displayCreateNewOverlay"
                        > </SharedWithMe>
                        <Archived
                            v-if="selectedView === 'archived'"
                            @open-overlay="displayCreateNewOverlay"
                        > </Archived>
                        <Published
                            v-if="selectedView === 'published'"
                            @open-overlay="displayCreateNewOverlay"
                        >
                        </Published>

                        <LetterOfOffer
                            v-if="selectedView === 'letterOfOffer'"
                            @update:tabOpen="handleTabChange"
                            @openCreateNewTemplate="openCreateNewTemplate"
                            :activeTab="offerSectionActiveTab"
                            @update-offer-active-tab="handleUpdateOfferActiveTab"
                            @open-overlay="displayCreateNewOverlay"
                            @editRow="handleEditRowStateFlag"
                        >
                        </LetterOfOffer>
                        <ContractContent
                            v-if="selectedView === 'contracts' || selectedView === 'contract'"
                            @openCreateNewTemplate="openCreateNewTemplate"
                            @update:tabOpen="handleTabChange"
                            :activeTab="contractSectionActiveTab"
                            @update-contract-active-tab="handleUpdateContractActiveTab"
                            @open-overlay="displayCreateNewOverlay"
                        >
                        </ContractContent>
                    </VCard>
                </div>

                <!-- Modal - Create new Contract/Offer -->
                <ContractAndOfferWizard
                    v-if="isCreateContract"
                    v-model="isCreateContract"
                    :selectedView="type"
                    :offerId="contractOfferId"
                    :passedSelectedTemplate="passedSelectedTemplate"
                    @handleContractLetterPreview="handleContractLetterPreview"
                    @handleCreateContractLetterFormData="handleCreateContractLetterFormData"
                />

                <!-- Create new template :employee="selectedEmployee" :customData="customisationData" -->
                <CreateNewTemplate
                    v-if="wysiwygTemplateCreator"
                    :template="selectedTemplate"
                    :type="type"
                    @handleCreateNewCancel="handleCreateNewCancel"
                    @close-wysiwyg="closeWysiwygTemplateCreator"
                />

                <!-- View Contract and Letter - Editing and Signature -->
                <ContractOfferOnPageContent
                    v-if="isContractOnPageContent"
                    v-model="contractData"
                    :editRowStateFlag="editRowStateFlag"
                    @initCreateContractForOffer="handleInitCreateContractForOffer"
                    @closeContract="isContractOnPageContent = false"
                />

            </section>
        </div>
    </div>
</template>

<script>
import Archived from "@/pages/Contract/components/Archived.vue";
import ContractContent from "@/pages/Contract/components/ContractContent.vue";
import CreateNewTemplate from "@/pages/Contract/components/CreateNewTemplate.vue";
import LeftSideMenu from "@/pages/Contract/components/LeftSideMenu.vue";
import LetterOfOffer from "@/pages/Contract/components/LetterOfOffer.vue";
import Published from "@/pages/Contract/components/Published.vue";
import SharedWithMe from "@/pages/Contract/components/SharedWithMe.vue";
import ViewAll from "@/pages/Contract/components/ViewAll.vue";
import CreateNewContract from "@/pages/Contract/components/CreateNewContract.vue";
import { provide, ref } from 'vue';

//TODO: Decomission these
import DialogCreate from "@/pages/Contract/components/CreatingTabs/index.vue";

//TODO: Replace with These
import ContractAndOfferWizard from "@/pages/Contract/components/ContractAndOfferWizard/ContractAndOfferWizard.vue"
import ContractOfferOnPageContent
    from "@/pages/Contract/components/ContractAndOfferWizard/ContractOfferOnPageContent/ContractOfferOnPageContent.vue"
//import Notifications from "@/components/AppNotifications.vue"
//import Notifications from "@core/components/NotificationsV2.vue"
import UserProfile from "@/layouts/components/UserProfile.vue"
import { storeToRefs } from "pinia"
import { useAuthStore } from "@/store/authStore"

export default {
    components: {
        UserProfile,
        ContractOfferOnPageContent,
        LeftSideMenu,
        ContractContent,
        ViewAll,
        SharedWithMe,
        Archived,
        Published,
        LetterOfOffer,
        CreateNewTemplate,
        CreateNewContract,
        DialogCreate,
        ContractAndOfferWizard,
    },
    data() {
        return {
            contractLetterPreview: false,  //shortcut
            selectedView: 'contracts',
            selectedViewTitle: 'Contracts',
            selectedTab: 'Contracts',
            isCreateContractVisible: false,
            isCreateContract: false, //shortcut
            isCreateLetterOfOfferVisible: false,
            type: 'contract',
            selectedTemplate: null,
            createContractLetterFormData: {},
            offerSectionActiveTab: null,
            contractSectionActiveTab: null,
            isContractOnPageContent: false,
            contractData: null,
            contractOfferId: null,
            userData:  storeToRefs(useAuthStore()),
            isLeftPanelCollapsed: false,
            passedSelectedTemplate: null,
            editRowStateFlag: false
        };
    },
    computed: {
        createOptions() {
            const templates = [
                { id: 1, name: "New Template" },
                { id: 2, name: "From My Templates" }
            ];

            const createNames = {
                "Contracts": "Create contract",
                "Letter of Offer": "Create letter of offer"
            };

            /*const createName = createNames[this.selectedViewTitle];

            if (createName) {
                templates.push({ id: 3, name: createName });
            }*/

            return templates;
        }
    },
    setup() {
        const wysiwygTemplateCreator = ref(false);
        const templateContentEdit = ref(null);
        const rowRecordOfTable = ref(null);
        const recordActionState = ref(null);

        provide('wysiwygTemplateCreator', wysiwygTemplateCreator);
        provide('templateContentEdit', templateContentEdit);
        provide('rowRecordOfTable', rowRecordOfTable);

        /*watch(rowRecordOfTable, (newVal) => {
            if (newVal) {

            }
        });*/

        return {
            wysiwygTemplateCreator,
            templateContentEdit,
            rowRecordOfTable,
        };
    },
    watch: {
        isCreateContract(newVal, oldVal) {
            if(newVal === false) {
                this.isCreateContractVisible = false
                this.contractLetterPreview = false
            }
        },
        rowRecordOfTable(newVal) {
            if (newVal) {
                this.handleRowRecordOfTable(newVal)
            }
        },
    },
    methods: {
        handleEditRowStateFlag(val) {
            this.editRowStateFlag = val
        },
        displayCreateNewOverlay(payload) {
            this.passedSelectedTemplate = payload
            this.contractOfferId = null
            this.isCreateContract = true;
            this.isCreateContractVisible = true
        },
        handleInitCreateContractForOffer(contractId, offerId){
            if(contractId === null){
                this.isContractOnPageContent = false
                this.type = 'contract'
                this.contractOfferId = offerId
                this.isCreateContract = true
                this.selectedView = 'contracts'
                this.selectedViewTitle = 'Contracts'
            }
        },
        handleRowRecordOfTable(data) {
            this.contractData = data
            this.isContractOnPageContent = true;
        },
        handleUpdateOfferActiveTab(tab) {
            this.offerSectionActiveTab = tab
        },
        handleUpdateContractActiveTab(tab) {
            this.contractSectionActiveTab = tab
        },
        handleCreateContractLetterFormData(val) {
            this.createContractLetterFormData = val
            this.contractLetterPreview = true
        },
        updateSelectedView(view) {
            this.selectedView = view;
            this.type = view
            if(view === 'contracts'){
                this.selectedViewTitle = 'Contracts'
            }else if(view === 'letterOfOffer'){
                this.selectedViewTitle = 'Letter of Offer'
            }else if(view === 'published'){
                this.selectedViewTitle = 'Published'
            }else if(view === 'archived'){
                this.selectedViewTitle = 'Archived'
            }else if(view === 'sharedWithMe'){
                this.selectedViewTitle = 'Shared with Me'
            }
        },
        handleContractLetterPreview(val) {
            this.contractLetterPreview = val
        },
        handleCreateNewCancel(cancel) {
            this.newTemplate = cancel;
            this.selectedView = 'contracts';
            this.selectedTab = 'contract';
            this.selectedTemplate = '';
            //console.log(this.selectedView, this.selectedTab);
            //console.log(handleCreateNewCancel);
        },
        handleTabChange(tabName) {
            this.selectedTab = tabName;
            console.log('Selected tab changed to:', tabName);
        },

        openCreateNewTemplate(templateData) {
            console.log("Template Data Received:", templateData);
            this.newTemplate = true;
            this.selectedTemplate = templateData.selectedTemplate;
            this.selectedEmployee = templateData.selectedEmployee;
            this.customisationData = templateData.customisationData;
            console.log("Selected Template:", this.selectedTemplate);
            console.log("Selected Employee:", templateData.selectedEmployee);
            console.log("Customisation Data:", templateData.customisationData);
        },

        handleCreateFunction(selectedId) {
            //[1 = Create New Template]
            if (selectedId === 1) {
                this.wysiwygTemplateCreator = true;
            }

            //[3 = Create contract/letter of offer]
            if(selectedId === 2) {
                this.contractOfferId = null
                this.isCreateContract = true;
                this.isCreateContractVisible = true
            }

            if( this.selectedView === 'letterOfOffer' ||
                this.selectedTab === 'Templates'
            ){
                this.type = 'letter_of_offer';
            }

            if(this.selectedView === 'contracts' || this.selectedView === 'contract'){
                this.type = 'contract';
            }
            /*
            else if (selectedId === 2) {
                this.newTemplate = true;
                this.type = 'contract';
            }
            this.handleCreateNew();*/
        },
        closeWysiwygTemplateCreator(type) {
            this.selectedView = type === 'letter_of_offer' ? 'letterOfOffer' : type
            this.offerSectionActiveTab = 'templates'
            this.wysiwygTemplateCreator = false
            this.myActiveTab = 'templates'
        },
    },//end methods
};
</script>

<style scoped lang="scss">
@use "@layouts/styles/_variables" as variables;
.__left_panel {
    border-radius: 0px!important;
    box-shadow: none;
}
.__right_panel {
    border-radius: 0px!important;
    box-shadow: none;
    //border-left: variables.$layout_content_boarder;
}
.v-spacer {
    display: none;
}

.custom-navbar-page .layout-wrapper.layout-nav-type-vertical.layout-navbar-sticky .layout-navbar {
    display: flex;
    justify-content: flex-end;
}

.__left_panel_title_wrapper {
    margin-left: -21px;
    border-bottom: 1px solid #ebeff5!important;
    width: 120%;
    height: 80px;
    display: flex;
    align-items: center;
}

.__global_text_font {
    font: 16px Arial, Helvetica, sans-serif;
}
</style>
