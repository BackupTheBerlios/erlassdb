CREATE TABLE IF NOT EXISTS `anhang` (
  `erlass` int(10) unsigned NOT NULL,
  `anhang` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`erlass`,`anhang`),
  KEY `anhang` (`anhang`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `erlass` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `aktenzeichen` varchar(64) NOT NULL,
  `datum` date NOT NULL,
  `institution` varchar(64) NOT NULL,
  `verfasser` varchar(64) NOT NULL,
  `text` text NOT NULL,
  `nfd` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`),
  FULLTEXT KEY `text` (`text`)
) ENGINE=MyISAM;

CREATE TABLE `erlassdb`.`nutzer` (
`id` VARCHAR( 128 ) NOT NULL ,
`passwort` VARCHAR( 32 ) NOT NULL ,
`rolle` ENUM( 'admin', 'dienstlich' ) NOT NULL ,
PRIMARY KEY ( `id` )
) ENGINE = MYISAM;