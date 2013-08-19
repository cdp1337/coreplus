<?php
/**
 * Created by JetBrains PhpStorm.
 * User: hinschn
 * Date: 7/24/12
 * Time: 10:13 AM
 * To change this template use File | Settings | File Templates.
 */
class JQueryCycle {
    public static function Load(){

        \Core\view()->addScript ('js/jquery.cycle.all.js');

        // IMPORTANT!  Tells the script that the include succeeded!
        return true;
    }
}
