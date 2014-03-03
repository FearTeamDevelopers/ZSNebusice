DROP TABLE IF EXISTS `tb_konzultace`;
DROP TABLE IF EXISTS `tb_cas`;
DROP TABLE IF EXISTS `tb_user`;

CREATE TABLE `tb_user` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`email` varchar(80) NOT NULL DEFAULT '',
`password` varchar(130) NOT NULL DEFAULT '',
`active` tinyint(4) NOT NULL DEFAULT 1,
`role` varchar(25) NOT NULL DEFAULT '',
`firstname` varchar (50) NOT NULL DEFAULT '',
`lastname` varchar (50) NOT NULL DEFAULT '',
`kabinet` varchar (10) NOT NULL DEFAULT '',
`potvrzeno` int (10) NOT NULL DEFAULT 0,
PRIMARY KEY (`id`),
UNIQUE (`email`),
KEY `ix_user_active` (`active`),
KEY `ix_user_login` (`email`, `password`, `active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tb_cas`(
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, 
`cas_start` varchar(20) NOT NULL DEFAULT '',
`cas_end` varchar(20) NOT NULL DEFAULT '',
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tb_konzultace` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`id_rodic` int(11) UNSIGNED NOT NULL ,
`id_ucitel` int(11) UNSIGNED NOT NULL ,
`id_cas` int(11) UNSIGNED NOT NULL ,
PRIMARY KEY (`id`),
FOREIGN KEY `fk_rodic` (`id_rodic`) REFERENCES `tb_user` (`id`) ON DELETE CASCADE,
FOREIGN KEY `fk_ucitel` (`id_ucitel`) REFERENCES `tb_user` (`id`) ON DELETE CASCADE,
FOREIGN KEY `fk_cas` (`id_cas`) REFERENCES `tb_cas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


insert into tb_user values(default, 'test10@test.cz', 'ba3253876aed6bc22d4a6ff53d8406c6ad864195ed144ab5c87621b6c233b548baeae6956df346ec8c17f5ea10f35ee3cbc514797ed7ddd3145464e2a0bab413', 
            default, 'role_ucitel', 'František', 'Dobrota', 'k105', default),
        (default, 'test11@test.cz', 'ba3253876aed6bc22d4a6ff53d8406c6ad864195ed144ab5c87621b6c233b548baeae6956df346ec8c17f5ea10f35ee3cbc514797ed7ddd3145464e2a0bab413', 
             default, 'role_ucitel', 'Jan', 'Nový', 'k102', default),
        (default, 'test12@test.cz', 'ba3253876aed6bc22d4a6ff53d8406c6ad864195ed144ab5c87621b6c233b548baeae6956df346ec8c17f5ea10f35ee3cbc514797ed7ddd3145464e2a0bab413', 
             default, 'role_ucitel', 'Milan', 'Novotný', 'k103', default),
        (default, 'test13@test.cz', 'ba3253876aed6bc22d4a6ff53d8406c6ad864195ed144ab5c87621b6c233b548baeae6956df346ec8c17f5ea10f35ee3cbc514797ed7ddd3145464e2a0bab413', 
             default, 'role_ucitel', 'Petr', 'Veselý', 'k101', default),
        (default, 'test14@test.cz', 'ba3253876aed6bc22d4a6ff53d8406c6ad864195ed144ab5c87621b6c233b548baeae6956df346ec8c17f5ea10f35ee3cbc514797ed7ddd3145464e2a0bab413', 
             default, 'role_ucitel', 'Jakub', 'Smutný', 'k104', default);

insert into tb_user values(default, 'test@test.cz', 'ba3253876aed6bc22d4a6ff53d8406c6ad864195ed144ab5c87621b6c233b548baeae6956df346ec8c17f5ea10f35ee3cbc514797ed7ddd3145464e2a0bab413', 
            default, 'role_rodic', 'Pan','Rodič', default,default),
        (default, 'test2@test.cz','ba3253876aed6bc22d4a6ff53d8406c6ad864195ed144ab5c87621b6c233b548baeae6956df346ec8c17f5ea10f35ee3cbc514797ed7ddd3145464e2a0bab413',
            default, 'role_rodic', 'Paní','Rodič', default, default),
        (default, 'test3@test.cz','ba3253876aed6bc22d4a6ff53d8406c6ad864195ed144ab5c87621b6c233b548baeae6956df346ec8c17f5ea10f35ee3cbc514797ed7ddd3145464e2a0bab413', 
            default, 'role_rodic', 'Pan2','Rodič', default, default);

insert into tb_cas values(default, '14:00','14:10');
insert into tb_cas values(default, '14:10','14:20');
insert into tb_cas values(default, '14:20','14:30');
insert into tb_cas values(default, '14:30','14:40');
insert into tb_cas values(default, '14:40','14:50');
insert into tb_cas values(default, '14:50','15:00');
insert into tb_cas values(default, '15:00','15:10');
insert into tb_cas values(default, '15:10','15:20');
insert into tb_cas values(default, '15:20','15:30');
insert into tb_cas values(default, '15:30','15:40');
insert into tb_cas values(default, '15:40','15:50');
insert into tb_cas values(default, '15:50','16:00');
insert into tb_cas values(default, '16:00','16:10');
insert into tb_cas values(default, '16:10','16:20');
insert into tb_cas values(default, '16:20','16:30');
insert into tb_cas values(default, '16:30','16:40');
insert into tb_cas values(default, '16:40','16:50');
insert into tb_cas values(default, '16:50','17:00');
insert into tb_cas values(default, '17:00','17:10');
insert into tb_cas values(default, '17:10','17:20');
insert into tb_cas values(default, '17:20','17:30');
insert into tb_cas values(default, '17:30','17:40');
insert into tb_cas values(default, '17:40','17:50');
insert into tb_cas values(default, '17:50','18:00');
insert into tb_cas values(default, '18:00','18:10');
insert into tb_cas values(default, '18:10','18:20');
insert into tb_cas values(default, '18:20','18:30');
insert into tb_cas values(default, '18:30','18:40');
insert into tb_cas values(default, '18:40','18:50');
insert into tb_cas values(default, '18:50','19:00');

insert into tb_konzultace values (default, 7, 2, 9), (default, 7, 1, 12), (default, 8, 4, 8), (default, 6, 5, 9);