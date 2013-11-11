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