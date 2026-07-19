<template>
    <div v-if="isReady" class="tw-w-full">
        <AppCombobox
            :loading="loading"
            :label="label"
            v-model="selected"
            :items="tags"
            item-title="name"
            item-value="id"
            placeholder="Select Tag"
            prepend-inner-icon="tabler-tag"
            class="tw-w-full"
            clearable
        />
    </div>
</template>

<script>
import resourceApi from "@/api/resources/resourceApi";

export default {
    emits: ['selected'],

    props: {
        label: {
            default: 'Select Tag'
        },
        multiple: {
            type: Boolean,
            default: false
        },
        resource: {
            type: Object,
            required: true
        }
    },

    data() {
        return {
            loading: false,
            tags: [],
            selected: null,
            isReady: false, // Controls whether the combobox is rendered
        };
    },

    async mounted() {
        await this.loadTags();
        this.isReady = true; // Show combobox only after data is ready
    },

    methods: {
        async loadTags() {
            this.loading = true;

            this.tags = await resourceApi.fetchAllTags();

            if(this.resource){
                const tagResponse = await resourceApi.getTagsByResourceId(this.resource.id);

                // Set selected using matched tag(s)
                const selectedTags = tagResponse.tags.map(tagId =>
                    this.tags.find(tag => tag.id === tagId)
                ).filter(Boolean); // Remove undefineds

                this.selected = this.multiple ? selectedTags : selectedTags[0] || null;
            }
            this.loading = false;
        }
    },

    watch: {
        selected(newVal) {
            this.$emit('selected', newVal);
        }
    }
};
</script>
