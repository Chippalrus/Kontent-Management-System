<?php
error_reporting( 0 );
require_once( __DIR__ . '/CCharacter.php' );
//=========================================================================================
//	CCharacter by Chippalrus
//=========================================================================================
class CRender extends CCharacter
{
//=========================================================================================
//	Members
//=========================================================================================

//=========================================================================================
//	Constructor / Destructor
//=========================================================================================
	public	function	__construct		()
	{
		parent::__construct();
	}

	public	function	__destruct(){	parent::__destruct();	}
//=========================================================================================
//	Get
//=========================================================================================
//=========================================================================================
//	Set
//=========================================================================================
//=========================================================================================
//	Methods
//=========================================================================================
	public	function	ConstructGW2Data( $atts =
	[	 'apikey'	 	=> ''
		,'character'	=> ''
		,'mode'		 	=> 'pve'
		,'charimg'		=> 'none'	] )
	{
		if( $atts['apikey'] != '' && $atts['character'] !='' )
		{
			$this->SetCharacterData( $atts['character'], $atts['mode'], $atts['apikey'] );
			$this->CalculateCharacterAttributes( $atts['mode'] );
			
			$sRenderHTML = '<style>' . file_get_contents(	__DIR__  . '/warriorbias.css' ) . '</style>';
			//Call renders
			$sRenderHTML .=
					'<div id="OverContainer">
						<div id="aContainer">' .
							$this->RenderArmorDetails( $this->GetEquipments(), EEquipment::HELM ) .
							$this->RenderArmorDetails( $this->GetEquipments(), EEquipment::SHOULDERS ) .
							$this->RenderArmorDetails( $this->GetEquipments(), EEquipment::COAT ) .
							$this->RenderArmorDetails( $this->GetEquipments(), EEquipment::GLOVES ) .
							$this->RenderArmorDetails( $this->GetEquipments(), EEquipment::LEGGINGS ) .
							$this->RenderArmorDetails( $this->GetEquipments(), EEquipment::BOOTS ) .
						'<div id="woopan">' .
							$this->RenderWeaponDetials( $this->GetEquipments(), EEquipment::A1 ) .
							$this->RenderWeaponDetials( $this->GetEquipments(), EEquipment::A2 ) .
						'</div>' .
					'</div>
					<div id="tContainer">' .
							$this->RenderTrinketDetials( $this->GetEquipments(), EEquipment::BACKPACK ).
							$this->RenderTrinketDetials( $this->GetEquipments(), EEquipment::AMULET ) .
							$this->RenderTrinketDetials( $this->GetEquipments(), EEquipment::RING1 ) .
							$this->RenderTrinketDetials( $this->GetEquipments(), EEquipment::RING2 ) .
							$this->RenderTrinketDetials( $this->GetEquipments(), EEquipment::ACCESSORY1 ) .
							$this->RenderTrinketDetials( $this->GetEquipments(), EEquipment::ACCESSORY2 ) .
						'<div id="woopan">' .
							$this->RenderWeaponDetials( $this->GetEquipments(), EEquipment::B1 ) .
							$this->RenderWeaponDetials( $this->GetEquipments(), EEquipment::B2 ) .
						'</div>' .
					'</div>';
			$sRenderHTML = $sRenderHTML .
					'<div id="sContainer">' .
						$this->RenderSpecializationDetials( $this->GetSpecialization( ESpecialization::A ), $this->GetTraits( $atts['mode'], ESpecialization::A ), ESpecialization::A, $atts['mode'] ) .
						$this->RenderSpecializationDetials( $this->GetSpecialization( ESpecialization::B ), $this->GetTraits( $atts['mode'], ESpecialization::B ), ESpecialization::B, $atts['mode'] ) .
						$this->RenderSpecializationDetials( $this->GetSpecialization( ESpecialization::C ), $this->GetTraits( $atts['mode'], ESpecialization::C ), ESpecialization::C, $atts['mode'] ) .
					'</div>';
			$sRenderHTML = $sRenderHTML .
					'<div id="stContainer">
						<div id="WeaponSetA">	<div class="WeaponSetA">Weapon Set A</div>
							<div id="power" class="attributes">
								<span class="power"></span><span class="tehtxt">'.
									round( $this->GetAttribute( EAttribute::POWER, $atts['mode'], 0 ), 0 ) .
								'</span><sub class="name">Power</sub>
							</div>
							<div id="toughness" class="attributes">
								<span class="toughness"></span><span class="tehtxt">' .
									$this->GetAttribute( EAttribute::TOUGHNESS, $atts['mode'], 0 ) .
								'</span><sub class="name">Toughness</sub>
							</div>
							<div id="vitality" class="attributes">
								<span class="vitality"></span><span class="tehtxt">' .
									$this->GetAttribute( EAttribute::VITALITY, $atts['mode'], 0 ) .
								'</span><sub class="name">Vitality</sub>
							</div>
							<div id="precision" class="attributes">
								<span class="precision"></span><span class="tehtxt">' .
									$this->GetAttribute( EAttribute::PRECISION, $atts['mode'], 0 ) .
								'</span><sub class="name">Precision</sub>
							</div>
							<div id="health" class="attributes">
								<span class="health"></span><span class="tehtxt">' .
									$this->GetAttribute( EAttribute::HEALTH, $atts['mode'], 0 ) .
								'</span><sub class="name">Health</sub>
							</div>
							<div id="armor" class="attributes">
								<span class="armor"></span><span class="tehtxt">' .
									$this->GetAttribute( EAttribute::ARMOR, $atts['mode'], 0 ) .
								'</span><sub class="name">Armor</sub>
							</div>
							<div id="ferocity" class="attributes">
								<span class="ferocity"></span><span class="tehtxt">' .
									$this->GetAttribute( EAttribute::FEROCITY, $atts['mode'], 0 ) .
								'</span><sub class="name">Ferocity</sub>
							</div>
							<div id="condidamage" class="attributes">
								<span class="condidamage"></span><span class="tehtxt">' .
									$this->GetAttribute( EAttribute::CONDIDAMAGE, $atts['mode'], 0 ) .
								'</span><sub class="name">Condi Dmg</sub>
							</div>
							<div id="healing" class="attributes">
								<span class="healing"></span><span class="tehtxt">' .
									$this->GetAttribute( EAttribute::HEALPOWER, $atts['mode'], 0 ) .
								'</span><sub class="name">Healing</sub>
							</div>
							<div id="critchance" class="attributes">
								<span class="critchance"></span><span class="tehtxt">' .
									round( $this->GetAttribute( EAttribute::CRITCHANCE, $atts['mode'], 0 ), 2 ) .
								'</span><sub class="name">Crit Chance</sub>
							</div>
							<div id="critdamage" class="attributes">
								<span class="critdamage"></span><span class="tehtxt">' .
									round( $this->GetAttribute( EAttribute::CRITDAMAGE, $atts['mode'], 0 ), 2 ) .
								'%</span><sub class="name">Crit Dmg</sub>
							</div>
						</div>
						<div id="WeaponSetB"><div class="WeaponSetB">Weapon Set B</div>
							<div id="power" class="attributes">
								<span class="power"></span><span class="tehtxt">'.
									round( $this->GetAttribute( EAttribute::POWER, $atts['mode'], 1 ), 0 ) .
								'</span><sub class="name">Power</sub>
							</div>
							<div id="toughness" class="attributes">
								<span class="toughness"></span><span class="tehtxt">' .
									$this->GetAttribute( EAttribute::TOUGHNESS, $atts['mode'], 1 ) .
								'</span><sub class="name">Toughness</sub>
							</div>
							<div id="vitality" class="attributes">
								<span class="vitality"></span><span class="tehtxt">' .
									$this->GetAttribute( EAttribute::VITALITY, $atts['mode'], 1 ) .
								'</span><sub class="name">Vitality</sub>
							</div>
							<div id="precision" class="attributes">
								<span class="precision"></span><span class="tehtxt">' .
									$this->GetAttribute( EAttribute::PRECISION, $atts['mode'], 1 ) .
								'</span><sub class="name">Precision</sub>
							</div>
							<div id="health" class="attributes">
								<span class="health"></span><span class="tehtxt">' .
									$this->GetAttribute( EAttribute::HEALTH, $atts['mode'], 1 ) .
								'</span><sub class="name">Health</sub>
							</div>
							<div id="armor" class="attributes">
								<span class="armor"></span><span class="tehtxt">' .
									$this->GetAttribute( EAttribute::ARMOR, $atts['mode'], 1 ) .
								'</span><sub class="name">Armor</sub>
							</div>
							<div id="ferocity" class="attributes">
								<span class="ferocity"></span><span class="tehtxt">' .
									$this->GetAttribute( EAttribute::FEROCITY, $atts['mode'], 1 ) .
								'</span><sub class="name">Ferocity</sub>
							</div>
							<div id="condidamage" class="attributes">
								<span class="condidamage"></span><span class="tehtxt">' .
									$this->GetAttribute( EAttribute::CONDIDAMAGE, $atts['mode'], 1 ) .
								'</span><sub class="name">Condi Dmg</sub>
							</div>
							<div id="healing" class="attributes">
								<span class="healing"></span><span class="tehtxt">' .
								$this->GetAttribute( EAttribute::HEALPOWER, $atts['mode'], 1 ) .
							'</span><sub class="name">Healing</sub>
							</div>
							<div id="critchance" class="attributes">
								<span class="critchance"></span><span class="tehtxt">' .
									round( $this->GetAttribute( EAttribute::CRITCHANCE, $atts['mode'], 1 ), 2 ) .
								'</span><sub class="name">Crit Chance</sub>
							</div>
							<div id="critdamage" class="attributes">
								<span class="critdamage"></span><span class="tehtxt">' .
									round( $this->GetAttribute( EAttribute::CRITDAMAGE, $atts['mode'], 1 ), 2 ) .
								'%</span><sub class="name">Crit Dmg</sub>
							</div>
						</div>
					</div>';
			if( $atts['charimg'] != '' )
			{
				$sRenderHTML = $sRenderHTML .
				'<div id="mContainer">
					<img class="charport" src="' . $atts['charimg'] .  '">
				</div>';
			}
			else
			{
				$sRenderHTML = $sRenderHTML .
				'<div id="mContainer">
					<img class="charport" src="' . SITE_URL . '도/img/chara/gw2/brank.png' .  '">
				</div>';
			}
			$sRenderHTML = $sRenderHTML .
			'</div><br/>';
		}
		else
		{
			$sRenderHTML = 'Error:: apikey= ' . $atts['apikey'] . ', character= ' . $atts['character'] . ', mode= ' . $atts['mode'];
		}
		return $sRenderHTML;
	}
	
	public	function	RenderArmorDetails( $equipment, $eSlot )
	{
		$sDis;
		if( !isset( $equipment[ EItemType::EQUIPMENT ][ $eSlot ] ) ){	$sDis = '<div class="emptySlot"><img class="Brank" src="../brank.png"></div>';	} else
		{
			$sRarity = $equipment[ EItemType::EQUIPMENT ][ $eSlot ]->{ 'rarity' };
	$sDis = 	'<div class="hoverArmor">
					<img class="' . $sRarity . '"
					 src="' .  $equipment[ EItemType::EQUIPMENT ][ $eSlot ]->{ 'icon' } . '">
					<div class="itemInfo">
						<div class="name">
							<span class="' . $sRarity . '">'
								 . $equipment[ EItemType::EQUIPMENT ][ $eSlot ]->{ 'name' } . 
							'</span>
						</div>
						<div class="stats">
							Armor: ' . $equipment[ EItemType::EQUIPMENT ][ $eSlot ]->{ 'details' }->{ 'defense' } .
						'</div>';
						for( $i = 0; $i < 3; $i++ )
						{
	$sDis = $sDis .			'<div class="stats">
							<span class="values">
								+' 
								. $equipment[ EItemType::EQUIPMENT ][ $eSlot ]->{ 'details' }->{ 'infix_upgrade' }->{ 'attributes' }[ $i ]->{ 'modifier' }
								. " "
								. $equipment[ EItemType::EQUIPMENT ][ $eSlot ]->{ 'details' }->{ 'infix_upgrade' }->{ 'attributes' }[ $i ]->{ 'attribute' } .
							'</span>
						</div>';
						}
	$sDis = $sDis .			'<div class="stats">
							<span>
								<img class="upgrades" src="';
								if( is_null( $equipment[ EItemType::UPGRADES ][ $eSlot ]->{ 'icon' } ) )
								{
									$sDis = $sDis . SITE_URL . '도/img/chara/gw2/brank.png';
								}
								else
								{
									$sDis = $sDis . $equipment[ EItemType::UPGRADES ][ $eSlot ]->{ 'icon' };
								}
	$sDis = $sDis .				'">
							</span>
							<span class="upgrades">'
								. $equipment[ EItemType::UPGRADES ][ $eSlot ]->{ 'name' } .
							'</span>';

							switch( $equipment[ EItemType::UPGRADES ][ $eSlot ]->{ 'details' }->{ 'type' } )
							{
								case 'Rune':
									for( $i = 0; $i < count( $equipment[ EItemType::UPGRADES ][ $eSlot ]->{ 'details' }->{ 'bonuses' } ); $i++ )
									{
										$iUp = $i + 1;
	$sDis = $sDis .					'<div class="upgradesInfo">'
										. '(' . $iUp . ') ' . $equipment[ EItemType::UPGRADES ][ $eSlot ]->{ 'details' }->{ 'bonuses' }[ $i ] .
									'</div>';
									}
								break;

								case 'Gem':
									$sAStatMain = $equipment[ EItemType::UPGRADES ][ $eSlot ]->{ 'details' }->{ 'infix_upgrade' }->{ 'attributes' };
									if( isset( $sAStatMain ) )
									{
										for( $i = 0; $i < count( $sAStatMain ); $i++ )
										{
	$sDis = $sDis .						'<div class="upgradesInfo">
											+' . $sAStatMain[ $i ]->{ 'modifier' } . ' ' . $sAStatMain[ $i ]->{ 'attribute' } .
										'</div>';
										}
									}
								break;
								default:break;
							}
	$sDis = $sDis .		'</div>';
						if( $sRarity == 'Ascended' )
						{
	$sDis = $sDis .		'<div class="stats">
							<span>
								<img class="infusion" src="';
								if( is_null( $equipment[ EItemType::INFUSIONS ][ $eSlot ]->{ 'icon' } ) )
								{
									$sDis = $sDis . SITE_URL . '도/img/chara/gw2/brank.png';
								}
								else
								{
									$sDis = $sDis . $equipment[ EItemType::INFUSIONS ][ $eSlot ]->{ 'icon' } ;
								}
	$sDis = $sDis .				'">
							</span>
							<span class="infusion">'
								. $equipment[ EItemType::INFUSIONS ][ $eSlot ]->{ 'name' } .
							'</span>';
							if( isset( $equipment[ EItemType::INFUSIONS ][ $eSlot ]->{ 'details' }->{ 'infix_upgrade' }->{ 'buff' } ) )
							{
								$sVar = explode( "\n", $equipment[ EItemType::INFUSIONS ][ $eSlot ]->{ 'details' }->{ 'infix_upgrade' }->{ 'buff' }->{ 'description' } );
								for( $i = 0; $i < count( $sVar ); $i++ )
								{
	$sDis = $sDis .			'<div class="upgradesInfo">'
								. $sVar[ $i ] .
							'</div>';
								}
							}
	$sDis = $sDis .		'</div>';
						}
	$sDis = $sDis .		'<div class="">
							<span class="">'

								. $sRarity  .

							'</span>

						</div>

						<div class="">

							<span class="">'

								. $equipment[ EItemType::EQUIPMENT ][ $eSlot ]->{ 'details' }->{ 'type' }  .

							'</span>

						</div>

						<div class="">

							<span class="">

								Required Level: ' . $equipment[ EItemType::EQUIPMENT ][ $eSlot ]->{ 'level' }  .

							'</span>

						</div>

						<div class="stats">

							<span class="flavor">'

								. $equipment[ EItemType::EQUIPMENT ][ $eSlot ]->{ 'description' }  .

							'</span>

						</div>

					</div>

				</div>';

		}

		return $sDis;

	}

	public	function	RenderWeaponDetials( $equipment, $eSlot )
	{
		$sDis;
		if( !isset( $equipment[ EItemType::EQUIPMENT ][ $eSlot ] ) ){ $sDis = '<div class="emptySlot"><img class="Brank" src="' . SITE_URL . '도/img/chara/gw2/brank.png"></div>'; } else
		{
			$sRarity = $equipment[ EItemType::EQUIPMENT ][ $eSlot ]->{ 'rarity' };
	$sDis =			'<div class="hoverWeapon">
					<img class="' . $sRarity . '"
					src="' . $equipment[ EItemType::EQUIPMENT ][ $eSlot ]->{ 'icon' } . '">
					<div class="itemInfo">
						<div class="name">
							<span class="' . $sRarity . '">'
								. $equipment[ EItemType::EQUIPMENT ][ $eSlot ]->{ 'name' } .
							'</span>
						</div>
						<div class="stats">
							Weapon Strength: 
							<span class="values">'
								. $equipment[ EItemType::EQUIPMENT ][ $eSlot ]->{ 'details' }->{ 'min_power' } .
							' </span>
							-
							<span class="values"> '
								. $equipment[ EItemType::EQUIPMENT ][ $eSlot ]->{ 'details' }->{ 'max_power' } .
							'</span>
						</div>';
						if( isset( $equipment[ EItemType::EQUIPMENT ][ $eSlot ]->{ 'details' }->{ 'infix_upgrade' }->{ 'attributes' } ) )
						{
							for( $i = 0; $i < count( $equipment[ EItemType::EQUIPMENT ][ $eSlot ]->{ 'details' }->{ 'infix_upgrade' }->{ 'attributes' } ); $i++ )
							{
	$sDis = $sDis .			'<div class="stats">
							<span class="values">
								+' 
								. $equipment[ EItemType::EQUIPMENT ][ $eSlot ]->{ 'details' }->{ 'infix_upgrade' }->{ 'attributes' }[ $i ]->{ 'modifier' }
								. " "
								. $equipment[ EItemType::EQUIPMENT ][ $eSlot ]->{ 'details' }->{ 'infix_upgrade' }->{ 'attributes' }[ $i ]->{ 'attribute' } .
							'</span>
						</div>';
							}
						}
	$sDis = $sDis .			'<div class="stats">
							<span>
								<img class="sigil" src="';
								if( is_null( $equipment[ EItemType::UPGRADES ][ $eSlot ]->{ 'icon' } ) )
								{
									$sDis = $sDis . SITE_URL . '도/img/chara/gw2/brank.png';
								}
								else
								{
									$sDis = $sDis . $equipment[ EItemType::UPGRADES ][ $eSlot ]->{ 'icon' };
								}
	$sDis = $sDis .				'">
							</span>
							<span class="sigil"> '
								. $equipment[ EItemType::UPGRADES ][ $eSlot ]->{ 'name' } .
							'</span>';
			
							switch( $equipment[ EItemType::UPGRADES ][ $eSlot ]->{ 'details' }->{ 'type' } )
							{
								case 'Sigil':
									if( isset( $equipment[ EItemType::UPGRADES ][ $eSlot ]->{ 'details' }->{ 'infix_upgrade' }->{ 'buff' } ) )
									{
	$sDis = $sDis .					'<div class="upgradesInfo">'
										. $equipment[ EItemType::UPGRADES ][ $eSlot ]->{ 'details' }->{ 'infix_upgrade' }->{ 'buff' }->{ 'description' } .
									'</div>';
									}
								break;

								case 'Gem':
									$sAStatMain = $equipment[ EItemType::UPGRADES ][ $eSlot ]->{ 'details' }->{ 'infix_upgrade' }->{ 'attributes' };
									if( isset( $sAStatMain ) )
									{
										for( $i = 0; $i < count( $sAStatMain ); $i++ )
										{
	$sDis = $sDis .						'<div class="upgradesInfo">
											+' . $sAStatMain[ $i ]->{ 'modifier' } . ' ' . $sAStatMain[ $i ]->{ 'attribute' } .
										'</div>';
										}
									}
								break;
								default:break;
							}
	$sDis = $sDis .			'</div>';
						if( $sRarity == 'Ascended' || $sRarity == 'Legendary' )
						{
	$sDis = $sDis .			'<div class="stats">
							<span>
								<img class="infusion" src="';
								if( is_null( $equipment[ EItemType::INFUSIONS ][ $eSlot ]->{ 'icon' } ) )
								{
									$sDis = $sDis . SITE_URL . '도/img/chara/gw2/brank.png';
								}
								else
								{
									$sDis = $sDis . $equipment[ EItemType::INFUSIONS ][ $eSlot ]->{ 'icon' };
								}
	$sDis = $sDis .				'">
							</span>
							<span class="infusion"> '
								. $equipment[ EItemType::INFUSIONS ][ $eSlot ]->{ 'name' } .
							'</span>';
							if( isset( $equipment[ EItemType::INFUSIONS ][ $eSlot ]->{ 'details' }->{ 'infix_upgrade' }->{ 'buff' } ) )
							{
								$sVar = explode( "\n", $equipment[ EItemType::INFUSIONS ][ $eSlot ]->{ 'details' }->{ 'infix_upgrade' }->{ 'buff' }->{ 'description' } );
								for( $i = 0; $i < count( $sVar ); $i++ )
								{
	$sDis = $sDis .			'<div class="upgradesInfo">'
								. $sVar[ $i ] .
							'</div>';
								}
							}
	$sDis = $sDis .		'</div>';
						}
						if( $eSlot != EEquipment::A2 )
						{
							if( $eSlot != EEquipment::B2 )
							{
								$ESecondUpgrade = EEquipment::A2;
								switch( $eSlot )
								{
									case EEquipment::A1:	$ESecondUpgrade = EEquipment::A2;	break;
									case EEquipment::B1:	$ESecondUpgrade = EEquipment::B2;	break;
								}
								
								if( $equipment[ EItemType::EQUIPMENT ][ $ESecondUpgrade ] != 0 ) {} else
								{		
	$sDis = $sDis .			'<div class="stats">
							<span>
								<img class="sigil" src="';
									if( is_null( $equipment[ EItemType::UPGRADES ][ $ESecondUpgrade ]->{ 'icon' } ) )
									{
										$sDis = $sDis . SITE_URL . '도/img/chara/gw2/brank.png';
									}
									else
									{
										$sDis = $sDis . $equipment[ EItemType::UPGRADES ][ $ESecondUpgrade ]->{ 'icon' };
									}
	$sDis = $sDis .				'">
							</span>
							<span class="sigil"> '
									. $equipment[ EItemType::UPGRADES ][ $ESecondUpgrade ]->{ 'name' } .
							'</span>';

							switch( $equipment[ EItemType::UPGRADES ][ $ESecondUpgrade ]->{ 'details' }->{ 'type' } )
							{
								case 'Sigil':
									if( isset( $equipment[ EItemType::UPGRADES ][ $ESecondUpgrade ]->{ 'details' }->{ 'infix_upgrade' }->{ 'buff' } ) )
									{
	$sDis = $sDis .					'<div class="upgradesInfo">'
										. $equipment[ EItemType::UPGRADES ][ $ESecondUpgrade ]->{ 'details' }->{ 'infix_upgrade' }->{ 'buff' }->{ 'description' } .
									'</div>';
									}
								break;

								case 'Gem':
									$sAStatMain = $equipment[ EItemType::UPGRADES ][ $ESecondUpgrade ]->{ 'details' }->{ 'infix_upgrade' }->{ 'attributes' };
									if( isset( $sAStatMain ) )
									{
										for( $i = 0; $i < count( $sAStatMain ); $i++ )
										{
	$sDis = $sDis .						'<div class="upgradesInfo">
											+' . $sAStatMain[ $i ]->{ 'modifier' } . ' ' . $sAStatMain[ $i ]->{ 'attribute' } .
										'</div>';
										}
									}
								break;
								default:break;
							}

	$sDis = $sDis .			'</div>';
									if( $sRarity == 'Ascended' || $sRarity == 'Legendary' )
									{
				$sDis = $sDis .			'<div class="stats">
										<span>
											<img class="infusion" src="';
											if( is_null( $equipment[ EItemType::INFUSIONS ][ $ESecondUpgrade ]->{ 'icon' } ) )
											{
												$sDis = $sDis . SITE_URL . '도/img/chara/gw2/brank.png';
											}
											else
											{
												$sDis = $sDis . $equipment[ EItemType::INFUSIONS ][ $ESecondUpgrade ]->{ 'icon' };
											}
				$sDis = $sDis .				'">
										</span>
										<span class="infusion"> '
											. $equipment[ EItemType::INFUSIONS ][ $ESecondUpgrade ]->{ 'name' } .
										'</span>';
										if( isset( $equipment[ EItemType::INFUSIONS ][ $ESecondUpgrade ]->{ 'details' }->{ 'infix_upgrade' }->{ 'buff' } ) )
										{
											$sVar = explode( "\n", $equipment[ EItemType::INFUSIONS ][ $ESecondUpgrade ]->{ 'details' }->{ 'infix_upgrade' }->{ 'buff' }->{ 'description' } );
											for( $i = 0; $i < count( $sVar ); $i++ )
											{
				$sDis = $sDis .			'<div class="upgradesInfo">'
											. $sVar[ $i ] .
										'</div>';
											}
										}
				$sDis = $sDis .		'</div>';
									}
								}
							}
						}
	$sDis = $sDis .			'<div class="">
							<span class="">'
								. $sRarity  .
							'</span>
						</div>
						<div class="">
							<span class="">'
								. $equipment[ EItemType::EQUIPMENT ][ $eSlot ]->{ 'details' }->{ 'type' }  .
							'</span>
						</div>
						<div class="">
							<span class="">
								Required Level: ' . $equipment[ EItemType::EQUIPMENT ][ $eSlot ]->{ 'level' }  .
							'</span>
						</div>
						<div class="stats">
							<span class="flavor">'
								. $equipment[ EItemType::EQUIPMENT ][ $eSlot ]->{ 'description' }  .
							'</span>
						</div>
					</div>
				</div>';
		}
		return $sDis;
	}

	public	function	RenderTrinketDetials( $equipment, $eSlot )
	{
		$sDis;
		$sRarity = $equipment[ EItemType::EQUIPMENT ][ $eSlot ]->{ 'rarity' };
		if( !isset( $equipment[ EItemType::EQUIPMENT ][ $eSlot ] ) ){	$sDis = '<div class="emptySlot"><img class="Brank" src="' . SITE_URL . '도/img/chara/gw2/brank.png</div>';	} else
		{
	$sDis = $sDis .	'<div class="hoverTrinket">
					<img class="' . $sRarity . '"
					src="' . $equipment[ EItemType::EQUIPMENT ][ $eSlot ]->{ 'icon' } . '">
					<div class="itemInfo">
						<div class="name">
							<span class="' . $sRarity . '">'
								. $equipment[ EItemType::EQUIPMENT ][ $eSlot ]->{ 'name' } .
							'</span>
						</div>';
						$sAStatMain = $equipment[ EItemType::EQUIPMENT ][ $eSlot ]->{ 'details' }->{ 'infix_upgrade' }->{ 'attributes' };
						if( isset( $sAStatMain ) )
						{
							for( $i = 0; $i < count( $sAStatMain ); $i++ )
							{
	$sDis = $sDis .			'<div class="stats">
							<span class="values">
								+' . $sAStatMain[ $i ]->{ 'modifier' } . ' ' . $sAStatMain[ $i ]->{ 'attribute' } .
							'</span>
						</div>';
							}
						}

						if( $sRarity != 'Ascended' )
						{
	$sDis = $sDis .			'<div class="stats">
							<span>
								<img class="upgrades" src="';
								if( is_null( $equipment[ EItemType::UPGRADES ][ $eSlot ]->{ 'icon' } ) )
								{
									$sDis = $sDis . SITE_URL . '도/img/chara/gw2/brank.png';
								}
								else
								{
									$sDis = $sDis . $equipment[ EItemType::UPGRADES ][ $eSlot ]->{ 'icon' };
								}
	$sDis = $sDis .				'">
							</span>
							<span class="upgrades"> '
								. $equipment[ EItemType::UPGRADES ][ $eSlot ]->{ 'name' } .
							'</span>';
							$sAStatMain = $equipment[ EItemType::UPGRADES ][ $eSlot ]->{ 'details' }->{ 'infix_upgrade' }->{ 'attributes' };
							if( isset( $sAStatMain ) )
							{
								for( $i = 0; $i < count( $sAStatMain ); $i++ )
								{
	$sDis = $sDis .				'<div class="upgradesInfo">
									+' . $sAStatMain[ $i ]->{ 'modifier' } . ' ' . $sAStatMain[ $i ]->{ 'attribute' } .
								'</div>';
								}
							}
	$sDis = $sDis .			'</div>';
						}
						else
						{
	$sDis = $sDis .			'<div class="stats">
							<span>
								<img class="infusion" src="';
								if( is_null( $equipment[ EItemType::INFUSIONS ][ $eSlot ]->{ 'icon' } ) )
								{
									$sDis = $sDis . SITE_URL . '도/img/chara/gw2/brank.png';
								}
								else
								{
									$sDis = $sDis . $equipment[ EItemType::INFUSIONS ][ $eSlot ]->{ 'icon' };
								}
	$sDis = $sDis .				'">
							</span>
							<span class="infusion"> '
								. $equipment[ EItemType::INFUSIONS ][ $eSlot ]->{ 'name' } .
							'</span>';
							if( isset( $equipment[ EItemType::INFUSIONS ][ $eSlot ]->{ 'details' }->{ 'infix_upgrade' }->{ 'buff' } ) )
							{
								$sVar = explode( "\n", $equipment[ EItemType::INFUSIONS ][ $eSlot ]->{ 'details' }->{ 'infix_upgrade' }->{ 'buff' }->{ 'description' } );
								for( $i = 0; $i < count( $sVar ); $i++ )
								{
	$sDis = $sDis .			'<div class="upgradesInfo">'
								. $sVar[ $i ] .
							'</div>';
								}
							}
	$sDis = $sDis .			'</div>';
						}
	$sDis = $sDis .			'<div class="">
							<span class="">'
								. $sRarity  .
							'</span>
						</div>
						<div class="">
							<span class="">'
								. $equipment[ EItemType::EQUIPMENT ][ $eSlot ]->{ 'details' }->{ 'type' }  .
							'</span>
						</div>
						<div class="">
							<span class="">
								Required Level: ' . $equipment[ EItemType::EQUIPMENT ][ $eSlot ]->{ 'level' }  .
							'</span>
						</div>
						<div class="stats">
							<span class="flavor">'
								. $equipment[ EItemType::EQUIPMENT ][ $eSlot ]->{ 'description' }  .
							'</span>
						</div>
					</div>
				</div>';
		}
		return $sDis;
	}

	public	function	RenderSpecializationDetials( $eSpecialization, $CharaTraits, $ESpec, $sMode )
	{
		$sDis;
		if( !isset( $eSpecialization ) ) {	$sDis = '<div class="emptySpec"></div>';	} else
		{
			$aCurrentTraits = $CharaTraits->{ 'traits' };
			$bHideTrait = true;
			$sABC = 'INVALID_';
			switch( $ESpec )
			{
				case 0:		$sABC = 'A_';	break;
				case 1:		$sABC = 'B_';	break;
				case 2:		$sABC = 'C_';	break;
				default:	$sABC = 'INVALID_';	break;
			}

			if( is_null( $eSpecialization[ ETrait::_S ]->{ 'name' } ) ){	$sDis = '<div class="emptySpec"><img class="BrankSpec" src="' . SITE_URL . '도/img/chara/gw2/brank.png"></div>';	} else
			{
		$sDis = $sDis .
					'<div class="sContainer' . $ESpec . '">
						<img class="s' . $ESpec . '" src="' . $eSpecialization[ ETrait::_S ]->{ 'background' } . '">
						<div class="specInfo">'  . $eSpecialization[ ETrait::_S ]->{ 'name' } .'</div>
					</div>
				<div class="tehTraits">';
				for( $j = ETrait::_0; $j < ETrait::MAX; $j++ )
				{
		$sDis = $sDis .	
					'<div id="' . $sABC . $j . '" class="hoverTrait">';
						switch( $eSpecialization[ $j ]->{ 'id' }  )
						{
							case $aCurrentTraits[ 0 ]:	$bHideTrait = false;	break;
							case $aCurrentTraits[ 1 ]:	$bHideTrait = false;	break;
							case $aCurrentTraits[ 2 ]:	$bHideTrait = false;	break;
							default: 					$bHideTrait = true;		break;
						}

						if( !$bHideTrait || $j < 3 )
						{
		$sDis = $sDis .	'<img id="selected" class="' . $sABC . $j . '" src="' . $eSpecialization[ $j ]->{ 'icon' } . '">';
						}
						else
						{
		$sDis = $sDis .	'<img id="unused" class="' . $sABC . $j . '" src="' . $eSpecialization[ $j ]->{ 'icon' } . '">';
						}
		$sDis = $sDis .	'<div class="traitInfo">
							<div class="name"><span>' . $eSpecialization[ $j ]->{ 'name' } . '</span></div>
							<div class="stats">' . $eSpecialization[ $j ]->{ 'description' } .'</div>';
							$aTraitSkills = $eSpecialization[ $j ]->{ 'skills' };
							if( isset( $aTraitSkills ) )
							{
								$iNumOfSkills = count( $aTraitSkills );
								for( $i = 0; $i < $iNumOfSkills; $i++ )
								{
				$sDis = $sDis .	'<div class="skillInfo">
									<div class="name"><span>' . $aTraitSkills[ $i ]->{ 'name' } . '</span></div>
									<div class="stats">' . $aTraitSkills[ $i ]->{ 'description' } . '</div>';
									$iNumOfFacts = count( $aTraitSkills[ $i ]->{ 'facts' } );
									for( $k = 0; $k < $iNumOfFacts; $k++ )
									{
										$aSkillFact = $aTraitSkills[ $i ]->{ 'facts' }[ $k ];
				$sDis = $sDis .		'<div class="stats">';
										if( isset( $aSkillFact->{ 'icon' } ) )
										{
				$sDis = $sDis .			'<span><img class="trait" src="' . $aSkillFact->{ 'icon' }  . '"></span>';
										}
				$sDis = $sDis .			'<span class="traitdetails">'. $this->RenderTraitDescription( $aSkillFact ) . '</span>
									</div>';
									}
		$sDis = $sDis .			'</div>
								<div class="stats">
									<span><img class="trait" src="' . $aTraitSkills[ $i ]->{ 'icon' }  . '"></span>
									<span class="traitCD">Triggers: '. $aTraitSkills[ $i ]->{ 'name' }  .'</span>
								</div>';
								}
							}
							$iNumOfFacts = count( $eSpecialization[ $j ]->{ 'facts' } );
							for( $i = 0; $i < $iNumOfFacts; $i++ )
							{
		$sDis = $sDis .		'<div class="stats">';
							if( $eSpecialization[ $j ]->{ 'facts' }[ $i ]->{ 'icon' } != '' )
							{
		$sDis = $sDis .			'<span><img class="trait" src="' . $eSpecialization[ $j ]->{ 'facts' }[ $i ]->{ 'icon' }  . '"></span>';
							}
		$sDis = $sDis .			'<span class="traitdetails">' .	$this->RenderTraitDescription( $eSpecialization[ $j ]->{ 'facts' }[ $i ] ) . '</span>
							</div>';
							}
		$sDis = $sDis .		
						'</div>
					</div>';
				}
		$sDis = $sDis .
				'</div>';
			}
		}
		return $sDis;
	}

	public	function	RenderTraitDescription( $aFact )
	{	
		$sText = '';
		switch( $aFact->{ 'type' } )
		{
			case	'AttributeAdjust':
				if( $aFact->{ 'text' } != 'Attribute Adjust' && $sText = $aFact->{ 'text' } != '' )
				{
					$sText = $aFact->{ 'text' } .': +' . $aFact->{ 'value' };
				}
				else
				{
					$sText = $aFact->{ 'target' } . ': +' . $aFact->{ 'value' };
				}
				break;

			case	'Buff':
				if( $aFact->{ 'duration' } > 0 )
				{	
					$sText =	'<div class="stacks">'
									. $aFact->{ 'apply_count' } .
								'</div>'
					. $aFact->{ 'status' } . 
						' <span class"duration">(' . $aFact->{ 'duration' } . 's)</span>: ' . 
								$aFact->{ 'description' };
				}
				else
				{
					$sText =	'<div class="stacks">'
									. $aFact->{ 'apply_count' } .
								'</div>'
					. $aFact->{ 'status' } . ': ' . $aFact->{ 'description' };
				}
			break;

			case	'BuffConversion':
				$sText = $aFact->{ 'source' } .
				' converted to ' . $aFact->{ 'target' } . ': ' .
				$aFact->{ 'percent' } . '%';
			break;

			case	'ComboField':
				$sText = $aFact->{ 'text' } . $aFact->{ 'field_type' };
			break;

			case	'ComboFinisher':
				$sText = $aFact->{ 'text' } . $aFact->{ 'finisher_type' } . ' (' . $aFact->{ 'percent' } . ')';
			break;

			case	'Damage':	$sText = 'Number of Hits: ' . $aFact->{ 'hit_count' };				break;		
			case	'Distance':	$sText = $aFact->{ 'text' } . ': ' . $aFact->{ 'distance' };		break;
			case	'Number':	$sText = $aFact->{ 'text' } . ': ' . $aFact->{ 'value' };			break;
			case	'Percent':	$sText = $aFact->{ 'text' } . ': ' . $aFact->{ 'percent' } . '%';	break;
			
			case	'PrefixedBuff':
				if( $aFact->{ 'duration' } > 0 )
				{	
					$sText =	'<div class="stacks">'
									. $aFact->{ 'apply_count' } .
								'</div>'
					. $aFact->{ 'status' } . 
						' <span class"duration">(' . $aFact->{ 'duration' } . 's)</span>: ' . 
								$aFact->{ 'description' };
				}
				else
				{
					$sText =	'<div class="stacks">'
									. $aFact->{ 'apply_count' } .
								'</div>'
					. $aFact->{ 'status' } . ': ' . $aFact->{ 'description' };
				}
			break;

			case	'Radius':
				$sText = $aFact->{ 'text' } . ': ' . $sText = $aFact->{ 'distance' };
			break;

			case	'Range':
				$sText = $aFact->{ 'text' } . ': ' . $sText = $aFact->{ 'value' };
			break;

			case	'Recharge':
				$sText = '<span class="traitCD"> Recharge: ' . $sText = $aFact->{ 'value' } . ' seconds</span>';
			break;

			case	'Time':
				$sText = $aFact->{ 'text' } . ': ' . $sText = $aFact->{ 'duration' } . ' seconds';
			break;

			case	'NoData':
			case	'Unblockable':	$sText = $aFact->{ 'text' };	break;
			default:				$sText = '';					break;
		}
		return $sText;
	}
}
?>