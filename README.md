# FinTS Kontodienst
## serverseitiges abrufen von Kontoständen und Kontoauszügen mit Weitergabe der Daten per REST API in Echtzeit

**Somit können Sie z.B. Ihr ERP System PSD2 konform aktuell halten** 

**Ihr eigener Server ruft die Daten ab und leitet sie entsprechend per REST API an den Empfänger weiter**

### Installation:
- Projektdateien z.B. auf Server kopieren (z.B. /kd/server/)
- Sub-/Domain auf Verzeichnis /kd/server/ rooten
- Install Script aufrufen: https://DOMAIN.TLD/install.php
- Führen Sie das install.php Script aus, um die Anbindung auf Ihrem Server verschlüsselt zu speichern

Die Server URL zur Bank erhalten Sie bei Ihrer Bank. 

Danke an die Jungs von https://github.com/nemiah/phpFinTS! "Kontodienst" verwendet das phpFinTS Projekt!

### Funktionen

#### get_statements():array
Liefert Array mit Kontoauszügen
#### get_saldo():array
Liefert den aktuellen Kontostand
#### get_accounts():array
Liefert verfügbare Konten
#### test():array
Zeigt ob Schnittstelle betriebsbereit ist


