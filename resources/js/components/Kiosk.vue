<template>
    <div>
        <div class="row" v-if="!wsConnectionOk">
            <div class="col-12 text-center kiosk-offline-container">
                <i class="bi-exclamation-circle large-icon"></i>
                <h1 id="kiosk-offline">Kiosk Offline</h1>
                <div>
                    <p class="last-connected-large">since <time-ago :refresh="1" :datetime="wsConnectionFailedAt" long/> ({{wsConnectionFailedAt | moment("MMM D [at] h:mm A")}})</p>
                    <p class="last-connected-large">Please <strong>DO NOT</strong> tap your BuzzCard at this time</p>
                    <p class="last-connected-large">Please <strong>DO</strong> be extra mindful of occupancy limits</p>
                    <p class="last-connected-large">Check #people-counter in Slack for status updates</p>
                </div>
            </div>
        </div>
        <div class="row" v-if="wsConnectionOk">
            <div class="col-12 text-center" style="margin-top: -100px;">
                <span><span class="people-count">{{ peopleHere.length }}</span></span>
                <h1 style="margin-top: -75px;">
                    {{ pluralPeople }} in the {{ currentSpaceName }} space
                </h1>
                <template v-if="showSpaceStatus === 'hide'">
                    <b>Maximum Occupancy: {{ maxPeople }}</b>
                </template>
                <h2>Tap your BuzzCard to sign in or out</h2>
            </div>
        </div>
        <div class="row pt-4" v-if="wsConnectionOk && showSpaceStatus === 'show'">
            <div class="col-12">
                <div class="card">
                    <h5 class="card-header">Space Status</h5>
                    <template v-if="loading.spaces">
                        <div class="spinner-grow" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </template>
                    <template v-else>
                        <div class="card-body">
                            <div class="col-12 space-status">
                                <template v-for="space in spaces">
                                    <h5 class="space-name">{{ space.name }}</h5>
                                    <b> {{ space.active_visit_count + space.active_child_visit_count}}</b> here, {{ space.max_occupancy }} max
                                    <p/>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
        <div class="row pt-4" v-if="wsConnectionOk">
            <div class="col-12">
                <div class="card">
                    <h5 class="card-header">Who's Here</h5>
                    <div class="card-body">
                        <p style="font-size: x-large;">
                            {{ this.peopleHere.join(", ")}}
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer fixed-bottom pb-8" v-if="!wsConnectionOk">
            <div class="container-fluid pt-4">
                <div class="justify-content-between text-center">
                    <div>
                        <p class="last-connected-small" v-if="offlineCardSwipes > 0">{{ offlineCardSwipes }} {{ offlineCardSwipes === 1 ? "person" : "people" }} did not read this notice and tapped their BuzzCard anyway</p>
                        <p><a style="color: black;" href="https://github.com/RoboJackets/people-counter">Made with ♥ by RoboJackets</a> | {{ pageHost }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<style scoped>
    @import url("https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css");

    .people-count {
        font-size: 300px !important;
        font-weight: bolder;
    }
    .space-status {
        column-count: 3;
    }
    @media (min-width: 1200px) {
        .space-status {
            column-count: 4;
        }
    }
    @media (max-width: 900px) {
        .space-status {
            column-count: 2;
        }
    }
    h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6 {
        font-weight: bolder;
    }
    h1 {
        font-size: 3.25rem;
    }
    h5.space-name, h5.card-header, b, p, div {
        font-size: xx-large;
    }
    .large-icon {
        font-size: 300px;
        margin-bottom: -100px;
    }
    #kiosk-offline {
        font-size: 125px;
        text-transform: uppercase;
        font-weight: bold;
        margin-top: -50px;
    }
    .last-connected-large {
        font-size: 50px;
    }
    .last-connected-small {
        font-size: 30px
    }
    .kiosk-offline-container {
        margin-top: -40px;
    }
</style>
<style>
/* this is intentionally not in the scoped styles because it's hiding the default footer added via Blade templates */
#blade-footer {
    display: none;
}
</style>
<script>
import Echo from 'laravel-echo';
import { TimeAgo } from 'vue2-timeago'
export default {
    components: {
      TimeAgo
    },
    data() {
        return {
            'peopleHere': [],
            'space': {},
            'spaces': {},
            'punch': {
                'gtid': null,
                'door': null,
                'include': 'user',
                'space_id': null,
            },
            'user': {},
            pageHost: window.location.host,
            loading: {
                'spaces': false,
            },
            wsConnectionOk: false,
            wsConnectionFailedAt: new Date().toISOString(),
            offlineCardSwipes: 0,
            showSpaceStatus: true,
            submitting: false,
            spaceId: null,
            visitsBaseUrl: '/api/visits',
            punchBaseUrl: '/api/visits/punch',
            spacesBaseUrl: '/api/spaces',
            userBaseUrl: '/api/user',
            dynamicColor: {
                backgroundColor: ''
            },
            sounds: {
                in: '/sounds/kiosk_in_short.mp3',
                out: '/sounds/kiosk_out_short.mp3',
                notice: '/sounds/kiosk_notice2.mp3',
                error: '/sounds/kiosk_error_xp.mp3',
                dohs: [
                    '/sounds/kiosk_doh1.mp3',
                    '/sounds/kiosk_doh2.mp3',
                    '/sounds/kiosk_doh3.mp3',
                    '/sounds/kiosk_doh4.mp3',
                    '/sounds/kiosk_doh5.mp3',
                    '/sounds/kiosk_doh6.mp3',
                ]
            }
        };
    },
    mounted() {
        let self = this;
        let swalQueue = [];

        // Get URL params
        const urlParams = new URLSearchParams(window.location.search);

        // Handle hiding space status
        if (urlParams.has('spaceStatus')) {
            console.log('Found spaceStatus in URL: ' + urlParams.get('spaceStatus'));
            this.setShowSpaceStatus(urlParams.get('spaceStatus'));
        } else if (localStorage.getItem('spaceStatus')) {
            console.log('Found spaceStatus in local storage');
            this.setShowSpaceStatus(urlParams.get('spaceStatus'));
        }

        // Handle Space
        if (urlParams.has('space')) {
            console.log('Found space in URL: ' + urlParams.get('space'));
            this.setSpaceId(urlParams.get('space'));
        } else if (localStorage.getItem('spaceId')) {
            console.log('Found space in local storage');
            this.setSpaceId(localStorage.getItem('spaceId'))
        } else {
            console.log('Did not find space');
            swalQueue.push({
                title: "Space",
                text: "Please provide the numeric ID of the space to display at this kiosk",
                inputValidator: (result) => {
                    return !result && 'You must specify a space ID'
                },
                preConfirm: function(value)
                {
                    self.setSpaceId(value)
                }
            })
        }

        // Handle Door
        if (urlParams.has('door')) {
            console.log('Found door in URL: ' + urlParams.get('door'));
            this.setPunchDoor(urlParams.get('door'));
        } else if (localStorage.getItem('door')) {
            console.log('Found door in local storage');
            this.setPunchDoor(localStorage.getItem('door'))
        } else {
            console.log('Did not find door');
            swalQueue.push({
                title: "Location",
                text: "Please provide a short identifier for location of this kiosk",
                inputValidator: (result) => {
                    return !result && 'You must specify a location'
                },
                preConfirm: function(value)
                {
                    self.setPunchDoor(value)
                }
            })
        }

        // Handle Token
        if (urlParams.has('token')) {
            console.log('Found API token in URL: ' + urlParams.get('token'));
            this.setApiToken(urlParams.get('token'))
        } else if (localStorage.getItem('api_token')) {
            console.log('Found API token in local storage');
            this.setApiToken(localStorage.getItem('api_token'))
        } else {
            console.log('Did not find API token');
            swalQueue.push({
                title: "Authentication",
                text: "Please provide an API token to process data",
                inputValidator: (result) => {
                    return !result && 'You must specify an API token'
                },
                preConfirm: function(value)
                {
                    self.setApiToken(value)
                }
            })
        }

        // Show swal to grab missing data (if any)
        if (swalQueue.length > 0) {
            this.$swal.mixin({
                input: 'text',
                confirmButtonText: 'Next &rarr;',
                showCancelButton: false,
                progressSteps: [...Array(swalQueue.length).keys()].map(x => ++x),
                allowOutsideClick: false,
            }).queue(swalQueue).then((result) => {
                if (result.value) {
                    this.$swal.fire({
                        title: 'All done!',
                        icon: 'success',
                    });
                    self.postMountedLoad();
                }
            })
        } else {
            self.postMountedLoad();
        }
    },
    computed: {
        currentSpaceName() {
            return (typeof this.space !== 'undefined') ?
                this.space.name :
                null
        },
        maxPeople() {
            return (typeof this.space !== 'undefined') ?
                this.space.max_occupancy :
                -1
        },
        pluralPeople() {
            return (1 === this.peopleHere.length) ? 'person is' : 'people are';
        },
        pluralPeoplePastTense() {
            return (1 === this.peopleHere.length) ? 'person was' : 'people were';
        }
    },
    watch: {
        peopleHere: function () {
            this.dynamicColor.backgroundColor = this.occupancyColor();
            document.body.style.backgroundColor = this.dynamicColor.backgroundColor;
        },
        wsConnectionOk: function() {
            this.dynamicColor.backgroundColor = this.occupancyColor();
            document.body.style.backgroundColor = this.dynamicColor.backgroundColor;
        }
    },
    methods: {
        setShowSpaceStatus(value) {
            localStorage.setItem('showSpaceStatus', value);
            this.showSpaceStatus = value;
        },
        setSpaceId(id) {
            localStorage.setItem('spaceId', id);
            this.spaceId = id;
            this.punch.space_id = id;
        },
        setPunchDoor(doorString) {
            let cleanDoor = doorString.toLowerCase().replace(/ /g,"_");
            localStorage.setItem('door', cleanDoor);
            this.punch.door = cleanDoor;
        },
        setApiToken(tokenString) {
            localStorage.setItem('api_token', tokenString);
            axios.defaults.headers.common['Authorization'] = 'Bearer ' + localStorage.getItem('api_token');
        },
        postMountedLoad() {
            this.loadUser();
            this.loadSpace();
            this.loadSpaces();
            this.loadWebSocket();
            this.startKeyboardListening();
        },
        loadUser() {
            self.axios
                .get(this.userBaseUrl)
                .then(response => {
                    let rawUser = response.data;
                    if (rawUser.hasOwnProperty('id')) {
                        this.user = rawUser;
                        Sentry.setUser(this.user.id, this.user.email, this.user.full_name)
                    }
                })
                .catch(error => {
                    this.axiosErrorToSentry(error)
                    this.handleAxiosError(error)
                });
        },
        loadSpace() {
            self.axios
                .get(this.spacesBaseUrl + '/' + this.spaceId + '?include=activeVisitsUsers,activeChildVisitsUsers')
                .then(response => {
                    let rawSpace = response.data;
                    if (rawSpace.length < 1) {
                        this.$swal.fire('Bueller...Bueller...', 'No spaces found.', 'warning');
                    } else {
                        this.space = rawSpace
                        this.peopleHere = this.parseVisitsUsers(rawSpace)
                    }
                })
                .catch(error => {
                    this.axiosErrorToSentry(error)
                    this.handleAxiosError(error)
                });
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
                    this.axiosErrorToSentry(error)
                    this.handleAxiosError(error)
                });
        },
        parseVisitsUsers(space) {
            let tempPeopleHere = []
            if (space.hasOwnProperty('active_visits_users') && space.active_visits_users.length > 0) {
                tempPeopleHere = tempPeopleHere.concat(space.active_visits_users.map(a => a.full_name))
            }
            if (space.hasOwnProperty('active_child_visits_users')
                && space.active_child_visits_users.length > 0) {
                tempPeopleHere = tempPeopleHere.concat(space.active_child_visits_users.map(a => a.full_name))
            }
            return tempPeopleHere.sort(function (a, b) {
                return a > b ? 1 : b > a ? -1 : 0;
            });
        },
        occupancyColor() {
            let max = this.maxPeople;
            let here = this.peopleHere.length;
            if (!this.wsConnectionOk) {
                return '#ffb732';
            } else if (here / max < 0.5) {
                // < 50% -> Green
                return '#66b266';
            } else if (here / max <= 0.75) {
                // 50%-75% -> Yellow
                return '#ffff66';
            } else if (here / max <= 0.99) {
                // 75% -> 99% -> Orange
                return '#ffb732';
            } else {
                // 100% -> red
                return '#ff3232';
            }
        },
        loadWebSocket() {
            let self = this;
            let pusher = require('pusher-js');
            pusher.logToConsole = true;
            let echo = new Echo({
                broadcaster: 'pusher',
                key: process.env.MIX_PUSHER_APP_KEY,
                wsHost: window.location.hostname,
                wsPath: "/ws",
                wsPort: 80,
                wssPort: 443,
                enableStats: false,
                encrypted: true,
                forceTLS: true,
                enabledTransports: ['ws', 'wss']
            });

            echo.connector.pusher.connection.bind('state_change', function(states) {
                console.log("WebSocket state changed from", states.previous, "to", states.current);

                if (states.current === "unavailable") {
                    self.wsConnectionOk = false;
                    if (!self.wsConnectionFailedAt) {
                        self.wsConnectionFailedAt = new Date().toISOString();
                    }
                    Sentry.captureMessage('WebSocket Unavailable', 'warning')
                }

                if (states.current === "connected") {
                    self.wsConnectionOk = true;
                    self.offlineCardSwipes = 0;
                    self.wsConnectionFailedAt = null;

                    // Reload data that might have changed since coming back online
                    self.loadUser();
                    self.loadSpace();
                    self.loadSpaces();
                }
            });

            echo.channel('punches')
                .listen('Punch', (e) => {
                    let response = e.spaces
                    let found = response.find(function (element) {
                        // This is deliberately not === because it doesn't work if it is
                        // If you want to figure out why and fix it, be my guest
                        return element.id == self.spaceId;
                    });
                    this.peopleHere = this.parseVisitsUsers(found)
                    this.spaces = e.spaces;
                });
        },
        startKeyboardListening() {
            //Remove focus from button
            document.activeElement.blur();
            // Listen for keystrokes from card swipe (or keyboard)
            let buffer = '';
            window.addEventListener(
                'keypress',
                function (e) {
                    if (this.submitting) {
                        return;
                    }
                    if (e.key != 'Enter') {
                        //A key that's not enter was pressed
                        buffer += e.key;
                    } else {
                        //Enter was pressed
                        this.cardPresented(buffer);
                        buffer = '';
                    }
                }.bind(this)
            );
        },
        randomIntFromInterval: function(min, max) { // min and max included
            // from a kind StackOverflower: https://stackoverflow.com/a/7228322
            return Math.floor(Math.random() * (max - min + 1) + min);
        },
        cardPresented: function (cardData) {
            // Card is presented, process the data
            let self = this;
            console.log('first cardData: ' + cardData);

            let pattError = new RegExp('[%;+][eE]\\?');

            if (cardData.startsWith('NFC-')) {
                cardData = cardData.substring(4);
            }

            // We're only accepting contactless card reads for this particular application
            // No mag stripe here, folks! You should really get a new BuzzCard...
            if (!this.wsConnectionOk) {
                new Audio(this.sounds.dohs[this.randomIntFromInterval(0, this.sounds.dohs.length - 1)]).play()
                this.offlineCardSwipes += 1;
            } else if (this.isNumeric(cardData) && cardData.length === 9 && cardData[0] === '9') {
                // Numeric nine-digit number starting with a nine
                this.punch.gtid = cardData;
                console.log('numeric cardData: ' + cardData);
                cardData = null;
                this.submit();
            } else if (pattError.test(cardData)) {
                // Error message sent from card reader
                new Audio(this.sounds.error).play()
                console.log('error cardData: ' + pattError.exec(cardData));
                cardData = null;
                this.$swal.fire({
                    title: 'Hmm...',
                    text: 'There was an error reading your card. Please tap again.',
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    icon: 'warning',
                    onClose: () => {
                        self.clearFields();
                    }
                })
            } else {
                new Audio(this.sounds.error).play()
                this.$swal.close();
                console.log('unknown cardData: ' + cardData);

                Sentry.captureMessage('Card format not recognized', 'info')
                cardData = null;
                this.$swal.fire({
                    title: 'Hmm...',
                    html: 'Card format not recognized.<br/>Contact developers@robojackets.org for assistance.',
                    icon: 'error',
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    onClose: () => {
                        self.clearFields();
                    }
                })
            }
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
                    let swalIcon;
                    let swalText = (direction === 'in') ? `Nice to see you, ${name}.` : `Have a great day, ${name}!`;

                    if (response.data.message) {
                        swalText += `<br/><br/><b>${response.data.message}</b>`;
                        swalIcon = 'info';
                        new Audio(this.sounds.notice).play()
                    } else {
                        swalIcon = 'success';
                        new Audio((direction === 'in') ? this.sounds.in : this.sounds.out).play();
                    }

                    this.$swal.fire({
                        title: `You're ${direction}!`,
                        html: swalText,
                        timer: 3000,
                        showConfirmButton: false,
                        icon: swalIcon,
                        timerProgressBar: true,
                        customClass: {
                            title: 'swal-swipe-title',
                        }
                    });
                    this.clearFields();
                })
                .catch(error => {
                    console.log(error);
                    this.hasError = true;
                    this.feedback = '';
                    this.clearFields();
                    new Audio(this.sounds.error).play()
                    if (error.response.status === 403) {
                        this.axiosErrorToSentry(error)
                        this.$swal.fire({
                            title: 'Whoops!',
                            text: "You don't have permission to perform that action.",
                            icon: 'error',
                            timer: 3000,
                            timerProgressBar: true,
                            showConfirmButton: false,
                        });
                    } else if (error.response.status === 422 && error.response.data.error.includes('space') ) {
                        let msg = error.response.data.error
                        msg += `<b>You must set your default space at ${window.location.hostname} before punching in.</b>`
                        this.$swal.fire({
                            title: 'STOP! Action Required',
                            html: msg,
                            icon: 'error',
                            timer: 10000,
                            timerProgressBar: true,
                            showConfirmButton: false,
                        });
                    } else if (error.response.status === 422 && error.response.data.error.includes('occupancy') ) {
                        let msg = `<b>${error.response.data.error}</b>`
                        msg += `<br/>View space occupancy at ${window.location.hostname}`
                        this.$swal.fire({
                            title: 'STOP! Punch Rejected',
                            html: msg,
                            icon: 'error',
                            timer: 10000,
                            timerProgressBar: true,
                            showConfirmButton: false,
                        });
                    } else {
                        this.axiosErrorToSentry(error)
                        this.$swal.fire({
                            title: 'Error',
                            text: 'An unexpected error occurred. If this continues, post in Slack #people-counter.',
                            icon: 'error',
                            timer: 5000,
                            timerProgressBar: true,
                            showConfirmButton: false,
                        });
                    }
                })
                .finally(() => {
                    this.submitting = false;
                    this.$swal.hideLoading();
                });
        },
        clearFields() {
            //Remove focus from button
            document.activeElement.blur();
            this.punch.gtid = '';
            console.log('fields cleared');
        },
        isNumeric(n) {
            return !isNaN(parseFloat(n)) && isFinite(n);
        },
        handleAxiosError(error) {
            new Audio(this.sounds.error).play()
            if (error.hasOwnProperty('response') && error.response.status === 403) {
                this.$swal.fire({
                    title: 'Whoops!',
                    text: "You don't have permission to perform that action.",
                    type: 'error',
                });
            } else if (error.hasOwnProperty('response') && error.response.status === 401) {
                this.$swal.fire({
                    title: 'Whoops!',
                    text: "You are not authenticated. Please try again.",
                    type: 'error',
                });
            } else {
                this.$swal.fire(
                    'Error',
                    'An unexpected error occurred. If this continues, post in Slack #people-counter.',
                    'error'
                );
            }
        },
        axiosErrorToSentry(error) {
            if (process.env.MIX_SENTRY_DSN !== undefined) {
                let axiosContext = null
                if (error.response) {
                    axiosContext = {
                        'Response Status': error.response.status,
                        'Response Body': error.response.data,
                        'Request URL': error.response.config.url,
                        // Disabled sending headers until we can sanitize API credentials
                        // 'request_headers': error.response.config.headers,
                        'HTTP Method': error.response.config.method,
                        'Request Body': error.response.config.data
                    }
                } else if (error.request) {
                    axiosContext = {
                        'request': error.request
                    }
                }
                Sentry.captureException(error, {
                    contexts: {
                        axios: axiosContext
                    }
                });
            }
        }
    }
};
</script>
