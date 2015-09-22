
CREATE SEQUENCE sqliimport_item_s
    START 1
    INCREMENT 1
    MAXVALUE 9223372036854775807
    MINVALUE 1
    CACHE 1;
    
CREATE SEQUENCE sqliimport_scheduled_s
    START 1
    INCREMENT 1
    MAXVALUE 9223372036854775807
    MINVALUE 1
    CACHE 1;
CREATE TABLE sqliimport_scheduled (
  id integer DEFAULT nextval('sqliimport_scheduled_s'::text) NOT NULL,
  handler VARCHAR(50) DEFAULT NULL,
  label VARCHAR(255) DEFAULT NULL,
  options_serialized TEXT,
  frequency VARCHAR(30) NOT NULL,
  next INTEGER NOT NULL DEFAULT 0,
  user_id INTEGER DEFAULT 0,
  requested_time INTEGER DEFAULT 0,
  is_active INTEGER DEFAULT 0,
  manual_frequency INTEGER DEFAULT 0
);

ALTER TABLE ONLY sqliimport_scheduled ADD CONSTRAINT sqliimport_scheduled_pkey PRIMARY KEY (id);

CREATE TABLE sqliimport_item (
  id INTEGER DEFAULT nextval('sqliimport_item_s'::text) NOT NULL,
  handler VARCHAR(50) DEFAULT NULL,
  options_serialized TEXT,
  user_id INTEGER DEFAULT 0,
  requested_time INTEGER DEFAULT 0,
  status INTEGER DEFAULT 0,
  percentage_int INTEGER DEFAULT 0,
  type INTEGER DEFAULT 1,
  progression_notes TEXT,
  process_time INTEGER DEFAULT 0,
  scheduled_id INTEGER DEFAULT 0
);

CREATE INDEX sqliimport_item_handler ON sqliimport_item USING btree (handler);
CREATE INDEX sqliimport_item_user_id ON sqliimport_item USING btree (user_id);
CREATE INDEX sqliimport_item_status ON sqliimport_item USING btree (status);
CREATE INDEX sqliimport_item_scheduled_id ON sqliimport_item USING btree (scheduled_id);
ALTER TABLE ONLY sqliimport_scheduled ADD CONSTRAINT sqliimport_scheduled_pkey PRIMARY KEY (id);


