<template>
    <VDialog v-model="modelValue" width="620">
        <div class="tw-relative tw-flex tw-flex-col tw-bg-white tw-p-6 tw-rounded-lg">

            <!-- Close Icon -->
            <div
                class="tw-absolute tw-top-8 tw-right-8 tw-cursor-pointer tw-bg-gray-50"
                style="background-color: #EBECEE!important; padding: 5px 5px; border-radius: 5px!important;"
                @click="$emit('update:modelValue', false)"
            >
                <VIcon icon="tabler-x" />
            </div>

            <!-- Centered Title -->
            <div class="tw-text-center tw-font-bold tw-text-[1.8rem] __global_font">
                Signature
            </div>

            <!-- Tabs -->
            <div class="tw-flex tw-justify-start tw-mt-6 tw-space-x-4"  style="height: 30px!important;">
                <button
                    v-for="(tab, index) in tabs"
                    :key="index"
                    @click="activeTab = index"
                    :class="[
                        'tw-pb-2 tw-border-b-2 tw-transition-all tw-duration-200 tw-text-[16px] tw-ps-3 tw-pe-3 __global_font',
                        activeTab === index ? 'tw-border-orange-500 tw-text-orange-600 __active_tab' : 'tw-border-transparent tw-text-gray-600'
                    ]"
                >
                    {{ tab }}
                </button>
            </div>

            <!-- Tab Content -->
            <div class="tw-mt-0 tw-pt-5" style="border-top: 1px solid #F2F2F2">
                <div class="__tab_content" v-if="activeTab === 0">
                    <DrawSignature
                        ref="drawSignatureRef"
                        v-model="activeTab"
                        @update:isCanvasBlank="isCanvasBlank = $event"
                    />
                </div>
                <div class="__tab_content" v-else-if="activeTab === 1">
                    <TypeSignature
                        ref="typeSignatureRef"
                        @set-font-signature="handleSetFontSignature"
                    />
                </div>
            </div>

            <div
                class="tw-mt-2 tw-text-justify tw-text-[.8rem]"
                style="display: inline-block; width: 100%; color: #868A99!important"
            >
                I understand that Katherine West Health Board Aboriginal Corporation uses my name, email address, and
                limited information will be used to complete the signature process, and to enhance the user experience.
                To learn more how Katherine West Health Board Aboriginal Corporation uses information, please see our
                Privacy Policy. By signing this document with an electronic signature, i agree that such signature will be
                as valid as handwritten signatures, and considered originals to the extent allowed by applicable law.
            </div>

            <div class="tw-flex tw-justify-between">
                <div>
                    <VBtn
                        class="tw-px-4 __cancel_left_button"
                        color="gray"
                        @click="$emit('update:modelValue', false)"
                    >
                        Cancel
                    </VBtn>
                </div>
                <div>
                    <VBtn
                        class="tw-px-4 __accept_right_button"
                        style="margin: 20px 10px;"
                        @click="signAndPreview"
                        :disabled="(activeTab === 0 && isCanvasBlank === true) || (activeTab === 1 && this.fontSignature === null)"
                    >
                        Accept and Sign
                    </VBtn>
                </div>
            </div>
        </div>
    </VDialog>

</template>

<script>
import { defineComponent } from "vue"
import DrawSignature
    from "@/pages/Contract/components/ContractAndOfferWizard/components/SignatureProcessor/DrawSignature.vue"
import contractApi from "@/api/contract/contractApi";
import TypeSignature
    from "@/pages/Contract/components/ContractAndOfferWizard/components/SignatureProcessor/TypeSignature.vue"

export default defineComponent({
    name: 'SignatureDialogBox',
    components: { TypeSignature, DrawSignature },
    props: {
        modelValue: {
            type: Boolean,
            required: true
        },
        selectedView: {
            type: String,
            required: false,
        },
    },
    beforeCreate() {
        this.isCanvasBlank = true
    },
    data() {
        return {
            htmlSignaturePageContent: null,
            tabs: ['Draw', 'Type'],
            activeTab: 0,
            isCanvasBlank: true,
            fontSignature: null
        }
    },
    methods: {
        handleSetFontSignature(textSignature) {
            if(textSignature === null || textSignature === ""){
                this.fontSignature = null
            }else{
                this.fontSignature = textSignature
            }
        },
        async signAndPreview() {
            if(this.activeTab === 0 && this.isCanvasBlank === false){
                const base64Image = this.$refs.drawSignatureRef.getCanvasImage();
                this.$emit('signature-image', base64Image); // emit to parent
                console.log("SIGNATURE DIALOG BOX - base64Image")
                console.log(base64Image)
                //formData.append('signature_image', base64Image);
                this.$emit('update:modelValue', false)
            }

            if(this.activeTab === 1 && this.fontSignature !== null) {
                //TODO: get the image
                const base64Image = await this.$refs.typeSignatureRef.getTextSignatureImage();
                this.$emit('signature-image', base64Image); // emit to parent
                console.log("SIGNATURE DIALOG BOX - base64Image")
                console.log(base64Image)
                //formData.append('signature_image', base64Image);
                this.$emit('update:modelValue', false)
            }
        },
        acceptAndSign(){
            try{
                const formData = new FormData();

                this.$refs.drawSignatureRef.getCanvasBlob((blob) => {
                    formData.append('signature_image', blob, 'signature.png');
                });

                this.$refs.drawSignatureRef.getCanvasBlob((blob) => {
                    if (blob) {
                        formData.append('signature_image', blob, 'signature.png');

                        axios.post('/submit', formData)
                            .then(() => alert('Submitted!'))
                            .catch(console.error);
                    }
                });
            }catch (error) {
                console.log(error)
            }
        }
    }
})
</script>

<style scoped>
.__message {
    display: grid;
    place-items: center;
    height: 100px;
}

.__active_tab {
    font-weight: bold;
    color: black!important;
    border-bottom: 2px solid orangered!important;
}

.__tab_content {
    height: 250px!important;
    background-color: #F2F2F2!important;
}

.__global_font {
    font-family: sans-serif, arial,sans-serif,Oswald!important
}

.__cancel_left_button {
    border-radius:6px;
    margin: 20px 0;
    padding: 0 15px!important;
    color: #858587;
    background-color: #dfe0e3;
}

.__accept_right_button {
    border-radius:6px;
    margin: 20px 0;
    padding: 0 15px!important;
    color: white;
}
</style>
