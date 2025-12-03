[![deutsche Version](https://logos.oxidmodule.com/de2_xs.svg)](README.md)
[![english version](https://logos.oxidmodule.com/en2_xs.svg)](README.en.md)

# OxLogIQ

## Overview
**OxLogIQ** is an improved logging plugin for **OXID eShop** that enriches log items with additional
context information, automatically rotates old files and sends critical messages by email.

It replaces the OXID default logger and is used every time `Registry::getLogger()` is called.

## Features
- Date-separated log files (adjustable)
- File rotation (adjustable)
- Alerting of critical events by email (optional + adjustable)
- Transfer log entries to [Sentry](https://sentry.io) (optional + adjustable)
- Transfer log entries to HTTP API (e.g. ElasticSearch) (optional + adjustable)
- Request ID (filter criterion)
- Session ID (filter criterion)
- Buffering (optimisation of write operations)
- Add code references to entries (errors and higher)
- Channel complemented by front end or back end
- Channel complemented by subshop
- Simple configuration via `config.inc.php` or environment variables

## Installation
1. Install via Composer
    ```bash
   composer require d3/oxlogiq
   ```
2. Set configuration
3. Clear the shop's TMP folder

## Configuration

The following parameters can be adjusted using these variables:

| Setting                  | Description                                                                                                    |
|--------------------------|----------------------------------------------------------------------------------------------------------------|
| sLogLevel (OXID default) | Lowest level written to the log files                                                                          |
| oxlogiq_retentionDays    | Number of days that log files are retained, <br/>- `0` for unlimited, <br/>-`null` for a single file (default) |
| oxlogiq_mailRecipients   | Recipient address(es) for alerts, <br/>array or string, <br/>`null` for no mail delivery (default)             |
| oxlogiq_mailLogLevel     | *optional:* lowest level that will be alerted by email (default: `ERROR`)                                      |
| oxlogiq_mailSubject      | *optional:* subject of the alert email (default: `Shop Log Alert`)                                             |
| oxlogiq_mailFrom         | *optional:* sender address (default: shop's info mail address)                                                 |
| oxlogiq_sentryDsn        | *optional:* Sentry Data Source Name                                                                            |
| oxlogiq_httpApiEndpoint  | *optional:* Http API endpoint (e.g. for ElasticSearch / ELK Stack)                                             |
| oxlogiq_httpApiKey       | *optional:* Http API key (e.g. for ElasticSearch / ELK Stack)                                                  |


Define these settings either as an environment variable or as a variable in the shop's `config.inc.php` file.

### Code example

```PHP
$this->sLogLevel = "ERROR";
$this->oxlogiq_retentionDays = 7;
$this->oxlogiq_mailRecipients = "alerts@mydomain.com";
// optional
$this->oxlogiq_mailLogLevel = "ERROR";
$this->oxlogiq_mailSubject = "Exception Alert";
$this->oxlogiq_mailFrom = "sender@mydomain.com";
$this->oxlogiq_sentryDsn = 'https://yourkey.ingest.us.sentry.io/yourproject';
$this->oxlogiq_httpApiEndpoint = 'https://my-observability-project.es.eu-central-1.aws.elastic.cloud/logs/_doc';
$this->oxlogiq_httpApiKey = 'ApiKey myApiKey';
```

## Use of the session/request ID

Each log entry is extended by the abbreviated session ID (sid) and request ID (uid). These can be used to filter for 
related log entries in extensive and mixed records. The session ID therefore refers to entries made by a specific shop 
user, while the request ID refers to a specific page or script call. Use the respective ID for filtering according to 
the following scheme:

```
cat source/log/oxideshop-2025-01-01.log | grep "[sid/uid]"
```

## Note

The shop is programmed to write the error message to the original `oxideshop.log` file in the event of a script 
termination. Unfortunately, we cannot change this with simple ways, but we do add these messages to the log files of 
this extension. If you use rotating log files, the `oxideshop.log` file can simply be deleted if it exists.

## Extending the logger

OxLogIQ is designed to be as easy to extend as possible. If, for example, a handler or processor is missing for your 
application, you can simply add it. It is also possible to create a completely new stack.
You can find implementation examples in the `examples` folder.

## Changelog

See [CHANGELOG](CHANGELOG.md) for further information.

## Contributing

If you have a suggestion that would make this better, please fork the repo and create a pull request. You can also 
simply open an issue. Don't forget to give the project a star! Thanks again!

- Fork the Project
- Create your Feature Branch (git checkout -b feature/AmazingFeature)
- Commit your Changes (git commit -m 'Add some AmazingFeature')
- Push to the Branch (git push origin feature/AmazingFeature)
- Open a Pull Request

## Licence of this software (OxLogIQ) [MIT]
(2025-10-27)

```
Copyright (c) D3 Data Development (Inh. Thomas Dartsch)

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
```