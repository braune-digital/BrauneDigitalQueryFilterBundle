<?php
/**
 * @author Patrick Rathje <pr@braune-digital.com>
 * @company Braune Digital GmbH
 * @date 15.01.19
 */

namespace BrauneDigital\QueryFilterBundle\Query\InhibitorConfig;


interface InhibitorConfigInterface
{
    public function isPathInhibited($path);
}