<template>

    <!--VList v-model:selected="selectedKey"-->
    <VList>
        <VListItem
            v-for="(item, i) in menuItems"
            :key="i"
            :value="item.key"
            @click="handleClick(item.key)"
            clickable
            active-class="v-list-item--active"
            :class="['custom-active', selectedKey === item.key ? 'v-list-item--active' : '']"
        >
            <template #prepend>
                <VIcon
                    :size="25"
                    :icon="item.icon"
                    class="me-3"
                />
            </template>

            <VListItemTitle>
                <span class="__global_text_font">{{ item.name }}</span>
            </VListItemTitle>
        </VListItem>
    </VList>

</template>
<script>
  export default {
    props: {
      selectedView: {
        type: String,
        default: ''
      }
    },
    mounted() {
        this.selectedKey = this.selectedView === 'contract'? 'contracts' : this.selectedView
        this.$emit('updateSelectedView', this.selectedKey);
        this.handleClick(this.selectedView)
        //this.selectedView === 'contract' ? 'contracts' : this.selectedView,
    },
    data() {
      return {
        selectedKey: this.selectedView,
        menuItems: [
          { id: 1, name: 'Shared With Me', icon: 'tabler-folder-heart', key: 'sharedWithMe' },
          { id: 2, name: 'Archived', icon: 'tabler-archive', key: 'archived' },
          { id: 3, name: 'Published', icon: 'tabler-file-certificate', key: 'published' },
          { id: 4, name: 'Letter of Offer', icon: 'tabler-file', key: 'letterOfOffer' },
          { id: 5, name: 'Contracts', icon: 'tabler-contract', key: 'contracts' },
        ],
      };
    },
    methods: {
      handleClick(item) {
          this.selectedKey = item === 'contract'? 'contracts' : item;
          this.$emit('updateSelectedView', item);
      },
    }
  }
</script>

<style scoped>
.v-list-item--active {
    background-color: #001871!important;
    border-radius: 6px;
}
</style>
