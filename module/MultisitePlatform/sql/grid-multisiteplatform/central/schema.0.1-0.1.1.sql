--------------------------------------------------------------------------------
-- table: domain                                                              --
--------------------------------------------------------------------------------

DROP VIEW IF EXISTS "_central"."fulldomain" CASCADE;

ALTER TABLE "_central"."domain"
      ALTER COLUMN "domain"
        SET DATA TYPE "_common"."domain";

ALTER TABLE "_central"."domain"
        ADD CONSTRAINT "domain_domain_overlaps"
    EXCLUDE USING btree ( "domain" "_common"."domain_ops" WITH OPERATOR( "_common".&& ) );

--------------------------------------------------------------------------------
-- view: fulldomain                                                           --
--------------------------------------------------------------------------------

CREATE OR REPLACE VIEW "_central"."fulldomain" AS
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
  FROM "_central"."site"
  JOIN "_central"."domain"
    ON "domain"."siteId"    = "site"."id"
  JOIN "_central"."subdomain"
    ON "subdomain"."siteId" = "site"."id";
