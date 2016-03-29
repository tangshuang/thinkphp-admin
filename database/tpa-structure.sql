DROP TABLE IF EXISTS  `tpa_admin_menu`;
CREATE TABLE `tpa_admin_menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级菜单ID',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '菜单标题',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '链接地址',
  `controller` varchar(64) NOT NULL DEFAULT '' COMMENT '==菜单对应的控制器==',
  `action` varchar(32) NOT NULL DEFAULT '' COMMENT '==菜单对应的方法==',
  `path` char(16) NOT NULL COMMENT '==用于检索的url摘要，由U(controller/action)产生==',
  `query` varchar(255) NOT NULL DEFAULT '' COMMENT '==菜单参数==',
  `hide` tinyint(1) NOT NULL COMMENT '是否显示在导航中',
  `sort` smallint(4) NOT NULL DEFAULT '0' COMMENT '排序（同级有效）',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='后台菜单';

DROP TABLE IF EXISTS  `tpa_administrator`;
CREATE TABLE `tpa_administrator` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `username` varchar(32) NOT NULL COMMENT '用户名',
  `email` varchar(64) NOT NULL COMMENT '用于登陆的邮箱',
  `password` char(32) NOT NULL COMMENT '密码',
  `group_id` int(10) NOT NULL COMMENT '管理员组ID',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '用户状态',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `status` (`status`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='后台管理员表';

DROP TABLE IF EXISTS  `tpa_administrator_group`;
CREATE TABLE `tpa_administrator_group` (
  `id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(32) NOT NULL DEFAULT '' COMMENT '用户组中文名称',
  `slug` varchar(32) NOT NULL DEFAULT '' COMMENT '用户组的别名，用于查询',
  `rules` mediumtext NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='用户组';

DROP TABLE IF EXISTS  `tpa_config`;
CREATE TABLE `tpa_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '配置ID',
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '配置名称',
  `remark` varchar(100) NOT NULL DEFAULT '' COMMENT '配置说明',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '配置类型',
  `value` text COMMENT '配置值',
  `sort` smallint(4) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

DROP TABLE IF EXISTS  `tpa_picture`;
CREATE TABLE `tpa_picture` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键id自增',
  `path` varchar(255) NOT NULL DEFAULT '' COMMENT '路径',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '图片链接',
  `md5` char(32) NOT NULL DEFAULT '' COMMENT '文件md5',
  `sha1` char(40) NOT NULL DEFAULT '' COMMENT '文件 sha1编码',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

--
-- ---------------------------------------------------------------------------------
--

DROP TABLE IF EXISTS  `tpa_user`;
CREATE TABLE `tpa_user` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `username` varchar(32) NOT NULL COMMENT '登陆名',
  `password` VARCHAR(64) NOT NULL COMMENT '登陆密码',
  `nickname` varchar(32) NOT NULL COMMENT '用户昵称（显示名）',
  `avatar_image_id` int(10) NOT NULL COMMENT '用户头像图片ID',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` tinyint(1) NOT NULL COMMENT '状态。-2.挂失状态；-1.删除；0.未注册，禁用; 1.正常使用',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2000 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='用户信息表';

DROP TABLE IF EXISTS  `tpa_user_info`;
CREATE TABLE `tpa_user_info` (
  `uid` int(10) NOT NULL,
  `qq` varchar(18) NOT NULL,
  `school` varchar(32) NOT NULL COMMENT '毕业院校',
  `interest` varchar(32) NOT NULL COMMENT '兴趣爱好',
  `email` varchar(32) NOT NULL COMMENT '邮箱',
  `weixin_subscribe_id` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '微信订阅号ID',
  `weixin_service_id` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '微信服务号ID',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='用户信息表';

DROP TABLE IF EXISTS  `tpa_news`;
CREATE TABLE `tpa_news` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(160) NOT NULL,
  `excerpt` varchar(255) NOT NULL,
  `detail` text NOT NULL,
  `category_id` int(10) NOT NULL,
  `is_recommended` tinyint(1) NOT NULL,
  `is_video` tinyint(1) NOT NULL,
  `cover_image_id` int(10) NOT NULL,
  `create_time` datetime NOT NULL,
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `view_count` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

DROP TABLE IF EXISTS  `tpa_news_category`;
CREATE TABLE `tpa_news_category` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `slug` varchar(16) NOT NULL,
  `title` varchar(32) NOT NULL,
  `detail` tinytext NOT NULL,
  `sort` smallint(4) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

DROP TABLE IF EXISTS  `tpa_email`;
CREATE TABLE `tpa_email` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `email` char(11) NOT NULL,
  `title` varchar(140) NOT NULL,
  `content` tinytext NOT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;
