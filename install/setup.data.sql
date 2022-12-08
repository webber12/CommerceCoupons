CREATE TABLE `{PREFIX}commerce_coupons` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `code` varchar(50) DEFAULT NULL,
  `date_start` date DEFAULT NULL,
  `date_finish` date DEFAULT NULL,
  `date_create` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `discount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `discount_summ` decimal(10,2) NOT NULL DEFAULT '0.00',
  `coupon_type` text NOT NULL,
  `limit_orders` int(10) NOT NULL DEFAULT '1',
  `active` tinyint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


CREATE TABLE `{PREFIX}commerce_coupons_orders` (
  `coupon_id` int(10) NOT NULL,
  `order_id` int(10) NOT NULL,
  `coupon_info` text NOT NULL,
  UNIQUE KEY `coupon_id_order_id` (`coupon_id`,`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;