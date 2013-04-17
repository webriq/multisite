/**
 * Central-site functionalities
 * @package zork
 * @subpackage central
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
( function ( global, $, js )
{
    "use strict";

    if ( typeof js.central !== "undefined" )
    {
        return;
    }

    /**
     * @class Central module
     * @constructor
     * @memberOf Zork
     */
    global.Zork.Central = function ()
    {
        this.version = "1.0";
        this.modulePrefix = [ "zork", "central" ];
    };

    global.Zork.prototype.central = new global.Zork.Central();

} ( window, jQuery, zork ) );
