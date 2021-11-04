import Vue from 'vue'
import Vuex from 'vuex'
import axios from 'axios';
import moment from 'moment';

Vue.use(Vuex)
export default new Vuex.Store({
  state: {
    token: null,
    dateFrom: moment().subtract(7, 'days').startOf('day').format(),
    dateTo: moment().format(),
    foodEntries: [],
    calories: [],
  },
  mutations: {
    setFoodEntries(state, entries) {
      state.foodEntries = entries;
    },
    setDateFrom(state, from) {
      state.dateFrom = from;
    },
    setDateTo(state, to) {
      state.dateTo = to;
    },
    setCalories(state, entries) {
      state.calories = entries;
    },
    setToken(state, token) {
      state.token = token;
      axios.defaults.headers.common['Authorization'] = token;
      localStorage.setItem('token', token);
      if (!token) {
        // Clear data
        state.foodEntries = [];
        state.dateFrom = moment().subtract(7, 'days').startOf('day').format();
        state.dateTo =  moment().format();
      }
    }
  },
  actions: {
    getFoodEntries({ commit, state }) {
      const params = {
        from: moment(state.dateFrom).startOf('day').format(),
        to:  moment(state.dateTo).endOf('day').format(),
      }
      axios.get('/api/food_entries', { params } )
          .then(response => {
            commit('setFoodEntries', response.data)
          })
    },
    getCalories({ commit, state }) {
      const params = {
        from: moment(state.dateFrom).startOf('day').format(),
        to:  moment(state.dateTo).endOf('day').format(),
      }
      axios.get('/api/food_entries/calories', { params })
          .then(response => {
            commit('setCalories', response.data)
          })
    }
  },
  modules: {
  }
})
