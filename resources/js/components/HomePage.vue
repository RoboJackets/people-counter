<template>
    <div>
        <div class="row pt-4">
            <div class="col-12">
                <template v-if="loading.user">
                    <div class="spinner-grow" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </template>
                <template v-else>
                    <b>Hi, {{ user.first_name }}!</b>
                    Not {{ user.first_name }}? <a href="/logout">Logout.</a>
                </template>
            </div>
        </div>
        <div class="row pt-4">
            <div class="col-md-6 col-sm-12">
                <div class="card">
                    <h5 class="card-header">My Status</h5>
                    <template v-if="loading.user">
                        <div class="spinner-grow" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </template>
                    <template v-else>
                        <h5 class="card-title">You're {{ currentVisitState }}!</h5>
                        <h6 class="card-subtitle mb-2 text-muted" v-if="currentVisitState === 'out'">
                            Swipe your BuzzCard at a kiosk to record your presence.
                        </h6>
                        <button type="button"
                                class="btn btn-primary"
                                v-if="currentVisitState === 'in'"
                                v-on:click="submit">
                            End Visit
                        </button>
                    </template>
                </div>
            </div>
            <div class="col-md-6 col-sm-12">
                <div class="card">
                    <h5 class="card-header">SCC Status</h5>
                    <template v-if="loading.visits">
                        <div class="spinner-grow" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </template>
                    <template v-else>
                        <h5 class="card-title">{{ visits.here }} {{ personTerm }} Here</h5>
                        <h6 class="card-subtitle mb-2 text-muted">Maximum {{ visits.max }} People</h6>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
    .card-title {
        padding-left: 1.25rem;
        padding-top: 0.75rem;
    }
    .card-subtitle {
        padding-left: 1.25rem;
    }
</style>

<script>
    export default {
        props: [''],
        data() {
            return {
                'user': {},
                'visits': {
                    'here': null,
                    'max': null,
                },
                'punch': {
                    'gtid': null,
                    'door': 'web',
                },
                loading: {
                    'user': false,
                    'visits': false,
                },
                submitting: false,
                userBaseUrl: '/api/user',
                visitsBaseUrl: '/api/visits',
                punchBaseUrl: '/api/visits/punch',
            };
        },
        mounted() {
            // Fetch data about the currently authenticated user
            this.loadUser();
            // Fetch data about SCC/global visits
            this.loadVisits();
        },
        computed: {
            currentVisitState: function() {
              return (this.activeVisit.id !== undefined) ? 'in' : 'out';
            },
            activeVisit: function() {
                if (this.user.hasOwnProperty('visits')) {
                    let visit = this.user.visits.find(element => element.out_time == null)
                    return (visit === undefined) ? {} : visit;
                } else {
                    return {};
                }
            },
            personTerm: function() {
                if (this.visits.here === 0) {
                    return "People";
                } else if (this.visits.here === 1) {
                    return "Person";
                } else {
                    return "People";
                }
            }
        },
        watch: {
        },
        methods: {
            async loadUser() {
                this.loading.user = true;
                await self.axios
                    .get(this.userBaseUrl + '?include=visits')
                    .then(response => {
                        this.user = response.data;
                        this.punch.gtid = this.user.gtid;
                        this.loading.user = false;
                    })
            },
            async loadVisits() {
                this.loading.visits = true;
                await self.axios
                    .get(this.visitsBaseUrl + '/count')
                    .then(response => {
                        this.visits = response.data;
                        this.loading.visits = false;
                    })
                    .catch(error => {
                        if (error.response.status === 403) {
                            this.$swal.fire({
                                title: 'Whoops!',
                                text: "You don't have permission to perform that action.",
                                type: 'error',
                            });
                        } else if (error.response.status === 401) {
                            // this.tokenSetup(true);
                        } else {
                            this.$swal.fire(
                                'Error',
                                'Unable to process data. Check your internet connection or try refreshing the page.',
                                'error'
                            );
                        }
                    });
            },
            submit() {
                // Submit attendance data
                this.submitting = true;
                this.$swal.showLoading();
                axios
                    .post(this.punchBaseUrl, this.punch)
                    .then(response => {
                        this.hasError = false;
                        let name = (response.data.name ? response.data.name : "Unknown User");
                        let direction = response.data.punch;
                        let swalText = (direction === 'in') ? 'Nice to see you, ' + name + '.' : 'Have a great day, ' + name + '!';

                        // Refresh user
                        this.loadUser()

                        this.$swal.fire({
                            title: "You're " + direction + "!",
                            text: swalText,
                            timer: 2500,
                            showConfirmButton: false,
                            type: 'success',
                            customClass: {
                                title: 'swal-swipe-title',
                            }
                        });
                    })
                    .catch(error => {
                        console.log(error);
                        this.hasError = true;
                        this.feedback = '';
                        this.clearFields();
                        if (error.response.status === 403) {
                            this.$swal.fire({
                                title: 'Whoops!',
                                text: "You don't have permission to perform that action.",
                                type: 'error',
                            });
                        } else {
                            this.$swal.fire(
                                'Error',
                                'Unable to process data. Check your internet connection or try refreshing the page.',
                                'error'
                            );
                        }
                    })
                    .finally(() => {
                        this.submitting = false;
                        this.$swal.hideLoading();
                    });
            },
        }
    };
</script>
