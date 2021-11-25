CREATE TABLE `voicequestion` (
  `id` int(11) NOT NULL,
  `challenge` int(11) NOT NULL,
  `texto` text,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`,`challenge`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `voicecache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fileId` varchar(200) DEFAULT NULL,
  `filePath` varchar(200) DEFAULT NULL,
  `downloaded_at` timestamp NULL DEFAULT NULL,
  `conversation_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `question` int(11) DEFAULT NULL,
  `challenge` int(11) DEFAULT NULL,
  `selected` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `fileId_UNIQUE` (`fileId`),
  KEY `fk_vc_3_idx` (`conversation_id`),
  KEY `fk_vc_4_idx` (`question`,`challenge`),
  KEY `fk_vc_5_idx` (`user_id`),
  CONSTRAINT `fk_vc_3` FOREIGN KEY (`conversation_id`) REFERENCES `conversation` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_vc_4` FOREIGN KEY (`question`, `challenge`) REFERENCES `voicequestion` (`id`, `challenge`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_vc_5` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `voicegrades` (
  `question` int(11) NOT NULL,
  `challenge` int(11) NOT NULL,
  `evaluated` bigint(20) NOT NULL,
  `evaluator` bigint(20) NOT NULL,
  `grade` tinyint(4) DEFAULT NULL,
  `sent` tinyint(1) NOT NULL DEFAULT '0',
  `voice_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`question`,`challenge`,`evaluated`,`evaluator`),
  KEY `grades_ibfk_2_idx` (`evaluated`),
  KEY `grades_ibfk_3_idx` (`evaluator`),
  KEY `grades_ibfk_4_idx` (`challenge`),
  KEY `grades_ibk_4_idx` (`question`,`challenge`),
  KEY `grades_ibk_5_idx` (`voice_id`),
  CONSTRAINT `grades_ibfk_2` FOREIGN KEY (`evaluated`) REFERENCES `voicecache` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `grades_ibfk_3` FOREIGN KEY (`evaluator`) REFERENCES `voicecache` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `grades_ibk_4` FOREIGN KEY (`question`, `challenge`) REFERENCES `voicecache` (`question`, `challenge`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

