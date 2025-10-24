# OxLogiQ

OxLogiQ ist das intelligente Logging-Plugin für OXID eShop, das Ordnung, Kontext und Übersicht in die Logwelt bringt. Es erweitert Standard-Logeinträge um relevante Daten, rotiert alte Einträge automatisch und meldet kritische Ereignisse per E-Mail.

## Vorteile
- datumgetrenntes Logging (einstellbar)
- Dateirotation (einstellbar)
- Benachrichtigung bei kritischen Ereignissen per Mail (einstellbar)
- Prozess-ID (Filterkriterium)
- Session-ID (Filterkriterium)
- Buffering (Optimierung der Schreibvorgänge)
- Ergänzen der Einträge um Codeverweise
- Channel ergänzt um Frontend bzw. Backend
- Channel ergänzt um den Subshop

## ToDos
- -/-

## Konfiguration

```
$this->logRemainingFiles = 7;  // omit for single log file, 0 for unlimited files
$this->logNotificationAddress = 'alert1@ds.data-develop.de';
```