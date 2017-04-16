create table if not exists b_fcm_wishlist (
	ID int(18) not null auto_increment,
	TIMESTAMP_X datetime not null default '0000-00-00 00:00:00',
	SITE_ID char(2) not null,
	ACTIVE char(1) not null default 'Y',
	USER_ID int(18) not null default '0',
	USER_TYPE char(1) not null default 'S',
	IBLOCK_ID int(18) not null default '0',
	ELEMENT_ID int(18) not null default '0',
	primary key (ID));