<template>
    <VDialog v-model="modelValue" width="620">
        <div class="tw-flex tw-flex-col tw-bg-white tw-p-6 tw-rounded-lg">
            <div class="tw-flex tw-justify-between">

                <div class="tw-py-2 tw-px-3">
                    <VIcon
                        class="tw-rounded-lg __caution_icon"
                        icon="tabler-alert-triangle"
                    />
                    &nbsp;&nbsp;
                    <strong v-if="dialogRejectMessage === null && rejectLocalStatus !== 'rejected'">Caution</strong>
                    <strong v-else>Reason for rejection:</strong>
                </div>

                <div class="tw-py-2 tw-px-3" @click="$emit('update:modelValue', false)">
                    <VIcon icon="tabler-x"></VIcon>
                </div>
            </div>

            <!-- Dialog message -->
            <div class="__dialog_message">
                <span v-if="dialogRejectMessage === null && rejectLocalStatus !== 'rejected'">
                    If you select <strong>CONTINUE</strong>, this document will be declared void.<br><br>
                    To request changes to this document, please select <strong>CANCEL</strong> and contact the sender directly with your request.<br><br>
                    You can exit signing and save the information that you've entered by using the <strong>FINISH LATER</strong> option in the <strong>MORE OPTION</strong> menu.
                </span>
                <span v-else>
                    {{dialogRejectMessage ?? rejectedLocalMessage}}
                </span>
            </div>

            <div class="tw-flex tw-justify-center tw-my-5">
                <VBtn
                    v-if="dialogRejectMessage === null && rejectLocalStatus !== 'rejected'"
                    class="tw-px-4"
                    style="height: 30px!important; width: 100px!important;"
                    @click="handleContinueRejection"
                >
                    Continue
                </VBtn>
                <VBtn
                    class="tw-px-4 tw-ms-3"
                    color="#CCCCCC"
                    style="height: 30px!important; width: 80px!important;"
                    @click="$emit('update:modelValue', false)"
                >
                    <span v-if="dialogRejectMessage === null && rejectLocalStatus === null">Cancel</span>
                    <span v-else>Close</span>
                </VBtn>
            </div>
        </div>
    </VDialog>
</template>

<script>
import { defineComponent } from "vue"

export default defineComponent({
    name: 'RejectDialogBox',
    props: {
        modelValue: {
            type: Boolean,
            required: true
        },
        dialogRejectMessage: {
            type: null,
            required: false
        },
        rejectLocalStatus: {
            type: null,
            required: false
        },
        rejectedLocalMessage: {
            type: null,
            required: false
        }
    },
    data() {
        return {
            htmlSignaturePageContent: null,
        }
    },
    methods: {
        handleContinueRejection() {
            this.$emit('reject-action');
            //this.$emit('update:modelValue', false);
        }
    }
})
</script>

<style scoped>

.__caution_icon {
    color: red!important;
    background-color: #FCF1E8;
    font-size: 32px;
    padding: 5px;
}

.__dialog_message {
    padding: 0 60px;
    font-family: Arial, sans-serif;
    font-size: 16px;
    line-height: 1.1;
}
</style>
