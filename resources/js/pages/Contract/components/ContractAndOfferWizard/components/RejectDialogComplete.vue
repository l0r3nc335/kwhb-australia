<template>
    <VDialog v-model="modelValue" width="700">
        <div class="tw-flex tw-flex-col tw-bg-white tw-p-6 tw-rounded-lg">
            <div class="tw-flex tw-justify-end">
                <div class="tw-py-2 tw-px-3 tw-rounded-lg tw-bg-gray-200" @click="$emit('update:modelValue', false)">
                    <VIcon icon="tabler-x"></VIcon>
                </div>
            </div>
            <div class="tw-flex tw-justify-center">
                <div class="tw-p-4 tw-rounded-full tw-my-8" style="background-color: #b0e1bb">
                    <VIcon icon="tabler-check" class="tw-text-[4rem] tw-text-emerald-500"></VIcon>
                </div>
            </div>
            <div class="tw-flex tw-justify-center tw-font-bold tw-text-[2rem]">
                You rejected the {{ selectedView === 'letter_of_offer' ? 'Letter of Offer' : 'Contract'}}
            </div>
            <div class="tw-flex tw-text-center tw-py-6 tw-px-16 __message">
                <span style="width: 300px;" class="tw-text-center">The sender has been notified. If you have any questions, please contact them.</span>
            </div>
            <div class="tw-flex tw-justify-center tw-my-5">
                <VBtn @click="$emit('update:modelValue', false)" class="tw-px-4">OK</VBtn>
            </div>
        </div>
    </VDialog>
</template>

<script>
import { defineComponent } from "vue"

export default defineComponent({
    name: 'RejectDialogComplete',
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
    data() {
        return {
            htmlSignaturePageContent: null,
        }
    },
})
</script>

<style scoped>
    .__message {
        display: grid;
        place-items: center;
        height: 100px;
    }
</style>
