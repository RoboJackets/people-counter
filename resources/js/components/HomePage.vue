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
                        <div class="card-body">
                            <button type="button"
                                    class="btn btn-primary"
                                    v-if="currentVisitState === 'in'"
                                    v-on:click="submit">
                                End Visit
                            </button>
                            <hr>
                            <b>Default Space:</b> Narnia
                            <button type="button" class="btn btn-secondary btn-sm">Edit</button>
                        </div>
                    </template>
                </div>
            </div>
            <div class="col-md-6 col-sm-12">
                <div class="card">
                    <h5 class="card-header">Space Status</h5>
                    <template v-if="loading.spaces">
                        <div class="spinner-grow" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </template>
                    <template v-else>
                        <div class="card-body">
                            <template v-for="space in spaces">
                                <h5 class="space-name">{{ space.name }}</h5>
                                <b> {{ space.activeVisitCount }}</b> here, {{ space.max_occupancy }} maximum
                                <br/>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
    .card-body {
        padding: 0 1.25rem 1.25rem;
    }
    .card-title {
        padding-left: 1.25rem;
        padding-top: 0.75rem;
    }
    .card-subtitle {
        padding-left: 1.25rem;
    }
    .space-name {
        padding-top: 10px;
    }
</style>

<script>
    export default {
        props: [''],
        data() {
            return {
                'user': {},
                'spaces': {},
                'punch': {
                    'gtid': null,
                    'door': 'web',
                },
                loading: {
                    'user': false,
                    'spaces': false,
                },
                submitting: false,
                userBaseUrl: '/api/user',
                spacesBaseUrl: '/api/spaces',
                punchBaseUrl: '/api/visits/punch',
            };
        },
        mounted() {
            // Fetch data about the currently authenticated user
            this.loadUser();
            // Fetch data about spaces
            this.loadSpaces();
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
            // personTerm: function() {
            //     if (this.visits.here === 0) {
            //         return "People";
            //     } else if (this.visits.here === 1) {
            //         return "Person";
            //     } else {
            //         return "People";
            //     }
            // }
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
            async loadSpaces() {
                this.loading.spaces = true;
                await self.axios
                    .get(this.spacesBaseUrl + '?append=activevisitcount')
                    .then(response => {
                        this.spaces = response.data;
                        this.loading.spaces = false;
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
