# OxLogiQ

## Übersicht
**OxLogiQ** ist ein intelligentes Logging-Plugin für den **OXID eShop**, das Standard-Logs mit zusätzlichen Kontextinformationen anreichert, automatisch alte Einträge rotiert und kritische Meldungen per E-Mail verschickt.  

## Features
- datumgetrenntes Logging (einstellbar)
- Dateirotation (einstellbar)
- Benachrichtigung bei kritischen Ereignissen per Mail (einstellbar)
- Prozess-ID (Filterkriterium)
- Session-ID (Filterkriterium)
- Buffering (Optimierung der Schreibvorgänge)
- Ergänzen der Einträge um Codeverweise
- Channel ergänzt um Frontend bzw. Backend
- Channel ergänzt um den Subshop
- Einfache Konfiguration über Variablen in der `config.inc.php`

## Installation
1. über Composer installieren
   ```bash
   composer require d3/oxlogiq
   ```
2. Konfiguration setzen
3. TMP-Ordner des Shops leeren

## Konfiguration

In der `config.inc.php` lassen sich folgende Parameter anpassen:

| Einstellung               | Beschreibung                                                                                                              |
|---------------------------|---------------------------------------------------------------------------------------------------------------------------|
| sLogLevel (OXID-Standard) | kleinste Level, die in die Log-Dateien geschrieben werden                                                                 |
| oxlogiq_retentionDays     | Anzahl der Tage, die Logfiles behalten werden, <br/>- `0` für unbegrenzt, <br/>-`null` für eine einzelne Datei (Standard) |
| oxlogiq_mailRecipients    | Empfängeradresse(n) für Alerts, <br/>Array oder String, <br/>`null` für keinen Mailversand (Standard)                     |
| oxlogiq_mailLogLevel      | kleinste Level, die per Mail benachrichtigt werden (Standard: `ERROR`)                                                    |
| oxlogiq_mailSubject       | Betreff der Benachrichtigungsmail (Standard: `Shop Log Notification`)                                                     |

### Codebeispiel

```PHP
$this->sLogLevel = 'WARNING';
$this->oxlogiq_retentionDays = 7;
$this->oxlogiq_mailRecipients = 'alerts@mydomain.com';
$this->oxlogiq_mailLogLevel = 'ERROR';
$this->oxlogiq_mailSubject = 'Ausnahmebenachrichtigung';
```

## Softwarelizenz (OxLogiQ) [MIT]

(27.10.2025)

```
Copyright (c) D3 Data Development (Inh. Thomas Dartsch)

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
```