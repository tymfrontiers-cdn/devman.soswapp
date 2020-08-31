SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

INSERT INTO `work_domain` (`name`, `acronym`, `path`, `icon`, `description`) VALUES
('project-dev', 'DEV', '/app/devman', '&lt;i class=&quot;fas fa-code&quot;&gt;&lt;/i&gt;', 'Developer/DevApp management');

INSERT INTO `work_path` (`name`, `domain`, `type`, `path`, `nav_visible`, `access_rank`, `access_rank_strict`, `onclick`, `classname`, `title`, `icon`, `sort`, `description`, `_created`) VALUES
('98x28u6kmwk6', 'project-dev', 'ALTER', '/app', 0, 6, 0, NULL, NULL, '/App access', NULL, 0, 'Access to add and manage apps', '2020-08-26 01:47:14'),
('qsuwt4sw4j3v', 'project-dev', 'ALTER', '/', 0, 6, 0, NULL, NULL, '/Full access', NULL, 0, 'Full access to dev domain', '2020-08-26 01:16:46'),
('r747pc5cn6ed', 'project-dev', 'READ', '/request-logs', 1, 6, 0, NULL, NULL, 'Request log', '&lt;i class=&quot;fas fa-random&quot;&gt;&lt;/i&gt;', 5, 'Access apps&#39; request log', '2020-08-26 01:56:03'),
('zb5y48d4sz43', 'project-dev', 'READ', '/apps', 1, 6, 0, '', '', 'Dev apps', '&lt;i class=&quot;fas fa-layer-group&quot;&gt;&lt;/i&gt;', 0, 'List of dev apps', '2020-08-26 01:46:17');
