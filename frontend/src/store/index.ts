import Vue from 'vue'
import Vuex from 'vuex'
import axios from 'axios';
import moment from 'moment';

Vue.use(Vuex)
export default new Vuex.Store({
  state: {
    token: null,
    isAdmin: false,
    dateFrom: moment().subtract(7, 'days').startOf('day').format(),
    dateTo: moment().format(),
    foodEntries: [],
    calories: [],
    stats: [],
    users: [],
  },
  mutations: {
    setIsAdmin(state, isAdmin) {
      state.isAdmin = isAdmin;
      localStorage.setItem('isAdmin', isAdmin);
    },
    setUsers(state, users) {
      state.users = users;
    },
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
    setStats(state, entries) {
      state.stats = entries;
    },
    setToken(state, token) {
      state.token = token;
      axios.defaults.headers.common['Authorization'] = token;
      localStorage.setItem('token', token);
      if (!token) {
        // Clear data
        state.foodEntries = [];
        state.stats = [];
        state.calories = [];
        state.dateFrom = moment().subtract(7, 'days').startOf('day').format();
        state.dateTo =  moment().format();
        state.isAdmin = false;
        state.users = [];
      }
    }
  },
  actions: {
    async getUser({ commit, state }, token) {
      axios.defaults.headers.common['Authorization'] = token;
      const response = await axios.get('/api/food_entries/me');
      if (response.data.isAdmin) {
        const usersResp = await axios.get('/api/food_entries/users');
        commit('setUsers', usersResp.data);
      }
      commit('setIsAdmin', response.data.isAdmin);
      commit('setToken', token);
    },
    async getUsers({ commit, state }) {
      if (state.isAdmin) {
        const usersResp = await axios.get('/api/food_entries/users');
        commit('setUsers', usersResp.data);
      }
    },
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
      if (state.isAdmin) return;
      const params = {
        from: moment(state.dateFrom).startOf('day').format(),
        to:  moment(state.dateTo).endOf('day').format(),
      }
      axios.get('/api/food_entries/calories', { params })
          .then(response => {
            commit('setCalories', response.data)
          })
    },
    getStats({ commit, state }) {
      if (!state.isAdmin) return;

      axios.get('/api/food_entries/stats')
          .then(response => {
            commit('setStats', response.data)
          })
    },
    deleteEntry({ commit, dispatch, state }, id) {
      axios.delete('/api/food_entries/' + id )
          .then(response => {
            dispatch('getFoodEntries');
            dispatch('getCalories');
            dispatch('getStats');
          })
    },
    updateEntry({ commit, dispatch, state }, data) {
      axios.put('/api/food_entries/' + data.id, data)
          .then(response => {
            dispatch('getFoodEntries');
            dispatch('getCalories');
            dispatch('getStats');
          })
    },
    createEntry({ commit, dispatch, state }, data) {
      delete data.id;
      axios.post('/api/food_entries/', data)
          .then(response => {
            dispatch('getFoodEntries');
            dispatch('getCalories');
            dispatch('getStats');
          })
    }
  },
  modules: {
  }
})
