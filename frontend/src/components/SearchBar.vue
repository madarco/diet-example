<template>
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
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';
import { State, Action, Mutation } from 'vuex-class'

/**
 * Search top bar
 */
@Component({})
export default class SearchBar extends Vue {
  @State dateFrom;
  @State dateTo;

  @Action getFoodEntries;
  @Action getCalories;

  @Mutation setDateFrom
  @Mutation setDateTo

  @State isAdmin;

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
    if (!this.isAdmin) {
      this.getCalories();
    }
  }

  createNew() {
    this.$bvModal.show('modal-entry');
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
