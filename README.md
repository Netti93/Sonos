Sonos PHP Modul für IP-Symcon
===

[![Check Style](https://github.com/tkugelberg/Sonos/workflows/Check%20Style/badge.svg)](https://github.com/tkugelberg/Sonos/actions)
[![Run Tests](https://github.com/tkugelberg/Sonos/workflows/Run%20Tests/badge.svg)](https://github.com/tkugelberg/Sonos/actions)

IP-Symcon PHP Modul um Sonos Lautsprecher zu steuern

**Inhalt**

1. [Funktionsumfang](#1-funktionsumfang)
2. [Anforderungen](#2-anforderungen)
3. [Installation](#3-installation)
4. [Instanztypen](#4-instanztypen)
5. [Konfiguration](#5-konfiguration)
6. [Variablen](#6-variablen)
7. [Timer](#7-timer)
8. [Funktionen](#8-funktionen)
9. [Update](#9-update)
10. [Was ist neu?](#10-was-ist-neu)

## 1. Funktionsumfang
Dieses Modul is dazu gedacht um allgemeine Funktionalitäten in Sonos aus IP-Symcon heraus auszulösen.  
Die folgenden Funktionalitäten sind implementiert:

- verschiedene Quellen festlegen
  - Radiosender
  - Playlisten
  - Analogen Eingang
  - SPDIF Eingang
- Gruppenhandling
- Wiedergabe/Pause/Stop/Zurück/Vor
- Lautstärke anpassen (inkl. default volume)
- Mute, Loudness, Bass, Treble
- Balance
- Sleeptimer
- Ansagen  
Audiodateien von einem Samba-Share (z.B. Synology) oder HTTP Server abspielen und danach den vorherigen zustand wieder herstellen

## 2. Anforderungen
 - IPS >= 5.4
 - Sonos audio system

## 3. Installation
Hinweis:
Derzeit ist das Modul noch als Beta im Store.
Daher bitte im Store nach "Sonos" suchen.
<kbd>![Store 1](imgs/storeBeta1.png?raw=true "Store 1")</kbd>

<kbd>![Store 2](imgs/storeBeta2.png?raw=true "Store 2")</kbd>   

Am einfachsten ist es die Sonos Lautsprecher über das "Sonos Discovery" Modul hinzuzufügen.
Hierzu muss unter Gerätesuche (die Glocke oben rechts in der Web Konsole) "Sonos Discovery" aktiviert werden.  
<kbd>![Discovery aktivieren](imgs/addDiscovery_de.png?raw=true "Discovery aktivieren")</kbd>  
Dies installiert auch direkt das Sonos Modul aus dem Store.

Alternativ kann man das Modul auch auch manuell installieren.  
Hierzu muss man im "Module Control" (Kern Instanzen->Modules) die URL https://github.com/tkugelberg/Sonos hinzufügen.
Dann kann man eine "Sonos Discovery" Instanz erstellen.  
<kbd>![Discovery Instanz erstellen](imgs/addDiscoveryInstance_de.png?raw=true "Discovery Instanz erstellen")</kbd>

Wenn man nun diese Discovery Instanz öffnet, sieht man all im Netzwerk gefundenen Lautsprecher.  
<kbd>![Discovery Instanz](imgs/discovery_de.png?raw=true "Discovery Instanz")</kbd>  
Hier kann man dann nur bestimmte oder gleich alle "Sonos Player" Instanzen anlegen lassen.

Gleichzeitig wird auch eine "Sonos Splitter" Instanz angelegt.

Falls keine Geräte gefunden werden, könnte dies daran liegen, dass IP-Symcon als Docker Container betrieben wird.  
Es muss darauf geachtet werden, dass der Port 1900 udp vom container auch erreicht werden kann.  
Aber z.B: bei Synology ist es nicht möglich dies zu tun, da Synology diesen Port auch verwendet. Hier hat man die Möglichkeit die Option "Dasselbe Netzwerk wie Dockerhost verwenden" zu aktivieren.

Alternativ können die Player Instanzen aber auch manuell angelegt werden.

## 4. Instanztypen
Es gibt 3 verschiedene Typen von Instanzen.

### 1. Sonos Discovery  
Von dieser Instanz gibt es nur eine, und sie dient lediglich dazu neue Lautsprechen im Netzwerk zu finden und einfach als Instanz anlegen zu lassen.

### 2. Sonos Splitter  
Im normalfall gibt es hiervon auch nur eine. Falls man mehr als einen Sonos Verbund (also z.B: in verschiedenen VLANs) betreibt, kann man mehere Splitter konfigurieren um dies abzubilden.  
Diese Instanz liest regelmäßig die Grupierung aus dem Sonos System aus, und gruppiert die Lautsprecher in IP-Symcon dementsprechend.  
Weiterhin dient sie dazu gemeinsame konfigurationen aller Lautsprecher zu verwalten. Dazu zählen:
  - Die höhe des "Album Art" im Web Front
  - Häuftigkeit der update Funktionalitäten
  - Playlist importe
  - Konfiguration von Radiosendern   

### 3. Sonos Player  
Hierbei handelt es sich um den eigentlichen Lautsprecher.  

## 5. Konfiguration
### 1 Sonos Splitter  
<kbd>![Splitter Instanz](imgs/splitterConfig_de.png?raw=true "Splitter Instanz")</kbd>  
1. __Album Art Höhe im Webfront__  
Wenn man im Player "Detaillierte Informationen" aktiviert, wird unter anderem in einer HTML Box ein Bild (Album Art) angezeigt.  
Diese Einstellung legt fest, wie groß dieses Bild ist.
2. __Update Grouping Frequenz__  
Hierbei handelt es sich um die Zeit in Sekunden zwischen 2 updateGrouping aufrufen der Splitter Instanz.  
Default: 120
3. __Update Status Frequenz__  
Hierbei handelt es sich um die Zeit in Sekunden zwischen 2 updateStatus aufrufen jeder Player Instanz.  
Default: 5
4. __Import Playlists__  
Mit diesem Parameter kann festgelegt werden, welche Playlisten aus Sonos in dem Profil SONOS.Playlist hinzugefügt wird.  
Dies hat zur Folge, dass diese als Button im Webfront angezeigt werden.  
Mögliche Werte sind:
  - keine  
  _Es werden keine Playlisten importiert_
  - gespeicherte
  _Eigene Playlisten, die in Sonos gespeichert wurden_
  - importierte  
  _Hierbei handelt es sich um Playlisten, die zusammen mit der Bibliothek importiert wurde (z.B. eine m3u Datei)_
  - gespeicherte & importierte
  - Favoriten  
  _z.B. Spotify Playlisten oder Radiosender, die als Favorit gespeichert wurden_
  - gespeicherte & Favoriten
  - importierte & Favoriten
  - gespeicherte, importierte & Favoriten  

5. __Radiosender__  
In dieser Tabelle müssen die Radiosender eingetragen werden, die über die funktion SNS_SetRadio( ) gestartet werden können.  
Weiterhin werden alle diese Sender als Knopf im Webfront angezeigt.  

  - Name  
Hierbei handelt es sich um den Namen des Senders. Dieser muss an die Funktion SNS_SetRadio übergeben werden und wird für den Knopf im Webfront verwendet
  - URL  
Dies ist die URL unter der der Radisender zu erreichen ist. Z.B. http://mp3-live.swr3.de/swr3_m.m3u muss als "x-rincon-mp3radio://mp3-live.swr3.de/swr3_m.m3u" angegeben werden.
  - Bild URL  
Diese URL wird verwendet, um das Serderlogo in den Detailinformationen im Webfront anzuzeigen.

Falls man in Sonos unter "TuneIn Radio" -> "Meine Radiosender" favoriten gepflegt hat, kann man diese automatisch mit dem Knopf "TuneIn Favoriten auslesen" in die Tabelle ünertragen.  
### 2 Sonos Player  
<kbd>![Player Instanz](imgs/playerConfig_de.png?raw=true "Player Instanz")</kbd>  
1. __IP-Adresse/Host__  
Dies ist der Hostname oder die IP-Adresse des Players. Sofern die Insanz aus der Discovery Instanz angelegt wurde, ist dieser Wert automatisch gefüllt.
2. __RINCON__  
RINCON ist die eindeutige Bezeichnung eines Lautsprechers. Sofern die Insanz aus der Discovery Instanz angelegt wurde, ist dieser Wert automatisch gefüllt.  
Wenn die Instanz manuell angelegt wurde und die RINCON nicht bekannt ist, kann der Knopf "RINCON auslesen" verwendet werden, um die RINCON zu ermittelt. Hierfür muss allerdings "IP-Adresse/Host" gefüllt sein.
3. __Modell__  
Hier sollte das Modell des Players ausgewählt werden. Sofern die Insanz aus der Discovery Instanz angelegt wurde, ist dieser Wert automatisch gefüllt.  
Mit dem Knopf "Modell auslesen" kann man diesen Wert aber auch auslesen, wenn "IP-Adresse/Host" mit dem richtigen Wert gefüllt ist.  
Es ist eine Liste der aktuell bekannten Player in der Drop-Down Liste gepflegt, welche derzeit die folgenden Werte umfasst:
  - Arc
  - Amp
  - Beam
  - Connect
  - Connect:Amp
  - Move
  - One
  - One SL
  - Play:1
  - Play:3
  - Play:5
  - Playbar
  - Playbase
  - Ray
  - Roam
  - SYMFONISK

  Wenn ein anderes Modell beim auslesen erkannt wird, wird es automatisch der Liste hinzugefügt. Falls dies der Fall ist, wäre es aber hilfreich, wenn ich dieses Modell genannt bekäme, um es hinzuzufügen.  
  Das Modell hat auswirkungen auf die angeotenen Features. So mann man z.B. die "Nachtmodus-Steuerung" nur für die Modelle "Playbar" und "Playbase" einschalten.  
4. __Maximale Dauer bis zur Zeitüberschreitung des ping__  
Bevor ein Lautsprecher kontaktiert wird, wird versucht diesen per Ping zu erreichen. Wenn der Lautsprecher diese Zeil lang nicht antwortet, wird er als "nicht erreichbar" erachtet.  
Wenn der Parameter auf 0 gesetzt wird, wird die Erreichbarkeit nicht überprüft.
5. __Standard Lautstärke__  
Diese Lautstärke wird verwendet, wenn die Funktion SNS_SetDefaultVolume() aufgerufen wird.
6. Nach nicht Verfügbarkeit der Gruppe automatisch wieder beitreten  
Wenn dies Option aktiviert ist wird ein Lautsprecher, der zuvor als "vanished" markiert war, wieder der vor dem verschwinden zugeordneten Gruppe hinzugefügt, solbald er wieder verfügbar ist.
7. __Mute-Steuerung__  
Diese Option legt eine Variable "Mute" an und aktiviert dass diese über SNS_updateStatus() mit dem aktuellen Wert gepflegt wird. Weiterhin taucht dann auch eine Konpf auf dem WebFront auf, über den man dies Steuern kann.
8. __Loudness-Steuerung__  
Diese Option legt eine Variable "Loudness" an und aktiviert dass diese über SNS_updateStatus() mit dem aktuellen Wert gepflegt wird. Weiterhin taucht dann auch eine Konpf auf dem WebFront auf, über den man dies Steuern kann.
9. __Tiefen-Steuerung__  
Diese Option legt eine Variable "Tiefen" an und aktiviert dass diese über SNS_updateStatus() mit dem aktuellen Wert gepflegt wird. Weiterhin taucht dann auch einen Slider auf dem WebFront auf, über den man dies Steuern kann.
10. __Höhen-Steuerung__  
Diese Option legt eine Variable "Höhen" an und aktiviert dass diese über SNS_updateStatus() mit dem aktuellen Wert gepflegt wird. Weiterhin taucht dann auch einen Slider auf dem WebFront auf, über den man dies Steuern kann.
11. __Balance-Steuerung__  
Diese Option legt eine Variable "Balance" an und aktiviert dass diese über SNS_updateStatus() mit dem aktuellen Wert gepflegt wird. Weiterhin taucht dann auch einen Slider auf dem WebFront auf, über den man dies Steuern kann.
12. __Sleeptimer-Steuerung__  
Diese Option legt eine Variable "Sleeptimer" an und aktiviert dass diese über SNS_updateStatus() mit dem aktuellen Wert gepflegt wird.
13. __Playmode-Steuerung__  
Diese Option legt die Variablen "Play Mode" und "Crossfade" an und aktiviert dass diese über SNS_updateStatus() mit dem aktuellen Wert gepflegt wird.  
Für "Play Mode" tauchen dann 6 Knöpfe und für "Crossfade" "Aus"/"An" auf dem Webfront auf, mit denen diese Funktionen gesteuert werden können.
14. __Nachtmodus-Steuerung__  
Diese Option legt die Variablen "Nachmodus" und "Dialogverbesserung" an und aktiviert dass diese über SNS_updateStatus() mit dem aktuellen Wert gepflegt wird.  
Für beide tauchen "Aus"/"An" auf dem Webfront auf, mit denen diese Funktionen gesteuert werden können.
15. __Detaillierte Informationen__  
Diese Option legt die Variablen "Details", "Cover URL", "Content Stream", "Artist", "Künstler", "Titel", "Album", "Titellänge", "Position" und "Sender ID" an, die über SNS_updateStatus() gefüllt werden.  
Weiterhin wird ein Medienobjekt "Cover" angelegt, welches mit dem Bild hinter "Cover URL" gefüllt.  
In der Variablen "Details" wird eine HTMLBox erzeugt, die am WebFront auch zu sehen ist. Alle anderen Variablen werden versteckt.
16. __Variablensortierung erzwingen__  
Wenn diese Option gesetzt ist, wird beim Speichern die vom Modul vorgeschlagene Reihenfolge der Vaiablen wieder hergestellt.

## 6. Variablen
Lediglich Player Instanzen haben Variablen.

- __Gruppenlautstärke__  
Diese Variable wird automatisch eingeblendet, wenn ein Lautsprecher als Gruppenmenber zugeordnet ist.  
Ihr Wert wird anhand der Lautstärke der einzelnen Gruppenmitglieder (Durchnittswert) berechnet.  
Er wird durch die Funktionen
  ```php
  SNS_ChangeGroupVolume(<InstanceID>,<Increment>);
  SNS_SetDefaultGroupVolume(<InstanceID>);
  SNS_SetGroupVolume(<InstanceID>,<Volume>);
  ``` 
  und die Funktion SNS_updateStatus() aktualisiert.
- __Teil der Gruppe__  
Diese Variable wird erstellt, wenn die Option "Koordinator" __nicht__ aktiviert ist.  
Sie enthält die InstanzID des Gruppenkoordinators der die Instanz zugeordnet ist.
- __gerade läuft__  
Diese Variable wird durch die Funktion SNS_updateStatus() aktuell gehalten.  
Sie enthält Informationen über das, was momentan gespielt wird.  
Falls die Instanz Mitglied einer Gruppe ist, wird die Variable versteckt (hidden) und mit dem Wert aus dem Gruppenkoordinator befüllt.
Wenn nicht kann sich der Wert auf 2 Arten zusammensetzen:
  1. Wenn das Feld "StreamContent" gefüllt ist, wird dieser übernommen (z.B.: bei Radiosendern)
  2. Ansonsten wird sie mit "<Titel>|<Artist>" gefüllt
- __Radio__  
Diese Variable enthält den aktuell laufenden Radiosender, sofern er in der Liste im WebFront verfügbaren Radiosender auftaucht (siehe Konfiguration).  
Eine Aktualisierung erfolgt durch die Funktion SNS_updateStatus().  
Falls die Instanz Mitglied einer Gruppe ist, wird die Variable versteckt (hidden) und mit dem Wert aus dem Gruppenkoordinator befüllt.
- __Status__  
Diese Variable enthält Informationen, in welchem Zustand sich die Sonos Instanz gerade befindet und wird von der Funktion SNS_updateStatus() aktualisiert.  
Falls die Instanz Mitglied einer Gruppe ist, wird die Variable versteckt (hidden) und mit dem Wert aus dem Gruppenkoordinator befüllt.
Mögliche Werte sind:
  - 0 - Zurück
  - 1 - Stop
  - 2 - Wiedergabe
  - 3 - Pause
  - 4 - Vor
  - -1 - Übergang

  0 bis 4 werden nur dazu genutzt um über das WebFront den Player zu steuern. 5 ist ein Wert der nur kurzfristig angenommen wird, wenn die Audioquelle gewechselt wird.
- __Lautstärke__  
Diese Variable enthält die Aktuelle Lautstärke der Instanz und wird von der Funktion SNS_updateStatus() aktualisiert.
- __Mute__  
Diese Variable wird nur erstellt, wenn die Option "Mute-Steuerung" aktiviert ist.
Sie enthält den aktuelle Zustand ob die Instanz gemuted ist und wird von der Funktion SNS_updateStatus() aktualisiert.
- __Nachtmodus__  
Diese Variable wird nur erstellt, wenn die Option "Nachtmodus-Steuerung" aktiviert ist.  
Sie enthält den aktuellen Zustand ob der Nachtmodus eingeschaltet ist und wird von der Funktion SNS_updateStatus() aktualisiert.
- __Dialogverbesserung__  
Diese Variable wird nur erstellt, wenn die Option "Nachtmodus-Steuerung" aktiviert ist.  
Sie enthält den aktuellen Zustand ob die Dialogverbesserung eingeschaltet ist und wird von der Funktion SNS_updateStatus() aktualisiert.
- __Loudness__  
Diese Variable wird nur erstellt, wenn die Option "Loudness-Steuerung" aktiviert ist.  
Sie enthält den aktuellen Zustand ob bei der Instanz Loudness eingeschaltet ist und wird von der Funktion SNS_updateStatus() aktualisiert.
- __Tiefen__  
Diese Variable wird nur erstellt, wenn die Option "Tiefen-Steuerung" aktiviert ist.  
Sie enthält die aktuellen Equalizer Einstellungen der Instanz und wird von der Funktion SNS_updateStatus() aktualisiert.
- __Höhen__  
Diese Variable wird nur erstellt, wenn die Option "Höhen-Steuerung" aktiviert ist.  
Sie enthält die aktuellen Equalizer Einstellungen der Instanz und wird von der Funktion SNS_updateStatus() aktualisiert.
- __Balance__  
Diese Variable wird nur erstellt, wenn die Option "Balance-Steuerung" aktiviert ist.  
Sie enthält die aktuellen Equalizer Einstellungen der Instanz und wird von der Funktion SNS_updateStatus() aktualisiert.
- __Sleeptimer__  
Diese Variable wird nur erstellt, wenn die Option "Sleeptimer-Steuerung" aktiviert ist.  
Sie enthält die aktuellen Wert des Sleeptimers der Instanz und wird von der Funktion SNS_updateStatus() aktualisiert.  
Falls die Instanz Mitglied einer Gruppe ist, wird die Variable versteckt (hidden) und mit dem Wert aus dem Gruppenkoordinator befüllt.
- __Wiedergabeliste__  
Diese Variable hat normalerweise keinen Wert gepflegt. Sie dient nur dazu vom WebFront aus eine Playliste anstarten zu können.  
Lediglich direkt nach dem Drücken des Knopfes am WebFront wird die Variable für eine Sekunde auf den Gewählten Wert gesetzt. Dies soll dem Verwener ein kurzes Feedback geben.
- __Play Mode__
Diese Variable wird nur erstellt, wenn die Option "Playmode-Steuerung" aktiviert ist.  
In diese Variablen ist der aktuelle Wert des Play Mode abgelegt und wird von der Funktion SNS_updateStatus() aktualisiert. Die möglichen Werte sind:
  - 0: "NORMAL"
  - 1: "REPEAT_ALL"
  - 2: "REPEAT_ONE"
  - 3: "SHUFFLE_NOREPEAT"
  - 4: "SHUFFLE"
  - 5: "SHUFFLE_REPEAT_ONE"
- __Crossfade__  
Diese Variable wird nur erstellt, wenn die Option "Playmode-Steuerung" aktiviert ist.  
Sie enthält den aktuellen Wert der Crossfade Einstellungen und wird von der Funktion SNS_updateStatus() aktualisiert.
- __Titel URL__  
Diese Variable wird nur erstellt, wenn die Option "Detaillierte Informationen" aktiviert ist.  
Sie enthält die URL zu dem Cover das gerade in Sonos angezeigt wird. Dies gilt aber nur für Titel, nicht für Streams.  
Die Variable wird von der Funktion SNS_updateStatus() aktualisiert.
- __Content Stream__  
Diese Variable wird nur erstellt, wenn die Option "Detaillierte Informationen" aktiviert ist.  
Sie enthält den Conten Stram bei bei gestreamten Sender (z.B. aktuelle Informationen) und wird von der Funktion SNS_updateStatus() aktualisiert.
- __Künstler__  
Diese Variable wird nur erstellt, wenn die Option "Detaillierte Informationen" aktiviert ist.  
Sie enthält den Künster des aktuell abgespielten Titels und wird von der Funktion SNS_updateStatus() aktualisiert.
- __Album__  
Diese Variable wird nur erstellt, wenn die Option "Detaillierte Informationen" aktiviert ist.  
Sie enthält das Album des aktuell abgespielten Titels und wird von der Funktion SNS_updateStatus() aktualisiert.
- __Titellänge__  
Diese Variable wird nur erstellt, wenn die Option "Detaillierte Informationen" aktiviert ist.  
Sie enthält die länge des aktuell abgespielten Titels und wird von der Funktion SNS_updateStatus() aktualisiert.
- __Position__  
Diese Variable wird nur erstellt, wenn die Option "Detaillierte Informationen" aktiviert ist.  
Sie enthält die aktuelle Position in dem aktuell abgespielten Titels und wird von der Funktion SNS_updateStatus() aktualisiert.
- __Titel__  
Diese Variable wird nur erstellt, wenn die Option "Detaillierte Informationen" aktiviert ist.  
Sie enthält den Titel des aktuell abgespielten Titels und wird von der Funktion SNS_updateStatus() aktualisiert.
- __Sender ID__  
Diese Variable wird nur erstellt, wenn die Option "Detaillierte Informationen" aktiviert ist.  
Sie enthält den die StationID aus TuneIn und wird von der Funktion SNS_updateStatus() aktualisiert.
- __Details__  
Diese Variable wird nur erstellt, wenn die Option "Detaillierte Informationen" aktiviert ist.  
Dies ist eine HTMLBox die das Cover, den Titel den Künster, das Album und Positionsinfos anzeigt:  
![Details Song](imgs/details_song.png?raw=true "Details song")  
Wenn gerade ein Sender gestreamt wird, sind nur ContenStram und Titel enthalten:  
![Details Radio](imgs/details_radio.png?raw=true "Details Radio")  

## 7. Timer
### 1. Sonos Discovery  
Das Discovery Modul hat einen Timer "Sonos Discovery", welcher alle 5 Minuten die Funktion SNS_Discover() aufruft.  
Hierdurch werden neue Instanzen gefunden und in der Gerätesuche angezeigt.
### 2. Sonos Splitter  
Das Splitter Modul hat einen Timer "Sonos Update Grouping", welcher ensprechend der Konfuguration regelmäßig die Funktion SNS_updateGrouping() aufruft.  
Hierbei wird von einem der Player der "Zone Group Status" abgerufen. Dieser enthält eine Liste von Koordinatoren inklusive der Lautsprecher die ihm zugeordnet sind.  
Weiterhin werden Lautsprecher als "vaished" gemeldet, die nicht mehr in Sonos verfügbar sind.  
All diese Informationen werden aufbereitet und an die Player Instanzen geschickt.  
Diese passen dementsprechend die notwendigen Variablen an und blenden diese ein oder aus. All das, was durch die Funktion SNS_SetGroup() auch passiert.  
Zusätzlich werden die Instanzen welche als vanished gemeldet werden komplett ausgeblendet und werden als vanished markiert.  
Dies hat zur Folge, dass eine Exception geraised wird, wenn versucht wird eine Funktion auf einer solchen Instanz aufzurufen.
### 3. Sonos Player
Das Player Modul hat einen Timer "Sonos Update Status", welcher ensprechend der Konfuguration regelmäßig die Funktion SNS_updateStatus() aufruft.  
Es werden zu verschiedenen Variablen die aktuellen Werte ermittelt und gespeichert.

## 8. Funktionen
### 8.1. Sonos Discovery  
- __SNS_Discover(int $InstanceID)__  
Diese Funktion wird in regelmäßigen Abständen per Timer aufgerufen. Es ist nicht notwendig diese manuell auszuführen.

### 8.2. Sonos Splitter   
- __SNS_updateGrouping(int $InstanceID)__  
Diese Funktion wird in regelmäßigen Abständen per Timer aufgerufen. Es ist nicht notwendig diese manuell auszuführen.
- __SNS_ReadTunein(int $InstanceID, string $ip)__  
Diese Funktion ist nur für das Konfigurationsformular. Endbenutzer sollten diese Funktion nicht verwenden.
- __SNS_UpdatePlaylists(int $InstanceID)__  
Bei der Ausführung dieser Funktion werden entsprechend der Konfiguration "Import Playlists" die Playlisten aus dem Sonos System abgerufen und in dem Profil "SONOS.Playlists" gespeichert.
- __SNS_StopAll(int $InstanceID)__
Bei der Ausführung dieser Funktion wird SNS_Stop() an alle Player geschickt, die mit dem Splitter verbunden sind.
- __SNS_PauseAll(int $UbstanceID)__
Bei der Ausführung dieser Funktion wird SNS_Pause() an alle Player geschickt, die mit dem Splitter verbunden sind.

### 8.3. Sonos Player  
- __SNS_alexaResponse(int $InstanceID)__  
Diese Funktion dient dazu ein "Custom Skill für Alexa" bereitzustellen. Für den Endanwender eher uninteressant.  
- __SNS_BecomeCoordinator(int $InstanceID)__  
Diese funktion schaut zunächst nach, ob der Player bereits ein Koordinator ist. Falls es so ist, beendet sie sich ohne etwas zu tun.  
Falls der Player Teil einer Gruppe ist, ruft die Funktion automatisch die Funktion SNS_DelegateGroupCoordinationTo() bei dem aktuellen Koordinator auf und gibt seine eigene Instance ID als $newGroupCoordinator und true als $rejoinGroup mit.  
- __SNS_ChangeGroupVolume(int $InstanceID, int $increment)__  
Ändert die Lautstärke jedes Mitglieds einer Gruppe um den mitgelieferten Wert in $increment.  
Kann positiv oder negativ sein.  
Falls die Lautstärke 100 übersteigen oder 0 unterschreiten würde, wird die Lautstärke auf diese Werte gesetzt.  
- __SNS_ChangeVolume(int $InstanceID, int $increment)__  
Ändert die Lautstärke einer Sonos Instanz um den mitgelieferten Wert in $increment.  
Kann positiv oder negativ sein.  
Falls die Lautstärke 100 übersteigen oder 0 unterschreiten würde, wird die Lautstärke auf diese Werte gesetzt.  
- __SNS_DelegateGroupCoordinationTo(int $InstanceID, int $newGroupCoordinator, bool $rejoinGroup)__  
Macht einen anderen Lautsprecher zum Gruppenkoordinator.  
Wird auf die instanz des aktuellen Gruppenkoordinators ausgeführt. $newGroupKoordinator ist der neue.
Wenn der Lautsprecher Box in der Gruppe bleiben soll, muss $rejoinGroup "true" sein, ansonsten wird der Alte Koordinator aus der Gruppe entfernt.  
- __SNS_DeleteSleepTimer(int $InstanceID)__  
Bricht den Sleeptimer ab.  
Sollte das Kommando auf einem Gruppenmember ausgeführt werden, wird es automatisch an den zuständigen Koordinator weitergeleitet und gilt somit für die ganze Gruppe.  
- __SNS_IsCoordinator(int $InstanceID): bool__  
Diese Funktion liefert einen bool zurück, ob es sich bei dem Player zu diesem Zeitpunkt um einen Koordinator handelt.  
- __SNS_Next(int $InstanceID)__  
Springt zum nächsten Titel.  
Sollte das Kommando auf einem Gruppenmember ausgeführt werden, wird es automatisch an den zuständigen Koordinator weitergeleitet und gilt somit für die ganze Gruppe.  
- __SNS_Pause(int $InstanceID)__  
Pausiert die Wiedergabe.  
Sollte das Kommando auf einem Gruppenmember ausgeführt werden, wird es automatisch an den zuständigen Koordinator weitergeleitet und gilt somit für die ganze Gruppe.  
- __SNS_Play(int $InstanceID)__  
Setzt die Wiedergabe fort.  
Sollte das Kommando auf einem Gruppenmember ausgeführt werden, wird es automatisch an den zuständigen Koordinator weitergeleitet und gilt somit für die ganze Gruppe.  
- __SNS_PlayFiles(int $InstanceID, string $files, string $volumeChange)__  
  - Falls gerade etwas wiedergegeben wird, wird die Wiedergabe pausiert
  - Die Lautstärke wird entsprechend $volumeChange angepasst  
   - "0" würde die Lautstärke nicht ändern  
   - "17" würde die Lautstärke auf 17 setzen  
   - "+8" würde die Lautstärke um 8 anheben  
   - "-8" würde die Lautstärke um 8 absenken
  - Alle Dateien, die in dem (als JSON encodierten) Array $files angegeben wurden, werden abgespielt.  
Entweder von einem Samba Share (CIFS) (z.B. "//server.local.domain/share/file.mp3") oder von einem HTTP Server (z.B.: "http://ipsymcon.fritz.box:3777/user/ansage/hallo.mp3")
  - Die Ausgangslautstärke wird wieder hergestellt
  - Die Audioquelle wird wieder hergestellt
  - Falls eine Wiedergabe aktiv war, wird sie wieder gestartet
   
Falls die Instanz einer Gruppe zugeordnet ist, wird sie für die Wiedergabe der Dateien aus der Gruppe genommen und danach wieder hinzugefügt.  
Mehrere Dateien abzuspielen könnte so aussehen:  
```php
SNS_PlayFiles(17265, json_encode( Array( "//ipsymcon.fritz.box/sonos/bla.mp3",
                                         "http://www.sounds.com/blubb.mp3") ), 0);
```
- __SNS_PlayFilesGrouping(int $InstanceID, string $instances, string $files, string $volumeChange)__  
Diese Funktion ruft die Funktion SNS_PlayFiles() auf. Dementsprechend ist das (als JSON encodierte) array $files gleich aufgebaut.  
Vorher werden die in $instances mitgegebenen Instanzen zu der gruppe von $InstanceID hinzugefügt. Fall eine der Instanzen ein Gruppenkoordinator ist, werden alle Player während der Wiedergabe der Datei(en) aus der Gruppe entfernt.   
Das (als JSON encodierte) array $instances beinhaltet pro hinzuzufügender instanz einen Eintrag mit dem Key "&lt;instance ID&gt;" der hinzuzufügenden instanz und einem Array mit settings. Diese Array kennt derzeit lediglich einen Eintrag mit dem Key "volume" mit dem Volume Wert entsprechend dem $volumeChange aus der Funktion SNS_PlayFiles.  
Beispiel:
```php
SNS_PlayFilesGrouping(46954 , json_encode( array( 11774 => array( "volume" => 10),
                                                  27728 => array( "volume" => "+10"),
                                                  59962 => array( "volume" => 30) ) ), json_encode(array( IVNTTS_saveMP3(12748, "Dieser Text wird angesagt"))), 28 );
```
  - Die Instanzen 11774, 27728 und 59962 werden der Gruppe mit dem Koordinator 46954 hinzugefügt.  
  - Die Instanz 11774 wird auf Lautstärke 10 gesetzt.  
  - Bei der Instanz 27728 wird die Lautstärke um 10 Punkte angehoben.  
  - Die Instanz 59962 wird auf Lautstärke 30 gesetzt.  
  - Die Instanz 46954 wird Gruppen Koordinator für die Ansage(n) und wird auf Lautstärke 28 gesetzt.  
  - Der Text "Dieser Text wird angesagt" wird vom dem SymconIvona Modul (Instanz 12748) in eine MP3 umgewandelt, welche dann abgespielt wird.
- __SNS_Previous(int $InstanceID)__  
Startet den vorhergehenden Titel in der Liste.  
Sollte das Kommando auf einem Gruppenmember ausgeführt werden, wird es automatisch an den zuständigen Koordinator weitergeleitet und gilt somit für die ganze Gruppe.  
- __SNS_RampToVolume(int $InstanceID, string $rampType, int $volume)__  
Ruft die Funktion RampToVolume in Sonos auf.  
Der Parameter $rampType kann als integer oder als string übergeben werden.  
  - 1 entspricht SLEEP_TIMER_RAMP_TYPE
  - 2 entspricht ALARM_RAMP_TYPE
  - 3 entspricht AUTOPLAY_RAMP_TYPE
- __SNS_SetAnalogInput(int $InstanceID, int $input_instance)__  
Selektiert den Analogen Input einer Instanz als Audioquelle.  
Sollte die Instanz sich gerade in einer Gruppe befinden, wird sie automatisch aus der Gruppe genommen und danach die neue Audiquelle gesetzt.  
Sollte diese Funktion auf einem Gruppenkoordinator ausgeführt werden gilt die neue Audioquelle für die ganze Gruppe.  
- __SNS_SetBalance(int $InstanceID, int $balance)__  
Passt die Balance Einstellungen im Equalizer der Sonos Instanz an. Nur Sinnvoll bei Setreopaaren oder AMPS.  
Mögliche Werte liegen zwischen -100 (ganz links) und 100 (gnaz rechts).  
- __SNS_SetBass(int $InstanceID, int $bass)__  
Passt die Bass Einstellungen im Equalizer der Sonos Instanz an.  
Mögliche Werte liegen zwischen -10 und 10.  
- __SNS_SetCrossfade(int $InstanceID, bool $crossfade)__  
Schaltet den Crossfade Modus für eine Instanz ein oder aus.  
Falls die Instanz Teil einer Gruppe ist, wird das Kommano automatisch an den Gruppenkoordinator weitergeleitet.  
true und false sind gültige Werte für $crossfade.  
- __SNS_SetDialogLevel(int $InstanceID, bool $dialogLevel)__  
Schaltet die Dialogverbesserung einer Instanz. Dieses Feature wird nur von Playbar, Playbase und Beam untersützt.  
true und false sind gültige Werte für $dialogLevel.  
- __SNS_SetDefaultGroupVolume(int $InstanceID)__  
Führt die Funktion SNS_SetDefaultVolume( ) für jeden Mitglied einer Gruppe aus.  
- __SNS_SetDefaultVolume(int $InstanceID)__  
Ändert die Lautstärke einer Instanz auf die Standard Lautstärke.  
- __SNS_SetGroup(int $InstanceID, int $groupCoordinator)__  
Fügt die Instanz zu einer Gruppe hinzu oder entfernt es aus einer Gruppe.  
Wenn die InstanzID eines Gruppenkoordinators mitgegeben wird, wird die instanz dieser Gruppe hinzugefügt.  
Wenn 0 mitgegeben wird, wird die Instanz aus allen Gruppen entfernt.  
- __SNS_SetGroupVolume(int $InstanceID, int $volume)__  
Führt die Funktion SNS_ChangeGroupVolume($volume - "aktuelle Lautstärke" ) aus.  
- __SNS_SetHdmiInput(int $InstanceID, int $input_instance)__  
Selektiert den HDMI Input einer Instanz als Audioquelle.  
Sollte die Instanz sich gerade in einer Gruppe befinden, wird sie automatisch aus der Gruppe genommen und danach die neue Audiquelle gesetzt.  
Sollte diese Funktion auf einem Gruppenkoordinator ausgeführt werden gilt die neue Audioquelle für die ganze Gruppe.
Anmerkung: Da HDMI scheinbar genau wie S/PDIF behandelt wird, wird intern lediglich SetSpdifInput aufgerufen.  
- __SNS_SetLoudness(int $InstanceID, bool $loudness)__  
Setzt das Loundess Flag an einer Instanz.  
true und false sind gültige Werte für $loudness.  
- __SNS_SetMute(int $InstanceID, bool $mute)__  
Mutet or unmutet eine Instanz.
true und false sind gültige Werte für $mute.  
- __SNS_SetMuteGroup(int $InstanceID, bool $mute)__  
Mutet or unmutet alle Instanzen einer Gruppe.
true und false sind gültige Werte für $mute.  
- __SNS_SetNightMode(int $InstanceID, bool $nightMode)__  
Schaltet den Nachmodus einer Instanz. Dieses Feature wird nur von Playbar, Playbase und Beam untersützt.  
true und false sind gültige Werte für $nightMode.  
- __SNS_SetPlaylist(int $InstanceID, string $name)__  
Entfernt alle Titel aus einer Queue und fügt alle Titel einer Playliste hinzu.  
Der Name der Playliste muss in Sonos bekannt sein.  
Es wird zunächst nach dem Namen in den gespeicherten Playlisten gesucht. Wird er dort nciht gefunden, wird ebenfalls in den Importierten Playlisten gesucht. Dabei wird ein Unterstrich ("_") durch ein Leerzeichen (" ") ersetzt und die Endungen ".m3u" und ".M3U" werden entfernt. Somit kann z.B. die Playliste mit dem Name "3_Doors_Down.m3u" mit dem Befehl SNS_SetPlaylist(12345,"3 Doors Down"); gestartet werden.  
Wird die Playlist auch hier nicht gefunden, wird zuletzt in den Favoriten gesucht.
Sollte die Instanz sich gerade in einer Gruppe befinden, wird sie automatisch aus der Gruppe genommen und danach die neue Audiquelle gesetzt.  
Sollte diese Funktion auf einem Gruppenkoordinator ausgeführt werden gilt die neue Audioquelle für die ganze Gruppe.  
- __SNS_SetPlayMode(int $InstanceID, int $playMode)__  
Setzt den Play Mode einer Sonos Instanz.  
Falls die Instanz Mitglied einer Gruppe ist, wird das Kommando automatisch an den Gruppenkoordinator weitergeleitet.  
Mögliche Werte für den Play Mode sind:
  - 0: "NORMAL"
  - 1: "REPEAT_ALL"
  - 2: "REPEAT_ONE"
  - 3: "SHUFFLE_NOREPEAT"
  - 4: "SHUFFLE"
  - 5: "SHUFFLE_REPEAT_ONE"
- __SNS_SetRadio(int $InstanceID, string $radio)__  
Setzt die Audioquelle auf die URL des in $radio mitgegebenen Radiosenders.  
Dieser muss hierzu in der Splitter Instanz gepflegt sein.
Sollte die Instanz sich gerade in einer Gruppe befinden, wird sie automatisch aus der Gruppe genommen und danach die neue Audiquelle gesetzt.  
Sollte diese Funktion auf einem Gruppenkoordinator ausgeführt werden gilt die neue Audioquelle für die ganze Gruppe.
- __SNS_SetSleepTimer(int $InstanceID, int $minutes)__  
Setzt den Sleeptimer auf die angegebene Anzahl an Minuten.  
Sollte das Kommando auf einem Gruppenmember ausgeführt werden, wird es automatisch an den zuständigen Koordinator weitergeleitet und gilt somit für die ganze Gruppe.  
- __SNS_SetSpdifInput(int $InstanceID, int $input_instance)__  
Selektiert den SPDIF Input einer Instanz als Audioquelle.  
Sollte die Instanz sich gerade in einer Gruppe befinden, wird sie automatisch aus der Gruppe genommen und danach die neue Audiquelle gesetzt.  
Sollte diese Funktion auf einem Gruppenkoordinator ausgeführt werden gilt die neue Audioquelle für die ganze Gruppe.  
- __SNS_SetTransportURI(int $InstanceID, string $uri)__  
Setzt die Transport URI auf den angegebenen Wert.  
Sollte die Instanz sich gerade in einer Gruppe befinden, wird sie automatisch aus der Gruppe genommen und danach die neue Audiquelle gesetzt.
Sollte diese Funktion auf einem Gruppenkoordinator ausgeführt werden gilt die neue Audioquelle für die ganze Gruppe.  
- __SNS_SetTreble(int $InstanceID, int $treble)__  
Passt die Treble Einstellungen im Equalizer der Sonos Instanz an.
Mögliche Werte liegen zwischen -10 und 10.  
- __SNS_SetVolume(int $InstanceID, int $volume)__  
Passt die Lautstärke einer Instanz an.
Mögliche Werte liegen zwischen 0 and 100.  
- __SNS_Stop(int $InstanceID)__  
Hält die Wiedergabe an.  
Sollte das Kommando auf einem Gruppenmember ausgeführt werden, wird es automatisch an den zuständigen Koordinator weitergeleitet und gilt somit für die ganze Gruppe.  
- __SNS_updateStatus(int $InstanceID)__  
Diese Funktion wird in regelmäßigen Abständen per Timer aufgerufen. Es ist nicht notwendig diese manuell auszuführen.  
- __SNS_getRINCON(int $InstanceID, string $ip)__  
Diese Funktion ist nur für das Konfigurationsformular. Endbenutzer sollten diese Funktion nicht verwenden.

## 9. Update
Um von der [alten Version des Sonos Moduls](https://github.com/tkugelberg/SymconSonos) zu dieser zu wechseln sind ein paar manuelle Schritte notwendig.  
Der Grund hierfür ist, dass sich z.B. die ID des Moduls geändert hat.  
### 1. sichern der alten ObjectIDs  
Damit man später besser aufräumen kann, ist es sinnvoll sich alle ObjectIDs aus Symcon zu merken.  
Diese Script gibt euch alle IDs + Namen aus.  
```php
$SonosPlayers = IPS_GetInstanceListByModuleID("{F6F3A773-F685-4FD2-805E-83FD99407EE8}");

foreach ( $SonosPlayers as $SonosPlayer ){
    print $SonosPlayer." -> ".IPS_GetName($SonosPlayer)."\n";
     getChild($SonosPlayer,'');
}

function getChild($Parent, $spacer){
    $spacer = "\t".$spacer;
    foreach (IPS_GetObject($Parent)["ChildrenIDs"] as $Child){
        print $spacer.$Child." -> ".IPS_GetName($Child)."\n";
        getChild($Child,$spacer);
    }
}
```
--> die Ausgabe in eine Textdatei kopieren und gut aufheben.
### 2. Alte Instanzen löschen  
Wie gesagt, alle Instanzen des alten Moduls löschen.
### 3. Profile löschen  
Die Namen der Profile werden sich ändern (Das "SONOS" kommt nach vorne). Daher sollten die alten gelöscht werden.  
Das geht mit diesem Script:
```php
if (IPS_VariableProfileExists("Balance.SONOS"))
        IPS_DeleteVariableProfile("Balance.SONOS");

if (IPS_VariableProfileExists("Groups.SONOS"))
        IPS_DeleteVariableProfile("Groups.SONOS");

if (IPS_VariableProfileExists("Playlist.SONOS"))
        IPS_DeleteVariableProfile("Playlist.SONOS");

if (IPS_VariableProfileExists("PlayMode.SONOS"))
        IPS_DeleteVariableProfile("PlayMode.SONOS");

if (IPS_VariableProfileExists("Radio.SONOS"))
        IPS_DeleteVariableProfile("Radio.SONOS");

if (IPS_VariableProfileExists("Status.SONOS"))
        IPS_DeleteVariableProfile("Status.SONOS");

if (IPS_VariableProfileExists("Switch.SONOS"))
        IPS_DeleteVariableProfile("Switch.SONOS");

if (IPS_VariableProfileExists("Tone.SONOS"))
        IPS_DeleteVariableProfile("Tone.SONOS");

if (IPS_VariableProfileExists("Volume.SONOS"))
        IPS_DeleteVariableProfile("Volume.SONOS");
``` 
### 4. altes Modul löschen  
Im "Module Control" (Kern Instanzen->Modules) muss das alte Modul entfernt werden.
### 5. neues Modul installieren
Wie oben beschrieben.

## 10. Was ist neu?
- Automatisches Discovery
- Splitter Instanz
  - zentrale Konfiguration
  - Grouping
  - Kommunikation untereinander
- ForceGrouping wird zu RejoinGroup
  - Findet nur noch erstmalig statt, nachdem der Lautsprecher verschwunden war
- Vanished
  - Wenn ein Lautsprecher in Sonos als "vanished" gekennzeichnet ist, wird er in IP-Symcon versteckt.
- Radio Konfiguration
  - Radiosender können komplett individuell in der Splitter Instanz konfiguriert werden
  - Es gibt keine ausgelieferten Radiosender mehr
- Die Variablen "Coordinator" und "Group Members" sind jetzt als Attribute modelliert.
- Falls konfiguriert, wird ein Medien Objekt angelegt, das mit dem Inhalt von "Cover URL" gefüllt wird