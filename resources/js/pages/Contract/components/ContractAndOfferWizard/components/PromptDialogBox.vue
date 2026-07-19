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
                    <!--VIcon icon="tabler-check" class="tw-text-[4rem] tw-text-emerald-500"></VIcon-->
                    <VIcon icon="tabler-alert-triangle" class="tw-text-[4rem] tw-text-yellow-500"></VIcon>
                </div>
            </div>
            <div class="tw-flex tw-justify-center tw-font-bold tw-text-[2rem]">
                {{ message }}
            </div>
            <div class="tw-flex tw-text-center tw-py-1 tw-px-16 __message">
               {{ submessage}}
            </div>
            <!-- ACTION -->
            <div class="tw-flex tw-justify-center tw-space-x-[40px] tw-my-5">
                <VBtn
                    class="__no_button tw-px-4"
                    color="gray"
                    @click="$emit('update:modelValue', false)"
                >
                    No
                </VBtn>

                <VBtn
                    @click="() => {
                        $emit('decision', true);
                        $emit('update:modelValue', false);
                    }"
                    class="tw-px-4"
                >
                    Yes
                </VBtn>
            </div>
        </div>
    </VDialog>
</template>

<script>
import { defineComponent } from "vue"

export default defineComponent({
    name: 'PromptDialogBox',
    props: {
        modelValue: {
            type: Boolean,
            required: true
        },
        promptDialogBoxType: {
            type: null,
            required: false
        },
        message: {
            type: null,
            required: true
        },
        submessage: {
            type: null,
            required: false
        },
    },
    data() {
        return {
        }
    },
});
</script>

<style scoped>
.__message {
    display: grid;
    place-items: center;
    height: 50px;
}

.__no_button {
    border-radius:6px;
    color: #858587;
    background-color: #dfe0e3;
}
</style>
