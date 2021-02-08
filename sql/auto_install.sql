CREATE TABLE IF NOT EXISTS zoom_account_settings (
    id int NOT NULL PRIMARY KEY UNIQUE AUTO_INCREMENT,
    name varchar(255) ,
    api_key varchar(255),
    secret_key varchar(255),
    user_id varchar(128)
);