<script setup lang="ts">
import contractApi from "@/api/contract/contractApi";
import toast from '@/composable/toast.js';
import ContractCustomisation from "@/pages/Contract/components/CreatingTabs/ContractCustomisation.vue";
import GettingStarted from "@/pages/Contract/components/CreatingTabs/GettingStarted.vue";
import PendingApproval from "@/pages/Contract/components/CreatingTabs/PendingApproval.vue";
import Preview from "@/pages/Contract/components/CreatingTabs/Preview.vue";
import SelectEmployee from "@/pages/Contract/components/CreatingTabs/SelectEmployee.vue";
import Signature from "@/pages/Contract/components/CreatingTabs/Signature.vue";
import { ref } from 'vue';

  const props = defineProps({ modelValue: Boolean });
  const emit = defineEmits(['update:modelValue', 'openCreateNewTemplate']);

  const currentStep = ref(0);
  const steps = [
    { title: "Get Started", icon: "" },
    { title: "Select Employee", icon: "" },
    { title: "Contract Customisation", icon: "" },
    { title: "Preview", icon: "" },
    { title: "Signature", icon: "" },
    { title: "Pending Approval", icon: "" },
    { title: "Complete", icon: "" },
  ];

  const selectedTemplate = ref(null);
  const selectedEmployee = ref(null);
  const customisationData = ref({});
  const contractTitle = ref(null);
  const selectedEmploymentType = ref(null);

  let newTemplateContent = '';  

  const handleTemplateSelection = (template) => {
    selectedTemplate.value = template;
  };

  const handleEmployeeSelection = (employee) => {
    selectedEmployee.value = employee;
  };

  const handleCustomizationData = (data) => {
    customisationData.value = data;
    console.log(data);
  };

  const handleContractTitleChange = (title) => {
    console.log('Received Contract Title:', title);
    contractTitle.value = title; 
  };

  const handleEmploymentTypeChange = (employmentType) => {
    console.log('Received Employment Type:', employmentType);
    selectedEmploymentType.value = employmentType;
  };

  const handleCreateClick = async () => {
    if (currentStep.value === 2) {
      try {
        await updateLiveContent();

        const contractId = await saveContent(newTemplateContent);

        const contractData = await getContract(contractId);

        emit('openCreateNewContract', {
          selectedTemplate: selectedTemplate.value,
          selectedEmployee: selectedEmployee.value,
          customisationData: customisationData.value,
          contractData: contractData,
        });

        emit("update:modelValue", false);
      } catch (error) {
        console.error("Error while saving contract:", error);
      }
    }

    if (currentStep.value < steps.length - 1) {
      currentStep.value++;
    }
  };

  const getContract = async (contractId) => {
    try {
      const response = await contractApi.editContract(contractId);
      return response;
    } catch (error) {
      return null;
    }
  };

  const saveContent = async (updatedContent) => {
    const contractData = {
      title: contractTitle.value,
      content: updatedContent,
      owner_employee_id: selectedEmployee.value.employeeId,
      status: 'pending_0',
      stage: 'draft',
      customisationData: customisationData.value,
      employment_type_id: selectedEmploymentType.value,
      location_id: selectedEmployee.value.locationId,
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
  };

  const updateLiveContent = () => {
    const placeholderRegex = /<<([^>]+)>>/g;

    if (!selectedTemplate.value.content) {
      console.error("Template content is empty or undefined.");
      return;
    }

    const parser = new DOMParser();
    const doc = parser.parseFromString(selectedTemplate.value.content, "text/html");

    const replacePlaceholders = (node) => {
      if (node.nodeType === Node.TEXT_NODE) {
        node.textContent = node.textContent.replace(placeholderRegex, (match, placeholder) => {
          const snakeKey = placeholder.toLowerCase().replace(/[^a-z0-9]+/g, "_");

          if (customisationData.value[snakeKey]) {
            return customisationData.value[snakeKey].value || match; 
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

    newTemplateContent = doc.body.innerHTML;
  };

  const canProceedToNextStep = () => {
    if (currentStep.value === 1 && !selectedEmployee.value) {
      return false;
    }
    if (currentStep.value === 0 && !selectedTemplate.value) {
      return false;
    }
    return true;
  };
</script>

<template>
  <VDialog
    :model-value="props.modelValue"
    @update:model-value="val => emit('update:modelValue', val)"
    persistent
  >
    <template #default>
      <VCard>
        <!-- Title -->
        <VCardTitle>
          <div style="display: flex; justify-content: space-between;">
            <span>Create Contract</span>
            <VBtn icon @click="emit('update:modelValue', false)">
              <VIcon>mdi-close</VIcon>
            </VBtn>
          </div>
        </VCardTitle>

        <!-- Stepper -->
        <VCardText style="background-color: #EBECEF">
          <AppStepper v-model:current-step="currentStep" :items="steps" />
        </VCardText>

        <VDivider />

        <!-- Step Content -->
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

          <!-- Navigation Buttons -->
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
