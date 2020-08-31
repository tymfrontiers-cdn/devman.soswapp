SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

DROP TABLE IF EXISTS `apps`;
CREATE TABLE `apps` (
  `name` char(55) NOT NULL,
  `live` tinyint(1) DEFAULT 0,
  `status` char(25) NOT NULL DEFAULT 'PENDING',
  `user` char(12) NOT NULL,
  `_pu_key` varchar(350) NOT NULL,
  `_pr_key` varchar(350) NOT NULL,
  `prefix` char(7) NOT NULL,
  `request_timeout` char(14) NOT NULL,
  `domain` varchar(125) DEFAULT NULL,
  `endpoint` char(55) DEFAULT NULL,
  `title` char(65) NOT NULL,
  `description` varchar(256) NOT NULL,
  `_created` datetime NOT NULL DEFAULT current_timestamp(),
  `_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `request_history`;
CREATE TABLE `request_history` (
  `id` int(10) UNSIGNED NOT NULL,
  `app` char(128) NOT NULL,
  `path` varchar(256) NOT NULL,
  `param` text DEFAULT NULL,
  `_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


ALTER TABLE `apps`
  ADD PRIMARY KEY (`name`),
  ADD KEY `user` (`user`);

ALTER TABLE `request_history`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `request_history`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
