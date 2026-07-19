<template>
    <div class="d-flex" style="justify-content: space-between; margin-top: 20px;">
        <div class="__left_menu"></div>

        <div class="__right_menu">
            <div class="tw-mt-16" style="max-width: 800px">
                <h2 class="text-h3 font-weight-bold" style="font: 32px Arial, Helvetica, sans-serif;">Letter of Offer Customisation</h2>
                <p class="mb-6 __global_text_font">
                    Select or enter any additional information that will be included in your Letter of Offer.
                </p>

                <div class="mt-2 mb-10 w-full">

                    <div class="d-flex">
                        <div class="__half_left tw-w-1/2">
                            <div class="__form_input">
                                <span class="__label __global_text_font">Position & Title</span><span style="color: red!important;">*</span>
                                <AppCombobox
                                    class="w-full __global_text_font"
                                    clearable
                                    v-model="formData.positionTitle"
                                    :items="positionStore.data.positions"
                                    item-title="name"
                                    item-value="id"
                                    @blur="isFormComplete"
                                    disabled
                                >
                                </AppCombobox>
                            </div>
                        </div>
                        <div class="__half_left tw-w-1/2" style="padding-right: 0!important;">
                                <span class="__label __global_text_font">Pay Classification</span><span style="color: red!important;">*</span>
                                <AppCombobox
                                    class="w-full __global_text_font"
                                    clearable
                                    v-model="formData.classification"
                                    :items="classifications"
                                    item-title="classification"
                                    item-value="id"
                                    @blur="isFormCompleteInPayClassification"
                                    disabled
                                >
                                </AppCombobox>
                        </div>
                    </div>
                    <div class="d-flex">
                        <div class="__half_left tw-w-1/2">
                            <div class="__form_input">
                                <span class="__label __global_text_font">Start Date</span><span style="color: red!important;">*</span>
                                <VueDatePicker
                                    v-model="formData.startDate"
                                    :min-date="getMinimumDate()"
                                    format="dd-MM-yyyy"
                                    :enable-time-picker="false"
                                    :auto-apply="true"
                                    position="left"
                                    clearable
                                    @blur="isFormComplete"
                                    disabled
                                >
                                </VueDatePicker>
                            </div>
                        </div>
                        <div class="__half_right tw-w-1/2">
                            <div class="__form_input tw-relative">
                                <div class="tw-w-full tw-text-sm" style="padding-top: 2px!important;">
                                    <div class="tw-w-1/2 tw-inline-block tw-text-left __global_text_font">
                                        Salary<span style="color: red!important;">*</span>
                                    </div>
                                    <!--div class="tw-w-1/2 tw-inline-block tw-text-right __global_text_font">
                                        ${{ minPay ?? 0 }} - ${{ maxPay }}
                                    </div-->
                                </div>
                                <v-icon class="__input_search_magnifier tw-absolute tw-top-9 tw-right-0 -tw-translate-y-1/2" size="20"  style="color: #353535;" >
                                    mdi-pencil
                                </v-icon>
                                <InputNumberFormatCurrency
                                    v-model="formData.salary"
                                    :max="maxPay"
                                    :min="minPay"
                                    @blur="isFormComplete"
                                    style="padding-right: 40px; background-color: #ddd9d9"
                                    :disabled="true"
                                />
                            </div>
                        </div>
                    </div>
                    <div class="d-flex">
                        <div class="__half_left tw-w-1/2">
                            <div class="__form_input">
                                <span class="__label __global_text_font">Employment Basis</span><span style="color: red!important;">*</span>
                                <AppCombobox
                                    class="w-full __global_text_font"
                                    clearable
                                    v-model="formData.employmentBasis"
                                    :items="employmentStore.data.employments"
                                    item-title="name"
                                    item-value="id"
                                    @blur="isFormComplete"
                                    disabled
                                >
                                </AppCombobox>
                            </div>
                        </div>
                        <div class="__half_right tw-w-1/2">
                            <div class="__form_input">
                                <span class="__label __global_text_font">Select/Add Vaccination</span><span style="color: red!important;">*</span>
                                <SelectAddDropdownListV2
                                    v-model="formData.vaccinations"
                                    :items="vaccinationStore.data.vaccinations"
                                    item-title="name"
                                    @blur="isFormComplete"
                                    disabled
                                />
                            </div>
                        </div>
                    </div>
                    <div class="d-flex">
                        <div class="__half_left tw-w-1/2">
                            <div class="__form_input">
                                <span class="__label __global_text_font">Select/Add Certifications</span><span style="color: red!important;">*</span>
                                <SelectAddDropdownListV2
                                    v-model="formData.certifications"
                                    :items="complianceStore.data.compliances"
                                    item-title="name"
                                    @blur="isFormComplete"
                                    disabled
                                />
                            </div>
                        </div>
                        <div class="__half_right tw-w-1/2">

                            <div class="__form_input">
                                <span class="__label __global_text_font">Select/Add Allowances</span>
                                <SelectAddDropdownListV2
                                    v-model="formData.allowances"
                                    :items="allowanceStore.data.allowances"
                                    @blur="isFormComplete"
                                    disabled
                                />
                            </div>
                        </div>
                    </div>
                    <div class="d-flex">
                        <div class="__half_left tw-w-1/2">
                            <div class="__form_input">
                                <!--span class="__label __global_text_font">Select/Add Allowances</span>
                                <SelectAddDropdownList
                                    v-model="formData.allowances"
                                    :items="selectAddAllowanceItems"
                                    @blur="isFormComplete"
                                /-->
                            </div>
                        </div>
                        <div class="__half_right tw-w-1/2">
                            <div class="__form_input">
                                <!--span class="__label __global_text_font">Select/Add Subsidies</span>
                                <SelectAddDropdownList
                                    v-model="formData.subsidies"
                                    :items="selectAddSubsidiesItems"
                                    @blur="isFormComplete"
                                /-->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { useComplianceStore } from "@/store/complianceStore";
import { useEmploymentStore } from "@/store/employmentStore";
import { usePositionStore } from "@/store/positionStore";
import { useVaccinationStore } from "@/store/vaccinationStore";
import InputNumberFormatCurrency from "./components/InputNumberFormatCurrency.vue";
import SelectAddDropdownList from "./components/SelectAddDropdownList.vue";
import SelectAddDropdownListV2 from "./components/SelectAddDropdownListV2.vue";

import complianceApi from "@/api/compliance/compliance";
import payClassification from "@/api/payClassification/payClassificationApi";
import payRangeApi from "@/api/payRange/payRangeApi";
import vaccinationApi from "@/api/vaccination/vaccination";
import { defineComponent } from "vue";
import { useAllowanceStore } from "@/store/allowanceStore"
import allowanceApi from "@/api/allowance/allowanceApi"

export default defineComponent({
    name: 'LetterCustomisationFullPage',
    components: { SelectAddDropdownList, InputNumberFormatCurrency, SelectAddDropdownListV2  },
    props: {
        modelValue: {
            type: null,
            required: true,
        },
    },
    data() {
        return {
            formData: {
                positionTitle: null,
                classification: null,
                startDate: null,
                salary: null,
                employmentBasis: null,
                vaccinations: [],
                certifications: [],
                requirements: [],
                allowances: [],
                subsidies: [],
            },
            selectAddPositionTitleItems: [
                { id: 1, name: 'Position Title 1' },
                { id: 2, name: 'Position Title 2' },
                { id: 3, name: 'Position Title 3' },
                { id: 4, name: 'Position Title 4' },
                { id: 5, name: 'Position Title 5' },
            ],
            employmentBasisItems: [
                { id: 1, name: 'Full time, Continuing' },
                { id: 2, name: 'Full time, Limited Tenure' },
                { id: 3, name: 'Part time, Continuing' },
                { id: 4, name: 'Part time, Limited Tenure' },
                { id: 5, name: 'Casual' },
            ],
            selectAddVaccinationItems: [
                { id: 1, name: "Sample Vaccine 1" },
                { id: 2, name: "Sample Vaccine 2" },
                { id: 3, name: "Sample Vaccine 3" },
            ],
            selectAddCertificationItems: [
                { id: 1, name: "Sample Certification 1" },
                { id: 2, name: "Sample Certification 2" },
                { id: 3, name: "Sample Certification 3" },
                { id: 4, name: "Sample Certification 4" },
                { id: 5, name: "Sample Certification 5" },
                { id: 6, name: "Sample Certification 6" },
                { id: 7, name: "Sample Certification 7" },
            ],
            selectAddRequirementsItems: [
                { id: 1, name: "Sample Requirements 1" },
                { id: 2, name: "Sample Requirements 2" },
                { id: 3, name: "Sample Requirements 3" },
                { id: 4, name: "Sample Requirements 4" },
                { id: 5, name: "Sample Requirements 5" },
                { id: 6, name: "Sample Requirements 6" },
                { id: 7, name: "Sample Requirements 7" },
            ],
            selectAddAllowanceItems: [
                { id: 1, name: "Sample Allowance 1" },
                { id: 2, name: "Sample Allowance 2" },
                { id: 3, name: "Sample Allowance 3" },
                { id: 4, name: "Sample Allowance 4" },
                { id: 5, name: "Sample Allowance 5" },
                { id: 6, name: "Sample Allowance 6" },
                { id: 7, name: "Sample Allowance 7" },
            ],
            selectAddSubsidiesItems: [
                { id: 1, name: "Sample Subsidies 1" },
                { id: 2, name: "Sample Subsidies 2" },
                { id: 3, name: "Sample Subsidies 3" },
                { id: 4, name: "Sample Subsidies 4" },
                { id: 5, name: "Sample Subsidies 5" },
                { id: 6, name: "Sample Subsidies 6" },
                { id: 7, name: "Sample Subsidies 7" },
            ],
            classifications: [],
            payRange: null,
            localPayRange: null,
            isEditPay: false,
            minPay: 0,
            maxPay: null,
        };
    },
    watch: {
        'formData.positionTitle'(newVal, oldVal) {
            this.getVaccinations(newVal.name)
            this.getCompliances(newVal.name)
            this.formData.classification = newVal.pay_classifications_data ?? null
            this.formData.salary = newVal.pay_classifications_data.annual_salary ?? null
        }
    },
    methods: {
        async getAllowances() {
            try {
                this.loading = true
                const allowances = this.formData.allowances
                const formData = new FormData()
                formData.append('idList', allowances)

                const response = await allowanceApi.fetchAllowancesPackage(formData)

                let allAllowances = ''
                response.forEach((allowance) => {
                    allAllowances += `&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;${allowance.name}: ${allowance.value} <br>`
                })
                this.loading = false
                return allAllowances
            } catch (error) {
                console.error('Error fetching allowances:', error)
                return ''
            }
        },
        async getVaccinations(position) {
            try {
                this.loadItems = true
                const response = await vaccinationApi.fetch_vaccination_by_role({ role: position })
                this.formData.vaccinations =  response.data//.map(item => item.id)

                this.loadItems = false
            } catch (error) {
                console.error(error)
            }
        },
        async getCompliances(position) {
            try {
                this.loadItems = true
                const response = await complianceApi.fetch_compliance_by_role({ role: position })
                this.formData.certifications = response.data//.map(item => item.id)

                this.loadItems = false
            } catch (error) {
                console.error(error)
            }
        },
        async isFormCompleteInPayClassification() {
            await this.isFormComplete()
            if(this.formData.classification === null){
                return
            }
            console.log(this.formData.classification)

            try {
                this.loading = true;

                this.minPay = this.formData.classification.start_range
                this.maxPay = this.formData.classification.end_range
                //this.formData.salary = this.maxPay

                this.loading = false;

            } catch (error) {
                console.error('Error fetching pay range:', error);
            } finally {
                this.loading = false;
            }
        },
        async loadPayRange() {
            try {
                const res_payrange = await payRangeApi.fetchPayRanges();
                const items_res_payrange = res_payrange.data ? res_payrange.data : res_payrange;

                this.classifications = items_res_payrange.map(items_res_payrange => ({
                    label: items_res_payrange.classification,
                    value: items_res_payrange.id,
                    start_range: items_res_payrange.start_range,
                    end_range: items_res_payrange.end_range
                }));

                //console.log("============================= PAY RANGE ========================")
                //console.log(this.classifications )
            } catch (e) {
                console.error("Failed loading pay classifications:", e);
            }
        },
        async loadClassifications() {
            try {
                const res = await payClassification.fetchActiveClassifications();
                this.classifications = res.data ? res.data : res;
            } catch (e) {
                console.error("Failed loading pay classifications:", e);
            }
        },
        async isFormComplete() {

            const {
                positionTitle,
                classification,
                startDate,
                salary,
                employmentBasis,
                vaccinations,
                certifications,
                //requirements,
                //allowances,
                //subsidies
            } = this.formData;

            const basicFieldsFilled =
                classification &&
                positionTitle &&
                startDate &&
                salary &&
                employmentBasis;

            const arraysFilled =
                vaccinations.length > 0 &&
                certifications.length > 0
                //requirements.length > 0 &&
               // allowances.length > 0 //&&
                //subsidies.length > 0;

            if(basicFieldsFilled && arraysFilled){
                this.formData.vaccinations = this.transformIDsToNamesArray( vaccinations, this.vaccinationStore.data.vaccinations)
                //this.formData.subsidies  = this.transformIDsToNamesArray(subsidies, this.selectAddSubsidiesItems)
                this.formData.certifications  = this.transformIDsToNamesArray(certifications, this.complianceStore.data.compliances)
                //this.formData.allAllowances = await this.getAllowances()
                this.$emit('form-data', this.formData,  true)
            }else{
                this.$emit('form-data', this.formData, false)
            }
        },
        transformIDsToNamesArray(field, list){
            return field.map(id => {
                const match = list.find(v => v.id === id);
                return match?.name || id;
            });
        },
        getMinimumDate(){
            return new Date().toISOString().split('T')[0];
        },
        setFormData() {
            this.formData.allowances = this.modelValue.customisation.allowances
            this.formData.certifications = this.modelValue.customisation.certifications
            this.formData.vaccinations = this.modelValue.customisation.vaccinations
            this.formData.employmentBasis = this.modelValue.customisation.employmentBasis
            this.formData.salary = this.modelValue.customisation.salary
            this.formData.startDate = this.modelValue.customisation.startDate
            this.formData.classification = this.modelValue.customisation.classification
            this.formData.positionTitle = this.modelValue.customisation.positionTitle
        }
    },
    async mounted() {
        await this.loadClassifications()
        //await this.loadPayRange()

        //this.loadResources()
        this.positionStore = usePositionStore()

        await this.vaccinationStore.setVaccinations()
        await this.positionStore.setPositions()
        await this.employmentStore.setEmployments()
        await this.complianceStore.setCompliances()
        await this.allowanceStore.setAllowances()

        console.log("modelValue")
        console.log(this.modelValue)

        this.setFormData()
    },
    computed: {
        vaccinationStore() {
            return useVaccinationStore()
        },
        positionStore() {
            return usePositionStore();
        },
        employmentStore() {
            return useEmploymentStore();
        },
        complianceStore() {
            return useComplianceStore();
        },
        allowanceStore() {
            return useAllowanceStore();
        }
    },
})
</script>

<style>
.__left_menu {
    width: 20%;
    padding: 5px;
}

.__right_menu {
    width: 80%;
    min-height: 500px !important;
}

.__half_left {
    padding: 5px 30px 5px 0px;
}

.__half_right {
    padding: 5px 0px 5px 0px;
}

.__form_input {
    margin-bottom: 20px;
}

.v-chip__content {
    color: green;
}
.v-chip__underlay {
    background-color: grey!important;
}

.v-list-item-title {

}

.__global_text_font {
    font: 16px Arial, Helvetica, sans-serif;
}
</style>
