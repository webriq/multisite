-- update default values for table: user

DO LANGUAGE plpgsql $$
BEGIN

    IF '_template' = CURRENT_SCHEMA THEN

        ALTER TABLE "user"
            DISABLE TRIGGER USER;

        DELETE FROM "user";

        ALTER TABLE "user"
             ENABLE TRIGGER USER;

        SELECT setval( 'user_id_seq', 0 );

    END IF;

END $$;
