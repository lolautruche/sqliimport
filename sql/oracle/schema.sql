CREATE TABLE sqliimport_scheduled (
  id int NOT NULL,
  handler varchar(50),
  label varchar(255),
  options_serialized clob,
  frequency varchar(30) NOT NULL,
  next int DEFAULT 0,
  user_id int,
  requested_time int,
  is_active smallint DEFAULT 0,
  manual_frequency int DEFAULT 0,
  PRIMARY KEY (id)
);

CREATE TABLE sqliimport_item (
  id int NOT NULL,
  handler varchar(50),
  options_serialized clob,
  user_id int,
  requested_time int DEFAULT 0,
  STATUS smallint DEFAULT 0,
  percentage_int smallint DEFAULT 0,
  type smallint DEFAULT 1,
  progression_notes clob,
  process_time int DEFAULT 0,
  scheduled_id int,
  PRIMARY KEY (id)
);

CREATE SEQUENCE se_sqliimport_item;
CREATE SEQUENCE se_sqliimport_scheduled;

CREATE OR REPLACE TRIGGER tr_sqliimport_scheduled_id
BEFORE INSERT ON sqliimport_scheduled
FOR EACH ROW
WHEN ( new.ID IS NULL )
BEGIN
    SELECT se_sqliimport_scheduled.NEXTVAL
    INTO :new.ID
    FROM dual;
END;
/

CREATE OR REPLACE TRIGGER tr_sqliimport_item_id
BEFORE INSERT ON sqliimport_item
FOR EACH ROW
WHEN ( new.ID IS NULL )
BEGIN
    SELECT se_sqliimport_item.NEXTVAL
    INTO :new.ID
    FROM dual;
END;
/