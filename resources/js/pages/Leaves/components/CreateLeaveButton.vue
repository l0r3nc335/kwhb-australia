<template>
    <div>
        <VDialog v-model="showLeaveForm" width="900"  class="create-leave-button-wrapper" persistent>
            <template #activator="{props}">
                <VBtn :color="color" v-bind="props">{{label}}</VBtn>
            </template>
            <DialogCloseBtn @click="showLeaveForm = false" />
            <LeaveForm :request-permission="requestPermission" :user-id="employee?.user_id" @close="showLeaveForm = false" @reload-leaves="$emit('reload-leaves')"></LeaveForm>
        </VDialog>
    </div>
</template>

<script>
    import LeaveForm from "@/pages/Leaves/forms/LeaveForm.vue";

    export default {
        emits: ['reload-leaves'],

        components: {
            LeaveForm
        },

        props: {
            requestPermission: {
                type: String,
                default: 'leave_management'
            },

            label: {
                type: String,
                default: 'Apply For Leave'
            },

            color: {
                type: String,
                default: '#E25204'
            },

            employee: {
                default: null
            }
        },

        data() {
            return {
                showLeaveForm: false
            }
        },
    }
</script>

<style>
    .create-leave-button-wrapper.v-dialog > .v-overlay__content {
        max-height: max-content !important;
        position: absolute;
        top: 30px;
        padding-bottom: 50px;
    }

    .create-leave-button-wrapper.v-dialog {
        overflow: auto !important;
    }

    .create-leave-button-wrapper .v-overlay-scroll-blocked:not(html) {
        overflow-y: auto !important;
    }

    .create-leave-button-wrapper .v-overlay {
        pointer-events: all !important;
    }
</style>

