<template>
    <div :style="dynamicColor">
        <div class="row">
            <div class="col-lg-8 col-sm-12 text-center">
                <span><span class="people-count">{{ peopleHere.length }}</span></span>
                <h2>people are in the {{ currentSpaceName }} space</h2>
            </div>
            <div class="col-lg-4 col-sm-12">
                <h1>Here:</h1>
                <ul>
                    <li v-for="person in peopleHere">{{ person }}</li>
                </ul>
                <h2>{{ maxPeople - peopleHere.length }} spots open</h2>
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
import Echo from 'laravel-echo';
export default {
    data() {
        return {
            'peopleHere': [],
            'space': {},
            'punch': {
                'gtid': null,
                'door': null,
                'include': 'user',
            },
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
        }
    },
    methods: {
        setSpaceId(id) {
            localStorage.setItem('spaceId', id);
            this.spaceId = id;
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
            this.loadVisits();
            this.loadSpace();
            this.loadWebSocket();
            this.startKeyboardListening();
        },
        loadSpace() {
            self.axios
                .get(this.spacesBaseUrl + '/' + this.spaceId)
                .then(response => {
                    let rawSpace = response.data;
                    if (rawSpace.length < 1) {
                        this.$swal.fire('Bueller...Bueller...', 'No spaces found.', 'warning');
                    } else {
                        this.space = rawSpace;
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
        loadVisits() {
            // Fetch active visits from the API to populate list of people
            self.axios
                .get(this.visitsBaseUrl + '?filter[active]=1&include=user')
                .then(response => {
                    let rawVisits = response.data;
                    if (rawVisits.length < 1) {
                        this.$swal.fire('Bueller...Bueller...', 'No people found.', 'warning');
                    } else {
                        let tempPeopleHere = []
                        rawVisits.forEach(function (visit) {
                            if (visit.user) {
                                tempPeopleHere.push(visit.user.full_name);
                            } else {
                                tempPeopleHere.push('Unknown Person');
                            }
                        });
                        // Set global peopleHere to alphabetized tempPeopleHere
                        this.peopleHere = tempPeopleHere.sort(function (a, b) {
                            return a > b ? 1 : b > a ? -1 : 0;
                        });
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
        loadWebSocket() {
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
                    this.peopleHere = e.people;
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
                    type: 'warning',
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
                    type: 'error',
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
                        type: 'success',
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
