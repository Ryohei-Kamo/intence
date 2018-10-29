drop table if exists `user_users` cascade;

create table `user_users` (
  `user_id` INT unsigned not null auto_increment comment 'ユーザID'
  , `user_name` VARCHAR(20) unique not null comment 'ユーザネーム'
  , `password` VARCHAR(256) default null comment 'Password'
  , `client_id` VARCHAR(255) not null comment 'クライアントID'
  , `status` TINYINT unsigned default 1 not null comment 'ステータス:1:アクティブ
2:管理者による停止中
3:退会済み'
  , constraint `user_users_PKC` primary key (`user_id`)
) comment 'ユーザ:' ENGINE=InnoDB CHARACTER SET utf8mb4 ROW_FORMAT=DYNAMIC;

drop table if exists `user_physical_dates` cascade;

create table `user_physical_datas` (
  `data_id` INT unsigned not null auto_increment comment 'データID'
  , `user_id` INT unsigned not null comment 'ユーザID'
  , `weight` DOUBLE(5,2) unsigned not null comment '体重'
  , `fat_percentage` DOUBLE(5,2) unsigned not null comment '体脂肪'
  , `muscle_mass` DOUBLE(5,2) unsigned not null comment '筋肉量'
  , `water_content` DOUBLE(5,2) unsigned not null comment '体水分量'
  , `visceral_fat` DOUBLE(5,2) unsigned not null comment '内臓脂肪'
  , `basal_metabolic_rate` DOUBLE(5,2) unsigned not null comment '基礎代謝量'
  , `bmi` DOUBLE(5,2) unsigned not null comment 'BMI'
  , `created_at` DATETIME default CURRENT_TIMESTAMP not null comment '投稿日時'
  , `updated_at` DATETIME default CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP not null comment '更新日時'
  , `delete_flag` TINYINT unsigned default 0 not null comment '削除フラグ:0:削除していない
1:削除済み'
  , `monitoring_status` TINYINT unsigned default 0 not null comment '監視ステータス:0:未確認
1:OK
2:条件付OK
3:NG'
  , `checked_admin_user_id` SMALLINT unsigned default NULL comment '最終確認管理者ID'
  , `checked_at` DATETIME default NULL comment '最終確認日時'
  , constraint `user_physical_dates_PKC` primary key (`data_id`)
) comment 'ユーザ健康データ' ENGINE=InnoDB CHARACTER SET utf8mb4;
