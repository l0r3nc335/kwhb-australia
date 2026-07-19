<template>
    <VContainer fluid>
          <VTimeline 
            side="end"
            density="comfortable"
            truncate-line="both"
            align="start"
          >
            <VTimelineItem
              v-for="(event, index) in props.events"
              :key="index"
              dot-color="primary"
              size="small"
              density="compact"
              height="80"
            >
                <span class="float-right font-weight-medium">{{ dateToday(event.created_at) }}</span>
                <h3 class="font-weight-bold">{{ event.action_name }}</h3>
                <span class="font-weight-regular">{{ event.description }}</span>
            </VTimelineItem>
          </VTimeline>
    </VContainer>
  </template>
  
  <script setup>
  import moment from "moment";
  const props = defineProps({
    events: Object,
  })

  const dateToday = (date) => {
    var now = moment();
    var publishedDate = moment(new Date(date)); 
    return moment.duration(publishedDate.diff(now)).humanize(true) 
  }
  </script>