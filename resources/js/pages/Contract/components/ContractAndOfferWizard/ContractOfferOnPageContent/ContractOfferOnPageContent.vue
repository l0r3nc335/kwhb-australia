<template>
    <div class="__non-printableXX">
        <div class="__editor_header_wrapper">
            <!--Button - Return Button left side -->
            <!--button class="__editor_back_button" @click="backstep" :disabled="this.currentStepContent === 3" :style="{ visibility: currentStepContent === 3 ? 'hidden' : 'visible' }">
                <IconBtn style=" width: 35px; height: 35px;">
                    <VIcon icon="tabler-arrow-left" />
                </IconBtn>
            </button-->
            <div class="__editor_filename_wrapper tw-ms-[40px]">
                <input
                    class="__editor_filename border"
                    ref="filenameRef"
                    v-model="modelValue.title"
                    :disabled="modelValue.status !== 'draft'"
                />
                <!--small style="display: block">Updated last: {{ formatToDDMMYYYY(modelValue.updated_at) || '' }}</small-->
            </div>

            <div style="display: flex; justify-content: center; align-items: center; height: 100px; margin-left: auto">
                <div
                    v-if="modelValue.status === 'draft'"
                    class="tw-font-bold tw-p-1 tw-rounded-lg d-flex tw-items-center tw-justify-center __stats_strip __status_strip_draft">
                   Draft
                </div>
                <div
                    v-if="['pending_0','pending_50'].includes(modelValue.status)"
                    class="tw-font-bold tw-p-1 tw-rounded-lg d-flex tw-items-center tw-justify-center __stats_strip __status_strip_in_progress">
                    In-Progress
                </div>
                <div
                    v-if="modelValue.status === 'rejected'"
                    class="tw-font-bold tw-p-1 tw-rounded-lg d-flex tw-items-center tw-justify-center __stats_strip __status_strip_rejected">
                    Rejected
                </div>
                <div
                    v-if="modelValue.status === 'completed'"
                    class="tw-font-bold tw-p-1 tw-rounded-lg d-flex tw-items-center tw-justify-center __stats_strip __status_strip_in_completed">
                    Completed
                </div>
                <div
                    v-if="modelValue.status === 'archive'"
                    class="tw-font-bold tw-p-1 tw-rounded-lg d-flex tw-items-center tw-justify-center __stats_strip __status_strip_rejected">
                    Archived
                </div>
            </div>

        </div>

        <div class="__preview_stepper_wrapper d-flex justify-center">
            <WizardStepper
                v-model="currentStepContent"
                :items="steps"
                :currentStep="currentStepContent"
                :selectedView="selectedView"
                :lastValidStep="lastValidStep"
                :selectedEmployee="modelValue"
                @update_currentStepContent="handleUpdateCurrentStepContent"
            />
        </div>

        <VCardText class="disable-tab-transition" style="padding: 0; margin: 0">
            <VWindow v-model="currentStepContent" class="disable-tab-transition">
                <!-- TODO: Redisplay 1 to 2 if needed. -->
                <VWindowItem>
                    <GettingStarted
                        :selectedTemplate="modelValue.model_value.template"
                        :selectedView="selectedView"
                        @templateSelected="handleTemplateSelection"
                    />
                </VWindowItem>
                <VWindowItem>
                    <SelectEmployee
                        :selectedEmployee="modelValue"
                        @employeeSelected="handleEmployeeSelection"
                    />
                </VWindowItem>
                <VWindowItem>
                    <!--SamplePOCTemplate /-->
                    <ContractCustomisationFullPage
                        v-if="selectedView === 'contract'"
                        v-model="modelValue"
                        @form-data="handleCustomisationFormData"
                    />
                    <LetterCustomisationFullPage
                        v-model="modelValue.model_value"
                        v-else
                        @form-data="handleCustomisationFormData"
                    />
                </VWindowItem>
                <VWindowItem>
                    <!-- STEP 3 -->
                    <div class="__preview_page_wrapper" v-if="currentStepContent === 3 && editRowStateFlag !== true">
                        <PreviewTemplateWithData @set-template-page="handleTemplatePage" v-model="modelValue.content" :type="selectedView" :stat="modelValue.status"/>
                    </div>
                    <div v-else-if="currentStepContent === 3 && editRowStateFlag === true">
                        <SammplePOCTemplate />
                    </div>
                </VWindowItem>
                <VWindowItem>
                    <!-- SIGNATURE -->
                    <div class="__preview_page_wrapper" v-if="currentStepContent === 4">
                        <TemplateSignatureLastPage
                            ref="step4Component"
                            v-model="modelValue"
                            @set-signature-page="handleSignaturePage"
                            :type="selectedView"
                            :signatureImage="based64Signature"
                            :isSignedAndSaved="isSignedAndSaved"
                            @handle-signature="handleSignature"
                            @enable-submit="handleEnableSubmit"
                        />
                    </div>
                </VWindowItem>
                <VWindowItem>
                    <!-- COMPLETE -->
                    <div class="__preview_page_wrapper" v-if="(currentStepContent === 5 || currentStepContent === 6) && allPageAsString">
                        <TemplatePendingApproval ref="step5Component" v-model="allPageAsString" :type="selectedView" />
                    </div>
                </VWindowItem>
            </VWindow>
        </VCardText>

        <!-- REJECT BUTTONS SECTION -->
        <VCardActions class="__wizzard_dialog_action_float_bottom justify-end">
            <div style="display: flex; justify-content: center; background-color: white;">
                <VCardActions
                    v-if="modelValue.status === 'pending_50' && currentStepContent === 4 && localStatus !== 'rejected'"
                    class="__wizzard_signature_action justify-center">
                    <VBtn
                        style="height: 30px!important; width: 80px!important;"
                        class="border border-black"
                        color="#CCCCCC"
                        @click="handleReject"
                    >
                        Reject
                    </VBtn>
                    <VBtn
                        style="height: 30px!important; width: 80px!important;"
                        class="__wizard_action_button"
                        color="white"
                        @click="handleSignature()"
                    >
                        Sign
                    </VBtn>
                </VCardActions>
                <VCardActions
                    v-else-if="modelValue.status === 'rejected' || localStatus === 'rejected'"
                    class="__wizzard_signature_action justify-center">
                    <VBtn
                        style="margin: 10px auto;"
                        class="border border-black"
                        color="#FF3030"
                        @click="handleReject"
                    >
                        View Reject Message
                    </VBtn>
                </VCardActions>
            </div>

            <!-- BOTTOM RIGHT SIDE BUTTON SECTIONS -->
            <div style="display: flex; justify-content: flex-end; gap: 8px; padding: 5px 20px">
                <VBtn
                    v-if="isDisplayBackButton()"
                    class="__back_button_right"
                    color="gray"
                    @click="backstep()"
                >
                    <span >Back</span>
                </VBtn>
                <VBtn
                    class="__back_button_right"
                    color="gray"
                    @click="handleCloseThisPage()"
                >
                    Close
                </VBtn>
                <VBtn
                    v-if="isDisplayNextButton()"
                    class="__wizard_action_button"
                    color="white"
                    @click="handleStepSectionPage()"
                >
                    <span>Next</span>
                </VBtn>
                <VBtn
                    v-if="isDisplaySaveButton()"
                    class="__wizard_action_button"
                    color="white"
                    @click="handleSave"
                >
                    <span>Save</span>
                </VBtn>
                <VBtn
                    v-if="modelValue.contract_id === null && modelValue.type === 'letter_of_offer' && modelValue.status === 'completed'"
                    class="__wizard_action_button"
                    color="white"
                    @click="createContractForThisOffer"
                >
                    Create Contract
                </VBtn>
            </div>
        </VCardActions>
    </div>

    <RejectDialogBox
        v-model="showRejectDialog"
        :dialogRejectMessage="modelValue.ceo_reject_message"
        :rejectLocalStatus="localStatus"
        :rejectedLocalMessage="rejectLocalMessage"
        @reject-action="handleRejectCommentDialogBox"/>
    <RejectCommentDialogBox v-model="showRejectCommentDialogBox" :contractId="modelValue.id" @reject-action="handleRejectSuccessDialogBox" :contractType="modelValue.type"/>
    <RejectDialogComplete v-model="showRejectSuccessDialog" :selectedView="selectedView"/>
    <SignatureDialogBox v-model="showSignatureDialogBox" @signature-image="handleSignaturePreview"/>

    <PromptDialogBox
        v-model="showPromptDialogBox"
        :message="promptMessage"
        :promptDialogBoxType="promptDialogBoxType"
        :submessage="promptSubmessage"
        @decision="handlePromptDialogBoxDecision"
    />

    <PromptMessage v-model="showPromptMessage"/>

</template>

<script>
import contractApi from "@/api/contract/contractApi"
import toast from "@/composable/toast"
import WizardStepper from "@/pages/Contract/components/ContractAndOfferWizard/WizardStepper.vue"
import PreviewTemplateWithData from "@/pages/Contract/components/ContractAndOfferWizard/components/PreviewTemplateWithData.vue"
import PromptMessage from "@/pages/Contract/components/ContractAndOfferWizard/components/PrompMessage.vue"
import PromptDialogBox from "@/pages/Contract/components/ContractAndOfferWizard/components/PromptDialogBox.vue"
import RejectCommentDialogBox from "@/pages/Contract/components/ContractAndOfferWizard/components/RejectCommentDialogBox.vue"
import RejectDialogBox from "@/pages/Contract/components/ContractAndOfferWizard/components/RejectDialogBox.vue"
import RejectDialogComplete from "@/pages/Contract/components/ContractAndOfferWizard/components/RejectDialogComplete.vue"
import SamplePOCTemplate from "@/pages/Contract/components/ContractAndOfferWizard/components/SamplePOCTemplate.vue"
import SignatureDialogBox from "@/pages/Contract/components/ContractAndOfferWizard/components/SignatureDialogBox.vue"
import TemplateCompleted from "@/pages/Contract/components/ContractAndOfferWizard/components/TemplateCompleted.vue"
import TemplatePendingApproval from "@/pages/Contract/components/ContractAndOfferWizard/components/TemplatePendingApproval.vue"
import TemplateSignatureLastPage from "@/pages/Contract/components/ContractAndOfferWizard/components/TemplateSignatureLastPage.vue"
import { useAuthStore } from "@/store/authStore"
import { defineComponent } from "vue"
import SammplePOCTemplate from "@/pages/Contract/components/ContractAndOfferWizard/components/SamplePOCTemplate.vue"
import ContractCustomisationFullPage
    from "@/pages/Contract/components/ContractAndOfferWizard/ContractCustomisationFullPage.vue"
import LetterCustomisationFullPage
    from "@/pages/Contract/components/ContractAndOfferWizard/LetterCustomisationFullPage.vue"
import SelectEmployee from "@/pages/Contract/components/ContractAndOfferWizard/SelectEmployee.vue"
import GettingStarted from "@/pages/Contract/components/ContractAndOfferWizard/GettingStarted.vue"

export default defineComponent({
    name: 'ContractOfferOnPageContent',
    components: {
        GettingStarted,
        SelectEmployee,
        LetterCustomisationFullPage, ContractCustomisationFullPage,
        SammplePOCTemplate,
        PromptMessage,
        RejectDialogBox,
        RejectCommentDialogBox,
        PromptDialogBox,
        RejectDialogComplete, SignatureDialogBox,
        TemplateSignatureLastPage,
        SamplePOCTemplate,
        TemplatePendingApproval,
        PreviewTemplateWithData,
        TemplateCompleted, WizardStepper
    },
    props:{
        modelValue: {
            type: null,
            required: true
        },
        editRowStateFlag: {
            type: null,
            required: true
        }
    },
    data() {
        return{
            currentStepContent: 3,
            steps: [
                { title: "Get Started", icon: "" },
                { title: "Select Employee", icon: "" },
                { title: "Contract Customisation", icon: "" },
                { title: "Preview", icon: "" },
                { title: "Signature", icon: "" },
                { title: "Complete", icon: "" },
            ],
            selectedView: 'letter_of_offer',
            htmlContent: null,
            showRejectDialog: false,
            rejectMessage: null,
            showRejectCommentDialogBox: false,
            showRejectSuccessDialog: false,
            showSignatureDialogBox: false,
            based64Signature: null,
            signStatus: 0,

            showPromptDialogBox: false,
            promptMessage: null,
            promptDialogBoxType: null,
            promptSubmessage: null,
            loading: false,

            allPageAsString: null,
            signaturePage: null,
            localStatus: null,
            rejectLocalMessage: null,

            showPromptMessage: false,
            lastValidStep: 3,
            isSignedAndSaved: false,
        }
    },
    beforeMount() {
      if(this.modelValue.status === 'completed'){
          this.currentStepContent = 5
          this.allPageAsString = this.modelValue.content
      }

      this.localStatus = this.modelValue.status
      this.lastValidStep = this.currentStepContent
    },
    mounted() {
        if (!this.modelValue?.model_value?.template) {
            alert("This is an old test data that is no longer compatible with the current updates. Please create new.")
            this.$emit('closeContract')
        }
    },
    methods: {
        isDisplayBackButton() {
            //currentStepContent === 4 || (currentStepContent < 6 && currentStepContent !== 0)
            if(this.currentStepContent !== 0){
                return true
            }

            return false
        },
        isDisplaySaveButton() {
            //in signature step and signed and not yet saved as signed
            if(this.currentStepContent === 4 && this.based64Signature !== null && this.isSignedAndSaved !== true) {
                return true
            }

            if(['draft'].includes(this.modelValue.status) && this.currentStepContent === this.lastValidStep) {
                return true
            }

            return false
        },
        isDisplayNextButton() {
            //draft and in preview
            if(['draft'].includes(this.modelValue.status) && this.currentStepContent <= 2) {
                return true
            }

            //pending 0 or 50
            if(['pending_0', 'pending_50'].includes(this.modelValue.status) && this.currentStepContent <= 3) {
                return true
            }

            //pending 0 or 50 and signed and saved
            if(['pending_0', 'pending_50'].includes(this.modelValue.status) && this.currentStepContent < 5 && this.isSignedAndSaved){
                return true
            }

            //
            if(['completed'].includes(this.modelValue.status) && this.currentStepContent < 5 ){
                return true
            }

            return false
        },
        handleCustomisationFormData(formData, isComplete) {
            console.log("this.modelValue - handleCustomisationFormData")
            console.log(this.modelValue)
            this.modelValue.customisation = formData
            this.isCustomisationDataComplete = isComplete
            //console.log(this.modelValue.customisation)
            //this.formData.customisation = formData
            //this.buildPreview()
        },
        handleUpdateCurrentStepContent(step) {
            if(step <= this.lastValidStep){
                this.currentStepContent  = step
            }else{
                alert(step)
            }
        },
        handleRejectCommentDialogBox (){
            this.showRejectDialog = false
            this.showRejectCommentDialogBox = true
            //this.localStatus = 'rejected'
            //this.modelValue.status = this.localStatus
        },
        handleReject () {
            this.showRejectDialog = true
        },
        handleRejectSuccessDialogBox(message) {
            this.showRejectCommentDialogBox = false
            this.showRejectSuccessDialog = true
            this.rejectLocalMessage = message

            this.localStatus = 'rejected'
            this.modelValue.status = this.localStatus
        },
        createContractForThisOffer() {
            this.$emit('initCreateContractForOffer', this.modelValue.contract_id, this.modelValue.id)
        },
        formatToDDMMYYYY(dateString) {
            const date = new Date(dateString);
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are 0-indexed
            const year = date.getFullYear();

            return `${day}-${month}-${year}`;
        },
        handleSave() {
            this.showPromptDialogBox = true
            this.promptDialogBoxType = 'save_dialog_type'
            this.promptMessage = "Submit"
            this.promptSubmessage = "Are you sure you want to submit?"
        },
        handleTemplatePage (htmlDataTemplatePage){

        },
        async handlePromptDialogBoxDecision(decision) {
            if(decision) {
                this.loading = true
                const formData = new FormData()
                formData.append('id', this.modelValue.id)
                formData.append('type', this.modelValue.type)

                //related to history logging
                formData.append('updated_by_id', useAuthStore().userData.id)
                formData.append('updated_by_name', useAuthStore().userData.name)

                if(['draft', 'rejected'].includes(this.modelValue.status)){
                    formData.append('status', 'pending_0')
                    formData.append('assignee_user_id', this.modelValue.owner_user_id)
                    formData.append('content', this.modelValue.content)
                    formData.append('title', this.modelValue.title)

                    //History logging for Reject
                    if(this.modelValue.status === 'rejected') {
                        formData.append('action', 'rejected')
                        formData.append('history_status', 'Signed')
                    }else{
                        formData.append('action', 'updated')
                        formData.append('history_status', 'Edited')
                    }
                }

                if(this.modelValue.status === 'pending_0'){
                    formData.append('status', 'pending_50')
                    //formData.append('content', this.allPageAsString) //applicant is not editor
                    formData.append('signature_page', this.signaturePage)
                    formData.append('assignee_user_id', this.modelValue.created_by_user_id)
                    formData.append('employee_signature', 'embeded')
                    formData.append('employee_signature_date', this.getDateNowMySQLFormat())

                    //History logging for pending_0
                    formData.append('action', 'signed')
                    formData.append('history_status', 'Signed')

                }else if(this.modelValue.status === 'pending_50') {
                    formData.append('status', 'completed')
                    formData.append('assignee_user_id', this.modelValue.created_by_user_id)
                    formData.append('ceo_signature', 'embeded')
                    formData.append('content', this.allPageAsString)
                    formData.append('signature_page', this.signaturePage)
                    formData.append('ceo_signature_date', this.getDateNowMySQLFormat())

                    //History logging for pending_50
                    formData.append('action', 'signed')
                    formData.append('history_status', 'Signed')
                }

                const response = await contractApi.updateContract(formData)
                toast('success', response.message)
                this.loading = false

                if(this.modelValue.status === 'pending_0'){
                    this.currentStepContent +=1
                    this.showPromptMessage = true
                }else  if(this.modelValue.status === 'pending_50'){
                    this.currentStepContent +=1
                    this.modelValue.status = 'completed'
                }else if(this.modelValue.status === 'draft'){
                    this.$emit('closeContract')
                }
                this.lastValidStep += 1
                this.isSignedAndSaved = true
            }
        },
        getDateNowMySQLFormat() {
            const today = new Date();
            const dd = String(today.getDate()).padStart(2, '0');
            const mm = String(today.getMonth() + 1).padStart(2, '0'); // Month is 0-indexed
            const yyyy = today.getFullYear();

            return `${yyyy}-${mm}-${dd}`;
        },
        handleEnableSubmit() {
            this.signStatus = 1
        },
        handleSignaturePage (htmlDataSignaturePage) {
            this.signaturePage = htmlDataSignaturePage
            this.allPageAsString = this.modelValue.content + htmlDataSignaturePage;
        },
        handleSignaturePreview(based64Image) {
            this.based64Signature = based64Image
        },
        handleBackwardStep() {
            this.htmlContent = this.modelValue.template.content;
        },
        handleCloseThisPage() {
            this.$emit('closeContract')
            //this.showPromptDialogBox = true
            //this.promptDialogBoxType = 'cancel_contract_offer_creation'
            //this.promptMessage = "Are you sure you want to cancel?"
        },
        handleStepSectionPage() {
            this.currentStepContent +=1
            if(this.currentStepContent > this.lastValidStep){
                this.lastValidStep += 1
            }
        },
        backstep() {
            this.currentStepContent -=1
        },
        handleSignature() {
            this.showSignatureDialogBox = true
        },
    }
})
</script>

<style scoped>

.__editor_header_wrapper {
    display: flex;
}
.__editor_back_button {
    border-radius:6px;
    margin: 20px 20px;
    color: #858587;
    background-color: #dfe0e3;
}
.__editor_filename_wrapper {
    display: block;
    height: 50px;
    align-self: center;
    max-width: 400px;
}

.__editor_filename_wrapper small {
    color: #808494;
}

.__editor_filename {
    height: 30px!important;
    font-size: 1.0em!important;
    font-weight: bold!important;
    color: #3C395C!important;
    padding: 0 10px!important;
    border-radius: 5px!important;
    width: 280px!important;
}
.__preview_stepper_wrapper {
    background-color: #F5F5F5;
    padding: 5px 10px;
    justify-content: center; /* vertical ^v */
}
.__preview_page_wrapper {
    height: calc(100vh - 200px);
    background-color: #d3d3d3;
    display: flex;
    justify-content: center;  /* center horizontally */
    overflow-y: scroll;
    padding-bottom: 0!important;
}

.__wizzard_dialog_action_float_bottom  {
    background-color: #EBECEF;
    padding: 0;

    position: fixed; /* Stick to the screen */
    bottom: 0;       /* Stick to bottom */
    left: 0;
    width: 100%;     /* Full width */
    z-index: 1;   /* Float on top of other elements */
    box-shadow: 0 -2px 6px rgba(0,0,0,0.1); /* Optional: subtle shadow */
    flex-direction: column; /* Stack children vertically */
    align-items: stretch; /* Allow individual children to control their own alignment */
}

.__wizzard_dialog_action_float_bottom_signature  {
    background-color: white;
    padding: 5px 12px 5px 150px;

    position: fixed; /* Stick to the screen */
    bottom: 80px;       /* Stick to bottom */
    left: 0;
    width: 100%;     /* Full width */
    z-index: 1;   /* Float on top of other elements */
    box-shadow: 0 -2px 6px rgba(0,0,0,0.1); /* Optional: subtle shadow */
}

.__back_button_right {
    border-radius:6px;
    margin: 15px 10px;
    padding: 0 15px!important;
    color: #858587;
    background-color: #dfe0e3;
}

.__wizard_action_button {
    background-color: #E35205;
    padding: 0 20px!important;
    margin: 15px 10px;
}

.__message {
    display: grid;
    place-items: center;
    height: 100px;
}
.__stats_strip {
    height: 30px;
    margin: 0 300px;
    padding: 20px 30px;
}

.__status_strip_draft {
    background-color: #F2F2F5;
    color: #808097;
}
.__status_strip_in_progress {
    background-color: #FDE1D0;
    color: #F82D03;
}
.__status_strip_rejected {
    background-color: #FEE2E2;
    color: red;
}

.__status_strip_in_completed {
    background-color: #DCFCE7;
    color: #22C55E;
}

.__wizzard_signature_action {
    background-color: #FCFCFC;
    padding: 0;
    margin-left: 200px!important;
}
</style>
