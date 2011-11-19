
CREATE TABLE IF NOT EXISTS `Erlass` (
  `id` int(10) unsigned NOT NULL PRIMARY KEY auto_increment,
  `Bestellnummer` varchar(64) NOT NULL,
  `Kategorie` varchar(128) NOT NULL,
  `Aktenzeichen` varchar(64) NOT NULL,
  `Datum` date NOT NULL default '0000-00-00',
  `Herkunft` varchar(128) NOT NULL,
  `Autor` varchar(128) NOT NULL,
  `Betreff` varchar(256) NOT NULL,
  `Dokument` mediumtext NOT NULL,
  `NfD` tinyint(1) unsigned NOT NULL,
  `Status` varchar(128) NOT NULL,
  KEY (Kategorie, Aktenzeichen, Datum, Herkunft, Autor),
  FULLTEXT (Betreff, Dokument)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `Thema` (
  `Name` varchar(128) NOT NULL default '' PRIMARY KEY,
  `parent` varchar(128) NOT NULL default '',
  KEY (parent)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `betrifft` (
  `Erlass` int(10) unsigned NOT NULL,
  `Thema` varchar(128) NOT NULL,
  PRIMARY KEY (Erlass, Thema)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `Kunde` (
  `id` varchar(128) NOT NULL PRIMARY KEY,
  `Passwort` binary(40) NOT NULL,
  `Stufe` tinyint(1) NOT NULL default '1'
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `challenge` (
  `Kunde` varchar(128) NOT NULL PRIMARY KEY,
  `challenge` binary(32) NOT NULL,
  `created` timestamp NOT NULL default CURRENT_TIMESTAMP
) ENGINE=MyISAM;

