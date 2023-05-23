<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/sonosAccess.php'; // SOAP Access to Sonos
require_once __DIR__ . '/../libs/VariableProfile.php';
require_once __DIR__ . '/../libs/CommonFunctions.php';

class SonosSplitter extends IPSModule
{
    use VariableProfile;
    use CommonFunctions;

    public function Create()
    {
        // Diese Zeile nicht löschen.

        $radioStationDefault = json_encode([
            ['name' => 'SWR3',             'URL' => 'x-rincon-mp3radio://swr-swr3-live.cast.addradio.de/swr/swr3/live/mp3/128/stream.mp3', 'imageURL' => 'http://cdn-radiotime-logos.tunein.com/s24896q.png'],
            ['name' => 'AC/DC Collection', 'URL' => 'x-rincon-mp3radio://streams.radiobob.de/bob-acdc/mp3-192/mediaplayer',                'imageURL' => 'http://cdn-radiotime-logos.tunein.com/s256712.png'],
            ['name' => 'FFN',              'URL' => 'x-rincon-mp3radio://player.ffn.de/ffn.mp3',                                           'imageURL' => 'http://cdn-radiotime-logos.tunein.com/s8954q.png']
        ]);

        parent::Create();
        $this->RegisterPropertyInteger('PlaylistImport', 0);
        $this->RegisterPropertyInteger('AlbumArtHeight', 170);
        $this->RegisterPropertyString('RadioStations', $radioStationDefault);
        $this->RegisterPropertyInteger('UpdateGroupingFrequency', 120);
        $this->RegisterPropertyInteger('UpdateStatusFrequency', 5);
        $this->RegisterTimer('Sonos Update Grouping', 0, 'SNS_updateGrouping(' . $this->InstanceID . ');');

        $this->RegisterAttributeInteger('LastPlaylistImport', -1);
    } // End Create

    public function ApplyChanges()
    {
        $this->RegisterMessage(0, IPS_KERNELSTARTED);
        // Diese Zeile nicht löschen
        parent::ApplyChanges();

        // create profiles
        $this->RegisterProfileIntegerEx('SONOS.PlayMode', 'Information', '', '', [
            [0, $this->Translate('Normal'),             '', -1],
            [1, $this->Translate('Repeat all'),         '', -1],
            [2, $this->Translate('Repeat one'),         '', -1],
            [3, $this->Translate('Shuffle no repeat'),  '', -1],
            [4, $this->Translate('Shuffle'),            '', -1],
            [5, $this->Translate('Shuffle repeat one'), '', -1]
        ]);
        $this->RegisterProfileIntegerEx('SONOS.PowerSource', 'EnergyStorage', '', '', [
            [0, $this->Translate('unknown'),            '', -1],
            [1, $this->Translate('Battery'),            '', -1],
            [2, $this->Translate('Charging Ring'),      '', -1],
            [3, $this->Translate('USB Power'),          '', -1]
        ]);
        $this->RegisterProfileInteger('SONOS.Volume', 'Intensity', '', ' %', 0, 100, 1);
        $this->RegisterProfileInteger('SONOS.Tone', 'Intensity', '', ' %', -10, 10, 1);
        $this->RegisterProfileInteger('SONOS.Balance', 'Intensity', '', ' %', -100, 100, 1);

        $this->RegisterProfileBool('SONOS.Switch', 'Information', '', '', [
            [false, $this->Translate('Off'), '', 0xFF0000],
            [true, $this->Translate('On'),  '', 0x00FF00]
        ]);

        // to be deleted, once everyone got the change
        if (IPS_VariableProfileExists('SONOS.Status')) {
            IPS_DeleteVariableProfile('SONOS.Status');
        }

//        if (IPS_VariableProfileExists('SONOS.Volume')) {
//            IPS_DeleteVariableProfile('SONOS.Volume');
//        }

        // Radio Stations
        $radioStations = json_decode($this->ReadPropertyString('RadioStations'), true);
        $Associations = [];
        $Value = 1;

        foreach ($radioStations as $radioStation) {
            $Associations[] = [$Value++, $radioStation['name'], '', -1];
            // associations only support up to 128 variables
            if ($Value === 129) {
                break;
            }
        }

        if (IPS_VariableProfileExists('SONOS.Radio')) {
            IPS_DeleteVariableProfile('SONOS.Radio');
        }
        $this->RegisterProfileIntegerEx('SONOS.Radio', 'Speaker', '', '', $Associations);

        if ($this->ReadAttributeInteger('LastPlaylistImport') != $this->ReadPropertyInteger('PlaylistImport')) {
            $this->UpdatePlaylists();
            $this->WriteAttributeInteger('LastPlaylistImport', $this->ReadPropertyInteger('PlaylistImport'));
        }

        if (!IPS_VariableProfileExists('SONOS.Groups')) {
            $this->RegisterProfileIntegerEx('SONOS.Groups', 'Network', '', '', [[0, $this->Translate('none'), '', -1]]);
        }

        $this->SetTimerInterval('Sonos Update Grouping', $this->ReadPropertyInteger('UpdateGroupingFrequency') * 1000);

        // Send different propeties to player instances, in case IPS is already started
        if (IPS_GetKernelRunlevel() == KR_READY) {
            $this->SendDataToChildren(json_encode([
                'DataID'         => '{36EA4430-7047-C11D-0854-43391B14E0D7}',
                'type'           => 'updateStatus',
                'targetInstance' => null,
                'data'           => $this->ReadPropertyInteger('UpdateStatusFrequency')
            ]));
            $this->SendDataToChildren(json_encode([
                'DataID'         => '{36EA4430-7047-C11D-0854-43391B14E0D7}',
                'type'           => 'RadioStations',
                'targetInstance' => null,
                'data'           => $this->ReadPropertyString('RadioStations')
            ]));
            $this->SendDataToChildren(json_encode([
                'DataID'         => '{36EA4430-7047-C11D-0854-43391B14E0D7}',
                'type'           => 'AlbumArtHight',
                'targetInstance' => null,
                'data'           => $this->ReadPropertyInteger('AlbumArtHeight')
            ]));
        }
    } // End ApplyChanges

    public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    {
        switch ($Message) {
          case IPS_KERNELSTARTED:
            // Set Timer for update Status in all Player instances
            $this->SendDataToChildren(json_encode([
                'DataID'         => '{36EA4430-7047-C11D-0854-43391B14E0D7}',
                'type'           => 'updateStatus',
                'targetInstance' => null,
                'data'           => $this->ReadPropertyInteger('UpdateStatusFrequency')
            ]));
            $this->SendDataToChildren(json_encode([
                'DataID'         => '{36EA4430-7047-C11D-0854-43391B14E0D7}',
                'type'           => 'RadioStations',
                'targetInstance' => null,
                'data'           => $this->ReadPropertyString('RadioStations')
            ]));
            $this->SendDataToChildren(json_encode([
                'DataID'         => '{36EA4430-7047-C11D-0854-43391B14E0D7}',
                'type'           => 'AlbumArtHight',
                'targetInstance' => null,
                'data'           => $this->ReadPropertyInteger('AlbumArtHeight')
            ]));
            $this->SendDataToChildren(json_encode([
                'DataID'         => '{36EA4430-7047-C11D-0854-43391B14E0D7}',
                'type'           => 'checkPlaylistAction',
                'targetInstance' => null,
                'data'           => ''
            ]));
            break;
    }
    }

    public function ForwardData($JSONString)
    {
        $input = json_decode($JSONString, true);

        if ($input['type'] != 'getVariableNoDebug' && $input['type'] != 'getCoordinatorValues' && $input['type'] != 'getProperties') {
            $this->SendDebug('"' . __FUNCTION__ . '" called with', sprintf('$JSONstring=%s', $JSONString), 0);
        }

        switch ($input['type']) {
            case 'AlbumArtRequest':
              $data = json_encode([
                  'DataID'         => '{36EA4430-7047-C11D-0854-43391B14E0D7}',
                  'type'           => 'AlbumArtHight',
                  'targetInstance' => $input['targetInstance'],
                  'data'           => $this->ReadPropertyInteger('AlbumArtHeight')
              ]);
              $this->SendDebug(__FUNCTION__ . '->SendDataToChildren', $data, 0);
              $this->SendDataToChildren($data);
              break;
            case 'UpdateStatusFrequencyRequest':
              $data = json_encode([
                  'DataID'         => '{36EA4430-7047-C11D-0854-43391B14E0D7}',
                  'type'           => 'updateStatus',
                  'targetInstance' => $input['targetInstance'],
                  'data'           => $this->ReadPropertyInteger('UpdateStatusFrequency')
              ]);
              $this->SendDebug(__FUNCTION__ . '->SendDataToChildren', $data, 0);
              $this->SendDataToChildren($data);
              break;
            case 'RadioStationsRequest':
              $data = json_encode([
                  'DataID'         => '{36EA4430-7047-C11D-0854-43391B14E0D7}',
                  'type'           => 'RadioStations',
                  'targetInstance' => $input['targetInstance'],
                  'data'           => $this->ReadPropertyString('RadioStations')
              ]);
              $this->SendDebug(__FUNCTION__ . '->SendDataToChildren', $data, 0);
              $this->SendDataToChildren($data);
              break;
            case 'becomeNewGroupCoordinator':
            case 'prepareAllPlayGrouping':
            case 'preparePlayGrouping':
            case 'preResetPlayGrouping':
            case 'resetPlayGrouping':
            case 'callFunction':
            case 'addMember':
            case 'removeMember':
            case 'setAttribute':
            case 'setVariable':
              $input['DataID'] = '{36EA4430-7047-C11D-0854-43391B14E0D7}'; // rewrite DataID
              $data = json_encode($input);                                 // just forward
              $this->SendDebug(__FUNCTION__ . '->SendDataToChildren', $data, 0);
              $this->SendDataToChildren($data);
              break;
            case 'getName':
            case 'getVariable':
              $input['DataID'] = '{36EA4430-7047-C11D-0854-43391B14E0D7}'; // rewrite DataID
              $data = json_encode($input);                                 // just forward
              $this->SendDebug(__FUNCTION__ . '->SendDataToChildren', $data, 0);
              $return = $this->SendDataToChildren($data);
              $this->SendDebug(__FUNCTION__ . '->received from children', json_encode($return), 0);
              return json_encode($return);
              break;
            case 'getVariableNoDebug':
            case 'getCoordinatorValues':
            case 'getProperties':
                // no debug
                $input['DataID'] = '{36EA4430-7047-C11D-0854-43391B14E0D7}'; // rewrite DataID
                $data = json_encode($input);                                 // just forward
                $return = $this->SendDataToChildren($data);
                return json_encode($return);
                break;
            default:
              throw new Exception(sprintf($this->Translate('unknown type %s in ForwardData'), $input['type']));
        }
    }

    public function GetConfigurationForm()
    {
        $Form = [
            'elements' => [
                [
                    'name'     => 'AlbumArtHeight',
                    'type'     => 'NumberSpinner',
                    'caption'  => 'Album Art Hight in WebFront',
                    'suffix'   => 'px'
                ],
                [
                    'name'     => 'UpdateGroupingFrequency',
                    'type'     => 'NumberSpinner',
                    'caption'  => 'Update Grouping Frequency',
                    'suffix'   => 's'
                ],
                [
                    'name'     => 'UpdateStatusFrequency',
                    'type'     => 'NumberSpinner',
                    'caption'  => 'Update Status Frequency',
                    'suffix'   => 's'
                ],
                [
                    'type' => 'RowLayout', 'items' => [
                        [
                            'name'     => 'PlaylistImport',
                            'type'     => 'Select',
                            'caption'  => 'Import Playlists',
                            'options'  => [
                                ['caption' => 'none',                        'value' => 0],
                                ['caption' => 'saved',                       'value' => 1],
                                ['caption' => 'imported',                    'value' => 2],
                                ['caption' => 'saved & imported',            'value' => 3],
                                ['caption' => 'favorites',                   'value' => 4],
                                ['caption' => 'saved & favorites',           'value' => 5],
                                ['caption' => 'imported & favorites',        'value' => 6],
                                ['caption' => 'saved, imported & favorites', 'value' => 7]
                            ]
                        ],
                        [
                            'name'     => 'updatePlaylist',
                            'type'     => 'Button',
                            'caption'  => 'update playlists',
                            'onClick'  => 'SNS_UpdatePlaylists($id);'
                        ]
                    ]
                ],
                [
                    'name'     => 'RadioStations',
                    'type'     => 'List',
                    'caption'  => 'Radio Stations',
                    'rowCount' => 6,
                    'add'      => true,
                    'delete'   => true,
                    'columns'  => [
                        [
                            'caption' => 'Name',
                            'name'    => 'name',
                            'width'   => '150px',
                            'edit'    => ['type' => 'ValidationTextBox'],
                            'add'     => '',
                            'save'    => true
                        ],
                        [
                            'caption' => 'URL',
                            'name'    => 'URL',
                            'width'   => '450px',
                            'edit'    => ['type' => 'ValidationTextBox'],
                            'add'     => '',
                            'save'    => true
                        ],
                        [
                            'caption' => 'Image URL',
                            'name'    => 'imageURL',
                            'width'   => '450px',
                            'edit'    => ['type' => 'ValidationTextBox'],
                            'add'     => '',
                            'save'    => true
                        ]
                    ]
                ]
            ],
            'actions' => [
                [
                    'type' => 'RowLayout', 'items' => [
                        [
                            'name'     => 'readTunein',
                            'type'     => 'Button',
                            'caption'  => 'read TuneIn favorites',
                            'onClick'  => 'SNS_ReadTunein($id);'
                        ],
                        [
                            'name'     => 'readTuneinLable',
                            'type'     => 'Label',
                            'caption'  => 'Read parameters from "My Radio Stations" in TuneIn and add them to "Radio Stations" above'
                        ]
                    ]
                ],
                [
                    'type' => 'RowLayout', 'items' => [
                        [
                            'name'     => 'updateGrouping',
                            'type'     => 'Button',
                            'caption'  => 'update grouping',
                            'onClick'  => 'SNS_updateGrouping($id);'
                        ],
                        [
                            'name'     => 'updateGroupingLabel',
                            'type'     => 'Label',
                            'caption'  => 'read grouping from sonos (normally executed automatically)'
                        ]
                    ]
                ]
            ]
        ];
        return json_encode($Form);
    } // End GetConfigurationForm

    public function StopAll()
    {
        $this->SendDebug('"' . __FUNCTION__ . '" called', '', 0);
        $data = json_encode([
            'DataID'         => '{36EA4430-7047-C11D-0854-43391B14E0D7}',
            'type'           => 'callFunction',
            'targetInstance' => null,
            'function'       => 'Stop'
        ]);
        $this->SendDebug(__FUNCTION__ . '->SendDataToChildren', $data, 0);
        $this->SendDataToChildren($data);
    } // End StopAll

    public function PauseAll()
    {
        $this->SendDebug('"' . __FUNCTION__ . '" called', '', 0);
        $data = json_encode([
            'DataID'         => '{36EA4430-7047-C11D-0854-43391B14E0D7}',
            'type'           => 'callFunction',
            'targetInstance' => null,
            'function'       => 'Pause'
        ]);
        $this->SendDebug(__FUNCTION__ . '->SendDataToChildren', $data, 0);
        $this->SendDataToChildren($data);
    } // End StopAll

    public function updateGrouping()
    {
        $instancePropertyList = $this->getInstanceList();
        $sonos = $this->getSonos($instancePropertyList, false);
        // get all Player instances, including required data

        if (isset($sonos)) {
            $SonosGrouping = new SimpleXMLElement($sonos->GetZoneGroupState());
        }

        if (!isset($SonosGrouping) || !$SonosGrouping) {
            return;
        }

        $InstanceList = [];
        foreach ($instancePropertyList as $instancePropertyJson) {
            $instanceProperty = json_decode($instancePropertyJson, true);
            $InstanceList[$instanceProperty['RINCON']] = [
                'IPAddress'     => $instanceProperty['IPAddress'],
                'TimeOut'       => $instanceProperty['TimeOut'],
                'InstanceID'    => $instanceProperty['instanceID']
            ];
        }

        $Grouping = [];
        foreach ($SonosGrouping->ZoneGroups->ZoneGroup as $zoneGroup) {
            //get RINCON of Coordinator
            $CoordinatorRINCON = (string) $zoneGroup->attributes()['Coordinator'];
            // check if Coordinator is configured in Symcon
            if (isset($InstanceList[$CoordinatorRINCON])) {
                $CoordinatorInstanceID = $InstanceList[$CoordinatorRINCON]['InstanceID'];
            } else {
                $CoordinatorInstanceID = 0; // 0 means is not configured
            }
            $GroupMember = [];
            foreach ($zoneGroup->ZoneGroupMember as $zoneGroupMember) {
                // Instances marked as "Invisible" are things like Stereo Pairs or Bridge
                if (isset($zoneGroupMember->attributes()['Invisible'])) {
                    continue;
                }
                // Get RINCON of Member
                $RINCON = (string) $zoneGroupMember->attributes()['UUID'];
                // Instance is not configured in Symcon --> ignore
                if (!isset($InstanceList[$RINCON])) {
                    continue;
                }
                // Coordinator is also part of group --> do not add as own entry
                if ($RINCON != $CoordinatorRINCON) {
                    $Grouping[$InstanceList[$RINCON]['InstanceID']] = [
                        'isCoordinator' => false,
                        'vanished'      => false,
                        'GroupMember'   => [],
                        'Coordinator'   => $CoordinatorInstanceID
                    ];
                    $GroupMember[] = $InstanceList[$RINCON]['InstanceID'];
                }
            }

            // --> Instance not in Symcon
            if ($CoordinatorInstanceID == 0) {
                // But members are, worth an exception
                if (count($GroupMember) != 0) {
                    throw new Exception(sprintf($this->Translate('Coordinator (%s) is not configured as Symcon instance. This cannot work.'), $CoordinatorRINCON));
                } else {   // also the members are not in Symcon, can be ignored
                    continue;
                }
            }

            // Add Coordinator including all members
            $Grouping[$CoordinatorInstanceID] = [
                'isCoordinator' => true,
                'vanished'      => false,
                'GroupMember'   => $GroupMember,
                'Coordinator'   => 0
            ];
        }

        if (isset($SonosGrouping->VanishedDevices->Device)) {
            foreach ($SonosGrouping->VanishedDevices->Device as $device) {
                $Grouping[strval($device->attributes()['UUID'])] = [
                    'isCoordinator' => false,
                    'vanished'      => true,
                    'GroupMember'   => [],
                    'Coordinator'   => 0
                ];
            }
        }

        foreach ($Grouping as $InstanceID => $Group) {
            $this->SendDataToChildren(json_encode([
                'DataID'         => '{36EA4430-7047-C11D-0854-43391B14E0D7}',
                'type'           => 'grouping',
                'targetInstance' => $InstanceID,
                'data'           => $Group
            ]));
        }
    } // End update Grouping

    public function ReadTunein()
    {
        $this->SendDebug('"' . __FUNCTION__ . '" called', '', 0);

        $sonos = $this->getSonos($this->getInstanceList());

        if (!isset($sonos)) {
            throw new Exception($this->Translate('Unable to access any Sonos Instance'));
        }

        $answer = $sonos->BrowseContentDirectory('R:0/0');
        if (isset($answer['Result'])) {
            $tuneinStations = new SimpleXMLElement($answer['Result']);

            $radioStations = json_decode($this->ReadPropertyString('RadioStations'), true);

            $this->SendDebug(__FUNCTION__ . ': following stations were returned', '', 0);

            foreach ($tuneinStations as $tuneinStation) {
                $name = strval($tuneinStation->xpath('dc:title')[0]);
                $this->SendDebug(__FUNCTION__ . ': Name', (string) $name, 0);
                $url = strval($tuneinStation->res);
                $this->SendDebug(__FUNCTION__ . ': URL', (string) $url, 0);
                if (preg_match('/s\d{3,}/', $url, $station)) {
                    $imageurl = 'http://cdn-radiotime-logos.tunein.com/' . $station[0] . 'q.png';
                    $this->SendDebug(__FUNCTION__ . ': Image', (string) $imageurl, 0);
                }
                $alreadyIn = false;
                foreach ($radioStations as $radioStation) {
                    if ($radioStation['name'] == $name && $radioStation['URL'] == $url) {
                        $alreadyIn = true;
                        break;
                    }
                }
                if ($alreadyIn == false) {
                    $radioStations[] = [
                        'name'     => $name,
                        'URL'      => $url,
                        'imageURL' => $imageurl
                    ];
                }
            }

            $this->UpdateFormField('RadioStations', 'values', json_encode($radioStations));
        }
    }

    public function UpdatePlaylists()
    {
        $this->SendDebug('"' . __FUNCTION__ . '" called', '', 0);
        $Associations = [];
        $PlaylistImport = $this->ReadPropertyInteger('PlaylistImport');
        $this->SendDebug(__FUNCTION__ . ': PlaylistImport set to', (string) $PlaylistImport, 0);
        $Value = 1;

        if ($PlaylistImport != 0) {
            $sonos = $this->getSonos($this->getInstanceList());

            if (!isset($sonos)) {
                throw new Exception($this->Translate('Unable to access any Sonos Instance'));
            }

            // saved
            if ($PlaylistImport === 1 || $PlaylistImport === 3 || $PlaylistImport === 5 || $PlaylistImport === 7) {
                $this->SendDebug(__FUNCTION__ . '->sonos', 'BrowseContentDirectory(\'SQ:\')', 0);
                foreach ((new SimpleXMLElement($sonos->BrowseContentDirectory('SQ:')['Result']))->container as $container) {
                    $this->SendDebug(__FUNCTION__ . ': Found PlayList', (string) $container->xpath('dc:title')[0], 0);
                    $Associations[] = [$Value++, (string) $container->xpath('dc:title')[0], '', -1];
                    // associations only support up to 128 variables
                    if ($Value === 129) {
                        break;
                    }
                }
            }

            // imported
            if (($PlaylistImport === 2 || $PlaylistImport === 3 || $PlaylistImport === 6 || $PlaylistImport === 7) && $Value < 33) {
                $this->SendDebug(__FUNCTION__ . '->sonos', 'BrowseContentDirectory(\'A:PLAYLISTS\')', 0);
                foreach ((new SimpleXMLElement($sonos->BrowseContentDirectory('A:PLAYLISTS')['Result']))->container as $container) {
                    $this->SendDebug(__FUNCTION__ . ': Found PlayList', (string) $container->xpath('dc:title')[0], 0);
                    $Associations[] = [$Value++, (string) preg_replace($this->getPlaylistReplacementFrom(), $this->getPlaylistReplacementTo(), $container->xpath('dc:title')[0]), '', -1];
                    // associations only support up to 128 variables
                    if ($Value === 129) {
                        break;
                    }
                }
            }

            // favorites
            if (($PlaylistImport === 4 || $PlaylistImport === 5 || $PlaylistImport === 6 || $PlaylistImport === 7) && $Value < 33) { // Spotify Playlist saved as Sonos Favorite
                $this->SendDebug(__FUNCTION__ . '->sonos', 'BrowseContentDirectory(\'FV:2\')', 0);
                foreach ((new SimpleXMLElement($sonos->BrowseContentDirectory('FV:2')['Result']))->item as $item) {
                    $this->SendDebug(__FUNCTION__ . ': Found PlayList', (string) $item->xpath('dc:title')[0], 0);
                    $Associations[] = [$Value++, (string) preg_replace($this->getPlaylistReplacementFrom(), $this->getPlaylistReplacementTo(), $item->xpath('dc:title')[0]), '', -1];
                    // associations only support up to 128 variables
                    if ($Value === 129) {
                        break;
                    }
                }
            }
        }

        if ($Value === 1) {
            $this->SendDebug(__FUNCTION__ . ': no PlayList found', '', 0);
            $Associations[] = [0, $this->Translate('no playlist available'), '', -1];
        }

        if (IPS_VariableProfileExists('SONOS.Playlist')) {
            IPS_DeleteVariableProfile('SONOS.Playlist');
        }

        $this->RegisterProfileIntegerEx('SONOS.Playlist', 'Database', '', '', $Associations);

        if (IPS_GetKernelRunlevel() == KR_READY) {
            $data = json_encode([
                'DataID'         => '{36EA4430-7047-C11D-0854-43391B14E0D7}',
                'type'           => 'checkPlaylistAction',
                'targetInstance' => null,
                'data'           => ''
            ]);
            $this->SendDebug(__FUNCTION__ . '->SendDataToChildren', $data, 0);
            $this->SendDataToChildren($data);
        }
    } // End UpdatePlaylists

    private function getInstanceList(): array
    {
        return $this->SendDataToChildren(json_encode([
            'DataID'         => '{36EA4430-7047-C11D-0854-43391B14E0D7}',
            'type'           => 'getProperties',
            'targetInstance' => null
        ]));
    }

    private function getSonos(array $instanceList, bool $writeDebug = true)
    {
        // find a working instance
        foreach ($instanceList as $InstanceJson) {
            $Instance = json_decode($InstanceJson, true);
            if (Sys_Ping($Instance['IPAddress'], $Instance['TimeOut']) == true) {
                $sonos = new SonosAccess($Instance['IPAddress']);
                if ($writeDebug) {
                    $this->SendDebug(__FUNCTION__ . ': using Player', $Instance['IPAddress'], 0);
                }
                return $sonos;
            }
        }
    }
}
