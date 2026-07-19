<template>
    <div class="d-flex" style="justify-content: space-between; margin-top: 20px;">
        <div class="__left_menu"></div>

        <div class="__right_menu">
            <div class="tw-mt-16" style="max-width: 800px">
                <h2 class="text-h3 font-weight-bold">Contract Customisation</h2>
                <p class="mb-6 __global_text_font">
                    Select or enter any additional information that will be included in your Contract.
                </p>

                <div class="mt-2 mb-10 w-full">
                    <!-- ROW1 -->
                    <div class="d-flex">
                        <!-- LEFT -->
                        <div class="__half_left tw-w-1/2">
                            <div class="__form_input">
                                <span class="__label __global_text_font">Position & Title</span>
                                <AppCombobox
                                    class="w-full __global_text_font"
                                    clearable
                                    v-model="formData.positionTitle"
                                    :items="positionStore.data.positions"
                                    placeholder="Select employment basis"
                                    item-title="name"
                                    item-value="id"
                                    @blur="isFormComplete"
                                >
                                </AppCombobox>
                            </div>
                        </div>
                        <!--------------------------------- RIGHT ------------------------------------>
                        <div class="__half_right tw-w-1/2">

                            <div class="__form_input">
                                <span class="__label __global_text_font">Classification</span>
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
                    </div>
                    <!-- ROW2 -->
                    <div class="d-flex">
                        <!-- LEFT -->
                        <div class="__half_left tw-w-1/2">
                            <div class="__form_input tw-relative">
                                <div class="tw-w-full tw-text-sm" style="margin-top: 2px!important;">
                                    <div class="tw-w-1/2 tw-inline-block tw-text-left __global_text_font">
                                        Salary:
                                    </div>
                                    <!--div class="tw-w-1/2 tw-inline-block tw-text-right __global_text_font">
                                        ${{ minPay ?? 0 }} - ${{ maxPay }}
                                    </div-->
                                </div>
                                <v-icon class="__input_search_magnifier tw-absolute tw-top-9 tw-right-0 -tw-translate-y-1/2" size="20"  style="color: #353535;" >
                                    mdi-pencil
                                </v-icon>
                                <InputNumberFormatCurrency
                                    @blur="isFormComplete"
                                    v-model="formData.salary"
                                    :max="maxPay"
                                    :min="minPay"
                                    placeholder="$"
                                    style="padding-right: 40px; background-color: #ddd9d9"
                                    :disabled="true"
                                />
                            </div>
                        </div>
                        <!--------------------------------- RIGHT ------------------------------------>
                        <div class="__half_right tw-w-1/2">
                            <div class="__form_input">
                                <div class="__form_input">
                                    <span class="__label __global_text_font">Employment Basis</span>
                                    <AppCombobox
                                        class="w-full __global_text_font"
                                        clearable
                                        v-model="formData.employmentBasis"
                                        :items="employmentStore.data.employments"
                                        item-title="name"
                                        item-value="id"
                                        @blur="isFormComplete"
                                    >
                                    </AppCombobox>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- ROW3 -->
                    <div class="d-flex">
                        <!-- LEFT -->
                        <div class="__half_left tw-w-1/2">
                            <div class="__form_input">
                                <span class="__label __global_text_font">Start Date</span>
                                <VueDatePicker
                                    v-model="formData.startDate"
                                    format="dd-MM-yyyy"
                                    :enable-time-picker="false"
                                    :min-date="getMinimumDate()"
                                    position="left"
                                    :auto-apply="true"
                                    clearable
                                    @blur="isFormComplete"
                                >
                                </VueDatePicker>
                            </div>
                        </div>
                        <!--------------------------------- RIGHT ------------------------------------>
                        <div class="__half_right tw-w-1/2">
                            <div class="__form_input">
                                <span class="__label __global_text_font">End Date</span>
                                <VueDatePicker
                                    :min-date="getMinimumDate()"
                                    v-model="formData.endDate"
                                    format="dd-MM-yyyy"
                                    :enable-time-picker="false"
                                    :auto-apply="true"
                                    position="left"
                                    clearable
                                    @blur="isFormComplete"
                                    :disabled="formData.isContractFullTime === true"
                                >
                                </VueDatePicker>
                            </div>
                        </div>
                    </div>
                    <!-- ROW4 -->
                    <div class="d-flex">
                        <!-- LEFT -->
                        <div class="__half_left tw-w-1/2">
                            <div class="__form_input">
                                <input
                                    type="checkbox"
                                    class="custom-checkbox tw-accent-green-600 tw-w-3 tw-h-3 tw-ms-5"
                                    v-model="formData.isContractFullTime"
                                    @blur="isFormComplete"
                                >
                                <span class="tw-ms-[15px] __global_text_font">This contract is ongoing.</span>
                            </div>
                        </div>
                        <!--------------------------------- RIGHT ------------------------------------>
                        <div class="__half_right tw-w-1/2">
                            <div class="__form_input">
                                <input type="hidden">
                                <span class="tw-ms-[15px]">&nbsp;</span>
                            </div>
                        </div>
                    </div>
                    <!-- ROW5 -->
                    <div class="d-flex">
                        <!-- LEFT -->
                        <div class="__half_left tw-w-1/2">
                            <div class="__form_input">
                                <span class="__label __global_text_font">Location</span>
                                <AppCombobox
                                    class="w-full __global_text_font"
                                    clearable
                                    v-model="formData.location"
                                    :items="locationItems"
                                    item-title="name"
                                    item-value="id"
                                    @blur="isFormComplete"
                                >
                                </AppCombobox>
                            </div>
                        </div>
                        <!--------------------------------- RIGHT ------------------------------------>
                        <div class="__half_right tw-w-1/2">
                            <div class="__form_input">
                                <span class="__label __global_text_font">Select/Add Vaccination</span>
                                <SelectAddDropdownListV2
                                    v-model="formData.vaccinations"
                                    :items="vaccinationStore.data.vaccinations"
                                    item-title="name"
                                    item-value="id"
                                    @blur="isFormComplete"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- ROW6 -->
                    <div class="d-flex">
                        <!--------------------------------- LEFT ------------------------------------>
                        <div class="__half_left tw-w-1/2">
                            <div class="__form_input">
                                <span class="__label __global_text_font">Select/Add Certifications</span>
                                <SelectAddDropdownListV2
                                    v-model="formData.certifications"
                                    :items="complianceStore.data.compliances"
                                    @blur="isFormComplete"
                                />
                            </div>
                        </div>
                        <!--------------------------------- RIGHT ------------------------------------>
                        <div class="__half_right tw-w-1/2">
                            <div class="__form_input">
                                <span class="__label __global_text_font">Select/Add Allowances</span>
                                <SelectAddDropdownListV2
                                    v-model="formData.allowances"
                                    :items="allowanceStore.data.allowances"
                                    item-title="name"
                                    @blur="isFormComplete"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import locationApi from "@/api/location/locationApi";
import { useVaccinationStore } from "@/store/vaccinationStore";
import { useAllowanceStore } from "@/store/allowanceStore";
import InputNumberFormatCurrency from "./components/InputNumberFormatCurrency.vue";
import SelectAddDropdownList from "./components/SelectAddDropdownList.vue";
import SelectAddDropdownListV2 from "./components/SelectAddDropdownListV2.vue";

import complianceApi from "@/api/compliance/compliance";
import payClassification from "@/api/payClassification/payClassificationApi";
import payRangeApi from "@/api/payRange/payRangeApi";
import vaccinationApi from "@/api/vaccination/vaccination";
import { useComplianceStore } from "@/store/complianceStore";
import { useEmploymentStore } from "@/store/employmentStore";
import { usePositionStore } from "@/store/positionStore";
import { defineComponent } from "vue";
import allowanceApi from "@/api/allowance/allowanceApi"

export default defineComponent({
    name: 'ContractCustomisation',
    components: { SelectAddDropdownList, InputNumberFormatCurrency, SelectAddDropdownListV2 },
    data() {
        return {
            formData: {
                positionTitle: null,
                classification: null,
                salary: null,
                startDate: null,
                isContractFullTime: false,
                location: null,
                certifications: [],
                employmentBasis: null,
                endDate: null,
                vaccinations: [],
                allowances: []
            },
            locationItems: [],
            employmentBasisItems: [
                { id: 1, name: 'Full time, Continuing' },
                { id: 2, name: 'Full time, Limited Tenure' },
                { id: 3, name: 'Part time, Continuing' },
                { id: 4, name: 'Part time, Limited Tenure' },
                { id: 5, name: 'Casual' },
            ],
            classificationItems: [
                { id: 1, name: 'Sample E340' },
                { id: 2, name: 'Sample E440' },
                { id: 3, name: 'Sample E540' },
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
            selectAddAllowanceItems: [
                { id: 1, name: "Sample Allowance 1" },
                { id: 2, name: "Sample Allowance 2" },
                { id: 3, name: "Sample Allowance 3" },
                { id: 4, name: "Sample Allowance 4" },
                { id: 5, name: "Sample Allowance 5" },
                { id: 6, name: "Sample Allowance 6" },
                { id: 7, name: "Sample Allowance 7" },
            ],
            classifications: [],
            minPay: 0,
            maxPay: null,
            payRange: null,
            localPayRange: null,
            allowances: null
        };
    },
    watch: {
        'formData.positionTitle'(newVal, oldVal) {
            this.getVaccinations(newVal.name)
            this.getCompliances(newVal.name)
            this.formData.classification = newVal.pay_classifications_data ?? null
            this.formData.salary = newVal.pay_classifications_data.annual_salary ?? null
        },
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
                this.formData.vaccinations =  response.data//response.data.map(item => item.id)

                this.loadItems = false
                if(Array.isArray(response.data) && response.data.length === 0) {
                    this.$toast?.info?.('The selected Position & Title does not have Vaccination settings.', {duration: 10000});
                }
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

                if(Array.isArray(response.data) && response.data.length === 0) {
                    this.$toast?.info?.('The selected Position & Title does not have Compliance settings.', {duration: 10000});
                }
            } catch (error) {
                console.error(error)
            }
        },
        async isFormCompleteInPayClassification() {
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

            this.isFormComplete()
        },
        async loadClassifications() {
            try {
                const res = await payClassification.fetchActiveClassifications();
                this.classifications = res.data ? res.data : res;

                if(Array.isArray(res.data) && res.data.length === 0) {
                    this.$toast?.info?.('The selected Position & Title does not have Pay Classification in Admin settings.', {duration: 10000});
                }
            } catch (e) {
                console.error("Failed loading pay classifications:", e);
            }
        },
        loadResources() {
            this.loadLocations()
        },
        async isFormComplete() {
            const {
                positionTitle,
                classification,
                salary,
                startDate,
                location,
                employmentBasis
            } = this.formData;

            // Check scalar values (not null or empty string)
            const basicFieldsFilled =
                positionTitle &&
                salary &&
                startDate &&
                location &&
                classification &&
                employmentBasis;

            // Check arrays (must not be empty)
            const arraysFilled =
                this.formData.certifications.length > 0 &&
                this.formData.vaccinations.length > 0 //&&
                //this.formData.allowances.length > 0;
            if( basicFieldsFilled && arraysFilled){
                if( (this.formData.isContractFullTime === false && this.formData.endDate !== null ) || (this.formData.isContractFullTime) ){

                    //this.formData.allAllowances = await this.getAllowances()
                    this.$emit('form-data', this.formData)
                }
            }else{
                this.$emit('form-data', null)
            }
        },
        async loadLocations() {
            this.locationItems = await locationApi.fetchLocations()
        },
        getMinimumDate(){
            return new Date().toISOString().split('T')[0];
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

            } catch (e) {
                console.error("Failed loading pay classifications:", e);
            }
        },
    },
    mounted: async function () {
        await this.loadClassifications()
        //await this.loadPayRange()

        this.loadResources()
        await this.vaccinationStore.setVaccinations()

        this.positionStore = usePositionStore()

        await this.positionStore.setPositions()
        await this.employmentStore.setEmployments()
        await this.complianceStore.setCompliances()
        await this.allowanceStore.setAllowances()

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
        min-height: 500px!important;
    }

    .__half_left {
        padding: 5px 30px 5px 0px;
    }

    .__half_right {
        padding: 5px 30px 5px 0px;
    }

    .__form_input {
        margin-bottom: 20px;
    }
</style>
