-- update default values for table: module

DO LANGUAGE plpgsql $$
BEGIN

    IF '_template' = CURRENT_SCHEMA THEN

        UPDATE "module"
           SET "enabled" = FALSE
         WHERE "module"  = 'Grid\DomainManager';

    END IF;

END $$;
