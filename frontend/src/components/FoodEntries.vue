<template>
  <div>
    <b-table v-if="foodEntries.length" striped hover :items="foodEntries" :fields="fields">
      <template #cell(eatDate)="data">
        <span :id="'tooltip-target-' + data.item.id">
          {{ new Date(data.item.eatDate) | moment("from", "now") }}
          <b-badge variant="warning"v-if="dayOverLimit(data.item.eatDate)">Over limit</b-badge>
        </span>
        <b-tooltip :target="'tooltip-target-' + data.item.id" triggers="hover">
          {{ new Date(data.item.eatDate) }}
        </b-tooltip>
      </template>
      <template #cell(user)="data">
        {{ getUsername(data.item.user) }}
      </template>
      <template #cell(calories)="data">
        {{ new Intl.NumberFormat().format(data.item.calories) }}
      </template>
      <template #cell(skipDiet)="data">
        <b-icon-check-square-fill v-if="data.item.skipDiet" variant="success"></b-icon-check-square-fill>
        <b-icon-x-square-fill v-else variant="danger"></b-icon-x-square-fill>
      </template>
      <template #cell(actions)="data">
        <b-button variant="info" @click="onEdit(data.item)" class="mr-1"><b-icon-pencil-square></b-icon-pencil-square></b-button>
        <b-button variant="danger" @click="onDeleteEntry(data.item.id)"><b-icon-trash></b-icon-trash></b-button>
      </template>
    </b-table>
    <div v-else class="m-4">
      <h4>No results found</h4>
    </div>
  </div>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';
import { State, Action, Mutation } from 'vuex-class'
import moment from 'moment';
import { BIconPencilSquare, BIconTrash, BIconCheckSquareFill, BIconXSquareFill } from 'bootstrap-vue'

/**
 * Components to handle the table with the list of entries
 */
@Component({
  components: {
    BIconTrash,
    BIconPencilSquare,
    BIconCheckSquareFill,
    BIconXSquareFill
  }
})
export default class FoodEntries extends Vue {
  @State foodEntries;
  @State calories;
  @State users;

  @Action deleteEntry;

  @Mutation setEntry;

  @State isAdmin;

  onDeleteEntry(id) {
    if(confirm("Are you sure?")) {
      this.deleteEntry(id);
    }
  }

  onEdit(entry) {
    this.setEntry(entry);
    this.$bvModal.show('modal-entry');
  }

  get fields() {
    let names = ['name', 'eatDate', 'calories', 'skipDiet'];
    if (this.isAdmin) {
      names.push('user');
    }
    names.push('actions');
    return names;
  }

  dayOverLimit(date) {
    if (!this.calories || !this.calories.entries || this.isAdmin) return false;

    const dateString = moment(date).format("YYYY-MM-DD");
    const entry = this.calories.entries.find(e => e.dateDay == dateString);
    return entry && entry.calories > this.calories.limit;
  }

  getUsername(id) {
    const user = this.users.find(u => u.id == id);
    if (user) return user.username;
  }
}
</script>
