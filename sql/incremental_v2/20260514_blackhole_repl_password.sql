-- #1212 follow-up: greenfield form needs the operator to supply a
-- replication password too. The current pipeline reuses the master
-- mysql_server.passwd, which forces the replication user to share
-- that password — surprising in practice. Persist an encrypted
-- password (using the same Crypt mechanism mysql_server.passwd uses)
-- so the operator can pick a dedicated REPLICATION SLAVE user with
-- its own credentials.
ALTER TABLE blackhole_conversion
    ADD COLUMN replication_password VARCHAR(255) NOT NULL DEFAULT ''
        AFTER replication_user;
