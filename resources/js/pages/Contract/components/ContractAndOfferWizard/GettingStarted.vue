<template>
    <div class="d-flex" style="justify-content: space-between; margin-top: 20px;">
        <div class="__left_menu">
            <VList>
                <VListItem
                    v-for="(item, i) in menuItems"
                    :key="i"
                    clickable
                    @click="handleClick(item.key)"
                >
                    <template #prepend>
                        <VIcon :size="25" :icon="item.icon" class="me-3" />
                    </template>

                    <VListItemTitle>
                        {{ item.name }}
                    </VListItemTitle>
                </VListItem>
            </VList>
        </div>

        <div class="__right_menu">
            <Templates
                :forCreating="1"
                @templateSelected="handleTemplateClick"
                :type="selectedView"
                :outsideOverlay="false"
                :selectedTemplateProp="selectedTemplate"
            />
        </div>

    </div>
</template>

<script>

import Templates from "@/pages/Contract/components/Templates.vue";
import { defineComponent } from "vue";

export default defineComponent({
    name: 'GettingStarted',
    components: { Templates },
    props: {
        selectedView: {
            type: String, // or whatever type `type` is meant to be
            required: false,
        },
        selectedTemplate: {
            type: null,
            required: false,
            default: null
        },
    },
    data() {
        return {
            menuItems: [
                { id: 1, name: 'All', icon: 'tabler-folder', key: 'all' },
                { id: 2, name: 'Shared With Me', icon: 'tabler-folder-heart', key: 'sharedWithMe' },
                { id: 3, name: 'Created By Me', icon: 'tabler-folder-root', key: 'createdByMe' },
            ]
        }
    },
    methods: {
        handleTemplateClick(template) {
            this.$emit('templateSelected', template);
        }
    }
})
</script>

<style>
    .__left_menu {
        /* border: 1px solid red; */
        width: 20%;
    }

    .__right_menu {
        /* border: 1px solid greenyellow; */
        width: 80%;
    }

</style>
