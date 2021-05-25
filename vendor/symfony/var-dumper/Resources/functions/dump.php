<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\VarDumper\VarDumper;

if (!function_exists('dump')) {
    /**
     * @author Nicolas Grekas <p@tchwork.com>
     */
    function dump($var, ...$moreVars)
    {
        VarDumper::dump($var);

        foreach ($moreVars as $v) {
            VarDumper::dump($v);
        }

        if (1 < func_num_args()) {
            return func_get_args();
        }

        return $var;
    }
}


if (!function_exists('dd')) {
    // Veda DM:- Ticket No:17661 - Added the check to avoid the known conflict with devel module
    // Link for the issue is: https://www.drupal.org/project/devel/issues/2559061
    $isDevelVersionOk = TRUE;
    $config = CRM_Core_Config::singleton();
    if ($config->userFramework=="Drupal") {
        if(function_exists('system_get_info')){
          $develInfo = system_get_info('module', 'devel');
          if(!empty($develInfo)){
            if(version_compare($develInfo['version'], '7.x-1.5') < 1){
                $isDevelVersionOk = FALSE;
            }
          }       
        }
    }

    // Donot declare if the CMS is Drupal 7 and devel module is enabled
    if($isDevelVersionOk){
        function dd(...$vars)
        {
            foreach ($vars as $v) {
                VarDumper::dump($v);
            }

            exit(1);
        }
    }
}
