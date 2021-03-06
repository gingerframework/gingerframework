<?php
/*
 * This file is part of the prooph processing framework.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 22.11.14 - 23:47
 */

namespace Prooph\Processing\Environment;

/**
 * Interface Connector
 *
 * @package Prooph\Processing\Environment
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
interface Connector extends Plugin
{
    /**
     * Return a composer cli require package argument string
     * for the package that includes the supported Prooph\Processing\Types of the plugin
     *
     * This can be same package as of the plugin itself but be aware that this package will be installed on every node
     *
     * @example: vendor/package:2.*
     *
     * @return string
     */
    public function getSupportedTypesComposerPackage();

    /**
     * Return an array containing each supported Prooph\Processing\Type class as key
     * and all supported workflow messages for that Prooph\Processing\Type as value list
     *
     * You can use the short hand of the workflow messages:
     * - collect-data   -> tells the system that the type can be collected by the plugin
     * - data-collected -> tells the system that the plugin wants to be informed when the type was collected
     * - process-data   -> tells the system that the type can be processed by the plugin
     * - data-processed -> tells the system that the plugin wants to be informed when the type was processed
     *
     * @example
     *
     * ['Vendor\Type\User' => ['collect-data', 'data-processed'], 'Vendor\Type\']
     *
     * @return array
     */
    public function getSupportedMessagesByTypeMap();
}
 