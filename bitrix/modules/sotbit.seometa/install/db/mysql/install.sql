CREATE TABLE IF NOT EXISTS `b_sotbit_seometa` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME` text,
  `ACTIVE` char(1) DEFAULT 'Y',
  `SORT` int(18) NOT NULL DEFAULT '100',
  `DATE_CHANGE` datetime DEFAULT NULL,
  `SITES` text,
  `TYPE_OF_CONDITION` varchar(255) DEFAULT NULL,
  `TYPE_OF_INFOBLOCK` varchar(255) DEFAULT NULL,
  `INFOBLOCK` varchar(255) DEFAULT NULL,
  `SECTIONS` varchar(255) DEFAULT NULL,
  `RULE` text,
  `META` text,
  `NO_INDEX` char(1) DEFAULT 'N',
  `STRONG` char(1) DEFAULT 'Y',
  `PRIORITY` float(3) NOT NULL DEFAULT 0.5,
  `CHANGEFREQ` varchar(10) NOT NULL DEFAULT 'monthly',
  `CATEGORY_ID` int(11) NOT NULL DEFAULT 0,        
  PRIMARY KEY (`ID`)
);
CREATE TABLE IF NOT EXISTS `b_sotbit_seometa_sitemaps` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `TIMESTAMP_CHANGE` timestamp,
  `SITE_ID` char(3) NOT NULL,
  `NAME` varchar(255) NOT NULL,
  `DATE_RUN` datetime DEFAULT NULL,
  `SETTINGS` longtext DEFAULT NULL,
  PRIMARY KEY (`ID`)
);                              
CREATE TABLE IF NOT EXISTS `b_sotbit_seometa_section`
(
    ID        int(11)        NOT NULL auto_increment,
    DATE_CHANGE DATETIME not null,
    DATE_CREATE DATETIME NULL,
    ACTIVE        CHAR(1)        DEFAULT 'Y' NOT NULL,
    SORT        int(11)        DEFAULT 500 NOT NULL,
    NAME        VARCHAR(255)    NULL,
    DESCRIPTION TEXT NULL,
    PARENT_CATEGORY_ID    INT(11) DEFAULT 0 NOT NULL,
    PRIMARY KEY (ID)
);                                                                                                                                                                                                                  
CREATE TABLE IF NOT EXISTS `b_sotbit_seometa_chpu`
(
  `ID` int(11) NOT NULL auto_increment,
  `ACTIVE` char(1) DEFAULT 'N',
  `NAME`        VARCHAR(255)    NULL,
  `REAL_URL` text,
  `NEW_URL` text,
  `CATEGORY_ID` int(11) NOT NULL DEFAULT 0,  
  `DATE_CHANGE` DATETIME not null,      
  PRIMARY KEY (ID)
);
CREATE TABLE IF NOT EXISTS `b_sotbit_seometa_section_chpu`
(
    ID        int(11)        NOT NULL auto_increment,
    DATE_CHANGE DATETIME not null,
    DATE_CREATE DATETIME NULL,
    ACTIVE        CHAR(1)        DEFAULT 'Y' NOT NULL,
    SORT        int(11)        DEFAULT 500 NOT NULL,
    NAME        VARCHAR(255)    NULL,
    DESCRIPTION TEXT NULL,
    PARENT_CATEGORY_ID    INT(11) DEFAULT 0 NOT NULL,
    PRIMARY KEY (ID)
);                                                                       
CREATE TABLE IF NOT EXISTS `b_sotbit_seometa_statistics`
(
    ID        int(11)        NOT NULL auto_increment,       
    DATE_CREATE DATETIME NULL,                              
    SORT        int(11)        DEFAULT 500 NOT NULL,   
    URL_FROM text,                              
    URL_TO text,   
    PAGES text,
    PAGES_COUNT int,
    ORDER_ID int(11),
    SESS_ID VARCHAR(255) NULL,
    PRIMARY KEY (ID)
);