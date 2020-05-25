<template>
  <div>
    <div class="row">
      <div class="col-lg-8 col-sm-12 text-center">
        <h2>There are currently</h2>
        <span><span class="people-count">{{ peopleHere.length }}</span> <span class="h2">of {{ maxPeople }}</span></span>
        <h2>people in the SCC</h2>
      </div>
      <div class="col-lg-4 col-sm-12">
        <h1>Here:</h1>
        <ul>
          <li v-for="person in peopleHere">{{ person }}</li>
        </ul>
      </div>
    </div>
    <div class="row pt-3">
      <div class="col-12 text-center">
        <h4>Tap your BuzzCard to sign in or out</h4>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: ['max-people'],
  data() {
    return {
      'peopleHere': []
    };
  },
  mounted() {
    Echo.channel('punches')
            .listen('Punch', (e) => {
              this.peopleHere = e.people;
            });
  },
  computed: {
  },
  methods: {
  },
};
</script>
