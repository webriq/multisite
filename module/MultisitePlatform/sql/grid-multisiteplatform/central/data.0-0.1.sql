-- default values for table: settings

INSERT INTO "_central"."settings" ( "key", "value" )
                           VALUES ( 'site.createSchema.user.platformOwner.displayName', 'Platform owner' ),
                                  ( 'site.createSchema.user.platformOwner.passwordHash', '' ),
                                  ( 'site.createSchema.user.platformOwner.locale', 'en' ),
                                  ( 'site.createSchema.referenceSchema', '_template' ),
                                  ( 'site.createSchema.siteCreatorSiteSchema', 'central' );
