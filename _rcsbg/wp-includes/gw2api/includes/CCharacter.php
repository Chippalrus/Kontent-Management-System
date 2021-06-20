<?php
require_once( __DIR__ . '/CGW2API.php' );
//=========================================================================================
//	CCharacter by Chippalrus
//=========================================================================================
class CCharacter extends CGW2API
{
//=========================================================================================
//	Members
//=========================================================================================
	protected	$m_CharacterData		=	null;
	protected	$m_Equipments			=	null;
	protected	$m_Specializations		=	null;
	protected	$m_CharacterAttri		=	null;
//=========================================================================================
//	Constructor / Destructor
//=========================================================================================
	public	function	__construct		()
	{
		parent::__construct( 20 );
		$this->m_CharacterData		=	Array();
		$this->m_Equipments			=	Array();
			$this->m_Equipments[ EItemType::EQUIPMENT ]	=	Array();
			$this->m_Equipments[ EItemType::UPGRADES ]	=	Array();
			$this->m_Equipments[ EItemType::INFUSIONS ]	=	Array();

		$this->m_Specializations	=	Array();
			$this->m_Specializations[ ESpecialization::A ]	=	Array();
			$this->m_Specializations[ ESpecialization::B ]	=	Array();
			$this->m_Specializations[ ESpecialization::C ]	=	Array();

		$this->m_CharacterAttri		=	Array();
			$this->m_CharacterAttri[ 'wvw' ] = Array();
			$this->m_CharacterAttri[ 'pve' ] = Array();
			$this->m_CharacterAttri[ 'pvp' ] = Array();

		for( $i = 0; $i < 2; $i++ )
		{
			$this->m_CharacterAttri[ 'wvw' ][ $i ] = Array();
			$this->m_CharacterAttri[ 'pve' ][ $i ] = Array();
			$this->m_CharacterAttri[ 'pvp' ][ $i ] = Array();
		}
	}

	public	function	__destruct(){	parent::__destruct();	}
//=========================================================================================
//	Get
//=========================================================================================
	public	function	GetName					(){		return $this->m_CharacterData->{	'name'				};	}
	public	function	GetRace					(){		return $this->m_CharacterData->{	'race' 				};	}
	public	function	GetGender				(){		return $this->m_CharacterData->{	'gender' 			};	}
	public	function	GetProfession			(){		return $this->m_CharacterData->{	'profession' 		};	}
	public	function	GetLevel				(){		return $this->m_CharacterData->{	'level' 			};	}
	public	function	GetAge					(){		return $this->m_CharacterData->{	'age' 				};	}
	public	function	GetDeaths				(){		return $this->m_CharacterData->{	'deaths' 			};	}
	public	function	GetEquipments			()						{	return $this->m_Equipments;		}
	public	function	GetEquipment			( $eType, $eEquipment )	{	return $this->m_Equipments[ $eType ][ $eEquipment ];	}
	public	function	GetSpecialization		( $eSpecialization )	{	return $this->m_Specializations[ $eSpecialization ];	}
	public	function	GetTraits				( $sMode, $Spec )		{	return $this->m_CharacterData->{ 'specializations' }->{ $sMode }[ $Spec ]; }
	public	function	GetAttribute			( $eAttribute, $sMode, $set ){	return	$this->m_CharacterAttri[ $sMode ][ $set ][ $eAttribute ];	}
	public	function	GetSlotIndex( $sSlot )
	{
		$tehSlot = 99;
		switch( $sSlot )
		{
			case 'WeaponA1':	$tehSlot = EEquipment::A1;	break;
			case 'WeaponA2':	$tehSlot = EEquipment::A2;	break;
			case 'WeaponB1':	$tehSlot = EEquipment::B1;	break;
			case 'WeaponB2':	$tehSlot = EEquipment::B2;	break;
			case 'Helm':		$tehSlot = EEquipment::HELM;		break;
			case 'Shoulders':	$tehSlot = EEquipment::SHOULDERS;	break;
			case 'Coat':		$tehSlot = EEquipment::COAT;		break;
			case 'Gloves':		$tehSlot = EEquipment::GLOVES;		break;
			case 'Leggings':	$tehSlot = EEquipment::LEGGINGS;	break;
			case 'Boots':		$tehSlot = EEquipment::BOOTS;		break;
			case 'Backpack':	$tehSlot = EEquipment::BACKPACK;	break;
			case 'Amulet':		$tehSlot = EEquipment::AMULET;		break;
			case 'Ring1':		$tehSlot = EEquipment::RING1;		break;
			case 'Ring2':		$tehSlot = EEquipment::RING2;		break;
			case 'Accessory1':	$tehSlot = EEquipment::ACCESSORY1;	break;
			case 'Accessory2':	$tehSlot = EEquipment::ACCESSORY2;	break;

			case 'HelmAquatic':		$tehSlot = EEquipment::HELMAQUATIC;	break;
			case 'WeaponAquaticA':	$tehSlot = EEquipment::AQUATIC_A;	break;
			case 'WeaponAquaticB':	$tehSlot = EEquipment::AQUATIC_B;	break;

			case 'Pick':	$tehSlot = EEquipment::PICK;	break;
			case 'Axe':		$tehSlot = EEquipment::AXE;		break;
			case 'Sickle':	$tehSlot = EEquipment::SICKLE;	break;
		}
		return $tehSlot;
	}
//=========================================================================================
//	Set
//=========================================================================================
	public	function	SetEquipmentData()
	{
		//	Get Equipment/Upgrades/Infusions Data
		$aEquipmentID = Array();
		$aEquipmentID[ EItemType::EQUIPMENT ] = Array();
		$aEquipmentID[ EItemType::UPGRADES ] = Array();
		$aEquipmentID[ EItemType::INFUSIONS ] = Array();
		// Grab IDs and omit duplicates from request
		$iLength = count( $this->m_CharacterData->{ 'equipment' } );
		for( $i = 0; $i < $iLength; $i++ )
		{
			// Equipment
			$bMatchedID = false;
			// Get the ID of the Item
			$tempID = $this->m_CharacterData->{ 'equipment' }[ $i ]->{ 'id' };
			// Loop through temp array
			for( $j = 0; $j < count( $aEquipmentID ); $j++ )
			{
				// Check for duplicate ID
				if( !is_null( $tempID ) && $tempID != $aEquipmentID[ EItemType::EQUIPMENT ][ $j ] )
				{
					$bMatchedID = false;
				}
				else
				{
					// If there is a duplicate break out of the loop
					$bMatchedID = true;
					break;
				}
			}
			// If no duplicate ID then add the ID into it.
			if( !$bMatchedID )	{	array_push( $aEquipmentID[ EItemType::EQUIPMENT ], $tempID );	}

			// Upgrades
			// Get the IDs of the upgrade
			$tempID = $this->m_CharacterData->{ 'equipment' }[ $i ]->{ 'upgrades' };
			// Loop through number of Upgrades
			for( $k = 0; $k < count( $tempID ); $k++ )
			{
				$bMatchedID = false;
				// Loop through temp array
				for( $j = 0; $j < count( $aEquipmentID ); $j++ )
				{
					// Check if there is a duplicate Upgrade ID
					if( !is_null( $tempID[ $k ] ) && $tempID[ $k ] != $aEquipmentID[ EItemType::UPGRADES ][ $j ] )
					{
						$bMatchedID = false;
					}
					else
					{
						// If there is a duplicate break out of the loop
						$bMatchedID = true;
						break;
					}
				}
				// If there is no duplicate Upgrade ID add ID to it.
				if( !$bMatchedID )	{	array_push( $aEquipmentID[ EItemType::UPGRADES ], $tempID[ $k ] );	}
			}
			
			// Infusions
			// Get the IDs of the Infusions
			$tempID = $this->m_CharacterData->{ 'equipment' }[ $i ]->{ 'infusions' };

			// Loop through number of Infusions
			for( $k = 0; $k < count( $tempID ); $k++ )
			{
				$bMatchedID = false;
				// Loop through temp array
				for( $j = 0; $j < count( $aEquipmentID ); $j++ )
				{
					// Check if there is a duplicate Infusion ID
					if( !is_null( $tempID[ $k ] ) && $tempID[ $k ] != $aEquipmentID[ EItemType::INFUSIONS ][ $j ] )
					{
						$bMatchedID = false;
					}
					else
					{
						// If there is a duplicate break out of the loop
						$bMatchedID = true;
						break;
					}
				}

				// If there is no duplicate Infusion ID add ID to it.
				if( !$bMatchedID )	{	array_push( $aEquipmentID[ EItemType::INFUSIONS ], $tempID[ $k ] );	}
			}
			// Stats
		}

		//	Request from API
		$aEquipmentID[ EItemType::EQUIPMENT ] = $this->GetContentBatch( $aEquipmentID[ EItemType::EQUIPMENT ], EURI::ITEMS, '-90 days' );
		$aEquipmentID[ EItemType::UPGRADES ] = $this->GetContentBatch( $aEquipmentID[ EItemType::UPGRADES ], EURI::ITEMS, '-90 days' );
		$aEquipmentID[ EItemType::INFUSIONS ] = $this->GetContentBatch( $aEquipmentID[ EItemType::INFUSIONS ], EURI::ITEMS, '-90 days' );

		//	Organize into Array
		$mEquipments = Array();	

		// Equipment
		$mEquipments[ EItemType::EQUIPMENT ]	= &$this->m_Equipments[ EItemType::EQUIPMENT ];	
		$iLength = count( $this->m_CharacterData->{ 'equipment' } );
		for( $i = 0; $i < count( $aEquipmentID[ EItemType::EQUIPMENT ] ); $i++ )
		{
			// Decode the json files
			$aEquipmentID[ EItemType::EQUIPMENT ][ $i ] = json_decode( $aEquipmentID[ EItemType::EQUIPMENT ][ $i ] );
			
			// Loop through Character data "Equipments"
			for( $j = 0; $j < $iLength; $j++ )
			{
				// Check for idendical item ID from decoded files
				$tempEquipment = $this->m_CharacterData->{ 'equipment' }[ $j ];
				if( $aEquipmentID[ EItemType::EQUIPMENT ][ $i ]->{ 'id' } == $tempEquipment->{ 'id' } )
				{
					// Store into its slot
					$iSlot = $this->GetSlotIndex( $tempEquipment->{ 'slot' } );
					$mEquipments[ EItemType::EQUIPMENT ][ $iSlot ] = $aEquipmentID[ EItemType::EQUIPMENT ][ $i ];

					//Selected Stat Attributes - to avoid calling API to grab more files since the info is given to us already
					if( !isset( $mEquipments[ EItemType::EQUIPMENT ][ $iSlot ]->{ 'details' }->{ 'infix_upgrade' }->{ 'attributes' } )
						&& isset( $mEquipments[ EItemType::EQUIPMENT ][ $iSlot ]->{ 'details' }->{ 'stat_choices' } ) )
					{
						// Check if 'infix_upgrade' exists, if not add it
						if( !isset( $mEquipments[ EItemType::EQUIPMENT ][ $iSlot ]->{ 'details' }->{ 'infix_upgrade' } ) )
						{
							$assoArr = json_decode( json_encode( $mEquipments[ EItemType::EQUIPMENT ][ $iSlot ]->{ 'details' } ), true );
							$assoArr[ 'infix_upgrade' ] = array( 'attributes' => array() );
						}

						// Check and Set Selected Stats
						$sAttr = 'None';
						$sModifier = 0;
						if( isset( $tempEquipment->{ 'stats' }->{ 'attributes' }->{ 'Power' } ) )
						{
							$sAttr = 'Power';
							$sModifier = $tempEquipment->{ 'stats' }->{ 'attributes' }->{ 'Power' };
							// Add selected stats to the 'attributes' in the item file
							array_push
							(
								$assoArr[ 'infix_upgrade' ][ 'attributes' ],
								array( 'attribute' => $sAttr, 'modifier' => $sModifier )
							);
						}
						
						if( isset( $tempEquipment->{ 'stats' }->{ 'attributes' }->{ 'Toughness' } ) )
						{
							$sAttr = 'Toughness';
							$sModifier = $tempEquipment->{ 'stats' }->{ 'attributes' }->{ 'Toughness' };
							// Add selected stats to the 'attributes' in the item file
							array_push
							(
								$assoArr[ 'infix_upgrade' ][ 'attributes' ],
								array( 'attribute' => $sAttr, 'modifier' => $sModifier )
							);
						}

						if( isset( $tempEquipment->{ 'stats' }->{ 'attributes' }->{ 'Vitality' } ) )
						{
							$sAttr = 'Vitality';
							$sModifier = $tempEquipment->{ 'stats' }->{ 'attributes' }->{ 'Vitality' };
							// Add selected stats to the 'attributes' in the item file
							array_push
							(
								$assoArr[ 'infix_upgrade' ][ 'attributes' ],
								array( 'attribute' => $sAttr, 'modifier' => $sModifier )
							);
						}

						if( isset( $tempEquipment->{ 'stats' }->{ 'attributes' }->{ 'Precision' } ) )
						{
							$sAttr = 'Precision';
							$sModifier = $tempEquipment->{ 'stats' }->{ 'attributes' }->{ 'Precision' };
							// Add selected stats to the 'attributes' in the item file
							array_push
							(
								$assoArr[ 'infix_upgrade' ][ 'attributes' ],
								array( 'attribute' => $sAttr, 'modifier' => $sModifier )
							);
						}

						if( isset( $tempEquipment->{ 'stats' }->{ 'attributes' }->{ 'Ferocity' } ) )
						{
							$sAttr = 'Ferocity';
							$sModifier = $tempEquipment->{ 'stats' }->{ 'attributes' }->{ 'Ferocity' };
							// Add selected stats to the 'attributes' in the item file
							array_push
							(
								$assoArr[ 'infix_upgrade' ][ 'attributes' ],
								array( 'attribute' => $sAttr, 'modifier' => $sModifier )
							);
						}

						if( isset( $tempEquipment->{ 'stats' }->{ 'attributes' }->{ 'Healing' } ) )
						{
							$sAttr = 'Healing';
							$sModifier = $tempEquipment->{ 'stats' }->{ 'attributes' }->{ 'Healing' };
							// Add selected stats to the 'attributes' in the item file
							array_push
							(
								$assoArr[ 'infix_upgrade' ][ 'attributes' ],
								array( 'attribute' => $sAttr, 'modifier' => $sModifier )
							);
						}

						if( isset( $tempEquipment->{ 'stats' }->{ 'attributes' }->{ 'CritDamage' } ) )
						{
							$sAttr = 'Ferocity';
							$sModifier = $tempEquipment->{ 'stats' }->{ 'attributes' }->{ 'CritDamage' };
							// Add selected stats to the 'attributes' in the item file
							array_push
							(
								$assoArr[ 'infix_upgrade' ][ 'attributes' ],
								array( 'attribute' => $sAttr, 'modifier' => $sModifier )
							);
						}

						if( isset( $tempEquipment->{ 'stats' }->{ 'attributes' }->{ 'ConditionDamage' } ) )
						{
							$sAttr = 'ConditionDamage';
							$sModifier = $tempEquipment->{ 'stats' }->{ 'attributes' }->{ 'ConditionDamage' };
							// Add selected stats to the 'attributes' in the item file
							array_push
							(
								$assoArr[ 'infix_upgrade' ][ 'attributes' ],
								array( 'attribute' => $sAttr, 'modifier' => $sModifier )
							);
						}
							$assoArr = json_decode( json_encode( $assoArr ) );
							$mEquipments[ EItemType::EQUIPMENT ][ $iSlot ]->{ 'details' } = $assoArr;
					}
				}
			}
		}

		// Upgrades
		$mEquipments[ EItemType::UPGRADES ]		= &$this->m_Equipments[ EItemType::UPGRADES ];
		for( $i = 0; $i < count( $aEquipmentID[ EItemType::UPGRADES ] ); $i++ )
		{
			// Decode the json files
			$aEquipmentID[ EItemType::UPGRADES ][ $i ] = json_decode( $aEquipmentID[ EItemType::UPGRADES ][ $i ] );

			// Loop through Character data "Equipments"
			for( $j = 0; $j < $iLength; $j++ )
			{
				// Check for idendical item ID from decoded files
				$tempEquipment = $this->m_CharacterData->{ 'equipment' }[ $j ];
				for( $k = 0; $k < count( $tempEquipment->{ 'upgrades' } ); $k++ )
				{
					if( $aEquipmentID[ EItemType::UPGRADES ][ $i ]->{ 'id' } == $tempEquipment->{ 'upgrades' }[ $k ] )
					{
						// Store into its slot
						$iSlot = $this->GetSlotIndex( $tempEquipment->{ 'slot' } );
						if( is_null( $mEquipments[ EItemType::UPGRADES ][ $iSlot ] ) )
						{
							$mEquipments[ EItemType::UPGRADES ][ $iSlot ] = $aEquipmentID[ EItemType::UPGRADES ][ $i ];
						}
						// If the slot is already filled and of the same slot then it is likely a Two-handed weapon
						else if( $iSlot == EEquipment::A1 )
						{
							// Store it into the weapon 2 slot instead
							$mEquipments[ EItemType::UPGRADES ][ EEquipment::A2 ] = $aEquipmentID[ EItemType::UPGRADES ][ $i ];
						}
						else if( $iSlot == EEquipment::B1 )
						{
							$mEquipments[ EItemType::UPGRADES ][ EEquipment::B2 ] = $aEquipmentID[ EItemType::UPGRADES ][ $i ];	
						}
					}
				}
			}
		}

		// Infusions
		$mEquipments[ EItemType::INFUSIONS ]	= &$this->m_Equipments[ EItemType::INFUSIONS ];
		for( $i = 0; $i < count( $aEquipmentID[ EItemType::INFUSIONS ] ); $i++ )
		{
			// Decode the json files
			$aEquipmentID[ EItemType::INFUSIONS ][ $i ] = json_decode( $aEquipmentID[ EItemType::INFUSIONS ][ $i ] );

			// Loop through Character data "Equipments"
			for( $j = 0; $j < $iLength; $j++ )
			{
				// Check for idendical item ID from decoded files
				$tempEquipment = $this->m_CharacterData->{ 'equipment' }[ $j ];
				for( $k = 0; $k < count( $tempEquipment->{ 'infusions' } ); $k++ )
				{
					if( $aEquipmentID[ EItemType::INFUSIONS ][ $i ]->{ 'id' } == $tempEquipment->{ 'infusions' }[ $k ] )
					{
						// Store into its slot
						$iSlot = $this->GetSlotIndex( $tempEquipment->{ 'slot' } );
						if( is_null( $mEquipments[ EItemType::INFUSIONS ][ $iSlot ] ) )
						{
							$mEquipments[ EItemType::INFUSIONS ][ $iSlot ] = $aEquipmentID[ EItemType::INFUSIONS ][ $i ];
						}
						// If the slot is already filled and of the same slot then it is likely a Two-handed weapon
						else if( $iSlot == EEquipment::A1 )
						{
							// Store it into the weapon 2 slot instead
							$mEquipments[ EItemType::INFUSIONS ][ EEquipment::A2 ] = $aEquipmentID[ EItemType::INFUSIONS ][ $i ];
						}
						else if( $iSlot == EEquipment::B1 )
						{
							$mEquipments[ EItemType::INFUSIONS ][ EEquipment::B2 ] = $aEquipmentID[ EItemType::INFUSIONS ][ $i ];	
						}
					}
				}
			}
		}
	}

	public	function	SetBuildData( $SpecMode )
	{
		// Get Specialization IDs of specific mode
		$aSpecIDs = Array();
		for( $i = 0; $i < count( $this->m_CharacterData->{ 'specializations' }->{ $SpecMode } ); $i++ )
		{
			if( !is_null( $this->m_CharacterData->{ 'specializations' }->{ $SpecMode }[ $i ] ) )
			{
				array_push( $aSpecIDs, $this->m_CharacterData->{ 'specializations' }->{ $SpecMode }[ $i ]->{ 'id' } );
			}
		}
		// Request for specialization data
		$aSpecIDs = $this->GetContentBatch( $aSpecIDs, EURI::SPECIALIZATIONS, '-90 days' );

		// Loop through each specialization
		for( $i = ESpecialization::A; $i < ESpecialization::MAX; $i++ )
		{
			// decode the content and store it
			$this->m_Specializations[ $i ][ ETrait::_S ] = json_decode( $aSpecIDs[ $i ] );
			if( isset( $this->m_Specializations[ $i ][ ETrait::_S ]->{ 'id' } ) )
			{
				$aTraitIDs = Array();
				// Loop through max amount of traits
				for( $j = ETrait::_0; $j < ETrait::MAX; $j++ )
				{
					if( $j < ETrait::_3 )
					{
						// Get the minor trait IDs
						array_push( $aTraitIDs, $this->m_Specializations[ $i ][ ETrait::_S ]->{ 'minor_traits' }[ $j ] );
					}
					else
					{
						// Get the major trait IDs
						array_push( $aTraitIDs, $this->m_Specializations[ $i ][ ETrait::_S ]->{ 'major_traits' }[ $j - 3 ] );
					}
				}

				// Request for trait data
				$aTraitIDs = $this->GetContentBatch( $aTraitIDs, EURI::TRAITS, '-90 days' );
				// Loop through max amount of traits ... again
				for( $j = ETrait::_0; $j < ETrait::MAX; $j++ )
				{
					// Decode the content and store it
					$this->m_Specializations[ $i ][ $j ] = json_decode( $aTraitIDs[ $j ] );
				}
			}
		}
	}

	public	function	CalculateCharacterAttributes( $sMode )
	{
		// Armor
		for( $i = EEquipment::HELM; $i < EEquipment::BACKPACK; $i++ )
		{
			$this->AddEquipmentToStats( $this->m_Equipments[ EItemType::EQUIPMENT ][ $i ], 2, $sMode );
		}
		$this->AddUpgradeComponent( EItemType::UPGRADES, EEquipment::HELM, EEquipment::BACKPACK, 2, $sMode );
		$this->AddUpgradeComponent( EItemType::INFUSIONS, EEquipment::HELM, EEquipment::BACKPACK, 2, $sMode );

		// Trinkets - fixes ascended trinkets
		for( $i = EEquipment::BACKPACK; $i < EEquipment::HELMAQUATIC; $i++ )
		{
			$this->AddEquipmentToStats( $this->m_Equipments[ EItemType::EQUIPMENT ][ $i ], 2, $sMode );
		}
		$this->AddUpgradeComponent( EItemType::UPGRADES, EEquipment::BACKPACK, EEquipment::HELMAQUATIC, 2, $sMode );
		$this->AddUpgradeComponent( EItemType::INFUSIONS, EEquipment::BACKPACK, EEquipment::HELMAQUATIC, 2, $sMode );	

		// Weapon
		for( $i = EEquipment::A1; $i < EEquipment::HELM; $i++ )
		{
			if( $i < EEquipment::B1 )
			{
				$this->AddEquipmentToStats( $this->m_Equipments[ EItemType::EQUIPMENT ][ $i ], 0, $sMode );	
			}
			else {	$this->AddEquipmentToStats( $this->m_Equipments[ EItemType::EQUIPMENT ][ $i ], 1, $sMode );		}
		}

		$this->AddUpgradeComponent( EItemType::UPGRADES, EEquipment::A1, EEquipment::B1, 0, $sMode );
		$this->AddUpgradeComponent( EItemType::INFUSIONS, EEquipment::A1, EEquipment::B1, 0, $sMode );
		$this->AddUpgradeComponent( EItemType::UPGRADES, EEquipment::B1, EEquipment::HELM, 1, $sMode );
		$this->AddUpgradeComponent( EItemType::INFUSIONS, EEquipment::B1, EEquipment::HELM, 1, $sMode );

		//Traits
		for( $i = 0; $i < ESpecialization::MAX; $i++ )
		{
			// Get the selected traits from character
			$aTraits = Array();
			$aTraits = $this->m_CharacterData->{ 'specializations' }->{ $sMode }[ $i ]->{ 'traits' };

			// Grab Calculated Attributes ( Base + Equipment )
			$aBaseAtt_A = Array();
			$aBaseAtt_B = Array();
			for( $j = 0; $j < EAttribute::MAX; $j++ )
			{
				$aBaseAtt_A[ $j ] = $this->GetAttribute( $j, $sMode, 0 );
				$aBaseAtt_B[ $j ] = $this->GetAttribute( $j, $sMode, 1 );
			}

			// Determine which traits have passive bonuses -- Minor
			for( $j = ETrait::_0; $j < ETrait::_3; $j++ )
			{
				$traitData = $this->m_Specializations[ $i ][ $j ];

				// Filter Conditional Bonuses
				switch( $traitData->{ 'id' } )
				{
					case 1350:	// Thick Skin
						for( $l = 0; $l < count( $traitData->{ 'facts' } ); $l++ )
						{
							$traitFact = $traitData->{ 'facts' }[ $l ];
						//	echo $aFact->{ 'target' }.':'. $aFact->{ 'value' }.'<br>';
							$this->AddAttribute( $traitFact->{ 'target' }, $traitFact->{ 'value' }, 2, $sMode );
						}
					break;
					case 1682: // Force of Will
						$this->AddAttribute( 'Vitality', 400, 2, $sMode );
					break;
					default:break;
				}
			}

			// Determine which traits have passive bonuses -- Major
			for( $j = ETrait::_3; $j < ETrait::MAX; $j++ )
			{
				$traitData = $this->m_Specializations[ $i ][ $j ];
				for( $k = 0; $k < count( $aTraits ); $k++ )
				{
					if( $traitData->{ 'id' } == $aTraits[ $k ] )
					{
						switch( $traitData->{ 'id' } )
						{
							case 1931:	// Last Rites
								$this->AddAttribute( 'Healing', 150, 2, $sMode );	// HP < 75%
							//	$this->AddAttribute( 'Healing', 300, 2, $sMode );	// HP < 50%
							//	$this->AddAttribute( 'Healing', 450, 2, $sMode );	// HP < 25%
							break;

							case 1156:	//	Kindled Zeal
							case 589:	//	Retributive Armor
							case 1821:	//	Hardened Foundation
							case 1379:	//	Armored Attack
							case 1984:	//	Pinpoint Distribution
							case 1860:	//	Mass Momentum
							case 978:	//	Instinctive Reaction
							case 1272:	//	Practiced Tolerance
							case 334:	//	Power Overwhelming
							case 232:	//	Ferocious Winds
							case 275:	//	Strength of Stone
							case 668:	//	Chaotic Transference
							case 810:	//	Target the Weak
								for( $l = 0; $l < count( $traitData->{ 'facts' } ); $l++ )
								{
									$traitFact = $traitData->{ 'facts' }[ $l ];
									switch( $traitFact->{ 'type' } )
									{
										case 'BuffConversion':
											$this->BuffConversion( $traitFact, $aBaseAtt_A, $aBaseAtt_B, $sMode );
										break;
										default:break;
									}
								}
							break;

							case 2011:	//	Blood Reaction
							case 855:	//	Deadly Strength
								$traitFact = $traitData->{ 'facts' }[ 0 ];
								switch( $traitFact->{ 'type' } )
								{
									case 'BuffConversion':
										$this->BuffConversion( $traitFact, $aBaseAtt_A, $aBaseAtt_B, $sMode );
									break;
									default:break;
								}
							break;
							default:break;
						}
					}
				}
			}
		}

		$sPrfoession = $this->m_CharacterData->{ 'profession' };
		for( $i = 0; $i < 2; $i++ )
		{
			// Calculate Toughness to Armor
			$this->m_CharacterAttri[ $sMode ][ $i ][ EAttribute::ARMOR ] += $this->m_CharacterAttri[ $sMode ][ $i ][ EAttribute::TOUGHNESS ];

			// Check for High/Med/Low HP
			switch( $sPrfoession )
			{
				case 'Warrior':		case 'Necromancer':
					$this->m_CharacterAttri[ $sMode ][ $i ][ EAttribute::HEALTH ] += BaseAttributes::HealthHigh;
				break;

				case 'Engineer':	case 'Ranger':		case 'Revenant':	case 'Mesmer':
					$this->m_CharacterAttri[ $sMode ][ $i ][ EAttribute::HEALTH ] += BaseAttributes::HealthMid;
				break;

				case 'Guardian':	case 'Elementalist':	case 'Thief':
					$this->m_CharacterAttri[ $sMode ][ $i ][ EAttribute::HEALTH ] += BaseAttributes::HealthLow;
				break;
			}
			// Calculate Vitality to  HP
			$this->m_CharacterAttri[ $sMode ][ $i ][ EAttribute::HEALTH ] += ( 10 * $this->m_CharacterAttri[ $sMode ][ $i ][ EAttribute::VITALITY ] );
			// Calculate Precision to Crit Chance
			$this->m_CharacterAttri[ $sMode ][ $i ][ EAttribute::CRITCHANCE ] += ( $this->m_CharacterAttri[ $sMode ][ $i ][ EAttribute::PRECISION ] - 916 ) / 21;
			// Calculate Ferocity to Crit Damage
			$this->m_CharacterAttri[ $sMode ][ $i ][ EAttribute::CRITDAMAGE ] += $this->m_CharacterAttri[ $sMode ][ $i ][ EAttribute::FEROCITY ] / 15;
		}
	}

	private function	BuffConversion( $tFact, $Attri_A, $Attri_B, $sMode )
	{
		switch( $tFact->{ 'source' } )
		{
			case 'Power':
				$this->AddAttribute( $tFact->{ 'target' }, $Attri_A[ EAttribute::POWER ] / $tFact->{ 'percent' }, 0, $sMode );
				$this->AddAttribute( $tFact->{ 'target' }, $Attri_B[ EAttribute::POWER ] / $tFact->{ 'percent' }, 1, $sMode );
		//		echo $Attri_A[ EAttribute::POWER ] . ' ' . $tFact->{ 'source' } . ' converted to ' . $tFact->{ 'percent' } . '% ' . $tFact->{ 'target' } . ' = ' . $Attri_A[ EAttribute::POWER ] / $tFact->{ 'percent' } .'<br>';
			break;

			case 'Toughness':
				$this->AddAttribute( $tFact->{ 'target' }, $Attri_A[ EAttribute::TOUGHNESS ] / $tFact->{ 'percent' }, 0, $sMode );
				$this->AddAttribute( $tFact->{ 'target' }, $Attri_B[ EAttribute::TOUGHNESS ] / $tFact->{ 'percent' }, 1, $sMode );
		//		echo $Attri_A[ EAttribute::TOUGHNESS ] . ' ' . $tFact->{ 'source' } . ' converted to ' . $tFact->{ 'percent' } . '% ' . $tFact->{ 'target' } . ' = ' . $Attri_A[ EAttribute::TOUGHNESS ] / $tFact->{ 'percent' } .'<br>';
			break;

			case 'Vitality':
				$this->AddAttribute( $tFact->{ 'target' }, $Attri_A[ EAttribute::VITALITY ] / $tFact->{ 'percent' }, 0, $sMode );
				$this->AddAttribute( $tFact->{ 'target' }, $Attri_B[ EAttribute::VITALITY ] / $tFact->{ 'percent' }, 1, $sMode );
		//		echo $Attri_A[ EAttribute::VITALITY ] . ' ' . $tFact->{ 'source' } . ' converted to ' . $tFact->{ 'percent' } . '% ' . $tFact->{ 'target' } . ' = ' . $Attri_A[ EAttribute::VITALITY ] / $tFact->{ 'percent' } .'<br>';
			break;

			case 'Precision':
				$this->AddAttribute( $tFact->{ 'target' }, $Attri_A[ EAttribute::PRECISION ] / $tFact->{ 'percent' }, 0, $sMode );
				$this->AddAttribute( $tFact->{ 'target' }, $Attri_B[ EAttribute::PRECISION ] / $tFact->{ 'percent' }, 1, $sMode );
		//		echo $Attri_A[ EAttribute::PRECISION ] . ' ' . $tFact->{ 'source' } . ' converted to ' . $tFact->{ 'percent' } . '% ' . $tFact->{ 'target' } . ' = ' . $Attri_A[ EAttribute::PRECISION ] / $tFact->{ 'percent' } .'<br>';
			break;

			case 'Ferocity':
			case 'CritDamage':
				$this->AddAttribute( $tFact->{ 'target' }, $Attri_A[ EAttribute::FEROCITY ] / $tFact->{ 'percent' }, 0, $sMode );
				$this->AddAttribute( $tFact->{ 'target' }, $Attri_B[ EAttribute::FEROCITY ] / $tFact->{ 'percent' }, 1, $sMode );
		//		echo $Attri_A[ EAttribute::FEROCITY ] . ' ' . $tFact->{ 'source' } . ' converted to ' . $tFact->{ 'percent' } . '% ' . $tFact->{ 'target' } . ' = ' . $Attri_A[ EAttribute::FEROCITY ] / $tFact->{ 'percent' } .'<br>';
			break;

			case 'ConditionDamage':
				$this->AddAttribute( $tFact->{ 'target' }, $Attri_A[ EAttribute::CONDIDAMAGE ] / $tFact->{ 'percent' }, 0, $sMode );
				$this->AddAttribute( $tFact->{ 'target' }, $Attri_B[ EAttribute::CONDIDAMAGE ] / $tFact->{ 'percent' }, 1, $sMode );
		//		echo $Attri_A[ EAttribute::CONDIDAMAGE ] . ' ' . $tFact->{ 'source' } . ' converted to ' . $tFact->{ 'percent' } . '% ' . $tFact->{ 'target' } . ' = ' . $Attri_A[ EAttribute::CONDIDAMAGE ] / $tFact->{ 'percent' } .'<br>';
			break;

			case 'Healing':
				$this->AddAttribute( $tFact->{ 'target' }, $Attri_A[ EAttribute::HEALPOWER ] / $tFact->{ 'percent' }, 0, $sMode );
				$this->AddAttribute( $tFact->{ 'target' }, $Attri_B[ EAttribute::HEALPOWER ] / $tFact->{ 'percent' }, 1, $sMode );
		//		echo $Attri_A[ EAttribute::HEALPOWER ] . ' ' . $tFact->{ 'source' } . ' converted to ' . $tFact->{ 'percent' } . '% ' . $tFact->{ 'target' } . ' = ' . $Attri_A[ EAttribute::HEALPOWER ] / $tFact->{ 'percent' } .'<br>';
			break;
			default:break;
		}
	}

	private	function	AddUpgradeComponent( $eType, $eMinIndex, $eMaxIndex, $set, $sMode )
	{
		$aUpgradeCount = Array();
		$aUpgradeData = Array();
		$aItem	=	&$this->m_Equipments[ $eType ];

		for( $i = $eMinIndex; $i < $eMaxIndex; $i++ )
		{
			if( isset( $aItem[ $i ]->{ 'name' } ) )
			{
				$matched = true;
				for( $j = 0; $j < count( $aUpgradeCount ); $j++ )
				{
					if( $aItem[ $i ]->{ 'id' } != $aUpgradeCount[ $j ]->{ 'id' } )
					{
						$matched = false;
					}
					else
					{
						$matched = true;
						$aUpgradeCount[ $j ]->{ 'count' } += 1;
						break;
					}
				}
				if( !$matched || count( $aUpgradeCount ) < 1 )
				{
					array_push( $aUpgradeData, $aItem[ $i ] );
					array_push
					(
						$aUpgradeCount,
						json_decode( '{"id": ' . $aItem[ $i ]->{ 'id' } . ',"count": ' . 1 . '}' )
					);
				}
			}
		}
		for( $i = 0; $i < count( $aUpgradeCount ); $i++ )
		{
			if( isset( $aUpgradeData[ $i ]->{ 'details' }->{ 'bonuses' } ) )
			{
				$this->UpgradeRuneRepair( $aUpgradeData[ $i ], $aUpgradeCount[ $i ]->{ 'count' } );
				$this->AddEquipmentToStats( $aUpgradeData[ $i ], $set, $sMode );
			}
			else if( isset( $aUpgradeData[ $i ]->{ 'details' }->{ 'infix_upgrade' }->{ 'attributes' } ) )
			{
				for( $j = 0; $j < $aUpgradeCount[ $i ]->{ 'count' }; $j++ )
				{
					$this->AddEquipmentToStats( $aUpgradeData[ $i ], $set, $sMode );
				}
			}
		}
	}

	private	function	UpgradeRuneRepair( &$aRune, $iNumBonuses )
	{
		$sStats = &$aRune->{ 'details' }->{ 'infix_upgrade' }->{ 'attributes' };
		$sBonuses = $aRune->{ 'details' }->{ 'bonuses' };

		for( $i = 0; $i < $iNumBonuses; $i++ )
		{
			$sText = explode
			(
				' ',
				$sBonuses[ $i ]
			);

			if( count( $sText ) < 3 )
			{
				$sModifier	= intval( preg_replace( '/[^0-9]+/', '', $sText[ 0 ] ), 10 );
				$sAttr		= $sText[ 1 ];
				if
				(
					$sAttr == 'Power'		|| $sAttr == 'Toughness'		|| $sAttr == 'Vitality'	||
					$sAttr == 'Precision'	|| $sAttr == 'Ferocity'			|| $sAttr == 'Healing'	||
					$sAttr == 'CritDamage'	|| $sAttr == 'ConditionDamage'
				)
				{
					array_push
					(
						$sStats, 
						json_decode( '{"attribute": "' . $sAttr . '","modifier": ' . $sModifier . '}' )
					);
				}
			}
		}
	}

	public	function	AddAttribute( $sAttribute, $iValue, $set, $sMode )
	{
		if( isset( $sAttribute ) && $iValue > 0 )
		{
			$eAttr = EAttribute::MAGICFIND;
			switch( $sAttribute )
			{
				case 'Power':		$eAttr = EAttribute::POWER;		break;
				case 'Toughness':	$eAttr = EAttribute::TOUGHNESS;	break;
				case 'Vitality':	$eAttr = EAttribute::VITALITY;	break;
				case 'Precision':	$eAttr = EAttribute::PRECISION;	break;
				case 'Armor':		$eAttr = EAttribute::ARMOR;		break;
				
				case 'Ferocity':
				case 'CritDamage':		$eAttr = EAttribute::FEROCITY;		break;
				
				case 'Condition Damage':
				case 'ConditionDamage':	$eAttr = EAttribute::CONDIDAMAGE;	break;
				
				case 'Healing':			$eAttr = EAttribute::HEALPOWER;		break;
				case 'AgonyResist':		$eAttr = EAttribute::AGONYRESIST;	break;
				default:break;
			}

			if( $set >= 2 )
			{
				$this->m_CharacterAttri[ $sMode ][ 0 ][ $eAttr ] += $iValue;
				$this->m_CharacterAttri[ $sMode ][ 1 ][ $eAttr ] += $iValue;
			} else { $this->m_CharacterAttri[ $sMode ][ $set ][ $eAttr ] += $iValue; }
		}
	}

	private	function	AddEquipmentToStats( &$aEquipment, $set, $sMode )
	{
		$aDetials = $aEquipment->{ 'details' };
		if( !is_null( $aDetials->{ 'defense' } ) )
		{
			$this->AddAttribute( 'Armor', $aDetials->{ 'defense' }, $set, $sMode );
		}

		$aAttr = $aDetials->{ 'infix_upgrade' }->{ 'attributes' };
		if( !is_null( $aAttr ) )
		{
			for( $j = 0; $j < count( $aAttr ); $j++ )
			{
				if( $aAttr[ $j ]->{ 'attribute' } == 'CritDamage' )
				{
					$aAttr[ $j ]->{ 'attribute' } = 'Ferocity';
				}
				$this->AddAttribute( $aAttr[ $j ]->{ 'attribute' }, $aAttr[ $j ]->{ 'modifier' }, $set, $sMode );
			}
		}
	}
//=========================================================================================
//	Functions
//=========================================================================================	
	public	function	SetCharacterData( $sCharacterName, $sMode, $sAPIKey )
	{
		// Get Character Data
		$CharData = $this->GetCharacter( $sCharacterName, $sAPIKey );	
		$this->m_CharacterData	= json_decode( $CharData );
		// Setup for Attributes
		for( $i = 0; $i < 2; $i++ )
		{
			$this->m_CharacterAttri[ $sMode ][ $i ][ EAttribute::POWER ]		= BaseAttributes::Power;
			$this->m_CharacterAttri[ $sMode ][ $i ][ EAttribute::TOUGHNESS ]	= BaseAttributes::Toughness;
			$this->m_CharacterAttri[ $sMode ][ $i ][ EAttribute::VITALITY ]		= BaseAttributes::Vitality;
			$this->m_CharacterAttri[ $sMode ][ $i ][ EAttribute::PRECISION ]	= BaseAttributes::Precision;
			$this->m_CharacterAttri[ $sMode ][ $i ][ EAttribute::FEROCITY ]		= 0;
			$this->m_CharacterAttri[ $sMode ][ $i ][ EAttribute::CRITDAMAGE ]	= BaseAttributes::CriticalDamage;
			$this->m_CharacterAttri[ $sMode ][ $i ][ EAttribute::ARMOR ]		= 0;
			$this->m_CharacterAttri[ $sMode ][ $i ][ EAttribute::CRITCHANCE ]	= 0;
			$this->m_CharacterAttri[ $sMode ][ $i ][ EAttribute::CONDIDAMAGE ]	= 0;
			$this->m_CharacterAttri[ $sMode ][ $i ][ EAttribute::HEALPOWER ]	= 0;
			$this->m_CharacterAttri[ $sMode ][ $i ][ EAttribute::CONDIDURA ]	= 0;
			$this->m_CharacterAttri[ $sMode ][ $i ][ EAttribute::BOONDURA ]		= 0;
			$this->m_CharacterAttri[ $sMode ][ $i ][ EAttribute::AGONYRESIST ]	= 0;
			$this->m_CharacterAttri[ $sMode ][ $i ][ EAttribute::MAGICFIND ]	= 0;
		}

		// Character Equipment and its Data
		$this->SetEquipmentData();
		// Specialization and Trait Data based on wvw/pve/pvp
		$this->SetBuildData( $sMode );
		// Calculate character attributes ( Call this seperatly after modifying attributes with missing data )
	//	$this->CalculateCharacterAttributes( $sMode );
	}
}
?>