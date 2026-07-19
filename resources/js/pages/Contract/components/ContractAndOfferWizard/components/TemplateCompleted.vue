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
                <span v-if="submitType === 'draft'">Saved as Draft</span>
                <span v-else>Saved as Draft</span>
            </div>
            <div class="tw-flex tw-text-center tw-py-6 tw-px-16 __message">
                <span v-if="submitType === 'draft'" style="width: 350px;" class="tw-text-center">This contract/offer was saved as draft successfully.</span>
                <span v-else style="width: 350px;" class="tw-text-center">Submitted to Sinon Cooney for approval.</span>
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
    name: 'TemplateCompleted',
    props: {
        modelValue: {
            type: Boolean,
            required: true
        },
        submitType: {
            type: null,
            required: true
        },
    },
    methods: {
        waitUntilReady() {
            return new Promise((resolve) => {
                setTimeout(() => {
                    resolve();
                }, 0);
            });
        },
    }
})
</script>

<style scoped>
.__message {
    display: grid;
    place-items: center;
    height: 100px;
}
</style>
