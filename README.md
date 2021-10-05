# DaikinWLAN
Beschreibung des Moduls.

### Inhaltsverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)
8. [Sonstiges](#8-sonstiges)

### 1. Funktionsumfang

* Es können alle Daikin Split-Klimageräte gesteuert werden, die mit einem der "alten" WLAN Controller bestückt sind und die passende Firmware haben. 
* Firmware der WLAN Molulse bis V 1.2.54 wurde getestet.
* Sollte das eingesetzte Gerät bereits den neuen WLAN Controller verwenden kann der alte über den S21 Bus nachgerüstet werden. (So habe ich das auch gemacht)
* Nicht alle Geräte unterstützen alle Funktionen. Diese können über die Oberfläche deaktiviert werden. Die FTXM/CTXM Serie senden z.B. keine Feuchte Istwerte.
* Die FTXM/CTXM Serie unterstützen auch die Steuerung der Lammelen. Diese können im Modul auch gsetzt werden, in der Daikin App gibt es dafür keine Möglichkeit.
* Die neue Online App ermöglicht noch einge Settings, wie z.B. den Econo Modus oder die Automatische Lammelensteuerung. Diese können von den Controllern aber nicht ausgewertet werden.
* Fur jenden Modus werden die Werte Temperatur, Lammelensteuerung, Sollfeuchte und Lüftergeschwindigkeit in der Anlage gespeichert. Wenn der Modus umgeschaltet wird, werden diese Werte wieder eingestellt.
* Leistungsstark kann nur im eingeschaltetetn Zustand benutzt werden. Wenn er läuft können keine anderen Lüftergeschwindigkeiten gesetzt werden.
* Beim Entfeuchten und Lüften können keine Temperaturen gesetzt werden, das Entfeuchten läuft auch zusätzlich immer mit der Lüftergeschwindigkeit "Auto".

WICHTIG
* Es können nur die Geräte verwendet werden, die mit dem Daikin Controller funktionieren
* Wenn einmal mir dem neuen Online Controller die dort angebotene Firmware aufgespielt wurde ist die interne API nicht mehr verfügbar!


### 2. Vorraussetzungen

- IP-Symcon ab Version 5.0
- Daikin Split Klimaanlage mit dem passenden WLAN Modul 

### 3. Software-Installation

* Über den Module Store das 'DaikinWLAN'-Modul installieren.
* Alternativ über das Module Control folgende URL hinzufügen: https://github.com/MWS-Koewi/Daikin-WLAN-Klimaanlage

### 4. Einrichten der Instanzen in IP-Symcon

 Unter 'Instanz hinzufügen' kann das 'DaikinWLAN'-Modul mithilfe des Schnellfilters gefunden werden.  
	- Weitere Informationen zum Hinzufügen von Instanzen in der [Dokumentation der Instanzen](https://www.symcon.de/service/dokumentation/konzepte/instanzen/#Instanz_hinzufügen)

__Konfigurationsseite__:

Name                  | Beschreibung
--------------------- | --------------------------------------------------------------------------------------------
IP-Adresse Innengerät | Die IP Adresse des Innengerätes das gesteuert werden sollen  
Abfrageintervall      | Das Intervall in dem die Werte gepollt werden sollen in Sekunden
Streamer              | Wenn ein Streamer vorhanden ist kann er eingeblendet und angesprochen werden
Leistungsstark        | Wenn die Stufe 'Leistungsstartk vorhanden ist kann sie eingeblendet und angesprochen werden
Außentemperatur       | Wenn Das Gerät die Außentemperatur liefert kann sie eingeblendet werden
Istfeuchte            | Wenn Das Gerät die Istfeuchte liefert kann sie eingeblendet werden
Sollfeuchte           | Wenn Das Gerät die Sollfeuchte einstellen kann, kann sie eingeblendet und angesprochen werden
Kompressorauslastung  | Wenn Das Gerät die Kompressorauslastung liefert kann sie eingeblendet werden
Infos                 | Die Informationen über MAC Adresse und Firmwareversion die angezeigt werden können
Fehlermeldungen       | Die gelieferten Fehlermeldeungen können angezeigt werden 



### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

#### Statusvariablen

ID                  | Name                  | Typ    | Profil                | Beschreibung
------------------- | --------------------- | ------ | --------------------- | ---------------------------------------
dknPowerSwitch      | Status                | Bool   | ~Switch	             | Gerät ein und aus Schalten
dknCurrentTemp      | Temperatur Istwert    | Float  | ~Temperature          | Gemessene Ist Temperatur
dknCurrentHumidity  | Feuchte Istwerr       | Int    | ~Humidity             | Gemessene Feuchtigkeit
dknSetTempValue     | Temperatur Sollwert   | Float  | Daikin.Temperature    | Soll Temperatur 
dknSetHumidityValue | Feuchte Sollwert      | Int    | ~Humidity             | Soll Luftfeuchte
dknCurrentOutTemp   | Temperatur Außen      | Float  | ~Temperature          | Außentemperatur
dknSetModeValue     | Modus                 | Int    | Daikin.Mode           | Modus der Anlage
dknCompressor       | Kompressor Auslastung | Int    | ~Intensity.100        | Auslastung des Kompressors
dknSetFanRateValue  | Lüfterstufe           | Int    | Daikin.FanRate        | Intensität des Gebläses
dknSetFanDirValue   | Lüfterrichtung        | Int    | Daikin.FanDirection   | Richtung in der die Lamellen schwenken
dknSetStreamer      | Streamer              | Bool   | ~Switch               | Luftreiniger
dknSetBooster       | Leistungsstark        | Bool   | ~Switch               | Power Stufe
dknErrorMessage     | Fehlermeldung         | String |                       | Fehlermeldung im Klartext
dknFirmware         | Firmware Version      | String |                       | Firmware des WLAN Moduls
dknMAC              | MAC Adresse           | String |                       | MAC Adresse des WLAN Modules


### 6. WebFront

Je nach gewähltem Modus werden im WebFront die einzelnen, der Instanz direkt untergeordneten Controls sichtbar/unsichtbar geschaltet.

### 7. PHP-Befehlsreferenz

Das Modul stellt folgende PHP-Befehle zur Verfügung.

Alle PHP-Befehle erhalten den Prefix DKN_

`RequestRead()`
Liest alle Werte der Steuerung aus

`SetPowerSwitch(bool $Wert)`
Schaltet die Anlage ein oder aus

`SetStreamerValue(bool $Wert)`
Schaltet den Luftreiniger ein oder aus

`SetBoosterValue(bool $Wert)`
Schaltet den Modus "Leistungsstark" ein oder aus. Der Modus "Leistungsstark" kann nur eingeschaltet werden, wenn die Anlage eingeschaltet ist.

`SetHumidityValue(int $Wert)`
Setzt die gewünschte Soll Luftfeuchtigkeit

`SetTempValue(int $Wert)`
Setzt die gewünschte Soll Temperatur. Eine Änderung Solltemperatur ist nur möglich, wenn der Modus nicht "Entfeuchten" oder "Lüften" gesetzt ist.

`SetFanRateValue(int $Wert)`
Setzt die gewünschte Lüfetrstufe. Diese kann nur geändert werden, wenn nicht der Booster eingeschaltet ist.

`SetFanDirValue(int $Wert)`
Setzt die gewünschte Richtung in der die Lammelen schwenken sollen.

`SetModueValue(int $Wert)`
Setzt den gewünschen Modus der Anlage. 

### 8. Sonstiges
Verwendung auf eigene Gefahr, der Autor übernimmt weder Gewähr noch Haftung. 
Nur für den privaten Gebrauch.
