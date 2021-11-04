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
      <b-button type="submit" class="mr-2" variant="primary">Search</b-button>
      - <b-button @click="createNew" class="ml-2" variant="success">Add Food Entry</b-button>
    </b-form>

    <div v-if="!isAdmin">Your daily limit: {{ calories.limit }}</div>
    <div v-if="isAdmin && stats">
      Entries this week: {{ stats.entriesLast7Days }} - Past week: {{ stats.entriesLastWeek }}<br />
      Calories per User:
      <b-badge :variant="cycleColor(key)" class="mr-2" v-for="(user, key) in stats.averageCaloriesPerUser">{{ getUsername(user.user) }}: {{ user.calories }}</b-badge>
    </div>

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
      <template #cell(actions)="data">
        <b-button variant="info" @click="onEdit(data.item)" class="mr-1">Edit</b-button>
        <b-button variant="danger" @click="onDeleteEntry(data.item.id)">X</b-button>
      </template>
    </b-table>
    <div v-else class="m-4">
      <h4>No results found</h4>
    </div>

    <b-modal id="modal-entry" hide-footer>
      <template #modal-title>
        Edit food entry
      </template>
      <div class="">
        <b-form @submit.prevent="onEditSave">
          <b-form-group v-if="isAdmin" id="input-group-3" label="User:" label-for="input-1">
            <b-form-select
                id="input-1"
                v-model="form.user"
                :options="users"
                value-field="id"
                text-field="username"
                required
            ></b-form-select>
          </b-form-group>

          <b-form-group id="input-group-2" label="Food Name:" label-for="input-2">
            <b-form-input
                id="input-2"
                v-model="form.name"
                placeholder="Enter food name"
                required
            ></b-form-input>
          </b-form-group>

          <b-form-group id="input-group-2" label="Calories count:" label-for="input-3">
            <b-form-input
                id="input-3"
                v-model="form.calories"
                type="number"
                min="1"
                placeholder="Enter calories count"
                required
            ></b-form-input>
          </b-form-group>

          <b-form-group id="input-group-2" label="When:" label-for="input-4">
            <b-form-datepicker id="input-4" v-model="form.date" :max="newDateToMax" class="mb-2"></b-form-datepicker>
            <b-form-timepicker v-model="form.time" locale="en" :state="validateTime" now-button></b-form-timepicker>
          </b-form-group>

          <b-form-group id="input-group-4" v-slot="{ ariaDescribedby }">
            <b-form-checkbox
                id="checkbox-1"
                v-model="form.skipDiet"
                name="checkbox-1"
                :value="true"
                :unchecked-value="false"
            >
              Skip this food <b-badge variant="success" class="mr-2">Cheat!</b-badge>
            </b-form-checkbox>
          </b-form-group>

          <b-button type="submit" class="mr-2" variant="primary" :disabled="!validateTime">Submit</b-button>
          <b-button type="reset" @click="$bvModal.hide('modal-entry')" variant="danger">Cancel</b-button>
        </b-form>
      </div>
    </b-modal>
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
  @State stats;
  @State users;

  @Action getFoodEntries;
  @Action getCalories;
  @Action getStats;
  @Action getUsers;
  @Action deleteEntry;
  @Action updateEntry;
  @Action createEntry;

  @Mutation setDateFrom
  @Mutation setDateTo

  @State isAdmin;

  DEFAULT_FORM = {
        id: null,
        user: 0,
        name: '',
        date: new Date(),
        time: moment().format('HH:mm:ss'),
        calories: 0,
        skipDiet: false,
  };
  form = {
    id: null,
    user: 0,
    name: '',
    date: new Date(),
    time: moment().format('HH:mm:ss'),
    calories: 0,
    skipDiet: false,
  };

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
    this.resetForm();
    this.search();
    if (this.isAdmin) {
      this.getStats();
      this.getUsers();
    }
  }

  onSubmit(event) {
    event.preventDefault();
    this.search();
  }

  search() {
    this.getFoodEntries();
    if (!this.isAdmin) {
      this.getCalories();
    }
  }

  onDeleteEntry(id) {
    if(confirm("Are you sure?")) {
      this.deleteEntry(id);
    }
  }

  resetForm() {
    Object.keys(this.DEFAULT_FORM).forEach(k => this.form[k] = this.DEFAULT_FORM[k]);
  }

  get validateTime() {
    return moment(moment(this.form.date).format('YYYY-MM-DD') + ' ' + this.form.time).isBefore();
  }

  onEdit(entry) {
    this.resetForm();
    this.form.id = entry.id;
    this.form.name = entry.name;
    this.form.calories = entry.calories;
    this.form.user = entry.user;
    this.form.date = entry.eatDate;
    this.form.time = moment(entry.eatDate).format('HH:mm:ss');
    this.form.skipDiet = entry.skipDiet;
    this.$bvModal.show('modal-entry');
  }

  async onEditSave() {
    this.form['eatDate'] = moment(moment(this.form.date).format('YYYY-MM-DD') + ' ' + this.form.time).format();
    if (!this.isAdmin) delete this.form.user;
    this.form.calories = Number.parseInt(this.form.calories);
    if (this.form.id) {
      await this.updateEntry(this.form);
    }
    else {
      delete this.form.id;
      await this.createEntry(this.form);
    }
    this.$bvModal.hide('modal-entry');
  }

  createNew() {
    this.resetForm();
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

  get newDateFromMax() {
    return this.newDateTo ? this.newDateTo :  new Date();
  }

  get newDateValid() {
    return this.newDateFrom === null || this.newDateTo === null || this.newDateFrom <= this.newDateTo;
  }

  get newDateToMax() {
    return new Date();
  }

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
