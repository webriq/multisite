-- insert default values for table: module

INSERT INTO "module" ( "module", "enabled" )
     VALUES ( 'Grid\DomainManager', TRUE );

-- insert default values for table: user_right

DO LANGUAGE plpgsql $$
BEGIN

    IF NOT EXISTS ( SELECT *
                      FROM "user_right"
                     WHERE "group"      = 'user.extra'
                       AND "resource"   = 'admin'
                       AND "privilege"  = 'ui' ) THEN

        INSERT INTO "user_right" ( "label", "group", "resource", "privilege", "optional" )
             VALUES ( NULL, 'user.extra', 'admin', 'ui', FALSE );

    END IF;

END $$;

INSERT INTO "user_right" ( "label", "group", "resource", "privilege", "optional", "module" )
     VALUES ( NULL, 'user.extra', 'central.site', 'view', TRUE, 'Grid\MultisiteCentral' ),
            ( NULL, 'central.site', 'central.site', 'create', TRUE, 'Grid\MultisiteCentral' );
