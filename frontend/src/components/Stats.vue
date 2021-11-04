<template>
  <div>
    <div v-if="!isAdmin">Your daily limit: {{ calories.limit }}</div>
    <div v-if="isAdmin && stats">
      Entries this week: {{ stats.entriesLast7Days }} - Past week: {{ stats.entriesLastWeek }}<br />
      Calories per User:
      <b-badge :variant="cycleColor(key)" class="mr-2" v-for="(user, key) in stats.averageCaloriesPerUser">{{ getUsername(user.user) }}: {{ user.calories }}</b-badge>
    </div>
  </div>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';
import { State } from 'vuex-class'

/**
 * Components to handle the stats section of the page
 */
@Component({})
export default class Stats extends Vue {
  @State calories;
  @State stats;
  @State users;

  @State isAdmin;

  getUsername(id) {
    const user = this.users.find(u => u.id == id);
    if (user) return user.username;
  }

  cycleColor(key) {
    const colors= ['primary', 'success', 'warning', 'danger', 'info'];
    return colors[key % colors.length];
  }
}
</script>
