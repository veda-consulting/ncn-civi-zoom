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

// Veda DM:- Ticket No:17661 - Added the check to avoid the known conflict with devel module
// Link for the issue is: https://www.drupal.org/project/devel/issues/2559061
$config = CRM_Core_Config::singleton();
if ($config->userFramework=="Drupal") {
  $info = system_get_info('module', 'devel');
  if(!empty($info)){
    if (!function_exists('dd')) {
        function dd(...$vars)
        {
            foreach ($vars as $v) {
                VarDumper::dump($v);
            }

            exit(1);
        }
    }
  }
}
