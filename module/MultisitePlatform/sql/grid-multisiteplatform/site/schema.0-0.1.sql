--------------------------------------------------------------------------------
-- set current schema at settings:site.createSchema.siteCreatorSiteSchema     --
--------------------------------------------------------------------------------

INSERT INTO "_central"."settings" ( "key", "value" )
                           VALUES ( 'site.createSchema.siteCreatorSiteSchema',
                                    CURRENT_SCHEMA );

--------------------------------------------------------------------------------
-- copy data into _central.user                                               --
--------------------------------------------------------------------------------

ALTER TABLE "_central"."user"
    DISABLE TRIGGER USER;

INSERT INTO "_central"."user" ( "id", "email" )
     SELECT "id",
            "email"
       FROM "user";

ALTER TABLE "_central"."user"
     ENABLE TRIGGER USER;

SELECT setval(
    '_central.user_id_seq',
    ( SELECT "last_value" + 1 FROM "user_id_seq" ),
    FALSE
);

--------------------------------------------------------------------------------
-- create site into _central.site                                             --
--------------------------------------------------------------------------------

ALTER TABLE "_central"."site"
    DISABLE TRIGGER USER;

INSERT INTO "_central"."site" ( "schema", "ownerId" )
     SELECT CURRENT_SCHEMA AS "schema",
            "id" AS "ownerId"
       FROM "user"
      WHERE "groupId" = 2
      LIMIT 1;

ALTER TABLE "_central"."site"
     ENABLE TRIGGER USER;

--------------------------------------------------------------------------------
-- copy data into _central.user_x_site                                        --
--------------------------------------------------------------------------------

ALTER TABLE "_central"."user_x_site"
    DISABLE TRIGGER USER;

INSERT INTO "_central"."user_x_site" ( "siteId", "userId", "displayName", "passwordHash", "state", "confirmed", "locale", "groupId", "original" )
     SELECT "site"."id" AS "siteId",
            "user"."id",
            "user"."displayName",
            "user"."passwordHash",
            "user"."state",
            "user"."confirmed",
            "user"."locale",
            "user"."groupId",
            TRUE AS "original"
       FROM "user",
            "_central"."site";

ALTER TABLE "_central"."user_x_site"
     ENABLE TRIGGER USER;

--------------------------------------------------------------------------------
-- copy data into _central.subdomain                                          --
--------------------------------------------------------------------------------

ALTER TABLE "_central"."subdomain"
    DISABLE TRIGGER USER;

INSERT INTO "_central"."subdomain" ( "siteId", "id", "subdomain", "locale" )
     SELECT "site"."id" AS "siteId",
            "subdomain"."id",
            "subdomain"."subdomain",
            "subdomain"."locale"
       FROM "subdomain",
            "_central"."site";

ALTER TABLE "_central"."subdomain"
     ENABLE TRIGGER USER;

--------------------------------------------------------------------------------
-- table: add foreign key: user.id -> _central.user.id                        --
--------------------------------------------------------------------------------

ALTER TABLE "user"
  ADD FOREIGN KEY ( "id" )
       REFERENCES "_central"."user" ( "id" )
        ON UPDATE CASCADE
        ON DELETE CASCADE;

--------------------------------------------------------------------------------
-- function: subdomain__delete_from_central()                                 --
--------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION "subdomain__delete_from_central"()
                   RETURNS TRIGGER
                  LANGUAGE plpgsql
                        AS $$
BEGIN

    DELETE FROM "_central"."subdomain"
          WHERE "id" = OLD."id"
            AND "siteId" = (
                    SELECT "id"
                      FROM "_central"."site"
                     WHERE "schema" = TG_TABLE_SCHEMA
                );

    RETURN OLD;

END $$;

--------------------------------------------------------------------------------
-- function: subdomain__insert_to_central()                                   --
--------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION "subdomain__insert_to_central"()
                   RETURNS TRIGGER
                  LANGUAGE plpgsql
                        AS $$
DECLARE
    "v_site_id" INTEGER;
BEGIN

    SELECT "id"
      INTO "v_site_id"
      FROM "_central"."site"
     WHERE "schema" = TG_TABLE_SCHEMA;

    IF NOT EXISTS (
        SELECT *
          FROM "_central"."subdomain"
         WHERE "siteId" = "v_site_id"
           AND "id" = NEW."id" ) THEN

        INSERT INTO "_central"."subdomain" ( "siteId", "id", "subdomain", "locale" )
             VALUES ( "v_site_id", NEW."id", NEW."subdomain", NEW."locale" );

     END IF;

     RETURN NEW;

END $$;

--------------------------------------------------------------------------------
-- function: subdomain__update_to_central()                                   --
--------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION "subdomain__update_to_central"()
                   RETURNS TRIGGER
                  LANGUAGE plpgsql
                        AS $$
BEGIN

    UPDATE "_central"."subdomain"
       SET "subdomain"  = NEW."subdomain",
           "locale"     = NEW."locale"
     WHERE "id" = NEW."id"
       AND "siteId" = (
               SELECT "id"
                 FROM "_central"."site"
                WHERE "schema" = TG_TABLE_SCHEMA
           )
       AND ( "subdomain" <> NEW."subdomain"
             OR "locale" <> NEW."locale" );

     RETURN NEW;

END $$;

--------------------------------------------------------------------------------
-- function: user__insert_data_to_central()                                   --
--------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION "user__insert_data_to_central"()
                   RETURNS TRIGGER
                  LANGUAGE plpgsql
                        AS $$
BEGIN

    IF NOT EXISTS (
        SELECT *
          FROM "_central"."user_x_site"
         WHERE "userId" = NEW."id"
           AND "siteId" = (
                   SELECT "id"
                     FROM "_central"."site"
                    WHERE "schema" = TG_TABLE_SCHEMA
               )
    ) THEN

        INSERT INTO "_central"."user_x_site" (
            "userId",
            "siteId",
            "displayName",
            "passwordHash",
            "state",
            "confirmed",
            "original",
            "groupId",
            "locale"
        ) VALUES (
            NEW."id",
            ( SELECT "id"
                FROM "_central"."site"
               WHERE "schema" = TG_TABLE_SCHEMA ),
            NEW."displayName",
            NEW."passwordHash",
            NEW."state",
            NEW."confirmed",
            TRUE,
            NEW."groupId",
            NEW."locale"
        );

    END IF;

    RETURN NEW;

END $$;

--------------------------------------------------------------------------------
-- function: user__insert_to_central()                                        --
--------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION "user__insert_to_central"()
                   RETURNS TRIGGER
                  LANGUAGE plpgsql
                        AS $$
BEGIN

    IF NOT EXISTS (
        SELECT *
          FROM "_central"."user"
         WHERE "email" = NEW."email"
    ) THEN

        INSERT INTO "_central"."user" ( "email" )
                               VALUES ( NEW."email" );

    END IF;

    NEW."id" = (
        SELECT "id"
          FROM "_central"."user"
         WHERE "email" = NEW."email"
    );

    RETURN NEW;

END $$;

--------------------------------------------------------------------------------
-- function: user__update_data_to_central()                                   --
--------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION "user__update_data_to_central"()
                   RETURNS TRIGGER
                  LANGUAGE plpgsql
                        AS $$
BEGIN

    UPDATE "_central"."user_x_site"
       SET "displayName"    = NEW."displayName",
           "passwordHash"   = NEW."passwordHash",
           "state"          = NEW."state",
           "confirmed"      = NEW."confirmed",
           "groupId"        = NEW."groupId",
           "locale"         = NEW."locale"
     WHERE "userId" = NEW."id"
       AND "siteId" = (
               SELECT "id"
                 FROM "_central"."site"
                WHERE "schema" = TG_TABLE_SCHEMA
           )
       AND ( "displayName"  <> NEW."displayName"
          OR "passwordHash" <> NEW."passwordHash"
          OR "state"        <> NEW."state"
          OR "confirmed"    <> NEW."confirmed"
          OR "groupId"      <> NEW."groupId"
          OR "locale"       <> NEW."locale" );

    RETURN NEW;

END $$;

--------------------------------------------------------------------------------
-- function: user__update_email_to_central()                                  --
--------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION "user__update_email_to_central"()
                   RETURNS TRIGGER
                  LANGUAGE plpgsql
                        AS $$
BEGIN

    UPDATE "_central"."user"
       SET "email"  = NEW."email"
     WHERE "id"     = NEW."id"
       AND "email" <> NEW."email";

    RETURN NEW;

END $$;

--------------------------------------------------------------------------------
-- trigger: user.1000__insert_data_to_central                                 --
--------------------------------------------------------------------------------

CREATE TRIGGER "1000__insert_data_to_central"
         AFTER INSERT
            ON "user"
           FOR EACH ROW
       EXECUTE PROCEDURE "user__insert_data_to_central"();

--------------------------------------------------------------------------------
-- trigger: user.1000__insert_to_central                                      --
--------------------------------------------------------------------------------

CREATE TRIGGER "1000__insert_to_central"
        BEFORE INSERT
            ON "user"
           FOR EACH ROW
       EXECUTE PROCEDURE "user__insert_to_central"();

--------------------------------------------------------------------------------
-- trigger: user.1000__update_data_to_central                                 --
--------------------------------------------------------------------------------

CREATE TRIGGER "1000__update_data_to_central"
         AFTER UPDATE OF "displayName",
                         "passwordHash",
                         "groupId",
                         "state",
                         "confirmed",
                         "locale"
            ON "user"
           FOR EACH ROW
       EXECUTE PROCEDURE "user__update_data_to_central"();

--------------------------------------------------------------------------------
-- trigger: user.1000__update_email_to_central                                --
--------------------------------------------------------------------------------

CREATE TRIGGER "1000__update_email_to_central"
         AFTER UPDATE OF "email"
            ON "user"
           FOR EACH ROW
       EXECUTE PROCEDURE "user__update_email_to_central"();

--------------------------------------------------------------------------------
-- trigger: subdomain.1000__delete_from_central                               --
--------------------------------------------------------------------------------

CREATE TRIGGER "1000__delete_from_central"
         AFTER DELETE
            ON "subdomain"
           FOR EACH ROW
       EXECUTE PROCEDURE "subdomain__delete_from_central"();

--------------------------------------------------------------------------------
-- trigger: subdomain.1000__insert_to_central                                 --
--------------------------------------------------------------------------------

CREATE TRIGGER "1000__insert_to_central"
         AFTER INSERT
            ON "subdomain"
           FOR EACH ROW
       EXECUTE PROCEDURE "subdomain__insert_to_central"();

--------------------------------------------------------------------------------
-- trigger: subdomain.1000__update_to_central                                 --
--------------------------------------------------------------------------------

CREATE TRIGGER "1000__update_to_central"
         AFTER UPDATE OF "subdomain",
                         "locale"
            ON "subdomain"
           FOR EACH ROW
       EXECUTE PROCEDURE "subdomain__update_to_central"();
