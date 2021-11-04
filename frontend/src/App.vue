<template>
  <div id="app">
    <b-navbar toggleable="lg" type="dark" variant="info" v-if="token">
      <b-navbar-brand href="#">TopDiet</b-navbar-brand>

      <b-navbar-toggle target="nav-collapse"></b-navbar-toggle>

      <b-collapse id="nav-collapse" is-nav>
        <b-navbar-nav>
          <b-nav-item to="/">Home</b-nav-item>
        </b-navbar-nav>

        <!-- Right aligned nav items -->
        <b-navbar-nav class="ml-auto">
          <b-nav-item href="#" @click="logout()">Logout</b-nav-item>
        </b-navbar-nav>
      </b-collapse>
    </b-navbar>

    <b-container>
      <router-view v-if="token"/>
      <b-form @submit.prevent="login(newToken)" v-else>
        <b-form-group
                      id="fieldset-1"
                      label="Enter your token"
                      label-for="input-1"
                      :state="token"
        >
          <b-form-input id="input-1" v-model="newToken" :state="token" trim required></b-form-input>
          <b-button type="submit" variant="primary">Submit</b-button>
        </b-form-group>
      </b-form>
    </b-container>

  </div>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';
import { State, Action, Mutation } from 'vuex-class'

/**
 * Main entry point with layout, navigation and login/logout
 */
@Component({})
export default class App extends Vue {
  @State token;

  newToken = "";

  @Mutation setToken;
  @Mutation setIsAdmin;
  @Action getUser;

  mounted() {
    this.login(null);
  }

  login(token) {
    if (!token) {
      this.setIsAdmin(localStorage.getItem('isAdmin') === 'true');
      this.setToken(localStorage.getItem('token'));
    }
    else {
      this.getUser(token);
    }
  }

  logout() {
    this.setIsAdmin(false);
    this.setToken('');
  }
}
</script>

<style>
#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
}

#nav {
  padding: 30px;
}

#nav a {
  font-weight: bold;
  color: #2c3e50;
}

#nav a.router-link-exact-active {
  color: #42b983;
}
</style>
