<template>
  <div class="home">
    <SearchBar />
    <Stats />
    <FoodEntries />
    <EditModal />
  </div>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';
import { State, Action, Mutation } from 'vuex-class'
import FoodEntries from '../components/FoodEntries';
import EditModal from '../components/EditModal';
import Stats from "@/components/Stats.vue";
import SearchBar from "@/components/SearchBar.vue";

/**
 * Main page with the list of food entries
 */
@Component({
  components: {
    Stats,
    FoodEntries,
    EditModal,
    SearchBar
  },
})
export default class Home extends Vue {
  @Action getStats;
  @Action getUsers;

  @State isAdmin;

  mounted() {
    if (this.isAdmin) {
      this.getStats();
      this.getUsers();
    }
  }
}
</script>
