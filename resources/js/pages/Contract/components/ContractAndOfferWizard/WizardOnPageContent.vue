<template>
    <div class="__non-printable">
        <div class="__editor_header_wrapper">
            <!--Button - Return Button left side -->
            <button class="__editor_back_button" @click="backstep" :disabled="this.currentStepContent === 3" :style="{ visibility: currentStepContent === 3 ? 'hidden' : 'visible' }">
                <IconBtn style=" width: 35px; height: 35px;">
                    <VIcon icon="tabler-arrow-left" />
                </IconBtn>
            </button>
            <div class="__editor_filename_wrapper">
                <input
                    class="__editor_filename border"
                    ref="filenameRef"
                    v-model="fileName"
                />
                <small style="display: block">Updated {{timeInitialized}} today </small>
            </div>
        </div>

        <div class="__preview_stepper_wrapper d-flex justify-center">
            <WizardStepper
                v-model="currentStepContent"
                :items="stepsContent"
                :currentStep="currentStepContent"
                :selectedView="selectedView"
                :lastValidStep="lastValidStep"
                :customisationDataComplete="isCustomisationDataComplete"
                :selectedEmployee="modelValue.employee.employeeData"
                @backwardStep="handleBackwardStep"
                @update_currentStepContent="handleUpdateCurrentStepContent"
            />
        </div>

        <VCardText class="disable-tab-transition" style="padding: 0; margin: 0">
            <VWindow v-model="currentStepContent" class="disable-tab-transition">
                <!-- TODO: Redisplay 1 to 2 if needed. -->
                <VWindowItem>
                    <!-- -->
                    <GettingStarted
                        :selectedTemplate="modelValue.template"
                        :selectedView="selectedView"
                        @templateSelected="handleTemplateSelection"
                    />
                </VWindowItem>
                <VWindowItem>
                    <SelectEmployee
                        :selectedEmployee="modelValue.employee.employeeData"
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
                        v-model="modelValue"
                        v-else
                        @form-data="handleCustomisationFormData"
                    />
                </VWindowItem>
                <VWindowItem>
                    <!-- PREVIEW -->
                    <div class="__preview_page_wrapper" v-if="currentStepContent === 3" style="height: calc(100vh - 200px);">
                        <PreviewTemplateWithData @set-template-page="handleTemplatePage" v-model="processedContent" :type="selectedView"/>
                    </div>
                </VWindowItem>
                <VWindowItem>
                    <!-- SIGNATURE -->
                    <div class="__preview_page_wrapper" v-if="currentStepContent === 4">
                        <TemplateSignatureLastPage ref="step4Component" @set-signature-page="handleSignaturePage" :type="selectedView" :signatureImage="based64Signature"/>
                    </div>

                </VWindowItem>
                <VWindowItem>
                    <!-- PENDING APPROVAL -->
                    <div class="__preview_page_wrapper" v-if="currentStepContent === 5  && allPageAsString">
                        <TemplatePendingApproval ref="step5Component" v-model="allPageAsString" :type="selectedView" @create-contract-letter="handleCreateContractLetter"/>
                    </div>
                </VWindowItem>
                <VWindowItem>
                    <!-- COMPLETE -->
                    <div class="__preview_page_wrapper" v-if="currentStepContent === 6">
                        <TemplateCompleted ref="step6Component" v-model="completed" :submitType="submitType" />
                    </div>
                </VWindowItem>
            </VWindow>
        </VCardText>

        <VCardActions
            v-if="isSuperUser && currentStepContent === 4 && false"
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

        <VCardActions class="__wizzard_dialog_action_float_bottom justify-end" style="padding: 5px 20px;">
            <VBtn
                v-if="currentStepContent === 4"
                class="__back_button_right"
                color="gray"
                @click="backstep"
            >
                <span >Back</span>
            </VBtn>
            <VBtn
                class="__back_button_right"
                color="gray"
                @click="handleSaveAsDraft"
            >
                Save as Draft
            </VBtn>
            <VBtn
                class="__back_button_right"
                color="gray"
                @click="cancelContractOfferCreation"
            >
                Cancel
            </VBtn>
            <VBtn
                v-if="nextButtonCounter === 3 && false"
                class="__wizard_action_button"
                color="white"
                @click="handleStepSectionPage"
            >
                Next
                <!--span  v-if="currentStepContent === 5  && allPageAsString && false">Create Contract</span-->
                <!--span  v-else>Next</span-->
            </VBtn>
        </VCardActions>
    </div>

    <div class="__print_preview" style="display: none;">
        <div
            v-if="currentStepContent === 3"
            class="__preview_page __richtext-preview">
            <div class="__html_page" v-html="processedContent"></div>
        </div>
        <TemplateSignatureLastPage
            v-if="currentStepContent === 4"
            v-model="processedContent"
            :type="selectedView"
        />
    </div>

    <RejectDialogBox v-model="showRejectDialog" @reject-action="handleRejectCommentDialogBox"/>
    <RejectCommentDialogBox v-model="showRejectCommentDialogBox" @reject-action="handleRejectSuccessDialogBox"/>
    <RejectDialogComplete v-model="showRejectSuccessDialog" :selectedView="selectedView"/>
    <SignatureDialogBox v-model="showSignatureDialogBox" @signature-image="handleSignaturePreview"/>
    <PromptDialogBox v-model="showPromptDialogBox" :message="promptMessage" :promptDialogBoxType="promptDialogBoxType" @decision="handlePromptDialogBoxDecision"/>
</template>

<script>
import contractApi from "@/api/contract/contractApi"
import toast from "@/composable/toast"
import WizardStepper from "@/pages/Contract/components/ContractAndOfferWizard/WizardStepper.vue"
import PreviewTemplateWithData from "@/pages/Contract/components/ContractAndOfferWizard/components/PreviewTemplateWithData.vue"
import PromptDialogBox from "@/pages/Contract/components/ContractAndOfferWizard/components/PromptDialogBox.vue"
import RejectCommentDialogBox from "@/pages/Contract/components/ContractAndOfferWizard/components/RejectCommentDialogBox.vue"
import RejectDialogBox from "@/pages/Contract/components/ContractAndOfferWizard/components/RejectDialogBox.vue"
import RejectDialogComplete from "@/pages/Contract/components/ContractAndOfferWizard/components/RejectDialogComplete.vue"
import SammplePOCTemplate from "@/pages/Contract/components/ContractAndOfferWizard/components/SamplePOCTemplate.vue"
import SignatureDialogBox from "@/pages/Contract/components/ContractAndOfferWizard/components/SignatureDialogBox.vue"
import TemplateCompleted from "@/pages/Contract/components/ContractAndOfferWizard/components/TemplateCompleted.vue"
import TemplatePendingApproval from "@/pages/Contract/components/ContractAndOfferWizard/components/TemplatePendingApproval.vue"
import TemplateSignatureLastPage from "@/pages/Contract/components/ContractAndOfferWizard/components/TemplateSignatureLastPage.vue"
import { useAuthStore } from "@/store/authStore"
import { defineComponent } from "vue"
import allowanceApi from "@/api/allowance/allowanceApi"
import LetterCustomisation from "@/pages/Contract/components/ContractAndOfferWizard/LetterCustomisation.vue"
import ContractCustomisation from "@/pages/Contract/components/ContractAndOfferWizard/ContractCustomisation.vue"
import LetterCustomisationFullPage
    from "@/pages/Contract/components/ContractAndOfferWizard/LetterCustomisationFullPage.vue"
import ContractCustomisationFullPage
    from "@/pages/Contract/components/ContractAndOfferWizard/ContractCustomisationFullPage.vue"
import SelectEmployee from "@/pages/Contract/components/ContractAndOfferWizard/SelectEmployee.vue"
import GettingStarted from "@/pages/Contract/components/ContractAndOfferWizard/GettingStarted.vue"

export default defineComponent({
    name: 'WizardOnPageContent',
    components: {
        GettingStarted,
        SelectEmployee,
        ContractCustomisationFullPage,
        LetterCustomisationFullPage,
        ContractCustomisation,
        LetterCustomisation, PromptDialogBox, SignatureDialogBox, TemplateCompleted, SammplePOCTemplate, TemplatePendingApproval, PreviewTemplateWithData, RejectDialogComplete, RejectCommentDialogBox, RejectDialogBox, TemplateSignatureLastPage, WizardStepper },
    props: {
        currentStep: {
            type: Number,
            required: true,
        },
        steps: {
            type: Array,
            required: true,
        },
        selectedView: {
            type: String,
            required: false,
        },
        modelValue: {
            type: [Object, String, Number, Array, Boolean],
            required: true
        }
    },
    data() {
        return {
            loading: false,
            htmlContent: null,
            htmlSignaturePageContent: null,
            processedContent: '',
            showRejectDialog: false,
            showRejectCommentDialogBox: false,
            showRejectSuccessDialog: false,
            showSignatureDialogBox: false,
            allowancesStaticData: [
                { id: 1, name: "Sample Allowance 1" },
                { id: 2, name: "Sample Allowance 2" },
                { id: 3, name: "Sample Allowance 3" },
                { id: 4, name: "Sample Allowance 4" },
                { id: 5, name: "Sample Allowance 5" },
                { id: 6, name: "Sample Allowance 6" },
                { id: 7, name: "Sample Allowance 7" },
            ],
            currentStepContent: this.currentStep,
            stepsContent: this.steps,
            allPages: {
                templatePage: null,
                signaturePage: null,
            },
            allPageAsString: null,
            timeInitialized: null,

            ceoSignatureTemp: null,
            fileName: null,
            form: {
                type: null,
                customData: null,
                modelValue: null,
                template: null,
                content: null,
                fullPageContent: null,
                title: null,
                ownerEmployeeId: null,
                ownerUserId: null,
                createdByUserId: null,
                assigneeUserId: null,
                status: null,
                expiryDate: null,
                locationId: null,
                employmentTypeId: null,
                employeeSignature: null,
                employeeSignatureDate: null,
                ceoSignature: null,
                ceoSignatureDate: null,
            },
            completed: false,
            based64Signature: null,
            showPromptDialogBox: false,
            promptMessage: null,
            promptDialogBoxType: null,
            submitType: null,

            nextButtonCounter: 3,
            allowances: null,
            lastValidStep: 3,
            isCustomisationDataComplete: false
        }
    },
    created() {
        this.currentStepContent = this.currentStep
        this.stepsContent = this.steps

        //this.allowances = this.getAllowances()
        this.buildPreview()
    },
    async mounted() {
        this.setFromTemplate(this.modelValue.template.content)
        this.setDefaultValues()
        this.isCustomisationDataComplete = true
        console.log("this.modelValue.employee.employeeData")
        console.log(this.modelValue.employee.employeeData)
    },
    watch: {
        completed(newVal, oldVal) {
            if(newVal === false) {
                this.$emit('close-wizard');
            }
        }
    },
    methods: {
        handleEmployeeSelection(employee) {
            this.modelValue.employee.employeeData = employee
        },
        handleCustomisationFormData(formData, isComplete) {
            console.log("this.modelValue - handleCustomisationFormData")
            console.log(this.modelValue)
            this.modelValue.customisation = formData
            this.isCustomisationDataComplete = isComplete
            //console.log(this.modelValue.customisation)
            //this.formData.customisation = formData
            this.buildPreview()
        },
        handleUpdateCurrentStepContent(step) {
            if(step <= this.lastValidStep){
                this.currentStepContent  = step
            }else{
                alert(step)
            }
        },
        handleSaveAsDraft() {
            this.showPromptDialogBox = true
            this.promptDialogBoxType = 'save_as_draft_contract_offer_creation'
            this.promptMessage = "Are you sure you want to save as DRAFT?"
        },
        async handlePromptDialogBoxDecision(decision) {
            if(decision && this.promptDialogBoxType === 'cancel_contract_offer_creation') {
                this.$emit('close-wizard', false)
            }

            if(decision && this.promptDialogBoxType === 'save_as_draft_contract_offer_creation') {
                //save contract plus signature page, template , data, and status

                this.submitType = 'draft'
                await this.populateFormData()
                console.log("+++ FORM DATA +++ ")
                await this.createContract()
                this.completed = true
                this.$emit('close-wizard');
            }
        },
        cancelContractOfferCreation() {
            this.showPromptDialogBox = true
            this.promptDialogBoxType = 'cancel_contract_offer_creation'
            this.promptMessage = "Are you sure you want to cancel?"
        },
        handleSignaturePreview(based64Image) {
            this.based64Signature = based64Image
        },
        handleSignature() {
            this.showSignatureDialogBox = true
        },
        setDefaultValues(){
            this.fileName = this.getDefaultFilename()
            this.timeInitialized = this.getTimeNow()
        },
        getDefaultFilename(){
            return this.selectedView + ': ' + this.modelValue.employee?.firstName +', '+this.modelValue.employee?.lastName
        },
        getTimeNow() {
            const now = new Date()
            let hours = now.getHours()
            let minutes = now.getMinutes()
            let seconds = now.getSeconds()
            const ampm = hours >= 12 ? 'PM' : 'AM'

            // Format to always be 2 digits
            hours = hours.toString().padStart(2, '0')
            minutes = minutes.toString().padStart(2, '0')
            //seconds = seconds.toString().padStart(2, '0')

            //return `${hours}:${minutes}:${seconds} ${ampm}`
            return `${hours}:${minutes} ${ampm}`
        },
        getDateNowMysqlReady() {
            //TODO: Note that mysql ready date is year-month-day time
            const now = new Date();
            return `${now.getFullYear()}-${now.getMonth() + 1}-${now.getDate()}`
        },
        setFromTemplate(templateContent) {
            this.form.template = templateContent
        },
        setFormCustomData(customData) {
            this.form.customData = customData
        },
        setFormFullPageContent(fullPageContent) {
            this.form.fullPageContent = fullPageContent
        },
        getWantedPlaceholders(templatesContent) {
            const textarea = document.createElement('textarea');
            textarea.innerHTML = templatesContent

            const matches = textarea.value.match(/<<([^<>]+)>>/g);
            const placeholderKeys = matches ? matches.map(m => m.replace(/<</, '').replace(/>>/, '')) : [];

            return placeholderKeys
        },
        handleCreateContractLetter() {
            this.setFormFullPageContent(this.allPageAsString)
        },
        isSuperUser(){
            return useAuthStore().userData.role.id === 1
        },
        handleTemplatePage (htmlDataTemplatePage){
            this.allPages.templatePage = htmlDataTemplatePage
            this.form.content = htmlDataTemplatePage
        },
        async handleSignaturePage (htmlDataSignaturePage) {
            //TODO: Set the All Pages as string template (done)
            const signaturePage = await htmlDataSignaturePage;

            this.allPages.signaturePage = signaturePage;
            this.allPageAsString = this.allPages.templatePage + signaturePage;
        },
        handleRejectSuccessDialogBox(rejectMessage) {
            this.showRejectCommentDialogBox = false
            this.showRejectSuccessDialog = true
        },
        handleRejectCommentDialogBox (){
            this.showRejectDialog = false
            this.showRejectCommentDialogBox = true
        },
        handleReject () {
            this.showRejectDialog = true
        },
        backstep () {
            if(this.currentStepContent !== 3) {
                this.$emit('currentStep', this.currentStep-1)
                this.currentStepContent -=1
            }/*else{
                //alert("TECH DEBT: Reimplement the prev steps in the modal.")
            }*/
        },
        populateFormData() {
            this.form.type = this.selectedView ?? 'letter_of_offer'
            this.form.title = this.fileName ?? this.getDefaultFilename()
            this.form.content = this.processedContent //this.allPageAsString
            this.form.modelValue = this.modelValue
            this.form.ownerEmployeeId = this.getEmployeeId()
            this.form.ownerUserId = this.getEmployeeUserId()
            this.form.createdByUserId = this.getCurrentUserId()
            this.form.assigneeUserId = this.getCurrentUserId()
            this.form.status = this.getInitialStatus(this.ceoSignatureTemp)
            this.form.expiryDate = this.getExpiryDate()
            this.form.locationId = this.getEmployeeLocationId()
            this.form.employmentTypeId = this.getEmploymentTypeId()
            this.form.ceoSignature = this.ceoSignatureTemp ?? null
            this.form.ceoSignatureDate = this.ceoSignatureTemp !== null ? this.getDateNowMysqlReady() : null
            this.form.templatethis = this.allPageAsString //to prevent issue with reserved word
        },
        getEmploymentTypeId() {
            return this.modelValue.customisation.employmentBasis.id
        },
        getEmployeeUserId() {
            return this.modelValue.employee.employeeData.user ? this.modelValue.employee.employeeData.user.id : this.modelValue.employee.employeeData.employeeData.user.id
        },
        getEmployeeId() {
            return this.modelValue.employee.employeeData.id ?? this.modelValue.employee.employeeData.employeeData.id
        },
        getCurrentUserId() {
            return useAuthStore().userData.id
        },
        getInitialStatus(ceoSignature) {
            return this.based64Signature !== null
                ? this.getStatusSignedByCeo()
                : 'draft';
        },
        getStatusSignedByCeo() {
            return 'pending_50';
        },
        formatRawDate(rawDate){
            const date = new Date(rawDate);

            // Format to: YYYY-MM-DD
            return `${date.getFullYear()}-${(date.getMonth()+1).toString().padStart(2, '0')}-${date.getDate().toString().padStart(2, '0')}`;
        },
        getExpiryDate() {
            const endDate = this.modelValue.customisation.endDate ?? null
            return endDate ? this.formatRawDate(endDate) : null
        },
        getEmployeeLocationId() {
            return this.modelValue.employee.employeeData.location_id ?? null
        },
        async handleStepSectionPage() {
            this.nextButtonCounter += 1
        },
        async handleStepSectionPagessss() {
            this.$emit('currentStep', this.currentStep+1)
            this.currentStepContent += 1;

            if(this.currentStepContent === 6){
                this.populateFormData()
                console.log("+++ FORM DATA +++")
                await this.createContract()

                this.completed = true
            }
        },
        /*
        appendFormData(formData, data, parentKey = '') {
            if (Array.isArray(data)) {
                // handle arrays
                data.forEach((item, index) => {
                    const fullKey = `${parentKey}[${index}]`
                    this.appendFormData(formData, item, fullKey)
                })
            } else if (data && typeof data === 'object' && !(data instanceof File)) {
                // handle objects
                Object.entries(data).forEach(([key, val]) => {
                    const fullKey = parentKey ? `${parentKey}[${key}]` : key
                    this.appendFormData(formData, val, fullKey)
                })
            } else {
                // handle primitive values (string, number, null, File, etc.)
                formData.append(parentKey, data == null ? '' : data)
            }
        },
        getTransformedFormData(){
            const formData = new FormData()

            const fieldMap = {
                type: 'type',
                title: 'title',
                content: 'content',
                template: 'template',
                customData: 'custom_data',
                modelValue: 'model_value',
                ownerEmployeeId: 'owner_employee_id',
                ownerUserId: 'owner_user_id',
                createdByUserId: 'created_by_user_id',
                assigneeUserId: 'assignee_user_id',
                status: 'status',
                expiryDate: 'expiry_date',
                locationId: 'location_id',
                employmentTypeId: 'employment_type_id',
                employeeSignature: 'employee_signature',
                employeeSignatureDate: 'employee_signature_date',
                ceoSignature: 'ceo_signature',
                ceoSignatureDate: 'ceo_signature_date',
            };
            console.log("this.form")
            console.log(this.form)
            Object.entries(fieldMap).forEach(([formKey, formDataKey]) => {
                const value = this.form[formKey];

                if (value !== null) {
                    if (formKey === 'customData' && typeof value === 'object') {
                        // ✅ send each key of customData as form field
                        Object.entries(value).forEach(([key, val]) => {
                            formData.append(`custom_data[${key}]`, val);
                        });
                    } else if (formKey === 'modelValue' && typeof value === 'object') {
                        this.appendFormData(formData, value, 'model_value') // ✅ handles multi-dimensional
                    }
                    else {
                        formData.append(formDataKey, value);
                    }
                }
            });
            console.log("formData")
            console.log(formData)
            return formData
        },
        */

        appendFormData(formData, data, parentKey = '') {
            if (Array.isArray(data)) {
                data.forEach((item, index) => {
                    const fullKey = `${parentKey}[${index}]`
                    this.appendFormData(formData, item, fullKey)
                })
            }
            else if (data instanceof Date) {
                formData.append(parentKey, data.toISOString())
            }
            else if (data && typeof data === 'object' && !(data instanceof File) && !(data instanceof Blob)) {
                Object.entries(data).forEach(([key, val]) => {
                    const fullKey = parentKey ? `${parentKey}[${key}]` : key
                    this.appendFormData(formData, val, fullKey)
                })
            }
            else {
                formData.append(parentKey, data == null ? '' : data)
            }
        },

        getTransformedFormData(){
            const formData = new FormData()

            const fieldMap = {
                type: 'type',
                title: 'title',
                content: 'content',
                template: 'template',
                customData: 'custom_data',
                modelValue: 'model_value',
                ownerEmployeeId: 'owner_employee_id',
                ownerUserId: 'owner_user_id',
                createdByUserId: 'created_by_user_id',
                assigneeUserId: 'assignee_user_id',
                status: 'status',
                startDate: 'start_date', // ✅ FIX: added missing mapping
                expiryDate: 'expiry_date',
                locationId: 'location_id',
                employmentTypeId: 'employment_type_id',
                employeeSignature: 'employee_signature',
                employeeSignatureDate: 'employee_signature_date',
                ceoSignature: 'ceo_signature',
                ceoSignatureDate: 'ceo_signature_date',
            };

            console.log("this.form")
            console.log(this.form)

            Object.entries(fieldMap).forEach(([formKey, formDataKey]) => {
                const value = this.form[formKey];

                if (value !== null) {
                    if (formKey === 'customData' && typeof value === 'object') {
                        // ✅ send each key of customData as form field
                        Object.entries(value).forEach(([key, val]) => {
                            formData.append(`custom_data[${key}]`, val);
                        });
                    } else if (formKey === 'modelValue' && typeof value === 'object') {
                        this.appendFormData(formData, value, 'model_value') // ✅ handles multi-dimensional
                    }
                    else {
                        formData.append(formDataKey, value);
                    }
                }
            });

            console.log("formData")
            console.log(formData)
            return formData
        },


        async createContract() {
            try {
                this.loading = true

                const response = await contractApi.createContract(this.getTransformedFormData())
                console.log("FINAL RESPONSE")
                console.log(response)
                toast('success', response.message)
                this.loading = false

            } catch (error) {
                console.error(error)
                this.loading = false
                if (error.response && error.response.status === 422) {
                    const validationErrors = error.response.data.errors

                    Object.values(validationErrors).forEach(errorMessages => {
                        errorMessages.forEach(message => {
                            toast('error', message)
                        })
                    })
                } else {
                    toast('error', 'An unexpected error occurred. Please try again.')
                    console.error(error)
                }
            }
        },
        decodeHtmlEntities(encodedStr) {
            const txt = document.createElement("textarea");
            txt.innerHTML = encodedStr;
            return txt.value;
        },
        handleBackwardStep() {
            this.htmlContent = this.modelValue.template.content;
        },
        buildPreview() {
            const decodedContent = this.decodeHtmlEntities(this.modelValue.template.content);

            this.processedContent = this.replacePlaceholders(
                decodedContent,
                this.modelValue
            );

            console.log("BUILD")
            console.log(this.processedContent)
        },
        formatCurrency(amount) {
            if (typeof amount !== 'number') {
                return isNaN(amount)? amount : new Intl.NumberFormat('en-US', {
                    style: 'currency',
                    currency: 'USD',
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(amount);
            }

            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(amount);
        },
        async getAllowances() {
            try {
                this.loading = true
                const allowances = this.modelValue.customisation.allowances
                const formData = new FormData()
                formData.append('idList', allowances)

                const response = await allowanceApi.fetchAllowancesPackage(formData)

                let allAllowances = ''
                response.forEach((allowance) => {
                    allAllowances += `${allowance.name}: ${allowance.value} <br>`
                })
                this.loading = false
                return allAllowances
            } catch (error) {
                console.error('Error fetching allowances:', error)
                return '' // fallback to empty if something goes wrong
            }
        },
        arrayToStringList(items) {
            let result = ''

            items.forEach((item, index) => {
                result = result + item.name + '<br>'
            });

            return result
        },
        replacePlaceholders(templateContent, data) {
            console.log("+++++++++++++++++++++++++++++++++++++++++++++++++++++++")
            console.log(data)
            const now = new Date();
            const nowDate = `${now.getDate()}-${now.getMonth() + 1}-${now.getFullYear()}`
            const date = data.customisation.startDate
            const StartDate = `${date.getDate() + 1}-${date.getMonth()}-${date.getFullYear()}`

            // TODO: Define all keys and where to get the values. Reference for the Key is the Template Editor placeholders

            const replacements = {
                //Allowances: data.customisation.allAllowances ?? '&lt;&lt;Allowances&gt;&gt;',
                Allowances: data.customisation.allowances ? this.arrayToStringList(data.customisation.allowances) : '&lt;&lt;Allowances&gt;&gt;',
                ApplicantAddress: data.employee?.employeeData.user.employee.address ?? '&lt;&lt;ApplicantAddress&gt;&gt;',
                ApplicantEmail: data.employee?.employeeData.user.employee.personal_email ?? '&lt;&lt;ApplicantEmail&gt;&gt;',
                ApplicantFirstName: data.employee.firstName ?? '&lt;&lt;ApplicantFirstName&gt;&gt;',
                ApplicantName: (data.employee?.firstName ?? '') + ' ' + (data.employee?.lastName ?? ''),

                ImmediateManager: (data.employee?.employeeData.manager?.first_name ?? '') + ' ' +  (data.employee?.employeeData.manager?.last_name ?? ''),
                ImmediateManagerPosition: data.employee?.employeeData.manager?.job_title ?? '&lt;&lt;ImmediateManagerPosition&gt;&gt;',

                JobTitle: data.customisation.positionTitle.name ?? '&lt;&lt;JobTitle&gt;&gt;',

                KWHBEmail: 'hr@kwhb.com.au' ?? '&lt;&lt;KWHBEmail&gt;&gt;',
                KWHBName: 'Katherine West Health Board' ?? '&lt;&lt;KWHBName&gt;&gt;',
                KWHBPhone: '+61 8 8971 9300' ?? '&lt;&lt;KWHBPhone&gt;&gt;',

                LetterDate: nowDate ?? '&lt;&lt;LetterDate&gt;&gt;',
                Location: data.employee?.employeeData.location.name ?? '&lt;&lt;Location&gt;&gt;',

                SalaryAmount: this.formatCurrency(data.customisation.salary) ?? '&lt;&lt;SalaryAmount&gt;&gt;',
                StartDate: StartDate ?? '&lt;&lt;StartDate&gt;&gt;',
                Vaccinations: data.customisation.vaccinations? this.arrayToStringList(data.customisation.vaccinations) : '&lt;&lt;Vaccinations&gt;&gt;',
                Subsidies: data.customisation.subsidies? this.arrayToStringList(data.customisation.subsidies) : '&lt;&lt;Subsidies&gt;&gt;',
                Compliance: data.customisation.certifications ? this.arrayToStringList(data.customisation.certifications) : '&lt;&lt;Compliance&gt;&gt;',
                HoursPerWeek: data.customisation.employmentBasis.hours_per_week ?? '&lt;&lt;HoursPerWeek&gt;&gt;',
                //TODO: TECH DEBT - define all keys and data. Get keys from Template editor/ckeditor
            };

            //Gets the placeholders from template table, content field
            const wantedPlaceholders = this.getWantedPlaceholders(this.modelValue.template.content)

            const filteredReplacements = Object.fromEntries(
                wantedPlaceholders.map(key => [key, replacements[key]])
            );
            this.setFormCustomData(filteredReplacements)

            console.log("==================== FILTERED REPLACEMENTS ===================")
            console.log(filteredReplacements);

            // Replace all placeholders with values
            return templateContent.replace(/<<(\w+)>>/g, (match, key) => {
                //TODO: use this only if alt key is not defined in replacements above
                return filteredReplacements[key] ??  '&lt;&lt;' + key + '&gt;&gt;';
                //return filteredReplacements[key]
            });
        },
    }
})
</script>


<style scoped>
.__editor_header_wrapper {
    display: flex;
}

.__back_button_right {
    border-radius:6px;
    margin: 15px 10px;
    padding: 0 15px!important;
    color: #858587;
    background-color: #dfe0e3;
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
    /*display: flex;*/
    justify-content: center; /* vertical ^v */
    /*align-items: center;  /*<- horizontal ->*/
}

.__preview_page_wrapper {
    height: calc(100vh - 20px)!important;
    background-color: #d3d3d3;
    display: flex;
    justify-content: center;  /* center horizontally */
    overflow-y: scroll;
    padding-bottom: 0!important;
}

.__preview_page {
    margin-top: 30px;
    height: 1344px;
    width: 816px;
    background: white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.__preview_page p {
    margin: 5px 0px;
}

/*  LEGAL PAPER SIZE  */
.__preview_page_signature {
    /* margin: 30px auto; */
    margin: 0 auto;
    /* height: 1444px; */
    /* width: 916px; */
    width: 8.5in;
    height: 14in;
    padding: 1in;
    background: white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    page-break-after: always;
}

.__preview_page_signature p {
    /* margin: 5px 0px; */
}

.__richtext-preview p {
    margin: 1em 0;
    line-height: 1.5;
}
.__richtext-preview h1,
.__richtext-preview h2,
.__richtext-preview h3 {
    margin: 1.2em 0 0.6em;
}

.__richtext-preview_signature p {
    /* margin: 1em 0; */
    /* line-height: 1.5; */
}
.__richtext-preview_signature h1,
.__richtext-preview_signature h2,
.__richtext-preview_signature h3 {
    /* margin: 1.2em 0 0.6em; */
}

.__wizzard_dialog_action {
    background-color: #EBECEF;
    padding-top: 12px;
}

.__wizzard_dialog_action_float_bottom  {
    background-color: #EBECEF;
    padding: 5px 12px!important;

    position: fixed; /* Stick to the screen */
    bottom: 0;       /* Stick to bottom */
    left: 0;
    width: 100%;     /* Full width */
    z-index: 1;   /* Float on top of other elements */
    box-shadow: 0 -2px 6px rgba(0,0,0,0.1); /* Optional: subtle shadow */
}

.__wizzard_signature_action {
    background-color: #FCFCFC;
    padding-top: 8px;
    padding-bottom: 8px;
}

.__wizard_action_button {
    background-color: #E35205;
    padding: 0 20px!important;
}

.__message {
    display: grid;
    place-items: center;
    height: 100px;
}

@media print {
    @page {
        /*size: legal; */
        size: 8.5in 14in; /* Explicitly legal size */
        margin: 0 0; /* Disable browser's default print margins */
        padding: 0 0 ;
    }

    body {
        margin: 0;
        padding: 0;
    }

    .__print_preview {
        height: 1244px!important;
        display: block!important;
    }

    .__non-printable {
        display: none!important;
    }

    /*LAYOUTS*/
    .layout-wrapper.layout-nav-type-vertical:not(.layout-overlay-nav) .layout-content-wrapper {
        padding-inline-start: 0px;
    }
    .layout-vertical-nav {
        display: none!important;
    }
    .__page, layout-vertical-nav {
        display: none !important;
    }
}
</style>

