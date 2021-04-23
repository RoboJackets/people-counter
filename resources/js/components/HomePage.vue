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
            <div class="col-md-4 col-sm-12">
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
                            <b>Default Space:</b>
                            <template v-if="!user.hasOwnProperty('spaces') || user.spaces.length === 0">
                                Not Set
                            </template>
                            <template v-else>
                                {{ user.spaces.map(a => a.name).join(", ")}}
                            </template>
                            <button type="button" class="btn btn-secondary btn-sm"
                                    v-on:click="openModal('defaultSpaceChangeModal')">
                                Edit
                            </button>
                        </div>
                    </template>
                </div>
            </div>
            <div class="col-md-8 col-sm-12">
                <div class="card">
                    <h5 class="card-header">Space Status</h5>
                    <template v-if="loading.spaces">
                        <div class="spinner-grow" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </template>
                    <template v-else>
                        <div class="card-body">
                            <div class="row">
                                <template v-for="space in spaces">
                                    <div class="col-md-6 col-sm-12">
                                        <h5 class="space-name">{{ space.name }}</h5>
                                        <b> {{ space.active_visit_count + space.active_child_visit_count}}</b> here, {{ space.max_occupancy }} maximum
                                        <br/>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show" id="backdrop" style="display: none;"></div>
        <div class="modal fade" tabindex="-1" id="defaultSpaceChangeModal">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Change Default Space(s)</h5>
                        <button type="button" class="close" aria-label="Close"
                                v-on:click="closeModal('defaultSpaceChangeModal')">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>
                            Please pick the space name(s) corresponding to your primary team(s).
                            If you are not affiliated with a specific team, pick "SCC - Main".
                        </p>
                        <template v-for="space in spaces">
                            <div class="form-group form-check">
                                <input class="form-check-input" type="checkbox"
                                       :id="space.id" :value="space.id" v-model="defaultSpaces">
                                <label :for="space.id">{{ space.name }}</label>
                            </div>
                        </template>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                                v-on:click="closeModal('defaultSpaceChangeModal')">Close</button>
                        <button type="button" class="btn btn-primary" v-on:click="saveUserSpaces">Save changes</button>
                    </div>
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
                'defaultSpaces': [],
                'punch': {
                    'gtid': null,
                    'door': 'web',
                },
                loading: {
                    'user': false,
                    'spaces': false,
                    'userSpaces': false,
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
        },
        watch: {
        },
        methods: {
            async loadUser() {
                this.loading.user = true;
                await self.axios
                    .get(this.userBaseUrl + '?include=visits,spaces')
                    .then(response => {
                        this.user = response.data;
                        this.punch.gtid = this.user.gtid;
                        this.loading.user = false;
                        if (process.env.MIX_SENTRY_DSN !== undefined) {
                            Sentry.setUser({
                                id: this.user.id,
                                username: this.user.username,
                                email: this.user.email
                            });
                        }
                    })
            },
            async loadSpaces() {
                this.loading.spaces = true;
                await self.axios
                    .get(this.spacesBaseUrl + '?append=active_visit_count,active_child_visit_count&sort=+name')
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
                            this.$swal.fire({
                                title: 'Whoops!',
                                text: "You are not authenticated. Please try again.",
                                type: 'error',
                            });
                        } else {
                            this.$swal.fire(
                                'Error',
                                'Unable to process data. Check your internet connection or try refreshing the page.',
                                'error'
                            );
                        }
                    });
            },
            saveUserSpaces() {
                // this.loading.userSpaces = true;
                this.$swal.showLoading();
                axios
                    .put(this.userBaseUrl + 's/' + this.user.id + '/spaces', {'spaces': this.defaultSpaces})
                    .then(response => {
                        this.user = response.data.user;
                        this.$swal.hideLoading();
                        this.$swal.close();
                        this.closeModal('defaultSpaceChangeModal')
                    }).catch(error => {
                        console.log(error);
                        this.$swal.hideLoading();
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
                        this.loadSpaces()

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
            openModal(modalId) {
                document.getElementById("backdrop").style.display = "block"
                document.getElementById(modalId).style.display = "block"
                document.getElementById(modalId).className += "show"
            },
            closeModal(modalId) {
                document.getElementById("backdrop").style.display = "none"
                document.getElementById(modalId).style.display = "none"
                document.getElementById(modalId).className += document.getElementById(modalId).className.replace("show", "")
            }
        }
    };
</script>
