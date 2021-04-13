# People Counter

Demo PR please ignore

A (somewhat simple) people counter for use in the Student Competition Center at Georgia Tech during the COVID-19 pandemic.

## Requirements

- Web Server (nginx preferably)
- PHP >= 7.2
- Database (MySQL, Postgres)
- BuzzAPI GTED Searcher Account (See Below)
- CAS (GT Login) Access (See Below)
- BuzzCard Reader (See Below)


## Deployment

1. `git clone https://github.com/robojackets/people-counter`
2. `cp .env.example .env`
3. Update `.env` accordingly for your environment
4. `php artisan key:generate`
5. `php artisan migrate`

## BuzzAPI

This application relies upon access to [GTED](http://iamweb1.iam.gatech.edu/docs/services/GTED)
via [BuzzAPI v3](http://iamweb1.iam.gatech.edu/docs/services/BuzzApi/overview) to get information
(name, username, email, GTID number) for use in tracking who is in the space.

BuzzAPI access isn't the easiest thing to come by. There's a formal data governance process that must be followed in
order to get access. More information on that can be found on [GT IAM's website](http://iamweb1.iam.gatech.edu/docs/Home).

If you are familiar with BuzzAPI and just need to know what access you need, request the following:

- `SEARCH` on `central.iam.gted.accounts`
- GTED Attributes: `givenName`, `sn`, `mail`, `uid`, `gtGTID`, `gtPrimaryGTAccountUsername`

## CAS (GT Login)

For the non-kiosk aspects of this application, CAS (aka GT Login) is utilized for authentication.
By default, any DNS hostname ending in `gatech.edu` is allowed to authenticate with CAS with no special configuration.
If you're operating on a non-`gatech.edu` hostname, such as robojackets.org, you'll need to request that your domain
name be added to CAS to be able to utilize it for authentication. See [IAM's website](http://iamweb1.iam.gatech.edu/docs/services/CAS) for more info on that.

## BuzzCard Reader

To ensure complete touchless operation, this application is designed for use with contactless Mifare USB card readers.
Some card readers are able to read the GTID number from the DESFire chip in current generation (2016-Present) BuzzCards.
The GTID number is then replayed to the computer as keystrokes for simple integration into the application.
Older (pre-2016, with barcode) BuzzCards do not have DESFire chips and will not work with these readers.

There are many options, though we've used [ACR 122U (~$40)](https://www.amazon.com/ACS-ACR122U-Contactless-Smart-Reader/dp/B01KEGQFYY)
and [Elatec TWN4 (~$150)](https://www.barcodesinc.com/elatec/twn4.htm) readers with great success.
The BuzzCard Center also sells Blackboard MRD5 USB card readers (~$350) to campus departments that should also work.

_Note:_ The ACR 122U does not natively support keyboard emulation, and will require middleware to emulate keystrokes.
We wrote one that we use in our shop, available on [GitHub](https://github.com/RoboJackets/apiary-nfc-reader).

Alternatively, a magnetic stripe card reader could by utilized if configured to output just GTID from the mag stripe.
However, that introduces a potential repeated touch point which for this project was not deemed acceptable.

## Websocket Server Configuration

Websockets are used in the application to keep all kiosks in sync real-time with who's in and out.
This application uses a PHP package to serve as a Pusher-compatible websocket server. 
To utilize this, you must modify your web server's configuration to proxy the websocket data to the websocket server.

### nginx

Add this to the same server block as the rest of the application.
This configures `nginx` to send anything `/ws/*` to the web socket server listening on port 6001.

```text
location /ws/ {
      proxy_pass             http://127.0.0.1:6001/;
      proxy_set_header Host  $host;
      proxy_read_timeout     60;
      proxy_connect_timeout  60;
      proxy_redirect         off;
      # Allow the use of websockets
      proxy_http_version 1.1;
      proxy_set_header Upgrade $http_upgrade;
      proxy_set_header Connection 'upgrade';
      proxy_set_header Host $host;
      proxy_cache_bypass $http_upgrade;
    }

```

### Supervisor Daemon

To keep the websocket server running, you'll need `supervisord` to manage the process. 
Full instructions and example config can be found on the [package's website](https://docs.beyondco.de/laravel-websockets/1.0/basic-usage/starting.html#keeping-the-socket-server-running-with-supervisord).
