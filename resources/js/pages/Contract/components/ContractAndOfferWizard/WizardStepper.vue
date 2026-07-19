<template>
    <div class="d-flex">
        <!--VBtn
            icon
            class="__vbutton_back"
            :style="{ visibility: currentStep === 0 ? 'hidden' : 'visible' }"
            @click="$emit('backwardStep')"
        >
            <VIcon>mdi-arrow-left</VIcon>
        </VBtn-->
        <VSlideGroup
            v-model="currentStep"
            class="app-stepper"
            show-arrows
            :direction="direction"
        >
            <VSlideGroupItem
                v-for="(item, index) in items"
                :key="item.title"
                :value="index"
            >
                <div
                    class="cursor-pointer mx-1"
                    :class="[(!isActiveStepValid && isValidationEnabled) && 'stepper-steps-invalid', activeOrCompletedStepsClasses(index)]"
                    @click="!isValidationEnabled && updateCurrentStep(index) && this.selectedEmployee !== null"
                >
                    <template v-if="item.icon">IMPLEMENT ITEM WITH ICON</template>

                    <template v-else>
                        <div class="d-flex align-center gap-x-0" >
                            <div class="d-flex align-center">
                                <div class="d-flex align-center justify-center" style="block-size: 24px; inline-size: 24px;">
                                    <VIcon
                                        icon="mdi-checkbox-blank-circle"
                                        :class="[
                                            index === currentStep ? 'text-red-500' :
                                            index > currentStep ? 'text-grey-500' :
                                            'text-green-500'
                                          ]"
                                        size="10"
                                    />
                                </div>
                            </div>

                            <div style="line-height: 0;">
                                <h6 :class="['text-sm font-weight-medium step-title ', updateStepperItemDesign(index)]" style="margin-bottom: 4px!important;">{{ item.title }}</h6>
                                <!--span v-if="item.subtitle" class="text-xs step-subtitle" >{{ item.subtitle }}-</span-->
                            </div>

                            <VIcon icon="mdi-chevron-right" :class="['__v_icon_arrow_right', updateStepperItemDesign(index)]" size="30" />
                            <!--div v-if="isHorizontalAndNotLastStep(index)" class="stepper-step-line" /-->
                        </div>
                    </template>

                </div>
            </VSlideGroupItem>
        </VSlideGroup>
    </div>
</template>


<script>
import { defineComponent } from "vue"

export default defineComponent({
    name: 'WizardStepper',
    props: {
        items: {
            type: Array,
            required: true,
        },
        currentStep: {
            type: Number,
            required: false,
            default: 0,
        },
        isActiveStepValid: {
            type: Boolean,
            required: false,
            default: undefined,
        },
        iconSize: {
            type: [String, Number],
            required: false,
            default: 52,
        },
        parentCounter: {
          type: Number,
          required: false,
        },
        lastValidStep: {
          type: Number,
          required: false,
        },
        selectedEmployee: {
          type: null,
          required: false,
          default: null
        },
        customisationDataComplete: {
            type: Boolean,
            required: false,
        },
    },
    data() {
        return {
            direction: 'horizontal',
            currentStep: this.currentStep || 0,
        }
    },
    computed: {
        activeOrCompletedStepsClasses() {
            return (index) => index < this.currentStep
                ? 'stepper-steps-title-completed'
                : index === this.currentStep
                    ? 'stepper-steps-title-active'
                    : '';
        },
        isHorizontalAndNotLastStep() {
            return (index) => this.direction === 'horizontal' && this.items.length - 1 !== index;
        },
        isValidationEnabled() {
            return this.isActiveStepValid !== undefined;
        },
    },
    methods: {
        updateStepperItemDesign(index) {
            if(index === this.lastValidStep && index !== this.currentStep){
                return 'stepper-steps-title-completed'
            }

            return index < this.currentStep ||  (this.currentStep < this.lastValidStep && this.currentStep !== index && index < this.lastValidStep)
                ? 'stepper-steps-title-completed'
                : index === this.currentStep
                    ? 'stepper-steps-title-active'
                    : '__color_gray';
        },
        updateCurrentStep(step) {
            /*if(step === 2 && this.selectedEmployee === null){
                alert("Please select an employee!")
                return
            }
            if(step === 3 && this.customisationDataComplete === false){
                alert("Form is incomplete!")
                return
            }*/
            if(step <= this.lastValidStep){
                this.$emit('update:currentStep', step);
                this.$emit('update_currentStepContent', step);
            }
        },
    },
})

</script>


<style lang="scss">
.__color_gray {
    color: #a3a1a1 !important;
}
.stepper-steps-title-completed{
    color: #28C76F!important;
}

.stepper-steps-title-active {
    color: orangered!important;
    font-weight: 700!important;
    font: bold 16px Arial, Helvetica, sans-serif!important;
}

.app-stepper {
    height: fit-content;
}

.__v_icon_arrow_right {
    margin-left: 20px;
    margin-right: 5px;
    color: #28C76F;
    transform: scaleX(0.6);
}

.__vbutton_back {
  border-radius: 7px!important;
  background-color: #D9D9E0!important;
  height: 30px!important;
  width: 30px!important;
  color: black!important;
}
</style>
