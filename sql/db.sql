DROP TABLE IF EXISTS `games`;
CREATE TABLE `games` (
  `gameUid`    int(11) NOT NULL AUTO_INCREMENT,
  `code`       varchar(10)      DEFAULT NULL,
  `maxPlayers` int              DEFAULT '10',
  `nbPlayers`  int              DEFAULT '0',
  `started`    tinyint(1)       DEFAULT '0',
  `finished`   tinyint(1)       DEFAULT '0',
  PRIMARY KEY (`gameUid`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

DROP TABLE IF EXISTS `games_players`;
CREATE TABLE `games_players` (
  `gamePlayerUid` int(11)    NOT NULL AUTO_INCREMENT,
  `gameUid`       int(11)    NOT NULL,
  `playerUid`     int(11)    NOT NULL,
  `played`        tinyint(1) NOT NULL,
  PRIMARY KEY (`gamePlayerUid`),
  UNIQUE KEY `unique_player_game` (`gameUid`, `playerUid`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

DROP TABLE IF EXISTS `players`;
CREATE TABLE `players` (
  `playerUid` int(11) NOT NULL AUTO_INCREMENT,
  `name`      varchar(45)      DEFAULT NULL,
  `password`  varchar(255)     DEFAULT NULL,
  `theme`     varchar(255)     DEFAULT NULL,
  PRIMARY KEY (`playerUid`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

DROP TABLE IF EXISTS `players_game_roles`;
CREATE TABLE `players_game_roles` (
  `playersRoleUid` int(11) NOT NULL AUTO_INCREMENT,
  `playerUid`      int(11)          DEFAULT NULL,
  `gameUid`        int(11)          DEFAULT NULL,
  `roleUid`        int(11)          DEFAULT NULL,
  PRIMARY KEY (`playersRoleUid`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `roleUid`                 int(11) NOT NULL AUTO_INCREMENT,
  `name`                    varchar(50)      DEFAULT NULL,
  `description`             text             DEFAULT NULL,
  `model`                   varchar(50)      DEFAULT NULL,
  `nb`                      tinyint(1)       DEFAULT '1',
  `good`                    tinyint(1)       DEFAULT '1',
  PRIMARY KEY (`roleUid`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

INSERT INTO `roles` VALUES 
(1, 'Gentil', 'Le gentil doit réussir 3 quêtes pour gagner la partie', 'good', 7, 1, 0),
(2, 'Merlin', 'Merlin sait qui sont les méchants et doit aider les gentils à faire les bonnes équipes sans se faire griller comme une buse.', 'merlin', 99, 1),
(3, 'Perceval', 'Perceval connait l’identité de Merlin (et/ou de Morgane), si Spruello est Perceval, les gentils sont dans la merde.', 'perceval', 1, 1),
(4, 'Méchant', "Le méchant ! Le méchant ! Oui c'est lui ! C'est le méchant ! Il doit faire échouer 3 quêtes ! C'est un méchant ! Est-ce que vous avez compris ?", 'evil', 5, 0),
(5, 'Assassin', "Si les gentils réussissent 3 quêtes, l'assasin peut essayer de tuer Merlin pour faire gagner les méchants" , 'assassin', 1, 0),
(6, 'Mordred', 'Mordered est un méchant inconnu de Merlin', 'mordred', 1, 0),
(7, 'Morgane', 'Morgane se fait passer pour Merlin aux yeux de Perceval, si Spruello est Perceval, ça peut être très amusant', 'morgana', 1, 0),
(8, 'Oberon', 'Oberon est un méchant qui ne connait', 'oberon', 1, 0),


DROP TABLE IF EXISTS `teams`;
CREATE TABLE `teams` (
  `teamUid`   int(11) NOT NULL AUTO_INCREMENT,
  `gameUid`   int(11)          DEFAULT NULL,
  `quest` tinyint(1)          DEFAULT NULL,
  `player1` int(11)          DEFAULT NULL,
  `player2` int(11)          DEFAULT NULL,
  `player3` int(11)          DEFAULT NULL,
  `player4` int(11)          DEFAULT NULL,
  `player5` int(11)          DEFAULT NULL,
  PRIMARY KEY (`voteUid`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;
  
DROP TABLE IF EXISTS `votes_teams`;
CREATE TABLE `votes_teams` (
  `voteUid`   int(11) NOT NULL AUTO_INCREMENT,
  `gameUid`   int(11)        DEFAULT NULL,
  `playerUid` int(11)        DEFAULT NULL,
  `teamUid` int(11)          DEFAULT NULL,
  `success` tinyint(1)       DEFAULT NULL,
  PRIMARY KEY (`voteUid`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;
  
DROP TABLE IF EXISTS `votes_quests`;
CREATE TABLE `votes_quests` (
  `voteUid`   int(11) NOT NULL AUTO_INCREMENT,
  `gameUid`   int(11)        DEFAULT NULL,
  `quest` tinyint(1)          DEFAULT NULL,
  `playerUid` int(11)        DEFAULT NULL,
  `success` tinyint(1)       DEFAULT NULL,
  PRIMARY KEY (`voteUid`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

DROP TABLE IF EXISTS `games_history`;
CREATE TABLE `games_history` (
  `gameHistoryUid`   int(11) NOT NULL AUTO_INCREMENT,
  `gameUid`   	int(11)         DEFAULT NULL,
  `playerUid` 	int(11)         DEFAULT NULL,
  `roleUid` 	int(11)         DEFAULT NULL,
  `team`		varchar(50)		DEFAULT NULL,
  `winner` 		tinyint(1)      DEFAULT NULL,
  `allies`		varchar(255)	DEFAULT NULL,
  PRIMARY KEY (`gameHistoryUid`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;
