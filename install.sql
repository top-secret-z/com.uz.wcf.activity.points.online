ALTER TABLE wcf1_user ADD uzWasOnline TINYINT(1) DEFAULT 0;
ALTER TABLE wcf1_user ADD INDEX uzWasOnline (uzWasOnline);
