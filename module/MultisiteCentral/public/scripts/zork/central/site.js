/**
 * Central-site functionalities
 * @package zork
 * @subpackage central
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
( function ( global, $, js )
{
    "use strict";

    if ( typeof js.central.site !== "undefined" )
    {
        return;
    }

    var wizard  = js.require( "js.wizard" ),
        message = js.require( "js.ui.message" );

    /**
     * @class CentralSite module
     * @constructor
     * @memberOf Zork
     */
    global.Zork.Central.Site = function ()
    {
        this.version = "1.0";
        this.modulePrefix = [ "zork", "central", "site" ];
    };

    global.Zork.Central.prototype.site = new global.Zork.Central.Site();

    /**
     * Site create wizard
     *
     * @param {$|HTMLElement} element
     * @memberOf Zork.Central.Site
     */
    global.Zork.Central.Site.prototype.create = function ( element )
    {
        element     = $( element );
        var form    = element.find( "form:first" );

        if ( form.length ) {
            form.find( ":input[name='cancel']" )
                .remove();

            form.submit( function () {
                wizard( {
                    "url"   : form.attr( "action" ),
                    "form"  : form,
                    "cancel": function ( cancel ) {
                        cancel = $( cancel );

                        message( {
                            "title": cancel.attr( "title" ),
                            "message": cancel.text()
                        } );
                    },
                    "finish": function ( finish, close ) {
                        finish = $( finish );

                        finish.find( "a, button, input[type=button]" )
                              .on( "click", close );

                        return false;
                    }
                } );
            } );
        }
    };

    global.Zork.Central.Site.prototype.create.isElementConstructor = true;

    /**
     * Site create wizard continue
     *
     * @param {$|HTMLElement} element
     * @memberOf Zork.Central.Site
     */
    global.Zork.Central.Site.prototype.confirmed = function ( element )
    {
        element = $( element );
        var url = element.attr( "href" );

        if ( url ) {
            wizard( {
                "url"   : url,
                "cancel": function ( cancel ) {
                    cancel = $( cancel );

                    message( {
                        "title": cancel.attr( "title" ),
                        "message": cancel.text()
                    } );
                },
                "finish": function ( finish, close ) {
                    finish = $( finish );

                    finish.find( "a, button, input[type=button]" )
                          .on( "click", close );

                    return false;
                }
            } );

            element.remove();
        }
    };

    global.Zork.Central.Site.prototype.confirmed.isElementConstructor = true;

} ( window, jQuery, zork ) );
