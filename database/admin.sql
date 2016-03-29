INSERT INTO `tpa_admin_menu` (`id`, `pid`, `title`, `url`, `path`, `controller`, `action`, `query`, `hide`, `sort`) VALUES
(30, 0, '资讯', 'News/index', 'a3fa9c94fdbdbc53', 'News', 'index', '', 0, 599),
(31, 30, '分类管理', 'NewsCategory/index', '2dff5fa83512fb74', 'NewsCategory', 'index', '', 0, 0),
(32, 30, '资讯管理', 'News/index', 'a3fa9c94fdbdbc53', 'News', 'index', '', 0, 99),
(33, 0, '功能', 'Picture/index', '99172da972ae351e', 'Picture', 'index', '', 0, 499),
(36, 0, '用户', 'Administrator/index', 'c9f31a0725385ee1', 'Administrator', 'index', '', 0, 399),
(37, 36, '前台用户', 'User/index', '1e4ad1d86b7cb818', 'User', 'index', '', 0, 9),
(38, 36, '后台用户', 'Administrator/index', 'c9f31a0725385ee1', 'Administrator', 'index', '', 0, 8),
(39, 38, '管理员管理', 'Administrator/index', 'c9f31a0725385ee1', 'Administrator', 'index', '', 0, 9),
(40, 38, '管理员组', 'AdministratorGroup/index', '213e66790728d2f4', 'AdministratorGroup', 'index', '', 0, 8),
(44, 0, '设置', 'Config/index', '4b77a9868c764c89', 'Config', 'index', '', 0, 1),
(45, 44, '系统设置', 'Config/index', '4b77a9868c764c89', 'Config', 'index', '', 0, 9),
(53, 44, '后台菜单', 'AdminMenu/index', '59a4af8b52b1eaf2', 'AdminMenu', 'index', '', 0, 8),
(86, 0, '首页', 'Index/index', 'a70a134b313d6a2c', 'Index', 'index', '', 0, 9999),
(144, 44, '数据备份', 'Database/index', '0eab917b893d54dc', 'Database', 'index', '', 0, 0),
(88, 33, '邮件管理', 'Email/index', '10853703bede3baa', 'Email', 'index', '', 0, 0),
(92, 33, '图片库', 'Picture/index', '99172da972ae351e', 'Picture', 'index', '', 0, 10),
(124, 32, '添加', 'News/add', 'c1c3581ece65a72c', 'News', 'add', '', 1, 0),
(125, 32, '编辑', 'News/edit', 'c7d1c729b85acc5a', 'News', 'edit', '', 1, 0),
(145, 92, '上传', 'Picture/upload', 'c53a4119bb3d6309', 'Picture', 'upload', '', 1, 0);

insert into `tpa_admin_rule`(`id`,`md5`,`title`,`url`,`sort`) values
('2','a604fb21b86cc3a3','文件上传','File/upload','0'),
('3','c53a4119bb3d6309','图片上传','Picture/upload','0');

insert into `tpa_administrator`(`id`,`username`,`email`,`password`,`group_id`,`status`) values
('1','administrator','admindi1@adf.com','7faef41c2d8ebfd4c3e6769310e64975','1','1');

insert into `tpa_administrator_group`(`id`,`title`,`slug`,`rules`) values
('1','超级管理员','administrator','a70a134b313d6a2c,ddc7e264cecd31ca,f7934d059654d9bc,463a021af31c5997,f4d7450df2d36b36,21570845d84d0816,b12ec9c891a37c14,dfe86c01d9b93210,6442063c27cd4e84,6e8b2a4027c4c736,cf0ad476957ba385,70e52fd8dd0408b8,c2314219c94d53f1,56f8dd3a835ba897,a3fa9c94fdbdbc53,2dff5fa83512fb74,99172da972ae351e,72bb036716a70f74,2acbad5155d29a0b,8b1c8ca40c110b81,10853703bede3baa,c9f31a0725385ee1,1e4ad1d86b7cb818,213e66790728d2f4,4b77a9868c764c89,b855d87ddeb91de3,8808352a1340bdfe,59a4af8b52b1eaf2,99268a496437e610,335b52d0629e1bce,0eab917b893d54dc,f70771fe8bf77b8b');

insert into `tpa_config`(`id`,`name`,`remark`,`type`,`value`,`sort`) values
('1','SITE_NAME','站点名称','2','Thinkphp Admin','0');