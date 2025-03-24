<?php

	declare(strict_types=1);

	class DaikinWLAN extends IPSModule
	{
		private const STATUS_INST_IP_IS_EMPTY   = 202;
		private const STATUS_INST_IP_IS_INVALID = 204; //IP Adresse ist ungültig

		var $Daikin_Properties = array(
			array("name" => "Status",                "ident" => "dknPowerSwitch",       "pos" => 1,  "anzeige" => "",     "varType" => 0,  "varProfile" => "~Switch",              "queryType" => "basic_info",         "value"=>"pow",     "varHasAction" => true),
			array("name" => "Temperatur Istwert",    "ident" => "dknCurrentTemp",       "pos" => 3,  "anzeige" => "",     "varType" => 2,  "varProfile" => "~Temperature",         "queryType" => "get_sensor_info",    "value"=>"htemp",   "varHasAction" => false),
			array("name" => "Feuchte Istwert",       "ident" => "dknCurrentHumidity",   "pos" => 5,  "anzeige" => "hui",  "varType" => 1,  "varProfile" => "~Humidity",            "queryType" => "get_sensor_info",    "value"=>"hhum",    "varHasAction" => false),
			array("name" => "Temperatur Sollwert",   "ident" => "dknSetTempValue",      "pos" => 4,  "anzeige" => "",     "varType" => 2,  "varProfile" => "Daikin.Temperature",   "queryType" => "get_control_info",   "value"=>"stemp",   "varHasAction" => true),
			array("name" => "Feuchte Sollwert",      "ident" => "dknSetHumidityValue",  "pos" => 6,  "anzeige" => "hus",  "varType" => 1,  "varProfile" => "~Humidity",            "queryType" => "get_control_info",   "value"=>"shum",    "varHasAction" => true),
			array("name" => "Temperatur Außen",      "ident" => "dknCurrentOutTemp",    "pos" => 2,  "anzeige" => "out",  "varType" => 2,  "varProfile" => "~Temperature",         "queryType" => "get_sensor_info",    "value"=>"otemp",   "varHasAction" => false),
			array("name" => "Modus",                 "ident" => "dknSetModeValue",      "pos" => 7,  "anzeige" => "",     "varType" => 1,  "varProfile" => "Daikin.Mode",          "queryType" => "get_control_info",   "value"=>"mode",    "varHasAction" => true),
			array("name" => "Lüfterstufe",           "ident" => "dknSetFanRateValue",   "pos" => 8,  "anzeige" => "",     "varType" => 1,  "varProfile" => "Daikin.FanRate",       "queryType" => "get_control_info",   "value"=>"f_rate",  "varHasAction" => true),
			array("name" => "Lüfterrichtung",        "ident" => "dknSetFanDirValue",    "pos" => 9,  "anzeige" => "",     "varType" => 1,  "varProfile" => "Daikin.FanDirection",  "queryType" => "get_control_info",   "value"=>"f_dir",   "varHasAction" => true),
			array("name" => "Streamer",              "ident" => "dknSetStreamer",       "pos" => 10, "anzeige" => "str",  "varType" => 0,  "varProfile" => "~Switch",              "queryType" => "get_control_info",   "value"=>"adv",     "varHasAction" => true),
			array("name" => "Leistungsstark",        "ident" => "dknSetBooster",        "pos" => 11, "anzeige" => "bos",  "varType" => 0,  "varProfile" => "~Switch",              "queryType" => "get_control_info",   "value"=>"adv",     "varHasAction" => true),
			array("name" => "Kompressor Auslastung", "ident" => "dknCompressor",        "pos" => 12, "anzeige" => "aus",  "varType" => 1,  "varProfile" => "~Intensity.100",       "queryType" => "get_sensor_info",    "value"=>"cmpfreq", "varHasAction" => false),
			array("name" => "HomeKit Status",        "ident" => "dknHomeKitState",      "pos" => 13, "anzeige" => "hki",  "varType" => 1,  "varProfile" => "Daikin.HomeKitState",  "queryType" => "external",           "value"=>"hsk",     "varHasAction" => true),
			array("name" => "Fehlermeldung",         "ident" => "dknErrorMessage",      "pos" => 14, "anzeige" => "err",  "varType" => 3,  "varProfile" => "",                     "queryType" => "basic_info",         "value"=>"err",     "varHasAction" => false),
			array("name" => "Firmware Version",      "ident" => "dknFirmware",          "pos" => 15, "anzeige" => "inf",  "varType" => 3,  "varProfile" => "",                     "queryType" => "basic_info",         "value"=>"ver",     "varHasAction" => false),
			array("name" => "MAC Adresse",           "ident" => "dknMAC",               "pos" => 16, "anzeige" => "inf",  "varType" => 3,  "varProfile" => "",                     "queryType" => "basic_info",         "value"=>"mac",     "varHasAction" => false)
		);

		var $aQueryTypes = array("common/basic_info", "aircon/get_control_info", "aircon/get_sensor_info");
	   
		var $aTranslatefRateRead  = array("A" => 0, "B" => 1, "3" => 2, "4" => 3, "5" => 4, "6" => 5, "7" => 6);
		var $aTranslatefRateWrite = array(0 => "A", 1 => "B", 2 => "3", 3 => "4", 4 => "5", 5 => "6", 6 => "7");

		var $aErrorCodeTranslation = array(
			"0" => "Kein Fehler",
			"10000" => "Konkurriende Modi (Kalt/Warm) angefordert",
			"U0" => "Zu wenig Kältemittel",
			"U2" => "Überspannung erkannt",
			"U4" => "Fehler bei Signalübertragung Innen<->Außen",
			"UA" => "Fehler bei Kombination Innen und Außengerät",
			"A1" => "Fehler bei Platine der Inneneinheit",
			"A5" => "Schutz gegen Einfrieren oder Hochdruck-Kontrolle",
			"A6" => "Störung: Ventilatormotor Innengerät",
			"AH" => "Fehler bei Streamer-Einheit",
			"C4" => "Fehler bei Wärmetauscher-Thermistor der Inneneinheit",
			"C7" => "Fehler bei Öffnen oder Schließen der Frontblende",
			"C9" => "Fehler bei Raumtemperatur-Thermistor",
			"CC" => "Fehler bei Feuchtigkeitssensor",
			"CE" => "Störung: Intelligenter Thermosensor",			
			"E1" => "Fehler bei Platine der Außeneinheit",
			"E3" => "Außengerät: Auslösung Hochdruckschalter ",
			"E5" => "Aktivierung des Überlastschutzes (Überlastung des Verdichters)",
			"E6" => "Verdichter-Blockierung",
			"E7" => "Blockierung des Gleichstrom-Ventilators",
			"E8" => "Eingangsstrom-Überstrom",
			"EA" => "Fehler bei 4-Wege-Ventil",
			"F3" => "Temperatursteuerung bei Abflussrohr",
			"F6" => "Hochdruck-Kontrolle (bei Kühlen)",
			"F8" => "Systemabschaltung aufgrund zu hoher interner Verdichtertemperatur",
			"H0" => "Fehler bei Sensor des Verdichtersystems",
			"H3" => "Außengerät: Störung Hochdruckschalter",
			"H6" => "Fehler bei Positionssensor",
			"H8" => "Fehler bei Sensor DC-Spannung / Stromstärke",
			"H9" => "Fehler bei Außenlufttemperatur-Thermistor",
			"J3" => "Fehler bei Thermistor des Abflussrohrs",
			"J6" => "Fehler bei Wärmetauscher-Thermistor der Außeneinheit",
			"L3" => "Fehler durch Überhitzung einer elektrischen Komponente",
			"L4" => "Anstieg bei Radiatorlamellen-Temperatur",
			"L5" => "Momentaner Überstrom bei Inverter (Gleichstrom)",
			"P4" => "Fehler bei Radiatiorlamellen-Thermistor",			
			"U0" => "Außengerät: Kältemittelmangel",
			"U2" => "Außengerät: Fehler Versorgungsspannung",
			"U4" => "Kommunikationsproblem Innen-/Außengerät",
			"U5" => "Fehlfunktion Übertragung zwischen Innengerät und Fernbedienung",
			"UA" => "Problem wegen Konflikt Innengerät, Außengerät",
		);
		
		public function Create(){
			//Never delete this line!
			parent::Create();

			// Erzeuge die eignen Profile
			$this->CreateVariableProfiles();

			$this->RegisterPropertyString('IPAddress', "0.0.0.0");
			$this->RegisterPropertyBoolean('StatusEmulieren', true);
			$this->RegisterPropertyInteger('Istfeuchte', 0);
			$this->RegisterPropertyInteger('Sollfeuchte', 1);
			$this->RegisterPropertyInteger('Aussentemp', 1);
			$this->RegisterPropertyInteger('Compressor', 1);
			$this->RegisterPropertyInteger('Infos', 0);
			$this->RegisterPropertyInteger('Streamer', 1);
			$this->RegisterPropertyInteger('Leistungsstark', 1);
			$this->RegisterPropertyInteger('Errormessage', 1);
			$this->RegisterPropertyInteger('ErrorConnectmessage', 1);
			$this->RegisterPropertyInteger('Interval', 0);
			$this->RegisterPropertyInteger('HomeKit', 0);

			$this->RegisterAttributeFloat('autoTemp', 20);
			$this->RegisterAttributeFloat('coolTemp', 20);
			$this->RegisterAttributeFloat('heatTemp', 20);
			
			$this->RegisterAttributeString('autoSpeed', "");
			$this->RegisterAttributeString('dhumSpeed', "");
			$this->RegisterAttributeString('coolSpeed', "");
			$this->RegisterAttributeString('heatSpeed', "");
			$this->RegisterAttributeString('fanSpeed', "");

			$this->RegisterAttributeInteger('autoDir', 0);
			$this->RegisterAttributeInteger('dhumDir', 0);
			$this->RegisterAttributeInteger('coolDir', 0);
			$this->RegisterAttributeInteger('heatDir', 0);
			$this->RegisterAttributeInteger('fanDir', 0);

			$this->RegisterAttributeInteger('autoHum', 20);
			$this->RegisterAttributeInteger('dhumHum', 20);
			$this->RegisterAttributeInteger('coolHum', 20);
			$this->RegisterAttributeInteger('heatHum', 20);

			$this->RegisterTimer('UpdateTimer', 0, 'DKN_RequestRead($_IPS["TARGET"]);');

		}

		public function Destroy(){
			//Never delete this line!
			parent::Destroy();
		}

		public function ApplyChanges(){
			//Never delete this line!
			parent::ApplyChanges();

			// create variables
			foreach ($this->Daikin_Properties as $property) {

				if($property['anzeige'] == "hui" && $this->ReadPropertyInteger("Istfeuchte") == 0) {
					@$this->DisableAction($property['ident']);
					$this->UnregisterVariable($property['ident']);
					continue;
				}

				if($property['anzeige'] == "hus" && $this->ReadPropertyInteger("Sollfeuchte") == 0) {
					@$this->DisableAction($property['ident']);
					$this->UnregisterVariable($property['ident']);
					continue;
				}

				if($property['anzeige'] == "bos" && $this->ReadPropertyInteger("Leistungsstark") == 0) {
					@$this->DisableAction($property['ident']);
					$this->UnregisterVariable($property['ident']);
					continue;
				}

				if($property['anzeige'] == "str" && $this->ReadPropertyInteger("Streamer") == 0) {
					@$this->DisableAction($property['ident']);
					$this->UnregisterVariable($property['ident']);
					continue;
				}

				if($property['anzeige'] == "out" && $this->ReadPropertyInteger("Aussentemp") == 0) {
					@$this->DisableAction($property['ident']);
					$this->UnregisterVariable($property['ident']);
					continue;
				}

				if($property['anzeige'] == "aus" && $this->ReadPropertyInteger("Compressor") == 0) {
					@$this->DisableAction($property['ident']);
					$this->UnregisterVariable($property['ident']);
					continue;
				}

				if($property['anzeige'] == "err" && $this->ReadPropertyInteger("Errormessage") == 0) {
					@$this->DisableAction($property['ident']);
					$this->UnregisterVariable($property['ident']);
					continue;
				}

				if($property['anzeige'] == "inf" && $this->ReadPropertyInteger("Infos") == 0) {
					@$this->DisableAction($property['ident']);
					$this->UnregisterVariable($property['ident']);
					continue;
				}

				if($property['anzeige'] == "hki" && $this->ReadPropertyInteger("HomeKit") == 0) {
					@$this->DisableAction($property['ident']);
					$this->UnregisterVariable($property['ident']);
					continue;
				}

				$var = @IPS_GetObjectIDByIdent($property['ident'], $this->InstanceID);
				if(!$var) {
					switch ($property['varType']) {
						case 0:
							if($property['varProfile'] != null && IPS_VariableProfileExists($property['varProfile'])) {
								$this->RegisterVariableBoolean($property['ident'], $property['name'], $property['varProfile'], $property['pos']);
							}
							else {
								$this->RegisterVariableBoolean($property['ident'], $property['name'], "", $property['pos']);
							}
							break;
						case 1:
							if($property['varProfile'] != null && IPS_VariableProfileExists($property['varProfile'])) {
								$this->RegisterVariableInteger($property['ident'], $property['name'], $property['varProfile'], $property['pos']);
							}
							else {
								$this->RegisterVariableInteger($property['ident'], $property['name'], "", $property['pos']);
							}
							break;
						case 2:
							if($property['varProfile'] != null && IPS_VariableProfileExists($property['varProfile'])) {
								$this->RegisterVariableFloat($property['ident'], $property['name'], $property['varProfile'], $property['pos']);
							}
							else {
								$this->RegisterVariableFloat($property['ident'], $property['name'], "", $property['pos']);
							}
							break;
						case 3:
							if($property['varProfile'] != null && IPS_VariableProfileExists($property['varProfile'])) {
								$this->RegisterVariableString($property['ident'], $property['name'], $property['varProfile'], $property['pos']);
							}
							else {
								$this->RegisterVariableString($property['ident'], $property['name'], "", $property['pos']);
							}
							break;
						}
				}
				if($property['varHasAction'])
					$this->EnableAction($property['ident']);
				else
					$this->DisableAction($property['ident']);
			}

			if ($this->ReadPropertyInteger('Interval') > 0) {
				$this->SetTimerInterval('UpdateTimer', $this->ReadPropertyInteger('Interval')*1000); //Angabe Minuten in ms
			} else {
				$this->SetTimerInterval('UpdateTimer', 0);
			}			

			$this->SetInstanceStatus();
		}

		public function RequestRead(){
			$aData = $this->QueryAircon();
			if($aData === false) return false;

			foreach ($this->Daikin_Properties as $Variable) {

				if($Variable['anzeige'] == "out" && $this->ReadPropertyInteger("Aussentemp") == 0) {continue;}
				if($Variable['anzeige'] == "aus" && $this->ReadPropertyInteger("Compressor") == 0) {continue;}
				if($Variable['anzeige'] == "err" && $this->ReadPropertyInteger("Errormessage") == 0) {continue;}
				if($Variable['anzeige'] == "inf" && $this->ReadPropertyInteger("Infos") == 0) {continue;}
				if($Variable['anzeige'] == "hui" && $this->ReadPropertyInteger("Istfeuchte") == 0) {continue;}
				if($Variable['anzeige'] == "hus" && $this->ReadPropertyInteger("Sollfeuchte") == 0) {continue;}
				if($Variable['anzeige'] == "str" && $this->ReadPropertyInteger("Streamer") == 0) {continue;}
				if($Variable['anzeige'] == "bos" && $this->ReadPropertyInteger("Leistungsstark") == 0) {continue;}
				if($Variable['anzeige'] == "hki") {continue;}

				$id = $this->GetIDForIdent($Variable['ident']);
				switch ($Variable['varType']){
					case 0:
						if($Variable['ident'] == 'dknSetStreamer'){
							if($aData[$Variable['queryType']][$Variable['value']] == "13" or $aData[$Variable['queryType']][$Variable['value']] == "2/13"){
								SetValueBoolean($id, 1);
							}
							else{
								SetValueBoolean($id, 0);
							}
						}
						elseif ($Variable['ident'] == 'dknSetBooster'){
							if($aData[$Variable['queryType']][$Variable['value']] == "2" or $aData[$Variable['queryType']][$Variable['value']] == "2/13"){
								SetValueBoolean($id, 1);
							}
							else{
								SetValueBoolean($id, 0);
							}
						}
						else{						
							SetValueBoolean($id, $aData[$Variable['queryType']][$Variable['value']]);
							if($Variable['ident'] == 'dknPowerSwitch' && $this->ReadPropertyInteger("HomeKit") == 1 ){
								$hkiStatusId = @IPS_GetObjectIDByIdent('dknHomeKitState', $this->InstanceID);
								$modus = @IPS_GetObjectIDByIdent('dknSetModeValue', $this->InstanceID);
								if(GetValue($id) == 1){
									// 0: Aus, 1: Heizen, 2: Kühlen, 3: Automatisch
									// default ist automatik
									$lHomekitMode = 3;
									switch( $modus )
									{
										case 1:  // Automatik
										case 2:  // Entfeuchten
										case 6:  // Lüften
											$lHomekitMode = 3;
										break;
										case 3:  // Kühlen
											$lHomekitMode = 2;
										break;
										case 4:  // Heizen
											$lHomekitMode = 1;
										break;
									}
									SetValueInteger($hkiStatusId, $lHomekitMode);	
								}
								else{
									SetValueInteger($hkiStatusId, 0);									
								}
							}
						}
						break;
					case 1:
						if($Variable['ident'] == 'dknSetFanRateValue'){
							SetValueInteger($id, $this->aTranslatefRateRead[$aData[$Variable['queryType']][$Variable['value']]]);
							continue 2;
						}
						if (is_numeric($aData[$Variable['queryType']][$Variable['value']])){
							if($Variable['ident'] == 'dknSetModeValue'){
								$temp = intval($aData[$Variable['queryType']][$Variable['value']]);
								if($temp == 7 or $temp == 0){
									$temp=1;
								}
								SetValueInteger($id, $temp);
								
								if($this->ReadPropertyInteger("HomeKit") == 1){
									$hkiStatusId = @IPS_GetObjectIDByIdent('dknHomeKitState', $this->InstanceID);
									$PowerStatusID = @IPS_GetObjectIDByIdent('dknPowerSwitch', $this->InstanceID);
									if(GetValue($PowerStatusID) == 1){
										// 0: Aus, 1: Heizen, 2: Kühlen, 3: Automatisch
										// default ist automatik
										$lHomekitMode = 3;
										switch( $temp )
										{
											case 1:  // Automatik
											case 2:  // Entfeuchten
											case 6:  // Lüften
												$lHomekitMode = 3;
											break;
											case 3:  // Kühlen
												$lHomekitMode = 2;
											break;
											case 4:  // Heizen
												$lHomekitMode = 1;
											break;
										}
										SetValueInteger($hkiStatusId, $lHomekitMode);
									}
								}
							}		
							else{
								SetValueInteger($id, $aData[$Variable['queryType']][$Variable['value']]);
							}
						}
						break;
					case 2:
						if (is_numeric($aData[$Variable['queryType']][$Variable['value']])){
							SetValueFloat($id, $aData[$Variable['queryType']][$Variable['value']]);
						}
						break;
					case 3:
						if($Variable['ident'] == 'dknErrorMessage'){
							if (isset($aData[$Variable['queryType']][$Variable['value']]) && isset($this->aErrorCodeTranslation[$aData[$Variable['queryType']][$Variable['value']]])) {
								SetValueString($id, $this->aErrorCodeTranslation[$aData[$Variable['queryType']][$Variable['value']]]);
							} else {
								IPS_LogMessage("DaikinWLAN", "Warnung: Fehlercode nicht gefunden für ID: $id");
							}
						}
						elseif($Variable['ident'] == 'dknFirmware'){
							$Wert = str_replace("_", ".", $aData[$Variable['queryType']][$Variable['value']]);
							SetValueString($id, $Wert);
						}
						elseif($Variable['ident'] == 'dknMAC'){
							$Wert = implode(":", str_split($aData[$Variable['queryType']][$Variable['value']],2)); 
							SetValueString($id, $Wert);
						}
						else{
							SetValueString($id, $aData[$Variable['queryType']][$Variable['value']]);
						}
						break;
				}
			}

			if (is_numeric($aData["get_control_info"]['dt1'])){ $this->WriteAttributeFloat('autoTemp', $aData["get_control_info"]['dt1']); }
			if (is_numeric($aData["get_control_info"]['dt1'])){ $this->WriteAttributeFloat('coolTemp', $aData["get_control_info"]['dt3']); }
			if (is_numeric($aData["get_control_info"]['dt1'])){ $this->WriteAttributeFloat('heatTemp', $aData["get_control_info"]['dt4']); }

			$this->WriteAttributeString('autoSpeed', $aData["get_control_info"]['dfr1']);
			$this->WriteAttributeString('dhumSpeed', $aData["get_control_info"]['dfr2']);
			$this->WriteAttributeString('dhumSpeed', 'A');
			$this->WriteAttributeString('coolSpeed', $aData["get_control_info"]['dfr3']);
			$this->WriteAttributeString('heatSpeed', $aData["get_control_info"]['dfr4']);
			$this->WriteAttributeString('fanSpeed', $aData["get_control_info"]['dfr6']);

			if (is_numeric($aData["get_control_info"]['dfd2'])){ $this->WriteAttributeInteger('autoDir', $aData["get_control_info"]['dfd1']); }
			if (is_numeric($aData["get_control_info"]['dfd2'])){ $this->WriteAttributeInteger('dhumDir', $aData["get_control_info"]['dfd2']); }
			if (is_numeric($aData["get_control_info"]['dfd3'])){ $this->WriteAttributeInteger('coolDir', $aData["get_control_info"]['dfd3']); }
			if (is_numeric($aData["get_control_info"]['dfd4'])){ $this->WriteAttributeInteger('heatDir', $aData["get_control_info"]['dfd4']); }
			if (is_numeric($aData["get_control_info"]['dfd6'])){ $this->WriteAttributeInteger('fanDir', $aData["get_control_info"]['dfd6']); }

			if (is_numeric($aData["get_control_info"]['dh1'])){ $this->WriteAttributeInteger('autoHum', $aData["get_control_info"]['dh1']); }
			if (is_numeric($aData["get_control_info"]['dh2'])){ $this->WriteAttributeInteger('dhumHum', $aData["get_control_info"]['dh2']); }
			if (is_numeric($aData["get_control_info"]['dh3'])){ $this->WriteAttributeInteger('coolHum', $aData["get_control_info"]['dh3']); }
			if (is_numeric($aData["get_control_info"]['dh4'])){ $this->WriteAttributeInteger('heatHum', $aData["get_control_info"]['dh4']); }
		}

		public function RequestAction($Ident, $Value){ 
			$data = array();
			$ip = $this->ReadPropertyString('IPAddress');
			
			switch ($Ident){
				case 'dknSetStreamer':
					$this->SetStreamerValue($Value);
					break;
				case 'dknSetBooster':
					$this->SetBoosterValue($Value);
					break;
				case 'dknSetHumidityValue':
					$this->SetHumidityValue($Value);
					break;
				case 'dknSetTempValue':
					$this->SetTempValue($Value);
					break;
				case 'dknSetFanRateValue':
					$this->SetFanRateValue($Value);
					break;
				case 'dknSetFanDirValue':
					$this->SetFanDirValue($Value);
					break;
				case 'dknSetModeValue':
					$this->SetModeValue($Value);
					break;
				case 'dknPowerSwitch':
					$this->SetPowerSwitch($Value);
					break;
				case 'dknHomeKitState':
					$this->SetHomeKitState($Value);
					break;
				}

		}

		public function SetStreamerValue(bool  $Value){
			$ip = $this->ReadPropertyString('IPAddress');
			$data = array();

			if($Value < 0) {$Value = 0;}
			if($Value > 1) {$Value = 1;}

			if ($this->ReadPropertyBoolean('StatusEmulieren') == true)
			{
				$this->SetValue('dknSetStreamer', $Value);
			}

			if($Value == 1 ){
				$data += ['en_streamer' => '1'];
			}else{
				$data += ['en_streamer' => '0'];
			}
			
			$options = array( 
				'http' => array( 
					'header'  => "Content-type: application/x-www-form-urlencoded", 
					'method'  => 'GET',
					'timeout' => 2,
					'content' => http_build_query($data) 
				) 
			);				

			$context = stream_context_create($options); 
			$content = http_build_query($data);
			$this->LogMessage("http://$ip/aircon/set_special_mode?$content", KL_NOTIFY);
			$result  = @file_get_contents("http://$ip/aircon/set_special_mode?$content", false, $context); 
			$data = explode(",", $result);
			if($data[0] !== "ret=OK") {
				$this->LogMessage("Fehler beim Schreiben auf die Klimaanlage $ip: $result", KL_ERROR);
			}
			else{
				$this->QueryAircon();
			}
		}

		public function SetBoosterValue(bool  $Value){
			$id = $this->GetIDForIdent('dknPowerSwitch');
			if(GetValueBoolean($id) != 1) return;

			$ip = $this->ReadPropertyString('IPAddress');
			$data = array();

			if($Value < 0) {$Value = 0;}
			if($Value > 1) {$Value = 1;}

			if ($this->ReadPropertyBoolean('StatusEmulieren') == true)
			{
				$this->SetValue('dknSetBooster', $Value);
			}

			if($Value == 1 ){
				$data += ['set_spmode' => '1'];
			}else{
				$data += ['set_spmode' => '0'];
			}
			$data += ['spmode_kind' => '1'];

			$options = array( 
				'http' => array( 
					'header'  => "Content-type: application/x-www-form-urlencoded", 
					'method'  => 'GET',
					'timeout' => 2,
					'content' => http_build_query($data) 
				) 
			);				

			$context = stream_context_create($options); 
			$content = http_build_query($data);
			$this->LogMessage("http://$ip/aircon/set_special_mode?$content", KL_NOTIFY);
			$result  = @file_get_contents("http://$ip/aircon/set_special_mode?$content", false, $context); 
			$data = explode(",", $result);
			if($data[0] !== "ret=OK") {
				$this->LogMessage("Fehler beim Schreiben auf die Klimaanlage $ip: $result", KL_ERROR);
			}
			else{
				$this->QueryAircon();
			}
		}

		public function SetPowerSwitch(bool $Value){
			if($Value < 0) {$Value = 0;}
			if($Value > 1) {$Value = 1;}
			if ($this->ReadPropertyBoolean('StatusEmulieren') == true)
			{
				$this->SetValue('dknPowerSwitch', $Value);
			}

			$data = $this->ReadValues();
			if($Value == 1 ){
				$data['pow'] = '1';
			}
			else{
				$data['pow'] = '0';
			}
			$this->WriteAircon($data);
		}

		public function SetHomeKitState(int $Value){

			if ($this->ReadPropertyBoolean('StatusEmulieren') == true)
			{
				$this->SetValue('dknHomeKitState', $Value);
			}

			if($Value == 0){
				$this->SetPowerSwitch(false);
			}
			else{
				$this->SetPowerSwitch(true);
				switch ($Value){
					case 1:
						$this->SetModeValue(4);
						break;
					case 2:
						$this->SetModeValue(3);
						break;
					case 3:
						$this->SetModeValue(1);
						break;
				}
			}

		}

		public function SetFanRateValue(int $Value){

			$id = @$this->GetIDForIdent('dknSetBooster');
			if ($id != null)
				if(GetValueBoolean($id) == 1) return;
				
			$id = $this->GetIDForIdent('dknSetModeValue');
			if(GetValueInteger($id) == 2) return;

			if ($this->ReadPropertyBoolean('StatusEmulieren') == true)
			{
				$this->SetValue('dknSetFanRateValue', $Value);
			}

			$data = $this->ReadValues();
			$data['f_rate'] = $this->aTranslatefRateWrite[$Value];
			//$this->LogMessage($data['f_rate'], KL_NOTIFY);
			$this->WriteAircon($data);
		}

		public function SetFanDirValue(int $Value){
			if ($this->ReadPropertyBoolean('StatusEmulieren') == true)
			{
				$this->SetValue('dknSetFanDirValue', $Value);
			}
			
			$data = $this->ReadValues();
			$data['f_dir'] = $Value;
			$this->WriteAircon($data);
		}

		public function SetTempValue(float $Value){
			
			$id = @$this->GetIDForIdent('dknSetBooster');
			if ($id != null)
				if(GetValueBoolean($id) == 1) return;

			$id = $this->GetIDForIdent('dknSetModeValue');
			switch (GetValueInteger($id)){
				case 1:
					if($Value < 18) {$Value = 18;}
					if($Value > 30) {$Value = 30;}
					break;
				case 2:
				case 6:
					return;
					break;
				case 3:
					if($Value < 18) {$Value = 18;}
					if($Value > 32) {$Value = 32;}
					break;					
				case 4:
					if($Value < 10) {$Value = 10;}
					if($Value > 30) {$Value = 30;}
					break;					
				}

			if ($this->ReadPropertyBoolean('StatusEmulieren') == true)
			{
				$this->SetValue('dknSetTempValue', $Value);
			}

			$data = $this->ReadValues();
			$data['stemp'] = str_replace(',', '.', "$Value");

			$this->WriteAircon($data);
		}

		public function SetHumidityValue(int $Value){
			if ($this->ReadPropertyBoolean('StatusEmulieren') == true)
			{
				$this->SetValue('dknSetHumidityValue', $Value);
			}
			
			$data = $this->ReadValues();
			$data['shum'] = $Value;
			$this->WriteAircon($data);
		}

		public function SetModeValue(int $Value){
			
			$data = $this->ReadValues();
			switch ($Value){
				case 1:
					$data['mode'] = $Value;
					$Wert = $this->ReadAttributeFloat('autoTemp');
					$data['stemp'] = strval(str_replace(',','.',"$Wert"));
					$data['f_rate'] = $this->ReadAttributeString('autoSpeed');
					$data['f_dir'] = $this->ReadAttributeInteger('autoDir');
					$data['shum'] = $this->ReadAttributeInteger('autoHum');
					break;
				case 2:
					$data['mode'] = $Value;
					$data['f_rate'] = $this->ReadAttributeString('dhumSpeed');
					$data['f_dir'] = $this->ReadAttributeInteger('dhumDir');
					$data['shum'] = $this->ReadAttributeInteger('dhumHum');
					break;
				case 3:
					$data['mode'] = $Value;
					$Wert = $this->ReadAttributeFloat('coolTemp');
					$data['stemp'] = strval(str_replace(',','.',"$Wert"));
					$data['f_rate'] = $this->ReadAttributeString('coolSpeed');
					$data['f_dir'] = $this->ReadAttributeInteger('coolDir');
					$data['shum'] = $this->ReadAttributeInteger('coolHum');
					break;
				case 4:
					$data['mode'] = $Value;
					$Wert = $this->ReadAttributeFloat('heatTemp');
					$data['stemp'] = strval(str_replace(',','.',"$Wert"));
					$data['f_rate'] = $this->ReadAttributeString('heatSpeed');
					$data['f_dir'] = $this->ReadAttributeInteger('heatDir');
					$data['shum'] = $this->ReadAttributeInteger('heatHum');
					break;
				case 6:
					$data['mode'] = $Value;
					$data['f_rate'] = $this->ReadAttributeString('fanSpeed');
					$data['f_dir'] = $this->ReadAttributeInteger('fanDir');
					break;
			}
			if ($this->ReadPropertyBoolean('StatusEmulieren') == true)
			{
				$this->SetValue('dknSetModeValue', $Value);
				$this->SetValue('dknSetTempValue', $data['stemp']);
				$this->SetValue('dknSetFanRateValue', $this->aTranslatefRateRead[$data['f_rate']]);
				$this->SetValue('dknSetFanDirValue', $data['f_dir']);
				if($this->ReadPropertyInteger("Sollfeuchte") == 1){
					$this->SetValue('dknSetHumidityValue', $data['shum']);
				}
			}
			$this->WriteAircon($data);
		}

		protected function CreateVariableProfiles() {
			$profileName = "Daikin.Temperature";
			if(!IPS_VariableProfileExists($profileName)) {
				IPS_CreateVariableProfile($profileName, 2);
			}
			IPS_SetVariableProfileText($profileName, "", " °C");
			IPS_SetVariableProfileDigits($profileName, 1);
			IPS_SetVariableProfileValues($profileName, 10, 32, 0.5);
			IPS_SetVariableProfileIcon($profileName,  "Temperature");
			
			
			$profileName = "Daikin.Mode";
			if(!IPS_VariableProfileExists($profileName)) {
				IPS_CreateVariableProfile($profileName, 1);
			}
			IPS_SetVariableProfileAssociation($profileName, 1, "Automatik", "Gear", 0x3366FF);
			IPS_SetVariableProfileAssociation($profileName, 2, "Entfeuchten", "Drops", 0x99CD00);
			IPS_SetVariableProfileAssociation($profileName, 3, "Kühlen", "Snowflake", 0x00FEFC);
			IPS_SetVariableProfileAssociation($profileName, 4, "Heizen", "Flame", 0xFE0000);
			IPS_SetVariableProfileAssociation($profileName, 6, "Lüften", "Shuffle", 0xFFFF9A);

			
			$profileName = "Daikin.FanDirection";
			if(!IPS_VariableProfileExists($profileName)) {
				IPS_CreateVariableProfile($profileName, 1);
			}
			IPS_SetVariableProfileAssociation($profileName, 0, "Aus", "", -1);
			IPS_SetVariableProfileAssociation($profileName, 1, "Vertikal", "", 0xFFFF01);
			IPS_SetVariableProfileAssociation($profileName, 2, "Horizontal", "", 0xFFCC00);
			IPS_SetVariableProfileAssociation($profileName, 3, "3D", "", 0xFE0000);


			$profileName = "Daikin.FanRate";
			if(!IPS_VariableProfileExists($profileName)) {
				IPS_CreateVariableProfile($profileName, 1);
			}
			IPS_SetVariableProfileAssociation($profileName, 0, "Auto", "", 0xFFFF01);
			IPS_SetVariableProfileAssociation($profileName, 1, "Silence", "", 0x00FF01);
			IPS_SetVariableProfileAssociation($profileName, 2, "Stufe 1", "", 0xFFCD9A);
			IPS_SetVariableProfileAssociation($profileName, 3, "Stufe 2", "", 0xFFCC00);
			IPS_SetVariableProfileAssociation($profileName, 4, "Stufe 3", "", 0xFF9900);
			IPS_SetVariableProfileAssociation($profileName, 5, "Stufe 4", "", 0xFF6600);
			IPS_SetVariableProfileAssociation($profileName, 6, "Stufe 5", "", 0x993302);
		
			$profileName = "Daikin.HomeKitState";
			if(!IPS_VariableProfileExists($profileName)) {
				IPS_CreateVariableProfile($profileName, 1);
			}
			IPS_SetVariableProfileAssociation($profileName, 0, "Aus", "", 0x00FF01);
			IPS_SetVariableProfileAssociation($profileName, 1, "Heizen", "Flame", 0xFE0000);
			IPS_SetVariableProfileAssociation($profileName, 2, "Kühlen", "Snowflake", 0x00FEFC);
			IPS_SetVariableProfileAssociation($profileName, 3, "Automatik", "Gear", 0x3366FF);
		}

		private function ReadValues(){
			$data = array();
			foreach ($this->Daikin_Properties as $Variable) {
				if($Variable['ident'] == 'dknSetHumidityValue' and $this->ReadPropertyInteger("Sollfeuchte") == 0){
					 $data += ['shum' => 0]; 
					 continue;
					} 
				if ($Variable['varHasAction'] == true) {
					
					$wert="";
					$id = @$this->GetIDForIdent($Variable['ident']);

					$wert = @strval(GetValue($id));

					if ($Variable['varType'] == 0){
						if($wert != "1") $wert = "0"; 
					}
					
					if ($Variable['ident'] == 'dknSetFanRateValue'){
						$wert = strval($this->aTranslatefRateWrite[intval($wert)]);
					}

					$wert = str_replace(",",".", $wert);
					$data += [$Variable['value'] => $wert];
				}
			}

			return $data;
		}

		private function WriteAircon($data){
			$ip = $this->ReadPropertyString('IPAddress');

			$options = array( 
				'http' => array( 
					'header'  => "Content-type: application/x-www-form-urlencoded				", 
					'method'  => 'GET',
					'timeout' => 2,
					'content' => http_build_query($data) 
				) 
			);				

			$content = http_build_query($data);
			$this->LogMessage("http://$ip/aircon/set_control_info?$content", KL_NOTIFY);

			$ip = $this->ReadPropertyString('IPAddress');
			$context = stream_context_create($options); 
			$result  = @file_get_contents("http://$ip/aircon/set_control_info?$content", false, $context); 
			$data = explode(",", $result);
			if($data[0] !== "ret=OK") {
				$this->LogMessage("Fehler beim Schreiben auf die Klimaanlage $ip: $result", KL_ERROR);
			}
			else{
				$this->QueryAircon();
			}
		}

		private function QueryAircon(){
			$context = stream_context_create(array(
				'http' => array(
					'timeout' => 2
					)
				)
			);

			$ip = $this->ReadPropertyString('IPAddress');

			//liest alle Seiten der Klimaanlage aus
			foreach($this->aQueryTypes as $sCurrent) {
				try {
					$result = @file_get_contents("http://$ip/$sCurrent", false, $context);

					if($result === false || substr($result, 4, 2) !== "OK") {
						if ($this->ReadPropertyInteger("ErrorConnectmessage") == 1) $this->LogMessage("Fehler bei Abfrage von Klimaanlage $ip", KL_WARNING);
						return false;
					}
				} catch (Exception $e) {
					$this->LogMessage("Fehler bei Abfrage von Klimaanlage $ip: ".$e->getMessage(), KL_ERROR );
					return false;
				}

				$sCurrent = substr($sCurrent, strpos($sCurrent, "/")+1);
				$aResult[$sCurrent] = $this->sortReturnIntoArray($result);
			}
		
			return $aResult;

		}

		private function SortReturnIntoArray($Data){
			$aTmp1 = explode(",", $Data);

			foreach($aTmp1 as $aCurrent) {
				$aTmp2 = explode("=", $aCurrent);
				$aResult[$aTmp2[0]] = $aTmp2[1];
			}
		
			return $aResult;			
		}

		private function SetInstanceStatus(): void{
			//IP Prüfen
			$ip = $this->ReadPropertyString('IPAddress');
			if ($ip === '') {
				$this->SetStatus(self::STATUS_INST_IP_IS_EMPTY);
				exit;
			}
			if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
				if (Sys_Ping ($ip, 1000)){
					$this->SetStatus(IS_ACTIVE);
				}
				else{
					$this->SetStatus(IS_INACTIVE);
				}
			} else {
				$this->SetStatus(self::STATUS_INST_IP_IS_INVALID); //IP Adresse ist ungültig
			}
		}		
	}
