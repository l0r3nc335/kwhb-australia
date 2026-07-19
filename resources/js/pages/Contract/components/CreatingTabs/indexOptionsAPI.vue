<template>
    <VDialog
        :model-value="modelValue"
        @update:model-value="val => $emit('update:modelValue', val)"
        persistent
    >
        <template #default>
            <VCard>
                <VCardTitle>
                    <div style="display: flex; justify-content: space-between;">
                        <span>Create Contract</span>
                        <VBtn icon @click="$emit('update:modelValue', false)">
                            <VIcon>mdi-close</VIcon>
                        </VBtn>
                    </div>
                </VCardTitle>

                <VCardText style="background-color: #EBECEF">
                    <AppStepper v-model:current-step="currentStep" :items="steps" />
                </VCardText>

                <VDivider />

                <VCardText>
                    <VWindow v-model="currentStep" class="disable-tab-transition">
                        <VWindowItem>
                            <GettingStarted @templateSelected="handleTemplateSelection" />
                        </VWindowItem>
                        <VWindowItem>
                            <SelectEmployee @employeeSelected="handleEmployeeSelection" />
                        </VWindowItem>
                        <VWindowItem>
                            <ContractCustomisation
                                :selectedTemplate="selectedTemplate"
                                :employee="selectedEmployee"
                                :customisationData="customisationData"
                                @updateContractTitle="handleContractTitleChange"
                                @updateEmploymentType="handleEmploymentTypeChange"
                                @updateCustomisationData="handleCustomizationData"
                            />
                        </VWindowItem>
                        <VWindowItem>
                            <Preview :template="selectedTemplate" :employee="selectedEmployee" />
                        </VWindowItem>
                        <VWindowItem>
                            <Signature />
                        </VWindowItem>
                        <VWindowItem>
                            <PendingApproval />
                        </VWindowItem>
                        <VWindowItem>
                            <div class="text-center">
                                <p>Contract is Complete!</p>
                                <VBtn color="success" @click="onSubmit">Close</VBtn>
                            </div>
                        </VWindowItem>
                    </VWindow>

                    <div class="d-flex justify-right mt-4" style="justify-content: right;">
                        <VBtn
                            v-if="currentStep === steps.length - 1"
                            color="success"
                            @click="onSubmit"
                        >
                            Submit
                        </VBtn>
                        <VBtn
                            v-else
                            :disabled="!canProceedToNextStep()"
                            @click="handleCreateClick"
                        >
                            {{ currentStep === 2 ? 'Create' : 'Proceed' }}
                        </VBtn>
                    </div>
                </VCardText>
            </VCard>
        </template>
    </VDialog>
</template>

<script lang="ts">
import contractApi from "@/api/contract/contractApi";
import toast from '@/composable/toast.js';
import ContractCustomisation from "@/pages/Contract/components/CreatingTabs/ContractCustomisation.vue";
import GettingStarted from "@/pages/Contract/components/CreatingTabs/GettingStarted.vue";
import PendingApproval from "@/pages/Contract/components/CreatingTabs/PendingApproval.vue";
import Preview from "@/pages/Contract/components/CreatingTabs/Preview.vue";
import SelectEmployee from "@/pages/Contract/components/CreatingTabs/SelectEmployee.vue";
import Signature from "@/pages/Contract/components/CreatingTabs/Signature.vue";

export default {
    components: {
        ContractCustomisation,
        GettingStarted,
        PendingApproval,
        Preview,
        SelectEmployee,
        Signature
    },
    props: {
        modelValue: Boolean
    },
    emits: ['update:modelValue', 'openCreateNewTemplate', 'openCreateNewContract'],
    data() {
        return {
            currentStep: 0,
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
            customisationData: {},
            contractTitle: null,
            selectedEmploymentType: null,
            newTemplateContent: ''
        };
    },
    methods: {
        handleTemplateSelection(template: any) {
            this.selectedTemplate = template;
        },
        handleEmployeeSelection(employee: any) {
            this.selectedEmployee = employee;
        },
        handleCustomizationData(data: any) {
            this.customisationData = data;
            console.log(data);
        },
        handleContractTitleChange(title: string) {
            console.log('Received Contract Title:', title);
            this.contractTitle = title;
        },
        handleEmploymentTypeChange(employmentType: any) {
            console.log('Received Employment Type:', employmentType);
            this.selectedEmploymentType = employmentType;
        },
        async handleCreateClick() {
            if (this.currentStep === 2) {
                try {
                    this.updateLiveContent();

                    const contractId = await this.saveContent(this.newTemplateContent);
                    const contractData = await this.getContract(contractId);

                    this.$emit('openCreateNewContract', {
                        selectedTemplate: this.selectedTemplate,
                        selectedEmployee: this.selectedEmployee,
                        customisationData: this.customisationData,
                        contractData: contractData,
                    });

                    this.$emit("update:modelValue", false);
                } catch (error) {
                    console.error("Error while saving contract:", error);
                }
            }

            if (this.currentStep < this.steps.length - 1) {
                this.currentStep++;
            }
        },
        async getContract(contractId: string) {
            try {
                return await contractApi.editContract(contractId);
            } catch (error) {
                return null;
            }
        },
        async saveContent(updatedContent: string) {
            const contractData = {
                title: this.contractTitle,
                content: updatedContent,
                owner_employee_id: this.selectedEmployee.employeeId,
                status: 'pending_0',
                stage: 'draft',
                customisationData: this.customisationData,
                employment_type_id: this.selectedEmploymentType,
                location_id: this.selectedEmployee.locationId,
            };

            try {
                const response = await contractApi.storeContract(contractData);
                if (response) {
                    toast('success', 'Template Contract saved!');
                    return response.contractID;
                }
            } catch (error) {
                console.error(error);
            }
        },
        updateLiveContent() {
            const placeholderRegex = /<<([^>]+)>>/g;

            if (!this.selectedTemplate?.content) {
                console.error("Template content is empty or undefined.");
                return;
            }

            const parser = new DOMParser();
            const doc = parser.parseFromString(this.selectedTemplate.content, "text/html");

            const replacePlaceholders = (node: any) => {
                if (node.nodeType === Node.TEXT_NODE) {
                    node.textContent = node.textContent.replace(placeholderRegex, (match: string, placeholder: string) => {
                        const snakeKey = placeholder.toLowerCase().replace(/[^a-z0-9]+/g, "_");
                        if (this.customisationData[snakeKey]) {
                            return this.customisationData[snakeKey].value || match;
                        } else {
                            console.warn(`No value found for placeholder: ${placeholder}`);
                            return match;
                        }
                    });
                } else if (node.nodeType === Node.ELEMENT_NODE) {
                    Array.from(node.childNodes).forEach(replacePlaceholders);
                }
            };

            replacePlaceholders(doc.body);
            this.newTemplateContent = doc.body.innerHTML;
        },
        canProceedToNextStep() {
            if (this.currentStep === 1 && !this.selectedEmployee) return false;
            if (this.currentStep === 0 && !this.selectedTemplate) return false;
            return true;
        },
        onSubmit() {
            this.$emit("update:modelValue", false);
        }
    }
};
</script>
