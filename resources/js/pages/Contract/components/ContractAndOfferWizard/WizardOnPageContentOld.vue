<template>
    <div class="__editor_header_wrapper">
        <!--Button - Return Button left side -->
        <button class="__editor_back_button" @click="cancelEdit($event)">
            <IconBtn style=" width: 35px; height: 35px;">
                <VIcon icon="tabler-arrow-left" />
            </IconBtn>
        </button>
        <div class="__editor_filename_wrapper">
            <input
                class="__editor_filename"
                ref="filenameRef"
                value="Filename here"
            />
            <small style="display: block">Updated 13:00 today</small>
        </div>
    </div>

    <div class="__preview_stepper_wrapper">
        <WizardStepper
            v-model="currentStepContent"
            :items="stepsContent"
            :currentStep="currentStepContent"
            :selectedView="selectedView"
            @backwardStep="handleBackwardStep"
        />
    </div>
    <VCardText class="disable-tab-transition" style="padding: 0; margin: 0">
        <VWindow v-model="currentStepContent" class="disable-tab-transition">
            <!-- TODO: Redisplay 1 to 2 if needed. -->
            <VWindowItem>&nbsp;</VWindowItem>
            <VWindowItem>&nbsp;</VWindowItem>
            <VWindowItem>&nbsp;</VWindowItem>
            <VWindowItem>
                <div class="__preview_page_wrapper">
                    <div class="__preview_page __richtext-preview">
                        <div class="__html_page" v-html="processedContent"></div>
                    </div>
                </div>
            </VWindowItem>
            <VWindowItem>
                <!-- TODO: Create the signature Template -->
                <div class="__preview_page_wrapper">
                    <div class="__preview_page __richtext-preview">
                        <div class="__html_page" v-html="processedContent"></div>
                    </div>
                </div>
            </VWindowItem>
            <VWindowItem>&nbsp;</VWindowItem>
            <VWindowItem>&nbsp;</VWindowItem>
        </VWindow>
    </VCardText>
    <VCardActions class="__wizzard_dialog_action justify-end">
        <VBtn
            class="__wizard_action_button"
            color="white"
            @click="handleStepSectionPage()"
        >
            Next
        </VBtn>
    </VCardActions>


</template>

<script>
import WizardStepper from "@/pages/Contract/components/ContractAndOfferWizard/WizardStepper.vue"
import { defineComponent } from "vue"

export default defineComponent({
    name: 'Preview',
    components: { WizardStepper },
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
            htmlContent: null,
            processedContent: '',
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
            stepsContent: this.steps
        }
    },
    created() {
        //alert(JSON.stringify(this.modelValue.employee))
        this.buildPreview()
    },
    methods: {
        handleStepSectionPage() {
            this.$emit('currentStep', this.currentStep+1)
            this.currentStepContent +=1
        },
        decodeHtmlEntities(encodedStr) {
            const txt = document.createElement("textarea");
            txt.innerHTML = encodedStr;
            return txt.value;
        },
        handleBackwardStep() {
            this.htmlContent = this.modelValue.template.content;
            //this.currentStep > 0? this.currentStep-- : ''
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
                amount = Number(amount);
            }
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(amount);
        },
        getAllowances() {
            let allowances = ''

            this.modelValue.customisation.allowances.forEach((allowance, index) => {
                //console.log(`Allowance ${index + 1}:`, allowance.name, allowance.amount);
                allowances = allowances + this.allowancesStaticData[allowance].name + '<br>'
                //alert(this.allowancesStaticData[allowance].name)
            });

            return allowances
        },
        replacePlaceholders(content, data) {
            const now = new Date();

            // Create a simple key-value map for substitution
            const replacements = {
                ApplicantName: data.employee.firstName + ' ' + data.lastName,
                ApplicantAddress: data.employee.address ?? '&lt;&lt;ApplicantAddress&gt;&gt;',
                LetterDate: now.toLocaleDateString(),
                ApplicantFirstName: data.employee.firstName,
                JobTitle: data.customisation.positionTitle,
                StartDate: data.customisation.startDate.toLocaleDateString(),
                SalaryAmount: this.formatCurrency(data.customisation.salary),
                Allowances: this.getAllowances()
                //TODO: define all keys to be tagged
                //ApplicantAddress: data.address ?? '<<ApplicantAddress>>',
                /*
                LetterDate: new Date().toLocaleDateString(),
                ApplicantFirstName: data.firstName,
                KWHBName: data.company || 'KWHB',
                JobTitle: data.jobTitle,
                ImmediateManager: data.managerName,
                ImmediateManagerPosition: data.managerPosition,
                SalaryAmount: data.salary,
                Subsidies: data.subsidies || 'None',
                StartDate: data.startDate,
                Location: data.location,*/
            };

            // Replace all placeholders in the format <<Key>>
            return content.replace(/<<(\w+)>>/g, (match, key) => {
                //If var not provided use the key to retain the placeholder <<Placeholder>>
                return replacements[key] ??  '&lt;&lt;' + key + '&gt;&gt;';
            });
        }
    }
})
</script>


<style>
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
    font-size: 1.2em;
    font-weight: bold;
    color: #3C395C;
}

.__preview_stepper_wrapper {
    background-color: #F5F5F5;
    padding: 5px 10px;
    /* display: flex; */
    justify-content: center; /* vertical ^v */
    /* align-items: center;  /*<- horizontal ->*/
}

.__preview_page_wrapper {
    background-color: #d3d3d3;
    display: flex;
    justify-content: center;  /* center horizontally */
}

.__preview_page {
    margin: 30px auto;
    height: 1300px;
    width: 816px;
    background: white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.__preview_page p {
    margin: 5px 0px;
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

.__wizzard_dialog_action {
    background-color: #EBECEF;
    padding-top: 12px;
}

.__wizard_action_button {
    background-color: #E35205;
    padding: 0px 20px;
}

@media print {
    body * {
        visibility: hidden;
    }

    .__html_page, .__html_page * {
        visibility: visible;
    }

    .__html_page {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        margin: 0;
        box-shadow: none !important;
        background: white;
    }
}
</style>

