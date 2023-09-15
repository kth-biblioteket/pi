--
-- Databas: `bibmet`
--

CREATE DATABASE IF NOT EXISTS `bibmet` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `bibmet`;

DELIMITER $$
--
-- Procedurer
--
CREATE DEFINER=`cecwik`@`localhost` PROCEDURE `tran`()
BEGIN
INSERT INTO bibmet.country(Country_code,Country_name) VALUES('XX','Testland');
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Tabellstruktur `bort_reg_isbn`
--

CREATE TABLE IF NOT EXISTS `bort_reg_isbn` (
  `ISBN` varchar(50) NOT NULL,
  `Pubtyp` varchar(50) DEFAULT NULL,
  `Titel` varchar(500) DEFAULT NULL,
  `TRITA` varchar(100) DEFAULT NULL,
  `KTH_id` varchar(8) DEFAULT NULL,
  `Fnamn` varchar(40) DEFAULT NULL,
  `Enamn` varchar(60) DEFAULT NULL,
  `Epost` varchar(50) DEFAULT NULL,
  `Dispdatum` varchar(20) DEFAULT NULL,
  `Regdatum` date DEFAULT NULL,
  `Handl` varchar(20) NOT NULL,
  `Kommentar` varchar(100) NOT NULL,
  `Returdatum` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `country`
--

CREATE TABLE IF NOT EXISTS `country` (
  `Country_code` char(2) NOT NULL,
  `Country_name` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `crossrefpost`
--

CREATE TABLE IF NOT EXISTS `crossrefpost` (
  `Datum` datetime NOT NULL,
  `DOI` varchar(20) NOT NULL,
  `Titel` varchar(100) NOT NULL,
  `Fnamn` varchar(20) NOT NULL,
  `Enamn` varchar(30) NOT NULL,
  `KTHid` char(7) NOT NULL,
  `Epost` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `crossref_post`
--

CREATE TABLE IF NOT EXISTS `crossref_post` (
  `Post_id` int(11) NOT NULL,
  `Datum` datetime NOT NULL,
  `DOI` varchar(30) NOT NULL,
  `Enamn` varchar(30) NOT NULL,
  `Fnamn` varchar(30) NOT NULL,
  `Epost` varchar(30) NOT NULL,
  `KTHid` char(7) NOT NULL,
  `Titel` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `db_info_text`
--

CREATE TABLE IF NOT EXISTS `db_info_text` (
  `DB_namn` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Trigger `db_info_text`
--
DELIMITER $$
CREATE TRIGGER `Hejhoppsan` BEFORE INSERT ON `db_info_text`
 FOR EACH ROW UPDATE Country SET Country_name = 'Australia' WHERE Country_code = 'AU'
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Tabellstruktur `filrad`
--

CREATE TABLE IF NOT EXISTS `filrad` (
  `Persondatum` datetime NOT NULL,
  `Radnr` int(11) NOT NULL,
  `Postnr` int(11) NOT NULL,
  `Rad` varchar(20000) NOT NULL,
  `KTHff` int(11) DEFAULT NULL,
  `C1_radnr` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `handlaeggare`
--

CREATE TABLE IF NOT EXISTS `handlaeggare` (
  `Handl_kod` char(2) NOT NULL,
  `Handl_namn` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `k_log_run`
--

CREATE TABLE IF NOT EXISTS `k_log_run` (
  `k_logg_koer_id` int(11) NOT NULL,
  `procedurnamn` varchar(50) DEFAULT NULL,
  `steg` varchar(50) DEFAULT NULL,
  `koerdatum` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `min_niv_isbn`
--

CREATE TABLE IF NOT EXISTS `min_niv_isbn` (
  `Antal` int(11) NOT NULL,
  `Regdatum` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `oanv_isbn`
--

CREATE TABLE IF NOT EXISTS `oanv_isbn` (
  `ISBN` varchar(50) NOT NULL,
  `Importdatum` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `oa_lista`
--

CREATE TABLE IF NOT EXISTS `oa_lista` (
  `Titel_id` int(11) DEFAULT NULL,
  `Titel` varchar(250) DEFAULT NULL,
  `Print_ISSN` char(9) DEFAULT NULL,
  `Online_ISSN` char(9) DEFAULT NULL,
  `Subject_package` varchar(250) DEFAULT NULL,
  `Link_url` varchar(250) DEFAULT NULL,
  `Foerlag` varchar(100) DEFAULT NULL,
  `Impact_factor` decimal(10,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `organization`
--

CREATE TABLE IF NOT EXISTS `organization` (
  `org_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `country_name` varchar(50) NOT NULL,
  `Org_type_code` char(2) NOT NULL,
  `city` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `organization_type`
--

CREATE TABLE IF NOT EXISTS `organization_type` (
  `Org_type_code` char(2) NOT NULL,
  `Org_type_sw` varchar(50) NOT NULL,
  `Org_type_en` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `reg_isbn`
--

CREATE TABLE IF NOT EXISTS `reg_isbn` (
  `ISBN` varchar(50) NOT NULL,
  `Pubtyp` varchar(50) DEFAULT NULL,
  `Titel` varchar(500) DEFAULT NULL,
  `TRITA` varchar(100) DEFAULT NULL,
  `KTH_id` varchar(8) DEFAULT NULL,
  `Fnamn` varchar(40) DEFAULT NULL,
  `Enamn` varchar(60) DEFAULT NULL,
  `Epost` varchar(50) DEFAULT NULL,
  `Dispdatum` varchar(20) DEFAULT NULL,
  `Regdatum` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `removed_rules`
--

CREATE TABLE IF NOT EXISTS `removed_rules` (
  `R_o_m_id` int(11) DEFAULT NULL,
  `R_c_m_id` int(11) DEFAULT NULL,
  `R_f_a_m_id` int(11) DEFAULT NULL,
  `Find_country` varchar(150) DEFAULT NULL,
  `Country_code` char(2) DEFAULT NULL,
  `Find_city` varchar(150) DEFAULT NULL,
  `Find_org` varchar(100) DEFAULT NULL,
  `Find_str_1` varchar(500) DEFAULT NULL,
  `Find_str_2` varchar(500) DEFAULT NULL,
  `Find_str_3` varchar(500) DEFAULT NULL,
  `Find_str_not_1` varchar(500) DEFAULT NULL,
  `Find_str_not_2` varchar(500) DEFAULT NULL,
  `Divide` int(11) DEFAULT NULL,
  `Country_1` varchar(150) DEFAULT NULL,
  `Country_2` varchar(150) DEFAULT NULL,
  `Country_3` varchar(150) DEFAULT NULL,
  `City_1` varchar(150) DEFAULT NULL,
  `City_2` varchar(150) DEFAULT NULL,
  `City_3` varchar(150) DEFAULT NULL,
  `Org_id_1` int(11) DEFAULT NULL,
  `Org_id_2` int(11) DEFAULT NULL,
  `Org_id_3` int(11) DEFAULT NULL,
  `User_id` varchar(20) DEFAULT NULL,
  `Rule_date` date DEFAULT NULL,
  `Remove_user_id` varchar(20) DEFAULT NULL,
  `Remove_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `removed_un_org_names`
--

CREATE TABLE IF NOT EXISTS `removed_un_org_names` (
  `Unified_org_names_id` int(11) DEFAULT NULL,
  `Unified_org_id` int(11) NOT NULL,
  `Name_local` varchar(150) NOT NULL,
  `Name_en` varchar(150) NOT NULL,
  `Country_name` varchar(150) NOT NULL,
  `Org_type_code` char(2) NOT NULL,
  `Comment` varchar(150) NOT NULL,
  `User_id` varchar(20) NOT NULL,
  `Latest_date` date NOT NULL,
  `Remove_user_id` varchar(20) NOT NULL,
  `Remove_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `rule_center_match`
--

CREATE TABLE IF NOT EXISTS `rule_center_match` (
  `R_c_m_id` int(11) NOT NULL,
  `Find_country` varchar(150) NOT NULL,
  `Country_code` char(2) DEFAULT NULL,
  `Find_city` varchar(150) DEFAULT NULL,
  `Find_org` varchar(100) DEFAULT NULL,
  `Find_sub_org` varchar(100) DEFAULT NULL,
  `Divide` int(11) DEFAULT NULL,
  `Country_1` varchar(150) DEFAULT NULL,
  `City_1` varchar(150) DEFAULT NULL,
  `Org_id_1` int(11) DEFAULT NULL,
  `Sub_org_id_1` int(11) DEFAULT NULL,
  `Country_2` varchar(150) DEFAULT NULL,
  `City_2` varchar(150) DEFAULT NULL,
  `Org_id_2` int(11) DEFAULT NULL,
  `Sub_org_id_2` int(11) DEFAULT NULL,
  `Country_3` varchar(150) DEFAULT NULL,
  `City_3` varchar(150) DEFAULT NULL,
  `Org_id_3` int(11) DEFAULT NULL,
  `Sub_org_id_3` int(11) DEFAULT NULL,
  `User_id` varchar(20) NOT NULL,
  `Rule_date` date NOT NULL,
  `Run_status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `rule_center_rundate`
--

CREATE TABLE IF NOT EXISTS `rule_center_rundate` (
  `Run_date` date NOT NULL,
  `Improve_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `rule_full_address_match`
--

CREATE TABLE IF NOT EXISTS `rule_full_address_match` (
  `R_f_a_m_id` int(11) NOT NULL,
  `Find_country` varchar(150) NOT NULL,
  `Country_code` char(2) DEFAULT NULL,
  `Find_city` varchar(150) DEFAULT NULL,
  `Find_str_1` varchar(500) DEFAULT NULL,
  `Find_str_2` varchar(500) DEFAULT NULL,
  `Find_str_3` varchar(500) DEFAULT NULL,
  `Find_str_not_1` varchar(500) DEFAULT NULL,
  `Find_str_not_2` varchar(500) DEFAULT NULL,
  `Divide` int(11) DEFAULT NULL,
  `Country_1` varchar(150) DEFAULT NULL,
  `City_1` varchar(150) DEFAULT NULL,
  `Org_id_1` int(11) DEFAULT NULL,
  `Sub_org_id_1` int(11) DEFAULT NULL,
  `Country_2` varchar(150) DEFAULT NULL,
  `City_2` varchar(150) DEFAULT NULL,
  `Org_id_2` int(11) DEFAULT NULL,
  `Sub_org_id_2` int(11) DEFAULT NULL,
  `Country_3` varchar(150) DEFAULT NULL,
  `City_3` varchar(150) DEFAULT NULL,
  `Org_id_3` int(11) DEFAULT NULL,
  `Sub_org_id_3` int(11) DEFAULT NULL,
  `User_id` varchar(20) NOT NULL,
  `Rule_date` date NOT NULL,
  `Run_status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `rule_full_address_rundate`
--

CREATE TABLE IF NOT EXISTS `rule_full_address_rundate` (
  `Run_date` date NOT NULL,
  `Improve_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `rule_org_match`
--

CREATE TABLE IF NOT EXISTS `rule_org_match` (
  `R_o_m_id` int(11) NOT NULL,
  `Find_country` varchar(150) DEFAULT NULL,
  `Country_code` char(2) DEFAULT NULL,
  `Find_city` varchar(150) DEFAULT NULL,
  `Find_org` varchar(250) DEFAULT NULL,
  `Find_sub_org` varchar(250) DEFAULT NULL,
  `Divide` int(11) DEFAULT NULL,
  `Country_1` varchar(150) DEFAULT NULL,
  `City_1` varchar(150) DEFAULT NULL,
  `Org_id_1` int(11) DEFAULT NULL,
  `Sub_org_id_1` varchar(100) DEFAULT NULL,
  `Country_2` varchar(150) DEFAULT NULL,
  `City_2` varchar(150) DEFAULT NULL,
  `Org_id_2` int(11) DEFAULT NULL,
  `Sub_org_id_2` varchar(100) DEFAULT NULL,
  `Country_3` varchar(150) DEFAULT NULL,
  `City_3` varchar(150) DEFAULT NULL,
  `Org_id_3` int(11) DEFAULT NULL,
  `Sub_org_id_3` varchar(100) DEFAULT NULL,
  `Rule_date` date DEFAULT NULL,
  `Run_status` int(11) DEFAULT NULL,
  `User_id` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `rule_org_rundate`
--

CREATE TABLE IF NOT EXISTS `rule_org_rundate` (
  `Run_date` date NOT NULL,
  `Improve_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `scopusrad`
--

CREATE TABLE IF NOT EXISTS `scopusrad` (
  `Persondatum` datetime NOT NULL,
  `Radnr` int(11) DEFAULT NULL,
  `Rad` mediumtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `tabortff`
--

CREATE TABLE IF NOT EXISTS `tabortff` (
  `Persondatum` datetime NOT NULL,
  `Postnr` int(11) NOT NULL,
  `Antalff` int(11) NOT NULL,
  `AntalKTH` int(11) DEFAULT NULL,
  `MinRadnrAF` int(11) DEFAULT NULL,
  `MaxRadnrAF` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `testadatum`
--

CREATE TABLE IF NOT EXISTS `testadatum` (
  `datkoll` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `unified_address`
--

CREATE TABLE IF NOT EXISTS `unified_address` (
  `u_a_id` int(11) NOT NULL,
  `org_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `unified_org_names`
--

CREATE TABLE IF NOT EXISTS `unified_org_names` (
  `Unified_org_names_id` int(11) NOT NULL,
  `Unified_org_id` int(11) DEFAULT NULL,
  `Name_local` varchar(100) DEFAULT NULL,
  `Name_en` varchar(100) DEFAULT NULL,
  `Country_name` varchar(150) DEFAULT NULL,
  `Org_type_code` char(4) DEFAULT NULL,
  `Comment` varchar(150) DEFAULT NULL,
  `User_id` varchar(20) NOT NULL,
  `Latest_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `username` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `webbuser`
--

CREATE TABLE IF NOT EXISTS `webbuser` (
  `webbuser_id` int(11) NOT NULL,
  `webbusername` varchar(20) NOT NULL,
  `webbuserpwd` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Index för dumpade tabeller
--

--
-- Index för tabell `crossref_post`
--
ALTER TABLE `crossref_post`
  ADD PRIMARY KEY (`Post_id`);

--
-- Index för tabell `reg_isbn`
--
ALTER TABLE `reg_isbn`
  ADD UNIQUE KEY `ISBN` (`ISBN`);

--
-- Index för tabell `rule_center_match`
--
ALTER TABLE `rule_center_match`
  ADD PRIMARY KEY (`R_c_m_id`);

--
-- Index för tabell `rule_full_address_match`
--
ALTER TABLE `rule_full_address_match`
  ADD PRIMARY KEY (`R_f_a_m_id`);

--
-- Index för tabell `rule_org_match`
--
ALTER TABLE `rule_org_match`
  ADD PRIMARY KEY (`R_o_m_id`);

--
-- Index för tabell `unified_org_names`
--
ALTER TABLE `unified_org_names`
  ADD PRIMARY KEY (`Unified_org_names_id`);

--
-- Index för tabell `webbuser`
--
ALTER TABLE `webbuser`
  ADD PRIMARY KEY (`webbuser_id`);

--
-- AUTO_INCREMENT för dumpade tabeller
--

--
-- AUTO_INCREMENT för tabell `crossref_post`
--
ALTER TABLE `crossref_post`
  MODIFY `Post_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT för tabell `rule_center_match`
--
ALTER TABLE `rule_center_match`
  MODIFY `R_c_m_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT för tabell `rule_full_address_match`
--
ALTER TABLE `rule_full_address_match`
  MODIFY `R_f_a_m_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT för tabell `rule_org_match`
--
ALTER TABLE `rule_org_match`
  MODIFY `R_o_m_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT för tabell `unified_org_names`
--
ALTER TABLE `unified_org_names`
  MODIFY `Unified_org_names_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT för tabell `webbuser`
--
ALTER TABLE `webbuser`
  MODIFY `webbuser_id` int(11) NOT NULL AUTO_INCREMENT;