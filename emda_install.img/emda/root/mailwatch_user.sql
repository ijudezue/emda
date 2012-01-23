GRANT ALL ON emdadb.* TO emdadb@localhost IDENTIFIED BY '<password>'; 
FLUSH PRIVILEGES;
INSERT INTO users SET username = 'emdadmin', password = md5('<password>'), fullname = 'EMDAdmin', type ='A';
