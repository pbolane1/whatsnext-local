TRUNCATE TABLE users;
TRUNCATE TABLE user_conditions;
TRUNCATE TABLE user_contacts;
TRUNCATE TABLE user_contract_dates;
TRUNCATE TABLE user_data;
TRUNCATE TABLE user_links;
DELETE FROM timeline_items WHERE user_id>0;
DELETE FROM widgets WHERE user_id>0;
DELETE FROM activity_log WHERE user_id>0;
