<?php
abstract class EEquipment
{
	const	A1	=	0;
	const	A2	=	1;
	const	B1	=	2;
	const	B2	=	3;
	const	HELM		=	4;
	const	SHOULDERS	=	5;
	const	COAT		=	6;
	const	GLOVES		=	7;
	const	LEGGINGS	=	8;
	const	BOOTS		=	9;
	const	BACKPACK	=	10;
	const	AMULET		=	11;
	const	RING1		=	12;
	const	RING2		=	13;
	const	ACCESSORY1	=	14;
	const	ACCESSORY2	=	15;
	const	HELMAQUATIC	=	16;
	const	AQUATIC_A	=	17;
	const	AQUATIC_B	=	18;
	const	PICK		=	19;
	const	AXE			=	20;
	const	SICKLE		=	21;
	
	const	MAX			=	22;
}

abstract class	EItemType
{
	const	EQUIPMENT	=	0;
	const	UPGRADES	=	1;
	const	INFUSIONS	=	2;
	const	MAX			=	3;
}

abstract class ESpecialization
{
	const	A	=	0;
	const	B	=	1;
	const	C	=	2;
	const	MAX =	3;
}

abstract class ETrait
{
// Minor Traits
	const	_0	=	0;
	const	_1	=	1;
	const	_2	=	2;
// Major Traits
	const	_3	=	3;
	const	_4	=	4;
	const	_5	=	5;
	const	_6 =	6;
	const	_7 =	7;
	const	_8 =	8;
	const	_9 =	9;
	const	_10 =	10;
	const	_11 =	11;
	
	const	MAX =	12;
	const	_S	=	13;
}

abstract class EAttribute
{
	const	POWER		=	0;
	const	TOUGHNESS	=	1;
	const	VITALITY	=	2;
	const	PRECISION	=	3;
	
	const	HEALTH		=	4;
	const	ARMOR		=	5;
	
	const	FEROCITY	=	6;
	const	CONDIDAMAGE	=	7;
	const	HEALPOWER	=	8;
	const	AGONYRESIST	=	9;
	
	const	CRITCHANCE	=	10;
	const	CRITDAMAGE	=	11;
	const	CONDIDURA	=	12;
	const	BOONDURA	=	13;
	
	const	MAGICFIND	=	14;
	const	MAX			=	15;
}

// Default Attributes with no Equipment
abstract class BaseAttributes
{	
	const	Power		=	1000;
	const	Toughness	=	1000;
	const	Vitality	=	1000;
	const	Precision	=	1000;

	const	CriticalDamage	=	150;
	
	const	HealthHigh	=	9212;
	const	HealthMid	=	5922;
	const	HealthLow	=	1645;
}

// URI for GW2API and Cache folders
abstract class	EURI
{
	const	BASE			=	'https://api.guildwars2.com/v2/';
	const	ACCESS_TOKEN	=	'?access_token=';
	const	ITEMS			=	'items';
	const	ITEMSTATS		=	'itemstats';
	const	TRAITS			=	'traits';
	const	SPECIALIZATIONS	=	'specializations';
	const	CHARACTERS		=	'characters';
	const	INVENTORY		=	'inventory';
}
?>