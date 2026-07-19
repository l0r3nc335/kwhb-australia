<script>
    import ContractEditor from "@/pages/Contract/components/ContractEditor.vue";

    export default {
        components: {
            ContractEditor
        },
        methods: {
            handleBackCreate(templateData) {
            if (!templateData || !templateData.selectedTemplate) {
                console.error("Missing template data or selectedTemplate property");
                return;
            }
            this.$emit('handleBackCreate', {
                selectedTemplate: templateData.selectedTemplate,
                selectedEmployee: templateData.selectedEmployee,
                customisationData: templateData.customisationData,
            });
            }

        },
        props: {
            type: { 
                type: String,
                required: true
            },
            template: {
                type: Object,
                default: () => null,
            },
            employee: {
                type: Object,
                default: () => null,
            },
            customData: {
                type: Object,
                default: () => null,
            },
            contractData: {
                type: Object,
                default: () => null,
            },
        },
    }
</script>

<template>
    <div>
        <ContractEditor @handleBackCreate="handleBackCreate" :type="type" :template="template" :employee="employee" :customData="customData" :contractData="contractData"/>
    </div>
</template>
