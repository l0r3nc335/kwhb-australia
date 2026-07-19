<template>
    <VCard class="card-items-wrapper hover:tw-cursor-pointer hover:tw-shadow-lg" :class="division.id == selectedItem ? 'tw-border' : ''" @click="select(division.id)">
        <VCardText class="d-flex align-center pb-1 ">
            <span>Total <b>{{ user_total_count }}</b> Users</span>

            <VSpacer />

            <div class="v-avatar-group">
                <template
                    v-for="(user, index) in users"
                    :key="user"
                >
                    <VAvatar
                        v-if="user_total_count > 4 && user_total_count !== 4 && index < 4"
                        size="36"
                        :image="getEmployeeProfileImage(user.employee)"
                        :title="`${user.employee.first_name} ${user.employee.last_name}`"
                    />

                    <VAvatar
                        v-if="user_total_count <= 4"
                        size="36"
                        :image="getEmployeeProfileImage(user.employee)"
                        :title="`${user.employee.first_name} ${user.employee.last_name}`"
                    />
                </template>
                <VAvatar
                    v-if="user_total_count > 4"
                    :color="$vuetify.theme.current.dark ? '#4A5072' : '#f6f6f7'"
                >
              <span>
                +{{ user_total_count - 4 }}
              </span>
                </VAvatar>
            </div>
        </VCardText>

        <VCardText class="pb-5 mb-3">
            <h4 class="text-h4 font-weight-bold" :class="division.id == selectedItem ? 'tw-text-green-600' : ''" >
                {{ division.name }}
            </h4>
        </VCardText>
    </VCard>
</template>
<script>
    export default {
        emits: ['select'],

        props: {
            division: {
                type: Object,
                required: true
            },
            users: {
                type: Array,
                required: true
            },
            user_total_count: {
                type: Number,
                required: true
            },
            selectedItem: {
                required: false,
                default: null
            }
        },

        data() {
            return {
                loading: false
            }
        },

        methods: {
            select(id) {
                this.$emit('select', id)
            }
        }
    }
</script>
