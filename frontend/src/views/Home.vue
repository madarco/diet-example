<template>
  <div class="home">
    <b-form inline class="m-2" @submit="onSubmit">
      <b-form-group
          id="fieldset-1"
          label-for="input-1"
          invalid-feedback="Date To should be prior Date From"
          :state="newDateValid"
      >
        <b-form-datepicker id="example-datepicker1" v-model="newDateFrom" :max="newDateFromMax" :state="newDateValid" class="mb-2 mr-sm-2 mb-sm-0"></b-form-datepicker>
      </b-form-group>
      <b-form-datepicker id="example-datepicker2" v-model="newDateTo" :max="newDateToMax" :state="newDateValid" class="mb-2 mr-sm-2 mb-sm-0"></b-form-datepicker>
      <b-button type="submit" variant="primary">Search</b-button>
    </b-form>

    <div>Your daily limit: {{ calories.limit }}</div>

    <b-table striped hover :items="foodEntries" :fields="fields">
      <template #cell(eatDate)="data">
        <span :id="'tooltip-target-' + data.item.id">
          {{ new Date(data.item.eatDate) | moment("from", "now") }}
          <b-badge variant="warning"v-if="dayOverLimit(data.item.eatDate)">Over limit</b-badge>
        </span>
        <b-tooltip :target="'tooltip-target-' + data.item.id" triggers="hover">
          {{ new Date(data.item.eatDate) }}
        </b-tooltip>
      </template>
    </b-table>
  </div>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';
import {
  State,
  Getter,
  Action,
  Mutation,
  namespace
} from 'vuex-class'
import moment from 'moment';

@Component({
  components: {
  },
})
export default class Home extends Vue {
  @State foodEntries;
  @State calories;
  @State dateFrom;
  @State dateTo;

  @Action getFoodEntries;
  @Action getCalories;

  @Mutation setDateFrom
  @Mutation setDateTo

  get newDateFrom() {
    return this.dateFrom;
  }
  set newDateFrom(from) {
    this.setDateFrom(from);
  }

  get newDateTo() {
    return this.dateTo;
  }
  set newDateTo(to) {
    this.setDateTo(to);
  }

  mounted() {
    this.search();
  }

  onSubmit(event) {
    event.preventDefault();
    this.search();
  }

  search() {
    this.getFoodEntries();
    this.getCalories();
  }

  get fields() {
    let names = ['name', 'eatDate', 'calories', 'skipDiet'];
    if (true) {
      names.push('user');
    }
    return names;
  }

  dayOverLimit(date) {
    if (!this.calories) return false;

    const dateString = moment(date).format("YYYY-MM-DD");
    const entry = this.calories.entries.find(e => e.dateDay == dateString);
    return entry.calories > this.calories.limit;
  }

  get newDateFromMax() {
    return this.newDateTo ? this.newDateTo :  new Date();
  }

  get newDateValid() {
    return this.newDateFrom === null || this.newDateTo === null || this.newDateFrom <= this.newDateTo;
  }

  get newDateToMax() {
    return new Date();
  }
}
</script>
