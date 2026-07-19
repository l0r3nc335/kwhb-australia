<script>
    import TemplateEditor from "@/pages/Contract/components/TemplateEditor/index.vue";
    //import TemplateEditor from "@/pages/Contract/components/TemplateEditor/index.vue";
    import DialogCreate from "@/pages/Contract/components/CreatingTabs/index.vue"
    export default {
        components: {
            DialogCreate,
            TemplateEditor
        },

        data() {
            return {
                showEditor: true,
                draggableStandardFields: [
                    {
                        id: 1,
                        label: 'Title',
                        name: 'Title'
                    },
                    {
                        id: 2,
                        label: 'Signature',
                        name: 'Signature'
                    },
                    {
                        id: 3,
                        label: 'Letter Date',
                        name: 'LetterDate'
                    },
                    {
                        id: 4,
                        label: 'Signature Date',
                        name: 'SignatureDate'
                    },
                    {
                        id: 5,
                        label: 'Upload Letter Head',
                        name: 'UploadLetterHead'
                    },
                    {
                        id: 6,
                        label: 'Applicant Name',
                        name: 'ApplicantName'
                    },
                    {
                        id: 7,
                        label: 'Applicant Email',
                        name: 'ApplicantEmail'
                    },
                    {
                        id: 8,
                        label: 'Applicant Phone',
                        name: 'ApplicantPhone'
                    },
                    {
                        id: 9,
                        label: 'KWHB Phone',
                        name: 'KWHBPhone'
                    },
                    {
                        id: 10,
                        label: 'KWHB Email',
                        name: 'KWHBEmail'
                    },
                    {
                        id: 11,
                        label: 'KWHB Name',
                        name: 'KWHBName'
                    },
                    {
                        id: 12,
                        label: 'Job Title',
                        name: 'JobTitle'
                    },
                    {
                        id: 13,
                        label: 'Agreement Classification',
                        name: 'AgreementClassification'
                    },
                    {
                        id: 14,
                        label: 'Current Rate of Pay',
                        name: 'CurrentRateOfPay'
                    },
                    {
                        id: 15,
                        label: 'Salary Amount',
                        name: 'SalaryAmount'
                    },
                    {
                        id: 16,
                        label: 'Vaccinations',
                        name: 'Vaccinations'
                    },
                    {
                        id: 17,
                        label: 'Compliance',
                        name: 'Compliance'
                    },
                    {
                        id: 18,
                        label: 'Subsidies',
                        name: 'Subsidies'
                    },
                    {
                        id: 19,
                        label: 'Hours Per Week',
                        name: 'HoursPerWeek'
                    },
                ]
            }
        },
        methods: {
            handleCreateNewCancel (create)
            {
                this.$emit('handleCreateNewCancel', create)
                console.log(handleCreateNewCancel)
            },
            handleCloseEditor(type) {
                this.$emit('close-wysiwyg', type);
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
        }

    }
    //@handleCreateNewCancel="handleCreateNewCancel"
</script>

<template>
    <div>
        <TemplateEditor
            v-if="showEditor"
            :type="type"
            :template="template"
            :employee="employee"
            :customData="customData"
            :draggableStandardFields="draggableStandardFields"
            @close-editor="handleCloseEditor"
        />
    </div>
</template>


<style>


</style>
