<template>
    <WizardOnPageContent
        v-if="!isDialogBox"
        v-model="formData"
        :currentStep="currentStep"
        :steps="steps"
        :selectedView="selectedView"
        @close-wizard="handleCloseWizard"
    />

    <VDialog
        v-if="isDialogBox"
        v-model="modelValue"
        persistent
    >
        <template #default>
            <VCard style="overflow-y: hidden">
                <VCardText class="__stepper_wrapper" style="position: relative;">
                    <VBtn
                        icon
                        :style="{ visibility: currentStep === 0 ? 'hidden' : 'visible' }"
                        class="__vbutton_close"
                        style="position: absolute; top: 18px; left: 18px;"
                        @click="handleBackwardStep"
                    >
                        <VIcon>mdi-arrow-left</VIcon>
                    </VBtn>

                    <div style="display: flex; justify-content: center; align-items: center;">
                        <WizardStepper
                            v-model:current-step="currentStep"
                            :items="steps"
                            :currentStep="currentStep"
                            :selectedView="selectedView"
                            :lastValidStep="lastValidStep"
                            :selectedEmployee="selectedEmployee"
                            @backwardStep="handleBackwardStep"
                        />
                    </div>
                    <div
                        class="tw-font-bold tw-p-1 tw-rounded-lg d-flex tw-items-center tw-justify-center __stats_strip __status_strip_draft"
                        style="position: absolute; top: 18px; right: 80px;"
                    >
                        Draft
                    </div>
                    <VBtn
                        icon
                        @click="$emit('update:modelValue', false)"
                        class="__vbutton_close"
                        style="position: absolute; top: 18px; right: 18px;"
                    >
                        <VIcon>mdi-close</VIcon>
                    </VBtn>
                </VCardText>

                <VCardText class="__vcard_text">
                  <VWindow v-model="currentStep" class="disable-tab-transition">
                      <VWindowItem>
                        <GettingStarted  @templateSelected="handleTemplateSelection"  :selectedView="selectedView"/>
                      </VWindowItem>
                      <VWindowItem>
                          <SelectEmployee @employeeSelected="handleEmployeeSelection" />
                      </VWindowItem>
                      <VWindowItem>
                          <ContractCustomisation
                              v-if="selectedView === 'contract'"
                              @form-data="handleCustomisationFormData"
                          />
                          <LetterCustomisation
                              v-else
                              @form-data="handleCustomisationFormData"
                          />
                      </VWindowItem>
                      <VWindowItem>
                          4
                      </VWindowItem>
                  </VWindow>
                </VCardText>

                <VCardActions class="__wizzard_dialog_action justify-end">
                  <VBtn
                      class="__wizard_action_button"
                      color="white"
                      :disabled="!isProceedToNextStep()"
                      @click="handleProceedClick()"
                  >
                    Proceed
                  </VBtn>
                </VCardActions>
            </VCard>
        </template>
    </VDialog>
</template>

<script>
import WizardStepper from "@/pages/Contract/components/ContractAndOfferWizard/WizardStepper.vue"
import GettingStarted from "@/pages/Contract/components/ContractAndOfferWizard/GettingStarted.vue"
import SelectEmployee from "@/pages/Contract/components/ContractAndOfferWizard/SelectEmployee.vue"
import ContractCustomisation from "@/pages/Contract/components/ContractAndOfferWizard/ContractCustomisation.vue";
import LetterCustomisation from "@/pages/Contract/components/ContractAndOfferWizard/LetterCustomisation.vue";
import WizardOnPageContent from "@/pages/Contract/components/ContractAndOfferWizard/WizardOnPageContent.vue";
import { defineComponent } from "vue"

export default defineComponent({
    name: 'WizardDialogBox',
    props: {
        modelValue: {
            type: Boolean,
            required: true,
        },
        selectedView: {
            type: String, // or whatever type `type` is meant to be contract or
            required: false,
        },
        offerId: {
            type: null,
            required: false,
        },
        passedSelectedTemplate: {
            type: null,
            required: false
        }
    },
    emits: ['update:modelValue'],
    components: {
        ContractCustomisation,
        LetterCustomisation,
        SelectEmployee,
        GettingStarted,
        WizardStepper,
        WizardOnPageContent,
    },
    data() {
        return {
            isDialogBox: true,  //shortcut
            currentStep: 0, //shortcut
            steps: [
                { title: "Get Started", icon: "" },
                { title: "Select Employee", icon: "" },
                { title: "Contract Customisation", icon: "" },
                { title: "Preview", icon: "" },
                { title: "Signature", icon: "" },
                { title: "Pending Approval", icon: "" },
                { title: "Complete", icon: "" },
            ],
            selectedTemplate: null,
            selectedEmployee: null,
            formData: {
                template: null,
                employee: null,
                customisation: null
            },
            lastValidStep: 0
        }
    },
    methods: {
      handleTemplateSelection(template) {
          this.selectedTemplate = template
          this.formData.template = template
          //this.$emit('templateSelected', template); emit to Index?
      },
      handleProceedClick() {
          if(this.lastValidStep === this.currentStep){
              this.lastValidStep += 1
          }

          this.currentStep += 1
          const halfway = [
              this.selectedTemplate != null &&
              this.selectedEmployee != null &&
              this.formData.customisation != null
          ]
          if(this.currentStep === 3 && halfway){

              //hide the contract/letter main page to give way to the preview page
              this.$emit('handleContractLetterPreview', true);

              //sends the formData to the parent. Do i need this when this child is the new preview page? to be answered later.
              this.$emit('handleCreateContractLetterFormData', this.formData);

              //hide this modal and show the preview.
              this.isDialogBox = false
          }
      },
      isProceedToNextStep() {
          switch (this.currentStep) {
              case 0:
                  return this.selectedTemplate != null;
              case 1:
                  return this.selectedEmployee != null;
              case 2:
                  return this.formData.customisation != null;
              default:
                  return false; // Or true, depending on your fallback logic
          }
      },
      handleBackwardStep() {
          this.currentStep > 0? this.currentStep-- : ''
      },
      handleEmployeeSelection(employee) {
          this.selectedEmployee = employee  //indi
          this.formData.employee = employee //collect
      },
      handleCustomisationFormData(formData) {
          this.formData.customisation = formData
      },
      getSelectedView() {
          if (this.selectedView === 'letter_of_offer') {
              this.steps = this.steps.map(step =>
                  step.title === 'Contract Customisation'
                      ? { ...step, title: 'Letter of Offer Customisation' }
                      : step
              )
          }
      },
      handleCloseWizard() {
          this.selectedTemplate = null;
          this.selectedEmployee = null;
          this.formData = {
              template: null,
              employee: null,
              customisation: null
          };
          this.$emit('update:modelValue', false)
      }
    },
    mounted() {
        this. getSelectedView()
        if(this.passedSelectedTemplate !== null){
            this.handleTemplateSelection(this.passedSelectedTemplate)
            this.handleProceedClick()
        }
    }
})

</script>

<style scoped lang="css">

    .__vcard_text {
        min-height: 549.6px;
        overflow-y: scroll;
    }

    .__vbutton_close {
        border-radius: 7px!important;
        background-color: #D9D9E0!important;
        height: 30px!important;
        width: 30px!important;
        color: black!important;
    }

    .__stepper_wrapper, .__wizzard_dialog_action {
        background-color: #EBECEF;
        padding-top: 12px;

        display: flex;
        justify-content: center; /* horizontal alignment (center) - left and right*/
    }

    .__vcard_text::-webkit-scrollbar  {
        width: 10px;
    }
    .__vcard_text::-webkit-scrollbar-thumb {
        background-color: #888; /* Or your desired color */
        border-radius: 0; /* Removes rounded edges */
    }

    .__wizard_action_button {
        background-color: #E35205; padding: 0 20px;
    }


    .__stats_strip {
        height: 30px;
    }

    .__status_strip_draft {
        padding: 5px 20px;
        background-color: #D9D9E0;
        color: #808097;
    }
</style>
