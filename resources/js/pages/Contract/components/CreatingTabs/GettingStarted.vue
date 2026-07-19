<template>
    <div class="d-flex" style="justify-content: space-between;">
        <!-- Sidebar: Menu Items -->
        <div style="width: 20%; padding-right: 20px;">
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
        
        <!-- Main Content Area: 80% Width -->
        <div style="width: 80%;">
            <Templates :forCreating="1" @templateSelected="handleTemplateClick" />
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import Templates from '@/pages/Contract/components/Templates.vue';

const menuItems = ref([
  { id: 1, name: 'All', icon: 'tabler-folder', key: 'all' },
  { id: 2, name: 'Shared With Me', icon: 'tabler-folder-heart', key: 'sharedWithMe' },
  { id: 3, name: 'Created By Me', icon: 'tabler-folder-root', key: 'createdByMe' },
]);

const handleClick = (key) => {
  console.log(`Item clicked: ${key}`);
};

const handleTemplateSelection = (template) => {
  console.log('Selected Template:', template);
};

const props = defineProps();
const emit = defineEmits(['templateSelected']);

const handleTemplateClick = (template) => {
  emit('templateSelected', template);
};
</script>
