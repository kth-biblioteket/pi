--
-- Databas: `hant_diva`
--
CREATE DATABASE IF NOT EXISTS `hant_diva` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `hant_diva`;

-- --------------------------------------------------------

--
-- Tabellstruktur `fg_orcid_kthid`
--

CREATE TABLE IF NOT EXISTS `fg_orcid_kthid` (
  `ORCIDid` char(19) NOT NULL,
  `KTH_id` char(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur `fg_orcid_kthid_20220622`
--

CREATE TABLE IF NOT EXISTS `fg_orcid_kthid_20220622` (
  `kth_id` varchar(8) DEFAULT NULL,
  `orcidid` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `filrad`
--

CREATE TABLE IF NOT EXISTS `filrad` (
  `Persondatum` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `Radnr` int(11) NOT NULL,
  `Rad` varchar(2000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur `gereatelink`
--

CREATE TABLE IF NOT EXISTS `gereatelink` (
  `id` int(11) NOT NULL,
  `hash` varchar(100) NOT NULL,
  `peer` text NOT NULL,
  `nonpeer` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur `kthid`
--

CREATE TABLE IF NOT EXISTS `kthid` (
  `id` int(11) NOT NULL,
  `hash` varchar(64) NOT NULL,
  `parameters` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur `nypersonal`
--

CREATE TABLE IF NOT EXISTS `nypersonal` (
  `KTH_id` char(8) NOT NULL,
  `Bef` char(2) NOT NULL,
  `Enamn` varchar(50) NOT NULL,
  `Fnamn` varchar(30) NOT NULL,
  `Anst_datum` varchar(10) DEFAULT NULL,
  `Anst_nuv_bef` char(10) DEFAULT NULL,
  `Bef_t_o_m` char(10) DEFAULT NULL,
  `Bef_ben` varchar(30) DEFAULT NULL,
  `Skol_kod` char(3) DEFAULT NULL,
  `Orgkod` varchar(10) DEFAULT NULL,
  `Ev_chef` varchar(4) DEFAULT NULL,
  `Fil_datum` char(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur `ny_personal`
--

CREATE TABLE IF NOT EXISTS `ny_personal` (
  `KTH_id` char(8) DEFAULT NULL,
  `Bef` char(2) DEFAULT NULL,
  `Enamn` varchar(50) DEFAULT NULL,
  `Fnamn` varchar(30) DEFAULT NULL,
  `Anst_datum` varchar(10) DEFAULT NULL,
  `Anst_nuv_bef` char(10) DEFAULT NULL,
  `Bef_t_o_m` char(10) DEFAULT NULL,
  `Skol_kod` char(3) DEFAULT NULL,
  `Org_kod` varchar(10) DEFAULT NULL,
  `Ev_chef` varchar(4) DEFAULT NULL,
  `Fildatum` char(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur `orcid_kthid`
--

CREATE TABLE IF NOT EXISTS `orcid_kthid` (
  `kth_id` varchar(8) DEFAULT NULL,
  `orcidid` varchar(19) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `orcid_kthid_20190510`
--

CREATE TABLE IF NOT EXISTS `orcid_kthid_20190510` (
  `ORCIDid` char(19) NOT NULL,
  `KTH_id` char(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur `orcid_kthid_20191002`
--

CREATE TABLE IF NOT EXISTS `orcid_kthid_20191002` (
  `kth_id` char(8) NOT NULL,
  `orcidid` char(19) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur `orcid_kthid_20191204`
--

CREATE TABLE IF NOT EXISTS `orcid_kthid_20191204` (
  `orcidid` char(19) DEFAULT NULL,
  `kth_id` char(8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur `orcid_kthid_20200312`
--

CREATE TABLE IF NOT EXISTS `orcid_kthid_20200312` (
  `kth_id` varchar(8) DEFAULT NULL,
  `orcidid` varchar(19) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `orcid_kthid_20200625`
--

CREATE TABLE IF NOT EXISTS `orcid_kthid_20200625` (
  `kth_id` varchar(8) DEFAULT NULL,
  `orcidid` varchar(19) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur `orcid_kthid_20200916`
--

CREATE TABLE IF NOT EXISTS `orcid_kthid_20200916` (
  `kth_id` varchar(8) DEFAULT NULL,
  `orcidid` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `orcid_kthid_20211025`
--

CREATE TABLE IF NOT EXISTS `orcid_kthid_20211025` (
  `kth_id` varchar(8) DEFAULT NULL,
  `orcidid` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur `orcid_kthid_20220221`
--

CREATE TABLE IF NOT EXISTS `orcid_kthid_20220221` (
  `kth_id` varchar(8) DEFAULT NULL,
  `orcidid` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur `orcid_kthid_20221129`
--

CREATE TABLE IF NOT EXISTS `orcid_kthid_20221129` (
  `kth_id` varchar(8) DEFAULT NULL,
  `orcidid` varchar(19) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur `orcid_kthid_ny`
--

CREATE TABLE IF NOT EXISTS `orcid_kthid_ny` (
  `kth_id` char(8) NOT NULL,
  `orcidid` char(19) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur `orcid_kthod`
--

CREATE TABLE IF NOT EXISTS `orcid_kthod` (
  `kth_id` varchar(8) DEFAULT NULL,
  `orcidid` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `organisation`
--

CREATE TABLE IF NOT EXISTS `organisation` (
  `Orgkod` varchar(10) NOT NULL,
  `Orgnr` varchar(10) DEFAULT NULL,
  `Orgnamn` varchar(100) NOT NULL,
  `Gatuadress` varchar(100) DEFAULT NULL,
  `Postnummer` char(5) DEFAULT NULL,
  `Postadress` varchar(50) DEFAULT NULL,
  `InomUF` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur `paramcache`
--

CREATE TABLE IF NOT EXISTS `paramcache` (
  `id` int(11) NOT NULL,
  `hash` varchar(64) NOT NULL,
  `parameters` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur `personal`
--

CREATE TABLE IF NOT EXISTS `personal` (
  `KTH_id` char(8) NOT NULL,
  `Bef` char(2) NOT NULL,
  `Enamn` varchar(50) NOT NULL,
  `Fnamn` varchar(30) NOT NULL,
  `Anst_datum` varchar(10) DEFAULT NULL,
  `Anst_nuv_bef` char(10) DEFAULT NULL,
  `Bef_t_o_m` char(10) DEFAULT NULL,
  `Bef_ben` varchar(30) DEFAULT NULL,
  `Skol_kod` char(3) DEFAULT NULL,
  `Orgkod` varchar(10) DEFAULT NULL,
  `Ev_chef` varchar(4) DEFAULT NULL,
  `Fil_datum` char(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur `personal_20211025`
--

CREATE TABLE IF NOT EXISTS `personal_20211025` (
  `KTH_id` char(8) NOT NULL,
  `Bef` char(2) NOT NULL,
  `Enamn` varchar(50) NOT NULL,
  `Fnamn` varchar(30) NOT NULL,
  `Anst_datum` varchar(10) DEFAULT NULL,
  `Anst_nuv_bef` char(10) DEFAULT NULL,
  `Bef_t_o_m` char(10) DEFAULT NULL,
  `Bef_ben` varchar(30) DEFAULT NULL,
  `Skol_kod` char(3) DEFAULT NULL,
  `Orgkod` varchar(10) DEFAULT NULL,
  `Ev_chef` varchar(4) DEFAULT NULL,
  `Fil_datum` char(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur `table 6`
--

CREATE TABLE IF NOT EXISTS `table 6` (
  `COL 1` varchar(124) DEFAULT NULL,
  `COL 2` varchar(99) DEFAULT NULL,
  `COL 3` varchar(48) DEFAULT NULL,
  `COL 4` varchar(47) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `table 7`
--

CREATE TABLE IF NOT EXISTS `table 7` (
  `COL 1` varchar(124) DEFAULT NULL,
  `COL 2` varchar(99) DEFAULT NULL,
  `COL 3` varchar(48) DEFAULT NULL,
  `COL 4` varchar(47) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `table 16`
--

CREATE TABLE IF NOT EXISTS `table 16` (
  `COL 1` varchar(8) DEFAULT NULL,
  `COL 2` varchar(3) DEFAULT NULL,
  `COL 3` varchar(28) DEFAULT NULL,
  `COL 4` varchar(20) DEFAULT NULL,
  `COL 5` varchar(10) DEFAULT NULL,
  `COL 6` varchar(12) DEFAULT NULL,
  `COL 7` varchar(24) DEFAULT NULL,
  `COL 8` varchar(24) DEFAULT NULL,
  `COL 9` varchar(8) DEFAULT NULL,
  `COL 10` varchar(7) DEFAULT NULL,
  `COL 11` varchar(10) DEFAULT NULL,
  `COL 12` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `table 17`
--

CREATE TABLE IF NOT EXISTS `table 17` (
  `COL 1` varchar(8) DEFAULT NULL,
  `COL 2` varchar(3) DEFAULT NULL,
  `COL 3` varchar(28) DEFAULT NULL,
  `COL 4` varchar(20) DEFAULT NULL,
  `COL 5` varchar(10) DEFAULT NULL,
  `COL 6` varchar(12) DEFAULT NULL,
  `COL 7` varchar(10) DEFAULT NULL,
  `COL 8` varchar(24) DEFAULT NULL,
  `COL 9` varchar(8) DEFAULT NULL,
  `COL 10` varchar(7) DEFAULT NULL,
  `COL 11` varchar(7) DEFAULT NULL,
  `COL 12` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `table 18`
--

CREATE TABLE IF NOT EXISTS `table 18` (
  `COL 1` varchar(8) DEFAULT NULL,
  `COL 2` varchar(3) DEFAULT NULL,
  `COL 3` varchar(28) DEFAULT NULL,
  `COL 4` varchar(20) DEFAULT NULL,
  `COL 5` varchar(10) DEFAULT NULL,
  `COL 6` varchar(12) DEFAULT NULL,
  `COL 7` varchar(10) DEFAULT NULL,
  `COL 8` varchar(25) DEFAULT NULL,
  `COL 9` varchar(8) DEFAULT NULL,
  `COL 10` varchar(6) DEFAULT NULL,
  `COL 11` varchar(7) DEFAULT NULL,
  `COL 12` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `table 22`
--

CREATE TABLE IF NOT EXISTS `table 22` (
  `kth_id` varchar(8) DEFAULT NULL,
  `orcidid` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `table 30`
--

CREATE TABLE IF NOT EXISTS `table 30` (
  `KTHid` varchar(8) DEFAULT NULL,
  `Orcid` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `table 31`
--

CREATE TABLE IF NOT EXISTS `table 31` (
  `KTHid` varchar(8) DEFAULT NULL,
  `Orcid` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `table 32`
--

CREATE TABLE IF NOT EXISTS `table 32` (
  `KTHid` varchar(8) DEFAULT NULL,
  `Orcid` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `table 34`
--

CREATE TABLE IF NOT EXISTS `table 34` (
  `kthid` varchar(8) DEFAULT NULL,
  `orcid` varchar(19) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `table 35`
--

CREATE TABLE IF NOT EXISTS `table 35` (
  `kthid` varchar(8) DEFAULT NULL,
  `orcid` varchar(19) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `table 36`
--

CREATE TABLE IF NOT EXISTS `table 36` (
  `kthid` varchar(8) DEFAULT NULL,
  `orcid` varchar(19) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `table 37`
--

CREATE TABLE IF NOT EXISTS `table 37` (
  `kthid` varchar(8) DEFAULT NULL,
  `orcid` varchar(19) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `table 38`
--

CREATE TABLE IF NOT EXISTS `table 38` (
  `kthid` varchar(8) DEFAULT NULL,
  `orcid` varchar(19) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `table 39`
--

CREATE TABLE IF NOT EXISTS `table 39` (
  `kthid` varchar(8) DEFAULT NULL,
  `orcid` varchar(19) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `table 42`
--

CREATE TABLE IF NOT EXISTS `table 42` (
  `COL 1` varchar(3) DEFAULT NULL,
  `COL 2` varchar(4) DEFAULT NULL,
  `COL 3` char(2) DEFAULT NULL,
  `COL 4` varchar(10) DEFAULT NULL,
  `COL 5` varchar(10) DEFAULT NULL,
  `COL 6` varchar(31) DEFAULT NULL,
  `COL 7` varchar(20) DEFAULT NULL,
  `COL 8` varchar(25) DEFAULT NULL,
  `COL 9` varchar(10) DEFAULT NULL,
  `COL 10` varchar(8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `uppdatering`
--

CREATE TABLE IF NOT EXISTS `uppdatering` (
  `personal` date NOT NULL,
  `orcid_kthid` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur `ut`
--

CREATE TABLE IF NOT EXISTS `ut` (
  `KTH_id` char(8) NOT NULL,
  `Bef` char(2) NOT NULL,
  `Enamn` varchar(50) NOT NULL,
  `Fnamn` varchar(30) NOT NULL,
  `Anst_nuv_bef` char(10) DEFAULT NULL,
  `Bef_t_o_m` char(10) DEFAULT NULL,
  `Bef_ben` varchar(30) DEFAULT NULL,
  `Skol_kod` char(3) DEFAULT NULL,
  `Orgkod` varchar(10) DEFAULT NULL,
  `Ev_chef` varchar(4) DEFAULT NULL,
  `Fil_datum` char(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur `utigen`
--

CREATE TABLE IF NOT EXISTS `utigen` (
  `KTH_id` char(8) NOT NULL,
  `Bef` char(2) NOT NULL,
  `Enamn` varchar(50) NOT NULL,
  `Fnamn` varchar(30) NOT NULL,
  `Anst_nuv_bef` char(10) DEFAULT NULL,
  `Bef_t_o_m` char(10) DEFAULT NULL,
  `Bef_ben` varchar(30) DEFAULT NULL,
  `Skol_kod` char(3) DEFAULT NULL,
  `Orgkod` varchar(10) DEFAULT NULL,
  `Ev_chef` varchar(4) DEFAULT NULL,
  `Fil_datum` char(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Index för dumpade tabeller
--

--
-- Index för tabell `gereatelink`
--
ALTER TABLE `gereatelink`
  ADD PRIMARY KEY (`id`);

--
-- Index för tabell `kthid`
--
ALTER TABLE `kthid`
  ADD PRIMARY KEY (`id`);

--
-- Index för tabell `organisation`
--
ALTER TABLE `organisation`
  ADD UNIQUE KEY `ix_Orgkod` (`Orgkod`) USING BTREE;

--
-- Index för tabell `paramcache`
--
ALTER TABLE `paramcache`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT för dumpade tabeller
--

--
-- AUTO_INCREMENT för tabell `gereatelink`
--
ALTER TABLE `gereatelink`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT för tabell `kthid`
--
ALTER TABLE `kthid`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT för tabell `paramcache`
--
ALTER TABLE `paramcache`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

GRANT ALL PRIVILEGES ON *.* TO 'pi_anv'@'%'