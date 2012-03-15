GRANT ALL ON emdadb.* TO emdadb@localhost IDENTIFIED BY '<webdbPassword>'; 
FLUSH PRIVILEGES;
INSERT INTO users SET username = 'emdadmin', password = md5('<webPassword>'), fullname = 'EMDAdmin', type ='A';
