--------------------------------------------------------------------------------
-- table: user                                                                --
--------------------------------------------------------------------------------

CREATE TABLE "_central"."user"
(
    "id"    SERIAL              NOT NULL,
    "email" CHARACTER VARYING   NOT NULL,

    PRIMARY KEY ( "id" ),
    UNIQUE ( "email" )
);

--------------------------------------------------------------------------------
-- table: site                                                                --
--------------------------------------------------------------------------------

CREATE TABLE "_central"."site"
(
    "id"        SERIAL                      NOT NULL,
    "schema"    CHARACTER VARYING           NOT NULL,
    "ownerId"   INTEGER                     NOT NULL,
    "created"   TIMESTAMP WITH TIME ZONE    NOT NULL    DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY ( "id" ),
    UNIQUE ( "schema" ),
    FOREIGN KEY ( "ownerId" )
     REFERENCES "_central"."user" ( "id" )
      ON UPDATE RESTRICT
      ON DELETE RESTRICT
);

--------------------------------------------------------------------------------
-- table: user_unified                                                        --
--------------------------------------------------------------------------------

CREATE TABLE "_central"."user_unified"
(
    "siteId"    INTEGER     NOT NULL,
    "userId"    INTEGER     NOT NULL,

    PRIMARY KEY ( "siteId", "userId" ),
    FOREIGN KEY ( "siteId" )
     REFERENCES "_central"."site" ( id )
      ON UPDATE CASCADE
      ON DELETE CASCADE,
    FOREIGN KEY ( "userId" )
     REFERENCES "_central"."user" ( id )
      ON UPDATE CASCADE
      ON DELETE CASCADE
);

--------------------------------------------------------------------------------
-- table: user_x_site                                                         --
--------------------------------------------------------------------------------

CREATE TABLE "_central"."user_x_site"
(
    "userId"        INTEGER                 NOT NULL,
    "siteId"        INTEGER                 NOT NULL,
    "displayName"   CHARACTER VARYING       NOT NULL,
    "passwordHash"  CHARACTER VARYING       NOT NULL,
    "state"         "_common"."user_state"  NOT NULL    DEFAULT 'active'::"_common"."user_state",
    "confirmed"     BOOLEAN                 NOT NULL    DEFAULT FALSE,
    "original"      BOOLEAN                 NOT NULL    DEFAULT TRUE,
    "locale"        CHARACTER VARYING       NOT NULL    DEFAULT '',
    "groupId"       INTEGER,

    PRIMARY KEY ( "userId", "siteId" ),
    UNIQUE ( "siteId", "displayName" ),
    FOREIGN KEY ( "siteId" )
     REFERENCES "_central"."site" ( "id" )
      ON UPDATE CASCADE
      ON DELETE CASCADE,
    FOREIGN KEY ( "userId" )
     REFERENCES "_central"."user" ( "id" )
      ON UPDATE CASCADE
      ON DELETE CASCADE
);

--------------------------------------------------------------------------------
-- table: unified_site_group                                                  --
--------------------------------------------------------------------------------

CREATE TABLE "_central"."unified_site_group"
(
    "id"    SERIAL              NOT NULL,
    "name"  CHARACTER VARYING   NOT NULL,

    PRIMARY KEY ( "id" ),
    UNIQUE ( "name" )
);

--------------------------------------------------------------------------------
-- table: unified_site                                                        --
--------------------------------------------------------------------------------

CREATE TABLE "_central"."unified_site"
(
    "unifiedGroupId"    INTEGER     NOT NULL,
    "siteId"            INTEGER     NOT NULL,

    PRIMARY KEY ( "unifiedGroupId", "siteId" ),
    FOREIGN KEY ( "siteId" )
     REFERENCES "_central"."site" ( "id" )
      ON UPDATE CASCADE
      ON DELETE CASCADE,
    FOREIGN KEY ( "unifiedGroupId" )
     REFERENCES "_central"."unified_site_group" ( "id" )
      ON UPDATE CASCADE
      ON DELETE CASCADE
);

--------------------------------------------------------------------------------
-- table: subdomain                                                           --
--------------------------------------------------------------------------------

CREATE TABLE "_central"."subdomain"
(
    "siteId"    INTEGER             NOT NULL,
    "id"        INTEGER             NOT NULL,
    "subdomain" CHARACTER VARYING   NOT NULL,
    "locale"    CHARACTER VARYING   NOT NULL    DEFAULT 'en',

    PRIMARY KEY ( "siteId", "id" ),
    UNIQUE ( "subdomain", "siteId" ),
    FOREIGN KEY ( "siteId" )
     REFERENCES "_central"."site" ( "id" )
      ON UPDATE CASCADE
      ON DELETE CASCADE
);

--------------------------------------------------------------------------------
-- table: domain                                                              --
--------------------------------------------------------------------------------

CREATE TABLE "_central"."domain"
(
    "id"        SERIAL              NOT NULL,
    "domain"    CHARACTER VARYING   NOT NULL,
    "siteId"    INTEGER             NOT NULL,

    PRIMARY KEY ( "id" ),
    UNIQUE ( "domain" ),
    FOREIGN KEY ( "siteId" )
     REFERENCES "_central"."site" ( "id" )
      ON UPDATE RESTRICT
      ON DELETE RESTRICT
);

--------------------------------------------------------------------------------
-- view: fulldomain                                                           --
--------------------------------------------------------------------------------

CREATE OR REPLACE VIEW "fulldomain" AS
SELECT "site"."id"      AS "siteId",
       "site"."schema",
       "site"."ownerId",
       "site"."created",
       "domain"."id"    AS "domainId",
       "domain"."domain",
       "subdomain"."id" AS "subdomainId",
       "subdomain"."subdomain",
       btrim( COALESCE( "subdomain"."subdomain", '' )
           || '.' || "domain"."domain", '.' ) AS "fulldomain"
  FROM "site"
  JOIN "domain"
    ON "domain"."siteId"    = "site"."id"
  JOIN "subdomain"
    ON "subdomain"."siteId" = "site"."id";

--------------------------------------------------------------------------------
-- function: site__create_schema                                              --
--------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION "_central"."site__create_schema"()
                   RETURNS TRIGGER
                  LANGUAGE plpgsql
                        AS $$
BEGIN

    IF EXISTS( SELECT SCHEMA_NAME
                 FROM INFORMATION_SCHEMA.SCHEMATA
                WHERE SCHEMA_NAME = NEW."schema" ) THEN

        RETURN NEW;

    END IF;

    PERFORM "_common"."copy_schema"(
        CAST(
            ( SELECT "value"
                FROM "_central"."settings"
               WHERE "key" = 'site.createSchema.referenceSchema'
               LIMIT 1 )
            AS CHARACTER VARYING
        ),
        NEW."schema"
    );

    RETURN NEW;

END $$;

--------------------------------------------------------------------------------
-- function: site__delete_schema                                              --
--------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION "_central"."site__delete_schema"()
                   RETURNS TRIGGER
                  LANGUAGE plpgsql
                        AS $$
BEGIN

    EXECUTE format(
        'DROP SCHEMA IF EXISTS %I CASCADE',
        OLD."schema"
    );

    RETURN NEW;

END $$;

--------------------------------------------------------------------------------
-- function: site__init_schema                                                --
--------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION "_central"."site__init_schema"()
                   RETURNS TRIGGER
                  LANGUAGE plpgsql
                        AS $$
DECLARE
    "v_site_id" INTEGER;
BEGIN

    SELECT "id"
      INTO "v_site_id"
      FROM "_central"."site"
     WHERE "schema" = (
                SELECT "value"
                  FROM "_central"."settings"
                 WHERE "key" = 'site.createSchema.siteCreatorSiteSchema'
                 LIMIT 1
             );

    INSERT INTO "_central"."user_x_site"
                ( "userId",
                  "siteId",
                  "displayName",
                  "passwordHash",
                  "state",
                  "confirmed",
                  "original",
                  "groupId",
                  "locale" )
          SELECT NEW."ownerId"  AS "userId",
                 NEW."id"       AS "siteId",
                 "displayName",
                 "passwordHash",
                 'active'       AS "state",
                 TRUE           AS "confirmed",
                 TRUE           AS "original",
                 2              AS "groupId", -- site-owner
                 "locale"
            FROM "_central"."user_x_site"
           WHERE "siteId" = "v_site_id"
             AND "userId" = NEW."ownerId"
           LIMIT 1;

    -- every auto-generated user will be unified

    INSERT INTO "_central"."user_unified"
         SELECT "siteId",
                "userId"
           FROM "_central"."user_x_site"
          WHERE "siteId" = NEW."id";

    RETURN NEW;

END $$;

--------------------------------------------------------------------------------
-- function: subdomain__delete_from_site                                      --
--------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION "_central"."subdomain__delete_from_site"()
                   RETURNS TRIGGER
                  LANGUAGE plpgsql
                        AS $$
DECLARE
    "v_schema"  CHARACTER VARYING;
BEGIN

    SELECT "schema"
      INTO "v_schema"
      FROM "_central"."site"
     WHERE "site"."id" = OLD."siteId"
     LIMIT 1;

    IF "v_schema" IS NOT NULL THEN

        EXECUTE format(
            'DELETE FROM %I."subdomain" WHERE "id" = %L',
            "v_schema",
            OLD."id"
        );

    END IF;

    RETURN OLD;

END $$;

--------------------------------------------------------------------------------
-- function: subdomain__insert_get_id                                         --
--------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION "_central"."subdomain__insert_get_id"()
                   RETURNS TRIGGER
                  LANGUAGE plpgsql
                        AS $$
DECLARE
    "v_schema"  CHARACTER VARYING;
BEGIN

    IF NEW."id" IS NULL THEN

        SELECT "schema"
          INTO "v_schema"
          FROM "_central"."site"
         WHERE "site"."id" = NEW."siteId"
         LIMIT 1;

        NEW."id" = ( SELECT nextval( "v_schema" || '.subdomain_id_seq' ) );

    END IF;

    RETURN NEW;

END $$;

--------------------------------------------------------------------------------
-- function: subdomain__insert_to_site                                        --
--------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION "_central"."subdomain__insert_to_site"()
                   RETURNS TRIGGER
                  LANGUAGE plpgsql
                        AS $$
DECLARE
    "v_schema"      CHARACTER VARYING;
    "v_not_exists"  BOOLEAN;
BEGIN

    SELECT "schema"
      INTO "v_schema"
      FROM "_central"."site"
     WHERE "site"."id" = NEW."siteId"
     LIMIT 1;

    EXECUTE format(
                'SELECT COUNT( * ) < 1 FROM %I."subdomain" WHERE id = %L',
                "v_schema",
                NEW."id"
            )
       INTO "v_not_exists";

    IF "v_not_exists" THEN

        EXECUTE format(
            'INSERT INTO %I."subdomain"( "id", "subdomain", "locale" )'
                ' VALUES ( %L, %L, %L )',
            "v_schema",
            NEW."id",
            NEW."subdomain",
            NEW."locale"
        );

    END IF;

    RETURN NEW;

END $$;

--------------------------------------------------------------------------------
-- function: subdomain__update_to_site                                        --
--------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION "_central"."subdomain__update_to_site"()
                   RETURNS TRIGGER
                  LANGUAGE plpgsql
                        AS $$
DECLARE
    "v_schema"  CHARACTER VARYING;
BEGIN

    SELECT "schema"
      INTO "v_schema"
      FROM "_central"."site"
     WHERE "site"."id" = NEW."siteId"
     LIMIT 1;

    EXECUTE format(
        'UPDATE %I."subdomain"'
          ' SET "subdomain" = %3$L,'
              ' "locale"    = %4$L'
        ' WHERE "id"        = %2$L'
          ' AND ( "subdomain"   <> %3$L'
             ' OR "locale"      <> %4$L )',
        "v_schema",
        NEW."id",
        NEW."subdomain",
        NEW."locale"
    );

    RETURN NEW;
END $$;

--------------------------------------------------------------------------------
-- function: user__delete_from_site                                           --
--------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION "_central"."user__delete_from_site"()
                   RETURNS TRIGGER
                  LANGUAGE plpgsql
                        AS $$
DECLARE
    "v_schema"  CHARACTER VARYING;
BEGIN

    SELECT "schema"
      INTO "v_schema"
      FROM "_central"."site"
     WHERE "site"."id" = OLD."siteId";

    IF "v_schema" IS NOT NULL THEN

        EXECUTE 'SET LOCAL search_path TO '
             || QUOTE_IDENT( "v_schema" )
             || ', "_common"';

        DELETE FROM "user"
              WHERE "id" = OLD."userId";

    END IF;

    RETURN OLD;

END $$;

--------------------------------------------------------------------------------
-- function: user__insert_data_to_site                                        --
--------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION "_central"."user__insert_data_to_site"()
                   RETURNS TRIGGER
                  LANGUAGE plpgsql
                        AS $$
DECLARE
    "v_schema"      CHARACTER VARYING;
    "v_not_exists"  BOOLEAN;
BEGIN

    SELECT "schema"
      INTO "v_schema"
      FROM "_central"."site"
     WHERE "site"."id" = NEW."siteId";

    EXECUTE 'SET LOCAL search_path TO '
         || QUOTE_IDENT( "v_schema" )
         || ', "_common"';

    SELECT COUNT( * ) < 1
      INTO "v_not_exists"
      FROM "user"
     WHERE "id" = NEW."userId";

    IF "v_not_exists" THEN

        INSERT INTO "user" (
            "id",
            "email",
            "displayName",
            "passwordHash",
            "state",
            "confirmed",
            "groupId",
            "locale"
        ) VALUES (
            NEW."userId",
            ( SELECT "user"."email"
                FROM "_central"."user"
               WHERE "user"."id" = NEW."userId" ),
            NEW."displayName",
            NEW."passwordHash",
            NEW."state",
            NEW."confirmed",
            NEW."groupId",
            NEW."locale"
        );

    END IF;

    RETURN NEW;

END $$;

--------------------------------------------------------------------------------
-- function: user__update_data_to_site                                        --
--------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION "_central"."user__update_data_to_site"()
                   RETURNS TRIGGER
                  LANGUAGE plpgsql
                        AS $$
DECLARE
    "v_schema" CHARACTER VARYING;
BEGIN

    SELECT "schema"
      INTO "v_schema"
      FROM "_central"."site"
     WHERE "site"."id" = NEW."siteId";

    EXECUTE 'SET LOCAL search_path TO '
         || QUOTE_IDENT( "v_schema" )
         || ', "_common"';

    UPDATE "user"
       SET "displayName"    =  NEW."displayName",
           "passwordHash"   =  NEW."passwordHash",
           "state"          =  NEW."state",
           "confirmed"      =  NEW."confirmed",
           "groupId"        =  NEW."groupId",
           "locale"         =  NEW."locale"
     WHERE "id"             =  NEW."userId"
       AND ( "displayName"  <> NEW."displayName"
          OR "passwordHash" <> NEW."passwordHash"
          OR "state"        <> NEW."state"
          OR "confirmed"    <> NEW."confirmed"
          OR "groupId"      <> NEW."groupId"
          OR "locale"       <> NEW."locale" );

    RETURN NEW;

END $$;

--------------------------------------------------------------------------------
-- function: user__update_email_to_sites                                      --
--------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION "_central"."user__update_email_to_sites"()
                   RETURNS TRIGGER
                  LANGUAGE plpgsql
                        AS $$
DECLARE
    "v_row" RECORD;
BEGIN

    FOR "v_row"
     IN SELECT "schema"
          FROM "_central"."site"
          JOIN "_central"."user_x_site"
            ON "user_x_site"."siteId" = "site"."id"
           AND "user_x_site"."userId" = NEW."id"
    LOOP

        EXECUTE format(
            'UPDATE %I."user"'
              ' SET "email"  =  %3$L'
            ' WHERE "id"     =  %2$L'
              ' AND "email"  <> %3$L',
            "v_row"."schema",
            NEW."id",
            NEW."email"
        );

    END LOOP;

    RETURN NEW;

END $$;

--------------------------------------------------------------------------------
-- function: user__update_unified_users                                       --
--------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION "_central"."user__update_unified_users"()
                   RETURNS TRIGGER
                  LANGUAGE plpgsql
                        AS $$
BEGIN

    UPDATE "_central"."user_x_site"
       SET "displayName"    = NEW."displayName",
           "passwordHash"   = NEW."passwordHash",
           "confirmed"      = NEW."confirmed",
           "locale"         = NEW."locale"
     WHERE "userId" = NEW."userId"
       AND "siteId" IN (
            SELECT "user_unified"."siteId"
              FROM "_central"."user_unified"
             WHERE "user_unified"."userId" =  NEW."userId"
               AND "user_unified"."siteId" <> NEW."siteId"
           )
       AND ( "displayName"   <> NEW."displayName"
          OR "passwordHash"  <> NEW."passwordHash"
          OR "confirmed"     <> NEW."confirmed"
          OR "locale"        <> NEW."locale" );

    RETURN NEW;

END $$;

--------------------------------------------------------------------------------
-- trigger: user.1000__update_email_to_sites                                  --
--------------------------------------------------------------------------------

CREATE TRIGGER "1000__update_email_to_sites"
         AFTER UPDATE OF "email"
            ON "_central"."user"
           FOR EACH ROW
       EXECUTE PROCEDURE "_central"."user__update_email_to_sites"();

--------------------------------------------------------------------------------
-- trigger: site.1000__create_schema                                          --
--------------------------------------------------------------------------------

CREATE TRIGGER "1000__create_schema"
         AFTER INSERT
            ON "_central"."site"
           FOR EACH ROW
       EXECUTE PROCEDURE "_central"."site__create_schema"();

--------------------------------------------------------------------------------
-- trigger: site.2000__init_schema                                            --
--------------------------------------------------------------------------------

CREATE TRIGGER "2000__init_schema"
         AFTER INSERT
            ON "_central"."site"
           FOR EACH ROW
       EXECUTE PROCEDURE "_central"."site__init_schema"();

--------------------------------------------------------------------------------
-- trigger: site.1000__delete_schema                                          --
--------------------------------------------------------------------------------

CREATE TRIGGER "1000__delete_schema"
         AFTER DELETE
            ON "_central"."site"
           FOR EACH ROW
       EXECUTE PROCEDURE "_central"."site__delete_schema"();

--------------------------------------------------------------------------------
-- trigger: user_x_site.1000__insert_data_to_site                             --
--------------------------------------------------------------------------------

CREATE TRIGGER "1000__insert_data_to_site"
         AFTER INSERT
            ON "_central"."user_x_site"
           FOR EACH ROW
       EXECUTE PROCEDURE "_central"."user__insert_data_to_site"();

--------------------------------------------------------------------------------
-- trigger: user_x_site.1000__delete_from_site                                --
--------------------------------------------------------------------------------

CREATE TRIGGER "1000__delete_from_site"
         AFTER DELETE
            ON "_central"."user_x_site"
           FOR EACH ROW
       EXECUTE PROCEDURE "_central"."user__delete_from_site"();

--------------------------------------------------------------------------------
-- trigger: subdomain.1000__insert_get_id                                     --
--------------------------------------------------------------------------------

CREATE TRIGGER "1000__insert_get_id"
        BEFORE INSERT
            ON "_central"."subdomain"
           FOR EACH ROW
       EXECUTE PROCEDURE "_central"."subdomain__insert_get_id"();

--------------------------------------------------------------------------------
-- trigger: subdomain.1000__insert_to_site                                    --
--------------------------------------------------------------------------------

CREATE TRIGGER "1000__insert_to_site"
         AFTER INSERT
            ON "_central"."subdomain"
           FOR EACH ROW
       EXECUTE PROCEDURE "_central"."subdomain__insert_to_site"();

--------------------------------------------------------------------------------
-- trigger: subdomain.1000__update_to_site                                    --
--------------------------------------------------------------------------------

CREATE TRIGGER "1000__update_to_site"
         AFTER UPDATE OF "subdomain",
                         "locale"
            ON "_central"."subdomain"
           FOR EACH ROW
       EXECUTE PROCEDURE "_central"."subdomain__update_to_site"();

--------------------------------------------------------------------------------
-- trigger: subdomain.1000__delete_from_site                                  --
--------------------------------------------------------------------------------

CREATE TRIGGER "1000__delete_from_site"
         AFTER DELETE
            ON "_central"."subdomain"
           FOR EACH ROW
       EXECUTE PROCEDURE "_central"."subdomain__delete_from_site"();
