[![deutsche Version](https://logos.oxidmodule.com/de2_xs.svg)](README.md)
[![english version](https://logos.oxidmodule.com/en2_xs.svg)](README.en.md)

# OxLogIQ

## Übersicht
**OxLogIQ** ist ein verbessertes Logging-Plugin für den **OXID eShop**, das Logeinträge mit zusätzlichen 
Kontextinformationen anreichert, automatisch alte Dateien rotiert und kritische Meldungen per E-Mail verschickt.

Es ersetzt den OXID Standardlogger und wird bei jedem Aufruf von `Registry::getLogger()` verwendet.

## Features
- datumgetrennte Logdateien (einstellbar)
- Dateirotation (einstellbar)
- Benachrichtigung bei kritischen Ereignissen per Mail (einstellbar)
- Übergabe der Logeinträge nach [Sentry](https://sentry.io) (einstellbar)
- Request-ID (Filterkriterium)
- Session-ID (Filterkriterium)
- Buffering (Optimierung der Schreibvorgänge)
- Ergänzen der Einträge um Codeverweise (Errors und höher)
- Channel ergänzt um Frontend bzw. Backend
- Channel ergänzt um den Subshop
- Einfache Konfiguration über `config.inc.php`- oder Environment-Variablen

## Installation
1. über Composer installieren
   ```bash
   composer require d3/oxlogiq
   ```
2. Konfiguration setzen
3. TMP-Ordner des Shops leeren

## Konfiguration

Über diese Variablen lassen sich folgende Parameter anpassen:

| Einstellung               | Beschreibung                                                                                                              |
|---------------------------|---------------------------------------------------------------------------------------------------------------------------|
| sLogLevel (OXID-Standard) | kleinste Level, die in die Log-Dateien geschrieben werden                                                                 |
| oxlogiq_retentionDays     | Anzahl der Tage, die Logfiles behalten werden, <br/>- `0` für unbegrenzt, <br/>-`null` für eine einzelne Datei (Standard) |
| oxlogiq_mailRecipients    | Empfängeradresse(n) für Alerts, <br/>Array oder String, <br/>`null` für keinen Mailversand (Standard)                     |
| oxlogiq_mailLogLevel      | *optional:* kleinste Level, die per Mail benachrichtigt werden (Standard: `ERROR`)                                        |
| oxlogiq_mailSubject       | *optional:* Betreff der Benachrichtigungsmail (Standard: `Shop Log Alert`)                                                |
| oxlogiq_mailFrom          | *optional:* Absenderadresse (Standard: Infomailadresse des Shops)                                                         |
| oxlogiq_sentryDsn         | *optional:* Sentry Data Source Name                                                                                       |                                                                                                                           |

### Codebeispiel

```PHP
$this->sLogLevel = 'WARNING';
$this->oxlogiq_retentionDays = 7;
$this->oxlogiq_mailRecipients = 'alerts@mydomain.com';
// optional
$this->oxlogiq_mailLogLevel = 'ERROR';
$this->oxlogiq_mailSubject = 'Ausnahmebenachrichtigung';
$this->oxlogiq_mailFrom = 'sender@mydomain.com';
$this->oxlogiq_sentryDsn = 'https://yourkey.ingest.us.sentry.io/yourproject';
```

## Nutzung der Session- / Request-ID

Jeder Logeintrag wird um die verkürzte Session- (sid) und Request-ID (uid) ergänzt. Diese können verwendet werden, um in 
umfangreichen und durchmischten Aufzeichnungen nach zusammengehörigen Logeinträgen zu filtern. Die Session-ID bezieht 
sich damit auf Einträge eines bestimmten Shopnutzers, die Request-ID auf einen bestimmten Seiten- oder Scriptaufruf.
Verwenden Sie die jeweilige ID nach folgendem Schema zur Filterung:

```
cat source/log/oxideshop-2025-01-01.log | grep "[sid/uid]"
```

## Changelog

Siehe [CHANGELOG](CHANGELOG.md) für weitere Informationen.

## Beitragen

Wenn Sie eine Verbesserungsvorschlag haben, legen Sie einen Fork des Repositories an und erstellen Sie einen Pull Request. Alternativ können Sie einfach ein Issue erstellen. Fügen Sie das Projekt zu Ihren Favoriten hinzu. Vielen Dank.

- Erstellen Sie einen Fork des Projekts
- Erstellen Sie einen Feature Branch (git checkout -b feature/AmazingFeature)
- Fügen Sie Ihre Änderungen hinzu (git commit -m 'Add some AmazingFeature')
- Übertragen Sie den Branch (git push origin feature/AmazingFeature)
- Öffnen Sie einen Pull Request

## Softwarelizenz (OxLogIQ) [MIT]
(27.10.2025)

```
Copyright (c) D3 Data Development (Inh. Thomas Dartsch)

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
```