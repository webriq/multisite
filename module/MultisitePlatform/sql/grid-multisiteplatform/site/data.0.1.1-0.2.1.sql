-- update default values for table: user

DO LANGUAGE plpgsql $$
BEGIN

    IF '_template' = CURRENT_SCHEMA THEN

        ALTER TABLE "user"
            DISABLE TRIGGER USER;

        DELETE FROM "user";

        ALTER TABLE "user"
             ENABLE TRIGGER USER;

        ALTER SEQUENCE "user_id_seq"
               RESTART;

    END IF;

END $$;
