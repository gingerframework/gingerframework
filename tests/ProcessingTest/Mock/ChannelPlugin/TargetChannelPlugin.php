<?php
/*
 * This file is part of the prooph processing framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 06.02.15 - 21:17
 */

namespace Prooph\ProcessingTest\Mock\ChannelPlugin;

use Prooph\ProcessingTest\Mock\SimpleBusPlugin;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;

final class TargetChannelPlugin extends SimpleBusPlugin
{

}
 