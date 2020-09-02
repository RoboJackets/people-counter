<template>
    <div>
        <div class="row">
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
        <div class="row pt-4" v-if="showSpaceStatus === 'show'">
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
                            <div class="col-12" style="column-count: 4">
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
        <div class="row pt-4">
            <div class="col-12">
                <div class="card">
                    <h5 class="card-header">Who's Here</h5>
                    <div class="card-body">
                        <p style="font-size: large;">
                            {{ this.peopleHere.join(", ")}}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<style scoped>
    .people-count {
        font-size: 300px !important;
        font-weight: bolder;
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
</style>
<script>
import Echo from 'laravel-echo';
export default {
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
            loading: {
                'spaces': false,
            },
            showSpaceStatus: true,
            submitting: false,
            spaceId: null,
            visitsBaseUrl: '/api/visits',
            punchBaseUrl: '/api/visits/punch',
            spacesBaseUrl: '/api/spaces',
            dynamicColor: {
                backgroundColor: ''
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
        }
    },
    watch: {
        peopleHere: function () {
            let max = this.maxPeople;
            let here = this.peopleHere.length;
            if (here / max < 0.5) {
                // < 50% -> Green
                this.dynamicColor.backgroundColor = '#66b266'
            } else if (here / max <= 0.75) {
                // 50%-75% -> Yellow
                this.dynamicColor.backgroundColor = '#ffff66'
            } else if (here / max <= 0.99) {
                // 75% -> 99% -> Orange
                this.dynamicColor.backgroundColor = '#ffb732';
            } else {
                // 100% -> red
                this.dynamicColor.backgroundColor = '#ff3232';
            }
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
            this.loadSpace();
            this.loadSpaces();
            this.loadWebSocket();
            this.startKeyboardListening();
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
                    if (error.response.status === 403) {
                        this.$swal.fire({
                            title: 'Whoops!',
                            text: "You don't have permission to perform that action.",
                            icon: 'error',
                        });
                    } else if (error.response.status === 401) {
                        this.$swal.fire({
                            title: 'Whoops!',
                            text: "Invalid API token or authentication error",
                            icon: 'error',
                        });
                    } else {
                        this.$swal.fire({
                            title: 'Error',
                            text: 'Unable to process data. Check your internet connection or try refreshing the page.',
                            icon: 'error',
                        });
                    }
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
            if (this.isNumeric(cardData) && cardData.length === 9 && cardData[0] === '9') {
                // Numeric nine-digit number starting with a nine
                this.punch.gtid = cardData;
                console.log('numeric cardData: ' + cardData);
                cardData = null;
                this.submit();
            } else if (pattError.test(cardData)) {
                // Error message sent from card reader
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
                this.$swal.close();
                console.log('unknown cardData: ' + cardData);
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
                    let swalText = (direction === 'in') ? 'Nice to see you, ' + name + '.' : 'Have a great day, ' + name + '!';
                    this.$swal.fire({
                        title: "You're " + direction + "!",
                        text: swalText,
                        timer: 3000,
                        showConfirmButton: false,
                        icon: 'success',
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
                    if (error.response.status === 403) {
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
                        msg += " You must set your default space at " + window.location.hostname
                        this.$swal.fire({
                            title: 'Action Required',
                            text: msg,
                            icon: 'info',
                            timer: 10000,
                            timerProgressBar: true,
                            showConfirmButton: false,
                        });
                    } else if (error.response.status === 422 && error.response.data.error.includes('occupancy') ) {
                        let msg = '<b>' + error.response.data.error + '</b>'
                        msg += "<br/>View space occupancy at " + window.location.hostname
                        this.$swal.fire({
                            title: 'STOP! Punch Rejected',
                            html: msg,
                            icon: 'error',
                            timer: 10000,
                            timerProgressBar: true,
                            showConfirmButton: false,
                        });
                    } else {
                        this.$swal.fire({
                            title: 'Error',
                            text: 'Unable to process data. Check your internet connection or try refreshing the page.',
                            icon: 'error',
                            timer: 3000,
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
    }
};
</script>
