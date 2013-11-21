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

    -- insert developer(s)

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
          SELECT "userId",
                 NEW."id"       AS "siteId",
                 "displayName",
                 "passwordHash",
                 'active'       AS "state",
                 TRUE           AS "confirmed",
                 FALSE          AS "original",
                 1              AS "groupId", -- developer
                 "locale"
            FROM "_central"."user_x_site"
           WHERE "siteId"   = "v_site_id"
             AND "groupId"  = 1;

    -- insert site-owner

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
          SELECT "userId",
                 NEW."id"       AS "siteId",
                 "displayName",
                 "passwordHash",
                 'active'       AS "state",
                 TRUE           AS "confirmed",
                 TRUE           AS "original",
                 2              AS "groupId", -- site-owner
                 "locale"
            FROM "_central"."user_x_site"
           WHERE "siteId"   = "v_site_id"
             AND "userId"   = NEW."ownerId"
             AND "groupId" <> 1
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

    EXECUTE format(
                'SELECT COUNT( * ) = 0
                   FROM %I."user"
                  WHERE "id" = $1',
                "v_schema"
            )
       INTO "v_not_exists"
      USING NEW."userId";

    IF "v_not_exists" THEN

        EXECUTE format(
                    'INSERT INTO %I."user" ( "id", "email", "displayName",
                                             "passwordHash", "state",
                                             "confirmed", "groupId", "locale" )
                          VALUES ( $1, ( SELECT "user"."email"
                                           FROM "_central"."user"
                                          WHERE "user"."id" = $1 ),
                                   $2, $3, $4, $5, $6, $7 )',
                    "v_schema"
                )
          USING NEW."userId",
                NEW."displayName",
                NEW."passwordHash",
                NEW."state",
                NEW."confirmed",
                NEW."groupId",
                NEW."locale";

    ELSE

        EXECUTE format(
                    'UPDATE %I."user"
                        SET "displayName"   = $2,
                            "passwordHash"  = $3,
                            "state"         = $4,
                            "confirmed"     = $5,
                            "groupId"       = $6,
                            "locale"        = $7
                      WHERE "id" = $1',
                    "v_schema"
                )
          USING NEW."userId",
                NEW."displayName",
                NEW."passwordHash",
                NEW."state",
                NEW."confirmed",
                NEW."groupId",
                NEW."locale";

    END IF;

    RETURN NEW;

END $$;

--------------------------------------------------------------------------------
-- trigger: user_x_site.1000__user__update_unified_users                      --
--------------------------------------------------------------------------------

CREATE TRIGGER "1000__user__update_unified_users"
         AFTER UPDATE
            ON "_central"."user_x_site"
           FOR EACH ROW
       EXECUTE PROCEDURE "_central"."user__update_unified_users"();

--------------------------------------------------------------------------------
-- trigger: user_x_site.2000__user__user__update_data_to_site                 --
--------------------------------------------------------------------------------

CREATE TRIGGER "2000__user__user__update_data_to_site"
         AFTER UPDATE
            ON "_central"."user_x_site"
           FOR EACH ROW
       EXECUTE PROCEDURE "_central"."user__update_data_to_site"();
