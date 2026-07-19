<template>

    <!-- First Row: Two columns side by side -->
    <div class="tw-flex tw-w-full">
        <div class="tw-w-1/2 tw-bg-bluess-200 tw-p-4">
            <AppSelect
                v-model="signatureFont"
                :items="['pacifico-regular', 'caveat-regular', 'playwrite-hu-font', 'playwrite-hr-font ', 'playwrite-in-font']"
                style="width: 190px!important;"
                class="select-white-active"
            />
        </div>
        <div class="tw-w-1/2 tw-bg-greenss-200 tw-p-4 tw-flex tw-justify-end tw-gap-1">
            <button
                class="tw-w-4 tw-h-4 tw-bg-black tw-shadow-sm tw-rounded-full"
                @click="setInkColor('__font_black')"
            ></button>
            <button
                class="tw-w-4 tw-h-4 tw-bg-blue-600 tw-shadow-sm tw-rounded-full"
                @click="setInkColor('__font_blue')"
            ></button>
            <button
                class="tw-w-4 tw-h-4 tw-bg-orange-600 tw-shadow-sm tw-rounded-full"
                @click="setInkColor('__font_orange')"
            ></button>
        </div>
    </div>

    <div class="tw-w-full tw-bg-yellowss-200 tw-p-4 tw-mt-2 tw-flex tw-flex-col" style="height: 170px;">
        <div class="tw-mt-auto tw-mb-8">
            <div ref="textSignatureContainer" class="d-flex justify-content-center align-items-center tw-pt-10">
                <input
                    type="text"
                    placeholder="Type your signature here"
                    :class="['tw-w-full tw-text-center tw-border-0 tw-border-b tw-border-gray-400 tw-bg-transparent focus:tw-outline-none focus:tw-border-b-black tw-text-[24px] d-flex justify-content-center align-items-center', signatureFont, fontColorClass]"
                    style="border-bottom: 1px solid #B0B2BB;"
                    v-model="textSignature"
                />
            </div>
        </div>
    </div>
</template>


<script>
import { defineComponent } from 'vue';
import html2canvas from 'html2canvas';
import { requiredValidator } from "@validators"

export default defineComponent({
    name: 'TypeSignature',
    props: {
        modelValue: {
            type: Number, // this is activeTab
            required: false,
        },
    },
    emits: ['set-font-signature'],
    data() {
        return {
            font: null,
            textSignature: null,
            signatureFont: 'pacifico-regular',
            fontColorClass: '__font_black'
        };
    },
    mounted() {

    },
    watch: {
        textSignature(newVal) {
            this.$emit('set-font-signature', newVal);
        }
    },
    methods: {
        async getTextSignatureImage() {
            const inputContainer = this.$refs.textSignatureContainer;
            const canvas = await html2canvas(inputContainer, {
                backgroundColor: null, // transparent background
                scale: 5,              // higher res
                useCORS: true
            });

            const base64Image = canvas.toDataURL('image/png');
            return base64Image;
        },
        setInkColor(color){
            this.fontColorClass = color
        },
    },
});
</script>


<style scoped>

:deep(.no-border-select .v-input__control) {
    border-bottom: none !important;
    box-shadow: none !important;
}

:deep(.no-border-select .v-field__outline) {
    border: none !important;
}

.pacifico-regular {
    font-family: "Pacifico", cursive!important;
    font-weight: 400!important;
    font-style: normal!important;
}
.caveat-regular {
    font-family: "Caveat", cursive!important;
    font-weight: 400!important;
    font-style: normal!important;
    font-optical-sizing: auto!important;
}

.ballet-font {
    font-family: "Ballet", cursive!important;
    font-optical-sizing: auto!important;
    font-weight: 400!important;
    font-style: normal!important;
    font-size: 50px!important;
}

.playwrite-hu-font {
   font-family: "Playwrite HU", cursive!important;
   font-optical-sizing: auto;
   font-weight: 400!important;
   font-style: normal;
}

.playwrite-hr-font {
   font-family: "Playwrite HR", cursive!important;
   font-optical-sizing: auto!important;
   font-weight: 400!important;
   font-style: normal!important;
}

.playwrite-in-font {
   font-family: "Playwrite IN", cursive!important;
   font-optical-sizing: auto!important;
   font-weight: 400!important;
   font-style: normal!important;
}

.__font_black {
    color: #000000!important;
}

.__font_blue {
    color: #2563EB!important;
}

.__font_orange {
    color: #EA580C!important;
}

:deep(.select-white-active .v-list-item--active) {
    color: white !important;
}
.v-list-item--active .v-list-item-title {
    color: white!important;
}
</style>
