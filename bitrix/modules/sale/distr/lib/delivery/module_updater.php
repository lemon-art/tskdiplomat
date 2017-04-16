<?
/*  NOT FOR RELEASE!
	Add content to updater
	and module_updater
*/

include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/update_class.php");
global $DB, $DBType;
$updater = new CUpdater();
$updater->Init($curPath = "", $DBType, $updaterName = "", $curDir = "", "sale", "DB");

$updater->CopyFiles("install/admin", "admin");

if(!$updater->TableExists("b_sale_delivery_srv"))
{
	$updater->Query(array(
		"MySQL" => "create table if not exists b_sale_delivery_srv
			(
				ID int NOT NULL AUTO_INCREMENT,
				CODE varchar(50) NULL,
				PARENT_ID int NULL,
				NAME varchar(255) NOT NULL,
				ACTIVE char(1) NOT NULL,
				DESCRIPTION varchar(255) NULL,
				SORT int NOT NULL,
				LOGOTIP int NULL,
				CONFIG text NULL,
				CLASS_NAME varchar(255) NOT NULL,
				CURRENCY char(3) NOT NULL,
				primary key (ID),
				index IX_CODE(CODE)
			);"
	));
}

if(!$updater->TableExists("b_sale_delivery_rstr"))
{
	$updater->Query(array(
		"MySQL" => "create table if not exists b_sale_delivery_rstr
			(
				ID int NOT NULL AUTO_INCREMENT,
				DELIVERY_ID int NOT NULL,
				SORT int DEFAULT 100,
				CLASS_NAME varchar(255) NOT NULL,
				PARAMS  text,
				primary key (ID)
			);"
	));
}

if(!$updater->TableExists("b_sale_delivery_es"))
{
	$updater->Query(array(
		"MySQL" => "create table if not exists b_sale_delivery_es
			(
				ID int NOT NULL AUTO_INCREMENT,
				CODE varchar(50) NULL,
				NAME varchar(255) NOT NULL,
				DESCRIPTION varchar(255) NULL,
				CLASS_NAME varchar(255) NOT NULL,
				PARAMS text NULL,
				RIGHTS char(3) NOT NULL,
				DELIVERY_ID int NOT NULL,
				INIT_VALUE varchar(255) NULL,
				ACTIVE char(1) NOT NULL,
				SORT int DEFAULT 100,
				primary key (ID),
				INDEX IX_DELIVERY_ID (DELIVERY_ID)

			);"
	));
}

if (!$updater->TableExists("b_sale_order_delivery_es"))
{
	$updater->Query(array(
		"MySQL"  => "create table if not exists b_sale_order_delivery_es
			(
				ID INT NOT NULL AUTO_INCREMENT,
				SHIPMENT_ID INT NOT NULL,
				EXTRA_SERVICE_ID INT NOT NULL,
				VALUE VARCHAR (255) NULL,
				PRIMARY KEY (ID)
			);"
	));
}

if ($updater->TableExists("b_sale_delivery"))
{
	if (!$DB->Query("SELECT CONVERTED FROM b_sale_delivery WHERE 1=0", true))
		$updater->Query(array(
			"MySQL"  => "ALTER TABLE b_sale_delivery ADD CONVERTED char(1) not null default 'N'", true)
		);
}

if ($updater->TableExists("b_sale_delivery_handler"))
{
	if (!$DB->Query("SELECT CONVERTED FROM b_sale_delivery_handler WHERE 1=0", true))
		$updater->Query(array(
			"MySQL"  => "ALTER TABLE b_sale_delivery_handler ADD CONVERTED char(1) not null default 'N'", true
		));
}

if(IsModuleInstalled("sale"))
{
	CAgent::AddAgent('\CSaleDelivery::convertToNewAgent(true);', "sale", "N");
	CAgent::AddAgent('\CSaleDeliveryHandler::convertToNewAgent(true);', "sale", "N");
	CAgent::AddAgent('\CSaleDelivery::convertPSRelationsAgent();', "sale", "N");
}