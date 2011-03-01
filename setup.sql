CREATE TABLE IF NOT EXISTS `anhang` (
  `erlass` int(10) unsigned NOT NULL,
  `anhang` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`erlass`,`anhang`),
  KEY `anhang` (`anhang`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `Erlass` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `Kategorie` varchar(128) NOT NULL default '',
  `Aktenzeichen` varchar(64) NOT NULL default '',
  `Datum` date NOT NULL default '0000-00-00',
  `Herkunft` varchar(128) NOT NULL default '',
  `Autor` varchar(128) NOT NULL default '',
  `Betreff` varchar(128) NOT NULL default '',
  `Dokument` text NOT NULL,
  `NfD` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `Kategorie` (`Kategorie`),
  KEY `Herkunft` (`Herkunft`),
  KEY `Autor` (`Autor`),
  KEY `Datum` (`Datum`),
  KEY `Aktenzeichen` (`Aktenzeichen`),
  KEY `Betreff` (`Betreff`),
  FULLTEXT KEY `Dokument` (`Dokument`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `Thema` (
  `Name` varchar(128) NOT NULL default '',
  `parent` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`Name`),
  KEY `parent` (`parent`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `Kunde` (
  `id` varchar(128) NOT NULL,
  `Passwort` binary(40) NOT NULL,
  `Stufe` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

