--------------------------------------------------------------------------------
-- domain: domain                                                             --
--------------------------------------------------------------------------------

CREATE DOMAIN "_common"."domain" AS CHARACTER VARYING
        CHECK ( VALUE ~* '^([[:alnum:]-]+\.)+[a-z]{2,}$' );

--------------------------------------------------------------------------------
-- function: domains_overlaps                                                 --
--------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION "_common"."domains_overlaps"( "p_domain1"    "_common"."domain",
                                                         "p_domain2"    "_common"."domain" )
                   RETURNS BOOLEAN
                  LANGUAGE plpgsql
                           IMMUTABLE
                        AS $$
DECLARE
    "v_tmp"             CHARACTER VARYING;
    "v_domain1"         CHARACTER VARYING;
    "v_domain2"         CHARACTER VARYING;
    "v_tmp_len"         INTEGER;
    "v_domain1_len"     INTEGER;
    "v_domain2_len"     INTEGER;
BEGIN

    "v_domain1" = LOWER( TEXT( "p_domain1" ) );
    "v_domain2" = LOWER( TEXT( "p_domain2" ) );

    IF "v_domain1" = "v_domain2" THEN
        RETURN TRUE;
    END IF;

    "v_domain1_len" = CHARACTER_LENGTH( "v_domain1" );
    "v_domain2_len" = CHARACTER_LENGTH( "v_domain2" );

    IF "v_domain1_len" = "v_domain2_len" THEN
        RETURN FALSE;
    END IF;

    IF "v_domain1_len" > "v_domain2_len" THEN
        RETURN ( '.' || "v_domain2" ) = SUBSTRING(
            "v_domain1"
            FROM "v_domain1_len" - "v_domain2_len"
        );
    ELSE
        RETURN ( '.' || "v_domain1" ) = SUBSTRING(
            "v_domain2"
            FROM "v_domain2_len" - "v_domain1_len"
        );
    END IF;

END $$;

--------------------------------------------------------------------------------
-- function: domains_overlaps_cmp                                             --
--------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION "_common"."domains_overlaps_cmp"( "p_domain1"    "_common"."domain",
                                                             "p_domain2"    "_common"."domain" )
                   RETURNS INTEGER
                  LANGUAGE plpgsql
                           IMMUTABLE
                        AS $$
DECLARE
    "v_tmp"             CHARACTER VARYING;
    "v_domain1"         CHARACTER VARYING;
    "v_domain2"         CHARACTER VARYING;
    "v_tmp_len"         INTEGER;
    "v_domain1_len"     INTEGER;
    "v_domain2_len"     INTEGER;
BEGIN

    "v_domain1" = LOWER( TEXT( "p_domain1" ) );
    "v_domain2" = LOWER( TEXT( "p_domain2" ) );

    IF "v_domain1" = "v_domain2" THEN
        RETURN 0;
    END IF;

    "v_domain1_len" = CHARACTER_LENGTH( "v_domain1" );
    "v_domain2_len" = CHARACTER_LENGTH( "v_domain2" );

    IF "v_domain1_len" > "v_domain2_len" THEN
        IF ( '.' || "v_domain2" ) = SUBSTRING( "v_domain1" FROM "v_domain1_len" - "v_domain2_len" ) THEN
            RETURN 0;
        END IF;
    END IF;

    IF "v_domain1_len" < "v_domain2_len" THEN
        IF ( '.' || "v_domain1" ) = SUBSTRING( "v_domain2" FROM "v_domain2_len" - "v_domain1_len" ) THEN
            RETURN 0;
        END IF;
    END IF;

    IF "v_domain1" < "v_domain2" THEN
        RETURN 1;
    END IF;

    IF "v_domain1" > "v_domain2" THEN
        RETURN -1;
    END IF;

    RETURN 0;

END $$;

--------------------------------------------------------------------------------
-- operator: &&                                                               --
--------------------------------------------------------------------------------

CREATE OPERATOR "_common".&& (
    PROCEDURE   = "_common"."domains_overlaps",
    LEFTARG     = "_common"."domain",
    RIGHTARG    = "_common"."domain",
    COMMUTATOR  = OPERATOR( "_common".&& )
);

--------------------------------------------------------------------------------
-- operator class: domain_ops                                                 --
--------------------------------------------------------------------------------

CREATE OPERATOR CLASS "_common"."domain_ops"
            FOR TYPE "_common"."domain"
          USING btree
             AS OPERATOR 1  <   ( TEXT, TEXT ),
                OPERATOR 2  <=  ( TEXT, TEXT ),
                OPERATOR 3  "_common".&&,
                OPERATOR 4  >=  ( TEXT, TEXT ),
                OPERATOR 5  >   ( TEXT, TEXT ),
                FUNCTION 1  "_common"."domains_overlaps_cmp"("_common"."domain", "_common"."domain");
