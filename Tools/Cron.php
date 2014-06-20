<?php
/*************************************************************************************/
/*      This file is part of the Thelia package.                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : dev@thelia.net                                                       */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace ShoppingFlux\Tools;

/**
 * Class Cron
 * @package ShoppingFlux\Tools
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class Cron 
{
    public static function isCronAvailable()
    {
        $output = array();
        exec("crontab -l", $output);

        return count($output) >= 1 && !preg_match("not allowed", $output[0]);
    }

    public static function addCronLine($command)
    {
        $safe_command = escapeshellcmd($command);

        exec("echo \"@hourly php ".THELIA_ROOT."/Thelia ".$command."\" | crontab -e");
    }
}