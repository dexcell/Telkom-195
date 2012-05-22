CREATE TABLE IF NOT EXISTS `t195_transactions` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `invoice` varchar(25) NOT NULL,
  `payment_code` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice` (`invoice`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;