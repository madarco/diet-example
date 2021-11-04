<template>
  <b-modal id="modal-entry" hide-footer @show="onShow">
    <template #modal-title>
      Edit food entry
    </template>
    <div class="">
      <b-form @submit.prevent="onEditSave">
        <!-- User -->
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

        <!-- Name -->
        <b-form-group id="input-group-2" label="Food Name:" label-for="input-2">
          <b-form-input
              id="input-2"
              v-model="form.name"
              placeholder="Enter food name"
              required
          ></b-form-input>
        </b-form-group>

        <!-- Calories -->
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

        <!-- Date/Time -->
        <b-form-group id="input-group-2" label="When:" label-for="input-4">
          <b-form-datepicker id="input-4" v-model="form.date" :max="newDateToMax" class="mb-2"></b-form-datepicker>
          <b-form-timepicker v-model="form.time" locale="en" :state="validateTime" now-button></b-form-timepicker>
        </b-form-group>

        <!-- Skip Diet -->
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
</template>

<script lang="ts">
import { Component, Vue, Prop } from 'vue-property-decorator';
import { State, Action } from 'vuex-class'
import moment from 'moment';

/**
 * Component to handle the edit/create modal
 */
@Component({})
export default class EditModal extends Vue {
  @State entry
  @State calories;
  @State users;

  @Action updateEntry;
  @Action createEntry;

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

  onShow() {
    this.resetForm();
    if (this.entry) {
      this.form.id = this.entry.id;
      this.form.name = this.entry.name;
      this.form.calories = this.entry.calories;
      this.form.user = this.entry.user;
      this.form.date = this.entry.eatDate;
      this.form.time = moment(this.entry.eatDate).format('HH:mm:ss');
      this.form.skipDiet = this.entry.skipDiet;
    }
  }

  resetForm() {
    Object.keys(this.DEFAULT_FORM).forEach(k => this.form[k] = this.DEFAULT_FORM[k]);
  }

  get validateTime() {
    return moment(moment(this.form.date).format('YYYY-MM-DD') + ' ' + this.form.time).isBefore();
  }

  async onEditSave() {
    this.form['eatDate'] = moment(moment(this.form.date).format('YYYY-MM-DD') + ' ' + this.form.time).format();
    if (!this.isAdmin) delete this.form.user;
    this.form.calories = Number.parseInt(String(this.form.calories));
    if (this.form.id) {
      await this.updateEntry(this.form);
    }
    else {
      delete this.form.id;
      await this.createEntry(this.form);
    }
    this.$bvModal.hide('modal-entry');
  }

  get newDateToMax() {
    return new Date();
  }
}
</script>
