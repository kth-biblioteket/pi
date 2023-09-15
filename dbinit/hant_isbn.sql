--
-- Databas: `hant_isbn`
--

CREATE DATABASE IF NOT EXISTS `hant_isbn` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `hant_isbn`;

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
  `Dispdatum` date DEFAULT NULL,
  `Regdatum` date DEFAULT NULL,
  `Handl` varchar(20) NOT NULL,
  `Kommentar` varchar(100) NOT NULL,
  `Returdatum` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `filrad`
--

CREATE TABLE IF NOT EXISTS `filrad` (
  `Persondatum` datetime NOT NULL,
  `Radnr` int(11) NOT NULL,
  `Postnr` int(11) NOT NULL,
  `Rad` varchar(2000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `min_niv_isbn`
--

CREATE TABLE IF NOT EXISTS `min_niv_isbn` (
  `Antal` int(11) NOT NULL,
  `Regdatum` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
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
  `Dispdatum` date DEFAULT NULL,
  `Regdatum` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
