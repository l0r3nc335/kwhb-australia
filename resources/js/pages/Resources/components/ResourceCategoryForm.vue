<template>
    <div class="tw-flex tw-flex-col tw-p-4">
        <div class="tw-flex tw-flex-col tw-py-2 tw-my-2">
            <VTextField v-model="name" placeholder="Name" class="mb-4"></VTextField>
            <VFileInput v-model="image" size="small" prepend-inner-icon="tabler-file" accept="image/*" :prepend-icon="false" label="Select category image..."/>
        </div>
        <div class="tw-flex tw-justify-end">
            <VBtn class="tw-mr-2" color="secondary" @click="$emit('close')">Cancel</VBtn>
            <VBtn color="success" :disabled="!formValidated" @click="save">Save</VBtn>
        </div>
    </div>
</template>

<script>
import resourceApi from "@/api/resources/resourceApi";

export default {
    props: {
        category: {
            default: null
        }
    },

    data() {
        return {
            name: null,
            image: null
        }
    },

    computed: {
        formValidated() {
            return this.name
        }
    },

    methods: {
        resetForm() {
            this.name = null
        },

        async save() {
            let form = new FormData();
            let image = this.image?.length > 0 ? this.image[0] : null

            form.append('name', this.name)
            form.append('image', image)

            await resourceApi.saveCategory(form)
            this.resetForm()

            this.$toast.success('Resource Category saved!')
            this.$emit('close')
            this.$emit('reload-categories')
        }
    }
}
</script>
