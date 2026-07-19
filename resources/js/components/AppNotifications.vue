<template>
    <IconBtn id="notification-btn">
        <VBadge
            v-bind="badgeProps"
            color="error"
            :content="unreadCount"
            class="notification-badge"
        >
            <VIcon
                size="26"
                icon="tabler-bell"
            />
        </VBadge>

        <VMenu
            activator="parent"
            width="380px"
            :location="location"
            offset="14px"
            :close-on-content-click="false"
        >
            <VCard class="d-flex flex-column">
                <!-- 👉 Header -->
                <VCardItem class="notification-section">
                    <VCardTitle class="text-lg">
                        Notifications
                    </VCardTitle>

                    <template #append>
                        <IconBtn
                            v-show="notifications.length"
                            @click="markAllReadOrUnread"
                        >
                            <VIcon :icon="!isAllMarkRead ? 'tabler-mail' : 'tabler-mail-opened' " />

                            <VTooltip
                                activator="parent"
                                location="start"
                            >
                                {{ !isAllMarkRead ? 'Mark all as unread' : 'Mark all as read' }}
                            </VTooltip>
                        </IconBtn>
                    </template>
                </VCardItem>

                <VDivider />

                <!-- 👉 Notifications list -->
                <PerfectScrollbar
                    :options="{ wheelPropagation: false }"
                    style="max-block-size: 23.75rem;"
                >
                    <VList class="notification-list rounded-0 py-0">
                        <template
                            v-for="(notification, index) in notifications"
                            :key="notification.title"
                        >
                            <VDivider v-if="index > 0" />
                            <VListItem
                                link
                                lines="one"
                                min-height="66px"
                                class="list-item-hover-class"
                                @click="goToNotificationLink(notification.link)"
                            >
                                <!-- Slot: Prepend -->
                                <!-- Handles Avatar: Image, Icon, Text -->
                                <template #prepend>
                                    <VListItemAction start>
                                        <VAvatar
                                            size="40"
                                            :color="notification.color && notification.icon ? notification.color : undefined"
                                            :image="getEmployeeProfileImage(notification.user.employee) || undefined"
                                            :icon="notification.icon || undefined"
                                            :variant="notification.img ? undefined : 'tonal' "
                                        >
                                            <span v-if="notification.text">{{ notification.text }}</span>
                                        </VAvatar>
                                    </VListItemAction>
                                </template>

                                <VListItemTitle>{{ notification.title }}</VListItemTitle>
                                <VListItemSubtitle>
                                    <div v-html="notification.content"></div>
                                </VListItemSubtitle>
                                <span class="text-xs text-disabled">{{ getHumanReadableTime(notification.created_at) }}</span>

                                <!-- Slot: Append -->
                                <template #append>
                                    <div class="d-flex flex-column align-center gap-4">
                                        <VBadge
                                            dot
                                            :color="!notification.isSeen ? 'primary' : '#a8aaae'"
                                            :class="`${notification.isSeen ? 'visible-in-hover' : ''} ms-1`"
                                            @click.stop="$emit(notification.isSeen ? 'unread' : 'read', [notification.id])"
                                        />

                                        <div style="block-size: 28px; inline-size: 28px;">
                                            <IconBtn
                                                size="small"
                                                class="visible-in-hover"
                                                @click="$emit('remove', notification.id)"
                                            >
                                                <VIcon
                                                    size="20"
                                                    icon="tabler-x"
                                                />
                                            </IconBtn>
                                        </div>
                                    </div>
                                </template>
                            </VListItem>
                        </template>

                        <VListItem
                            v-show="!notifications.length"
                            class="text-center text-medium-emphasis"
                            style="block-size: 56px;"
                        >
                            <VListItemTitle>No Notification Found!</VListItemTitle>
                        </VListItem>
                    </VList>
                </PerfectScrollbar>

                <VDivider />

                <!-- 👉 Footer -->
                <VCardActions
                    v-show="notifications.length"
                    class="notification-footer"
                >
                    <VBtn @click="loadNotifications" block>
                        Load More
                    </VBtn>
                </VCardActions>
            </VCard>
        </VMenu>
    </IconBtn>
</template>

<script>
    import notificationApi from "@/api/notification/notificationApi";

    export default {
        emits: [
            'read',
            'unread',
            'remove',
            'click:notification'
        ],

        props: {
            badgeProps: {
                type: null,
                required: false,
                default: undefined,
            },

            location: {
                type: null,
                required: false,
                default: 'bottom end',
            },
        },

        data() {
            return {
                notifications: [],
                unreadCount: 0,
                pagination: {
                    page: 1,
                    perPage: 5
                }
            }
        },

        mounted() {
            this.loadNotifications()
        },

        computed: {
            isAllMarkRead() {
                return this.unreadCount === 0
            }
        },

        methods: {
            goToNotificationLink(link) {
                window.location.href = link
            },

            markAllReadOrUnread(id) {

            },

            async loadNotifications(next = false) {
                if (next) {
                    this.pagination.page++;
                }
                let data = await notificationApi.fetchNotifications(this.pagination.page, this.pagination.perPage)
                this.unreadCount = data.unread_count;
                this.notifications = [...this.notifications, ...data.notifications.data]
            }
        }
    }
</script>

<style lang="scss">
.notification-section {
    padding: 14px !important;
}

.notification-footer {
    padding: 6px !important;
}

.list-item-hover-class {
    .visible-in-hover {
        display: none;
    }

    &:hover {
        .visible-in-hover {
            display: block;
        }
    }
}

.notification-list.v-list {
    .v-list-item {
        border-radius: 0 !important;
        margin: 0 !important;
    }
}

// Badge Style Override for Notification Badge
.notification-badge {
    .v-badge__badge {
        /* stylelint-disable-next-line liberty/use-logical-spec */
        min-width: 18px;
        padding: 0;
        block-size: 18px;
    }
}
</style>
