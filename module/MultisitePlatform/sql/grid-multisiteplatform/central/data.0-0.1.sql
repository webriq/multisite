-- default values for table: settings

DO LANGUAGE plpgsql $$
BEGIN

    IF NOT EXISTS ( SELECT "value"
                      FROM "_central"."settings"
                     WHERE "key" = 'site.createSchema.referenceSchema' ) THEN

        INSERT INTO "_central"."settings" ( "key", "value" )
                                   VALUES ( 'site.createSchema.referenceSchema',
                                            '_template' );

    END IF;

END $$;
